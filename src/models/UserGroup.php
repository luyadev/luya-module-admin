<?php

namespace luya\admin\models;

use yii\db\ActiveRecord;

/**
 * User Group Relation
 *
 * The relation between a {{luya\admin\models\User}} and a {{luya\admin\models\Group}}.
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $group_id
 * @property Group $group
 * @property User $user
 *
 * @since 3.3.0
 * @author Basil Suter <git@nadar.io>
 */
class UserGroup extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'admin_user_group';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'group_id'], 'required'],
            [['user_id', 'group_id'], 'integer'],
            [['user_id', 'group_id'], 'unique', 'targetAttribute' => ['user_id', 'group_id']]
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
            'group_id' => 'Group ID',
        ];
    }

    /**
     * Group Relation.
     *
     * @return Group
     */
    public function getGroup()
    {
        return $this->hasOne(Group::class, ['id' => 'group_id']);
    }

    /**
     * User Relation.
     *
     * @return User
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
