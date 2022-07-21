<?php

namespace luya\admin\jobs;

use luya\admin\models\StorageFile;
use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;

/**
 * Process certain filters for an image/file.
 *
 * @author Basil Suter <git@nadar.io>
 * @since 4.0.0
 */
class ImageFilterJob extends BaseObject implements JobInterface
{
    /**
     * @var integer The storage file id
     */
    public $fileId;

    /**
     * @var string|array The filter identifiers. This can be either an array or a string.
     */
    public $filterIdentifiers;

    /**
     * {@inheritDoc}
     */
    public function execute($queue)
    {
        if (StorageFile::find()->where(['id' => $this->fileId])->exists()) {
            Yii::$app->storage->createImage($this->fileId, 0);
            foreach ((array) $this->filterIdentifiers as $identifier) {
                Yii::$app->storage->createImage($this->fileId, $identifier);
            }
        }
    }
}
