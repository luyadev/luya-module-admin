<?php

namespace luya\admin\ngrest\validators;

use luya\admin\ngrest\base\NgRestModelInterface;
use luya\admin\validators\StorageUploadValidator as ValidatorsStorageUploadValidator;
use yii\base\InvalidConfigException;
use yii\helpers\Json;

/**
 * NgRest Model Storage Upload Validator.
 *
 * Compared to the {{luya\admin\validators\StorageUploadValidator}}, this validator will assigned the value
 * suited for NgRest model attributes with {{luya\admin\ngrest\plugins\File}} attribute type.
 *
 * Therefore its required to use for the given attribute:
 *
 * ```php
 * public function ngrestAttributeTypes()
 * {
 *     return [
 *         'my_file_id' => 'file',
 *     ]
 * }
 * ```
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class StorageUploadValidator extends ValidatorsStorageUploadValidator
{
    /**
     * {@inheritDoc}
     */
    public function validateAttribute($model, $attribute)
    {
        if (!$model instanceof NgRestModelInterface) {
            throw new InvalidConfigException("The model must be an instance of NgRestModelInterface.");
        }

        if ($model->getIsNgRestContext()) {
            return;
        }

        $files = $this->uploadToFiles($model, $attribute);

        if (!$this->multiple) {
            // if there has no file uploaded, do nothing.
            // in order to ensure a file needs to be uploaded, use the required validator
            if (empty($files)) {
                return;
            }
            $file = reset($files);
            return $model->$attribute = $file->id;
        }

        $data = [];
        foreach ($files as $save) {
            $data[] = [
                'fileId' => $save->id,
                'caption' => null,
                'hiddenStorageUploadSource' => $save->getLinkAbsolute(),
                'hiddenStorageUploadName' => $save->getName(),
            ];
        }

        $model->$attribute = Json::encode($data);
    }
}
