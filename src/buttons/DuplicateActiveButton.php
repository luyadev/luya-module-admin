<?php

namespace luya\admin\buttons;

use luya\admin\Module;
use luya\admin\ngrest\base\ActiveButton;
use luya\admin\ngrest\base\NgRestModel;

/**
 * Adds a duplicate row button to the CRUD.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.2.3
 */
class DuplicateActiveButton extends ActiveButton
{
    /**
     * @var boolean Whether the model should be validate while saving or not.
     * @since 3.0.0
     */
    public $modelValidation = true;

    /**
     * {@inheritDoc}
     */
    public function getDefaultIcon()
    {
        return 'control_point_duplicate';
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultLabel()
    {
        return Module::t('active_button_duplicate_label');
    }

    /**
     * {@inheritDoc}
     */
    public function handle(NgRestModel $model)
    {
        $data = $model::ngRestFind()->byPrimaryKey($model->getPrimaryKey())->asArray()->one();

        if (!$data) {
            return $this->sendError(Module::t('active_button_duplicate_error', ['message' => 'Model with id ' . $model->getPrimaryKey() . ' not found.']));
        }

        $copy = new $model();
        $copy->attributes = $data;

        if ($copy->save($this->modelValidation)) {
            $this->sendReloadEvent();
            return $this->sendSuccess(Module::t('active_button_duplicate_success'));
        }

        $message = implode(" ", $copy->getErrorSummary(true));
        return $this->sendError(Module::t('active_button_duplicate_error', ['message' => $message]));
    }
}
