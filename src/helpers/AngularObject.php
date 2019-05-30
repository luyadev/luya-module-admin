<?php

namespace luya\admin\helpers;

use yii\base\BaseObject;

/**
 * Angular Object from the Angular Helper methods.
 *
 * This object allows you to customize field informations by chaining methods.
 *
 * @since 2.0.0
 * @author Basil Suter <basil@nadar.io>
 */
class AngularObject extends BaseObject
{
    public $type;

    public $options = [];

    private $_hint;

    /**
     * Set a hint message
     *
     * @param string $text
     * @return static
     */
    public function hint($text)
    {
        $this->_hint = $text;

        return $this;
    }

    /**
     * Set placeholder information
     *
     * @param string $placeholder
     * @return static
     */
    public function placeholder($placeholder)
    {
        $this->options['placeholder'] = $placeholder;

        return $this;
    }

    /**
     * Set init value
     *
     * @param mixed $initvalue
     * @return static
     */
    public function initvalue($initvalue)
    {
        $this->options['initvalue'] = $initvalue;

        return $this;
    }

    /**
     * Set options valuess
     *
     * @param array $options
     * @return static
     */
    public function options(array $options)
    {
        $this->options['options'] = $options;

        return $this;
    }

    /**
     * Set a given option key with a value
     *
     * @param string $key
     * @param mixed $value
     * @return static
     */
    public function optionValue($key, $value)
    {
        $this->options['options'][$key] = $value;

        return $this;
    }

    /**
     * Render the Angular Object element
     *
     * @return string
     */
    public function render()
    {
        $html = null;
        if ($this->_hint) {
            $html = '<span class="help-button btn btn-icon btn-help" tooltip tooltip-text="'.$this->_hint.'" tooltip-position="left"></span>';
        }

        $html .= Angular::directive($this->type, $this->options);

        return $html;
    }

    /**
     * When the element is directly forced to echo its output this method is called and the template will be
     * render with the `render()` method.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }
}
