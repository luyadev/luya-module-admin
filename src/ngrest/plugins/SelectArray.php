<?php

namespace luya\admin\ngrest\plugins;

use luya\admin\traits\LazyDataLoadTrait;
use luya\helpers\ArrayHelper;

/**
 * Create a selection based on an assoc array provided via $data attribute.
 *
 * Example usage:
 *
 * ```php
 * public function ngRestAttributeTypes()
 * {
 *     'genres' => ['selectArray', 'data' => [1 => 'Male', 2 => 'Female']],
 * }
 * ```
 * Or use a closure for lazy data load:
 *
 * ```php
 * public function ngRestAttributeTypes()
 * {
 *     return [
 *         'genres' => ['selectArray', 'data' => function () {
 *               return new Query()->all();
 *          }],
 *     ];
 * }
 * ```
 *
 * @property array $data Setter/Getter for the dropdown values.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class SelectArray extends Select
{
    use LazyDataLoadTrait;

    private $_data;

    /**
     * Setter method for Data.
     *
     * @param array|closure $data
     */
    public function setData($data)
    {
        $this->_data = $data;
    }

    /**
     *
     * {@inheritDoc}
     * @see \luya\admin\ngrest\plugins\Select::getData()
     */
    public function getData()
    {
        $cleandata = [];

        foreach ($this->lazyLoadData($this->_data) as $key => $value) {
            $cleandata[] = [
                'value' => $key,
                'label' => $value,
            ];
        }

        return ArrayHelper::typeCast($cleandata);
    }
}
