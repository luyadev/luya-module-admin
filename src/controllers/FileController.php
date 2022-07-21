<?php

namespace luya\admin\controllers;

use luya\admin\events\FileDownloadEvent;
use luya\admin\models\StorageFile;
use luya\admin\Module;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

/**
 * File Download Controller
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class FileController extends \luya\web\Controller
{
    /**
     * File Download Action.
     *
     * @param integer $id
     * @param string $hash
     * @param string $fileName
     * @return \yii\web\Response
     */
    public function actionDownload($id, $hash, $fileName)
    {
        $model = StorageFile::findOne(['id' => $id, 'hash_name' => $hash, 'is_deleted' => false]);
        // proceed when file exists
        if ($model && $model->fileExists) {
            $event = new FileDownloadEvent(['file' => $model]);

            Yii::$app->trigger(Module::EVENT_BEFORE_FILE_DOWNLOAD, $event);

            if (!$event->isValid) {
                throw new BadRequestHttpException('Unable to perform file download request due to access restrictions.');
            }

            // update the model count stats
            $model->updateCounters(['passthrough_file_stats' => 1]);

            return Yii::$app->response->sendStreamAsFile($model->stream, $model->name_original, [
                'inline' => (bool) $model->inline_disposition,
                'mimeType' => $model->mime_type,
                'fileSize' => $model->file_size,
            ]);
        }

        // throw not found http exception, will not trigger error api transfer.
        throw new NotFoundHttpException("Unable to find requested file '{$fileName}'.");
    }
}
