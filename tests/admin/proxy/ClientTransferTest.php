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

        $this->createAdminStorageFileFixture([
            1 => [
                'id' => 1,
                'is_hidden' => 0,
                'folder_id' => 0,
                'name_original' => 'test.jpg',
                'name_new' => 'test',
                'name_new_compound' => 'test.jpg',
                'mime_type' => 'image/jpeg',
                'extension' => 'jpg',
                'hash_file' => 'unknwon',
                'hash_name' => 'foobar',
                'upload_timestamp' => time(),
                'upload_user_id' => 1,
                'is_deleted' => 0,
            ]
        ]);
        $this->createAdminStorageImageFixture();

        $this->createAdminGroupFixture([

        ]);
    }

    public function testTransfer()
    {
        $command = $this->getCommand();

        $this->app->controller = $command;

        $build = new ClientBuild($command, $this->app->db, [
            'buildConfig' => [
                'tables' => [
                    'admin_group' => [
                        'pks' => 1,
                        'name' => 'admin_group',
                        'rows' => 1, // the total amount of rows
                        'fields' => ['id', 'name', 'text', 'is_deleted'],
                        'offset_total' => 10,
                    ]
                ],
            ],
        ]);

        $transfer = new ClientTransfer([
            'build' => $build,
        ]);

        $this->assertNotNull($transfer->start());

    }
}