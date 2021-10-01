<?php

namespace admintests\admin\ngrest\render;

use admintests\AdminModelTestCase;
use admintests\AdminTestCase;
use luya\admin\models\User;
use luya\admin\ngrest\Config;
use luya\admin\ngrest\ConfigBuilder;
use luya\admin\ngrest\render\RenderCrud;
use luya\admin\ngrest\render\RenderCrudView;
use luya\testsuite\fixtures\NgRestModelFixture;

class RenderCrudTest extends AdminModelTestCase
{
    /**
     *
     * @return \luya\admin\ngrest\render\RenderCrud
     */
    protected function getCrud()
    {
        return new RenderCrud();
    }
    
    public function testSettingButtondefinition()
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

    public function testReadonlyListUpdateRender()
    {
        $userFixture = new NgRestModelFixture([
            'modelClass' => User::class,
        ]);

        $crud = new RenderCrud();
        $crud->setModel(new User);

        $configBuilder = new ConfigBuilder(User::class);
        $configBuilder->list->field('fieldname', 'alias', false)->textarea(['readonly' => true]);
        $configBuilder->update->field('fieldname', 'alias', false)->textarea(['readonly' => true]);

        $config = new Config();
        $config->setModel(new User);
        $config->setConfig($configBuilder->getConfig());

        $content = null;
        foreach ($config->getPointer(RenderCrud::TYPE_UPDATE, []) as $e) {
            $content .= $crud->generatePluginHtml($e, RenderCrud::TYPE_UPDATE);
        }

        $this->assertSame('<span ng-if="getFieldHelp(\'fieldname\')" class="help-button btn btn-icon btn-help" tooltip tooltip-expression="getFieldHelp(\'fieldname\')" tooltip-position="left"></span><div class="form-group form-side-by-side">
                <div class="form-side form-side-label">alias</div>
                <div class="form-side"><span ng-bind="data.update.fieldname"></span></div>
            </div>', $content);
    }

    public function testSetInterfaceSettings()
    {
        $noInterface = [
            RenderCrud::INTERFACE_TITLE           => false,
            RenderCrud::INTERFACE_DESCRIPTION     => false,
            RenderCrud::INTERFACE_GLOBALBUTTONS   => false,
            RenderCrud::INTERFACE_SEARCH          => false,
            RenderCrud::INTERFACE_GROUP           => false,
            RenderCrud::INTERFACE_FILTER          => false,
            RenderCrud::INTERFACE_COUNTER         => false
        ];

        $fullInterface = [
            RenderCrud::INTERFACE_TITLE           => true,
            RenderCrud::INTERFACE_DESCRIPTION     => true,
            RenderCrud::INTERFACE_GLOBALBUTTONS   => true,
            RenderCrud::INTERFACE_SEARCH          => true,
            RenderCrud::INTERFACE_GROUP           => true,
            RenderCrud::INTERFACE_FILTER          => true,
            RenderCrud::INTERFACE_COUNTER         => true
        ];


        $crud = $this->getCrud();

        // Booleans
        $crud->setInterfaceSettings(true);
        $this->assertSame($fullInterface, $crud->getInterfaceSettings());

        $crud->setInterfaceSettings(false);
        $this->assertSame($noInterface, $crud->getInterfaceSettings());

        // Strings
        $crud->setInterfaceSettings('');
        $this->assertSame($fullInterface, $crud->getInterfaceSettings());

        $crud->setInterfaceSettings('minimal');
        $a = $noInterface;
        $a[RenderCrud::INTERFACE_TITLE] = true;
        $a[RenderCrud::INTERFACE_COUNTER] = true;
        $this->assertSame($a, $crud->getInterfaceSettings());

        // Arrays
        $crud->setInterfaceSettings([]);
        $this->assertSame($noInterface, $crud->getInterfaceSettings());

        $crud->setInterfaceSettings([RenderCrud::INTERFACE_TITLE => 'someValue', 'someUnknownKey' => true]);
        $a = $noInterface;
        $a[RenderCrud::INTERFACE_TITLE] = 'someValue';
        $this->assertSame($a, $crud->getInterfaceSettings());
    }
}
