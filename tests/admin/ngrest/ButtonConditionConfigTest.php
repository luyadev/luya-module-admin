<?php

namespace admintests\admin\ngrest;

use admintests\AdminModelTestCase;
use luya\admin\aws\ChangePasswordActiveWindow;
use luya\admin\aws\UserHistorySummaryActiveWindow;
use luya\admin\buttons\TimestampActiveButton;
use luya\admin\models\User;
use luya\admin\ngrest\render\RenderCrud;
use luya\testsuite\fixtures\NgRestModelFixture;
use luya\testsuite\traits\DatabaseTableTrait;

class ButtonConditionConfigTest extends AdminModelTestCase
{
    use DatabaseTableTrait;

    public function testActiveWindowsButtonConditionDefinition()
    {
        $fixture = new NgRestModelFixture([
            'modelClass' => ScopeBasedConditionUserModel::class,
        ]);

        $ngRestCfg = $fixture->newModel->getNgRestConfig();

        $activeWindows = $ngRestCfg->getPointer('aw');

        $changePasswordActiveWindow = array_shift($activeWindows);
        $this->assertArrayHasKey('condition', $changePasswordActiveWindow['objectConfig']);
        $this->assertEquals($changePasswordActiveWindow['objectConfig']['condition'], '{firstname}==\'foo\'');

        $context = new RenderCrud([
            'config' => $ngRestCfg,
        ]);

        // test the RenderCrud::listContextVariablize()
        $this->assertSame(
            $context->listContextVariablize($changePasswordActiveWindow['objectConfig']['condition']),
            'item.firstname==\'foo\''
        );

        $fixture->cleanup();
    }

    public function testActiveButtonButtonConditionDefinition()
    {
        $fixture = new NgRestModelFixture([
            'modelClass' => ScopeBasedConditionUserModel::class,
        ]);

        $ngRestCfg = $fixture->newModel->getNgRestConfig();
        $this->assertArrayHasKey('condition', $ngRestCfg->getActiveButtons()[0]);
        $this->assertEquals($ngRestCfg->getActiveButtons()[0]['condition'], '{bar}==0');

        $fixture->cleanup();
    }


    public function testScopeBasedButtonsConditionDefinition()
    {
        $fixture = new NgRestModelFixture([
            'modelClass' => ScopeBasedConditionUserModel::class,
        ]);

        //        $fooUser = $fixture->getModel('user1');
        $ngRestCfg = $fixture->newModel->getNgRestConfig();
        $ngRestConfigOptions = $fixture->newModel->getNgRestScopeConfigOptions($ngRestCfg);

        $this->assertArrayHasKey('buttonCondition', $ngRestConfigOptions);
        $this->assertSame(
            $ngRestConfigOptions['buttonCondition'],
            [
                    [ 'update', '{title}>1'],
                    [ 'delete', '{title}==2 && {firstname}==\'bar\''],
                ]
        );


        $this->assertArrayHasKey('buttonCondition', $ngRestCfg->config['options']);

        $this->assertSame(
            $ngRestCfg->config['options']['buttonCondition'],
            [
                    [ 'update', '{title}>1'],
                    [ 'delete', '{title}==2 && {firstname}==\'bar\''],
                ]
        );

        $context = new RenderCrud([
            'config' => $ngRestCfg,
        ]);


        $deleteCondition = $context->getConfigButtonCondition('delete');
        $this->assertSame(
            $deleteCondition,
            '{title}==2 && {firstname}==\'bar\''
        );

        $this->assertSame(
            $context->listContextVariablize($deleteCondition),
            'item.title==2 && item.firstname==\'bar\''
        );

        $fixture->cleanup();
    }


    public function testNgRestConfigBasedButtonsCondition()
    {
        $fixture = new NgRestModelFixture([
            'modelClass' => NgRestConfigBasedConditionUserModel::class,
        ]);

        $ngRestCfg = $fixture->newModel->getNgRestConfig();
        $this->assertArrayHasKey('buttonCondition', $ngRestCfg->config['options']);

        $this->assertSame(
            $ngRestCfg->config['options']['buttonCondition'],
            [
                    [ 'update', '{title}==1'],
                    [ 'delete', '{title}==2'],
                ]
        );

        $fixture->cleanup();
    }
}

class ScopeBasedConditionUserModel extends User
{
    /**
     * @inheritdoc
     */
    public function ngRestActiveButtons()
    {
        return [
            ['class' => TimestampActiveButton::class, 'attribute' => 'foo', 'condition' => '{bar}==0']
        ];
    }

    /**
     * @inheritdoc
     */
    public function ngRestScopes()
    {
        return [
            ['list', ['title', 'firstname', 'lastname', 'email']],
            ['create', ['title', 'firstname', 'lastname', 'email', 'password']],
            ['update', ['title', 'firstname', 'lastname', 'email'], ['buttonCondition' => '{title}>1']],
            ['delete', true, ['buttonCondition' => ['{title}' => 2, '{firstname}' => '\'bar\'']] ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function ngRestActiveWindows()
    {
        return [
            ['class' => ChangePasswordActiveWindow::class, 'label' => false, 'condition' => '{firstname}==\'foo\''],
            ['class' => UserHistorySummaryActiveWindow::class, 'label' => false],
        ];
    }
}


class NgRestConfigBasedConditionUserModel extends ScopeBasedConditionUserModel
{
    public function ngRestConfigOptions()
    {
        return [
                'buttonCondition' => [
                    [ 'update', '{title}==1'],
                    [ 'delete', '{title}==2'],
                ],
            ];
    }
}
