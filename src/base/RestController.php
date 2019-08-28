<?php

namespace luya\admin\base;

use Yii;
use luya\rest\UserBehaviorInterface;
use luya\rest\Controller;
use yii\web\ForbiddenHttpException;
use luya\admin\models\UserOnline;
use luya\admin\traits\AdminRestBehaviorTrait;

/**
 * Base class for RestControllers.
 *
 * Provides the basic functionality to access and serialize this controller via rest
 * api.
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
 * In order to make permission for a certain action the {{luya\admin\base\Module::extendPermissionRoutes()}} can be configured as followed:
 * 
 * ```php
 * public function extendPermissionRoutes()
 * {
 *      return [
 *          ['route' => 'mymodule/test/secure-action', 'alias' => 'Secure Action Name'],
 *      ];
 * }
 * ```
 * 
 * Now the permission call for this action must be done inside the controller action:
 * 
 * ```php
 * class TestController extends \luya\admin\base\RestController
 * {
 *     public function actionSecureAction()
 *     {
 *          $this->checkRouteAccess('secure-action');
 * 
 *          // do stuff as the permission is ensured.
 *     }
 * }
 * ```
 * 
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class RestController extends Controller implements UserBehaviorInterface
{
    use AdminRestBehaviorTrait;

    private $_permitted;
    
    /**
     * Shorthand method to check whether the current user exists for the given route, otherwise throw forbidden exception.
     *
     * @throws ForbiddenHttpException::
     * @since 1.1.0
     */
    public function checkRouteAccess($route)
    {
        if (!Yii::$app->auth->matchRoute($this->userAuthClass()->identity, $route)) {
            throw new ForbiddenHttpException("Unable to access action '$route' due to insufficient permissions.");
        }

        $this->_permitted = true;
        UserOnline::refreshUser($this->userAuthClass()->identity, $route);
    }

    /**
     * Ensure the action of rest controllers can not viewed by api users by default.
     *
     * @param \yii\base\Action $action
     * @return boolean
     */
    public function beforeAction($action)
    {
        $action = parent::beforeAction($action);

        if (!$this->_permitted) {
            $this->canApiUserAccess();
        }

        return $action;
    }
}
