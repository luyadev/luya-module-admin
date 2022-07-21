<?php

namespace luya\admin\ngrest\plugins;

use luya\admin\helpers\Angular;
use luya\admin\ngrest\base\Plugin;

/**
 * Create a slugable input field.
 *
 * The input field will remove whitespaces and other not valid utf8 url chars from the string while typing.
 *
 * If you like to generate the slug value from another field you can configure listenere with a listener attribute.
 *
 * Example configuration where slug reads from input title.
 *
 * ```php
 * public function ngRestAttributeTypes()
 * {
 *     return [
 *         'title' => 'text',
 *         'slug' => ['slug', 'listener' => 'title'],
 *     ];
 * }
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class Slug extends Plugin
{
    /**
     * @var string Input field placeholder value.
     */
    public $placeholder;

    /**
     * @var string Define another input attribute as listener, which will then pass trough the input value from
     * the listener field into the slug field while typing.
     * @since 1.2.1
     */
    public $listener;

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
        return $this->createFormTag('zaa-slug', $id, $ngModel, [
            'placeholder' => $this->placeholder,
            'listener' => Angular::replaceFieldFromNgModelContext($ngModel, $this->listener),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function renderUpdate($id, $ngModel)
    {
        return $this->renderCreate($id, $ngModel);
    }
}
