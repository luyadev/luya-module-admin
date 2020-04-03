<?php

namespace luya\admin\ngrest\plugins;

use luya\admin\ngrest\base\Plugin;
use yii\base\InvalidConfigException;

/**
 * Create a dynamic form input based on Angular Directives.
 *
 * ```php
 * public function ngRestAttributeTypes()
 * {
 *     return [
 *         'type' => ['selectArrray', 'data' => [self::TYPE_PASSWORD => 'Passwort Input', self::TYPE_TEXT => 'Text Input']],
 *         'value' => ['injector', 'attribute' => 'type'],
 *     ];
 * }
 * ```
 *
 * The above example shows how the first attribute contains the value of the directive inside the $type attribute, the second
 * attribute $value uses the injector plugin in order to rendern this given type interactively in the form. This allows you to
 * change the input type dynamically while typing.
 *
 * In order to see all possible zaa directive types take a look at {{luya\admin\base\TypesInterface}}.
 *
 * @author Bennet Klarhoelter <boehsermoe@me.com>
 * @since 1.2.3
 */
class Injector extends Plugin
{
    /**
     * @var string Property name from the model to use as ZAA Directive.
     */
    public $attribute;

    /**
     * @inheritdoc
     */
    public function init()
    {
        /**
         * @see BaseObject::canGetProperty
         */
        if ($this->renderContext && !$this->renderContext->getModel()->hasAttribute($this->attribute)) {
            throw new InvalidConfigException("Model property `$this->attribute` must be exist and readable.");
        }

        parent::init();
    }

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
        return $this->createFormTag('zaa-injector', $id, $ngModel, ['dir' => 'data.create.' . $this->attribute, 'options' => null]);
    }

    /**
     * @inheritdoc
     */
    public function renderUpdate($id, $ngModel)
    {
        return $this->createFormTag('zaa-injector', $id, $ngModel, ['dir' => 'data.update.' . $this->attribute, 'options' => null]);
    }
}
