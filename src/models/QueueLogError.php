<?php

namespace luya\admin\models;

use luya\admin\aws\DetailViewActiveWindow;
use luya\admin\ngrest\base\NgRestModel;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * Queue Log Error.
 *
 * File has been created with `crud/create` command.
 *
 * @property integer $id
 * @property integer $queue_log_id
 * @property text $message
 * @property string $code
 * @property text $trace
 * @property text $file
 * @property string $line
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @since 3.0.0
 * @author Basil Suter <git@nadar.io>s
 */
class QueueLogError extends NgRestModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admin_queue_log_error}}';
    }

    /**
     * @inheritdoc
     */
    public static function ngRestApiEndpoint()
    {
        return 'api-admin-queuelogerror';
    }

    /**
     * {@inheritDoc}
     */
    public function behaviors()
    {
        return [
            ['class' => TimestampBehavior::class],
        ];
    }

    /**
     * @inheritdoc
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

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function ngRestAttributeTypes()
    {
        return [
            'queue_log_id' => 'number',
            'message' => 'textarea',
            'code' => 'text',
            'trace' => 'textarea',
            'file' => 'textarea',
            'line' => 'text',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * @inheritdoc
     */
    public function ngRestScopes()
    {
        return [
            ['list', ['code', 'message', 'created_at']],
            ['delete', true],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function ngRestActiveWindows()
    {
        return [
            ['class' => DetailViewActiveWindow::class],
        ];
    }

    /**
     * Get Queue Log Relation
     *
     * @return QueueLogError
     */
    public function getLog()
    {
        return $this->hasOne(QueueLogError::class, ['id' => 'queue_log_id']);
    }
}
