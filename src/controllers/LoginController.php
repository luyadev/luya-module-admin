<?php

namespace luya\admin\controllers;

use Yii;
use yii\web\Response;
use luya\helpers\Url;
use luya\admin\models\LoginForm;
use luya\admin\Module;
use luya\admin\base\Controller;
use luya\admin\models\UserOnline;
use luya\admin\assets\Login;

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
     * {@inheritDoc}
     * @see \luya\admin\base\Controller::getRules()
     */
    public function getRules()
    {
        return [
            [
                'allow' => true,
                'actions' => ['index', 'async', 'async-token'],
                'roles' => ['?', '@'],
            ],
        ];
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
    public function actionIndex()
    {
        // redirect logged in users
        if (!Yii::$app->adminuser->isGuest) {
            return $this->redirect(['/admin/default/index']);
        }
       
        $this->registerAsset(Login::class);
        
        $this->view->registerJs("$('#email').focus(); checkInputLabels();
        	observeLogin('#loginForm', '".Url::toAjax('admin/login/async')."', '".Url::toAjax('admin/login/async-token')."');
        ");
    
        UserOnline::clearList($this->module->userIdleTimeout);
        
        return $this->render('index');
    }
    
    /**
     * Async single sign in action.
     *
     * This action is triggered by the async request from the login form.
     *
     * If successfull and 2FA is enabled, a token will be stored and sent to the user's email.
     *
     * @return array
     */
    public function actionAsync()
    {
        if (($lockout = $this->sessionBruteForceLock())) {
            return $this->sendArray(false, [Module::t('login_async_submission_limit_reached', ['time' =>  Yii::$app->formatter->asRelativeTime($lockout)])]);
        }
        
        // get the login form model
        $model = new LoginForm();
        $model->allowedAttempts = $this->module->loginUserAttemptCount;
        $model->lockoutTime = $this->module->loginUserAttemptLockoutTime;
        
        $loginData = Yii::$app->request->post('login');
        Yii::$app->session->remove('secureId');
        // see if values are sent via post
        if ($loginData) {
            $model->attributes = $loginData;
            if (($userObject = $model->login()) !== false) {
                // see if secure login is enabled or not
                if ($this->module->secureLogin) {
                    // try to send the secure token to the given user email store the token in the session.
                    if ($model->sendSecureLogin()) {
                        Yii::$app->session->set('secureId', $model->getUser()->id);
                        return $this->sendArray(false, [], true);
                    }
                    
                    return $this->sendArray(false, ['Unable to send and store secure token.']);
                }
                
                if (Yii::$app->adminuser->login($userObject)) {
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
        if (($lockout = $this->sessionBruteForceLock())) {
            return $this->sendArray(false, [Module::t('login_async_submission_limit_reached', ['time' =>  Yii::$app->formatter->asRelativeTime($lockout)])]);
        }
        
        $secureToken = Yii::$app->request->post('secure_token', false);
        
        $model = new LoginForm();
        $model->secureTokenExpirationTime = $this->module->secureTokenExpirationTime;
        
        if ($secureToken) {
            $user = $model->validateSecureToken($secureToken, Yii::$app->session->get('secureId'));

            if ($user && Yii::$app->adminuser->login($user)) {
                Yii::$app->session->remove('secureId');
                return $this->sendArray(true);
            }
            
            return $this->sendArray(false, [Module::t('login_async_token_error')]);
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
    private function sendArray($refresh, array $errors = [], $enterSecureToken = false, $message = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        return [
            'refresh' => $refresh,
            'message' => $message,
            'errors' => $errors,
            'enterSecureToken' => $enterSecureToken,
            'time' => time(),
        ];
    }

    /**
     * Ensure current brute force attempt based on session.
     *
     * @return boolean|integer
     * @since 1.2.0
     */
    private function sessionBruteForceLock()
    {
        $attempt = Yii::$app->session->get('__attempt_count', 0);
        
        $counter = $attempt + 1;
        
        Yii::$app->session->set('__attempt_count', $counter);
        
        $lockout = Yii::$app->session->get('__attempt_lockout');
        
        if ($lockout && $lockout > time()) {
            Yii::$app->session->set('__attempt_count', 0);
            return $lockout;
        }
        
        if ($counter >= $this->module->loginSessionAttemptCount) {
            Yii::$app->session->set('__attempt_lockout', time() + $this->module->loginSessionAttemptLockoutTime);
        }
        
        return false;
    }
}
