<?php

namespace luya\admin\behaviors;

use luya\admin\models\NgrestLog;
use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\helpers\Json;
use yii\web\Application;

/**
 * LogBehavior stores informations when active records are updated, inserted or deleted.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class LogBehavior extends Behavior
{
    public $route = '';

    public $api = '';

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'eventAfterInsert',
            ActiveRecord::EVENT_AFTER_UPDATE => 'eventAfterUpdate',
            ActiveRecord::EVENT_AFTER_DELETE => 'eventAfterDelete',
        ];
    }

    /**
     * The value to transform to json.
     *
     * @param string|array $array
     * @return string
     */
    private function toJson($array)
    {
        $array = (array) $array;

        return Json::encode($array);
    }

    /**
     * Returns the user id for the current admin user if logged in and component is existsi.
     *
     * @return integer
     * @since 1.2.3
     */
    protected function getUserId()
    {
        if (Yii::$app->has('adminuser') && Yii::$app->adminuser->getIdentity()) {
            return Yii::$app->adminuser->id;
        }

        return 0;
    }

    /**
     * Method to ensure whether the current log process should be run or not as log behavior can also be attached
     * the very universal models.
     *
     * @return boolean
     * @since 1.2.3
     */
    protected function isLoggable()
    {
        if (Yii::$app instanceof Application && Yii::$app->hasModule('admin') && Yii::$app->has('adminuser')) {
            return true;
        }

        return false;
    }

    /**
     * After delete event.
     *
     * @param \yii\base\Event $event
     */
    public function eventAfterDelete($event)
    {
        if ($this->isLoggable()) {
            $model = new NgrestLog();
            $model->attributes = [
                'user_id' => $this->getUserId(),
                'timestamp_create' => time(),
                'route' => $this->route,
                'api' => $this->api,
                'is_insert' => false,
                'is_update' => false,
                'is_delete' => true,
                'attributes_json' => $this->toJson($event->sender->getAttributes()),
                'table_name' => $event->sender->tableName(),
                'pk_value' => implode("-", $event->sender->getPrimaryKey(true)),
            ];
            $model->save();
        }
    }

    /**
     * After insert event.
     *
     * @param \yii\db\AfterSaveEvent $event
     */
    public function eventAfterInsert($event)
    {
        if ($this->isLoggable()) {
            $model = new NgrestLog();
            $model->attributes = [
                'user_id' => $this->getUserId(),
                'timestamp_create' => time(),
                'route' => $this->route,
                'api' => $this->api,
                'is_insert' => true,
                'is_update' => false,
                'attributes_json' => $this->toJson($event->sender->getAttributes()),
                'attributes_diff_json' => null,
                'table_name' => $event->sender->tableName(),
                'pk_value' => implode("-", $event->sender->getPrimaryKey(true)),
            ];
            $model->save();
        }
    }

    /**
     * After Update.
     *
     * @param \yii\db\AfterSaveEvent $event
     */
    public function eventAfterUpdate($event)
    {
        if ($this->isLoggable()) {
            $model = new NgrestLog();
            $model->attributes = [
                'user_id' => $this->getUserId(),
                'timestamp_create' => time(),
                'route' => $this->route,
                'api' => $this->api,
                'is_insert' => false,
                'is_update' => true,
                'attributes_json' => $this->toJson($event->sender->getAttributes()),
                'attributes_diff_json' => $this->toJson($event->changedAttributes),
                'table_name' => $event->sender->tableName(),
                'pk_value' => implode("-", $event->sender->getPrimaryKey(true)),
            ];
            $model->save();
        }
    }
}
