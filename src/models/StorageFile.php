<?php

namespace luya\admin\models;

use Yii;
use luya\web\Application;
use yii\db\ActiveRecord;
use luya\helpers\FileHelper;

/**
 * This is the model class for table "admin_storage_file".
 *
 * @property integer $id
 * @property boolean $is_hidden
 * @property integer $folder_id
 * @property string $name_original
 * @property string $name_new
 * @property string $name_new_compound
 * @property string $mime_type
 * @property string $extension
 * @property string $hash_file
 * @property string $hash_name
 * @property integer $upload_timestamp
 * @property integer $file_size
 * @property integer $upload_user_id
 * @property integer $is_deleted
 * @property integer $passthrough_file
 * @property string $passthrough_file_password
 * @property integer $passthrough_file_stats
 * @property string $caption
 * @property string $inline_disposition
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
final class StorageFile extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        // call parent
        parent::init();
        
        // ensure upload timestamp and upload_user_id if empty.
        $this->on(self::EVENT_BEFORE_INSERT, function ($event) {
            $this->upload_timestamp = time();
            if (empty($this->upload_user_id)) {
                if (Yii::$app instanceof Application && !Yii::$app->adminuser->isGuest) {
                    $this->upload_user_id = Yii::$app->adminuser->getId();
                }
            }
        });
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'admin_storage_file';
    }
    
    /**
     * @inheritdoc
     */
    public static function find()
    {
        return parent::find()->orderBy(['name_original' => 'ASC']);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name_original', 'name_new', 'mime_type', 'name_new_compound', 'extension', 'hash_file', 'hash_name'], 'required'],
            [['folder_id', 'file_size', 'is_deleted'], 'safe'],
            [['is_hidden'], 'boolean'],
            [['inline_disposition', 'upload_timestamp', 'upload_user_id'], 'integer'],
            [['caption'], 'string'],
        ];
    }
    
    /**
     * Delete a given file.
     *
     * Override default implementation. Mark as deleted and remove files from file system.
     *
     * Keep file in order to provide all file references.
     *
     * @return boolean
     */
    public function delete()
    {
        $file = Yii::$app->storage->getFile($this->id);
        
        if ($file && !Yii::$app->storage->fileSystemDeleteFile($file->systemFileName)) {
            Logger::error("Unable to remove file from filesystem: " . $file->systemFileName);
        }
        
        $this->updateAttributes(['is_deleted' => true]);
        
        return true;
    }
    
    /**
     * Get upload user.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'upload_user_id']);
    }
    
    /**
     * Get all images fro the given file.
     * 
     * @return \yii\db\ActiveQuery
     */
    public function getImages()
    {
        return $this->hasMany(StorageImage::class, ['file_id' => 'id']);
    }
    
    /**
     * Get the file for the corresponding model.
     *
     * @return \luya\admin\file\Item|boolean
     * @since 1.2.0
     */
    public function getFile()
    {
        return Yii::$app->storage->getFile($this->id);
    }
    
    /**
     * @inheritdoc
     */
    public function extraFields()
    {
        return ['user', 'file', 'images'];
    }
}
