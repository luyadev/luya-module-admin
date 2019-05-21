<?php

namespace luya\admin\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%admin_user_auth_notification}}".
 *
 * @property int $id
 * @property int $user_id
 * @property int $auth_id
 * @property int $is_muted
 * @property int $latest_pk_value
 * @property int $created_at
 * @property int $updated_at
 */
class UserAuthNotification extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%admin_user_auth_notification}}';
    }

    public function behaviors()
    {
        return [
            ['class' => TimestampBehavior::class],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'auth_id'], 'required'],
            [['user_id', 'auth_id', 'is_muted', 'created_at', 'updated_at'], 'integer'],
            [['latest_pk_value'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'auth_id' => 'Auth ID',
            'is_muted' => 'Is Muted',
            'latest_pk_value' => 'New Rows Count',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
