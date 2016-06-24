<?php

namespace Statamic\Addons\Workshop;

use Statamic\API\URL;
use Statamic\API\Crypt;
use Statamic\API\Entry;
use Statamic\API\Helper;
use Statamic\API\Content;
use Statamic\Extend\Tags;
use Stringy\StaticStringy as Stringy;

class WorkshopTags extends Tags
{
    /**
     * Fields that can be overridden with tag parameters
     *
     * @var array
     */
    private $meta = [
        'collection',
        'date',
        'fieldset',
        'published',
		'parent',
        'redirect',
        'slug',
        'slugify'
    ];

    /**
     * The middleman. The camelCase handler. The dude.
     * We are using workshop:noun:verb syntax, and
     * this does the magic transformation.
     *
     * @param string $method
     * @param array  $args
     * @return method
     */
    public function __call($method, $args)
    {
        $method = Stringy::camelize(str_replace(':', '_', $method));
        
        if (method_exists($this, $method)) {
            return $this->$method();
        }
    }

    /**
     * The {{ workshop:entry:create }} tag
     *
     * @return string|array
     */
    public function entryCreate()
    {
        $data = [];

        $html = $this->formOpen('entryCreate');
        $html .= $this->getMetaFields();
        $html .= $this->parse($data);
        $html .= '</form>';

        return $html;
    }

    /**
     * The {{ workshop:entry:create }} tag
     *
     * @return string|array
     */
    public function entryEdit()
    {
        $url = $this->get('url', URL::getCurrent());

        $entry = ($this->get('id')) ? Content::uuidRaw($this->get('id')) : Content::getRaw($url);

        $html = $this->formOpen('entryUpdate');
        $html .= $this->parse($entry->data());
        $html .= '</form>';

        return $html;
    }

    /**
     * The {{ workshop:page:create }} tag
     *
     * @return string|array
     */
    public function pageCreate()
    {
        $data = [];

        $html = $this->formOpen('pageCreate');
        $html .= $this->getMetaFields();
        $html .= $this->parse($data);
        $html .= '</form>';

        return $html;
    }

    /**
     * Maps to {{ form:success }}
     *
     * @return bool
     */
    public function success()
    {
        return $this->flash->exists('success');
    }

    /**
     * Encypts any special meta fields set as tag parameters
     * and sets them in a special HTML hidden input field.
     *
     * @return string
     */
    private function getMetaFields()
    {
        $meta = array_intersect_key($this->parameters, array_flip($this->meta));

        return '<input type="hidden" name="_meta" value="'. Crypt::encrypt($meta) .'" />';
    }
}
