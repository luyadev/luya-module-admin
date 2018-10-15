<?php

namespace luya\admin\models;

use Yii;
use yii\db\ActiveRecord;
use luya\helpers\FileHelper;
use luya\admin\filters\TinyCrop;

/**
 * StorageImage Model.
 *
 * @property int $id
 * @property int $file_id
 * @property int $filter_id
 * @property int $resolution_width
 * @property int $resolution_height
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
final class StorageImage extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'admin_storage_image';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['file_id'], 'required'],
            [['filter_id', 'resolution_width', 'resolution_height'], 'safe'],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function fields()
    {
        return ['id', 'file_id', 'filter_id', 'resolution_width', 'resolution_height', 'source'];
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            $this->deleteSource();
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * @return StorageFile
     */
    public function getFile()
    {
        return $this->hasOne(StorageFile::className(), ['id' => 'file_id']);
    }
    
    /**
     * Returns the current file source path for the current filter image.
     * 
     * @return string
     */
    public function getSource()
    {
        return Yii::$app->storage->fileAbsoluteHttpPath($this->filter_id . '_' . $this->file->name_new_compound);
    }
    
    /**
     * Get the path to the source files internal, on the servers path.
     *
     * This is used when you want to to grab the file on server side for example to read the file
     * with `file_get_contents` and is the absolut path on the file system on the server.
     *
     * @return string The path to the file on the filesystem of the server.
     * @since 1.2.2.1
     */
    public function getServerSource()
    {
        return Yii::$app->storage->fileServerPath($this->filter_id . '_' . $this->file->name_new_compound);
    }

    /**
     * Return boolean value whether the file server source exsits on the server or not.
     *
     * @return boolean Whether the file still exists in the storage folder or not.
     * @since 1.2.2.1
     */
    public function getFileExists()
    {
        return Yii::$app->storage->fileSystemExists($this->filter_id . '_' . $this->file->name_new_compound);
    }

    /**
     * Return a storage image object representing the thumbnail which is used for file manager and crud list previews.
     * 
     * > The thumbnail won't be created on the fly! So you have to use storage system to create the thumbnail for the givne filter.
     * > This should have been done already while uploading
     * 
     * @since 1.2.2.1
     */
    public function getThumbnail()
    {
        $tinyCrop = Yii::$app->storage->getFiltersArrayItem(TinyCrop::identifier());
        return $this->hasOne(self::class, ['file_id' => 'file_id'])->andWhere(['filter_id' => $tinyCrop['id']]);
    }

    /**
     * @return boolean
     */
    public function deleteSource()
    {
        $image = Yii::$app->storage->getImage($this->id);
        if ($image) {
            if (!Yii::$app->storage->fileSystemDeleteFile($image->systemFileName)) {
                return false; // unable to unlink image
            }
        } else {
            return false; // image not even found
        }
        
        return true;
    }
    
    /**
     * Expand fields source and thumbnail.
     */
    public function extraFields()
    {
        return ['thumbnail'];
    }
}
