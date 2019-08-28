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
    
    /**
     * Returns the default permission route to check. By default this will return 
     * the current route of the performed action.
     *
     * In order to override permission check use:
     * 
     * ```php
     * public function permissionRoute($action)
     * {
     *      return 'my/custom/route';
     * }
     * ```
     * 
     * Or to switch routes for given actions use:
     * 
     * ```php
     * public function permissionRoute($action)
     * {
     *      if ($action->id == 'my-index-action') {
     *           return 'module/index/action';
     *      }
     * 
     *      return 'module/index/another-action';
     * }
     * ```
     * 
     * Keep in mind this permission route check is mainly to determine if an action exists
     * 
     * @param \yii\base\Action $action
     * @return string
     * @since 2.2.0
     */
    public function permissionRoute($action)
    {
        return implode('/', [$action->controller->module->id, $action->controller->id, $action->id]);
    }
    
    /**
     * Shorthand method to check whether the current user exists for the given route, otherwise throw forbidden exception.
     *
     * @throws ForbiddenHttpException::
     * @since 1.1.0
     */
    public function checkRouteAccess($route)
    {
        if (!Yii::$app->auth->matchRoute($this->userAuthClass()->identity, $route)) {
            throw new ForbiddenHttpException("Unable to access route '$route' due to insufficient permissions.");
        }

        UserOnline::refreshUser($this->userAuthClass()->identity, $route);
    }

    /**
     * Ensure the action of rest controllers can not viewed by api users by default.
     *
     * @param \yii\base\Action $action
     * @return boolean
     * @since 2.2.0
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            $route = $this->permissionRoute($action);

            if ($this->isActionAuthOptional($action->id)) {
                return true;
            }
            // check whether for the current route exists a permission entry
            // if the permission entry exists, a checkRouteAccess() must be done.
            // otherwise just check whether api user can access the api without permission entry.
            if (Yii::$app->auth->isInRoutePermissionTable($route)) {
                $this->checkRouteAccess($route);
            } else {
                $this->canApiUserAccess();
            }

            return true;
        }

        return false;
    }
}
