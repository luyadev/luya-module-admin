<?php

namespace luya\admin\ngrest\base\actions;

use luya\admin\models\UserOnline;
use Yii;
use yii\db\ActiveRecordInterface;
use yii\web\NotFoundHttpException;

/**
 * View
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class ViewAction extends \yii\rest\ViewAction
{
    /**
     * Returns the data model based on the primary key given.
     * If the data model is not found, a 404 HTTP exception will be raised.
     * @param string $id the ID of the model to be loaded. If the model has a composite primary key,
     * the ID must be a string of the primary key values separated by commas.
     * The order of the primary key values should follow that returned by the `primaryKey()` method
     * of the model.
     *
     * > This override of parent models allows us to join the relation data without using extraFields() basically its a main idea
     * > behind yii relations and serializer which is not used for the view action without overriding findModel().
     *
     * @return ActiveRecordInterface the model found
     * @throws NotFoundHttpException if the model cannot be found
     * @since 1.2.2.1
     */
    public function findModel($id)
    {
        if ($this->findModel !== null) {
            return call_user_func($this->findModel, $id, $this);
        }


        $model = $this->controller->findModelClassObject($this->modelClass, $id, 'view');

        if ($model) {
            return $model;
        }

        throw new NotFoundHttpException("Object not found: $id");
    }

    /**
     * Return the model for a given resource id.
     *
     * @return yii\db\ActiveRecordInterface
     */
    public function run($id)
    {
        $model = $this->findModel($id);

        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id, $model);
        }

        if (!Yii::$app->adminuser->isGuest && !Yii::$app->adminuser->identity->is_api_user) {
            $modelClass = $this->modelClass;
            $table = $modelClass::tableName();
            $alias = Yii::$app->adminmenu->getApiDetail($modelClass::ngRestApiEndpoint());
            UserOnline::lock(Yii::$app->adminuser->id, $table, $id, 'lock_admin_edit_crud_item', ['table' => $alias['alias'], 'id' => $id, 'module' => $alias['module']['alias']]);
        }

        return $model;
    }
}
