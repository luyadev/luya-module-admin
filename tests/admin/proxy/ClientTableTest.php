<?php

namespace admintests\admin\proxy;

use admintests\AdminTestCase;
use admintests\data\mocks\proxy\ClientTableMock;
use luya\admin\proxy\ClientBuild;
use luya\admin\proxy\ClientTable;
use luya\admin\commands\ProxyController;

class ClientTableTest extends AdminTestCase
{
    /**
     * @throws \yii\db\Exception
     * @expectedException \yii\db\Exception
     */
    public function testSyncDataWithConnectionLost()
    {
        $this->app->db->createCommand('CREATE TABLE IF NOT EXISTS temp_synctest LIKE admin_user')->execute();
        
        $ctrl = new ProxyController('proxyctrl', $this->app);
        $build = new ClientBuild($ctrl, [
            'buildConfig' => ['tables' => ['temp_synctest' => ['name' => 'temp_synctest']]],
        ]);
        $table = new ClientTableMock($build, ['name' => 'temp_synctest']);
    
        try {
            $table->syncData();
        } catch (\yii\db\Exception $exception) {
            $this->assertEquals('PDOStatement::execute(): MySQL server has gone away', $exception->getPrevious()->getMessage());
            throw $exception;
        }
    }
}
