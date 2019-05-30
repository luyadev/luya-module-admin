<?php

namespace luya\admin\events;

use yii\base\Event;

class FileEvent extends Event
{
    /**
     * @var \luya\admin\models\StorageFile
     */
    public $file;
}
