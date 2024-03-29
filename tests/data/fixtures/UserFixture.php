<?php

namespace admintests\data\fixtures;

use yii\test\ActiveFixture;

class UserFixture extends ActiveFixture
{
    public $modelClass = 'luya\admin\models\User';

    public function load()
    {
        parent::resetTable();
        parent::load();
    }

    public function getData()
    {
        return [
            'user1' => [
                'id' => 1,
                'title' => 1,
                'firstname' => 'John',
                'lastname' => 'Doe',
                'email' => 'john@luya.io',
                'password' => 'nohash',
                'is_deleted' => 0,
                'is_api_user' => 0,
            ],
            'user2' => [
                'id' => 2,
                'title' => 2,
                'firstname' => 'Jane',
                'lastname' => 'Doe',
                'email' => 'jane@luya.io',
                'password' => 'nohash',
                'is_deleted' => 0,
                'is_api_user' => 0,
            ],
            'user3' => [
                'id' => 3,
                'title' => 3,
                'firstname' => 'Is',
                'lastname' => 'Deleted',
                'email' => 'deleted@luya.io',
                'password' => 'nohash',
                'is_deleted' => 1,
                'is_api_user' => 0,
            ]
        ];
    }
}
