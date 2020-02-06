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
        $fixture = new NgRestModelFixture([
            'modelClass' => User::class,
        ]);

        PermissionScope::run($this->app, function (PermissionScope $scope) {
            $scope->loginUser();

            $stub = new StubController('id', $this->app);
            $html = $stub->actionIndex();

            $this->assertNotEmpty($html);
        });
    }
}

class StubController extends Controller
{
    public $modelClass = User::class;
}
