<?php

namespace luya\admin\base;

use Yii;
use luya\rest\UserBehaviorInterface;
use luya\rest\Controller;
use yii\web\ForbiddenHttpException;
use luya\admin\models\UserOnline;
use luya\admin\Module as AdminModule;

/**
 * Base class for RestControllers.
 *
 * provides the basic functionality to access and serialize this controller via rest
 * api. Does not define the method names!
 *
 *```php
 * class TestController extends \luya\admin\base\RestController
 * {
 *     public function actionFooBar()
 *     {
 *         return ['foo', 'bar'];
 *     }
 * }
 * ```
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class RestController extends Controller implements UserBehaviorInterface
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        
        $this->enableCors = AdminModule::getInstance()->cors;
        $this->jsonCruft = AdminModule::getInstance()->jsonCruft;
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
    
    /**
     * Shorthand method to check whether the current use exists for the given route, otherwise throw forbidden http exception.
     *
     * @throws ForbiddenHttpException::
     * @since 1.1.0
     */
    public function checkRouteAccess($route)
    {
        UserOnline::refreshUser($this->userAuthClass()->identity, $route);
        
        if (!Yii::$app->auth->matchRoute($this->userAuthClass()->identity->id, $route)) {
            throw new ForbiddenHttpException('Unable to access this action due to insufficient permissions.');
        }
    }
}
