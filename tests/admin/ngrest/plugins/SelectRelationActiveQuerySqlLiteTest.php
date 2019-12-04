<?php

namespace admintests\admin\ngrest\plugins;

use admintests\AdminModelTestCase;
use luya\testsuite\fixtures\NgRestModelFixture;
use luya\admin\models\User;
use luya\admin\ngrest\plugins\SelectRelationActiveQuery;
use luya\admin\ngrest\base\NgRestModel;
use yii\base\Event;

class SelectRelationActiveQueryTestSqlLite extends AdminModelTestCase
{
    public function testRelationLoad()
    {
        $user = new NgRestModelFixture([
            'modelClass' => User::class,
            'fixtureData' => [
                "user1" => [
                    'id' => 1,
                    'title' => 1,
                    'firstname' => 'John',
                    'lastname' => 'Doe',
                    'email' => 'john@luya.io',
                    'password' => 'nohash',
                    'is_deleted' => 0,
                    'is_api_user' => 0,
                ]
            ]
        ]);
        
        $userModel = $user->getModel('user1');

        $pluginModel = new NgRestModelFixture([
            'modelClass' => TestModel::class,
            'fixtureData' => [
                1 => [
                    'id' => 1,
                    'user_id' => 1,
                ]
            ]
        ]);

        $pluginModel1 = $pluginModel->getModel(1);

        $plugin = new SelectRelationActiveQuery([
            'name' => 'user_id',
            'alias' => 'user_id',
            'i18n' => false,
            'relation' => 'user',
            'labelField' => ['lastname', 'firstname'],
        ]);

        $event = new Event();
        $event->sender = $pluginModel1;

        $plugin->onListFind($event);

        $this->assertSame('Doe John', $pluginModel1->user_id);
    }
}

class TestModel extends NgRestModel
{
    public static function ngRestApiEndpoint()
    {
        return 'api-end-point';
    }

    public static function tableName()
    {
        return 'footable';
    }

    public function rules()
    {
        return [
            [['id', 'user_id'], 'string'],
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
};
