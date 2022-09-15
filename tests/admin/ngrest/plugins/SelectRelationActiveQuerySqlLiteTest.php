<?php

namespace admintests\admin\ngrest\plugins;

use admintests\AdminModelTestCase;
use admintests\data\models\I18nUser;
use luya\admin\helpers\I18n;
use luya\admin\models\Lang;
use luya\admin\models\User;
use luya\admin\ngrest\base\NgRestModel;
use luya\admin\ngrest\plugins\SelectRelationActiveQuery;
use luya\testsuite\fixtures\NgRestModelFixture;
use yii\base\Event;

class SelectRelationActiveQuerySqlLiteTest extends AdminModelTestCase
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



    private function getAdminLanguageMock($lang1IsActive, $lang2IsActive)
    {
        return new NgRestModelFixture([
            'modelClass' => Lang::class,
            'fixtureData' => [
                1 => [
                    'id' => 1,
                    'name' => 'Lang1',
                    'short_code' => 'lang1',
                    'is_default' => $lang1IsActive,
                    'is_deleted' => false,
                ],
                2 => [
                    'id' => 2,
                    'name' => 'Lang2',
                    'short_code' => 'lang2',
                    'is_default' => $lang2IsActive,
                    'is_deleted' => false,
                ]
            ]
        ]);
    }

    public function testI18nSingleLabelStringOnListFind()
    {
        $langFixture = $this->getAdminLanguageMock(true, false);
        $onlineFixture = $this->createAdminUserOnlineFixture([
            'userOnline1' => [
                'user_id' => 1,
                'last_timestamp' => 1544767318,
                'invoken_route' => 'admin/default/index',
            ],
        ]);

        $i18nUserFixutre = new NgRestModelFixture([
            'modelClass' => I18nUser::class,
            'fixtureData' => [
                'user1' => [
                    'id' => 1,
                    'title' => 1,
                    'firstname' => I18n::encode([
                        'lang1' => 'John',
                        'lang2' => 'Jojo',
                    ]),
                    'lastname' => I18n::encode([
                        'lang1' => 'Doe',
                        'lang2' => 'Dodo',
                    ]),
                    'email' => 'john@luya.io',
                    'password' => 'nohash',
                    'is_deleted' => 0,
                    'is_api_user' => 0,
                ],
            ]
        ]);

        $online = $onlineFixture->getModel('userOnline1');

        $plugin = new SelectRelationActiveQuery([
            'name' => 'user_id',
            'alias' => 'user_id',
            'query' => $online->hasOne(I18nUser::class, ['id' => 'user_id']),
            'labelField' => 'firstname',
        ]);


        $event = new Event();
        $lang1Online = clone $online;
        $event->sender = $lang1Online;
        $plugin->onListFind($event);

        $this->assertSame("John", $lang1Online->user_id);
    }

    public function testI18nMixedLabelStringOnListFind()
    {
        $this->getAdminLanguageMock(true, false);
        $onlineFixture = $this->createAdminUserOnlineFixture([
            'userOnline1' => [
                'user_id' => 1,
                'last_timestamp' => 1544767318,
                'invoken_route' => 'admin/default/index',
            ],
        ]);

        $i18nUserFixutre = new NgRestModelFixture([
            'modelClass' => I18nUser::class,
            'fixtureData' => [
                'user1' => [
                    'id' => 1,
                    'title' => 1,
                    'firstname' => I18n::encode([
                        'lang1' => 'John',
                        'lang2' => 'Jojo',
                    ]),
                    'lastname' => I18n::encode([
                        'lang1' => 'Doe',
                        'lang2' => 'Dodo',
                    ]),
                    'email' => 'john@luya.io',
                    'password' => 'nohash',
                    'is_deleted' => 0,
                    'is_api_user' => 0,
                ],
            ]
        ]);

        $online = $onlineFixture->getModel('userOnline1');
        $plugin = new SelectRelationActiveQuery([
            'name' => 'user_id',
            'alias' => 'user_id',
            'query' => $online->hasOne(I18nUser::class, ['id' => 'user_id']),
            'labelField' => 'firstname,email',
        ]);


        $event = new Event();
        $lang1Online = clone $online;
        $event->sender = $lang1Online;
        $plugin->onListFind($event);

        $this->assertSame("John john@luya.io", $lang1Online->user_id);
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
