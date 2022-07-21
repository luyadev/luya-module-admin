<?php

namespace luya\admin\models;

use luya\admin\ngrest\base\NgRestModel;
use Yii;

/**
 * User Request.
 *
 * File has been created with `crud/create` command.
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $timestamp
 * @property string $request_url
 * @property string $request_method
 * @property integer $response_time
 */
class UserRequest extends NgRestModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admin_user_request}}';
    }

    /**
     * @inheritdoc
     */
    public static function ngRestApiEndpoint()
    {
        return 'api-admin-userrequest';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'Admin User ID'),
            'timestamp' => Yii::t('app', 'Timestamp'),
            'request_url' => Yii::t('app', 'Request Url'),
            'request_method' => Yii::t('app', 'Request Type'),
            'response_time' => Yii::t('app', 'Response Time'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'timestamp', 'request_url', 'request_method', 'response_time'], 'required'],
            [['user_id', 'timestamp', 'response_time'], 'integer'],
            [['request_url', 'request_method'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function ngRestAttributeTypes()
    {
        return [
            'user_id' => 'number',
            'timestamp' => 'number',
            'request_url' => 'text',
            'request_method' => 'text',
            'response_time' => 'number',
        ];
    }

    /**
     * @inheritdoc
     */
    public function ngRestScopes()
    {
        return [
            ['list', ['user_id', 'timestamp', 'request_url', 'request_method', 'response_time']],
            [['create', 'update'], ['user_id', 'timestamp', 'request_url', 'request_method', 'response_time']],
            ['delete', false],
        ];
    }
}
