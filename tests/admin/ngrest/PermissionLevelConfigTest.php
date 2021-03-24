<?php

namespace admintests\admin\ngrest;

use admintests\AdminModelTestCase;
use luya\admin\buttons\TimestampActiveButton;
use \luya\admin\buttons\DuplicateActiveButton;
use luya\admin\models\User;
use luya\testsuite\fixtures\NgRestModelFixture;
use luya\testsuite\traits\DatabaseTableTrait;
use luya\admin\aws\ChangePasswordActiveWindow;
use luya\admin\aws\UserHistorySummaryActiveWindow;
use luya\admin\components\Auth;

class PermissionLevelConfigTest extends AdminModelTestCase
{
    use DatabaseTableTrait;
    
    public function testActiveButtonButtonConditionDefinition()
    {
        $fixture = new NgRestModelFixture([
            'modelClass' => ScopeBasedConditionUserModel::class,
        ]);
 
        $ngRestCfg = $fixture->newModel->getNgRestConfig();
        $activeButtons = $ngRestCfg->getActiveButtons();
        
        $this->assertArrayHasKey('permissionLevel', $activeButtons[0]);
        $this->assertEquals(Auth::CAN_VIEW, $activeButtons[0]['permissionLevel']);        
        
        $fixture->cleanup();
    }
    
    public function testActiveWindowsButtonConditionDefinition()
    {
        $fixture = new NgRestModelFixture([
            'modelClass' => ScopeBasedConditionUserModel::class,
        ]);
 
        $ngRestCfg = $fixture->newModel->getNgRestConfig();
        
        $activeWindows=$ngRestCfg->getPointer('aw');
        $changePasswordActiveWindow = array_shift($activeWindows);
        $this->assertArrayHasKey('permissionLevel', $changePasswordActiveWindow['objectConfig']);
        $this->assertEquals(Auth::CAN_UPDATE, $changePasswordActiveWindow['objectConfig']['permissionLevel']);
        
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
            ['class' => TimestampActiveButton::class, 'attribute' => 'foo', 'permissionLevel' => Auth::CAN_VIEW],
            ['class' => DuplicateActiveButton::class]
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function ngRestActiveWindows()
    {
        return [
            ['class' => ChangePasswordActiveWindow::class, 'label' => false, 'permissionLevel' => Auth::CAN_UPDATE],
            ['class' => UserHistorySummaryActiveWindow::class, 'label' => false],
        ];
    }
}