<?php

namespace luya\admin\ngrest\plugins;

use luya\helpers\StringHelper;
use luya\admin\ngrest\base\Plugin;

/**
 * Base class for select dropdowns via Array or Model.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
abstract class Select extends Plugin
{
    /**
     * If enabled the list tag will transform into an interactive schedling overlay.
     *
     * @var boolean
     * @since 1.3.0
     */
    public $scheduling = false;

    /**
     * @var integer|string If an init value is available which is matching with the select data, you can not reset the model to null. So initvalue ensures
     * that a value must be selected, or selects your initvalue by default.
     */
    public $initValue = 0;
    
    /**
     * @var string This value will be displayed in the ngrest list overview if the given value is empty(). In order to turn off this behavior set `emptyListValue` to false.
     */
    public $emptyListValue = "-";

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
        return $this->createFormTag(self::TYPE_SELECT, $id, $ngModel, [
            'initvalue' => $this->initValue,
            'options' => $this->getServiceName('selectdata'),
        ]);
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
    }
}
