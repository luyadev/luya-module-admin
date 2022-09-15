<?php

namespace luya\admin\tests\admin\base;

use admintests\AdminModelTestCase;
use luya\admin\components\Auth;
use luya\admin\importers\AuthImporter;
use luya\admin\tests\data\modules\ExtendPermissionModule;
use luya\console\Application;
use luya\console\commands\ImportController;
use luya\testsuite\scopes\PermissionScope;
use Yii;

class ModuleTest extends AdminModelTestCase
{
    public function getConfigArray()
    {
        $config = parent::getConfigArray();

        $config['modules']['extends'] = [
            'class' => ExtendPermissionModule::class,
        ];
        $config['components']['auth'] = [
            'class' => Auth::class,
        ];

        return $config;
    }

    public function testModuleInstance()
    {
        $this->assertInstanceOf(ExtendPermissionModule::class, $this->app->getModule('extends'));
    }

    public function testExtendApiPermissions()
    {
        $module = $this->app->getModule('extends');
        $this->assertSame([
            ['api' => 'my-test-api', 'alias' => 'Foobar Alias'],
        ], $module->extendPermissionApis());


        $this->assertSame([
            ['api' => 'my-test-api', 'alias' => 'Foobar Alias'],
        ], $module->getAuthApis());

        // import auths

        PermissionScope::run($this->app, function (PermissionScope $scope) use ($module) {
            /*
            $console = new Application($this->getConfigArray());
            $importer = new ImportController($console, $module);
            $auth = new AuthImporter($importer, $module);
            $this->assertNull($auth->run());
            // no log message, means no deletion
            $this->assertSame([], $importer->getLog());

            $api = $this->app->adminmenu->getApiDetail('my-test-api');

            var_dump($api);

            $auth = $this->app->auth->getApiTable(1, 'my-test-api');

            var_dump($auth);
            */
            Yii::$app = $this->app;
            $console = new Application($this->getConfigArray());
            $importer = new ImportController($console, $module);
            $auth = new AuthImporter($importer, $module);
            Yii::$app = $this->app;
            $this->assertNull($auth->run());
            // no log message, means no deletion
            $this->assertSame([], $importer->getLog());
            Yii::$app = $this->app;

            // as the api is not contained in the menu, the admin menu can not find any informations about that!
            // even though the permission exists in admin_auth its not available in the menu system, which
            // can be required in order to return menu related infos.
            $this->assertFalse($this->app->adminmenu->getApiDetail('my-test-api'));
        });
    }
}
