<?php

namespace luya\admin\models;

use luya\admin\aws\DeleteTagsActiveWindow;
use luya\admin\ngrest\base\NgRestModel;
use luya\admin\Module;
use luya\admin\traits\TaggableTrait;
use yii\db\ActiveRecordInterface;

/**
 * This is the model class for table "admin_tag".
 *
 * @property integer $id
 * @property string $name
 * @property string $translation
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
     * @inheritdoc
     */
    public function ngRestActiveWindows()
    {
        return [
            ['class' => DeleteTagsActiveWindow::class],
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
     * Toggle (Enable or Disable) a given tag for a Model.
     *
     * ```php
     * $tag = Tag::find()->where(['alias' => 'soccer'])->one();
     *
     * $model = MyModel::findOne(1);
     * $tag->toggleRelationByModel($model);
     * ```
     *
     * @param ActiveRecordInterface $model
     * @return boolean
     * @since 2.2.1
     */
    public function toggleRelationByModel(ActiveRecordInterface $model)
    {
        $pkId = $model->getPrimaryKey(false);
        $tableName = TaggableTrait::cleanBaseTableName($model->tableName());
        
        return $this->toggleRelation($pkId, $tableName);
    }

    /**
     * Toggle a tag relation for given pkId and tableName.
     *
     * @param integer $pkId
     * @param string $tableName
     * @return boolean
     * @since 2.2.1
     */
    public function toggleRelation($pkId, $tableName)
    {
        $relation = $this->getTagRelations()
            ->andWhere([
                'table_name' => $tableName,
                'pk_id' => $pkId,
            ])
            ->one();

        if ($relation) {
            return (bool) $relation->delete();
        }

        $relation = new TagRelation();
        $relation->tag_id = $this->id;
        $relation->table_name = $tableName;
        $relation->pk_id = $pkId;

        return $relation->save();
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTagRelations()
    {
        return $this->hasMany(TagRelation::class, ['tag_id' => 'id']);
    }
}
