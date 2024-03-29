<?php

namespace luya\admin\controllers;

use luya\admin\base\Controller;
use Yii;

/**
 * Account Controller contains User Profile Views.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class AccountController extends Controller
{
    public $disablePermissionCheck = true;

    public function actionDashboard()
    {
        return $this->render('dashboard', [
            'user' => Yii::$app->adminuser->identity,
        ]);
    }
}
