<?php

namespace luya\admin\ngrest\plugins;

use luya\helpers\ArrayHelper;
use luya\admin\ngrest\base\Plugin;

/**
 * Decimal Input-Form field.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class Decimal extends Plugin
{
    /**
     *
     * @var float Floating point steps.
     */
    public $steps = 0.01;

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
        return $this->createFormTag('zaa-decimal', $id, $ngModel, ['options' => json_encode(['steps' => $this->steps ])]);
    }

    /**
     * @inheritdoc
     */
    public function renderUpdate($id, $ngModel)
    {
        return $this->renderCreate($id, $ngModel);
    }
    
    /**
     * @inheritdoc
     */
    public function onAfterExpandFind($event)
    {
        $fieldValue = $event->sender->getAttribute($this->name);
        
        if (is_array($fieldValue)) {
            $this->writeAttribute($event, ArrayHelper::typeCast($fieldValue));
        } else {
            $this->writeAttribute($event, (float) $fieldValue);
        }
    }
    
    /**
     * @inheritdoc
     */
    public function onAfterFind($event)
    {
        $this->writeAttribute($event, (float) $event->sender->getAttribute($this->name));
    }
}
