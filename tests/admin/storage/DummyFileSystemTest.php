<?php

namespace admintests\components;

use Yii;
use admintests\AdminTestCase;
use luya\admin\filesystem\DummyFileSystem;

class DummyFileSystemTest extends AdminTestCase
{
    private function getStorage()
    {
        return new DummyFileSystem(Yii::$app->request);
    }
    
    public function testDummyFiles()
    {
        $storage = $this->getStorage();
        $storage->addDummyFile(['id' => 1]);
        $storage->insertDummyFiles();
        
        $this->assertSame(1, $storage->getFilesArrayItem(1)['id']);
    }
}
