<?php

namespace luya\admin\models;

use Yii;
use luya\admin\jobs\ScheduleJob;
use luya\helpers\StringHelper;
use luya\admin\ngrest\base\NgRestModelInterface;
use luya\Exception;

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
        $model = $class::ngRestFind()->select(array_merge($class::primaryKey(), [$this->target_attribute_name]))->byPrimaryKey($this->primary_key)->one();

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
     * Ensure if the given class is an ngrest model and permission exists.
     *
     * @param string $class
     * @return boolean
     */
    public function hasTriggerPermission($class)
    {
        $model = new $class();

        if (!$model instanceof NgRestModelInterface) {
            return false;
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

        Yii::$app->adminqueue->delay($delay)->push(new ScheduleJob(['schedulerId' => $this->id]));
    }
}
