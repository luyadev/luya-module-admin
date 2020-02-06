<?php

namespace admintests\data\models;

use luya\admin\models\User;

class PoolModel extends User
{
    public function ngRestPools()
    {
        return [
            'pool1' => ['field' => 'value'],
        ];
    }
}
