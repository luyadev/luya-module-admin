<?php

namespace luya\admin\buttons;

use luya\admin\Module;
use luya\admin\ngrest\base\ActiveButton;
use luya\admin\ngrest\base\NgRestModel;
use yii\base\InvalidConfigException;

/**
 * Set a timestamp for a given attribute.
 *
 * This buttons allows you to save the current timestamp for a given attribute in the model.
 *
 * Usage example:
 *
 * ```php
 * [
 *     'class' => 'luya\admin\buttons\TimestampActiveButton',
 *     'attribute' => 'update_at',
 *     'label' => 'Set Timestamp',
 * ]
 * ```
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 2.0.0
 */
class TimestampActiveButton extends ActiveButton
{
    /**
     * @var string|array The attribute (or attributes if an array is provided) which should receive the current timestamp (trough time() method).
     */
    public $attribute;

    /**
     * {@inheritDoc}
     */
    public function getDefaultIcon()
    {
        return 'update';
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultLabel()
    {
        return Module::t('active_button_timestamp_label');
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
        $map = [];
        foreach ((array) $this->attribute as $value) {
            $map[$value] = time();
        }
        if ($model->updateAttributes($map)) {
            $this->sendReloadEvent();
            return $this->sendSuccess(Module::t('active_button_timestamp_success'));
        }

        return $this->sendError(Module::t('active_button_timestamp_error'));
    }
}
