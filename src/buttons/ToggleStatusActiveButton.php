<?php

namespace luya\admin\buttons;

use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use luya\admin\Module;
use luya\admin\ngrest\base\ActiveButton;
use luya\admin\ngrest\base\NgRestModel;

/**
 * Set a boolean for a given attribute.
 *
 * This buttons allows you to save the active status for a given attribute in the model.
 *
 * Usage example:
 *
 * ```php
 * [
 *     'class' => 'luya\admin\buttons\ToggleStatusActiveButton',
 *     'attribute' => 'is_active',
 *     'label' => 'Set active',
 * ]
 * ```
 *
 * @author Bennet Klarhölter <boehsermoe@me.com>
 * @since 2.2.0
 */
class ToggleStatusActiveButton extends ActiveButton
{
    /**
     * @var string The attribute which should set.
     */
    public $attribute;
    
    public $enableValue = true;
    
    public $disableValue = false;
    
    /**
     * @var bool Keep only one model with active status and disable all other entries.
     */
    public $uniqueStatus = false;
    
    /**
     * @var string
     */
    public $modelNameAttribute = 'id';
    
    /**
     * {@inheritDoc}
     */
    public function getDefaultIcon()
    {
        return 'toggle_on';
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultLabel()
    {
        return Module::t('active_button_togglestatus_label');
    }

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        parent::init();

        if (!$this->attribute) {
            throw new InvalidConfigException("The attribute property can not be null.");
        }
    }

    /**
     * {@inheritDoc}
     */
    public function handle(NgRestModel $model)
    {
        $transaction = $model::getDb()->beginTransaction();
        
        try {
            $newValue = $this->toggleValue($model->getAttribute($this->attribute));
    
            if ($this->uniqueStatus) {
                $model::updateAll([$this->attribute => $this->disableValue]);
                $model->updateAttributes([$this->attribute => $newValue]);
            } else {
                $model->updateAttributes([$this->attribute => $newValue]);
            }
            
            $transaction->commit();

            $this->sendReloadEvent();
            if ($newValue) {
                return $this->sendSuccess(Module::t('active_button_togglestatus_enabled', ['modelName' => $model->{$this->modelNameAttribute}]));
            } else {
                return $this->sendSuccess(Module::t('active_button_togglestatus_disabled', ['modelName' => $model->{$this->modelNameAttribute}]));
            }
        } catch (\Throwable $ex) {
            $transaction->rollBack();
            throw $ex;
        }
    
        return $this->sendError(Module::t('active_button_togglestatus_error'));
    }
    
    private function toggleValue($value)
    {
        switch ($value) {
            case $this->disableValue:
                return $this->enableValue;
            case $this->enableValue:
                return $this->disableValue;
            default:
                throw new InvalidArgumentException("The value '$value' could not toggled.");
        }
    }
}
