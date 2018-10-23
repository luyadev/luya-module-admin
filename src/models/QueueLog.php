<?php

namespace luya\admin\models;

use Yii;

/**
 * This is the model class for table "admin_queue_log".
 *
 * @property int $id
 * @property int $queue_id
 * @property string $title
 * @property int $push_timestamp
 * @property int $run_timestamp
 * @property int $end_timestamp
 * @property int $is_error
 */
class QueueLog extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'admin_queue_log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['queue_id', 'push_timestamp'], 'required'],
            [['queue_id', 'push_timestamp', 'run_timestamp', 'end_timestamp', 'is_error'], 'integer'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'queue_id' => 'Queue ID',
            'title' => 'Title',
            'push_timestamp' => 'Push Timestamp',
            'run_timestamp' => 'Run Timestamp',
            'end_timestamp' => 'End Timestamp',
            'is_error' => 'Is Error',
        ];
    }
}