<?php

namespace admintests\admin\ngrest\render;

use admintests\AdminTestCase;
use luya\admin\ngrest\render\RenderCrud;
use luya\admin\ngrest\render\RenderCrudView;

class RenderCrudTest extends AdminTestCase
{
    /**
     *
     * @return \luya\admin\ngrest\render\RenderCrud
     */
    protected function getCrud()
    {
        return new RenderCrud();
    }
    
    public function testSettingButtonDefintion()
    {
        $crud = $this->getCrud();
        $crud->setSettingButtonDefinitions([
            ['label' => 'foo', 'tag' => 'span', 'ng-href' => '#click', 'class' => 'foobar'],
        ]);
        
        $this->assertSame([
            '<span class="foobar" label="foo" ng-href="#click"><i class="material-icons">extension</i><span> foo</span></span>',
        ], $crud->getSettingButtonDefinitions());
    }

    public function testSetView()
    {
        $crud = $this->getCrud();
        
        $crud->view = RenderCrudView::class;
        $this->assertTrue(is_a($crud->view, RenderCrudView::class));
        
        $crud->view = ['class' => RenderCrudView::class];
        $this->assertTrue(is_a($crud->view, RenderCrudView::class));
        
        $crud->view = new RenderCrudView();
        $this->assertTrue(is_a($crud->view, RenderCrudView::class));
    }

    public function testGetView()
    {
        $crud = $this->getCrud();
        $this->assertTrue(is_a($crud->view, RenderCrudView::class));
    }
}
