<?php

namespace luya\admin\models;

use Yii;
use luya\admin\file\Item;
use luya\admin\ngrest\base\NgRestModel;
use luya\admin\Module;
use luya\admin\aws\StorageFilterImagesActiveWindow;

/**
 * This is the model class for table "admin_storage_filter".
 *
 * @property integer $id
 * @property string $identifier
 * @property string $name
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
final class StorageFilter extends NgRestModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'admin_storage_filter';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['identifier'], 'required'],
            [['identifier'], 'string', 'max' => 100],
            [['name'], 'string', 'max' => 255],
            [['identifier'], 'unique'],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'identifier' => Module::t('model_storagefilter_identifier'),
            'name' => Module::t('model_storagefilter_name'),
        ];
    }

    /**
     * Remove image sources of the file.
     */
    public function removeImageSources()
    {
        $log = [];
        foreach (StorageImage::find()->where(['filter_id' => $this->id])->all() as $img) {
            $image = Yii::$app->storage->getImage($img->id);
            
            $log[$image->serverSource] = $img->deleteSource();
        }
        return $log;
    }
    
    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            foreach (StorageImage::find()->where(['filter_id' => $this->id])->all() as $img) {
                $img->delete();
            }
            return true;
        }
        
        return false;
    }

    /**
     * Apply the current filter chain.
     *
     * Apply all filters from the chain to a given file and stores the new generated file on the $fileSavePath
     *
     * @param string $source the Source path to the file, which the filter chain should be applied to.
     * @param string $fileSavePath
     * @return boolean
     */
    public function applyFilterChain($source, $fileSavePath)
    {
        $loadFrom = $source;
        //$loadFrom = $file->getServerSource();
        
        foreach (StorageFilterChain::find()->where(['filter_id' => $this->id])->with(['effect'])->all() as $chain) {
            // apply filter
            $response = $chain->applyFilter($loadFrom, $fileSavePath);
            // override load from path for next iteration (if any).
            $loadFrom = $fileSavePath;
        }
        
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function ngRestApiEndpoint()
    {
        return 'api-admin-filter';
    }

    /**
     * @inheritdoc
     */
    public function ngRestAttributeTypes()
    {
        return [
            'name' => 'text',
            'identifier' => 'text',
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function ngRestConfig($config)
    {
        $config->aw->load(StorageFilterImagesActiveWindow::class);
        
        $this->ngRestConfigDefine($config, 'list', ['name', 'identifier']);
        
        return $config;
    }
}
