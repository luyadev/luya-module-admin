<?php

namespace admintests;

use luya\admin\Bootstrap;
use luya\web\Application;
use yii\base\Event;
use yii\console\Application as YiiApplication;

class BootstrapTest extends AdminModelTestCase
{
    public function testRunQueueJob()
    {
        $event = new Event(['sender' => $this->app]);
        $bootstrap = new Bootstrap();

        $runNow = $bootstrap->runQueueJob($event);
        $this->assertNull($runNow);

        $bootstrap->bootstrap($this->app);

        $this->app->trigger(Application::EVENT_BEFORE_REQUEST);
    }

    public function testCliRunBootstrap()
    {
        $cli = new YiiApplication(['id' => 'foo', 'basePath' => dirname(__DIR__)]);

        $bootstrap = new Bootstrap();
        $bs = $bootstrap->bootstrap($cli);
        $this->assertNull($bs);
    }
}
