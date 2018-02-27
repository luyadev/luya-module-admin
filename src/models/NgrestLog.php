<?php

namespace luya\admin\models;

use Yii;
use luya\helpers\Json;

/**
 * This is the model class for table "admin_ngrest_log".
 *
 * @property int $id
 * @property int $user_id
 * @property int $timestamp_create
 * @property string $route
 * @property string $api
 * @property int $is_update
 * @property int $is_insert
 * @property int $is_delete
 * @property string $attributes_json
 * @property string $attributes_diff_json
 * @property string $pk_value
 * @property string $table_name
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.1.0
 */
class NgrestLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'admin_ngrest_log';
    }
    
    /**
     * @inheritdoc
     */
    public static function find()
    {
        return parent::find()->orderBy(['timestamp_create' => SORT_DESC]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'timestamp_create', 'route', 'api', 'attributes_json'], 'required'],
            [['user_id', 'timestamp_create', 'is_update', 'is_insert', 'is_delete'], 'integer'],
            [['attributes_json', 'attributes_diff_json'], 'string'],
            [['route', 'api'], 'string', 'max' => 80],
            [['pk_value', 'table_name'], 'string', 'max' => 255],
        ];
    }
    
    /**
     * Get attributes json as array.
     *
     * @return array
     */
    public function getAttributesJsonArray()
    {
        return $this->convertValueToJson($this->attributes_json);
    }
    
    /**
     * Get attributes json diff as array.
     *
     * @return array
     */
    public function getAttributesJsonDiffArray()
    {
        return $this->convertValueToJson($this->attributes_diff_json);
    }
    
    /**
     * Get a given attribute by its name from the attributes json diff array.
     *
     * @param string $attribute The attribute to check inside the array.
     * @return mixed
     */
    public function getAttributeFromJsonDiffArray($attribute)
    {
        return isset($this->getAttributesJsonDiffArray()[$attribute]) ? $this->getAttributesJsonDiffArray()[$attribute] : '';
    }
    
    /**
     * Convert a given value into an array.
     *
     * @param string $value
     * @return array
     */
    protected function convertValueToJson($value)
    {
        if (Json::isJson($value)) {
            return Json::decode($value);
        }
        
        return [];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'timestamp_create' => 'Timestamp Create',
            'route' => 'Route',
            'api' => 'Api',
            'is_update' => 'Is Update',
            'is_insert' => 'Is Insert',
            'is_delete' => 'Is Delete',
            'attributes_json' => 'Attributes Json',
            'attributes_diff_json' => 'Attributes Diff Json',
            'pk_value' => 'Pk Value',
            'table_name' => 'Table Name',
        ];
    }
}
