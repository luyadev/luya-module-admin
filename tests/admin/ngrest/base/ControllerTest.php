<?php

namespace admintests\admin\ngrest\base;

use admintests\AdminModelTestCase;
use luya\admin\models\User;
use luya\admin\ngrest\base\Controller;
use luya\testsuite\fixtures\NgRestModelFixture;
use luya\testsuite\scopes\PermissionScope;
use luya\testsuite\traits\AdminDatabaseTableTrait;

class ControllerTest extends AdminModelTestCase
{
    use AdminDatabaseTableTrait;

    public function testIndex()
    {
        
        PermissionScope::run($this->app, function (PermissionScope $scope) {
            $fixture = new NgRestModelFixture([
                'modelClass' => User::class,
            ]);
    
            $scope->loginUser();

            $stub = new StubController('id', $this->app);
            $html = $stub->actionIndex();

            $this->assertNotEmpty($html);
        });
    }

    public function testDescriptionSetterGetter()
    {
        $stub = new StubController('id', $this->app);
        $stub->setDescription('foo');
        $this->assertSame('foo', $stub->getDescription());
    }
}

class StubController extends Controller
{
    public $modelClass = User::class;
}
