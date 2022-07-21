<?php

namespace luya\admin\controllers;

use luya\admin\base\Controller;
use luya\admin\models\UserDevice;
use luya\admin\models\UserLogin;
use luya\admin\Module;
use luya\helpers\Url;
use luya\TagParser;
use luya\web\View;
use Yii;
use yii\helpers\Json;
use yii\helpers\Markdown;

/**
 * Administration Controller provides, dashboard, logout and index.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class DefaultController extends Controller
{
    /**
     * @var boolean Whether the permission system should apply or not, if disabled it will stil check if admin user is logged in or not.
     */
    public $disablePermissionCheck = true;

    /**
     * @var string Path to the admin layout
     */
    public $layout = '@admin/views/layouts/main';

    /**
     * Find assets to register, and add them into the view.
     */
    public function init()
    {
        // call parent
        parent::init();

        // get controller based assets
        foreach (Yii::$app->getAdminModulesAssets() as $class) {
            $this->registerAsset($class);
        }
    }

    /**
     * Renders the Open API latest file with redoc inline
     *
     * @return string
     * @since 3.2.0
     */
    public function actionApiDoc()
    {
        return $this->renderPartial('apidoc');
    }

    /**
     * Render the admin index page.
     *
     * @return string
     */
    public function actionIndex()
    {
        $tags = [];
        foreach (TagParser::getInstantiatedTagObjects() as $name => $object) {
            $tags[] = [
                'name' => $name,
                'example' => $object->example(),
                'readme' => Markdown::process($object->readme()),
            ];
        }

        // register i18n
        $this->view->registerJs('var i18n=' . Json::encode($this->module->jsTranslations), View::POS_HEAD);

        $where = ['user_id' => Yii::$app->adminuser->id, 'is_destroyed' => false];
        if ($this->module->logoutOnUserIpChange) {
            $where['ip'] = Yii::$app->request->userIP;
        }
        $authToken = UserLogin::find()->select(['auth_token'])->andWHere($where)->scalar();

        $this->view->registerJs('zaa.run([\'$rootScope\', function($rootScope) { $rootScope.luyacfg = ' . Json::encode([
            'authToken' => $authToken,
            'homeUrl' => Url::home(true),
            'i18n' => $this->module->jsTranslations,
            'helptags' => $tags,
        ]). '; }]);', View::POS_END);

        return $this->render('index');
    }

    /**
     * Render Partial for dashboard objects (angular template).
     *
     * @return string
     */
    public function actionDashboard()
    {
        $items = [];
        foreach (Yii::$app->adminModules as $module) {
            foreach ($module->dashboardObjects as $config) {
                $items[] = Yii::createObject($config);
            }
        }

        return $this->renderPartial('dashboard', [
            'items' => $items,
        ]);
    }

    /**
     * Trigger user logout.
     *
     * @return \yii\web\Response
     */
    public function actionLogout($autologout = false)
    {
        $checksum = UserDevice::generateUserAgentChecksum(Yii::$app->request->userAgent);
        // remove device with the same checksum for this user.
        UserDevice::deleteAll(['user_id' => Yii::$app->adminuser->id, 'user_agent_checksum' => $checksum]);

        if (!Yii::$app->adminuser->logout(false)) {
            Yii::$app->session->destroy();
        }

        return $this->redirect(['/admin/login/index', 'autologout' => $autologout]);
    }

    /**
     * Context helper for layout main.php in order to colorize debug informations.
     *
     * @param string $value
     * @param boolean $displayValue
     * @return string
     */
    public function colorizeValue($value, $displayValue = false)
    {
        $text = ($displayValue) ? $value : Module::t('debug_state_on');
        if ($value) {
            return '<span style="color:green;">'.$text.'</span>';
        }
        return '<span style="color:red;">'.Module::t('debug_state_off').'</span>';
    }

    /**
     * Reload buttons from admin module config.
     *
     * @return array An array with all reload buttons.
     */
    public function reloadButtonArray()
    {
        return $this->module->reloadButtons;
    }
}
