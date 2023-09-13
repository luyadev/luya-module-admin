<?php

namespace admintests\admin\ngrest;

use admintests\AdminModelTestCase;
use luya\admin\aws\ChangePasswordActiveWindow;
use luya\admin\aws\UserHistorySummaryActiveWindow;
use luya\admin\buttons\DuplicateActiveButton;
use luya\admin\buttons\TimestampActiveButton;
use luya\admin\components\Auth;
use luya\admin\models\User;
use luya\testsuite\fixtures\NgRestModelFixture;
use luya\testsuite\traits\DatabaseTableTrait;

class PermissionLevelConfigTest extends AdminModelTestCase
{
    use DatabaseTableTrait;

    public function testActiveButtonButtonConditionDefinition()
    {
        $fixture = new NgRestModelFixture([
            'modelClass' => PermissionLevelUserModel::class,
        ]);

        $ngRestCfg = $fixture->newModel->getNgRestConfig();
        $activeButtons = $ngRestCfg->getActiveButtons();

        $this->assertArrayHasKey('permissionLevel', $activeButtons[0]);
        $this->assertEquals(Auth::CAN_VIEW, $activeButtons[0]['permissionLevel']);

        // check default value (if not explicitly set)
        $this->assertArrayHasKey('permissionLevel', $activeButtons[1]);
        $this->assertEquals(Auth::CAN_UPDATE, $activeButtons[1]['permissionLevel']);

        $fixture->cleanup();
    }

    public function testActiveWindowsButtonConditionDefinition()
    {
        $fixture = new NgRestModelFixture([
            'modelClass' => PermissionLevelUserModel::class,
        ]);

        $ngRestCfg = $fixture->newModel->getNgRestConfig();

        $activeWindows = $ngRestCfg->getPointer('aw');

        $changePasswordActiveWindow = array_shift($activeWindows);
        $this->assertArrayHasKey('permissionLevel', $changePasswordActiveWindow['objectConfig']);
        $this->assertEquals(Auth::CAN_DELETE, $changePasswordActiveWindow['objectConfig']['permissionLevel']);

        $userHistoryActiveWindow = array_shift($activeWindows);
        $this->assertArrayHasKey('permissionLevel', $userHistoryActiveWindow['objectConfig']);
        $this->assertEquals('', $userHistoryActiveWindow['objectConfig']['permissionLevel']);

        $fixture->cleanup();
    }
}

class PermissionLevelUserModel extends User
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
            ['class' => ChangePasswordActiveWindow::class, 'label' => false, 'permissionLevel' => Auth::CAN_DELETE],
            ['class' => UserHistorySummaryActiveWindow::class, 'label' => false, 'permissionLevel' => ''],
        ];
    }
}
