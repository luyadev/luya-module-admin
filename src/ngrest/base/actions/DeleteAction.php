<?php

namespace luya\admin\ngrest\base\actions;

use Yii;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/**
 * Delete
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class DeleteAction extends \yii\rest\DeleteAction
{
    /**
     * Run the delete action with enhanced error checking methods.
     *
     * @see \yii\rest\DeleteAction::run()
     */
    public function run($id)
    {
        $model = $this->modelClass::ngRestFind()->byPrimaryKey($id)->one();

        if (!$model) {
            throw new NotFoundHttpException("Object not found: $id");
        }

        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id, $model);
        }

        if ($model->delete() === false) {
            // custom implementation of LUYA in order to throw more informations when delete errors happen.
            if ($model->hasErrors()) {
                Yii::$app->getResponse()->setStatusCode(422);
                $errors = [];
                foreach ($model->getErrors() as $field => $errorMessages) {
                    foreach ($errorMessages as $message) {
                        $errors[] = ['field' => $field, 'message' => $message];
                    }
                }

                return $errors;
            }

            throw new ServerErrorHttpException('Failed to delete the object for unknown reason.');
        }

        Yii::$app->getResponse()->setStatusCode(204);
    }
}
