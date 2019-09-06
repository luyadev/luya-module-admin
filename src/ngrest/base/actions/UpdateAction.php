<?php

namespace luya\admin\ngrest\base\actions;

use Yii;
use luya\admin\models\UserOnline;
use yii\web\ServerErrorHttpException;
use yii\web\NotFoundHttpException;

/**
 * UpdateAction for REST implementation.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class UpdateAction extends \yii\rest\UpdateAction
{
    public function run($id)
    {
        /* @var $model ActiveRecord */
        $model = $this->controller->findModelClassObject($this->modelClass, $id, 'update');

        if (!$model) {
            throw new NotFoundHttpException("Object not found: $id");
        }
        
        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id, $model);
        }
        
        $model->scenario = $this->scenario;
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->save() === false && !$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to update the object for unknown reason.');
        }

        UserOnline::unlock(Yii::$app->adminuser->id);

        return $model;
    }
}
