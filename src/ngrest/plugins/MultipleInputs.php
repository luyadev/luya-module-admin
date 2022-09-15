<?php

namespace luya\admin\ngrest\plugins;

use luya\admin\ngrest\base\Plugin;
use luya\helpers\Json;
use Yii;

/**
 * Multiple Input Types
 * 
 * ```php
 * 'data_json' => [
 *     'class' => MultipleInputs::class,
 *     types' => [
 *         [
 *             'type' => TypesInterface::TYPE_TEXT,
 *             'var' => 'title',
 *             'label' => 'Title',
 *         ],
 *         [
 *             'type' => TypesInterface::TYPE_IMAGEUPLOAD,
 *             'var' => 'image',
 *             'label' => 'Image',
 *         ],
 *         [
 *             'type' => TypesInterface::TYPE_SELECT,
 *             'var' => 'salutation',
 *             'label' => 'Salutation',
 *             'options' => [
 *                  ['value' => 1, 'label' => 'Mr.'],
 *                  ['value' => 2, 'label' => 'Mrs.'],
 *             ]
 *         ],
 *     ]
 * ]
 * ```
 * 
 * > Keep in mind, this requires a database field which should be text format, as the data will be transformed to json.
 * 
 * The above output array could be:
 * 
 * ```php
 * [
 *     [
 *         'title' => 'Foo Bar',
 *         'image' => 1,
 *         'salutation' => 1,
 *     ],
 *     [
 *         'title' => 'Bar Foo',
 *         'image' => 2,
 *         'salutation' => 2,
 *     ],
 * ]
 * ```
 * 
 * @author Basil Suter <basil@nadar.io>
 * @since 4.6.0
 */
class MultipleInputs extends Plugin
{
    /**
     * @var array This option is required, it defines the different fields inside this array. Use {{luya\admin\base\TypesInterface}} for a list of all types.
     * 
     * ```php
     * types' => [
     *     [
     *         'type' => \luya\admin\base\TypesInterface::TYPE_TEXT,
     *         'var' => 'title',
     *         'label' => 'Title',
     *     ],
     * ]
     * ```
     */
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
