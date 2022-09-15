<?php

namespace admintests\admin\ngrest\plugins;

use admintests\AdminTestCase;
use admintests\data\fixtures\UserFixture;
use luya\admin\ngrest\plugins\FileArray;
use Yii;
use yii\base\Event;

class FileArrayTest extends AdminTestCase
{
    public function testFileIteratorObject()
    {
        $event = new Event();
        $model = new UserFixture();
        $model->load();
        $user = $model->getModel('user1');
        $user->id = '{"en":[{"fileId":"70","caption":"A"},{"fileId":"69","caption":"B"}],"de":[],"fr":[]}';
        $event->sender = $user;
        $plugin = new FileArray([
            'alias' => 'alias',
            'name' => 'id',
            'i18n' => true,
            'fileIterator' => true,
        ]);

        $plugin->onFind($event);

        $this->assertInstanceOf('\luya\admin\file\Iterator', $user->id);
    }


    public function testNotFileIteratorObject()
    {
        $event = new Event();
        $model = new UserFixture();
        $model->load();
        $user = $model->getModel('user1');
        $user->id = '{"en":[{"fileId":"70","caption":"A"},{"fileId":"69","caption":"B"}],"de":[],"fr":[]}';
        $event->sender = $user;
        $plugin = new FileArray([
            'alias' => 'alias',
            'name' => 'id',
            'i18n' => true,
            'fileIterator' => false,
        ]);

        $plugin->onFind($event);

        $this->assertTrue(is_array($user->id));
    }


    public function testFileIteratorObjectNotI18n()
    {
        $event = new Event();
        $model = new UserFixture();
        $model->load();
        $user = $model->getModel('user1');
        $user->id = '[{"fileId":"70","caption":"A"},{"fileId":"69","caption":"B"}]';
        $event->sender = $user;
        $plugin = new FileArray([
            'alias' => 'alias',
            'name' => 'id',
            'i18n' => false,
            'fileIterator' => true,
        ]);

        $plugin->onFind($event);

        $this->assertInstanceOf('\luya\admin\file\Iterator', $user->id);
    }

    public function testFileIteratorObjectNotI18nEmptyValue()
    {
        $event = new Event();
        $model = new UserFixture();
        $model->load();
        $user = $model->getModel('user1');
        $user->id = '[]';
        $event->sender = $user;
        $plugin = new FileArray([
            'alias' => 'alias',
            'name' => 'id',
            'i18n' => false,
            'fileIterator' => true,
        ]);

        $plugin->onFind($event);

        $this->assertSame([], $user->id);
    }

    public function testNotFileIteratorObjectNotI18n()
    {
        $event = new Event();
        $model = new UserFixture();
        $model->load();
        $user = $model->getModel('user1');
        $user->id = '[{"fileId":"70","caption":"A"},{"fileId":"69","caption":"B"}]';
        $event->sender = $user;
        $plugin = new FileArray([
            'alias' => 'alias',
            'name' => 'id',
            'i18n' => false,
            'fileIterator' => false,
        ]);

        $plugin->onFind($event);

        $this->assertTrue(is_array($user->id));
    }

    public function testFileIteratorCaptionDirectInputAccessI18n()
    {
        Yii::$app->storage->addDummyFile(['id' => 1, 'name_new' => 'foo.jpg', 'caption' => '{"en":"foobar"}']);
        Yii::$app->storage->insertDummyFiles();


        $event = new Event();
        $model = new UserFixture();
        $model->load();
        $user = $model->getModel('user1');
        $user->id = '[{"fileId":"1","caption":"bazfoo"}]';
        $event->sender = $user;
        $plugin = new FileArray([
            'alias' => 'id',
            'name' => 'id',
            'i18n' => true,
            'fileIterator' => true,
        ]);

        $plugin->onFind($event);

        $this->assertSame('foobar', Yii::$app->storage->getFile(1)->caption);


        foreach ($user->id as $k => $obj) {
            $this->assertSame('bazfoo', $obj->caption);
        }
    }

    public function testFileIteratorCaptionDirectInputAccess()
    {
        Yii::$app->storage->addDummyFile(['id' => 1, 'name_new' => 'foo.jpg', 'caption' => 'foobar']);
        Yii::$app->storage->insertDummyFiles();

        Yii::$app->storage->addDummyImage(['file_id' => 1, 'id' => 1]);
        Yii::$app->storage->insertDummyImages();


        $event = new Event();
        $model = new UserFixture();
        $model->load();
        $user = $model->getModel('user1');
        $user->id = '[{"fileId":"1","caption":"bazfoo"}]';
        $event->sender = $user;
        $plugin = new FileArray([
            'alias' => 'id',
            'name' => 'id',
            'i18n' => false,
            'fileIterator' => true,
        ]);

        $plugin->onFind($event);

        $this->assertSame('foobar', Yii::$app->storage->getFile(1)->caption);

        foreach ($user->id as $k => $obj) {
            $this->assertSame('bazfoo', $obj->caption);
        }
    }
}
