<?php

namespace luya\admin\apis;

use Yii;
use luya\admin\ngrest\base\Api;
use luya\admin\models\UserChangePassword;
use luya\admin\models\User;

/**
 * User API, provides ability to manager and list all administration users.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class UserController extends Api
{
    /**
     * @var string Path to the user model class.
     */
    public $modelClass = 'luya\admin\models\User';
    
    /**
     * Dump the current data from your user session.
     *
     * @return array
     */
    public function actionSession()
    {
        $session = [
            'packages' => [],
            'user' => Yii::$app->adminuser->identity->toArray(['title', 'firstname', 'lastname', 'email', 'id']),
            'settings' => Yii::$app->adminuser->identity->setting->getArray([
                User::USER_SETTING_ISDEVELOPER,
                User::USER_SETTING_UILANGUAGE,
            ], [
                User::USER_SETTING_UILANGUAGE => $this->module->interfaceLanguage,
            ]),
        ];
        
        // if developer option is enabled provide package infos
        if ($session['settings'][User::USER_SETTING_ISDEVELOPER]) {
            $session['packages'] = Yii::$app->getPackageInstaller()->getConfigs();
        }
        
        return $session;
    }
    
    /**
     * Action to change the password for the given User.
     * 
     * @return \luya\admin\models\UserChangePassword
     */
    public function actionChangePassword()
    {
        $model = new UserChangePassword();
        $model->setUser(Yii::$app->adminuser->identity);
        $model->attributes = Yii::$app->request->bodyParams;
        if ($model->validate()) {
            $model->checkAndStore();
        }
        
        return $model;
    }
    
    /**
     * Update data for the current session user.
     * 
     * @return array
     */
    public function actionSessionUpdate()
    {
        $user = Yii::$app->adminuser->identity;
        $user->attributes = Yii::$app->request->bodyParams;
        $user->update(true, ['title', 'firstname', 'lastname', 'email', 'id']);
        
        return $user;
    }
    
    /**
     * Change user settings.
     * 
     * @return boolean
     */
    public function actionChangeSettings()
    {
        $params = Yii::$app->request->bodyParams;
        
        foreach ($params as $param => $value) {
            Yii::$app->adminuser->identity->setting->set($param, $value);
        }
        
        return true;
    }
}
