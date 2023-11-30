<?php

namespace luya\admin\models;

use luya\admin\aws\ApiRequestInsightActiveWindow;
use luya\admin\aws\ChangePasswordActiveWindow;
use luya\admin\aws\ChangePasswordInterface;
use luya\admin\aws\UserHistorySummaryActiveWindow;
use luya\admin\base\RestActiveController;
use luya\admin\events\UserAccessTokenLoginEvent;
use luya\admin\Module;
use luya\admin\ngrest\base\NgRestModel;
use luya\admin\traits\SoftDeleteTrait;
use luya\helpers\Html;
use luya\helpers\Url;
use luya\validators\StrengthValidator;
use Yii;
use yii\base\InvalidArgumentException;
use yii\helpers\Json;
use yii\web\IdentityInterface;

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
 * @property UserSetting $setting Setting object to store data.
 * @property integer $is_api_user
 * @property integer $api_rate_limit
 * @property string $api_allowed_ips
 * @property integer $api_last_activity
 * @property string $email_verification_token
 * @property integer $email_verification_token_timestamp
 * @property integer $login_attempt
 * @property integer $login_attempt_lock_expiration
 * @property boolean $is_request_logger_enabled
 * @property int|null $login_2fa_enabled {@since 3.0.0}
 * @property string|null $login_2fa_secret {@since 3.0.0}
 * @property string|null $login_2fa_backup_key {@since 3.0.0}
 * @property string|null $password_verification_token {@since 3.0.0}
 * @property int|null $password_verification_token_timestamp {@since 3.0.0}
 * @property Group[] $groups Expand groups for this user
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class User extends NgRestModel implements IdentityInterface, ChangePasswordInterface
{
    use SoftDeleteTrait;
    public const USER_SETTING_ISDEVELOPER = 'isDeveloper';

    public const USER_SETTING_UILANGUAGE = 'luyadminlanguage';

    public const USER_SETTING_NEWUSEREMAIL = 'newUserEmail';

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
            'title' => ['selectArray', 'data' => static::getTitles()],
            'firstname' => ['text', 'inline' => true],
            'lastname' => ['text', 'inline' => true],
            'email' => 'text',
            'password' => 'password',
            'login_attempt_lock_expiration' => 'datetime',
            'is_request_logger_enabled' => 'toggleStatus',
        ];
    }

    /**
     * @inheritdoc
     */
    public function ngRestExport()
    {
        return [
            'id' => 'text',
            'lastname' => 'text',
            'firstname' => 'text',
            'email' => 'text',
            'login_attempt_lock_expiration' => 'datetime',
        ];
    }

    /**
     * @inheritdoc
     */
    public function ngRestFilters()
    {
        return [
            'Removed' => self::find()->where(['is_api_user' => false, 'is_deleted' => true]),
        ];
    }

    /**
     * @inheritdoc
     */
    public function ngRestExtraAttributeTypes()
    {
        return [
            'lastloginTimestamp' => ['datetime', 'sortField' => false],
        ];
    }

    /**
     * @inheritdoc
     */
    public function ngRestScopes()
    {
        return [
            ['list', ['title', 'firstname', 'lastname', 'email', 'lastloginTimestamp']],
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
            ['class' => ChangePasswordActiveWindow::class, 'label' => false],
            ['class' => UserHistorySummaryActiveWindow::class, 'label' => false],
            ['class' => ApiRequestInsightActiveWindow::class, 'label' => false],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admin_user}}';
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
            [['firstname', 'lastname', 'password', 'password_salt', 'cookie_token', 'api_allowed_ips', 'login_2fa_secret', 'login_2fa_backup_key'], 'string', 'max' => 255],
            [['email'], 'email'],
            [['email'], 'unique', 'except' => ['login']],
            [['email'], function ($attribute) {
                if (self::find()->where(['email' => $this->$attribute, 'is_deleted' => true])->exists()) {
                    $this->addError($attribute, Module::t('user_model_email_deleted_account_exists'));
                }
            }, 'except' => ['login']],
            [['auth_token'], 'unique'],
            [['settings'], 'string'],
            [['is_request_logger_enabled'], 'boolean'],
            [['email_verification_token_timestamp', 'login_attempt', 'login_attempt_lock_expiration', 'is_deleted', 'is_api_user', 'password_verification_token', 'password_verification_token_timestamp'], 'integer'],
            [['email_verification_token', 'secure_token', 'password_verification_token'], 'string', 'length' => 40],
            [['password'], StrengthValidator::class, 'when' => fn () => Module::getInstance()->strongPasswordPolicy, 'on' => ['restcreate', 'restupdate', 'default']],
            [['login_2fa_enabled'], 'integer'],
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
            'restcreate' => ['title', 'firstname', 'lastname', 'email', 'password', 'is_request_logger_enabled'],
            'restupdate' => ['title', 'firstname', 'lastname', 'email', 'login_attempt_lock_expiration', 'is_request_logger_enabled'],
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
    public function changePassword($newpass)
    {
        $this->password = $newpass;

        if ($this->encodePassword()) {
            if ($this->save(true, ['password', 'password_salt'])) {
                return true;
            }
        }

        $this->addError('newpass', Module::t('user_change_password_error'));
        return false;
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
        return $this->hasMany(Group::class, ['id' => 'group_id'])->viaTable('{{%admin_user_group}}', ['user_id' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public function extraFields()
    {
        return array_unique(array_merge(['groups', 'lastloginTimestamp'], parent::extraFields()));
    }

    /**
     * Devices Active Query
     *
     * @return UserDevice[]
     * @since 3.0.0
     */
    public function getDevices()
    {
        return $this->hasMany(UserDevice::class, ['user_id' => 'id']);
    }

    /**
     * Render user token based email:
     *
     * This is currently used for secure token and email validation tokens.
     *
     * @see https://mjml.io/try-it-live/Hk9rJe68B
     * @since 2.2.0
     */
    public static function generateTokenEmail($token, $title, $text)
    {
        return Yii::$app->view->render('@admin/views/mail/_token.php', [
            'url' => Url::domain(Url::base(true)),
            'token' => $token,
            'browser' => "-",
            'title' => $title,
            'text' => $text,
        ]);
    }

    public static function generateResetEmail($url, $title, $text)
    {
        return Yii::$app->view->render('@admin/views/mail/_reset.php', [
            'url' => Url::domain(Url::base(true)),
            'token' => Html::a(Module::t('reset_email_btn_label'), $url),
            'browser' => "-",
            'title' => $title,
            'text' => $text,
        ]);
    }

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
        return self::find()->andWhere(['email' => $email, 'is_api_user' => false])->one();
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
        $where = ['{{%admin_user}}.id' => $id, 'is_destroyed' => false, 'is_api_user' => false];
        if (Module::getInstance()->logoutOnUserIpChange) {
            $where['ip'] = Yii::$app->request->userIP;
        }
        return static::find()->joinWith(['userLogins ul'])->andWhere($where)->one();
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        if (empty($token) || !is_scalar($token)) {
            throw new InvalidArgumentException("The provided access token is invalid.");
        }

        $event = new UserAccessTokenLoginEvent();
        $event->type = $type;
        $event->token = $token;
        Yii::$app->trigger(Module::EVENT_USER_ACCESS_TOKEN_LOGIN, $event);

        if ($event->user) {
            $user = $event->user;
        } else {
            $user = static::findOne(['auth_token' => $token]);
        }

        // if the given user can be found, udpate the api last activity timestamp.
        if ($user && Module::getInstance()->apiUserTrackLastActivity) {
            $user->updateAttributes(['api_last_activity' => time()]);
        }

        // this ensures the user cookie won't be destroyed.
        Yii::$app->adminuser->enableAutoLogin = false;
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
    public function getAuthKey()
    {
        $userAgent = Yii::$app->request->userAgent;

        // no user agent, dissable auto login
        if (empty($userAgent)) {
            return false;
        }

        $checksum = UserDevice::generateUserAgentChecksum($userAgent);

        $model = UserDevice::find()->where(['user_id' => $this->id, 'user_agent_checksum' => $checksum])->one();

        if ($model) {
            // update last update timestamp and return existing auth key
            $model->touch('updated_at');
            return $model->auth_key;
        }

        $model = new UserDevice();
        $model->user_id = $this->id;
        $model->user_agent = $userAgent;
        $model->user_agent_checksum = $checksum;
        $model->auth_key = Yii::$app->security->generatePasswordHash(Yii::$app->security->generateRandomKey() . $checksum);

        if ($model->save()) {
            return $model->auth_key;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return UserDevice::find()->where(['auth_key' => $authKey, 'user_id' => $this->id])->exists();
    }
}
