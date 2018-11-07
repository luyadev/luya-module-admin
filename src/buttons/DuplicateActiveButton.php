<?php

namespace luya\admin\buttons;

use luya\admin\ngrest\base\ActiveButton;
use luya\admin\ngrest\base\NgRestModel;

/**
 * Adds a duplicate row button.
 * 
 * @author Basil Suter <basil@nadar.io>
 * @since 1.2.3
 */
class DuplicateActiveButton extends ActiveButton
{
    public $icon = 'control_point_duplicate';

    public $label = 'Duplicate';

    public function handle(NgRestModel $model)
    {
        $copy = clone $model;
        $copy->isNewRecord = true;
        foreach ($model->getPrimaryKey(true) as $field => $value) {
            unset($copy->{$field});
        }
        
        if ($copy->save()) {
            $this->sendReloadEvent();
            return $this->sendSuccess("A copy has been made.");
        }

        return $this->sendError("Error while duplicate the given model." . var_export($copy->getErrors(), true));
    }
}