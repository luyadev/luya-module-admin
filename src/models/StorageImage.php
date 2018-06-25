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
     * 
     * @return StorageFile
     */
    public function getFile()
    {
        return $this->hasOne(StorageFile::className(), ['id' => 'file_id']);
    }
    
    /**
     * Returns the current file source path for the current filter image.
     * @return string
     */
    public function getSource()
    {
        $fileName = $this->filter_id . '_' . $this->file->name_new_compound;
        
        return Yii::$app->storage->fileAbsoluteHttpPath($fileName);
    }
    
    public function getThumbnail()
    {
        // @TODO: check what happens on large file systems?
        $tinyCrop = Yii::$app->storage->getFiltersArrayItem(TinyCrop::identifier());
        return Yii::$app->storage->addImage($this->file_id, $tinyCrop['id']);
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
    
    public function extraFields()
    {
        return ['source', 'thumbnail'];
    }
}
