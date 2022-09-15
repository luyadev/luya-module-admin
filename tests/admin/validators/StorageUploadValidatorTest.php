<?php

namespace luya\admin\tests\admin\validators;

use admintests\AdminModelTestCase;
use luya\admin\validators\StorageUploadValidator;
use luya\base\DynamicModel;
use yii\web\UploadedFile;

class StorageUploadValidatorTest extends AdminModelTestCase
{
    public function testSingleUploadValdiator()
    {
        UploadedFile::reset();
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

        $this->assertStringContainsString('myfile', $response);
    }

    public function testMultiUploadValidtor()
    {
        UploadedFile::reset();
        $validator = new StorageUploadValidator();
        $validator->multiple = true;

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

        $this->assertStringContainsString('myfile', $model->file_id);
        $this->assertStringContainsString('["', $model->file_id); // its a json!
    }

    public function testEmptyFilesArray()
    {
        UploadedFile::reset();
        $_FILES = [];
        $validator = new StorageUploadValidator();
        $model = new DynamicModel(['file_id' => 0]);
        $this->assertNull($validator->validateAttribute($model, 'file_id'));
    }
}
