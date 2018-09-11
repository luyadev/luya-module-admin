<?php

namespace luya\admin\ngrest\base\actions;

use Yii;
use luya\admin\models\UserOnline;

/**
 * IndexAction for REST implementation.
 *
 * In order to enable or disable the pagination for index actions regulatet by the ActiveController
 * the main yii\rest\IndexAction is overriten by adding the pagination propertie to the action
 * provided from the luya\rest\ActiveController.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class ViewAction extends \yii\rest\ViewAction
{
    public function run($id)
    {
        $result = parent::run($id);
        
        // auto expand the given relations
        // @TODO 11.9.2018: Fields should be resolved trough expand param of get request. The yii serializer should then expand those given fields instead of auto expand those
        // trough $relations
        // As this can make problems with relations like `items.news` (sub relations) the expand wont work.
        // a. either check to remove sub relations 
        // b. or remove this implementation
        /*
        $relations = $this->controller->getWithRelation('view');
        foreach ($relations as $relationAttribute) {
            $result->{$relationAttribute};
        }
        */
        
        if (!Yii::$app->adminuser->identity->is_api_user) {
            $modelClass = $this->modelClass;
            $table = $modelClass::tableName();
            $alias = Yii::$app->adminmenu->getApiDetail($modelClass::ngRestApiEndpoint());
            UserOnline::lock(Yii::$app->adminuser->id, $table, $id, 'lock_admin_edit_crud_item', ['table' => $alias['alias'], 'id' => $id, 'module' => $alias['module']['alias']]);
        }
        
        return $result;
    }
}
