<?php

namespace luya\admin\ngrest\plugins;

use luya\admin\ngrest\base\Plugin;
use luya\helpers\ArrayHelper;

/**
 * Create a HTML5 number-tag.
 *
 * You can optional set a placeholder value to guide your users, or an init value which will be assigned
 * to the angular model if nothing is set.
 *
 * Example for default init Value
 *
 * ```php
 * 'sort_index' => ['number', 'initValue' => 1000],
 * ```
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class Number extends Plugin
{
    /**
     * @var integer The default init value for this field
     */
    public $initValue = 0;

    /**
     * @var integer Html field placeholder
     */
    public $placeholder;

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
        return $this->createFormTag('zaa-number', $id, $ngModel, ['placeholder' => $this->placeholder, 'initvalue' => $this->initValue]);
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
            $this->writeAttribute($event, (int) $fieldValue);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function onAfterFind($event)
    {
        $this->writeAttribute($event, (int) $event->sender->getAttribute($this->name));

        return true;
    }
}
