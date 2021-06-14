<?php

namespace admintests\admin\traits;

use admintests\AdminModelTestCase;
use admintests\data\fixtures\UserFixture;
use luya\admin\traits\LazyDataLoadTrait;

class LazyDataLoadTraitTest extends AdminModelTestCase
{
    public function testSaveEventI18n()
    {
        $event = new Event();
        $model = new UserFixture();
        $model->load();
        $user = $model->getModel('user1');
        
        $user->id = ['de' => [['value' => 1]]];
        
        $event->sender = $user;
        
        $plugin = new CheckboxList([
            'alias' => 'alias',
            'name' => 'id',
            'i18n' => true,
            'data' => [1 => 'Mr', 2 => 'Mrs'],
        ]);
        
        $plugin->onSave($event);
        
        $this->assertSame('{"de":[{"value":1}]}', $user->id);
    }
}

class LazyDataLoadTraitMock
{
    use LazyDataLoadTrait;
}