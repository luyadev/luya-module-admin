<?php

namespace luya\admin\models;

use luya\admin\aws\DetailViewActiveWindow;
use luya\admin\Module;
use luya\admin\ngrest\base\NgRestModel;
use luya\admin\ngrest\plugins\SelectRelationActiveQuery;
use luya\behaviors\JsonBehavior;
use luya\helpers\ArrayHelper;
use yii\helpers\VarDumper;

/**
 * Ngrest Log.
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $timestamp_create
 * @property string $route
 * @property string $api
 * @property tinyint $is_update
 * @property tinyint $is_insert
 * @property text $attributes_json
 * @property text $attributes_diff_json
 * @property string $pk_value
 * @property string $table_name
 * @property tinyint $is_delete
 *
 * @author Basil Suter <git@nadar.io>
 * @since 3.2.0
 */
class NgrestLog extends NgRestModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admin_ngrest_log}}';
    }

    public function init()
    {
        parent::init();

        $this->detachBehavior('LogBehavior');
    }

    public function behaviors()
    {
        return [
            [
                'class' => JsonBehavior::class,
                'attributes' => ['attributes_json', 'attributes_diff_json'],
                'encodeBeforeValidate' => true,
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function ngRestApiEndpoint()
    {
        return 'api-admin-ngrestlog';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => Module::t('model_ngrestlog_user_id_label'),
            'timestamp_create' => Module::t('model_ngrestlog_timestamp_create_label'),
            'route' => Module::t('model_ngrestlog_route_label'),
            'api' => Module::t('model_ngrestlog_api_label'),
            'is_update' => Module::t('model_ngrestlog_is_update_label'),
            'is_insert' => Module::t('model_ngrestlog_is_insert_label'),
            'attributes_json' => Module::t('model_ngrestlog_attributes_json_label'),
            'attributes_diff_json' => Module::t('model_ngrestlog_attributes_diff_json_label'),
            'pk_value' => Module::t('model_ngrestlog_pk_value_label'),
            'table_name' => Module::t('model_ngrestlog_table_name_label'),
            'is_delete' => Module::t('model_ngrestlog_is_delete_label'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'timestamp_create', 'attributes_json'], 'required'],
            [['user_id', 'timestamp_create'], 'integer'],
            [['is_update', 'is_insert', 'is_delete'], 'boolean'],
            [['attributes_json', 'attributes_diff_json'], 'string', 'max' => 65535],
            [['route', 'api'], 'string', 'max' => 80],
            [['pk_value', 'table_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function ngRestAttributeTypes()
    {
        return [
            'user_id' => [
                'class' => SelectRelationActiveQuery::class,
                'query' => $this->getUser(),
                'relation' => 'user',
                'labelField' => 'firstname,lastname'
            ],
            'timestamp_create' => 'datetime',
            'route' => 'text',
            'api' => 'text',
            'is_update' => 'toggleStatus',
            'is_insert' => 'toggleStatus',
            'attributes_json' => 'raw',
            'attributes_diff_json' => 'raw',
            'pk_value' => 'text',
            'table_name' => 'text',
            'is_delete' => 'toggleStatus',
        ];
    }

    public function attributesAttributeDiff($attribute)
    {
        $oldValue = $this->attributes_diff_json[$attribute] ?? null;
        $newValue = $this->attributes_json[$attribute] ?? null;

        if ($oldValue == $newValue) {
            return false;
        }

        return $oldValue;
    }

    /**
     * @inheritdoc
     */
    public function ngRestScopes()
    {
        return [
            ['list', ['user_id', 'timestamp_create', 'route', 'api', 'pk_value', 'table_name']],
            ['delete', true],
        ];
    }

    /**
     * @inheritdoc
     */
    public function ngRestActiveWindows()
    {
        return [
            [
                'class' => DetailViewActiveWindow::class,
                'attributes' => [
                    [
                        'attribute' => 'user_id',
                        'value' => fn ($model) => $model->user->email
                    ],
                    'timestamp_create:datetime',
                    'route',
                    'api',
                    [
                        'attribute' => 'attributes_json',
                        'value' => fn ($model) => is_array($model->attributes_json) ? VarDumper::dumpAsString(ArrayHelper::coverSensitiveValues($model->attributes_json)) : $model->attributes_json
                    ],
                    [
                        'attribute' => 'attributes_diff_json',
                        'value' => fn ($model) => is_array($model->attributes_diff_json) ? VarDumper::dumpAsString(ArrayHelper::coverSensitiveValues($model->attributes_diff_json)) : $model->attributes_diff_json
                    ],
                    'table_name:raw',
                    'pk_value:raw',
                    'is_updated:boolean',
                    'is_insert:boolean',
                    'is_delete:boolean',
                ]
            ],
        ];
    }

    /**
     * Get User
     *
     * @return User
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * {@inheritDoc}
     */
    public function ngRestExport()
    {
        return [
            'timestamp_create' => 'datetime',
            'is_update' => 'boolean',
            'is_insert' => 'boolean',
            'is_delete' => 'boolean',
        ];
    }
}
