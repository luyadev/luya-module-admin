<?php

namespace admintests\admin\ngrest\render;

use admintests\AdminModelTestCase;
use luya\admin\aws\ChangePasswordActiveWindow;
use luya\admin\ngrest\Config;
use luya\admin\ngrest\ConfigBuilder;
use luya\admin\ngrest\render\RenderActiveWindowCallback;

class RenderActiveWindowCallbackTest extends AdminModelTestCase
{
    public function testNotFoundHash()
    {
        $config = new Config();

        $render = new RenderActiveWindowCallback();
        $render->setConfig($config);

        $this->expectException('luya\Exception');
        $render->render();
    }

    public function testArgumentException()
    {
        $build = new ConfigBuilder('foomodel');
        $build->aw->load(['class' => ChangePasswordActiveWindow::class]);

        $config = new Config();
        $config->setConfig($build->getConfig());

        // active window object hash: ff21bd877239c16ade6e598df6d2bfa91c127953
        $this->app->request->setQueryParams(['activeWindowHash' => 'ff21bd877239c16ade6e598df6d2bfa91c127953', 'activeWindowCallback' => 'save']);
        $render = new RenderActiveWindowCallback();
        $render->setConfig($config);

        $this->assertSame([
            'success' => false,
            'error' => true,
            'message' => 'Some required data is missing.',
            'responseData' => [
                'message' => "The argument 'newpass' is required for method 'callbackSave' in class 'luya\admin\aws\ChangePasswordActiveWindow'.",
            ],
            'events' => [],
        ], $render->render());
    }
}
