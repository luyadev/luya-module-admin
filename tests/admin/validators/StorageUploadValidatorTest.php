<?php

namespace luya\admin\tests\admin\validators;

use admintests\AdminModelTestCase;
use luya\admin\validators\StorageUploadValidator;
use luya\base\DynamicModel;

class StorageUploadValidatorTest extends AdminModelTestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testSingleUploadValdiator()
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

    /**
     * @runInSeparateProcess
     */
    public function testMultiUploadValidtor()
    {
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
        
        $this->assertContains('myfile', $model->file_id);
        $this->assertContains('["', $model->file_id); // its a json!
    }

    /**
     * @runInSeparateProcess
     */
    public function testEmptyFilesArray()
    {
        $_FILES = [];
        $validator = new StorageUploadValidator();
        $model = new DynamicModel(['file_id' => 0]);
        $this->assertNull($validator->validateAttribute($model, 'file_id'));
    }
}