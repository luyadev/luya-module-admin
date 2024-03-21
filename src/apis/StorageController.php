<?php

namespace luya\admin\apis;

use InvalidArgumentException;
use luya\admin\base\RestController;
use luya\admin\events\FileEvent;
use luya\admin\filters\MediumThumbnail;
use luya\admin\filters\TinyCrop;
use luya\admin\helpers\I18n;
use luya\admin\helpers\Storage;
use luya\admin\models\StorageFile;
use luya\admin\models\StorageFolder;
use luya\admin\models\StorageImage;
use luya\admin\models\TagRelation;
use luya\admin\Module;
use luya\admin\storage\BaseFileSystemStorage;
use luya\Exception;
use luya\traits\CacheableTrait;
use Yii;
use yii\base\Action;
use yii\caching\DbDependency;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

/**
 * Filemanager and Storage API.
 *
 * Storage API, provides data from system image, files, filters and folders to build the filemanager, allows create/delete process to manipulate storage data.
 *
 * The storage controller is used to make the luya angular file manager work with the {{luya\admin\storage\BaseFileSystemStorage}}.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class StorageController extends RestController
{
    use CacheableTrait;

    /**
     * @var string The route which is used in the permission system
     */
    public const PERMISSION_ROUTE = 'admin/storage/index';

    /**
     * @var array A list of action ids which are whiteliste and does not require the file manager permission.
     * @since 2.3.0
     */
    protected $whitelistedActions = ['data-folders', 'data-files', 'data-filters'];

    /**
     * {@inheritDoc}
     */
    public function permissionRoute(Action $action)
    {
        // whiteliste certain data endpoints from permission system as this would trigger a user logout
        // if people without file permission visit any NgRest CRUD view.
        if (in_array($action->id, $this->whitelistedActions)) {
            return false;
        }

        return self::PERMISSION_ROUTE;
    }

    // DATA READERS

    /**
     * Get all folders from the storage component.
     *
     * @return array
     */
    public function actionDataFolders()
    {
        return $this->getOrSetHasCache('storageApiDataFolders', function () {
            $folders = [];
            foreach (Yii::$app->storage->findFolders() as $key => $folder) {
                $folders[$key] = $folder->toArray();
                $folders[$key]['toggle_open'] = (int) Yii::$app->adminuser->identity->setting->get('foldertree.'.$folder->id);
                $folders[$key]['subfolder'] = Yii::$app->storage->getFolder($folder->id)->hasChild();
            }
            return $folders;
        }, 0, new DbDependency(['sql' => 'SELECT MAX(id) FROM {{%admin_storage_folder}} WHERE is_deleted=false']));
    }

    /**
     * Get all files from the storage container.
     *
     * @return array
     */
    public function actionDataFiles($folderId = 0, $search = null)
    {
        $query = StorageFile::find()
            ->select(['id', 'name_original', 'extension', 'upload_timestamp', 'file_size', 'mime_type'])
            ->where(['is_hidden' => false, 'is_deleted' => false])
            ->with(['images.file']);

        if (!empty($search)) {
            $query->andFilterWhere(['or',
                ['like', 'name_original', $search],
                ['like', 'caption', $search],
                ['=', 'id', $search],
            ]);
        } else {
            $query->andWhere(['folder_id' => $folderId]);
        }

        return new ActiveDataProvider([
            'query' => $query,
        ]);
    }

    /**
     * Toggle Tags for a given file.
     *
     * If a relation exists, remove, otherwise add.
     *
     * @return The array of associated tags for the given file.
     * @since 2.0.0
     */
    public function actionToggleFileTag()
    {
        $tagId = Yii::$app->request->getBodyParam('tagId');
        $fileId = Yii::$app->request->getBodyParam('fileId');

        $file = StorageFile::findOne($fileId);

        if (!$file) {
            throw new NotFoundHttpException("Unable to find the given file to toggle the tag.");
        }

        $relation = TagRelation::find()->where(['table_name' => StorageFile::cleanBaseTableName(StorageFile::tableName()), 'pk_id' => $fileId, 'tag_id' => $tagId])->one();

        if ($relation) {
            $relation->delete();

            return $file->tags;
        }

        $model = new TagRelation();
        $model->table_name = StorageFile::cleanBaseTableName(StorageFile::tableName());
        $model->pk_id = $fileId;
        $model->tag_id = $tagId;

        $model->save();
        return $file->tags;
    }

    /**
     * Get all storage file informations for a given ID.
     *
     * @param integer $fileId
     * @throws NotFoundHttpException
     * @return array
     * @since 1.2.0
     */
    public function actionFileInfo($id)
    {
        $model = StorageFile::find()->where(['id' => $id])->with(['user', 'images', 'tags'])->one();

        if (!$model) {
            throw new NotFoundHttpException("Unable to find the given storage file.");
        }

        return $model->toArray([], ['user', 'file', 'images', 'source', 'tags']);
    }

    /**
     * Create or replace a certain file based on new cropped image informations.
     *
     * @return Item
     * @since 3.1.0
     */
    public function actionFileCrop()
    {
        $data = Yii::$app->request->getBodyParam('distImage');
        $fileName = Yii::$app->request->getBodyParam('fileName');
        $ext = Yii::$app->request->getBodyParam('extension');

        $saveAsCopy = Yii::$app->request->getBodyParam('saveAsCopy');
        $fileId = Yii::$app->request->getBodyParam('fileId');

        $file = StorageFile::findOne($fileId);

        if (empty($data) || empty($fileName) || empty($ext)) {
            throw new InvalidArgumentException("Invalid Params");
        }

        [$type, $data] = explode(';', $data);
        [, $data] = explode(',', $data);
        $data = base64_decode($data);

        if (!$saveAsCopy && $fileId) {
            Storage::replaceFileFromContent($file->name_new_compound, $data);
            return Storage::refreshFile($fileId, $file->getServerSource());
        }

        return Storage::uploadFromContent($data, $file->name_new .'_copy.'.$ext, $file->folder_id);
    }

    /**
     * Get file model.
     *
     * This is mainly used for external api access.
     *
     * @param integer $id
     * @return StorageFile
     * @since 1.2.3
     */
    public function actionFile($id)
    {
        $model = StorageFile::find()->where(['id' => $id])->one();

        if (!$model) {
            throw new NotFoundHttpException("Unable to find the given storage file.");
        }

        return $model;
    }

    /**
     * Get image model.
     *
     * This is mainly used for external api access.
     *
     * @param integer $id
     * @return StorageImage
     * @since 1.2.3
     */
    public function actionImage($id)
    {
        $model = StorageImage::find()->where(['id' => $id])->with(['file'])->one();

        if (!$model) {
            throw new NotFoundHttpException("Unable to find the given storage image.");
        }

        return $model;
    }

    /**
     *
     * @param integer $id
     * @throws NotFoundHttpException
     * @return array
     * @since 1.2.2
     */
    public function actionImageInfo($id)
    {
        $model = StorageImage::find()->where(['id' => $id])->with(['file', 'tinyCropImage.file'])->one();

        if (!$model) {
            throw new NotFoundHttpException("Unable to find the given storage image.");
        }

        // try to create thumbnail on view if not done
        if (empty($model->tinyCropImage)) {
            // there are very rare cases where the thumbnail does not exists, therefore generate the thumbnail and reload the model.
            Yii::$app->storage->createImage($model->file_id, Yii::$app->storage->getFiltersArrayItem(TinyCrop::identifier())['id']);
            // refresh model internal (as $model->refresh() wont load the relations data we have to call the same model with relations again)
            $model = StorageImage::find()->where(['id' => $id])->with(['file', 'tinyCropImage.file'])->one();
        }

        return $model->toArray(['id', 'source', 'file_id', 'filter_id', 'resolution_width', 'resolution_height', 'file'], ['source', 'tinyCropImage.file']);
    }

    /**
     * A post request with an array of images to load!
     *
     *
     * @since 1.2.2.1
     */
    public function actionImagesInfo()
    {
        $ids = Yii::$app->request->getBodyParam('ids', []);
        $ids = array_unique($ids);
        return new ActiveDataProvider([
            'query' => StorageImage::find()->where(['in', 'id', $ids])->with(['file', 'tinyCropImage.file']),
            'pagination' => false,
        ]);
    }

    /**
     *
     * @param integer $id
     * @throws NotFoundHttpException
     * @return array
     * @since 1.2.0
     */
    public function actionFileUpdate($id, $pageId = 0)
    {
        $model = StorageFile::find()->where(['id' => $id])->with(['user'])->one();

        if (!$model) {
            throw new NotFoundHttpException("Unable to find the given storage file.");
        }

        $post = Yii::$app->request->bodyParams;
        $model->attributes = $post;

        if ($model->update(true, ['name_original', 'inline_disposition']) !== false) {
            Yii::$app->storage->trigger(BaseFileSystemStorage::FILE_UPDATE_EVENT, new FileEvent(['file' => $model]));

            $this->flushApiCache($model->folder_id, $pageId);
            return $model;
        }

        return $this->sendModelError($model);
    }

    /**
     * Update the caption of storage file.
     *
     * @return boolean
     */
    public function actionFilemanagerUpdateCaption()
    {
        $fileId = Yii::$app->request->post('id', false);
        $captionsText = Yii::$app->request->post('captionsText', false);
        $pageId = Yii::$app->request->post('pageId', 0);

        if ($fileId && is_scalar($fileId) && $captionsText) {
            $model = StorageFile::findOne($fileId);
            if ($model) {
                $model->updateAttributes([
                    'caption' => I18n::encode($captionsText),
                ]);

                $this->flushApiCache($model->folder_id, $pageId);

                return true;
            }
        }

        return false;
    }

    /**
     * Upload an image to the filemanager.
     *
     * @return array An array with
     * - error: Whether an error occured or not.
     * - id: The id of the image
     * - image: The image object (since 2.0)
     */
    public function actionImageFilter()
    {
        $image = Yii::$app->storage->createImage(Yii::$app->request->post('fileId', null), Yii::$app->request->post('filterId', null));
        if ($image) {
            return [
                'error' => false,
                'id' => $image->id,
                'image' => $image
            ];
        }

        return $this->sendArrayError([
            'error' => true,
            'message' => Module::t('api_storage_image_upload_error', ['error' => 'Unable to create the filter for the given image. Maybe the file source is not readable.']),
        ]);
    }

    /**
     * Get all available registered filters.
     *
     * @return array
     */
    public function actionDataFilters()
    {
        return Yii::$app->storage->filtersArray;
    }

    /**
     * Action to replace a current file with a new.
     *
     * @return boolean
     */
    public function actionFileReplace()
    {
        $fileId = Yii::$app->request->post('fileId', false);
        $raw = $_FILES['file'] ?? false;
        /** @var $file \luya\admin\file\Item */
        if ($raw && $file = Yii::$app->storage->getFile($fileId)) {
            $newFileSource = $raw['tmp_name'];
            if (is_uploaded_file($newFileSource)) {
                // check for same extension / mimeType
                $fileData = Yii::$app->storage->ensureFileUpload($raw['tmp_name'], $raw['name']);

                if ($fileData['mimeType'] != $file->mimeType) {
                    throw new BadRequestHttpException("The type must be the same as the original file in order to replace.");
                }

                if (Storage::replaceFile($file->systemFileName, $newFileSource, $raw['name'])) {
                    return Storage::refreshFile($file->id, $newFileSource);
                }
            }
        }

        return false;
    }

    /**
     * Image Upload with $_FILES array:
     *
     * Post values:
     * + file
     * + folderId
     * + isHidden
     *
     * @return array
     * @since 1.2.3
     */
    public function actionImagesUpload()
    {
        $result = $this->actionFilesUpload();

        if ($result['upload'] && $result['file']) {
            $fileId = $result['file']->id;

            $image = Yii::$app->storage->createImage($fileId, 0);

            if ($image) {
                // create system thumbnails
                $tinyCrop = Yii::$app->storage->createImage($fileId, Yii::$app->storage->getFilterId(TinyCrop::identifier()));
                $mediumThumbnail = Yii::$app->storage->createImage($fileId, Yii::$app->storage->getFilterId(MediumThumbnail::identifier()));
            }

            return [
                'image' => $image,
                'tinyCrop' => $tinyCrop,
                'mediumThumbnail' => $mediumThumbnail,
            ];
        }

        return $this->sendArrayError(['image' => 'Unable to create the given with and the corresponding filters.']);
    }

    /**
     * Upload a new file from $_FILES array.
     *
     * Post Values:
     *
     * + file
     * + folderId
     * + isHidden
     *
     * @return array An array with an assoc array containing the following keys
     * + upload: boolean, whether upload was sucessfull or not
     * + message: string, a message which describes the upload status. Even a success upload contains a message
     * + file: {{luya\admin\file\Item}}, the file item if successfull
     * + queueIds: array, a list with queue job ids if things need to be processed by queue (for example filters).
    */
    public function actionFilesUpload()
    {
        foreach ($_FILES as $k => $file) {
            if ($file['error'] !== UPLOAD_ERR_OK) {
                Yii::$app->response->setStatusCode(422, 'Data Validation Failed.');
                return ['upload' => false, 'message' => Storage::getUploadErrorMessage($file['error']), 'file' => null, 'queueIds' => []];
            }
            try {
                $response = Yii::$app->storage->addFile($file['tmp_name'], $file['name'], Yii::$app->request->post('folderId', 0), Yii::$app->request->post('isHidden', false));
                if ($response) {
                    return [
                        'upload' => true,
                        'message' => Module::t('api_storage_file_upload_succes'),
                        'file' => $response,
                        'queueIds' => Yii::$app->storage->queueJobIds,
                    ];
                } else {
                    Yii::$app->response->setStatusCode(422, 'Data Validation Failed.');
                    return ['upload' => false, 'message' => Module::t('api_storage_file_upload_folder_error'), 'file' => null, 'queueIds' => []];
                }
            } catch (Exception $err) {
                Yii::$app->response->setStatusCode(422, 'Data Validation Failed.');
                return ['upload' => false, 'message' => Module::t('api_sotrage_file_upload_error', ['error' => $err->getMessage()]), 'file' => null, 'queueIds' => []];
            }
        }

        // If the files array is empty, this is an indicator for exceeding the upload_max_filesize from php ini or a wrong upload definition.
        Yii::$app->response->setStatusCode(422, 'Data Validation Failed.');
        return ['upload' => false, 'message' => Storage::getUploadErrorMessage(UPLOAD_ERR_NO_FILE), 'file' => null, 'queueIds' => []];
    }

    /**
     * Move files into another folder.
     *
     * @return boolean
     */
    public function actionFilemanagerMoveFiles()
    {
        $toFolderId = Yii::$app->request->post('toFolderId', 0);
        $fileIds = Yii::$app->request->post('fileIds', []);

        $currentPageId = Yii::$app->request->post('currentPageId', 0);
        $currentFolderId = Yii::$app->request->post('currentFolderId', 0);

        $response = Storage::moveFilesToFolder($fileIds, $toFolderId);
        $this->flushApiCache($currentFolderId, $currentPageId);
        $this->flushApiCache($toFolderId, $currentPageId);
        $this->flushHasCache();
        return $response;
    }

    /**
     * Remove files from the storage component.
     *
     * @return boolean
     */
    public function actionFilemanagerRemoveFiles()
    {
        $pageId = Yii::$app->request->post('pageId', 0);
        $folderId = Yii::$app->request->post('folderId', 0);
        foreach (Yii::$app->request->post('ids', []) as $id) {
            if (!Storage::removeFile($id)) {
                return false;
            }
        }
        $this->flushApiCache($folderId, $pageId);
        return true;
    }

    /**
     * Check whether a folder is empty or not in order to delete this folder.
     *
     * @param integer $folderId The folder id to check whether it has files or not.
     * @return boolean
     */
    public function actionIsFolderEmpty($folderId)
    {
        $count = StorageFile::find()->where(['folder_id' => $folderId, 'is_deleted' => false])->count();

        return [
            'count' => $count,
            'empty' => $count > 0 ? false : true,
        ];
    }

    /**
     * delete folder, all subfolders and all included files.
     *
     * 1. search another folders with matching parentIds and call deleteFolder on them
     * 2. get all included files and delete them
     * 3. delete folder
     *
     * @param integer $folderId The folder to delete.
     * @return boolean
     */
    public function actionFolderDelete($folderId)
    {
        // find all subfolders
        $matchingChildFolders = StorageFolder::find()->where(['parent_id' => $folderId])->asArray()->all();
        foreach ($matchingChildFolders as $matchingChildFolder) {
            $this->actionFolderDelete($matchingChildFolder['id']);
        }

        // find all attached files and delete them
        $folderFiles = StorageFile::find()->where(['folder_id' => $folderId])->all();
        foreach ($folderFiles as $folderFile) {
            $folderFile->delete();
        }

        // delete folder
        $model = StorageFolder::findOne($folderId);
        if (!$model) {
            return false;
        }
        $model->is_deleted = true;

        $this->flushApiCache();

        return $model->update();
    }

    /**
     * Update the folder model data.
     *
     * @param integer $folderId The folder id.
     * @return boolean
     */
    public function actionFolderUpdate($folderId)
    {
        $model = StorageFolder::findOne($folderId);
        if (!$model) {
            return false;
        }
        $model->attributes = Yii::$app->request->post();

        $this->flushApiCache();

        $model->update();

        return $model;
    }

    /**
     * Create a new folder pased on post data.
     *
     * @return boolean
     */
    public function actionFolderCreate()
    {
        $folderName = Yii::$app->request->post('folderName', null);
        $parentFolderId = Yii::$app->request->post('parentFolderId', 0);
        $response = Yii::$app->storage->addFolder($folderName, $parentFolderId);
        $this->flushApiCache();

        return $response;
    }

    /**
     * Flush the storage caching data.
     */
    protected function flushApiCache($folderId = 0, $page = 0)
    {
        Yii::$app->storage->flushArrays();
        $this->deleteHasCache('storageApiDataFolders');
        $this->deleteHasCache(['storageApiDataFiles', (int) $folderId, (int) $page]);
    }
}
