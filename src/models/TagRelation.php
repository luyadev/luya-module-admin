<?php

namespace luya\admin\models;

use luya\admin\traits\TaggableTrait;
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
        return self::find()->where(['table_name' => TaggableTrait::cleanBaseTableName($tableName), 'pk_id' => $pkId])->asArray($asArray)->all();
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
        return self::find()->select('tag_id')->where(['table_name' => TaggableTrait::cleanBaseTableName($tableName)])->distinct()->asArray($asArray)->all();
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
}
