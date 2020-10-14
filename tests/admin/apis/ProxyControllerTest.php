<?php

namespace luya\admin\tests\admin\apis;

use admintests\AdminModelTestCase;
use luya\admin\apis\ProxyController;
use luya\admin\models\ProxyBuild;
use luya\admin\models\ProxyMachine;
use luya\testsuite\fixtures\NgRestModelFixture;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class ProxyControllerTest extends AdminModelTestCase
{
    /**
     * @var NgRestModelFixture
     */
    protected $build;
    
    /**
     * @var NgRestModelFixture
     */
    protected $machine;

    public function afterSetup()
    {
        parent::afterSetup();
        
        $this->build = new NgRestModelFixture([
            'modelClass' => ProxyBuild::class,
            'fixtureData' => [
                1 => [
                    'id' => 1,
                    'machine_id' => 1,
                    'timestamp' => 123456778,
                    'build_token' => 'token',
                    'config' => '{}',
                    'is_complet' => 0,
                    'expiration_time' => 9999999999999,
                ]
            ]
        ]);

        $this->machine = new NgRestModelFixture([
            'modelClass' => ProxyMachine::class,
            'fixtureData' => [
                1 => [
                    'id' => 1,
                    'name' => 'machine',
                    'access_token' => 'machine',
                    'identifier' => 'machine',
                    'is_disabled' => 0,
                    'is_deleted' => 0,
                ]
            ]
        ]);

        $this->createAdminStorageFileFixture();
        $this->createAdminStorageImageFixture();
    }

    public function testActionImageProviderUnableToFindBuild()
    {
        $ctrl = new ProxyController('proxy', $this->app->getModule('admin'));

        $this->expectException(ForbiddenHttpException::class);
        $ctrl->actionImageProvider('xx', 'xx', 1);
    }

    public function testActionImageProviderMissingImage()
    {
        $ctrl = new ProxyController('proxy', $this->app->getModule('admin'));
        $this->expectException(NotFoundHttpException::class);
        $ctrl->actionImageProvider('machine', 'token', 1);
    }

    public function testActionFileProviderUnableToFindBuild()
    {
        $ctrl = new ProxyController('proxy', $this->app->getModule('admin'));

        $this->expectException(ForbiddenHttpException::class);
        $ctrl->actionFileProvider('xx', 'xx', 1);
    }

    public function testActionFileProviderMissingImage()
    {
        $ctrl = new ProxyController('proxy', $this->app->getModule('admin'));
        $this->expectException(NotFoundHttpException::class);
        $ctrl->actionFileProvider('machine', 'token', 1);
    }
}