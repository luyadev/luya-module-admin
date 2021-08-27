<?php

namespace luya\admin\ngrest\plugins;

use luya\admin\helpers\Angular;
use luya\admin\traits\LazyDataLoadTrait;
use luya\helpers\ArrayHelper;

/**
 * Nondestructive analogue of SelectArray plugin.
 *
 * Create a selection based on an assoc array provided via $data attribute.
 * Will NOT override the default values from the database.
 *
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
 * @author Anton Ikonnikov <antikon2@yandex.ru>
 * @since 4.1.0
 */
class SelectArrayGently extends Select
{
    use LazyDataLoadTrait;
    
    private $_data;


    public $assignAfterFind = false;

    /**
     * @inheritdoc
     */
    public function renderList($id, $ngModel)
    {
        if ($this->scheduling && $this->renderContext->canUpdate()) {
            return $this->createSchedulerListTag($ngModel, $this->getData(), 'item');
        }

        $options = Angular::optionsFilter([
                                              'options' => $this->getServiceName('selectdata'),
                                          ]);

        return $this->createTag('select-array-gently', null, ArrayHelper::merge(['model' => $ngModel], $options));
    }
    
    /**
     * Setter method for Data.
     *
     * @param array $data
     */
    public function setData(array $data)
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
