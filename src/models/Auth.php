<?php

namespace luya\admin\models;

/**
 * This is the model class for table "admin_auth".
 *
 * @property int $id
 * @property string $alias_name
 * @property string $module_name
 * @property int $is_crud
 * @property string $route
 * @property string $api
 * @property string $pool
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 2.0.0
 */
class Auth extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%admin_auth}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['alias_name', 'module_name'], 'required'],
            [['is_crud'], 'integer'],
            [['alias_name', 'module_name'], 'string', 'max' => 60],
            [['route', 'api'], 'string', 'max' => 200],
            [['pool'], 'string', 'max' => 255, 'skipOnEmpty' => true],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'alias_name' => 'Alias Name',
            'module_name' => 'Module Name',
            'is_crud' => 'Is Crud',
            'route' => 'Route',
            'api' => 'Api',
            'pool' => 'Pool',
        ];
    }
}
