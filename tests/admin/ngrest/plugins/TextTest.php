<?php

namespace admintests\admin\ngrest\plugins;

use admintests\AdminTestCase;
use admintests\data\fixtures\UserFixture;
use luya\admin\ngrest\plugins\Text;
use yii\base\ModelEvent;

class TextTest extends AdminTestCase
{
    public function testHtmlNewLine()
    {
        // using an active record similar model would do the job as well
        // port to testsuite
        $fixture = new UserFixture();
        $fixture->load();
        $model = $fixture->getModel('user1');
        $model->firstname = '<img src=X onerror=alert(1)>1234';

        $event = new ModelEvent(['sender' => $model]);

        $plugin = new Text([
            'alias' => 'firstname',
            'name' => 'firstname',
            'i18n' => false,
        ]);

        $this->assertSame('<img src=X onerror=alert(1)>1234', $model->firstname);
        $plugin->onAssign($event);
        $this->assertSame('&lt;img src=X onerror=alert(1)&gt;1234', $model->firstname);
    }
}
