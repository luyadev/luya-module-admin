<?php

namespace luya\admin\models;

use luya\admin\ngrest\base\NgRestModel;
use luya\admin\Module;
use luya\admin\traits\TaggableTrait;

/**
 * This is the model class for table "admin_tag".
 *
 * @property integer $id
 * @property string $name
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
final class Tag extends NgRestModel
{
    public $i18n = ['translation'];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admin_tag}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['translation'], 'string'],
            [['name'], 'unique'],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => Module::t('model_tag_name'),
            'relationsCount' => Module::t('model_tag_relations_count'),
            'translation' => Module::t('model_tag_translation'),
        ];
    }

    public function attributeHints()
    {
        return [
            'translation' => Module::t('model_tag_translation_hint'),
        ];
    }

    /**
     * @inheritdoc
     */
    public static function ngRestApiEndpoint()
    {
        return 'api-admin-tag';
    }

    /**
     * @inheritdoc
     */
    public function ngRestAttributeTypes()
    {
        return [
            'name' => 'text',
            'translation' => 'text',
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function ngRestExtraAttributeTypes()
    {
        return [
            'relationsCount' => 'text',
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function ngRestScopes()
    {
        return [
            [['list'], ['name', 'relationsCount']],
            [['create', 'update'], ['name', 'translation']],
        ];
    }
    
    /**
     * Returns the amount of rows for the curren tag.
     *
     * @return integer
     */
    public function getRelationsCount()
    {
        return count($this->tagRelations);
    }

    /**
     * Get all primary key assigned tags for a table name.
     *
     * @param string $tableName
     * @param integer $pkId
     * @return \yii\db\ActiveRecord
     */
    public static function findRelations($tableName, $pkId)
    {
        return self::find()
            ->innerJoin(TagRelation::tableName(), '{{%admin_tag_relation}}.tag_id={{%admin_tag}}.id')
            ->where(['pk_id' => $pkId, 'table_name' => TaggableTrait::cleanBaseTableName($tableName)])
            ->indexBy('name')
            ->orderBy(['name' => SORT_ASC])
            ->all();
    }
    
    /**
     * Get all assigned tags for table name.
     *
     * @param string $tableName
     * @return \yii\db\ActiveRecord
     */
    public static function findRelationsTable($tableName)
    {
        return self::find()
            ->innerJoin(TagRelation::tableName(), '{{%admin_tag_relation}}.tag_id={{%admin_tag}}.id')
            ->where(['table_name' => TaggableTrait::cleanBaseTableName($tableName)])
            ->indexBy('name')
            ->orderBy(['name' => SORT_ASC])
            ->distinct()
            ->all();
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTagRelations()
    {
        return $this->hasMany(TagRelation::class, ['tag_id' => 'id']);
    }
}
