<?php

namespace luya\admin\ngrest\plugins;

use luya\admin\ngrest\base\Plugin;
use luya\helpers\Html as HtmlHelper;

/**
 * Create a generic form input based on ZAA Directives.
 *
 * ```php
 *   public function ngRestAttributeTypes()
 *   {
 *      return [
 *          'name' => 'text',
 *          'type' => 'text',
 *          'value' => ['injector', 'directiveField' => 'type'],
 *      ];
 *   }
 * ```
 *
 * @author Bennet Klarhoelter <boehsermoe@me.com>
 * @since 1.2.3
 */
class Injector extends Plugin
{
    /**
     * @var string Property name from the model to use as ZAA Directive.
     */
    public $directiveField;

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
        return $this->createFormTag('zaa-injector', $id, $ngModel, ['dir' => 'data.create.' . $this->directiveField, 'options' => null]);
    }

    /**
     * @inheritdoc
     */
    public function renderUpdate($id, $ngModel)
    {
        return $this->createFormTag('zaa-injector', $id, $ngModel, ['dir' => 'data.update.' . $this->dir, 'options' => null]);
    }
}
