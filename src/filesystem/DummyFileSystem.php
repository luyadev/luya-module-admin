<?php

namespace luya\admin\filesystem;

use Yii;
use luya\admin\storage\BaseFileSystemStorage;

/**
 * Dummy File System for Testing.
 *
 * This file system is used for unit testing, or in situations where there is no storage system required but any componets are depending on this.
 *
 * Configuration example
 *
 * ```php
 * 'components' => [
 *     'storage' => 'luya\admin\filesystem\DumyFileSystem',
 * ],
 * ```
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.3
 */
class DummyFileSystem extends BaseFileSystemStorage
{
    /**
     * @inheritdoc
     */
    public function getHttpPath()
    {
        return Yii::getAlias('@app/storage/http-path');
    }
    
    /**
     * @inheritdoc
     */
    public function getAbsoluteHttpPath()
    {
        return Yii::getAlias('@app/storage/absolute-http-path');
    }
    
    /**
     * @inheritdoc
     */
    public function getServerPath()
    {
        return Yii::getAlias('@app/storage/server-path');
    }
    
    /**
     * @inheritdoc
     */
    public function fileSystemSaveFile($source, $fileName)
    {
        return true;
    }
    
    /**
     * @inheritdoc
     */
    public function fileSystemReplaceFile($oldSource, $newSource)
    {
        return true;
    }
    
    /**
     * @inheritdoc
     */
    public function fileSystemDeleteFile($source)
    {
        return true;
    }
}
