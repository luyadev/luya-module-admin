<?php

namespace luya\admin\commands;

use luya\admin\models\StorageFile;
use luya\admin\models\Tag;
use luya\admin\models\TagRelation;
use luya\console\Command;
use Yii;
use yii\db\Query;

/**
 * Tags Controller.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 2.0.0
 */
class TagController extends Command
{
    /**
     * Handle wrong declared table names and try to cleanup not existing relations.
     *
     * @return integer
     */
    public function actionFixTableNames()
    {
        $batch = TagRelation::find()->batch();
        $i = 0;
        $fixed = 0;
        $errors = 0;
        foreach ($batch as $rows) {
            foreach ($rows as $relation) {
                $i++;
                $tableName = StorageFile::cleanBaseTableName($relation->table_name);
                if ($relation->table_name !== $tableName) {
                    // if the new name exists already as combination, we can just delete the old one.
                    /*
                    // maybe use configuration option to delete entries by default.
                    if (TagRelation::find()->where(['table_name' => $tableName, 'pk_id' => $relation->pk_id, 'tag_id' => $relation->tag_id])->exists()) {
                        $relation->delete();
                        $fixed++;
                        continue;
                    }
                    */

                    $relation->table_name = $tableName;
                    if ($relation->save()) {
                        $fixed++;
                    } else {
                        $errors++;
                    }
                }

                unset($tableName, $relation);
            }
        }

        return $this->outputSuccess("{$i} items checked and {$fixed} items fixed with {$errors} errors.");
    }

    /**
     * Handle not existing relations, tags which does not exists or relation entries which does not exists in the table.
     *
     * @return integer
     */
    public function actionCleanup()
    {
        $tagIds = Tag::find()->select(['id'])->column();

        $batch = TagRelation::find()->batch();
        $i = 0;
        $delete = 0;
        foreach ($batch as $rows) {
            foreach ($rows as $relation) {
                $i++;
                // check if tag id exists in table
                if (!in_array($relation->tag_id, $tagIds)) {
                    $relation->delete();
                    $delete++;
                    continue;
                }

                $prefixedTableName = '{{%'.$relation->table_name.'}}';
                $pk = $this->tableSchema($prefixedTableName)->primaryKey;
                $query = (new Query())
                    ->from($prefixedTableName)
                    ->where([current($pk) => $relation->pk_id]) // provide model mapping or read pk name from schema
                    ->exists();

                if (!$query) {
                    $relation->delete();
                    $delete++;
                }

                unset($relation, $prefixedTableName, $pk, $query);
            }
        }

        return $this->outputSuccess("{$i} items checked and {$delete} items deleted.");
    }

    private array $_schemas = [];

    private function tableSchema($tableName)
    {
        if (array_key_exists($tableName, $this->_schemas)) {
            return $this->_schemas[$tableName];
        }

        $schema =  Yii::$app->db
        ->getSchema()
        ->getTableSchema($tableName);

        $this->_schemas[$tableName] = $schema;

        return $schema;
    }
}
