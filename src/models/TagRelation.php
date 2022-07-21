<?php

namespace luya\admin\models;

use yii\db\ActiveRecord;

/**
 * Tags in realtion to another Table.
 *
 * In order to find all tags to a specific relation use:
 *
 * ```php
 * $tags = TagRelation::getDataForRelation('my_table', '14');
 * ```
 *
 * The above example will return all
 *
 * @property string $table_name
 * @property integer $pk_id
 * @property integer $tag_id
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
final class TagRelation extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admin_tag_relation}}';
    }

    public function init()
    {
        parent::init();
        $this->on(self::EVENT_AFTER_VALIDATE, function () {
            $this->table_name = StorageFile::cleanBaseTableName($this->table_name);
        });
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tag_id', 'pk_id'], 'integer'],
            [['tag_id', 'table_name', 'pk_id'], 'unique', 'targetAttribute' => ['tag_id', 'table_name', 'pk_id']],
        ];
    }

    /**
     * Get an array with all entries for a table name associated with a primary key.
     *
     * This methods i mainly used internal to retrieve data for the Active Window. Use the {{luya\admin\traits\TagsTrait}} in your Model instead.
     *
     * @param string $tableName The table name
     * @param integer $pkId The primary key combination.
     * @param boolean $asArray Whether active records should be returned or raw arrays.
     * @return array|ActiveRecord[]
     */
    public static function getDataForRelation($tableName, $pkId, $asArray = true)
    {
        return self::find()->where(['table_name' => StorageFile::cleanBaseTableName($tableName), 'pk_id' => $pkId])->asArray($asArray)->all();
    }

    /**
     * Get an array with all entries for a table name.
     *
     * This methods i mainly used internal to retrieve data for the Active Window. Use the {{luya\admin\traits\TagsTrait}} in your Model instead.
     *
     * @param string $tableName The table name.
     * @param boolean $asArray Whether active records should be returned or raw arrays.
     * @return array|ActiveRecord[]
     */
    public static function getDistinctDataForTable($tableName, $asArray = true)
    {
        return self::find()->select('tag_id')->where(['table_name' => StorageFile::cleanBaseTableName($tableName)])->distinct()->asArray($asArray)->all();
    }

    /**
     * Save multiple tags for a given pk and table name.
     *
     * @param array $tagIds
     * @param integer $tableName
     * @param integer $pkId
     * @return integer Returns the number of relations successful added.
     * @since 2.2.1
     */
    public static function batchInsertRelations(array $tagIds, $tableName, $pkId)
    {
        $counter = 0;
        foreach ($tagIds as $tagId) {
            $relation = new self();
            $relation->table_name = $tableName;
            $relation->tag_id = $tagId;
            $relation->pk_id = $pkId;
            if ($relation->save()) {
                $counter++;
            }
        }

        return $counter;
    }

    /**
     * Remove all relations of the table and add new relations based on tagIds array and pkId.
     *
     * > compared to {{batchInsertRelations}} this will also remove all existing relation entries for this table.
     *
     * @param array $tagIds
     * @param string $tableName
     * @param integer $pkId
     * @return integer Returns the number of relations successful added.
     * @since 2.2.1
     */
    public static function batchUpdateRelations(array $tagIds, $tableName, $pkId)
    {
        foreach (self::getDataForRelation($tableName, $pkId, false) as $relation) {
            $relation->delete();
        }

        return self::batchInsertRelations($tagIds, $tableName, $pkId);
    }

    /**
     * Get tag object relation.
     *
     * @return \luya\admin\models\Tag
     */
    public function getTag()
    {
        return $this->hasOne(Tag::class, ['id' => 'tag_id']);
    }

    /**
     * Delete (cleanup) all relations for a given model.
     *
     * @param ActiveRecord $model
     * @return integer
     * @since 4.2.0
     */
    public static function cleanup(ActiveRecord $model)
    {
        return self::deleteAll(['table_name' => StorageFile::cleanBaseTableName($model->tableName()), 'pk_id' => $model->getPrimaryKey()]);
    }
}
