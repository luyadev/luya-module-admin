<?php

namespace admintests\admin\ngrest;

use admintests\AdminModelTestCase;
use luya\admin\buttons\TimestampActiveButton;
use luya\admin\models\Group;
use luya\admin\models\NgrestLog;
use luya\admin\models\User;
use luya\admin\models\UserLogin;
use luya\admin\ngrest\Config;
use luya\testsuite\fixtures\NgRestModelFixture;

class ConfigTest extends AdminModelTestCase
{
    /**
     * @expectedException yii\base\InvalidConfigException
     */
    public function testSetConfigException()
    {
        $cfg = new Config(['apiEndpoint' => 'rest-url', 'primaryKey' => ['id']]);
        $cfg->setConfig(['foo' => 'bar']);
        $cfg->setConfig(['not' => 'valid']); // will throw exception: Cant set config if config is not empty
    }

    public function testAddFieldIfExists()
    {
        $cfg = new Config(['apiEndpoint' => 'rest-url', 'primaryKey' => ['id']]);
        $this->assertEquals(true, $cfg->addField('list', 'foo'));
        $this->assertEquals(false, $cfg->addField('list', 'foo'));
    }
    
    public function testLazyLoadGetters()
    {
        $cfg = new Config(['apiEndpoint' => 'rest-url', 'primaryKey' => ['id']]);

        $fixture = new NgRestModelFixture([
            'modelClass' => StubUserModel::class,
        ]);

        $cfg->setModel($fixture->newModel);
        
        $this->assertSame([
            [
                'label' => 'Label',
                'apiEndpoint' => 'api-admin-group',
                'arrayIndex' => 0,
                'modelClass' => 'YWRtaW50ZXN0c1xhZG1pblxuZ3Jlc3RcU3R1YlVzZXJNb2RlbA==',
                'tabLabelAttribute' => null,
                'relationLink' => [
                    'id' => 'group_id'
                ]
            ],
        ], $cfg->getRelations());
        $this->assertSame([
            [
                'class' => TimestampActiveButton::class,
                'attribute' => 'foo',
            ]
        ], $cfg->getActiveButtons());

        $cfg->setPrimaryKey(['key1', 'key2']);
    }

    public function testInvalidCompositeKeyRelations()
    {
        $cfg = new Config(['apiEndpoint' => 'rest-url', 'primaryKey' => ['id']]);

        $fixture = new NgRestModelFixture([
            'modelClass' => StubUserModel::class,
        ]);

        $cfg->setModel($fixture->newModel);
        $cfg->setPrimaryKey(['key1', 'key2']);
        $this->expectException('yii\base\InvalidConfigException');
        $cfg->getRelations();
    }
}

class StubUserModel extends User
{
    public function ngRestActiveButtons()
    {
        return [
            ['class' => TimestampActiveButton::class, 'attribute' => 'foo']
        ];
    }

    public function ngRestRelations()
    {
        return [
            ['label' => 'Label', 'targetModel' => Group::class, 'dataProvider' => $this->getGroups()],
        ];
    }
}