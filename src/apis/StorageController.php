<?php

namespace luya\admin\apis;

use Yii;
use luya\Exception;
use luya\admin\helpers\Storage;
use luya\admin\models\StorageFile;
use luya\admin\models\StorageFolder;
use luya\admin\Module;
use luya\traits\CacheableTrait;
use luya\admin\helpers\I18n;
use luya\admin\base\RestController;
use yii\caching\DbDependency;
use luya\admin\filters\TinyCrop;
use luya\admin\filters\MediumThumbnail;
use luya\helpers\FileHelper;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use luya\admin\models\StorageImage;
use luya\admin\file\Item;
use luya\helpers\ArrayHelper;
use yii\data\ActiveDataProvider;

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
    const PERMISSION_ROUTE = 'admin/storage/index';
    
    /**
     * Flush the storage caching data.
     */
    protected function flushApiCache($folderId = 0, $page = 0)
    {
        Yii::$app->storage->flushArrays();
        $this->deleteHasCache('storageApiDataFolders');
        $this->deleteHasCache(['storageApiDataFiles', (int) $folderId, (int) $page]);
    }
    
    // DATA READERS

    /**
     * Get all folders from the storage component.
     *
     * @return array
     */
    public function actionDataFolders()
    {
        return $this->getOrSetHasCache('storageApiDataFolders', function() {
            $folders = [];
            foreach (Yii::$app->storage->findFolders() as $key => $folder) {
                $folders[$key] = $folder->toArray();
                $folders[$key]['toggle_open'] = (int) Yii::$app->adminuser->identity->setting->get('foldertree.'.$folder->id);
                $folders[$key]['subfolder'] = Yii::$app->storage->getFolder($folder->id)->hasChild();
            }
            return $folders;
        }, 0, new DbDependency(['sql' => 'SELECT MAX(id) FROM admin_storage_folder WHERE is_deleted=false']));
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
            $query->andFilterWhere(['or', ['like', 'name_original', $search], ['like', 'caption', $search]]);
        } else {
            $query->andWhere(['folder_id' => $folderId]);
        }

        return new ActiveDataProvider([
            'query' => $query,
        ]);
    }
    
    // ACTIONS
    
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
        $model = StorageFile::find()->where(['id' => $id])->with(['user', 'images'])->one();
        
        if (!$model) {
            throw new NotFoundHttpException("Unable to find the given storage file.");
        }
        
        return $model->toArray([], ['user', 'file', 'images', 'source']);
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
        $this->checkRouteAccess(self::PERMISSION_ROUTE);
        
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
     * @return array
     */
    public function actionImageFilter()
    {
        $this->checkRouteAccess(self::PERMISSION_ROUTE);
        try {
            $create = Yii::$app->storage->createImage(Yii::$app->request->post('fileId', null), Yii::$app->request->post('filterId', null), true);
            if ($create) {
                return [
                    'error' => false, 
                    'id' => $create->id,
                ];
            }
        } catch (Exception $err) {
            return $this->sendArrayError([
                'error' => true, 
                'message' => Module::t('api_storage_image_upload_error'),
            ]);
        }
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
        $this->checkRouteAccess(self::PERMISSION_ROUTE);
        
        $fileId = Yii::$app->request->post('fileId', false);
        $pageId = Yii::$app->request->post('pageId', 0);
        Yii::warning('replace request for file id' . $fileId, __METHOD__);
        $raw = $_FILES['file'];
        /** @var $file \luya\admin\file\Item */
        if ($file = Yii::$app->storage->getFile($fileId)) {
            $newFileSource = $raw['tmp_name'];
            if (is_uploaded_file($newFileSource)) {
                
                // check for same extension / mimeType
                $fileData = Yii::$app->storage->ensureFileUpload($raw['tmp_name'], $raw['name']);
                
                if ($fileData['mimeType'] != $file->mimeType) {
                    throw new BadRequestHttpException("The type must be the same as the original file in order to replace.");
                }
                
                if (Storage::replaceFile($file->systemFileName, $newFileSource, $raw['name'])) {
                    foreach (StorageImage::find()->where(['file_id' => $file->id])->all() as $img) {
                        $removal = Storage::removeImage($img->id, false);
                    }
                    
                    // calculate new file files based on new file
                    $model = StorageFile::findOne((int) $fileId);
                    $fileHash = FileHelper::md5sum($newFileSource);
                    $fileSize = @filesize($newFileSource);
                    $model->updateAttributes([
                        'hash_file' => $fileHash,
                        'file_size' => $fileSize,
                        'upload_timestamp' => time(),
                    ]);
                    $this->flushApiCache($model->folder_id, $pageId);
                    return true;
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
     * @return array An array with upload and message key.
    */
    public function actionFilesUpload()
    {
        $this->checkRouteAccess(self::PERMISSION_ROUTE);
        
        foreach ($_FILES as $k => $file) {
            if ($file['error'] !== UPLOAD_ERR_OK) {
                Yii::$app->response->setStatusCode(422, 'Data Validation Failed.');
                return ['upload' => false, 'message' => Storage::getUploadErrorMessage($file['error']), 'file' => null];
            }
            try {
                $response = Yii::$app->storage->addFile($file['tmp_name'], $file['name'], Yii::$app->request->post('folderId', 0), Yii::$app->request->post('isHidden', false));
                if ($response) {
                    return ['upload' => true, 'message' => Module::t('api_storage_file_upload_succes'), 'file' => $response];
                } else {
                    Yii::$app->response->setStatusCode(422, 'Data Validation Failed.');
                    return ['upload' => false, 'message' => Module::t('api_storage_file_upload_folder_error'), 'file' => null];
                }
            } catch (Exception $err) {
                Yii::$app->response->setStatusCode(422, 'Data Validation Failed.');
                return ['upload' => false, 'message' => Module::t('api_sotrage_file_upload_error', ['error' => $err->getMessage()]), 'file' => null];
            }
        }
    
        // If the files array is empty, this is an indicator for exceeding the upload_max_filesize from php ini or a wrong upload defintion.
        Yii::$app->response->setStatusCode(422, 'Data Validation Failed.');
        return ['upload' => false, 'message' => Storage::getUploadErrorMessage(UPLOAD_ERR_NO_FILE), 'file' => null];
    }
    
    /**
     * Move files into another folder.
     *
     * @return boolean
     */
    public function actionFilemanagerMoveFiles()
    {
        $this->checkRouteAccess(self::PERMISSION_ROUTE);
        
        $toFolderId = Yii::$app->request->post('toFolderId', 0);
        $fileIds = Yii::$app->request->post('fileIds', []);
        
        $currentPageId = Yii::$app->request->post('currentPageId', 0);
        $currentFolderId = Yii::$app->request->post('currentFolderId', 0);
        
        $response = Storage::moveFilesToFolder($fileIds, $toFolderId);
        $this->flushApiCache($currentFolderId, $currentPageId);
        $this->flushApiCache($toFolderId, $currentPageId);
        $this->flushHasCache($toFolderId, 0);
        return $response;
    }
    
    /**
     * Remove files from the storage component.
     *
     * @todo make permission check.
     * @return boolean
     */
    public function actionFilemanagerRemoveFiles()
    {
        $this->checkRouteAccess(self::PERMISSION_ROUTE);
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
            'empty' => $count > 0  ? false : true,
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
     * @todo move to storage helpers?
     * @return boolean
     */
    public function actionFolderDelete($folderId)
    {
        $this->checkRouteAccess(self::PERMISSION_ROUTE);
        
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
        $this->checkRouteAccess(self::PERMISSION_ROUTE);
        
        $model = StorageFolder::findOne($folderId);
        if (!$model) {
            return false;
        }
        $model->attributes = Yii::$app->request->post();
    
        $this->flushApiCache();
        
        return $model->update();
    }
    
    /**
     * Create a new folder pased on post data.
     *
     * @return boolean
     */
    public function actionFolderCreate()
    {
        $this->checkRouteAccess(self::PERMISSION_ROUTE);
        
        $folderName = Yii::$app->request->post('folderName', null);
        $parentFolderId = Yii::$app->request->post('parentFolderId', 0);
        $response = Yii::$app->storage->addFolder($folderName, $parentFolderId);
        $this->flushApiCache();
        
        return $response;
    }
}
