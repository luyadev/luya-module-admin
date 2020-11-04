<?php

namespace luya\admin\models;

use Yii;
use luya\admin\ngrest\base\NgRestModel;

/**
 * Admin Property
 *
 * Base classes for CMS properties which are set by import process.
 *
 * @property integer $id
 * @property string $module_name
 * @property string $var_name
 * @property string $class_name
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class Property extends NgRestModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admin_property}}';
    }

    /**
     * @inheritdoc
     */
    public static function ngRestApiEndpoint()
    {
        return 'api-admin-property';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'module_name' => 'Module Name',
            'var_name' => 'Var Name',
            'class_name' => 'Class Name',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['var_name', 'class_name'], 'required'],
            [['module_name'], 'string', 'max' => 120],
            [['var_name'], 'string', 'max' => 40],
            [['class_name'], 'string', 'max' => 200],
            [['var_name'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function ngRestAttributeTypes()
    {
        return [
            'module_name' => 'raw',
            'var_name' => 'raw',
            'class_name' => 'raw',
        ];
    }

    /**
     * @inheritdoc
     */
    public function ngRestScopes()
    {
        return [
            ['list', ['module_name', 'var_name', 'class_name']],
            ['delete', false],
        ];
    }
    
    /**
     * Create the property object with a given value.
     *
     * @param mixed $value
     * @return \luya\admin\base\Property
     */
    public function createObject($value)
    {
        return static::getObject($this->class_name, $value);
    }
    
    /**
     * Generate the Property Object.
     *
     * @param string $className
     * @param mixed $value
     * @return \luya\admin\base\Property
     */
    public static function getObject($className, $value = null)
    {
        return Yii::createObject(['class' => $className, 'value' => $value]);
    }
}
