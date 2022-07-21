<?php

namespace luya\admin\ngrest\plugins;

use luya\admin\helpers\Angular;
use luya\admin\ngrest\base\Plugin;
use luya\helpers\StringHelper;

/**
 * Base class for select dropdowns via Array or Model.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
abstract class Select extends Plugin
{
    /**
     * @var boolean If enabled the list tag will transform into an interactive scheduling overlay. Keep in mind to turn on the queue system in order to enable time based scheduling
     * @see {{luya\admin\Module::$autoBootstrapQueue}}
     * @since 2.0.0
     */
    public $scheduling = false;

    /**
     * @var integer|string If an init value is available which is matching with the select data, you can not reset the model to null. So initvalue ensures
     * that a value must be selected, or selects your initvalue by default. Since version 2.0 the default value is `''` instead of `0` otherwise the common
     * rule setup for those attributes won't handle the required field correctly until you set `isEmpty` option in the validator config. Using `null` as init value
     * will remove the field from the payload, therefore using `''` instead.
     */
    public $initValue = '';

    /**
     * @var string This value will be displayed in the ngrest list overview if the given value is empty(). In order to turn off this behavior set `emptyListValue` to false.
     */
    public $emptyListValue = "-";

    /**
     * @var boolean If enabeld, which is default, the selected value will be automaticcaly assigned with the model attribute and override its default
     * value from the database. This might be a problem when working with relations.
     * @see https://github.com/luyadev/luya-module-admin/issues/439
     * @since 3.0.0
     */
    public $assignAfterFind = true;

    /**
     * Getter method for data array.
     *
     * @return array
     */
    abstract public function getData();

    /**
     * @inheritdoc
     */
    public function renderList($id, $ngModel)
    {
        if ($this->scheduling && $this->renderContext->canUpdate()) {
            return $this->createSchedulerListTag($ngModel, $this->getData(), 'item');
        }

        return $this->createListTag($ngModel);
    }

    /**
     * @inheritdoc
     */
    public function renderCreate($id, $ngModel)
    {
        return $this->createFormTag(self::TYPE_SELECT, $id, $ngModel, Angular::optionsFilter([
            'initvalue' => $this->initValue,
            'options' => $this->getServiceName('selectdata'),
        ]));
    }

    /**
     * @inheritdoc
     */
    public function renderUpdate($id, $ngModel)
    {
        if ($this->scheduling && $this->renderContext->canUpdate()) {
            return [
                '<div class="crud-loader-tag">' . $this->createSchedulerListTag($ngModel, $this->getData(), 'data.update', ['only-icon' => 1]) . '</div>',
                $this->renderCreate($id, $ngModel),
            ];
        }

        return $this->renderCreate($id, $ngModel);
    }

    /**
     * @inheritdoc
     */
    public function serviceData($event)
    {
        return ['selectdata' => $this->getData()];
    }

    /**
     * @inheritdoc
     */
    public function onAfterListFind($event)
    {
        if (!$this->assignAfterFind) {
            return parent::onAfterListFind($event);
        }

        $value = StringHelper::typeCast($event->sender->getAttribute($this->name));

        if ($this->scheduling) {
            $this->writeAttribute($event, $value);
        } else {
            if ($this->emptyListValue && empty($value)) {
                $this->writeAttribute($event, $this->emptyListValue);
            } else {
                foreach ($this->getData() as $item) {
                    if (StringHelper::typeCast($item['value']) === $value) {
                        $this->writeAttribute($event, $item['label']);
                    }
                }
            }
        }

        return true;
    }
}
