<?php

namespace luya\admin\models;

use luya\admin\jobs\ScheduleJob;
use luya\admin\ngrest\base\NgRestModelInterface;
use luya\Exception;
use luya\helpers\StringHelper;
use Yii;

/**
 * This is the model class for table "admin_scheduler".
 *
 * @property int $id
 * @property string $model_class
 * @property string $primary_key
 * @property string $target_attribute_name
 * @property string $new_attribute_value
 * @property string $old_attribute_value
 * @property int $schedule_timestamp
 * @property int $is_done
 */
class Scheduler extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%admin_scheduler}}';
    }

    public function init()
    {
        parent::init();
        $this->on(self::EVENT_AFTER_DELETE, function () {
            $queueId = Config::find()->where(['name' => "queueScheduler.{$this->id}", 'is_system' => true])->select(['value'])->scalar();

            if (!empty($queueId)) {
                Yii::$app->adminqueue->remove($queueId);
                QueueLog::deleteAll(['queue_id' => $queueId]);
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['model_class', 'primary_key', 'target_attribute_name', 'new_attribute_value', 'schedule_timestamp'], 'required'],
            [['schedule_timestamp', 'is_done'], 'integer'],
            [['model_class', 'target_attribute_name'], 'string', 'max' => 255],
            [['old_attribute_value', 'new_attribute_value'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'model_class' => 'Model Class',
            'primary_key' => 'Primary Key',
            'target_attribute_name' => 'Target Attribute Name',
            'new_attribute_value' => 'New Attribute Value',
            'old_attribute_value' => 'Old Attribute Value',
            'schedule_timestamp' => 'Schedule Timestamp',
            'is_done' => 'Is Done',
        ];
    }

    /**
     * Job Trigger.
     *
     * This method is execute by the queue job.
     *
     * @return integer The number of affected and changed rows.
     * @throws Exception
     */
    public function triggerJob()
    {
        $class = $this->model_class;

        $model = new $class();

        if ($model instanceof NgRestModelInterface) {
            $find = $class::ngRestFind()->byPrimaryKey($this->primary_key);
        } else {
            $find = $class::find()->andWhere(['id' => $this->primary_key]);
        }

        $model = $find->select(array_merge($class::primaryKey(), [$this->target_attribute_name]))->one();

        if ($model) {
            $oldValue = $model->{$this->target_attribute_name};
            $model->{$this->target_attribute_name} = StringHelper::typeCast($this->new_attribute_value);

            if ($model->save(true, [$this->target_attribute_name])) {
                return $this->updateAttributes(['old_attribute_value' => $oldValue, 'is_done' => true]);
            }

            throw new Exception("The scheduler could not save the new value for model '{$this->model_class}' with primary key '{$this->primary_key}'.");
        }

        throw new Exception("The scheduler could not find model '{$this->model_class}' with primary key '{$this->primary_key}'.");
    }

    /**
     * Ensure if the given class is an ngrest model and permission exists. If its not
     * an ngrest model, there is no permission system and trigger permission is granted (@since 4.0)
     *
     * @param string $class
     * @return boolean
     */
    public function hasTriggerPermission($class)
    {
        $model = new $class();

        if (!$model instanceof NgRestModelInterface) {
            return true;
        }

        return Yii::$app->adminmenu->getApiDetail($class::ngRestApiEndpoint());
    }

    /**
     * Push the given scheduler model into the queue.
     *
     * @return void
     */
    public function pushQueue()
    {
        $delay = $this->schedule_timestamp - time();

        if ($delay < 1) {
            $delay = 0;
        }

        $queueId = Yii::$app->adminqueue->delay($delay)->push(new ScheduleJob(['schedulerId' => $this->id]));

        // until there is a migration, store informations in config:
        // see: https://github.com/luyadev/luya-module-admin/issues/655
        $config = new Config();
        $config->is_system = 1;
        $config->name = "queueScheduler.{$this->id}";
        $config->value = $queueId;
        $config->save();
    }
}
