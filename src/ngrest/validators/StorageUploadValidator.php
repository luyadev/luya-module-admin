<?php

namespace luya\admin\ngrest\validators;

use yii\helpers\Json;
use luya\admin\ngrest\base\NgRestModelInterface;
use luya\admin\validators\StorageUploadValidator as ValidatorsStorageUploadValidator;
use yii\base\InvalidConfigException;

/**
 * NgRest Model Storage Upload Validator.
 *
 * Compared to the {{luya\admin\validators\StorageUploadValidator}}, this validator will assigned the value
 * suited for NgRest model attributes with {{luya\admin\ngrest\plugins\File}} attribute type.
 * 
 * Therfore its required to use for the given attribute:
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
