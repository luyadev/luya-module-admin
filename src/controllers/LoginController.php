<?php

namespace luya\admin\controllers;

use luya\admin\assets\Login;
use luya\admin\base\Controller;
use luya\admin\models\LoginForm;
use luya\admin\models\ResetPasswordChangeForm;
use luya\admin\models\ResetPasswordForm;
use luya\admin\models\User;
use luya\admin\models\UserLoginLockout;
use luya\admin\models\UserOnline;
use luya\admin\Module;
use luya\helpers\Url;
use RobThree\Auth\TwoFactorAuth;
use Yii;
use yii\base\InvalidConfigException;
use yii\filters\HttpCache;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

/**
 * Login Controller contains async actions, async token send action and login mechanism.
 *
 * 1. If session based {{luya\admin\Module::$loginSessionAttemptCount}} is exceeded a lockout ban will make login unavailable. Any async request is evaluated as attempt.
 * 2. If the email adresse is correctly retrieved from the database $loginUserAttemptCount exceed check starts. If the count is exceeded the lockout time is stored for the user inside the database.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class LoginController extends Controller
{
    /**
     * @var string Switch to nosession layout instead of admin default template.
     */
    public $layout = '@admin/views/layouts/nosession';

    /**
     * @var string A path to an image which should be display on the login screen, if not set no images is displayed.
     * @since 2.0.2
     */
    public $backgroundImage;

    /**
     * {@inheritDoc}
     * @see \luya\admin\base\Controller::getRules()
     */
    public function getRules()
    {
        return [
            [
                'allow' => true,
                'actions' => ['index', 'async', 'async-token', 'twofa-token', 'reset', 'password-reset'],
                'roles' => ['?', '@'],
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['httpCache'] = [
            'class' => HttpCache::class,
            'cacheControlHeader' => 'no-store, no-cache',
            'lastModified' => fn ($action, $params) => time(),
        ];

        return $behaviors;
    }

    /**
     * Login Form.
     *
     * This action renders and display the login form.
     *
     * + Single sign in runs {{luya\admin\controllers\LoginController::actionAsync()}}.
     * + 2FA calls {{luya\admin\controllers\LoginController::actionAsyncToken()}} afterwards.
     *
     * @return \yii\web\Response|string
     */
    public function actionIndex($autologout = null)
    {
        // redirect logged in users
        if (!Yii::$app->adminuser->isGuest) {
            return $this->redirect(['/admin/default/index']);
        }

        $this->registerAsset(Login::class);

        $this->view->registerJs("observeLogin('#loginForm', '".Url::toAjax('admin/login/async')."', '".Url::toAjax('admin/login/async-token')."', '".Url::toAjax('admin/login/twofa-token')."');");

        UserOnline::clearList($this->module->userIdleTimeout);

        return $this->render('index', [
            'autologout' => $autologout,
            'resetPassword' => $this->module->resetPassword,
            'disableLogin' => $this->module->disableLogin,
            'disableLoginMessage' => $this->module->disableLoginMessage ?: Module::t('disabled_login_text'),
        ]);
    }

    /**
     * Provides a form to enter an email which will then send a reset link.
     *
     * @return string
     * @since 3.0.0
     */
    public function actionReset()
    {
        if (!$this->module->resetPassword) {
            throw new ForbiddenHttpException();
        }

        $this->registerAsset(Login::class);

        $model = new ResetPasswordForm();
        $error = false;
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $user = User::findByEmail($model->email);
            if ($user) {
                $user->password_verification_token = Yii::$app->security->generateRandomString();
                $user->password_verification_token_timestamp = time() + $this->module->resetPasswordExpirationTime;
                if ($user->update(true, ['password_verification_token_timestamp' , 'password_verification_token'])) {
                    // token and timestamp has been stored. send mail.
                    $mail = Yii::$app->mail;
                    $mail->layout = false; // ensure layout is disabled even when enabled in application config
                    $send = $mail
                        ->compose(Module::t('reset_email_subject'), User::generateResetEmail(
                            Url::toRoute(['/admin/login/password-reset', 'token' => $user->password_verification_token, 'id' => $user->id], true),
                            Module::t('reset_email_subject'),
                            Module::t('reset_email_text')
                        ))
                        ->address($user->email)
                        ->send();

                    if (!$send) {
                        $error = true;
                        $model->addError('email', Module::t('reset_mail_error'));
                    }
                }
            }

            if (!$error) {
                Yii::$app->session->setFlash('reset_password_success');
                return $this->refresh();
            }
        }

        return $this->render('reset', [
            'model' => $model,
        ]);
    }

    /**
     * The reset action which allows to store a new password for a valid token and id.
     *
     * @param string $token
     * @param integer $id
     * @return string
     * @since 3.0.0
     */
    public function actionPasswordReset($token, $id)
    {
        if (!$this->module->resetPassword) {
            throw new ForbiddenHttpException();
        }

        $user = User::find()->where([
            'and',
            ['=', 'is_deleted', false],
            ['=', 'id', $id],
            ['=', 'password_verification_token', $token],
            ['>=', 'password_verification_token_timestamp', time()]
        ])->one();

        if (!$user) {
            Yii::$app->session->setFlash('invalid_reset_token');
            return $this->redirect('index');
        }

        $this->registerAsset(Login::class);

        $model = new ResetPasswordChangeForm();

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($user->changePassword($model->password)) {
                Yii::$app->session->setFlash('reset_password_success');
                $user->password_verification_token_timestamp = time();
                $user->password_verification_token = Yii::$app->security->generateRandomString();
                $user->save(true, ['password_verification_token_timestamp', 'password_verification_token']);
                return $this->redirect('index');
            } else {
                $model->addErrors($user->getErrors());
            }
        }

        return $this->render('password-reset', [
            'model' => $model,
        ]);
    }

    /**
     * Async single sign in action.
     *
     * This action is triggered by the async request from the login form.
     *
     * If successful and 2FA is enabled, a token will be stored and sent to the user's email.
     *
     * @return array
     */
    public function actionAsync()
    {
        if (($lockout = $this->sessionBruteForceLock(0))) {
            return $this->sendArray(false, [Module::t('login_async_submission_limit_reached', ['time' =>  Yii::$app->formatter->asRelativeTime($lockout)])]);
        }

        $model = new LoginForm();
        $model->allowedAttempts = $this->module->loginUserAttemptCount;
        $model->lockoutTime = $this->module->loginUserAttemptLockoutTime;

        $loginData = Yii::$app->request->post('login');
        Yii::$app->session->remove('secureId');
        // see if values are sent via post
        if ($loginData) {
            $model->attributes = $loginData;
            if (($userObject = $model->login()) !== false) {
                Yii::$app->session->set('autologin', $model->autologin);
                // if the user has enabled the 2fa verification
                if ($userObject->login_2fa_enabled) {
                    Yii::$app->session->set('secureId', $model->getUser()->id);
                    return $this->sendArray(false, [], false, null, true);
                }

                // see if secure login is enabled or not
                if ($this->module->secureLogin) {
                    // try to send the secure token to the given user email store the token in the session.
                    if ($model->sendSecureLogin()) {
                        Yii::$app->session->set('secureId', $model->getUser()->id);
                        return $this->sendArray(false, [], true);
                    }

                    return $this->sendArray(false, [Module::t('login_async_secure_token_error')]);
                }

                if (!$model->autologin) {
                    // auto login is disabled, disable the function
                    Yii::$app->adminuser->enableAutoLogin = false;
                }
                if (Yii::$app->adminuser->login($userObject, Yii::$app->adminuser->cookieLoginDuration)) {
                    return $this->sendArray(true);
                }
            }
        }

        return $this->sendArray(false, $model->getErrors(), false);
    }

    /**
     * Async Secure Token Login.
     *
     * @return array
     */
    public function actionAsyncToken()
    {
        if (($lockout = $this->sessionBruteForceLock(Yii::$app->session->get('secureId')))) {
            return $this->sendArray(false, [Module::t('login_async_submission_limit_reached', ['time' =>  Yii::$app->formatter->asRelativeTime($lockout)])]);
        }
        $secureToken = Yii::$app->request->post('secure_token', false);

        $model = new LoginForm();
        $model->secureTokenExpirationTime = $this->module->secureTokenExpirationTime;

        if ($secureToken) {
            $user = $model->validateSecureToken($secureToken, Yii::$app->session->get('secureId'));

            $autologin = Yii::$app->session->get('autologin', false);

            if (!$autologin) {
                // auto login is disabled, disable the function
                Yii::$app->adminuser->enableAutoLogin = false;
            }

            if ($user && Yii::$app->adminuser->login($user, Yii::$app->adminuser->cookieLoginDuration)) {
                Yii::$app->session->remove('secureId');
                return $this->sendArray(true);
            }

            return $this->sendArray(false, [Module::t('login_async_token_error')]);
        }

        return $this->sendArray(false, [Module::t('login_async_token_globalerror')]);
    }

    public function actionTwofaToken()
    {
        if (($lockout = $this->sessionBruteForceLock(Yii::$app->session->get('secureId')))) {
            return $this->sendArray(false, [Module::t('login_async_submission_limit_reached', ['time' =>  Yii::$app->formatter->asRelativeTime($lockout)])]);
        }

        $user = User::findOne(Yii::$app->session->get('secureId'));
        $verify = Yii::$app->request->post('verfiy_code', false);

        $backupCode = Yii::$app->request->post('backup_code', false);

        if ($backupCode && $user) {
            if (Yii::$app->security->validatePassword($backupCode, $user->login_2fa_backup_key)) {
                $autologin = Yii::$app->session->get('autologin', false);

                if (!$autologin) {
                    // auto login is disabled, disable the function
                    Yii::$app->adminuser->enableAutoLogin = false;
                }

                if ($user && Yii::$app->adminuser->login($user, Yii::$app->adminuser->cookieLoginDuration)) {
                    Yii::$app->session->remove('secureId');
                    return $this->sendArray(true);
                }
            } else {
                return $this->sendArray(false, [Module::t('login_async_twofa_wrong_backup_code')]);
            }
        }

        if ($verify && $user) {
            $twoFa = new TwoFactorAuth();
            if ($twoFa->verifyCode($user->login_2fa_secret, $verify)) {
                $autologin = Yii::$app->session->get('autologin', false);

                if (!$autologin) {
                    // auto login is disabled, disable the function
                    Yii::$app->adminuser->enableAutoLogin = false;
                }

                if ($user && Yii::$app->adminuser->login($user, Yii::$app->adminuser->cookieLoginDuration)) {
                    Yii::$app->session->remove('secureId');
                    return $this->sendArray(true);
                }
            } else {
                return $this->sendArray(false, [Module::t('login_async_twofa_verify_error')]);
            }
        }

        return $this->sendArray(false, [Module::t('login_async_token_globalerror')]);
    }

    /**
     * Change the response format to json and return the array.
     *
     * @param boolean $refresh
     * @param array $errors
     * @param boolean $enterSecureToken
     * @param string $message
     * @return array
     */
    private function sendArray($refresh, array $errors = [], $enterSecureToken = false, $message = null, $enterTwoFaToken = false)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'refresh' => $refresh,
            'message' => $message,
            'errors' => $this->toErrorArray($errors),
            'enterSecureToken' => $enterSecureToken,
            'enterTwoFaToken' => $enterTwoFaToken,
            'time' => time(),
        ];
    }

    private function toErrorArray(array $errors)
    {
        $array = [];
        foreach ($errors as $field => $message) {
            $array[] = ['field' => $field, 'message' => $message];
        }

        return $array;
    }

    /**
     * Ensure current brute force attempt based on session.
     *
     * @param $userId an user id or empty blocks the whole ip
     * @return boolean|integer
     * @since 1.2.0
     */
    private function sessionBruteForceLock($userId)
    {
        if (empty($userId)) {
            // block all request from this IP
            $userId = 0;
        }
        $userIP = Yii::$app->request->userIP;

        $model = UserLoginLockout::find()->where(['ip' => $userIP, 'user_id' => $userId])->one();

        if (!$model) {
            $model = new UserLoginLockout();
            $model->user_id = $userId;
            $model->ip = $userIP;
            $model->attempt_count = 0;

            if (!$model->save()) {
                throw new InvalidConfigException("error while storing the model." . var_export($model->getErrors(), true));
            }
        } else {
            // reset the attempt count if lockout time has been passed
            if ((time() - $model->updated_at) > $this->module->loginSessionAttemptLockoutTime) {
                $model->updateAttributes(['attempt_count' => 0]);
            }
        }

        $model->updateCounters(['attempt_count' => 1]);


        if ($model->attempt_count >= $this->module->loginSessionAttemptCount) {
            return $model->updated_at + $this->module->loginSessionAttemptLockoutTime;
        }

        $model->touch('updated_at');


        return false;
    }
}
