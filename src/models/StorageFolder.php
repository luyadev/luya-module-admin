<?php

namespace luya\admin\models;

use yii\db\ActiveRecord;

/**
 * Storage Folder Model.
 *
 * @property int $id
 * @property string $name
 * @property int $parent_id
 * @property int $timestamp_create
 * @property int $is_deleted
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
final class StorageFolder extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admin_storage_folder}}';
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        return parent::find()->orderBy(['name' => 'ASC']);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['parent_id', 'timestamp_create'], 'integer'],
            [['is_deleted'], 'boolean'],
            [['name'], 'string', 'max' => 255],
        ];
    }
}
