<?php

namespace luya\admin\models;

use Yii;
use luya\admin\ngrest\base\NgRestModel;
use luya\admin\traits\SoftDeleteTrait;
use luya\admin\traits\SortableTrait;
use yii\behaviors\TimestampBehavior;

/**
 * Admin Property
 *
 * Base classes for CMS properties which are set by import process.
 *
 * @property integer $id
 * @property string $module_name
 * @property string $var_name
 * @property string $class_name
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer|boolean $is_deleted
 * @property integer $sort_index
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class Property extends NgRestModel
{
    use SortableTrait;
    use SoftDeleteTrait;

    /**
     * The field which should by used to sort.
     *
     * @return string
     */
    public static function sortableField()
    {
        return 'sort_index';
    }

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
     * {@inheritDoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
            ]
        ];
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
            [['created_at', 'updated_at', 'sort_index'], 'integer'],
            [['is_deleted'], 'boolean'],
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
            'sort_index' => 'sortable',
        ];
    }

    /**
     * @inheritdoc
     */
    public function ngRestScopes()
    {
        return [
            ['list', ['module_name', 'var_name', 'class_name', 'sortable']],
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
