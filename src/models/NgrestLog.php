<?php

namespace luya\admin\models;

use Yii;
use luya\admin\ngrest\base\NgRestModel;

/**
 * Ngrest Log.
 * 
 * File has been created with `crud/create` command. 
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
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'timestamp_create' => Yii::t('app', 'Timestamp Create'),
            'route' => Yii::t('app', 'Route'),
            'api' => Yii::t('app', 'Api'),
            'is_update' => Yii::t('app', 'Is Update'),
            'is_insert' => Yii::t('app', 'Is Insert'),
            'attributes_json' => Yii::t('app', 'Attributes Json'),
            'attributes_diff_json' => Yii::t('app', 'Attributes Diff Json'),
            'pk_value' => Yii::t('app', 'Pk Value'),
            'table_name' => Yii::t('app', 'Table Name'),
            'is_delete' => Yii::t('app', 'Is Delete'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'timestamp_create', 'route', 'api', 'attributes_json'], 'required'],
            [['user_id', 'timestamp_create', 'is_update', 'is_insert', 'is_delete'], 'integer'],
            [['attributes_json', 'attributes_diff_json'], 'string'],
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
            'user_id' => 'number',
            'timestamp_create' => 'number',
            'route' => 'text',
            'api' => 'text',
            'is_update' => 'number',
            'is_insert' => 'number',
            'attributes_json' => 'textarea',
            'attributes_diff_json' => 'textarea',
            'pk_value' => 'text',
            'table_name' => 'text',
            'is_delete' => 'number',
        ];
    }

    /**
     * @inheritdoc
     */
    public function ngRestScopes()
    {
        return [
            ['list', ['user_id', 'timestamp_create', 'route', 'api', 'is_update', 'is_insert', 'attributes_json', 'attributes_diff_json', 'pk_value', 'table_name', 'is_delete']],
            [['create', 'update'], ['user_id', 'timestamp_create', 'route', 'api', 'is_update', 'is_insert', 'attributes_json', 'attributes_diff_json', 'pk_value', 'table_name', 'is_delete']],
            ['delete', false],
        ];
    }
}