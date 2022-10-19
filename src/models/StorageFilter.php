<?php

namespace luya\admin\models;

use luya\admin\aws\StorageFilterImagesActiveWindow;
use luya\admin\Module;
use luya\admin\ngrest\base\NgRestModel;
use yii\imagine\Image;

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
        return '{{%admin_storage_filter}}';
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
            $source = $img->serverSource;
            $image = $img->delete();

            $log[$source] = $image;
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
        // load resource object before processing chain
        $image = Image::getImagine()->open($source);
        $saveOptions = [];

        foreach (StorageFilterChain::find()->where(['filter_id' => $this->id])->with(['effect'])->all() as $chain) {
            // apply filter
            [$image, $saveOptions] = $chain->applyFilter($image, $saveOptions);
        }

        // auto rotate & save
        $image = Image::autoRotate($image)
            ->save($fileSavePath, $saveOptions);

        unset($image);
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
    public function ngRestScopes()
    {
        return [
            [['list'], ['name', 'identifier']],
        ];
    }

    public function ngRestActiveWindows()
    {
        return [
            ['class' => StorageFilterImagesActiveWindow::class],
        ];
    }
}
