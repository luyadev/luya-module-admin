<?php

namespace luya\admin\apis;

use luya\admin\base\RestController;
use luya\admin\models\Config;
use luya\admin\models\Lang;
use luya\admin\models\Property;
use luya\admin\models\Scheduler;
use luya\admin\models\Tag;
use luya\admin\models\UserLogin;
use luya\admin\Module;
use luya\traits\CacheableTrait;
use Yii;
use yii\base\InvalidCallException;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

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
     * @event Event A global event which is triggered after the admin flush cache (reload) button is clicked.
     * @since 2.0.0
     */
    public const EVENT_FLUSH_CACHE = 'flushCache';

    /**
     * Call Reload Button
     *
     * Run the callback function of a reload button configure in {{luya\admin\Module::$reloadButtons}}
     *
     * @param integer|string $key The array key from reload buttons array
     * @return array Returns an array with response, button and message
     * @since 2.0.0
     */
    public function actionReloadButtonCall($key)
    {
        $button = array_key_exists($key, $this->module->reloadButtons) ? $this->module->reloadButtons[$key] : false;

        if (!$button) {
            throw new NotFoundHttpException("Unable to find the given reload button.");
        }

        $response = call_user_func($button->callback, $button);

        return [
            'response' => $response,
            'button' => $button,
            'message' => $button->response ?: Module::t('admin_button_execute', ['label' => $button->originalLabel]),
        ];
    }

    /**
     * Check the status of a queue job
     *
     * @param integer $jobId The queue job it from the push() method
     * @return array An array with `is_waiting`, `is_reserved` and `is_done` information.
     */
    public function actionQueueJob($jobId)
    {
        return [
            'is_waiting' => Yii::$app->adminqueue->isWaiting($jobId),
            'is_reserved' => Yii::$app->adminqueue->isReserved($jobId),
            'is_done' => Yii::$app->adminqueue->isDone($jobId),
        ];
    }

    /**
     * Get all log entries for a given scheulder model with primary key.
     *
     * @param string $model The namespace to the model
     * @param integer $pk The primary key
     * @return ActiveDataProvider
     * @since 2.0.0
     */
    public function actionSchedulerLog($model, $pk, $target)
    {
        return new ActiveDataProvider([
            'query' => Scheduler::find()->where(['model_class' => $model, 'primary_key' => $pk, 'target_attribute_name' => $target]),
            'sort' => ['defaultOrder' => ['schedule_timestamp' => SORT_ASC]],
            'pagination' => false,
        ]);
    }

    /**
     * Add a task to the scheduler.
     *
     * @return array
     * @uses Scheduler
     * @since 2.0.0
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
            Config::set(Config::CONFIG_QUEUE_TIMESTAMP, time());
            return $model;
        }

        return $this->sendModelError($model);
    }

    /**
     * Remove a job for a given ID.
     *
     * @return boolean
     * @since 2.0.0
     */
    public function actionSchedulerDelete($id)
    {
        $job = Scheduler::findOne($id);

        if ($job) {
            return $job->delete();
        }

        return false;
    }

    /**
     * Get all available tags.
     *
     * This response differs to the admin-api-tag as returns all tags without pagination.
     *
     * @return array
     * @since 2.0.0
     */
    public function actionTags()
    {
        return Tag::find()->select(['id', 'name', 'translation'])->orderBy(['name' => SORT_ASC])->all();
    }

    /**
     * Toggle the state of a given tag for a certain relation.
     *
     * @param integer $tagId
     * @param integer $pkId
     * @param string $tableName
     * @return boolean
     * @since 2.2.1
     */
    public function actionTagRelationToggle($tagId, $pkId, $tableName)
    {
        $tag = Tag::findOne($tagId);

        if (!$tag) {
            throw new InvalidCallException("The given tag id does not exists.");
        }

        return $tag->toggleRelation($pkId, $tableName);
    }

    /**
     * Set the lastest ngrest filter selection in the User Settings.
     *
     * @uses string apiEndpoint
     * @uses string filterName
     * @return boolean
     */
    public function actionNgrestFilter()
    {
        $apiEndpoint = Yii::$app->request->getBodyParam('apiEndpoint');
        $filterName = Yii::$app->request->getBodyParam('filterName');

        return Yii::$app->adminuser->identity->setting->set('ngrestfilter.'.$apiEndpoint, $filterName);
    }

    /**
     * Set the lastest ngrest CRUD list order direction in the User Settings.
     *
     * @uses string apiEndpoint
     * @uses string sort
     * @uses string field
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
        return Lang::find()->orderBy(['is_default' => SORT_DESC])->all();
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
                'help' => $object->help(),
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
        if (function_exists('opcache_reset')) {
            @opcache_reset();
        }

        $this->flushHasCache();

        $user = Yii::$app->adminuser->identity;
        $user->updateAttributes(['force_reload' => false]);

        Yii::$app->trigger(self::EVENT_FLUSH_CACHE);

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
     * @uses string folderId
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
     * @uses array data
     */
    public function actionFilemanagerFoldertreeHistory()
    {
        $this->deleteHasCache('storageApiDataFolders');

        $data = Yii::$app->request->getBodyParam('data');
        Yii::$app->adminuser->identity->setting->set('foldertree.'.$data['id'], (int) $data['toggle_open']);

        return Yii::$app->response->statusCode = 204;
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
