<?php

namespace luya\admin\traits;

use luya\admin\models\Tag;

/**
 * Tag Trait.
 *
 * This trait can be assigned in order to read the tag data for an ActiveRecord model.
 *
 * See {{luya\admin\aws\TaggableActiveWindow}} order to atach the TagActiveWindow.
 *
 * When the TagsTrait is attached to an {{luya\admin\ngrest\base\NgRestModel}} use the trait as below:
 *
 * ```php
 * $tags = Model::findOne(1)->tags;
 * ```
 *
 * Or you can also get all tags assigned for this table:
 *
 * ```php
 * $allTags = Model::findTags();
 * ```
 *
 * Since `getTags()` is a relation you can also preload those tag informations within a model like:
 *
 * ```php
 * foreach (News::find()->with(['tags'])->all() as $news) {
 *     var_dump($news->tags);
 * }
 * ```
 *
 * To get all records with one or more tags matching:
 * ```php
 * $matchTags = [1, 2, 3];
 * Model::find()
 *     ->joinWith(['tags'])
 *     ->andWhere(['in', 'tag_id', $matchTags])
 *     ->all();
 * ```
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.2.2
 */
trait TaggableTrait
{
    /**
     * Returns all related tag for the current active record item.
     *
     * @return \luya\admin\models\Tag An active record array from tag models
     */
    public function getTags()
    {
        return $this->hasMany(Tag::class, ['id' => 'tag_id'])->viaTable('{{%admin_tag_relation}}', ['pk_id' => 'id'], function ($query) {
            $query->onCondition(['table_name' => static::cleanBaseTableName(static::tableName())]);
        });
    }

    /**
     * Remove brackes from the table name, this makes sure the table name also works if the db prefix changes.
     *
     * @return string
     * @since 2.0.0
     */
    public static function cleanBaseTableName($tableName)
    {
        return str_replace(['{{%', '}}'], '', $tableName);
    }

    /**
     * Get all tags associated with this table.
     *
     * @return \luya\admin\models\Tag An active record array from tag model.
     */
    public static function findTags()
    {
        return Tag::findRelationsTable(static::cleanBaseTableName(static::tableName()));
    }
}
