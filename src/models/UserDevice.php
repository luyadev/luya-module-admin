<?php

namespace luya\admin\models;

use Yii;

/**
 * This is the model class for table "{{%admin_user_device}}".
 *
 * @property int $id
 * @property int $user_id
 * @property string $auth_key
 * @property string $user_agent
 * @property string $user_agent_checksum
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class AdminUserDevice extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%admin_user_device}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'auth_key', 'user_agent', 'user_agent_checksum'], 'required'],
            [['user_id', 'created_at', 'updated_at'], 'integer'],
            [['auth_key'], 'string', 'max' => 190],
            [['user_agent', 'user_agent_checksum'], 'string', 'max' => 255],
            [['auth_key'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'auth_key' => Yii::t('app', 'Auth Key'),
            'user_agent' => Yii::t('app', 'User Agent'),
            'user_agent_checksum' => Yii::t('app', 'User Agent Checksum'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
