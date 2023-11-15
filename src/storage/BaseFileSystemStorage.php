<?php

namespace luya\admin\storage;

use luya\admin\events\FileEvent;
use luya\admin\file\Item;
use luya\admin\filters\MediumThumbnail;
use luya\admin\filters\TinyCrop;
use luya\admin\helpers\Storage;
use luya\admin\jobs\ImageFilterJob;
use luya\admin\models\StorageFile;
use luya\admin\models\StorageFilter;
use luya\admin\models\StorageFolder;
use luya\admin\models\StorageImage;
use luya\admin\Module;
use luya\Exception;
use luya\helpers\FileHelper;
use luya\traits\CacheableTrait;
use luya\web\Request;
use Yii;
use yii\base\Component;
use yii\base\UserException;
use yii\db\Query;
use yii\helpers\Inflector;
use yii\helpers\VarDumper;

/**
 * Storage Container for reading, saving and holding files.
 *
 * Create images, files, manipulate, foreach and get details. The storage container will be the singleton similar instance containing all the loaded images and files.
 *
 * The base storage system is implemented by filesystems:
 *
 * + {{luya\admin\filesystem\LocalStorage}} (Default system for the admin module)
 * + {{luya\admin\filesystem\S3}}
 *
 * As files, images and folders implement the same traits you can also read more about enhanced usage:
 *
 * + Querying Data with {{\luya\admin\storage\QueryTrait}}
 * + Where conditions {{\luya\admin\storage\QueryTrait::where()}}
 *
 * ## Handling Files
 *
 * First adding a new file to the Storage system using the {{\luya\admin\storage\BaseFileSystemStorage::addFile()}} method.
 *
 * ```php
 * Yii::$app->storage->addFile('/the/path/to/File.jpg', 'File.jpg', 0, 1);
 * ```
 *
 * The response of the add file method is an {{\luya\admin\file\Item}} Object.
 *
 * Get an array of files based on search parameters (When not passing any arguments all files would be returned.):
 *
 * ```php
 * Yii::$app->storage->findFiles(['is_hidden' => 0, 'is_deleted' => 0]);
 * ```
 *
 * In order to get a single file object based on its ID use:
 *
 * ```php
 * Yii::$app->storage->getFile(5);
 * ```
 *
 * To find a file based on other where arguments instead of the id use findFile:
 *
 * ```php
 * Yii::$app->storage->findFile(['name' => 'MyFile.jpg']);
 * ```
 *
 * ### Handling Images
 *
 * An image object is always based on the {{\luya\admin\file\Item}} object and a {{luya\admin\base\Filter}}. In order to add an image you already need a fileId and filterId. If the filterId is 0, it means no additional filter will be applied.
 *
 * ```php
 * Yii::$app->storage->addImage(123, 0); // create an image from file object id 123 without filter.
 * ```
 *
 * The newly created image will return a {{luya\admin\image\Item}} object.
 *
 * In order to find one image:
 *
 * ```php
 * Yii::$app->storage->findImage(['id' => 123]);
 * ```
 *
 * or find one image by its ID:
 *
 * ```php
 * Yii::$app->storage->getImage(123);
 * ```
 *
 * To get an array of images based on where conditions use:
 *
 * ```php
 * Yii::$app->storage->findImages(['file_id' => 1, 'filter_id' => 0]);
 * ```
 *
 * @property string $serverPath Get the server path (for php) to the storage folder.
 * @property array $filesArray An array containing all files
 * @property array $imagesArray An array containg all images
 * @property array $foldersArray An array containing all folders
 * @property array $filtersArray An array with all filters
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
abstract class BaseFileSystemStorage extends Component
{
    use CacheableTrait;

    /**
     * @var string This event is triggered when the storage file model is updating, for example when change the disposition.
     * @since 2.0.0
     */
    public const FILE_UPDATE_EVENT = 'onFileUpdate';

    /**
     * @var string This event is triggered when a new file is uploaded to the file system.
     * @since 2.0.0
     */
    public const FILE_SAVE_EVENT = 'onFileSave';

    /**
     * Return the http path for a given file on the file system.
     *
     * @param string $fileName The name of the file on the filesystem (like: my_example_1234.jpg without path infos), the $fileName is used as identifier on the filesystem.
     * @since 1.2.0
     */
    abstract public function fileHttpPath($fileName);

    /**
     * Return the absolute http path for a given file on the file system.
     *
     * @param string $fileName The name of the file on the filesystem (like: my_example_1234.jpg without path infos), the $fileName is used as identifier on the filesystem.
     * @since 1.2.0
     */
    abstract public function fileAbsoluteHttpPath($fileName);
    /**
     * Returns the path internal server path to the given file on the file system.
     *
     * @param string $fileName The name of the file on the filesystem (like: my_example_1234.jpg without path infos), the $fileName is used as identifier on the filesystem.
     */
    abstract public function fileServerPath($fileName);

    /**
     * Check if the file exists on the given file system.
     *
     * @param string $fileName The name of the file on the filesystem (like: my_example_1234.jpg without path infos), the $fileName is used as identifier on the filesystem.
     * @since 1.2.0
     */
    abstract public function fileSystemExists($fileName);

    /**
     * Get the content of the file on the given file system.
     *
     * @param string $fileName The name of the file on the filesystem (like: my_example_1234.jpg without path infos), the $fileName is used as identifier on the filesystem.
     * @since 1.2.0
     */
    abstract public function fileSystemContent($fileName);

    /**
     * Save the given file source as a new file with the given fileName on the filesystem.
     *
     * @param string $source The absolute file source path and filename, like `/tmp/upload/myfile.jpg`.
     * @param string $fileName The name of the file on the filesystem (like: my_example_1234.jpg without path infos), the $fileName is used as identifier on the filesystem.
     * @return boolean Whether the file has been stored or not.
     */
    abstract public function fileSystemSaveFile($source, $fileName);

    /**
     * Generate a stream/resource for the file to download
     * @param string $fileName
     * @return resource
     * @since 4.0.0
     */
    abstract public function fileSystemStream($fileName);

    /**
     * Replace an existing file source with a new one on the filesystem.
     *
     * @param string $fileName The name of the file on the filesystem (like: my_example_1234.jpg without path infos), the $fileName is used as identifier on the filesystem.
     * @param string $newSource The absolute file source path and filename, like `/tmp/upload/myfile.jpg`.
     * @return boolean Whether the file has replaced stored or not.
     */
    abstract public function fileSystemReplaceFile($fileName, $newSource);

    /**
     * Delete a given file source on the filesystem.
     *
     * @param string $fileName The name of the file on the filesystem (like: my_example_1234.jpg without path infos), the $fileName is used as identifier on the filesystem.
     * @return boolean Whether the file has been deleted or not.
     */
    abstract public function fileSystemDeleteFile($fileName);

    /**
     * @var string File cache key.
     */
    public const CACHE_KEY_FILE = 'storage_fileCacheKey';

    /**
     * @var string Image cache key.
     */
    public const CACHE_KEY_IMAGE = 'storage_imageCacheKey';

    /**
     * @var string Folder cache key.
     */
    public const CACHE_KEY_FOLDER = 'storage_folderCacheKey';

    /**
     * @var string Filter cache key.
     */
    public const CACHE_KEY_FILTER = 'storage_filterCacheKey';

    /**
     * @var array The mime types which will be rejected.
     */
    public $dangerousMimeTypes = [
        'application/x-msdownload',
        'application/x-msdos-program',
        'application/x-msdos-windows',
        'application/x-download',
        'application/bat',
        'application/x-bat',
        'application/com',
        'application/x-com',
        'application/exe',
        'application/x-exe',
        'application/x-winexe',
        'application/x-winhlp',
        'application/x-winhelp',
        'application/x-javascript',
        'application/hta',
        'application/x-ms-shortcut',
        'application/octet-stream',
        'vms/exe',
        'text/javascript',
        'text/scriptlet',
        'text/x-php',
        'text/plain',
        'application/x-spss',
        'image/svg+xml',
    ];

    /**
     * @var array The extension which will be rejected.
     */
    public $dangerousExtensions = [
        'html', 'php', 'phtml', 'php3', 'exe', 'bat', 'js',
    ];

    /**
     * @var array a list of mime types which are indicating images
     * @since 1.2.2.1
     */
    public $imageMimeTypes = [
        'image/gif', 'image/jpeg', 'image/png', 'image/jpg',
    ];

    /**
     * @var boolean Whether secure file upload is enabled or not. If enabled dangerous mime types and extensions will
     * be rejected and the file mime type needs to be verified by phps `fileinfo` extension.
     */
    public $secureFileUpload = true;

    /**
     * @var array The mime types inside this array are whitelistet and will be stored whether validation failes or not. For example if mime
     * type 'text/plain' is given for a 'csv' extension, the valid extensions would be 'txt' or 'log', this would throw an exception, therefore
     * you can whitelist the 'text/plain' mime type. This can be usefull when uploading csv files.
     * @since 1.1.0
     */
    public $whitelistMimeTypes = [];

    /**
     * @var array An array with extensions which are whitelisted. This can be very dangerous as it will skip the check whether the mime type is
     * matching the extension types. If an extensions in {{$dangerousExtensions}} and {{$whitelistExtensions}} it will still throw an exception as
     * {{$dangerousExtensions}} take precedence over {{$$whitelistExtensions}}.
     * @since 1.2.2
     */
    public $whitelistExtensions = [];

    /**
     * @var boolean When enabled the storage component will try to recreated missing images when {{luya\admin\image\Item::getSource()}} of an
     * image is called but the `getFileExists()` does return false, which means that the source file has been deleted.
     * So in those cases the storage component will automatiaccly try to recreated this image based on the filterId and
     * fileId.
     */
    public $autoFixMissingImageSources = true;

    /**
     * @var boolean When enabled, the filters in the {{luya\admin\storage\BaseFileSystemStorage::$queueFiltersList}} will be applied to the uploaded file if the file is an image. We
     * recommend you turn this on, only when using the `queue/listen` command see [[app-queue.md]], because the user needs to wait until the queue job is processed
     * in the admin ui.
     * @since 4.0.0
     */
    public $queueFilters = false;

    /**
     * @var array If {{luya\admin\storage\BaseFileSystemStorage::$queueFilters}} is enabled, the following image filters will be processed. We recommend
     * to add the default filters which are used in the admin ui (for file manager thumbnails). Therefore those are default values `['tiny-crop', 'medium-thumbnail']`.
     * @since 4.0.0
     */
    public $queueFiltersList = ['tiny-crop', 'medium-thumbnail'];

    /**
     * @var array If the storage system pushed any jobs into the queue, this array holds the queue job ids.
     */
    public $queueJobIds = [];

    /**
     * @var integer|boolean If enabled (integer) the storage system will check whether the total pixel size of the image is not bigger then the given value.
     * For the default value we have calculated 2560x2560 which is 6553600 pixels. If the image is bigger then this value, the image will not be stored.
     * If $maxTotalPixel is false, the check will be disabled.
     * @since 5.0.0
     */
    public $maxTotalPixel = 6553600;

    /**
     * Consturctor resolveds Request component from DI container
     *
     * @param \luya\web\Request $request The request component class resolved by the Dependency Injector.
     * @param array $config
     */
    public function __construct(public Request $request, array $config = [])
    {
        parent::__construct($config);
    }

    private $_filesArray;

    /**
     * Get all storage files as an array from database.
     *
     * This method is used to retrieve all files from the database and indexed by file key.
     *
     * @return array An array with all storage files indexed by the file id.
     */
    public function getFilesArray()
    {
        if ($this->_filesArray === null) {
            $this->_filesArray = $this->getQueryCacheHelper((new Query())->from('{{%admin_storage_file}}')->select(['id', 'is_hidden', 'is_deleted', 'folder_id', 'name_original', 'name_new', 'name_new_compound', 'mime_type', 'extension', 'hash_name', 'hash_file', 'upload_timestamp', 'file_size', 'upload_user_id', 'caption'])->indexBy('id'), self::CACHE_KEY_FILE);
        }

        return $this->_filesArray;
    }

    /**
     * Setter method for fiels array.
     *
     * This is mainly used when working with unit tests.
     *
     * @param array $files
     */
    public function setFilesArray(array $files)
    {
        $this->_filesArray = $files;
    }

    /**
     * Get a single file by file id from the files array.
     *
     * @param integer $fileId The file id to find.
     * @return boolean|array The file array or false if not found.
     */
    public function getFilesArrayItem($fileId)
    {
        return $this->filesArray[$fileId] ?? false;
    }

    private $_imagesArray;

    /**
     * Get all storage images as an array from database.
     *
     * This method is used to retrieve all images from the database and indexed by image key.
     *
     * @return array An array with all storage images indexed by the image id.
     */
    public function getImagesArray()
    {
        if ($this->_imagesArray === null) {
            $this->_imagesArray = $this->getQueryCacheHelper((new Query())->from('{{%admin_storage_image}}')->select(['id', 'file_id', 'filter_id', 'resolution_width', 'resolution_height'])->indexBy('id'), self::CACHE_KEY_IMAGE);
        }

        return $this->_imagesArray;
    }

    /**
     * Setter method for images array.
     *
     * This is mainly used when working with unit tests.
     *
     * @param array $images
     */
    public function setImagesArray(array $images)
    {
        $this->_imagesArray = $images;
    }

    /**
     * Get a single image by image id from the files array.
     *
     * @param integer $imageId The image id to find.
     * @return boolean|array The image array or false if not found.
     */
    public function getImagesArrayItem($imageId)
    {
        return $this->imagesArray[$imageId] ?? false;
    }

    /**
     * Get an array with all files based on a where condition.
     *
     * This method returns an array with files matching there $args array condition. If no argument is provided all files will be returned.
     *
     * See {{\luya\admin\storage\QueryTrait::where}} for condition informations.
     *
     * @param array $args An array with conditions to match e.g. `['is_hidden' => 1, 'is_deleted' => 0]`.
     * @return \luya\admin\file\Iterator An iterator object containing all files found for the condition provided.
     */
    public function findFiles(array $args = [])
    {
        return (new \luya\admin\file\Query())->where($args)->all();
    }

    /**
     * Get a single file based on a where condition.
     *
     * This method returns a single file matching the where condition, if the multiple results match the condition the first one will be picked.
     *
     * See {{\luya\admin\storage\QueryTrait::where}} for condition informations.
     *
     * @param array $args An array with conditions to match e.g. `['is_hidden' => 1, 'is_deleted' => 0]`.
     * @return \luya\admin\file\Item The file item object.
     */
    public function findFile(array $args)
    {
        return (new \luya\admin\file\Query())->where($args)->one();
    }

    /**
     * Get a single file based on the ID.
     *
     * If not found false is returned.
     *
     * @param integer $fileId The requested storage file id.
     * @return \luya\admin\file\Item|boolean The file object or false if not found.
     */
    public function getFile($fileId)
    {
        return (new \luya\admin\file\Query())->findOne($fileId);
    }

    /**
     * Ensure a file uploads and return relevant file infos.
     *
     * @param string $fileSource The file on the server ($_FILES['tmp'])
     * @param string $fileName Original upload name of the file ($_FILES['name'])
     * @throws Exception
     * @return array Returns an array with the following KeywordPatch
     * + fileInfo:
     * + mimeType:
     * + fileName:
     * + secureFileName: The file name with all insecure chars removed
     * + fileSource:
     * + extension: jpg, png, etc.
     * + hashName: a short hash name for the given file, not the md5 sum.
     */
    public function ensureFileUpload($fileSource, $fileName)
    {
        // throw exception if source or name is empty
        if (empty($fileSource) || empty($fileName)) {
            throw new Exception("Filename and source can not be empty.");
        }
        // if filename is blob, its a paste event from the browser, therefore generate the filename from the file source.
        // @TODO: move out of ensureFileUpload
        if ($fileName == 'blob') {
            $ext = FileHelper::getExtensionsByMimeType(FileHelper::getMimeType($fileSource));
            $fileName = 'paste-'.date("Y-m-d-H-i").'.'.$ext[0];
        }
        // get file informations from the name
        $fileInfo = FileHelper::getFileInfo($fileName);
        // get the mimeType from the fileSource, if $secureFileUpload is disabled, the mime type will be extracted from the file extensions
        // instead of using the fileinfo extension, therefore this is not recommend.
        $mimeType = FileHelper::getMimeType($fileSource, null, !$this->secureFileUpload);
        // empty mime type indicates a wrong file upload.
        if (empty($mimeType)) {
            throw new Exception("Unable to find mimeType for the given file, make sure the php extension 'fileinfo' is installed.");
        }

        $extensionsFromMimeType = FileHelper::getExtensionsByMimeType($mimeType);

        if (empty($extensionsFromMimeType) && empty($this->whitelistExtensions)) {
            throw new Exception("Unable to find extension for given mimeType \"{$mimeType}\" or it contains insecure data.");
        }

        if (!empty($this->whitelistExtensions)) {
            $extensionsFromMimeType = array_merge($extensionsFromMimeType, $this->whitelistExtensions);
        }

        // check if the file extension is matching the entries from FileHelper::getExtensionsByMimeType array.
        if (!in_array($fileInfo->extension, $extensionsFromMimeType) && !in_array($mimeType, $this->whitelistMimeTypes)) {
            throw new Exception("The given file extension \"{$fileInfo->extension}\" for file with mimeType \"{$mimeType}\" is not matching any valid extension: ".VarDumper::dumpAsString($extensionsFromMimeType).".");
        }

        foreach ($extensionsFromMimeType as $extension) {
            if (in_array($extension, $this->dangerousExtensions)) {
                throw new Exception("The file extension '{$extension}' seems to be dangerous and can not be stored.");
            }
        }

        // check whether a mimetype is in the dangerousMimeTypes list and not whitelisted in whitelistMimeTypes.
        if (in_array($mimeType, $this->dangerousMimeTypes) && !in_array($mimeType, $this->whitelistMimeTypes)) {
            throw new Exception("The file mimeType '{$mimeType}' seems to be dangerous and can not be stored.");
        }

        return [
            'fileInfo' => $fileInfo,
            'mimeType' => $mimeType,
            'fileName' => $fileName,
            'secureFileName' => Inflector::slug(str_replace('_', '-', $fileInfo->name), '-'),
            'fileSource' => $fileSource,
            'fileSize' => filesize($fileSource),
            'extension' => $fileInfo->extension,
            'hashName' => FileHelper::hashName($fileName),
        ];
    }

    /**
     * Add a new file based on the source to the storage system.
     *
     * When using the $_FILES array you can also make usage of the file helper methods:
     *
     * + {{luya\admin\helpers\Storage::uploadFromFiles}}
     * + {{luya\admin\helpers\Storage::uploadFromFileArray}}
     *
     * When not using the $_FILES array:
     *
     * ```php
     * Yii::$app->storage->addFile('/the/path/to/File.jpg', 'File.jpg', 0, 1);
     * ```
     *
     * @param string $fileSource Path to the file source where the file should be created from
     * @param string $fileName The name of this file (must contain data type suffix).
     * @param integer $folderId The id of the folder where the file should be stored in.
     * @param boolean $isHidden Should the file visible in the filemanager or not.
     * @return bool|Item|Exception Returns the item object, if an error happens an exception is thrown.
     * @throws Exception
     */
    public function addFile($fileSource, $fileName, $folderId = 0, $isHidden = false)
    {
        // ensure the file upload
        $fileData = $this->ensureFileUpload($fileSource, $fileName);
        // generate md5 hash from file source
        $fileHash = FileHelper::md5sum($fileSource);
        // generate new file name for the target file system
        $newName = implode('.', [$fileData['secureFileName'].'_'.$fileData['hashName'], $fileData['extension']]);
        // prefill the storage file model attributes
        $model = new StorageFile();
        $model->setAttributes([
            'name_original' => $fileName,
            'name_new' => $fileData['secureFileName'],
            'name_new_compound' => $newName,
            'mime_type' => $fileData['mimeType'],
            'extension' => $fileData['extension'],
            'folder_id' => (int) $folderId,
            'hash_file' => $fileHash,
            'hash_name' => $fileData['hashName'],
            'is_hidden' => $isHidden ? true : false,
            'is_deleted' => false,
            'file_size' => $fileData['fileSize'],
            'caption' => null,
            'inline_disposition' => (int) Module::getInstance()->fileDefaultInlineDisposition,
        ]);

        if ($this->maxTotalPixel && $model->getIsImage()) {
            ['width' => $width, 'height' => $height] = Storage::getImageResolution($fileSource);

            if (($width * $height) > $this->maxTotalPixel) {
                throw new UserException("The provided image is too big. The maximum allowed pixel size is {$this->maxTotalPixel} pixels.");
            }
        }

        if (!$this->fileSystemSaveFile($fileSource, $newName)) {
            return false;
        }

        if ($model->validate()) {
            if ($model->save()) {
                if ($model->isImage && $this->queueFilters) {
                    $this->queueJobIds[] = Yii::$app->adminqueue->push(new ImageFilterJob(['fileId' => $model->id, 'filterIdentifiers' => $this->queueFiltersList]));
                }
                $this->trigger(self::FILE_SAVE_EVENT, new FileEvent(['file' => $model]));
                $this->deleteHasCache(self::CACHE_KEY_FILE);
                $this->_filesArray[$model->id] = $model->toArray();
                return $this->getFile($model->id);
            }
        }
        return false;
    }

    /**
     * Get an array with all images based on a where condition.
     *
     * This method returns an array with images matching there $args array condition. If no argument is provided all images will be returned.
     *
     * See {{\luya\admin\storage\QueryTrait::where()}} for condition informations.
     *
     * @param array $args An array with conditions to match e.g. `['is_hidden' => 1, 'is_deleted' => 0]`.
     * @return \luya\admin\image\Iterator An iterator object containing all image found for the condition provided.
     */
    public function findImages(array $args = [])
    {
        return (new \luya\admin\image\Query())->where($args)->all();
    }

    /**
     * Get a single image based on a where condition.
     *
     * This method returns a single image matching the where condition, if the multiple results match the condition the first one will be picked.
     *
     * See {{\luya\admin\storage\QueryTrait::where()}} for condition informations.
     *
     * @param array $args An array with conditions to match e.g. `['is_hidden' => 1, 'is_deleted' => 0]`.
     * @return \luya\admin\image\Item The file item object.
     */
    public function findImage(array $args = [])
    {
        return (new \luya\admin\image\Query())->where($args)->one();
    }

    /**
     * Get a single image based on the ID.
     *
     * If not found false is returned.
     *
     * @param integer $imageId The requested storage image id.
     * @return \luya\admin\image\Item|boolean The image object or false if not found.
     */
    public function getImage($imageId)
    {
        return (new \luya\admin\image\Query())->findOne($imageId);
    }

    /**
     * Add a new image based an existing file Id.
     *
     * The storage system uses the same file base, for images and files. The difference between a file and an image is the filter which is applied.
     *
     * Only files of the type image can be used (or added) as an image.
     *
     * An image object is always based on the {{\luya\admin\file\Item}} object and a {{luya\admin\base\Filter}}.
     *
     * ```php
     * Yii::$app->storage->addImage(123, 0); // create an image from file object id 123 without filter.
     * ```
     *
     * @param integer $fileId The id of the file where image should be created from.
     * @param integer $filterId The id of the filter which should be applied to, if filter is 0, no filter will be added. Filter can new also be the string name of the filter like `tiny-crop`.
     * @param boolean $throwException Whether the addImage should throw an exception or just return boolean
     * @return bool|\luya\admin\image\Item|Exception Returns the item object, if an error happens and $throwException is off `false` is returned otherwhise an exception is thrown.
     * @throws \luya\Exception
     */
    public function addImage($fileId, $filterId = 0, $throwException = false)
    {
        try {
            // if the filterId is provded as a string the filter will be looked up by its name in the get filters array list.
            if (is_string($filterId) && !is_numeric($filterId)) {
                $filterLookup = $this->getFiltersArrayItem($filterId);
                if (!$filterLookup) {
                    throw new Exception("The provided filter name " . $filterId . " does not exist.");
                }
                $filterId = $filterLookup['id'];
            }

            $query = (new \luya\admin\image\Query())->where(['file_id' => $fileId, 'filter_id' => $filterId])->one();

            if ($query && $query->fileExists) {
                return $query;
            }

            $fileQuery = $this->getFile($fileId);

            if (!$fileQuery || !$fileQuery->fileExists) {
                if ($fileQuery !== false) {
                    throw new Exception("Unable to create image, the base file server source '{$fileQuery->serverSource}' does not exist.");
                }

                throw new Exception("Unable to find the file with id '{$fileId}', image can not be created.");
            }

            $model = $this->createImage($fileId, $filterId);

            if (!$model) {
                throw new Exception("Unable to create the image on the filesystem.");
            }

            $this->_imagesArray[$model->id] = $model->toArray();
            $this->deleteHasCache(self::CACHE_KEY_IMAGE);

            return $this->getImage($model->id);
        } catch (\Exception $err) {
            if ($throwException) {
                throw $err;
            }
        }

        return false;
    }

    /**
     * Just creating the image based on input informations without usage of storage files or images list.
     *
     * @param integer $fileId The id of the file to create  filter of
     * @param integer $filterId The filter id to apply on the given file.
     * @since 1.2.2.1
     * @return \luya\admin\models\StorageImage|false Returns the storage image model on success, otherwise false.
     */
    public function createImage($fileId, $filterId)
    {
        gc_collect_cycles();

        $image = StorageImage::find()->where(['file_id' => $fileId, 'filter_id' => $filterId])->one();

        // the image exists already in the database and the file system
        if ($image && $image->fileExists) {
            return $image;
        }

        $file = StorageFile::findOne($fileId);

        // https://github.com/luyadev/luya-module-admin/issues/415
        if (!$file && !$file->isImage) {
            return false;
        }
        // create the new image name
        $fileName = $filterId.'_'.$file->name_new_compound;

        $fromTempFile = @tempnam(sys_get_temp_dir(), 'fromFile'); // @see https://www.php.net/manual/de/function.tempnam.php#120451 & https://github.com/luyadev/luya-module-admin/issues/316
        $fromTempFile .= $fileName;

        $content = $file->getContent();

        // it seems the content can not be found.
        if ($content === false) {
            return false;
        }

        $writeFile = FileHelper::writeFile($fromTempFile, $content);

        // unable to write the temp file
        if (!$writeFile) {
            return false;
        }

        // create a temp file
        $tempFile = @tempnam(sys_get_temp_dir(), 'destFile'); // @see https://www.php.net/manual/de/function.tempnam.php#120451 & https://github.com/luyadev/luya-module-admin/issues/316
        $tempFile .= $fileName;

        // there is no filter, which means we create an image version for a given file.
        if (empty($filterId)) {
            @copy($fromTempFile, $tempFile);
        } else {
            $filter = StorageFilter::findOne($filterId);
            if (!$filter || !$filter->applyFilterChain($fromTempFile, $tempFile)) {
                return false;
            }
        }

        $resolution = Storage::getImageResolution($tempFile);
        // now copy the file to the storage system
        $this->fileSystemSaveFile($tempFile, $fileName);
        FileHelper::unlink($tempFile);
        FileHelper::unlink($fromTempFile);

        $this->flushImageArray();

        // ensure the existing of the model
        if ($image) {
            $image->resolution_height = $resolution['height'];
            $image->resolution_width = $resolution['width'];
            $image->save();

            return $image;
        }

        $image = new StorageImage();
        $image->file_id = $fileId;
        $image->filter_id = $filterId;
        $image->resolution_height = $resolution['height'];
        $image->resolution_width = $resolution['width'];
        if (!$image->save()) {
            return false;
        }

        return $image;
    }

    private $_foldersArray;

    /**
     * Get all storage folders as an array from database.
     *
     * This method is used to retrieve all folders from the database and indexed by folder key.
     *
     * @return array An array with all storage folders indexed by the folder id.
     */
    public function getFoldersArray()
    {
        if ($this->_foldersArray === null) {
            $query = (new Query())
                ->from('{{%admin_storage_folder}} as folder')
                ->select(['folder.id', 'name', 'parent_id', 'timestamp_create', 'COUNT(file.id) filesCount'])
                ->where(['folder.is_deleted' => false])
                ->orderBy(['name' => SORT_ASC, 'parent_id' => SORT_ASC])
                ->leftJoin('{{%admin_storage_file}} as file', 'folder.id=file.folder_id AND file.is_deleted = :deleted', [':deleted' => false])
                ->groupBy(['folder.id'])
                ->indexBy(['id']);
            $this->_foldersArray = $this->getQueryCacheHelper($query, self::CACHE_KEY_FOLDER);
        }

        return $this->_foldersArray;
    }

    /**
     * Get a single folder by folder id from the folders array.
     *
     * @param integer $folderId The folder id to find.
     * @return boolean|array The folder array or false if not found.
     */
    public function getFoldersArrayItem($folderId)
    {
        return $this->foldersArray[$folderId] ?? false;
    }

    /**
     * Get an array with all folders based on a where condition.
     *
     * If no argument is provided all images will be returned.
     *
     * See {{\luya\admin\storage\QueryTrait::where()}} for condition informations.
     *
     * @param array $args An array with conditions to match e.g. `['is_hidden' => 1, 'is_deleted' => 0]`.
     * @return \luya\admin\folder\Iterator An iterator object containing all image found for the condition provided.
     */
    public function findFolders(array $args = [])
    {
        return (new \luya\admin\folder\Query())->where($args)->all();
    }

    /**
     * Get a single folder based on a where condition.
     *
     * This method returns a single fpöder matching the where condition, if the multiple results match the condition the first one will be picked.
     *
     * See {{\luya\admin\storage\QueryTrait::where()}} for condition informations.
     *
     * @param array $args An array with conditions to match e.g. `['is_hidden' => 1, 'is_deleted' => 0]`.
     * @return \luya\admin\folder\Item The folder item object.
     */
    public function findFolder(array $args = [])
    {
        return (new \luya\admin\folder\Query())->where($args)->one();
    }

    /**
     * Get a single folder based on the ID.
     *
     * If not found false is returned.
     *
     * @param integer $folderId The requested storage folder id.
     * @return \luya\admin\folder\Item|boolean The folder object or false if not found.
     */
    public function getFolder($folderId)
    {
        return (new \luya\admin\folder\Query())->where(['id' => $folderId])->one();
    }

    /**
     * Add new folder to the storage system.
     *
     * @param string $folderName The name of the new folder
     * @param integer $parentFolderId If its a subfolder the id of the parent folder must be provided.
     * @return boolean|integer Returns the folder id or false if something went wrong.
     */
    public function addFolder($folderName, $parentFolderId = 0)
    {
        $model = new StorageFolder();
        $model->name = $folderName;
        $model->parent_id = $parentFolderId;
        $model->timestamp_create = time();
        $this->deleteHasCache(self::CACHE_KEY_FOLDER);
        if ($model->save(false)) {
            return $model->id;
        }

        return false;
    }

    private $_filtersArray;

    /**
     * Get all storage filters as an array from database.
     *
     * This method is used to retrieve all filters from the database and indexed by filter identifier key.
     *
     * @return array An array with all storage filters indexed by the filter identifier.
     */
    public function getFiltersArray()
    {
        if ($this->_filtersArray === null) {
            $this->_filtersArray = $this->getQueryCacheHelper((new Query())->from('{{%admin_storage_filter}}')->select(['id', 'identifier', 'name'])->indexBy('identifier')->orderBy(['name' => SORT_ASC]), self::CACHE_KEY_FILTER);
        }

        return $this->_filtersArray;
    }

    /**
     * Setter method for filters array.
     *
     * This is mainly used when working with unit tests.
     *
     * @param array $filters
     */
    public function setFiltersArray(array $filters)
    {
        $this->_filtersArray = $filters;
    }

    /**
     * Get a single filter by filter identifier from the filters array.
     *
     * @param integer $filterIdentifier The filter identifier to find use {{luya\admin\base\Filter::identifier()}} method.
     * @return boolean|array The filter array or false if not found.
     */
    public function getFiltersArrayItem($filterIdentifier)
    {
        return $this->filtersArray[$filterIdentifier] ?? false;
    }

    /**
     * Get the filter id based on the identifier.
     *
     * This is a short hand method as its used very often
     *
     * @param string $identifier
     * @return integer
     * @since 1.2.2.1
     */
    public function getFilterId($identifier)
    {
        $filter = $this->getFiltersArrayItem($identifier);
        return $filter ? (int) $filter['id'] : false;
    }

    /**
     * Caching helper method.
     *
     * @param \yii\db\Query $query
     * @param string|array $key
     * @return mixed|boolean
     */
    private function getQueryCacheHelper(\yii\db\Query $query, $key)
    {
        $data = $this->getHasCache($key);

        if ($data === false) {
            $data = $query->all();
            $this->setHasCache($key, $data);
        }

        return $data;
    }

    /**
     * Will force to refresh all container arrays and clean up the cache
     */
    public function flushArrays()
    {
        $this->_filesArray = null;
        $this->_imagesArray = null;
        $this->_foldersArray = null;
        $this->_filtersArray = null;
        $this->deleteHasCache(self::CACHE_KEY_FILE);
        $this->deleteHasCache(self::CACHE_KEY_IMAGE);
        $this->deleteHasCache(self::CACHE_KEY_FOLDER);
        $this->deleteHasCache(self::CACHE_KEY_FILTER);
    }

    /**
     * Flush only images array and image cache.
     *
     * @since 1.2.3
     */
    public function flushImageArray()
    {
        $this->deleteHasCache(self::CACHE_KEY_IMAGE);
    }

    /**
     * This method allwos you to generate all thumbnails for the file manager, you can trigger this process when
     * importing or creating several images at once, so the user does not have to create the thumbnails
     *
     * @return boolean
     */
    public function processThumbnails()
    {
        foreach ($this->findFiles(['is_hidden' => false, 'is_deleted' => false]) as $file) {
            if ($file->isImage) {
                // create tiny thumbnail
                $this->createImage($file->id, $this->getFilterId(TinyCrop::identifier()));
                $this->createImage($file->id, $this->getFilterId(MediumThumbnail::identifier()));
            }
        }

        // force auto fix
        $this->autoFixMissingImageSources = true;

        foreach ($this->findImages() as $image) {
            if (!empty($image->file) && !$image->file->isHidden && !$image->file->isDeleted) {
                $image->source; // which forces to recreate missing sources.
            }
        }

        return true;
    }
}
