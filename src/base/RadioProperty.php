<?php

namespace luya\admin\base;

use luya\admin\helpers\Angular;

/**
 * Radio Select Property.
 *
 * Radio input to select a given item from the list and returns its value.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
abstract class RadioProperty extends Property
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
        return self::TYPE_RADIO;
    }

    /**
     * @inheritdoc
     */
    public function options()
    {
        return Angular::optionsArrayInput($this->items());
    }
}
