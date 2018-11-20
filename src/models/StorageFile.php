<?php

namespace luya\admin\models;

use Yii;
use luya\web\Application;
use yii\db\ActiveRecord;
use luya\helpers\FileHelper;
use luya\admin\behaviors\LogBehavior;
use luya\admin\filters\TinyCrop;
use luya\admin\filters\MediumThumbnail;
use luya\admin\traits\TaggableTrait;
use luya\admin\helpers\I18n;

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
    use TaggableTrait;
    
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
    public function behaviors()
    {
        return [
            LogBehavior::class,
        ];
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
        return parent::find();
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
        if ($this->beforeDelete()) {
        
            if (!Yii::$app->storage->fileSystemDeleteFile($this->name_new_compound)) {
                Logger::error("Unable to remove file from filesystem: " . $this->name_new_compound);
            }
            
            $this->updateAttributes(['is_deleted' => true]);
            
            $this->afterDelete();
            return true;
        }
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
     * Returns the current file source path for the current filter image.
     * @return string
     */
    public function getSource()
    {
        return Yii::$app->storage->fileAbsoluteHttpPath($this->name_new_compound);
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
        return Yii::$app->storage->fileServerPath($this->name_new_compound);
    }

    /**
     * Get the size of a file in human readable size.
     *
     * For example sizes are partial splitet in readable forms:
     *
     * + 100B
     * + 100KB
     * + 10MB
     * + 1GB
     *
     * @return string The humand readable size.
     * @since 1.2.2.1
     */
    public function getSizeReadable()
    {
        return FileHelper::humanReadableFilesize($this->file_size);
    }

    /**
     * Whether the file is of type image or not.
     *
     * If the mime type of the files is equals to:
     *
     * + `image/gif`
     * + `image/jpeg`
     * + `image/jpg`
     * + `image/png`
     * + `image/bmp`
     * + `image/tiff`
     *
     * The file indicates to be an image and return value is true.
     *
     * @return boolean Whether the file is of type image or not.
     * @since 1.2.2.1
     */
    public function getIsImage()
    {
        return in_array($this->mime_type, Yii::$app->storage->imageMimeTypes);
    }

    /**
     * Create the thumbnail for this given file if its an image.
     * 
     * > This method is used internal when uploading a file which is an image, the file manager preview images are created here.
     *
     * @return array Returns an array with the key source which contains the source to the thumbnail.
     * @since 1.2.2.1
     */
    public function getCreateThumbnail()
    {
        if (!$this->isImage) {
            return false;
        }

        $tinyCrop = Yii::$app->storage->getFilterId(TinyCrop::identifier());
        foreach ($this->images as $image) {
            if ($image->filter_id == $tinyCrop) {
                return ['source' => $image->source];
            }
        }

        // create the thumbnail on the fly if not existing
        $image = Yii::$app->storage->createImage($this->id, $tinyCrop);
        if ($image) {
            return ['source' => $image->source];
        }
    }

    /**
     * Create the thumbnail medium for this given file if its an image.
     * 
     * > This method is used internal when uploading a file which is an image, the file manager preview images are created here.
     *
     * @return array Returns an array with the key source which contains the source to the thumbnail medium.
     * @since 1.2.2.1
     */
    public function getCreateThumbnailMedium()
    {
        if (!$this->isImage) {
            return false;
        }
        $mediumThumbnail = Yii::$app->storage->getFilterId(MediumThumbnail::identifier());

        foreach ($this->images as $image) {
            if ($image->filter_id == $mediumThumbnail) {
                return ['source' => $image->source];
            }
        }

        // create the thumbnail on the fly if not existing
        $image = Yii::$app->storage->createImage($this->id, $mediumThumbnail);
        if ($image) {
            return ['source' => $image->source];
        }
    }

    /**
     * Get the parsed response for a file caption as expand.
     * 
     * @since 1.2.3
     * @return string The caption parsed for the current input langauge.
     */
    public function getCaption()
    {
        return I18n::decodeFindActive($this->caption);
    }

    /**
     * @inheritdoc
     */
    public function extraFields()
    {
        return ['user', 'file', 'images', 'createThumbnail', 'createThumbnailMedium', 'isImage', 'sizeReadable', 'source', 'caption'];
    }
}
