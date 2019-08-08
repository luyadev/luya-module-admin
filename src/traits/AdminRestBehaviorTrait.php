<?php

namespace luya\admin\traits;

use Yii;
use luya\admin\behaviors\UserRequestBehavior;
use luya\admin\Module as AdminModule;
use luya\admin\models\ApiUser;
use luya\helpers\ObjectHelper;
use yii\base\InvalidConfigException;

trait AdminRestBehaviorTrait
{
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
            $methods[] = [
                'class' => 'sizeg\jwt\JwtHttpBearerAuth',
                'auth' => [$this, 'authJwtUser'],
            ];
        }

        return $methods;
    }

    /**
     * Authenticate the jwt user and return the API User
     *
     * @param [type] $token
     * @param [type] $authMethod
     * @return boolean|ApiUser
     */
    public function authJwtUser($token, $authMethod)
    {
        $modelClass = Yii::createObject($this->_module->jwtAuthModel);

        if (!ObjectHelper::isInstanceOf($modelClass, 'luya\admin\base\JwtIdentityInterface', false)) {
            throw new InvalidConfigException("The jwtAuthModel must implement the JwtIdentityInterface interface.");
        }

        $auth = $modelClass::loginByJwtToken($token);

        // validation was success, now return the API user in terms of permissions:
        if ($auth) {
            return ApiUser::find()->andWhere(['email' => $this->_module->jwtApiUserEmail, 'is_api_user' => true])->one();
        }

        return false;
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