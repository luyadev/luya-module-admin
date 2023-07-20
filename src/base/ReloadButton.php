<?php

namespace luya\admin\base;

use yii\base\BaseObject;
use yii\base\InvalidConfigException;

/**
 * Reload Button Object.
 *
 * @property string $label
 * @property callable $callback
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 2.0.0
 */
class ReloadButton extends BaseObject
{
    /**
     * @var string The icon from material icon set.
     * @see https://material.io/tools/icons/
     */
    public $icon;

    /**
     * @var string The original label value which is used to display the generic success message.
     */
    public $originalLabel;

    /**
     * @var string The custom API response message.
     */
    public $response;

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        parent::init();

        if (!$this->icon || !$this->label || !$this->callback) {
            throw new InvalidConfigException("The reload button attributes icon, label and callback can not be empty.");
        }
    }

    private $_label;

    /**
     * Setter method for label
     *
     * @param string $label The label to set. Spaces will be replaced with &nsbp; in order to no wrap the button text.
     */
    public function setLabel($label)
    {
        $this->originalLabel = $label;
        $this->_label = str_replace(" ", "&nbsp;", $label);
    }

    /**
     * Getter method for label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->_label;
    }

    private $_callback;

    /**
     * Setter method for callback.
     *
     * @param callable $callback
     */
    public function setCallback(callable $callback)
    {
        $this->_callback = $callback;
    }

    /**
     * Getter method for callback
     *
     * @return callable
     */
    public function getCallback()
    {
        return $this->_callback;
    }
}
