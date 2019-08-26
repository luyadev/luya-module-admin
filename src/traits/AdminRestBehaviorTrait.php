<?php

namespace luya\admin\traits;

use luya\admin\base\JwtIdentityInterface;
use Yii;
use luya\admin\behaviors\UserRequestBehavior;
use luya\admin\Module as AdminModule;
use luya\admin\models\ApiUser;
use luya\helpers\ObjectHelper;
use yii\base\InvalidConfigException;

/**
 * A trait for LUYA admin rest behaviors.
 * 
 * Implemented by
 * 
 * + {{luya\admin\base\RestActiveController}}
 * + {{luya\admin\base\RestController}}
 * 
 * @author Basil Suter <basil@nadar.io>
 * @since 2.1.0
 */
trait AdminRestBehaviorTrait
{
    /**
     * @var \luya\admin\base\JwtIdentityInterface If an authentification trough jwt token happnes, this variable holds the jwt user identity.
     */
    public $jwtIdentity;
    
    /**
     * @var AdminModule
     */
    private $_module;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    
        $this->_module = AdminModule::getInstance();

        $this->enableCors = $this->_module->cors;
        $this->jsonCruft = $this->_module->jsonCruft;
    }
    
    /**
     * {@inheritDoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors[] = [
            'class' => UserRequestBehavior::class,
        ];

        return $behaviors;
    }

    /**
     * {@inheritDoc}
     */
    public function getCompositeAuthMethods()
    {
        $methods = parent::getCompositeAuthMethods();

        if ($this->_module->jwtAuthModel && $this->_module->jwtApiUserEmail) {
            array_unshift($methods, [
                'class' => 'sizeg\jwt\JwtHttpBearerAuth',
                'auth' => [$this, 'authJwtUser'],
            ]);
        }

        return $methods;
    }

    /**
     * Authenticate the jwt user and return the API User
     *
     * @param mixed $token
     * @param mixed $authMethod
     * @return boolean|ApiUser
     */
    public function authJwtUser($token, $authMethod)
    {
        $modelClass = Yii::createObject($this->_module->jwtAuthModel);

        if (!ObjectHelper::isInstanceOf($modelClass, JwtIdentityInterface::class, false)) {
            throw new InvalidConfigException("The jwtAuthModel must implement the JwtIdentityInterface interface.");
        }

        $auth = $modelClass::loginByJwtToken($token);

        // validation was success, now return the API user in terms of permissions:
        if ($auth && ObjectHelper::isInstanceOf($auth, JwtIdentityInterface::class, false)) {
            $this->jwtIdentity = $auth;

            // login the api user to the adminuser component.
            $user = ApiUser::find()->andWhere(['email' => $this->_module->jwtApiUserEmail, 'is_api_user' => true])->one();

            if (!$user) {
                throw new InvalidConfigException("The jwt api user could not be found. Ensure `jwtApiUserEmail` is configured property.");
            }

            return $this->userAuthClass()->loginByAccessToken($user->auth_token, 'sizeg\jwt\JwtHttpBearerAuth');
        }

        return null;
    }

    /**
     * Get the current user auth object.
     *
     * @return \luya\admin\components\AdminUser
     */
    public function userAuthClass()
    {
        return Yii::$app->adminuser;
    }
}