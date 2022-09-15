<?php

namespace admintests\admin\ngrest\base;

use admintests\AdminTestCase;
use admintests\data\models\I18nUser;
use admintests\data\stubs\StubPlugin;
use luya\admin\ngrest\plugins\Text;
use luya\base\DynamicModel;
use luya\testsuite\fixtures\NgRestModelFixture;
use yii\base\Event;

class PluginTest extends AdminTestCase
{
    /**
     * @var StubPlugin
     */
    public $plugin;
    /**
     * @var StubPlugin
     */
    public $plugini18n;

    public function afterSetup()
    {
        parent::afterSetup();
        $this->plugin = new StubPlugin(['name' => 'myField', 'alias' => 'Stub Label', 'i18n' => false]);
        $this->plugini18n = new StubPlugin(['name' => 'myField', 'alias' => 'Stub Label', 'i18n' => true]);
    }
    public function testInit()
    {
        $this->expectException('luya\Exception');
        $plugin = new StubPlugin();
    }

    public function testGetServiceName()
    {
        $this->assertSame('service.myField.fooBar', $this->plugin->getServiceName('fooBar'));
    }

    public function testNgShowCondition()
    {
        // expression
        $plugin = new Text(['condition' => '{feld2}=1', 'name' => 'feld1', 'i18n' => false, 'alias' => 'Feld 1 Label']);
        $this->assertSame('data.create.feld2=1', $plugin->getNgShowCondition('data.create.feld1'));

        // no condition
        $plugin = new Text(['name' => 'feld1', 'i18n' => false, 'alias' => 'Feld 1 Label']);
        $this->assertSame('', $plugin->getNgShowCondition('data.create.feld1'));

        // condition without field
        $plugin = new Text(['condition' => '0=1', 'name' => 'feld1', 'i18n' => false, 'alias' => 'Feld 1 Label']);
        $this->assertSame('0=1', $plugin->getNgShowCondition('data.create.feld1'));

        // condition without multiple fields
        $plugin = new Text(['condition' => '{feld1} && {feld3}', 'name' => 'feld1', 'i18n' => false, 'alias' => 'Feld 1 Label']);
        $this->assertSame('data.create.feld1 && data.create.feld3', $plugin->getNgShowCondition('data.create.feld1'));
    }

    public function testBeforeListFindProperty()
    {
        $plugin = $this->plugin;
        $plugin->beforeListFind = function ($value, $model) {
            return strtoupper($value);
        };

        $sender = new DynamicModel(['foo' => 'bar', 'myField' => 'barfoo']);
        $this->assertSame('bar', $sender->foo);
        $this->assertSame('barfoo', $sender->myField);

        $event = new Event();
        $event->sender = $sender;

        $plugin->onListFind($event);


        $this->assertSame('bar', $event->sender->foo);
        $this->assertSame('BARFOO', $event->sender->myField);
    }

    public function testOnListFindi18n()
    {
        new NgRestModelFixture([
            'modelClass' => I18nUser::class,
        ]);
        $model = new I18nUser();
        $model->firstname = '{"de":"Deutsch","en":"English"}';
        $this->assertSame('{"de":"Deutsch","en":"English"}', $model->firstname);

        $event = new Event();
        $event->sender = $model;

        $plugin = $this->plugini18n;
        $plugin->name = 'firstname';
        $plugin->onListFind($event);

        $this->assertSame('English', $model->firstname);
    }

    public function testOnExpandFindI18n()
    {
        new NgRestModelFixture([
            'modelClass' => I18nUser::class,
        ]);
        $model = new I18nUser();
        $model->firstname = '{"de":"Deutsch","en":"English"}';
        $this->assertSame('{"de":"Deutsch","en":"English"}', $model->firstname);

        $event = new Event();
        $event->sender = $model;

        $plugin = $this->plugini18n;
        $plugin->name = 'firstname';
        $plugin->onExpandFind($event);

        $this->assertSame([
            'de' => 'Deutsch',
            'en' => 'English',
        ], $model->firstname);
    }
}
