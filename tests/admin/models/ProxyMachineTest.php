<?php

namespace luya\admin\tests\admin\models;

use admintests\AdminModelTestCase;
use luya\admin\models\NgrestLog;
use luya\admin\models\ProxyMachine;
use luya\testsuite\fixtures\NgRestModelFixture;

class ProxyMachineTest extends AdminModelTestCase
{
    public function testCreateModel()
    {
        $fixture = new NgRestModelFixture([
            'modelClass' => ProxyMachine::class,
        ]);

        $log = new NgRestModelFixture([
            'modelClass' => NgrestLog::class,
        ]);

        $model = $fixture->newModel;
        $model->name = 'test';
        $this->assertTrue($model->save());

        $token = $model->access_token;
        $identifier = $model->identifier;

        $this->assertNotEmpty($identifier);
        $this->assertNotEmpty($token);
        $this->assertSame('test', $model->name);

        $model->name = 'foobar';
        $this->assertTrue($model->save());

        $this->assertSame('foobar', $model->name);
        $this->assertSame($token, $model->access_token);
        $this->assertSame($identifier, $model->identifier);

        $this->assertNotContains($token, ['-', '_']);
    }
}
