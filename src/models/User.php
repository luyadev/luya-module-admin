<?php

namespace luya\admin\models;

use Yii;
use yii\web\IdentityInterface;
use yii\helpers\Json;
use luya\admin\aws\ChangePasswordInterface;
use luya\admin\Module;
use luya\admin\traits\SoftDeleteTrait;
use luya\admin\ngrest\base\NgRestModel;
use luya\admin\aws\ChangePasswordActiveWindow;
use luya\admin\aws\UserHistorySummaryActiveWindow;
use luya\admin\base\RestActiveController;
use yii\base\InvalidArgumentException;
use luya\validators\StrengthValidator;

/**
 * User Model represents all Administration Users.
 *
 * @property integer $id
 * @property string $firstname
 * @property string $lastname
 * @property integer $title
 * @property string $email
 * @property string $password
 * @property string $password_salt
 * @property string $auth_token
 * @property integer $is_deleted
 * @property string $secure_token
 * @property integer $secure_token_timestamp
 * @property integer $force_reload
 * @property string $settings
 * @property \luya\admin\models\UserSetting $setting Setting object to store data.
 * @property integer $is_api_user
 * @property integer $api_rate_limit
 * @property string $api_allowed_ips
 * @property integer $api_last_activity
 * @property string $email_verification_token
 * @property integer $email_verification_token_timestamp
 * @property integer $login_attempt
 * @property integer $login_attempt_lock_expiration
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class User extends NgRestModel implements IdentityInterface, ChangePasswordInterface
{
    const USER_SETTING_ISDEVELOPER = 'isDeveloper';
    
    const USER_SETTING_UILANGUAGE = 'luyadminlanguage';
    
    const USER_SETTING_NEWUSEREMAIL = 'newUserEmail';
    
    use SoftDeleteTrait;
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        
        $this->on(self::EVENT_BEFORE_INSERT, function () {
            if ($this->scenario == RestActiveController::SCENARIO_RESTCREATE) {
                $this->encodePassword();
                
                if ($this->isNewRecord) {
                    $this->is_deleted = false;
                    $this->auth_token = Yii::$app->security->hashData(Yii::$app->security->generateRandomString(), $this->password_salt);
                }
            }
        });
    }
    
    private $_setting;
    
    /**
     * Get user settings objects.
     *
     * @return \luya\admin\models\UserSetting
     */
    public function getSetting()
    {
        if ($this->_setting === null) {
            $settingsArray = (empty($this->settings)) ? [] : Json::decode($this->settings);
            $this->_setting = Yii::createObject(['class' => UserSetting::class, 'sender' => $this, 'data' => $settingsArray]);
        }
        
        return $this->_setting;
    }
    
    /**
     * Setter method for user settings which encodes the json.
     *
     * @param array $data
     */
    public function updateSettings(array $data)
    {
        return $this->updateAttributes(['settings' => Json::encode($data)]);
    }
    
    /**
     * Get the last login Timestamp
     */
    public function getLastloginTimestamp()
    {
        return $this->getUserLogins()->select(['timestamp_create'])->scalar();
    }
    
    
    /**
     * @inheritdoc
     */
    public static function ngRestApiEndpoint()
    {
        return 'api-admin-user';
    }
    
    /**
     * @inheritdoc
     */
    public static function ngRestFind()
    {
        return self::find()->andWhere(['is_api_user' => false]);
    }
    
    /**
     * @inheritdoc
     */
    public function ngRestListOrder()
    {
        return ['firstname' => SORT_ASC];
    }
    
    /**
     * @inheritdoc
     */
    public function ngRestAttributeTypes()
    {
        return [
            'title' => ['selectArray', 'data' => static::getTitles(), 'initValue' => 0],
            'firstname' => 'text',
            'lastname' => 'text',
            'email' => 'text',
            'password' => 'password',
            'login_attempt_lock_expiration' => 'datetime',
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function ngRestFilters()
    {
        return [
            'Removed' => self::find()->where(['is_deleted' => true, 'is_api_user' => false]),
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function ngRestExtraAttributeTypes()
    {
        return [
            'lastloginTimestamp' => 'datetime',
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function ngRestScopes()
    {
        return [
            ['list', ['firstname', 'lastname', 'email', 'lastloginTimestamp']],
            ['create', ['title', 'firstname', 'lastname', 'email', 'password']],
            ['update', ['title', 'firstname', 'lastname', 'email', 'login_attempt_lock_expiration']],
            ['delete', true],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function ngRestActiveWindows()
    {
        return [
            ['class' => UserHistorySummaryActiveWindow::class, 'label' => false],
            ['class' => ChangePasswordActiveWindow::class, 'label' => false],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'admin_user';
    }

    /**
     * @inheritdoc
     */
    public function genericSearchFields()
    {
        return ['firstname', 'lastname', 'email'];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'firstname', 'lastname', 'email', 'password'], 'required', 'on' => 'restcreate'],
            [['title', 'firstname', 'lastname', 'email'], 'required', 'on' => 'restupdate'],
            [['email', 'password'], 'required', 'on' => 'login'],
            [['secure_token'], 'required', 'on' => 'securelayer'],
            [['title', 'firstname', 'lastname', 'email', 'password'], 'required', 'on' => 'default'],
            [['email'], 'email'],
            [['email'], 'unique', 'except' => ['login']],
            [['auth_token'], 'unique'],
            [['settings'], 'string'],
            [['email_verification_token_timestamp', 'login_attempt', 'login_attempt_lock_expiration', 'is_deleted', 'is_api_user'], 'integer'],
            [['email_verification_token'], 'string', 'length' => 40],
            [['password'], StrengthValidator::class, 'when' => function () {
                return Module::getInstance()->strongPasswordPolicy;
            }, 'on' => ['restcreate', 'restupdate', 'default']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'title' => Module::t('mode_user_title'),
            'firstname' => Module::t('mode_user_firstname'),
            'lastname' => Module::t('mode_user_lastname'),
            'email' => Module::t('mode_user_email'),
            'password' => Module::t('mode_user_password'),
            'lastloginTimestamp' => Module::t('model_user_lastlogintimestamp'),
            'api_last_activity' => Module::t('model_user_api_last_activity'),
            'login_attempt_lock_expiration' => Module::t('model_user_login_attempt_lock_expiration'),
            'email_verification_token' => Module::t('model_user_email_verification_token'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'restcreate' => ['title', 'firstname', 'lastname', 'email', 'password'],
            'restupdate' => ['title', 'firstname', 'lastname', 'email', 'login_attempt_lock_expiration'],
            'changepassword' => ['password', 'password_salt'],
            'login' => ['email', 'password', 'force_reload'],
            'securelayer' => ['secure_token'],
            'default' => ['title', 'firstname', 'lastname', 'email', 'password', 'force_reload', 'settings'],
        ];
    }

    /**
     * Generate an easy readable random token.
     *
     * @param number $length
     * @return mixed
     * @since 1.2.0
     */
    private function generateToken($length = 6)
    {
        $token = Yii::$app->security->generateRandomString($length);
        $replace = array_rand(range(2, 9));
        return str_replace(['-', '_', 'l', 1], $replace, strtolower($token));
    }
    
    /**
     * Generate, store and return the secure Login token.
     *
     * @return string
     */
    public function getAndStoreToken()
    {
        $token = $this->generateToken(6);
        
        $this->setAttribute('secure_token', sha1($token));
        $this->setAttribute('secure_token_timestamp', time());
        $this->update(false);

        return $token;
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        return parent::find()->where(['is_deleted' => false]);
    }

    /**
     * @inheritdoc
     */
    public function changePassword($newpass)
    {
        $this->password = $newpass;

        if ($this->encodePassword()) {
            if ($this->save()) {
                return true;
            }
        }

        return $this->addError('newpass', 'Error while saving new password.');
    }
    
    /**
     * Encodes the current active record password field.
     * @return boolean
     */
    public function encodePassword()
    {
        if (!$this->validate(['password'])) {
            return false;
        }
        
        // create random string for password salting
        $this->password_salt = Yii::$app->getSecurity()->generateRandomString();
        // store the password
        $this->password = Yii::$app->getSecurity()->generatePasswordHash($this->password.$this->password_salt);

        return true;
    }
    
    /**
     * Get the title Mr, Mrs. as string for the current user.
     *
     * @return string
     */
    public function getTitleNamed()
    {
        return !isset(self::getTitles()[$this->title]) ?: self::getTitles()[$this->title];
    }

    /**
     * Returns the available titles (mr, mrs index by numberic identifier
     *
     * @return array
     */
    public static function getTitles()
    {
        return [
            1 => Module::t('model_user_title_mr'),
            2 => Module::t('model_user_title_mrs'),
        ];
    }

    /**
     * Return sensitive fields from api exposure.
     *
     * {@inheritDoc}
     * @see \yii\db\BaseActiveRecord::fields()
     */
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['password'], $fields['password_salt'], $fields['auth_token'], $fields['is_deleted'], $fields['email_verification_token'], $fields['cookie_token'], $fields['secure_token'], $fields['settings']);
        return $fields;
    }

    /**
     * Return the current related groups.
     * @return \yii\db\ActiveQuery
     */
    public function getGroups()
    {
        return $this->hasMany(Group::class, ['id' => 'group_id'])->viaTable('admin_user_group', ['user_id' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public function extraFields()
    {
        return ['groups', 'lastloginTimestamp'];
    }

    // AuthMethods

    /**
     * Finds a current user for a given email.
     *
     * This is used for the login form, and can therefore not be used for api users (since 1.1.0)
     *
     * @param string $email The email address to find the user from.
     * @return \yii\db\ActiveRecord|null
     */
    public static function findByEmail($email)
    {
        return self::find()->where(['email' => $email, 'is_deleted' => false, 'is_api_user' => false])->one();
    }

    /**
     * Validates the password for the current given user.
     *
     * @param string $password The plain user input password.
     * @return boolean
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password.$this->password_salt, $this->password);
    }
    
    /**
     * Get the user logins for the given user.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserLogins()
    {
        return $this->hasMany(UserLogin::class, ['user_id' => 'id']);
    }
    
    /**
     * Get all ngrest log entries for this user.
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNgrestLogs()
    {
        return $this->hasMany(NgrestLog::class, ['user_id' => 'id']);
    }
    
    // Change e-mail
    
    /**
     * Generate and save a email verification token and return the token.
     *
     * @return mixed
     * @since 1.2.0
     */
    public function getAndStoreEmailVerificationToken()
    {
        $token = $this->generateToken(6);
        
        $this->updateAttributes([
            'email_verification_token' => sha1($token),
            'email_verification_token_timestamp' => time(),
        ]);
        
        return $token;
    }
    
    /**
     * Reset the user model email verification token and timestamp
     *
     * @since 1.2.0
     */
    public function resetEmailVerification()
    {
        $this->updateAttributes([
            'email_verification_token' => null,
            'email_verification_token_timestamp' => null,
        ]);
    }

    // IdentityInterface

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::find()->joinWith(['userLogins ul'])->andWhere(['admin_user.id' => $id, 'is_destroyed' => false, 'is_api_user' => false, 'ip' => Yii::$app->request->userIP])->one();
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        if (empty($token) || !is_scalar($token)) {
            throw new InvalidArgumentException("The provided access token is invalid.");
        }
        
        $user = static::findOne(['auth_token' => $token]);
        // if the given user can be found, udpate the api last activity timestamp.
        if ($user) {
            $user->updateAttributes(['api_last_activity' => time()]);
        }
        
        return $user;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getAuthToken()
    {
        return $this->auth_token;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return false;
    }
}
