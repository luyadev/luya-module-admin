<?php

namespace luya\admin\filesystem;

use luya\admin\storage\BaseFileSystemStorage;
use luya\Exception;
use luya\helpers\FileHelper;
use luya\helpers\Url;
use Yii;

/**
 * Local File System uses the storage folder inside @webroot.
 *
 * This is the default file system which is used for LUYA. The LocalFileSystem class uses
 * the storage folder inside the @webroot directory in order to store and read files and images.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class LocalFileSystem extends BaseFileSystemStorage
{
    private $_httpPath;

    /**
     * @var string The name of the folder which is used to storage the data inside the @webroot directory.
     */
    public $folderName = 'storage';

    /**
     * Setter for the http path in order to read online storage files.
     *
     * Sometimes you want to set the http directory of where the files are display in the frontend to read from another
     * server. For example you have a prod and a preprod sytem and when deploying the preprod system the database will
     * be copied into the preprod system from prod. Now all the files are located on the prod system and you will have
     * broke image/file links. To generate the image/file links you can now override the httpPath in your configuration
     * to read all the files from the prod server. For example add this in the `components` section of your config:
     *
     * ```php
     * 'storage' => [
     *     'class' => 'luya\admin\filesystem\LocalFileSystem',
     *     'httpPath' => 'mystorage/files',
     *     'absoluteHttpPath' => 'https://mywebsite.com/mystorage/files',
     * ]
     * ```
     *
     * @param string $path The location of your storage folder without trailing slash. E.g `http://prod.example.com/storage`
     */
    public function setHttpPath($path)
    {
        $this->_httpPath = $path;
    }

    /**
     * @inheritdoc
     */
    public function fileHttpPath($fileName)
    {
        if ($this->_httpPath === null) {
            $this->_httpPath = $this->request->baseUrl . DIRECTORY_SEPARATOR . $this->folderName;
        }

        return $this->_httpPath . DIRECTORY_SEPARATOR . $fileName;
    }

    private $_absoluteHttpPath;

    /**
     * Setter fro the absolute http path in order to read from another storage source.
     *
     * Sometimes you want to set the http directory of where the files are display in the frontend to read from another
     * server. For example you have a prod and a preprod sytem and when deploying the preprod system the database will
     * be copied into the preprod system from prod. Now all the files are located on the prod system and you will have
     * broke image/file links. To generate the image/file links you can now override the httpPath in your configuration
     * to read all the files from the prod server. For example add this in the `components` section of your config:
     *
     * ```php
     * 'storage' => [
     *     'class' => 'luya\admin\filesystem\LocalFileSystem',
     *     'httpPath' => 'mystorage/files',
     *     'absoluteHttpPath' => 'https://mywebsite.com/mystorage/files',
     * ]
     * ```
     *
     * @param string $path The absolute location of your storage folder without trailing slash. E.g `http://prod.example.com/storage`
     */
    public function setAbsoluteHttpPath($path)
    {
        $this->_absoluteHttpPath = $path;
    }

    /**
     * @inheritdoc
     */
    public function fileAbsoluteHttpPath($fileName)
    {
        if ($this->_absoluteHttpPath === null) {
            $this->_absoluteHttpPath = Url::base(true) . DIRECTORY_SEPARATOR . $this->folderName;
        }

        return $this->_absoluteHttpPath . DIRECTORY_SEPARATOR . $fileName;
    }

    private $_serverPath;

    /**
     * Setter method for serverPath.
     *
     * @param string $path
     */
    public function setServerPath($path)
    {
        $this->_serverPath = $path;
    }

    /**
     * @inheritdoc
     */
    public function fileServerPath($fileName)
    {
        if ($this->_serverPath === null) {
            $this->_serverPath = Yii::getAlias('@webroot') . DIRECTORY_SEPARATOR . $this->folderName;
        }

        return $this->_serverPath . DIRECTORY_SEPARATOR . $fileName;
    }

    /**
     * @inheritdoc
     */
    public function fileSystemExists($fileName)
    {
        return file_exists($this->fileServerPath($fileName));
    }

    /**
     * @inheritdoc
     */
    public function fileSystemContent($fileName)
    {
        return FileHelper::getFileContent($this->fileServerPath($fileName));
    }

    /**
     * {@inheritDoc}
     */
    public function fileSystemStream($fileName)
    {
        return fopen($this->fileServerPath($fileName), 'r');
    }

    /**
     * @inheritdoc
     */
    public function fileSystemSaveFile($source, $fileName)
    {
        $savePath = $this->fileServerPath($fileName);

        if (is_uploaded_file($source)) {
            if (!@move_uploaded_file($source, $savePath)) {
                throw new Exception("Error while moving an uploaded file from \"$source\" to \"$savePath\".");
            }
        } else {
            if (!@copy($source, $savePath)) {
                throw new Exception("Error while copy the file from \"$source\" to \"$savePath\".");
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function fileSystemReplaceFile($fileName, $newSource)
    {
        $oldSource = $this->fileServerPath($fileName);
        $toDelete = $oldSource . uniqid('oldfile') . '.bkl';
        if (rename($oldSource, $toDelete)) {
            if (copy($newSource, $oldSource)) {
                @unlink($toDelete);
                return true;
            }
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function fileSystemDeleteFile($fileName)
    {
        return FileHelper::unlink($this->fileServerPath($fileName));
    }
}
