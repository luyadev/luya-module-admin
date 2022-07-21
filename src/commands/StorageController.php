<?php

namespace luya\admin\commands;

use luya\console\Command;
use Yii;

/**
 * LUYA Admin Storage command.
 *
 * As since 1.2 the storage importer is removed, we have to move all commands here, as they wont work since storage system refactoring.
 *
 * @author Martin Petrasch <martin.petrasch@zephir.ch>
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class StorageController extends Command
{
    /**
     * Create all thumbnails for filemanager preview. Otherwhise they are created on request load.
     */
    public function actionProcessThumbnails()
    {
        $response = Yii::$app->storage->processThumbnails();

        if ($response) {
            return $this->outputSuccess('Successful generated storage thumbnails.');
        }

        return $this->outputError('Error while creating the storage thumbnails.');
    }

    /**
     * See image duplications exists of filter and file id combination and remove them execept of the first created.
     *
     * @return number
     */
    public function actionCleanupImageTable()
    {
        $rows = Yii::$app->db->createCommand('SELECT file_id, filter_id, COUNT(*) as count FROM {{%admin_storage_image}} GROUP BY file_id, filter_id HAVING COUNT(*) > 1')->queryAll();

        if (empty($rows)) {
            return $this->outputInfo("no dublications has been detected.");
        }

        $this->outputInfo("dublicated image files detected:");
        foreach ($rows as $row) {
            $this->output("> file {$row['file_id']} with filter {$row['filter_id']} found {$row['count']} duplicates.");
        }

        if ($this->confirm("Do you want to delte the duplicated files in the image storage table?")) {
            foreach ($rows as $key => $row) {
                // get the lowest entrie
                $keep = Yii::$app->db->createCommand('SELECT id FROM {{%admin_storage_image}} WHERE file_id=:fileId AND filter_id=:filterId ORDER BY id LIMIT 1', [
                    ':fileId' => $row['file_id'],
                    ':filterId' => $row['filter_id'],
                ])->queryOne();

                if (!$keep) {
                    $this->outputError('Unable to find the first row for this delete request. Skip this one');
                    continue;
                }

                $remove = Yii::$app->db->createCommand()->delete('{{%admin_storage_image}}', 'file_id=:fileId AND filter_id=:filterId AND id!=:id', [
                    ':fileId' => $row['file_id'],
                    ':filterId' => $row['filter_id'],
                    ':id' => $keep['id'],
                ])->execute();

                if ($remove) {
                    $this->outputSuccess("< Remove {$row['count']} duplications for file {$row['file_id']} with filter {$row['filter_id']}.");
                }
            }
        }

        return $this->outputSuccess("all duplications has been removed.");
    }
}
