<?php

namespace admintests\admin\proxy;

use admintests\AdminTestCase;
use admintests\data\mocks\proxy\ClientTableMock;
use luya\admin\proxy\ClientBuild;
use luya\admin\commands\ProxyController;
use yii\db\Exception;
use Yii;

class ClientTableTest extends AdminTestCase
{
    /**
     * @throws \yii\db\Exception
     * @expectedException \yii\db\Exception
     */
    public function testSyncDataWithConnectionLost()
    {
        $this->app->db->createCommand('CREATE TABLE IF NOT EXISTS temp_synctest LIKE admin_user')->execute();
    
        Yii::$app->controller = new ProxyController('proxyctrl', $this->app);
        $build = new ClientBuild(Yii::$app->controller, [
            'buildConfig' => ['tables' => ['temp_synctest' => ['name' => 'temp_synctest', 'rows' => 0]]],
        ]);
        $table = new ClientTableMock($build, ['name' => 'temp_synctest', 'rows' => 0]);
    
        $this->expectException(Exception::class);
        $this->expectExceptionMessageRegExp('Error while sending QUERY packet. PID=\d+
The SQL being executed was: SET FOREIGN_KEY_CHECKS = 1;');
        $table->syncData();
    }
}
