<?php

namespace admintests\admin\ngrest;

use admintests\AdminModelTestCase;
use luya\admin\apis\UserController;
use luya\admin\buttons\TimestampActiveButton;
use luya\admin\components\Auth;
use luya\admin\models\Group;
use luya\admin\models\User;
use luya\admin\ngrest\Config;
use luya\testsuite\fixtures\NgRestModelFixture;
use luya\testsuite\traits\DatabaseTableTrait;
use yii\base\InvalidConfigException;

class ConfigTest extends AdminModelTestCase
{
    use DatabaseTableTrait;

    public function testSetConfigException()
    {
        $cfg = new Config(['apiEndpoint' => 'rest-url', 'primaryKey' => ['id']]);
        $cfg->setConfig(['foo' => 'bar']);

        $this->expectException(InvalidConfigException::class);
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
                'hash' => '0b825e122b29fedf9d68ed51404e408968ede7f5',
                'label' => 'Timestamp',
                'icon' => 'update',
                'condition' => '',
                'permissionLevel' => Auth::CAN_UPDATE,
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

    public function testRelationCallOnApiWithConfig()
    {
        $this->createAdminLangFixture();
        new NgRestModelFixture([
            'modelClass' => StubUserModel::class,
        ]);
        new NgRestModelFixture([
            'modelClass' => Group::class,
            'fixtureData' => [
                1 => [
                    'id' => 1,
                    'name' => 'name',
                    'text' => 'text',
                    'is_deleted' => 0,
                ]
            ]
        ]);
        $this->createTableIfNotExists('admin_user_group', [
            'user_id' => 'int',
            'group_id' => 'int',
        ]);
        $api = new UserController('user', $this->app);
        $api->modelClass = StubUserModel::class;

        $this->expectException('yii\base\InvalidCallException');
        $api->actionRelationCall(0, 1, base64_encode(Group::class));
        $api->actionRelationCall(0, 2, base64_encode(Group::class));
        $api->actionRelationCall(0, 2, 'unknownclass');
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
