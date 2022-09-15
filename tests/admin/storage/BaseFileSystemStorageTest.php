<?php

namespace admintests\components;

use admintests\AdminTestCase;
use luya\admin\filesystem\LocalFileSystem;
use Yii;

class BaseFileSystemStorageTest extends AdminTestCase
{
    private function getStorage()
    {
        return new LocalFileSystem(Yii::$app->request);
    }

    public function testHttpPath()
    {
        $this->assertStringContainsString('storage'.DIRECTORY_SEPARATOR.'foo.jpg', $this->getStorage()->fileHttpPath('foo.jpg'));
    }

    public function testAbsoluteHttpPath()
    {
        $this->assertStringContainsString('storage'.DIRECTORY_SEPARATOR.'foo.jpg', $this->getStorage()->fileAbsoluteHttpPath('foo.jpg'));
    }

    public function testServerPath()
    {
        $this->assertStringContainsString('storage'.DIRECTORY_SEPARATOR.'foo.jpg', $this->getStorage()->fileServerPath('foo.jpg'));
    }

    public function testBaseFileSystemStorage()
    {
        /** @var $mock \luya\admin\storage\BaseFileSystemStorage */
        $mock = $this->getMockForAbstractClass('luya\admin\storage\BaseFileSystemStorage', ['request' => Yii::$app->request]);

        // just test wrong get file and get image methods which sure return false and empty arrays
        $this->assertFalse($mock->getFile(0));
        $this->assertEmpty($mock->findFiles([]));
        $this->assertEmpty($mock->findFile([]));

        $this->assertFalse($mock->getImage(0));
        $this->assertEmpty($mock->findImages([]));
        $this->assertEmpty($mock->findImage([]));

        $this->assertFalse($mock->getFolder(0));
        $this->assertEmpty($mock->findFolder([]));
        $this->assertEmpty($mock->findFolders([]));

        $this->assertNull($mock->flushArrays());
    }
}
