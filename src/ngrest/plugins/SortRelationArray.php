<?php

namespace luya\admin\ngrest\plugins;

use luya\admin\traits\LazyDataLoadTrait;/**
 * Sort Relation Array Plugin.
 * Generate a multi selectable and sortable list based on an arry input.
 * Example usage:
 * ```php
 * public function ngRestAttributeTypes()
 * {
 *     'genres' => ['sortRelationArray', 'data' => [1 => 'Jazz', 2 => 'Funk', 3 => 'Soul']
 * }
 * ```
 *
 * Or use a closure for lazy data load:
 *
 * ```php
 * public function ngRestAttributeTypes()
 * {
 *     return [
 *         'genres' => ['sortRelationArray', 'data' => function () {
 *               return new Query()->all();
 *          }],
 *     ];
 * }
 * ```
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */

class SortRelationArray extends SortRelation
{
    use LazyDataLoadTrait;

    private $_data;

    /**
     * @inheritdoc
     */
    public function getData()
    {
        $data = [];
        foreach ($this->lazyLoadData($this->_data) as $value => $label) {
            $data[] = ['value' => $value, 'label' => $label];
        }

        return ['sourceData' => $data];
    }

    /**
     * Setter method for the data.
     *
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->_data = $data;
    }
}
