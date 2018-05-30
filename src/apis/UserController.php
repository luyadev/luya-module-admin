<?php

namespace luya\admin\apis;

use Yii;
use luya\admin\ngrest\base\Api;
use luya\admin\models\UserChangePassword;
use luya\admin\models\User;
use luya\validators\StrengthValidator;
use luya\helpers\Url;
use luya\admin\Module;

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
        $user = Yii::$app->adminuser->identity;
        $session = [
            'packages' => [],
            'user' => $user->toArray(['title', 'firstname', 'lastname', 'email', 'id', 'email_verification_token_timestamp']),
            'activities' => ['open_email_validation' => $this->hasOpenEmailValidation($user)],
            'settings' => Yii::$app->adminuser->identity->setting->getArray([
                User::USER_SETTING_ISDEVELOPER, User::USER_SETTING_UILANGUAGE, User::USER_SETTING_NEWUSEREMAIL
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
     * Ensure whether the current user has an active email verification token or not.
     *
     * @param User $user The user object to evaluate.
     * @return boolean
     */
    private function hasOpenEmailValidation(User $user)
    {
        $ts = $user->email_verification_token_timestamp;
        if (!empty($ts) && (time() - $this->module->emailVerificationTokenExpirationTime) <= $ts) {
            return true;
        }
        
        return false;
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
        
        if ($this->module->strongPasswordPolicy) {
            $model->validators->append(StrengthValidator::createValidator(StrengthValidator::class, $model, ['newpass']));
        }
        
        if ($model->validate()) {
            $model->checkAndStore();
        }
        
        return $model;
    }
    
    /**
     * Action to change the email based on token input.
     *
     * @return boolean
     * @since 1.2.0
     */
    public function actionChangeEmail()
    {
        $token = Yii::$app->request->getBodyParam('token');
        $user = Yii::$app->adminuser->identity;
        
        if (!empty($token) && sha1($token) == $user->email_verification_token && $this->hasOpenEmailValidation($user)) {
            $newEmail = $user->setting->get(User::USER_SETTING_NEWUSEREMAIL);
            
            $user->email = $newEmail;
            if ($user->update(true, ['email'])) {
                $user->resetEmailVerification();
                $newEmail = $user->setting->remove(User::USER_SETTING_NEWUSEREMAIL);
                return ['success' => true];
            } else {
                return $this->sendModelError($user);
            }
        }
        
        return $this->sendArrayError(['email' => Module::t('account_changeemail_wrongtokenorempty')]);
    }
    
    /**
     * Update data for the current session user.
     *
     * @return array
     */
    public function actionSessionUpdate()
    {
        $identity = Yii::$app->adminuser->identity;
        $user = clone Yii::$app->adminuser->identity;
        $user->attributes = Yii::$app->request->bodyParams;
        $verify = ['title', 'firstname', 'lastname', 'id'];
        
        // check if email has changed, if yes send secure token and temp store new value in user settings table.
        if ($user->validate(['email']) && $user->email !== $identity->email && $this->module->emailVerification) {
            $token = $user->getAndStoreEmailVerificationToken();
            
            $mail = Yii::$app->mail->compose(Module::t('account_changeemail_subject'), Module::t('account_changeemail_body', ['url' => Url::base(true), 'token' => $token]))
            ->address($identity->email, $identity->firstname . ' '. $identity->lastname)
            ->send();
            
            if ($mail) {
                $identity->setting->set(User::USER_SETTING_NEWUSEREMAIL, $user->email);
            } else {
                $user->addError('email', Module::t('account_changeemail_tokensenterror', ['email' => $identity->email]));
                $identity->resetEmailVerification();
            }
        }
        
        if (!$this->module->emailVerification) {
            $verify[] = 'email';
        }
        
        if (!$user->hasErrors() && $user->update(true, $verify) !== false) {
            return $user;
        }
        
        return $this->sendModelError($user);
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
