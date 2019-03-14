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
            'buildConfig' => ['tables' => ['temp_synctest' => ['name' => 'temp_synctest', 'rows' => []]]],
        ]);
        $table = new ClientTableMock($build, ['name' => 'temp_synctest']);
    
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('PDOStatement::execute(): MySQL server has gone away');
        $table->syncData();
    }
}
