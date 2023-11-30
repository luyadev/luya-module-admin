<?php

namespace luya\admin\components;

use luya\admin\models\UserLogin;
use luya\admin\models\UserLoginLockout;
use luya\admin\models\UserOnline;
use Yii;
use yii\web\User;
use yii\web\UserEvent;

/**
 * AdminUser Component.
 *
 * The administration user Identity extends from {{yii\web\User}} in order to configure customized behaviors.
 *
 * @property \luya\admin\models\User $identity The user identitity object.
 * @property string $interfaceLanguage
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class AdminUser extends User
{
    /**
     * @inheritdoc
     */
    public $identityClass = '\luya\admin\models\User';

    /**
     * @inheritdoc
     */
    public $loginUrl = ['/admin/login/index'];

    /**
     * @inheritdoc
     */
    public $identityCookie = ['name' => '_adminIdentity', 'httpOnly' => true];

    /**
     * @inheritdoc
     */
    public $enableAutoLogin = true;

    /**
     * @var integer The number of seconds the web cookie with auto login should be active
     * @since 3.0.0
     */
    public $cookieLoginDuration = 2_592_000; // 30 days (60 * 60 * 24 * 30)

    /**
     * @var string Variable to assign the default language from the admin module in order to set default language if not set.
     */
    public $defaultLanguage;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        //$this->idParam = '__luyaAdminId_' . md5(Yii::$app->id);
        $this->idParam = $this->uniqueHostVariable('id');

        $this->on(self::EVENT_BEFORE_LOGOUT, [$this, 'onBeforeLogout']);
        $this->on(self::EVENT_AFTER_LOGIN, [$this, 'onAfterLogin']);
    }

    public function uniqueHostVariable($key)
    {
        return '__luyaAdmin_' . md5(Yii::$app->id) . '_' . $key;
    }

    /**
     * After the login process of the user, set the admin interface language based on the user settings.
     * @param UserEvent $event
     */
    public function onAfterLogin(UserEvent $event)
    {
        if (!$this->identity->is_api_user) {
            Yii::$app->language = $this->getInterfaceLanguage();
            // remove all lockout entrys for the given ip
            UserLoginLockout::deleteAll(['ip' => Yii::$app->request->userIP]);
        }
    }

    /**
     * Return the interface language for the given logged in user.
     *
     * @return string
     */
    public function getInterfaceLanguage()
    {
        return $this->getIsGuest() ? $this->defaultLanguage : $this->identity->setting->get('luyadminlanguage', $this->defaultLanguage);
    }

    /**
     * After loging out, the useronline status must be refreshed and the current user must be deleted from the user online list.
     */
    public function onBeforeLogout()
    {
        UserOnline::removeUser($this->id);

        $this->identity->updateAttributes([
            'auth_token' => Yii::$app->security->hashData(Yii::$app->security->generateRandomString(), $this->identity->password_salt),
        ]);

        // kill all user logins for the given user
        UserLogin::updateAll(['is_destroyed' => true], ['user_id' => $this->id]);
    }

    /**
     * Perform a can api match request for the logged in user if user is logged in, returns false otherwhise.
     *
     * See the {{luya\admin\components\Auth::matchApi}} for details.
     *
     * @param string $apiEndpoint
     * @param string $typeVerification
     * @return boolean Whether the current user can request the provided api endpoint.
     */
    public function canApi($apiEndpoint, $typeVerification = false)
    {
        return !$this->isGuest && Yii::$app->auth->matchApi($this->getId(), $apiEndpoint, $typeVerification);
    }

    /**
     * Perform a can route auth request match for the logged in user if user is logged in, returns false otherwhise.
     *
     * See the {{luya\admin\components\Auth::matchRoute}} for details.
     *
     * @param string $route
     * @return bool Whether the current user can request the provided route.
     */
    public function canRoute($route)
    {
        return !$this->isGuest && Yii::$app->auth->matchRoute($this->getId(), $route);
    }
}
