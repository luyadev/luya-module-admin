<?php

namespace luya\admin\buttons;

use luya\admin\ngrest\base\ActiveButton;
use luya\admin\ngrest\base\NgRestModel;
use luya\admin\Module;

/**
 * Adds a duplicate row button to the CRUD.
 * 
 * @author Basil Suter <basil@nadar.io>
 * @since 1.2.3
 */
class DuplicateActiveButton extends ActiveButton
{
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
        $copy = clone $model;
        $copy->isNewRecord = true;
        foreach ($model->getPrimaryKey(true) as $field => $value) {
            unset($copy->{$field});
        }
        
        if ($copy->save()) {
            $this->sendReloadEvent();
            return $this->sendSuccess(Module::t('active_button_duplicate_success'));
        }

        $message = implode(" ", $copy->getErrorSummary(true));
        return $this->sendError(Module::t('active_button_duplicate_error', ['message' => $message]));
    }
}