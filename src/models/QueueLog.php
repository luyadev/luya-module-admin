<?php

namespace luya\admin\models;

use Yii;
use luya\admin\ngrest\base\NgRestModel;

/**
 * Queue Log.
 *
 * File has been created with `crud/create` command.
 *
 * @property integer $id
 * @property integer $queue_id
 * @property string $title
 * @property integer $push_timestamp
 * @property integer $run_timestamp
 * @property integer $end_timestamp
 * @property tinyint $is_error
 */
class QueueLog extends NgRestModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admin_queue_log}}';
    }

    /**
     * @inheritdoc
     */
    public static function ngRestApiEndpoint()
    {
        return 'api-admin-queuelog';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'queue_id' => Yii::t('app', 'Queue ID'),
            'title' => Yii::t('app', 'Title'),
            'push_timestamp' => Yii::t('app', 'Push Timestamp'),
            'run_timestamp' => Yii::t('app', 'Run Timestamp'),
            'end_timestamp' => Yii::t('app', 'End Timestamp'),
            'is_error' => Yii::t('app', 'Is Error'),
        ];
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function ngRestAttributeTypes()
    {
        return [
            'queue_id' => 'number',
            'title' => 'text',
            'push_timestamp' => 'datetime',
            'run_timestamp' => 'datetime',
            'end_timestamp' => 'datetime',
            'is_error' => ['toggleStatus', 'interactive' => false],
        ];
    }

    /**
     * @inheritdoc
     */
    public function ngRestScopes()
    {
        return [
            ['list', ['title', 'push_timestamp', 'run_timestamp', 'end_timestamp', 'is_error']],
            ['delete', true],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function ngRestRelations()
    {
        return [
            [
                'label' => 'Errors',
                'targetModel' => QueueLogError::class,
                'dataProvider' => $this->getLogErrors(),
            ],
        ];
    }
    
    /**
     * Get Queue Log Errors
     *
     * @return QueueLogError[]
     */
    public function getLogErrors()
    {
        return $this->hasMany(QueueLogError::class, ['queue_log_id' => 'id']);
    }
}
