<?php

namespace admintests\data\fixtures;

use yii\test\ActiveFixture;

class UserOnlineFixture extends ActiveFixture
{
    public $modelClass = 'luya\admin\models\UserOnline';

    public function load()
    {
        parent::resetTable();
        parent::load();
    }

    public function getData()
    {
        return [
            'userOnline1' => [
                'user_id' => 1,
                'last_timestamp' => 1544767318,
                'invoken_route' => 'admin/default/index',
            ],
        ];
    }
}
