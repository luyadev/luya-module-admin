<?php

namespace luya\admin\apis;

use Yii;
use luya\traits\CacheableTrait;
use luya\admin\models\Property;
use luya\admin\models\Lang;
use luya\admin\models\Tag;
use luya\admin\base\RestController;
use luya\admin\models\UserLogin;
use luya\admin\models\Scheduler;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;

/**
 * Common Admin API Tasks.
 *
 * Delivers default values for the specifing table. It means it does not return a key numeric array,
 * it does only return 1 assoc array wich reperents the default row.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class CommonController extends RestController
{
    use CacheableTrait;
    
    /**
     * Get all log entries for a given scheulder model with primary key.
     *
     * @param [type] $model
     * @param [type] $pk
     * @return void
     * @since 1.3.0
     */
    public function actionSchedulerLog($model, $pk)
    {
        return new ActiveDataProvider([
            'query' => Scheduler::find()->where(['model_class' => $model, 'primary_key' => $pk]),
            'sort'=> ['defaultOrder' => ['schedule_timestamp' => SORT_DESC]]
        ]);
    }

    /**
     * Add a task to the scheduler.
     *
     * @return void
     */
    public function actionSchedulerAdd()
    {
        $model = new Scheduler();
        $model->attributes = Yii::$app->request->bodyParams;

        if (!$model->hasTriggerPermission($model->model_class)) {
            throw new ForbiddenHttpException("Unable to schedule a task for the given model.");
        }

        if ($model->save()) {
            $model->pushQueue();

            // if its a "now" job, run the internal worker now so the log table is refreshed immediately
            Yii::$app->adminqueue->run(false);

            return $model;
        }

        return $this->sendModelError($model);
    }

    /**
     * Get all available tags.
     *
     * This response differs to the admin-api-tag as returns all tags without pagination.
     *
     * @return array
     * @since 1.3.0
     */
    public function actionTags()
    {
        return Tag::find()->select(['id', 'name', 'translation'])->orderBy(['name' => SORT_ASC])->all();
    }

    /**
     * Set the lastest ngrest filter selection in the User Settings.
     *
     * @return boolean
     */
    public function actionNgrestFilter()
    {
        $apiEndpoint = Yii::$app->request->getBodyParam('apiEndpoint');
        $filterName = Yii::$app->request->getBodyParam('filterName');
        
        return Yii::$app->adminuser->identity->setting->set('ngrestfilter.'.$apiEndpoint, $filterName);
    }
    
    /**
     * Set the lastest ngrest curd list order direction in the User Settings.
     *
     * @return boolean
     */
    public function actionNgrestOrder()
    {
        $apiEndpoint = Yii::$app->request->getBodyParam('apiEndpoint');
        $sort = Yii::$app->request->getBodyParam('sort');
        $field = Yii::$app->request->getBodyParam('field');
        
        if ($sort == '-') {
            $sort = SORT_DESC;
        } else {
            $sort = SORT_ASC;
        }
        
        return Yii::$app->adminuser->identity->setting->set('ngrestorder.'.$apiEndpoint, ['sort' => $sort, 'field' => $field]);
    }
    
    /**
     * Get all available languages from the database as array.
     *
     * @return array The available languages.
     */
    public function actionDataLanguages()
    {
        return Lang::find()->asArray()->all();
    }
    
    /**
     * Get all available administration regisetered properties.
     *
     * @return array Get all properties.
     */
    public function actionDataProperties()
    {
        $data = [];
        foreach (Property::find()->all() as $item) {
            $object = Property::getObject($item->class_name);
            $data[] = [
                'id' => $item->id,
                'var_name' => $object->varName(),
                'option_json' => $object->options(),
                'label' => $object->label(),
                'type' => $object->type(),
                'default_value' => $object->defaultValue(),
                'i18n' => $object->i18n,
            ];
        }
        
        return $data;
    }
    
    /**
     * Triggerable action to flush the application cache and force user reload.
     *
     * @return boolean
     */
    public function actionCache()
    {
        $this->flushHasCache();
    
        $user = Yii::$app->adminuser->identity;
        $user->updateAttributes(['force_reload' => false]);
    
        return true;
    }
    
    /**
     * Get a list with all frontend modules, which is used in several dropdowns in the admin ui.
     *
     * @return array An array with all frontend modules.
     */
    public function actionDataModules()
    {
        $data = [];
        foreach (Yii::$app->getFrontendModules() as $k => $f) {
            $data[] = ['value' => $k, 'label' => $k];
        }
        return $data;
    }

    /**
     * Save the last selected filemanager folder in the user settings.
     *
     * @return boolean
     */
    public function actionSaveFilemanagerFolderState()
    {
        $folderId = Yii::$app->request->getBodyParam('folderId');
        
        if ($folderId) {
            return Yii::$app->adminuser->identity->setting->set('filemanagerFolderId', $folderId);
        } else {
            return Yii::$app->adminuser->identity->setting->remove('filemanagerFolderId');
        }
    }
    
    /**
     * Return the latest selected filemanager from the user settings.
     *
     * @return integer The folder id.
     */
    public function actionGetFilemanagerFolderState()
    {
        return Yii::$app->adminuser->identity->setting->get('filemanagerFolderId', 0);
    }

    /**
     * Store the open and closed folders from the filemanager tree in the user settings.
     *
     * @return boolean
     */
    public function actionFilemanagerFoldertreeHistory()
    {
        $this->deleteHasCache('storageApiDataFolders');

        $data = Yii::$app->request->getBodyParam('data');
        Yii::$app->adminuser->identity->setting->set('foldertree.'.$data['id'], (int) $data['toggle_open']);
    }

    /**
     * Last User logins
     * @return array|\yii\db\ActiveRecord[]
     */
    public function actionLastLogins()
    {
        return UserLogin::find()->select(['user_id', 'max(timestamp_create) as maxdate'])->joinWith(['user' => function ($q) {
            $q->select(['id', 'firstname', 'lastname']);
        }])->limit(10)->groupBy(['user_id'])->orderBy(['maxdate' => SORT_DESC])->asArray()->all();
    }
}
