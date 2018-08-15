<?php

namespace luya\admin\controllers;

use Yii;
use luya\admin\Module;
use luya\helpers\Url;
use yii\helpers\Json;
use luya\admin\base\Controller;
use luya\TagParser;
use luya\web\View;
use yii\helpers\Markdown;
use luya\admin\models\UserLogin;

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
        foreach ($this->module->assets as $class) {
            $this->registerAsset($class);
        }
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
        
        $authToken = UserLogin::find()->select(['auth_token'])->where(['user_id' => Yii::$app->adminuser->id, 'ip' => Yii::$app->request->userIP, 'is_destroyed' => false])->scalar();
        
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
    public function actionLogout()
    {
        if (!Yii::$app->adminuser->logout(false)) {
            Yii::$app->session->destroy();
        }
        
        return $this->redirect(['/admin/login/index', 'logout' => true]);
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
}
