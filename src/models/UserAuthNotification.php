<?php

namespace luya\admin\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use luya\helpers\Json;

/**
 * This is the model class for table "{{%admin_user_auth_notification}}".
 *
 * @property int $id
 * @property int $user_id
 * @property int $auth_id
 * @property int $is_muted
 * @property string $model_latest_pk_value
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
            [['user_id', 'auth_id', 'is_muted', 'created_at', 'updated_at'], 'integer'],
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
        
        $nowValue = array_sum($className::findLatestPrimaryKeyValue());

        $oldValue = array_sum(Json::decode($this->model_latest_pk_value));

        return $nowValue - $oldValue;
    }
}
