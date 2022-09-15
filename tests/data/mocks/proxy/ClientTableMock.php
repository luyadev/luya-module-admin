<?php

namespace admintests\data\mocks\proxy;

use luya\admin\proxy\ClientTable;
use Yii;

class ClientTableMock extends ClientTable
{
    protected function cleanup($sqlMode)
    {
        try {
            // provoke sql error on later cleanup
            Yii::$app->db->createCommand('KILL (SELECT CONNECTION_ID());')->execute();
        } catch (\Throwable $ex) {
        }

        parent::cleanup($sqlMode);
    }
}
