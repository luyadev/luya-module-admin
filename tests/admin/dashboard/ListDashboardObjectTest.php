<?php

namespace admintests\admin\dashboard;

use admintests\AdminTestCase;
use luya\admin\dashboard\ListDashboardObject;
use luya\admin\dashboard\TableDashboardObject;

class ListDashboardObjectTest extends AdminTestCase
{
    public function testListObject()
    {
        $o = new ListDashboardObject();
        $o->variables = [
            'foo' => 'bar',
            'date' => time(),
            'eval' => function () {
                return time();
            },
            'tt' => ['app', 'Value'],
        ];
        $o->setTemplate('{{foo}} - {{ date }} - {{eval}} - {{tt}} - {{item.user.firstname}}');
        $s = $o->getTemplate();

        $this->assertStringContainsString('Value - {{item.user.firstname}}', $s);
        $this->assertStringContainsString('bar', $s);
    }

    public function testTableObject()
    {
        $o = new TableDashboardObject();
        $o->variables = [
            'foo' => 'bar',
            'date' => time(),
            'eval' => function () {
                return time();
            },
            'tt' => ['app', 'Value'],
        ];
        $o->setTemplate('{{foo}} - {{ date }} - {{eval}} - {{tt}} - {{item.user.firstname}}');
        $s = $o->getTemplate();

        $this->assertStringContainsString('Value - {{item.user.firstname}}', $s);
        $this->assertStringContainsString('bar', $s);
    }
}
