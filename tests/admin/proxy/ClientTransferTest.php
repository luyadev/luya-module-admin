<?php

namespace luya\admin\tests\admin\proxy;

use admintests\AdminConsoleSqLiteTestCase;
use luya\admin\commands\ProxyController;
use luya\admin\proxy\ClientBuild;
use luya\admin\proxy\ClientTransfer;
use luya\testsuite\traits\AdminDatabaseTableTrait;
use yii\base\InvalidConfigException;

class ClientTransferTest extends AdminConsoleSqLiteTestCase
{
    use AdminDatabaseTableTrait;

    /**
     * @return ProxyController
     */
    private function getCommand()
    {
        return new ProxyController('id', $this->app);
    }

    public function testInvalidPropertyException()
    {
        $this->expectException(InvalidConfigException::class);
        new ClientTransfer();
    }

    public function afterSetup()
    {
        parent::afterSetup();

        $this->createAdminStorageFileFixture();
        $this->createAdminStorageImageFixture();
    }

    public function testTransfer()
    {
        $build = new ClientBuild($this->getCommand(), $this->app->db, [
            'buildConfig' => [
                'tables' => [],
            ],
        ]);

        $transfer = new ClientTransfer([
            'build' => $build,
        ]);

        $this->assertNotNull($transfer->start());

    }
}