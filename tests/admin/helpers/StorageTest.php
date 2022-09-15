<?php

namespace admintests\admin\helpers;

use admintests\AdminModelTestCase;
use InvalidArgumentException;
use luya\admin\helpers\Storage;
use luya\admin\models\StorageFile;
use luya\admin\models\StorageImage;
use luya\testsuite\fixtures\NgRestModelFixture;

class StorageTest extends AdminModelTestCase
{
    /*
    public function testSuccessUploadFromFiles()
    {
        $files[] = ['tmp_name' => Yii::getAlias('@data/image.jpg'), 'name' => 'image.jpg', 'type' => 'image/jpg', 'error' => 0, 'size' => 123];

        $response = Storage::uploadFromFiles($files);

        $this->assertTrue($response['upload']);
    }
    */

    public function testErrorUploadFromFiles()
    {
        $files[] = ['tmp_name' => 'not/found.jpg', 'name' => 'image.jpg', 'type' => 'image/jpg', 'error' => 1, 'size' => 123];

        $response = Storage::uploadFromFiles($files);

        $this->assertFalse($response['upload']);
    }

    public function testGetImageResolution()
    {
        $res = Storage::getImageResolution(__DIR__ . '/../../data/image.jpg');

        $this->assertSame(['width' => 2560, 'height' => 1600], $res);
    }

    public function testUploadErrorMessages()
    {
        $this->assertSame('The uploaded file exceeds the upload_max_filesize directive in php.ini.', Storage::getUploadErrorMessage(UPLOAD_ERR_INI_SIZE));
    }

    public function testRefreshFile()
    {
        new NgRestModelFixture([
            'modelClass' => StorageImage::class,
        ]);
        new NgRestModelFixture([
            'modelClass' => StorageFile::class,
        ]);
        $this->expectException(InvalidArgumentException::class);
        Storage::refreshFile(1, 'path/to/file.png');
    }

    public function testReplaceFileFromContent()
    {
        $this->assertFalse(Storage::replaceFileFromContent('invalid', 'invalid.png'));
    }

    public function testUploadFromContent()
    {
        $this->expectException('luya\Exception');
        Storage::uploadFromContent('invalid', 'invalid.png');
    }
}
