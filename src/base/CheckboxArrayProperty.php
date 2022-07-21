<?php

namespace luya\admin\base;

use luya\admin\helpers\Angular;

/**
 * Checkbox Array Property.
 *
 * Provide items to select and returns the selected items.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
abstract class CheckboxArrayProperty extends Property
{
    /**
     * Key value array for the options.
     *
     * @return array An array with a key and and a value, the key will be stored when selecting data.
     */
    abstract public function items();

    /**
     * @inheritdoc
     */
    public function type()
    {
        return self::TYPE_CHECKBOX_ARRAY;
    }

    /**
     * @inheritdoc
     */
    public function options()
    {
        return ['items' => Angular::optionsArrayInput($this->items())];
    }
}
