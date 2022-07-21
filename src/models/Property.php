<?php

namespace luya\admin\models;

use luya\admin\Module;
use luya\admin\ngrest\base\NgRestModel;
use luya\admin\traits\SoftDeleteTrait;
use Yii;
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
    use SoftDeleteTrait;

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
    * Overrides the ngRestFind() method of the ActiveRecord
    * @return \yii\db\ActiveQuery
    */
    public static function ngRestFind()
    {
        return parent::ngRestFind()->orderBy(['sort_index' => SORT_ASC])->andWhere(['is_deleted' => false]);
    }

    /**
     * Overrides the find() method of the ActiveRecord
     * @return \yii\db\ActiveQuery
     */
    public static function find()
    {
        return parent::find()->orderBy(['sort_index' => SORT_ASC])->andWhere(['is_deleted' => false]);
    }

    /**
     * Disable the list ordering.
     *
     * @return boolean
     */
    public function ngRestListOrder()
    {
        return false;
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
            'module_name' => Module::t('model_property_module_name_label'),
            'var_name' => Module::t('model_property_var_name_label'),
            'class_name' => Module::t('model_property_class_name_label'),
            'sort_index' => Module::t('model_property_sort_index_label'),
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
            ['list', ['module_name', 'var_name', 'class_name', 'sort_index']],
            ['delete', true],
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
