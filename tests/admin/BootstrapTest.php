<?php

namespace admintests;

use luya\admin\Bootstrap;
use luya\web\Application;
use yii\base\Event;

class BootstrapTest extends AdminTestCase
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
}