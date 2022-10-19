<?php

namespace luya\admin\ngrest\plugins;

use luya\admin\ngrest\base\Plugin;
use luya\helpers\ArrayHelper;

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
        return $this->createFormTag('zaa-decimal', $id, $ngModel, ['options' => json_encode(['steps' => $this->steps ], JSON_THROW_ON_ERROR)]);
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

        return true;
    }

    /**
     * @inheritdoc
     */
    public function onAfterFind($event)
    {
        $this->writeAttribute($event, (float) $event->sender->getAttribute($this->name));

        return true;
    }
}
