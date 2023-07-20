<?php

namespace luya\admin\apis;

use luya\admin\models\User;
use luya\admin\models\UserChangePassword;
use luya\admin\models\UserDevice;
use luya\admin\Module;
use luya\admin\ngrest\base\Api;
use luya\base\PackageInstaller;
use luya\validators\StrengthValidator;
use RobThree\Auth\TwoFactorAuth;
use Yii;
use yii\web\NotFoundHttpException;

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
     * Profile
     *
     * Return informations about the current logged in user
     *
     * @return array
     */
    public function actionSession()
    {
        $user = Yii::$app->adminuser->identity;

        $qrcode = null;
        $secret = null;
        if (!$user->login_2fa_enabled) {
            $tfa = new TwoFactorAuth(Yii::$app->siteTitle);
            $secret = $tfa->createSecret();
            $qrcode = $tfa->getQRCodeImageAsDataUri(Yii::$app->siteTitle, $secret);
        }

        $session = [
            'packages' => [],
            'user' => $user->toArray(['title', 'firstname', 'lastname', 'email', 'id', 'email_verification_token_timestamp']),
            'activities' => ['open_email_validation' => $this->hasOpenEmailValidation($user)],
            'settings' => Yii::$app->adminuser->identity->setting->getArray([
                User::USER_SETTING_ISDEVELOPER, User::USER_SETTING_UILANGUAGE, User::USER_SETTING_NEWUSEREMAIL
            ], [
                User::USER_SETTING_UILANGUAGE => $this->module->interfaceLanguage,
            ]),
            'vendor_install_timestamp' => Yii::$app->getPackageInstaller()->getTimestamp(),
            'devices' => $user->devices,
            'twoFa' => [
                'enabled' => $user->login_2fa_enabled,
                'qrcode' => $qrcode,
                'secret' => $secret,
            ],
        ];

        // if developer option is enabled provide package infos
        if ($session['settings'][User::USER_SETTING_ISDEVELOPER]) {
            $session['packages'] = $this->packagesToArray(Yii::$app->getPackageInstaller());
        }

        return $session;
    }

    /**
     * Generate an array with package infos
     *
     * @param PackageInstaller $installer
     * @return array
     * @since 2.2.0
     */
    private function packagesToArray(PackageInstaller $installer)
    {
        $packages = [];
        foreach ($installer->getConfigs() as $config) {
            $packages[] = [
                'package' => $config->package,
                'bootstrap' => $config->bootstrap,
                'blocks' => $config->blocks,
            ];
        }
        return $packages;
    }

    /**
     * Disable 2FA
     * Action to disable the two fa auth for this user.
     *
     * @return array
     * @since 3.0.0
     */
    public function actionDisableTwofa()
    {
        $user = Yii::$app->adminuser->identity;
        $user->login_2fa_enabled = 0;
        $user->login_2fa_secret = '';
        $user->login_2fa_backup_key = '';

        if ($user->update(true, ['login_2fa_enabled', 'login_2fa_secret', 'login_2fa_backup_key'])) {
            return [];
        }

        return $this->sendModelError($user);
    }

    /**
     * Register 2FA Device
     *
     * Action to register new OTP device
     *
     * @return array
     * @since 3.0.0
     */
    public function actionRegisterTwofa()
    {
        $user = Yii::$app->adminuser->identity;

        $verification = Yii::$app->request->getBodyParam('verification');
        $secret = Yii::$app->request->getBodyParam('secret');

        $tfa = new TwoFactorAuth(Yii::$app->siteTitle);

        if (!$tfa->verifyCode($secret, $verification)) {
            return $this->sendArrayError(['verificaton' => Module::t('user_register_2fa_verification_error')]);
        }

        $code = random_int(1_000_000, 9_999_999);
        $backupKey = Yii::$app->security->generatePasswordHash($code);

        $user->login_2fa_enabled = 1;
        $user->login_2fa_secret = $secret;
        $user->login_2fa_backup_key = $backupKey;

        if ($user->update(true, ['login_2fa_enabled', 'login_2fa_secret', 'login_2fa_backup_key'])) {
            return [
                'backupCode' => $code,
            ];
        }

        return $this->sendModelError($user);
    }

    /**
     * Remove Device
     *
     * @return array
     * @since 3.0.0
     */
    public function actionRemoveDevice()
    {
        $deviceId = Yii::$app->request->getBodyParam('deviceId');

        $device = UserDevice::find()->where(['id' => $deviceId, 'user_id' => Yii::$app->adminuser->id])->one();

        if ($device) {
            return $device->delete();
        }

        throw new NotFoundHttpException();
    }

    /**
     * Change Password
     *
     * A request including body params `newpass`, `oldpass`, `newpassrepeat`.
     *
     * @uses UserChangePassword
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
     * Change E-Mail
     *
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
     * Update Profile
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
            $mailer = Yii::$app->mail;
            $mailer->layout = false; // disable layout as mail template contains layout
            $mail = $mailer->compose(Module::t('account_changeemail_subject'), User::generateTokenEmail($token, Module::t('account_changeemail_subject'), Module::t('account_changeemail_body')))
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
     * Change Settings
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
}
