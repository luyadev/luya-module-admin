<?php

namespace luya\admin\models;

use Yii;
use luya\admin\jobs\ScheduleJob;

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
        return 'admin_scheduler';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['model_class', 'primary_key', 'target_attribute_name', 'new_attribute_value', 'schedule_timestamp'], 'required'],
            [['schedule_timestamp', 'is_done'], 'integer'],
            [['model_class', 'target_attribute_name', 'new_attribute_value'], 'string', 'max' => 255],
            [['old_attribute_value'], 'safe'],
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

    public function triggerJob()
    {
        try {
            $class = $this->model_class;
            $model = $class::findOne($this->primary_key);

            if ($model) {
                $oldValue = $model->{$this->target_attribute_name};
                $model->{$this->target_attribute_name} = $this->new_attribute_value;
                $model->save(true, [$this->target_attribute_name]);

                return $this->updateAttributes(['old_attribute_value' => $oldValue, 'is_done' => true]);
            }
        } catch (\Exception $err) {
            $this->delete();
        }
    }

    public function pushQueue()
    {
        $delay = $this->schedule_timestamp - time();

        if ($delay < 1) {
            $delay = 1;
        }

        Yii::$app->adminqueue->delay($delay)->push(new ScheduleJob(['schedulerId' => $this->id]));
    }    
}