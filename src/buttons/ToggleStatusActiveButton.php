<?php

namespace luya\admin\buttons;

use luya\admin\Module;
use luya\admin\ngrest\base\ActiveButton;
use luya\admin\ngrest\base\NgRestModel;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;

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

    /**
     * Value which will saved to the model as active status.
     * @var mixed
     */
    public $enableValue = true;

    /**
     * Value which will saved to the model as inactive status.
     * @var mixed
     */
    public $disableValue = false;

    /**
     * Keep only one model with active status and disable all other entries.
     * @var bool
     */
    public $uniqueStatus = false;

    /**
     * Attribute name for the success notification.
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
            throw new InvalidConfigException("The attribute property must be set.");
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
            if ($newValue == $this->enableValue) {
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

    /**
     * Return the opposite attribute value for $enableValue or $disableValue.
     *
     * @param $value
     *
     * @return mixed
     */
    private function toggleValue($value)
    {
        return match ($value) {
            $this->disableValue => $this->enableValue,
            $this->enableValue => $this->disableValue,
            default => throw new InvalidArgumentException("The value '$value' could not toggled."),
        };
    }
}
