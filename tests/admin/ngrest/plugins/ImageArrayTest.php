<?php

namespace admintests\admin\ngrest\plugins;

use admintests\AdminTestCase;

use admintests\data\fixtures\UserFixture;
use luya\admin\ngrest\plugins\ImageArray;
use Yii;
use yii\base\Event;

class ImageArrayTest extends AdminTestCase
{
    public function testimageIteratorObject()
    {
        $event = new Event();
        $model = new UserFixture();
        $model->load();
        $user = $model->getModel('user1');
        $user->id = '{"en":[{"imageId":"70","caption":"A"},{"imageId":"69","caption":"B"}],"de":[],"fr":[]}';
        $event->sender = $user;
        $plugin = new ImageArray([
            'alias' => 'alias',
            'name' => 'id',
            'i18n' => true,
            'imageIterator' => true,
        ]);

        $plugin->onFind($event);

        $this->assertInstanceOf('\luya\admin\image\Iterator', $user->id);
    }


    public function testNotimageIteratorObject()
    {
        $event = new Event();
        $model = new UserFixture();
        $model->load();
        $user = $model->getModel('user1');
        $user->id = '{"en":[{"imageId":"70","caption":"A"},{"imageId":"69","caption":"B"}],"de":[],"fr":[]}';
        $event->sender = $user;
        $plugin = new ImageArray([
            'alias' => 'alias',
            'name' => 'id',
            'i18n' => true,
            'imageIterator' => false,
        ]);

        $plugin->onFind($event);

        $this->assertTrue(is_array($user->id));
    }


    public function testimageIteratorObjectNotI18n()
    {
        $event = new Event();
        $model = new UserFixture();
        $model->load();
        $user = $model->getModel('user1');
        $user->id = '[{"imageId":"70","caption":"A"},{"imageId":"69","caption":"B"}]';
        $event->sender = $user;
        $plugin = new ImageArray([
            'alias' => 'alias',
            'name' => 'id',
            'i18n' => false,
            'imageIterator' => true,
        ]);

        $plugin->onFind($event);

        $this->assertInstanceOf('\luya\admin\image\Iterator', $user->id);
    }

    public function testimageIteratorObjectNotI18nEmptyValue()
    {
        $event = new Event();
        $model = new UserFixture();
        $model->load();
        $user = $model->getModel('user1');
        $user->id = '[]';
        $event->sender = $user;
        $plugin = new ImageArray([
            'alias' => 'alias',
            'name' => 'id',
            'i18n' => false,
            'imageIterator' => true,
        ]);

        $plugin->onFind($event);

        $this->assertSame([], $user->id);
    }

    public function testNotimageIteratorObjectNotI18n()
    {
        $event = new Event();
        $model = new UserFixture();
        $model->load();
        $user = $model->getModel('user1');
        $user->id = '[{"imageId":"70","caption":"A"},{"imageId":"69","caption":"B"}]';
        $event->sender = $user;
        $plugin = new ImageArray([
            'alias' => 'alias',
            'name' => 'id',
            'i18n' => false,
            'imageIterator' => false,
        ]);

        $plugin->onFind($event);

        $this->assertTrue(is_array($user->id));
    }

    public function testImageIteratorCaptionDirectInputAccess()
    {
        Yii::$app->storage->addDummyFile(['id' => 1, 'name_new' => 'foo.jpg', 'caption' => '{"en":"foobar"}']);
        Yii::$app->storage->insertDummyFiles();

        Yii::$app->storage->addDummyImage(['file_id' => 1, 'id' => 1]);
        Yii::$app->storage->insertDummyImages();


        $event = new Event();
        $model = new UserFixture();
        $model->load();
        $user = $model->getModel('user1');
        $user->id = '[{"imageId":"1","caption":"bazfoo"}]';
        $event->sender = $user;
        $plugin = new ImageArray([
            'alias' => 'id',
            'name' => 'id',
            'i18n' => false,
            'imageIterator' => true,
        ]);

        $plugin->onFind($event);

        $this->assertSame('foobar', Yii::$app->storage->getFile(1)->caption);


        foreach ($user->id as $k => $obj) {
            $this->assertSame('bazfoo', $obj->caption);
        }
    }

    public function testCreateTemplate()
    {
        $plugin = new ImageArray([
            'alias' => 'id',
            'name' => 'id',
            'i18n' => false,
        ]);

        $this->assertSame('<zaa-image-array-upload options=\'{"description":true,"filter":true}\' fieldid="id" model="model" label="id" fieldname="id" i18n=""></zaa-image-array-upload>', $plugin->renderCreate('id', 'model'));
    }
}
