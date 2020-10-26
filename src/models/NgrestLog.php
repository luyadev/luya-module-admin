<?php

namespace luya\admin\models;

use luya\admin\aws\DetailViewActiveWindow;
use Yii;
use luya\admin\ngrest\base\NgRestModel;
use luya\admin\ngrest\plugins\SelectRelationActiveQuery;
use luya\behaviors\JsonBehavior;

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
        $oldValue = isset($this->attributes_diff_json[$attribute]) ? $this->attributes_diff_json[$attribute] : null;
        $newValue = isset($this->attributes_json[$attribute]) ? $this->attributes_json[$attribute] : null;

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
            ['class' => DetailViewActiveWindow::class],
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
}
