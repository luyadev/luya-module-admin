<?php

namespace luya\admin\validators;

use luya\base\DynamicModel;
use luya\helpers\Json;
use Yii;
use yii\base\Model;
use yii\validators\Validator;
use yii\web\UploadedFile;

/**
 * Storage Upload Validator.
 *
 * Storing Files into the storage system and retrieve certain informations from the file, which will
 * then be assigned to the model attributes value. In order to upload files in frontend scenarios with
 * [[ngrest-model.md]] use {{luya\admin\ngrest\validators\StorageUploadValidator}} instead.
 *
 * This implementation will assigned the aboluste source path from {{luya\admin\file\Item}} to the
 * attribute in the model attribute, after successfull upload and validation.
 *
 * ### Single File Upload
 *
 * Model rule:
 *
 * ```php
 * [['attachment'], StorageUploadValidator::class],
 * ```
 *
 * View File:
 *
 * ```php
 * <?= $form->field($model, 'attachment')->fileInput(['accept' => 'file/*']) ?>
 * ```
 *
 * ### Multiple Files Upload
 *
 * Mode rule:
 *
 * ```php
 * [['attachments'], StorageUploadValidator::class, 'multiple' => true],
 * ```
 *
 * View File:
 *
 * ```php
 * <?= $form->field($model, 'attachments[]')->fileInput(['multiple' => true, 'accept' => 'file/*']) ?>
 * ```
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 3.6.0
 */
class StorageUploadValidator extends Validator
{
    /**
     * @inheritdoc
     */
    public $skipOnEmpty = false;

    /**
     * @var boolean Whether its possible to upload multiple files or just a single file
     */
    public $multiple = false;

    /**
     * @var integer The folder id where all files will be uploaded to, this is the virtual directory number from {{luya\admin\componenets\StorageContainer}}. Defaults
     * is 0 which is the root directory of the file manager. If {{isHidden}} is enabled, the folder id does not matter as its not shown in the file manager anyhow.
     */
    public $folderId = 0;

    /**
     * @var boolean Whether the files should be visible inside the file manager or not.
     */
    public $isHidden = true;

    /**
     * {@inheritDoc}
     */
    public function validateAttribute($model, $attribute)
    {
        $files = $this->uploadToFiles($model, $attribute);

        if (empty($files) || $model->hasErrors($attribute)) {
            return;
        }

        if (!$this->multiple) {
            $file = reset($files);
            return $model->$attribute = $file->getSourceAbsolute();
        }

        $data = [];
        foreach ($files as $save) {
            $data[] = $save->getSourceAbsolute();
        }

        $model->$attribute = Json::encode($data);
    }

    /**
     * Files to Storage Item
     *
     * @param Model $model
     * @param string $attribute
     * @return array
     */
    public function uploadToFiles($model, $attribute)
    {
        $files = $this->multiple ? UploadedFile::getInstances($model, $attribute) : UploadedFile::getInstance($model, $attribute);

        $contextModel = new DynamicModel(['file' => $files]);
        $contextModel->addRule(['file'], 'file', ['maxFiles' => $this->multiple ? 0 : 1])->validate();

        if ($contextModel->hasErrors()) {
            return $this->addError($model, $attribute, $contextModel->getFirstError('file'));
        }

        if (!is_array($files)) {
            $files = is_null($files) ? [] : [$files];
        }

        $data = [];
        foreach ($files as $file) {
            $name = $file->baseName . '.' . $file->extension;
            $file = Yii::$app->storage->addFile($file->tempName, $name, $this->folderId, $this->isHidden);
            if ($file) {
                $data[] = $file;
            } else {
                $this->addError($model, $attribute, "Unable to save the file {$name} on the server.");
            }
        }

        return $data;
    }
}
