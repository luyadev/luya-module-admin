<?php

namespace luya\admin\models;

use luya\admin\Module;
use luya\admin\ngrest\base\NgRestModel;
use yii\db\Expression;

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
            'queue_id' => Module::t('model_queuelog_queue_id_label'),
            'title' => Module::t('model_queuelog_title_label'),
            'push_timestamp' => Module::t('model_queuelog_push_timestamp_label'),
            'run_timestamp' => Module::t('model_queuelog_run_timestamp_label'),
            'end_timestamp' => Module::t('model_queuelog_end_timestamp_label'),
            'is_error' => Module::t('model_queuelog_is_error_label'),
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
     * {@inheritDoc}
     */
    public function ngRestFilters()
    {
        return [
            'Upcoming' => self::find()->where(['is', 'run_timestamp', new Expression('null')]),
            'Processed' => self::find()->where(['is not', 'run_timestamp', new Expression('null')]),
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
