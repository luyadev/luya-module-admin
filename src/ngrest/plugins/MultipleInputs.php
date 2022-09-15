<?php

namespace luya\admin\ngrest\plugins;

use luya\admin\ngrest\base\Plugin;
use luya\helpers\Json;
use Yii;

/**
 * Multiple Input Types
 * 
 * ```php
 * 'text' => [
                'class' => MultipleInputs::class,
                'types' => [
                    [
                        'type' => TypesInterface::TYPE_TEXT,
                        'var' => 'title',
                        'label' => 'Titel!',
                    ],
                    [
                        'type' => TypesInterface::TYPE_IMAGEUPLOAD,
                        'var' => 'image',
                        'label' => 'Bild',
                    ]
                ]
            ]
 * ```
 * @author Basil Suter <basil@nadar.io>
 * @since 4.6.0
 */
class MultipleInputs extends Plugin
{
    public $types = [];

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
        return $this->createFormTag('zaa-multiple-inputs', $id, $ngModel, [
            'options' => $this->types,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function renderUpdate($id, $ngModel)
    {
        return $this->renderCreate($id, $ngModel);
    }

    /**
     * {@inheritDoc}
     */
    public function onBeforeSave($event)
    {
        if (!$this->i18n) {
            $this->writeAttribute($event, Json::encode($event->sender->getAttribute($this->name)));
            return false;
        }

        return true;
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function onAfterFind($event)
    {
        $this->writeAttribute($event, Json::decode($event->sender->getAttribute($this->name)));

        return true;
    }
}
