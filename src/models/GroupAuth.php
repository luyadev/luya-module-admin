<?php

namespace luya\admin\models;

use yii\db\ActiveRecord;

/**
 * Group Auth Relation
 *
 * The relation between and {{luya\admin\models\Auth}} and {{luya\admin\models\Group}}.
 *
 * @property int|null $group_id
 * @property int|null $auth_id
 * @property int|null $crud_create
 * @property int|null $crud_update
 * @property int|null $crud_delete
 * @property Group $group
 * @property Auth $auth
 *
 * @since 3.3.0
 * @author Basil Suter <git@nadar.io>
 */
class GroupAuth extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'admin_group_auth';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['group_id', 'auth_id', 'crud_create', 'crud_update', 'crud_delete'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'group_id' => 'Group ID',
            'auth_id' => 'Auth ID',
            'crud_create' => 'Crud Create',
            'crud_update' => 'Crud Update',
            'crud_delete' => 'Crud Delete',
        ];
    }

    /**
     * Auth Relation.
     *
     * @return Auth
     */
    public function getAuth()
    {
        return $this->hasOne(Auth::class, ['id' => 'auth_id']);
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
}
