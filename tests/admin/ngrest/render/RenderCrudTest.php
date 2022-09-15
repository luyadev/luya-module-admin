<?php

namespace admintests\admin\ngrest\render;

use admintests\AdminModelTestCase;
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
        $crud->setModel(new User());

        $configBuilder = new ConfigBuilder(User::class);
        $configBuilder->list->field('fieldname', 'alias', false)->textarea(['readonly' => true]);
        $configBuilder->update->field('fieldname', 'alias', false)->textarea(['readonly' => true]);

        $config = new Config();
        $config->setModel(new User());
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
}
