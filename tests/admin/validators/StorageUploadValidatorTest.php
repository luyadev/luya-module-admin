<?php

namespace luya\admin\tests\admin\validators;

use admintests\AdminModelTestCase;
use luya\admin\validators\StorageUploadValidator;
use luya\base\DynamicModel;

class StorageUploadValidatorTest extends AdminModelTestCase
{
    public function testValidateAttribute()
    {
        $validator = new StorageUploadValidator();

        $model = new DynamicModel(['file_id' => 0]);
        $this->createAdminStorageFileFixture();
        $this->createAdminNgRestLogFixture();

        $_FILES['DynamicModel[file_id]'] = [
            'name' => 'MyFile.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => dirname(__DIR__) . '/../data/image.jpg',
            'error' => UPLOAD_ERR_OK,
            'size' => 98174
        ];
        $response = $validator->validateAttribute($model, 'file_id');
        
        $validator->uploadToFiles($model, 'file_id');

        $this->assertContains('myfile', $response);
    }
}