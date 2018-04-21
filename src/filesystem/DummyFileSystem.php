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

    private $_files = [];
    
    /**
     * Add a dummy file.
     * 
     * Do not forget to call `insertDummyFiles()` afterwards.
     * 
     * @param array $config
     * @since 1.1.1
     */
    public function addDummyFile(array $config)
    {
        $keys = ['id', 'is_hidden', 'is_deleted', 'folder_id', 'name_original', 'name_new', 'name_new_compound', 'mime_type', 'extension', 'hash_name', 'hash_file', 'upload_timestamp', 'file_size', 'upload_user_id', 'caption'];
        $item = array_flip($keys);
        $data = array_merge($item, $config);
        $this->_files[$data['id']] = $data;
    }
    
    /**
     * Insert the dummy files from `addDummyFile()`.
     * 
     * @since 1.1.1
     */
    public function insertDummyFiles()
    {
        $this->setFilesArray($this->_files);
    }
    
    private $_images = [];

    /**
     * Add dummy image.
     * 
     * Do not forget to call `insertDummyImages()` afterwards.
     * 
     * @param array $config
     * @since 1.1.1
     */
    public function addDummyImage(array $config)
    {
        $keys = ['id', 'file_id', 'filter_id', 'resolution_width', 'resolution_height'];
        $item = array_flip($keys);
        $data = array_merge($item, $config);
        $this->_images[$data['id']] = $data;
    }
    
    /**
     * Insert the dummy images from `addDummyImage()`.
     * 
     * @since 1.1.1
     */
    public function insertDummyImages()
    {
        $this->setImagesArray($this->_images);
    }
}
