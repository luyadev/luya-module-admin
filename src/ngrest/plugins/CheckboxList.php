<?php

namespace luya\admin\ngrest\plugins;

use luya\admin\helpers\I18n;
use luya\admin\ngrest\base\Plugin;
use luya\admin\traits\LazyDataLoadTrait;
use luya\helpers\ArrayHelper;
use luya\helpers\StringHelper;

/**
 * Create a checkbox list with selection based on an array with key value pairing.
 *
 * Example usage:
 *
 * ```php
 * public function ngRestAttributeTypes()
 * {
 *     return [
 *         'genres' => ['checkboxList', 'data' => [1 => 'Male', 2 => 'Female']],
 *     ];
 * }
 * ```
 *
 * Or use a closure for lazy data load:
 *
 * ```php
 * public function ngRestAttributeTypes()
 * {
 *     return [
 *         'genres' => ['checkboxList', 'data' => function () {
 *               return new Query()->all();
 *          }],
 *     ];
 * }
 * ```
 *
 * The plugin stores the value of the selected checkbox items as json into the database.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class CheckboxList extends Plugin
{
    use LazyDataLoadTrait;

    /**
     * @var array|\Closure
     */
    public $data = [];

    public $i18nEmptyValue = [];

    /**
     * @inheritdoc
     */
    public function renderList($id, $ngModel)
    {
        return $this->createListTag($ngModel);
    }

    /**
     * @inheritdoc
     */
    public function renderCreate($id, $ngModel)
    {
        return $this->createFormTag('zaa-checkbox-array', $id, $ngModel, ['options' => $this->getServiceName('checkboxitems')]);
    }

    /**
     * @inheritdoc
     */
    public function renderUpdate($id, $ngModel)
    {
        return $this->renderCreate($id, $ngModel);
    }

    protected function getItems()
    {
        $data = [];

        foreach ($this->lazyLoadData($this->data) as $value => $label) {
            $data[] = ['value' => $value, 'label' => $label];
        }

        return ['items' => ArrayHelper::typeCast($data)];
    }

    /**
     * @inheritdoc
     */
    public function serviceData($event)
    {
        return ['checkboxitems' => $this->getItems()];
    }

    /**
     * @inheritdoc
     */
    public function onBeforeSave($event)
    {
        // if its not i18n casted field we have to serialize the file array as json and abort further event excution.
        if (!$this->i18n) {
            // as it could be an assigned array from the frontend model assigne via a form, we verify if the array contains a value key.
            $value = $event->sender->getAttribute($this->name);

            $data = [];
            if (is_array($value)) {
                foreach ($value as $key => $row) {
                    if (!is_array($row)) {
                        $data[] = ['value' => $row];
                    } else {
                        $data[] = $row;
                    }
                }
            } else {
                $data = $value;
            }

            $this->writeAttribute($event, I18n::encode($data));
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function onBeforeExpandFind($event)
    {
        if (!$this->i18n) {
            $this->writeAttribute($event, $this->jsonDecode($event->sender->getAttribute($this->name)));
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function onBeforeFind($event)
    {
        if (!$this->i18n) {
            $array = $this->jsonDecode($event->sender->getAttribute($this->name));
            $this->writeAttribute($event, ArrayHelper::getColumn($array, 'value'));
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function onAfterListFind($event)
    {
        $value = $event->sender->getAttribute($this->name);
        if (!$this->i18n) {
            $value = $this->jsonDecode($value);
        }

        $value = StringHelper::typeCast($value);

        if (!empty($value)) {
            $results = [];
            foreach ($this->getItems()['items'] as $item) {
                foreach ($value as $k => $v) {
                    if (isset($v['value']) && $item['value'] === $v['value']) {
                        $results[] = $item['label'];
                    }
                }
            }
            $this->writeAttribute($event, implode(", ", $results));
        }

        return true;
    }
}
