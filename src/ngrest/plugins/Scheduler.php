<?php

namespace luya\admin\ngrest\plugins;

use luya\admin\ngrest\base\Plugin;
use yii\base\InvalidConfigException;

/**
 * Create the Scheulder tag for a given field.
 * 
 * The scheduler tag allows you to change the given field value based on input values for a given field if a model is ailable.
 * 
 * Configuration example:
 * 
 * ```php
 * 'status' => ['scheduler', 'values' => [0 => 'Offline', 1 => 'Online']]
 * 
 * ```
 * <luya-schedule value="{{currentValueOfTheEntity}}" model-class="luya\admin\models\User" attribute-name="is_deleted" attribute-values="{0:'Not Deleted',1:'Deleted'}"
 * ```
 * 
 * @since 1.3.0
 * @author Basil Suter <basil@nadar.io>
 */
class Scheduler extends Plugin
{
    public $values = [];

    public function init()
    {
        parent::init();

        if (empty($this->values)) {
            throw new InvalidConfigException("The given scheulder config is invalide, you have to set at least a vlaues and modeclass property in order to run this plugin.");
        }
    }

    /**
     * @inheritdoc
     */
    public function renderList($id, $ngModel)
    {
        return $this->createTag('luya-schedule', 'barfoo', [
            'value' => $ngModel,
            'model-class' => get_class($this->renderContext->getModel()),
            'attribute-name' => $this->name,
            'attribute-values' => $this->values,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function renderCreate($id, $ngModel)
    {
        return $this->renderList($id, $ngModel);
    }

    /**
     * @inheritdoc
     */
    public function renderUpdate($id, $ngModel)
    {
        return $this->renderList($id, $ngModel);
    }
}