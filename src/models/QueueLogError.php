<?php

namespace luya\admin\models;

use Yii;

/**
 * This is the model class for table "{{%admin_queue_log_error}}".
 *
 * @property int $id
 * @property int $queue_log_id
 * @property string|null $message
 * @property string|null $code
 * @property string|null $trace
 * @property string|null $file
 * @property string|null $line
 * @property int|null $created_at
 * @property int|null $updated_at
 * 
 * @since 3.0.0
 * @author Basil <git@nadar.io>
 */
class QueueLogError extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%admin_queue_log_error}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['queue_log_id'], 'required'],
            [['queue_log_id', 'created_at', 'updated_at'], 'integer'],
            [['message', 'trace', 'file'], 'string'],
            [['code', 'line'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'queue_log_id' => Yii::t('app', 'Queue Log ID'),
            'message' => Yii::t('app', 'Message'),
            'code' => Yii::t('app', 'Code'),
            'trace' => Yii::t('app', 'Trace'),
            'file' => Yii::t('app', 'File'),
            'line' => Yii::t('app', 'Line'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
