<?php

namespace luya\admin\tests\admin\ngrest\validators;

use admintests\AdminModelTestCase;
use luya\admin\models\StorageImage;
use luya\admin\ngrest\validators\StorageUploadValidator;
use yii\web\UploadedFile;

class NgRestStorageUploadValidatorTest extends AdminModelTestCase
{
    public function testSingleUploadValdiator()
    {
        $this->createAdminStorageFileFixture();
        $this->createAdminNgRestLogFixture();
        $this->createAdminStorageImageFixture();
        UploadedFile::reset();
        $validator = new StorageUploadValidator();
        $model = new StorageImage(['file_id' => 0]);

        $_FILES['StorageImage[file_id]'] = [
            'name' => 'MyFile.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => dirname(__DIR__) . '/../../data/image.jpg',
            'error' => UPLOAD_ERR_OK,
            'size' => 98174
        ];
        $validator->validateAttribute($model, 'file_id');

        $this->assertSame(1, $model->file_id);
    }

    public function testMultiUploadValidtor()
    {
        UploadedFile::reset();
        $validator = new StorageUploadValidator();
        $validator->multiple = true;

        $this->createAdminStorageFileFixture();
        $this->createAdminNgRestLogFixture();
        $this->createAdminStorageImageFixture();
        $model = new StorageImage(['file_id' => 0]);

        $_FILES['StorageImage[file_id]'] = [
            'name' => 'MyFile.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => dirname(__DIR__) . '/../../data/image.jpg',
            'error' => UPLOAD_ERR_OK,
            'size' => 98174
        ];
        $validator->validateAttribute($model, 'file_id');

        $this->assertStringContainsString('[{"fileId":1,"caption":null,"hiddenStorageUploadSource":"', $model->file_id);
    }
}
