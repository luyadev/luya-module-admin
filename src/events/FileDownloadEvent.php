<?php

namespace luya\admin\events;

use luya\admin\models\StorageFile;

/**
 * File Download Event.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class FileDownloadEvent extends \yii\base\Event
{
    /**
     * {@inheritDoc}
     */
    public $isValid = true;

    /**
     * @var StorageFile $file The file which is downloaded (requested).
     */
    public $file;
}
