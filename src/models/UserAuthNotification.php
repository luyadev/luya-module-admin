<?php

namespace luya\admin\models;

use luya\helpers\Json;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * User Notifications by Auth Item
 *
 * @property int $id
 * @property int $user_id
 * @property int $auth_id
 * @property int $is_muted
 * @property string $model_latest_pk_value An array with primary key values stored as json, f.e. `["1"]`
 * @property string $model_class
 * @property int $created_at
 * @property int $updated_at
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 2.0.0
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
            [['user_id', 'auth_id', 'created_at', 'updated_at'], 'integer'],
            [['is_muted'], 'boolean'],
            [['model_latest_pk_value', 'model_class'], 'string'],
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
            'model_latest_pk_value' => 'New Rows Count',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Get auth
     *
     * @return Auth
     */
    public function getAuth()
    {
        return $this->hasOne(Auth::class, ['id' => 'auth_id']);
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Get the diff count between primary key in table and current value.
     *
     * @return integer
     */
    public function getDiffCount()
    {
        $className = $this->model_class;

        // if a module has been removed, the class might not exists anymore
        // @see https://github.com/luyadev/luya-module-admin/issues/520
        if (!class_exists($className)) {
            return 0;
        }

        $nowValue = array_sum($className::findLatestPrimaryKeyValue());
        $oldValue = array_sum(Json::decode($this->model_latest_pk_value));

        $count = $nowValue - $oldValue;

        // ensure negative values are handled as 0
        return $count < 0 ? 0 : $count;
    }
}
