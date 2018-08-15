<?php

namespace luya\admin\models;

use Yii;
use yii\helpers\Url;
use luya\admin\Module;
use yii\base\Model;

/**
 * Admin Login Form Model.
 *
 * @property \luya\admin\models\User $user The user model.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
final class LoginForm extends Model
{
    private $_user = false;

    public $email;
    
    public $password;

    public $attempts = 0;
    
    public $allowedAttempts = 10;
    
    public $lockoutTime = (60 * 60);
    
    public $secureTokenExpirationTime = (60 * 7);
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email', 'password'], 'required'],
            [['email'], 'email'],
            ['password', 'validatePassword'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'email' => Module::t('model_loginform_email_label'),
            'password' => Module::t('model_loginform_password_label'),
        ];
    }

    /**
     * Inline validator.
     *
     * @param string $attribute Attribute value
     */
    public function validatePassword($attribute)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            
            if ($user && $this->userAttemptBruteForceLock($user)) {
                return $this->addError($attribute, Module::t('model_loginform_max_user_attempts', ['time' => Yii::$app->formatter->asRelativeTime($user->login_attempt_lock_expiration)]));
            }
            
            if (!$user || !$user->validatePassword($this->password)) {
                if ($this->attempts) {
                    // use `model_loginform_wrong_user_or_password` instead of `model_loginform_wrong_user_or_password_attempts` due to informations about correct email input.
                    $this->addError($attribute, Module::t('model_loginform_wrong_user_or_password', ['attempt' => $this->attempts, 'allowedAttempts' => $this->allowedAttempts]));
                } else {
                    $this->addError($attribute, Module::t('model_loginform_wrong_user_or_password'));
                }
            }
        }
    }
    
    /**
     * Check if the given user has a lockout, otherwise upcount the attempts.
     *
     * @param User $user
     * @return boolean
     * @since 1.2.0
     */
    private function userAttemptBruteForceLock(User $user)
    {
        if ($this->userAttemptBruteForceLockHasExceeded($user)) {
            return true;
        }
        
        $this->attempts = $user->login_attempt + 1;
        
        if ($this->attempts >= $this->allowedAttempts) {
            $user->updateAttributes(['login_attempt_lock_expiration' => time() + $this->lockoutTime]);
        }
        
        $user->updateAttributes(['login_attempt' => $this->attempts]);
    }
    
    /**
     * Check if lockout has expired or not.
     *
     * @param User $user
     * @return boolean
     * @since 1.2.0
     */
    private function userAttemptBruteForceLockHasExceeded(User $user)
    {
        if ($user->login_attempt_lock_expiration > time()) {
            $user->updateAttributes(['login_attempt' => 0]);
            
            return true;
        }
        
        return false;
    }

    /**
     * Send the secure token by mail.
     *
     * @return boolean
     */
    public function sendSecureLogin()
    {
        $token = $this->getUser()->getAndStoreToken();

        return Yii::$app->mail
            ->compose(Module::t('login_securetoken_mail_subject'), Module::t('login_securetoken_mail', [
                'url' => Url::base(true),
                'token' => $token,
            ]))
            ->address($this->user->email)
            ->send();
    }

    /**
     * Validate the secure token.
     *
     * @param string $token
     * @param integer $userId
     * @return boolean|\luya\admin\models\User
     */
    public function validateSecureToken($token, $userId)
    {
        $user = User::findOne($userId);
        
        if (!$user) {
            return false;
        }
        
        if ($this->userAttemptBruteForceLockHasExceeded($user)) {
            return false;
        }
        
        if ($user->secure_token == sha1($token) && $user->secure_token_timestamp >= (time() - $this->secureTokenExpirationTime)) {
            return $user;
        }

        return false;
    }

    /**
     * Login the current user if valid.
     *
     * @return boolean|\luya\admin\models\User|boolean
     */
    public function login()
    {
        if ($this->validate()) {
            $user = $this->getUser();
            $user->detachBehavior('LogBehavior');
            
            // update user model
            $user->updateAttributes([
                'force_reload' => false,
                'login_attempt' => 0,
                'login_attempt_lock_expiration' => null,
                'auth_token' => Yii::$app->security->hashData(Yii::$app->security->generateRandomString(), $user->password_salt),
            ]);
            
            // kill prev user logins
            UserLogin::updateAll(['is_destroyed' => true], ['user_id' => $user->id]);
            
            // create new user login
            $login = new UserLogin([
                'auth_token' => $user->auth_token,
                'user_id' => $user->id,
                'is_destroyed' => false,
            ]);
            $login->save();
            
            // refresh user online list
            UserOnline::refreshUser($user, 'login');
            return $user;
        }
        
        return false;
    }

    /**
     * @return boolean|\luya\admin\models\User
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findByEmail($this->email);
        }

        return $this->_user;
    }
}
