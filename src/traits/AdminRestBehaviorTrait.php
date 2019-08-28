<?php

namespace luya\admin\traits;

use Yii;
use luya\admin\behaviors\UserRequestBehavior;
use luya\admin\Module as AdminModule;

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

        // if the jwt component is registered, authentication will be enabled.
        if (Yii::$app->get('jwt', false)) {
            array_unshift($methods, [
                'class' => 'sizeg\jwt\JwtHttpBearerAuth',
                'auth' => [Yii::$app->jwt, 'authenticateUser'],
            ]);
        }

        return $methods;
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