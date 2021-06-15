<?php

namespace luya\admin\jobs;

use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;

/**
 * Process an image filter for a given file
 */
class ImageFilterJob extends BaseObject implements JobInterface
{
    public $fileId;

    public $filterIdentifiers;

    public function execute($queue)
    {
        Yii::$app->storage->createImage($this->fileId, 0); 
        foreach ((array) $this->filterIdentifiers as $identifier) {
            Yii::$app->storage->createImage($this->fileId, $identifier); 
        }
    }
}