<?php

namespace luya\admin\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "admin_user_login".
 *
 * @property int $id
 * @property int $user_id
 * @property int $timestamp_create
 * @property string $auth_token
 * @property string $ip
 * @property int|null $is_destroyed
 * @property string|null $user_agent {@since 3.0.0}
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
final class UserLogin extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->on(self::EVENT_BEFORE_VALIDATE, function ($event) {
            if ($event->sender->isNewRecord) {
                $this->timestamp_create = time();
                $this->ip = Yii::$app->request->userIP;
            }
        });
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admin_user_login}}';
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        return parent::find()->orderBy(['timestamp_create' => SORT_DESC]);
    }

    /**
     * Get user relation.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'timestamp_create', 'auth_token', 'ip'], 'required'],
            [['user_id', 'timestamp_create'], 'integer'],
            [['is_destroyed'], 'boolean'],
            [['auth_token'], 'string', 'max' => 120],
            [['ip'], 'string', 'max' => 45],
            [['user_agent'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'timestamp_create' => 'Timestamp Create',
            'auth_token' => 'Auth Token',
            'ip' => 'Ip',
            'is_destroyed' => 'Is Destroyed',
            'user_agen' => 'User Agent',
        ];
    }
}
