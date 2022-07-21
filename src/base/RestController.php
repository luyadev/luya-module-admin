<?php

namespace luya\admin\base;

use luya\admin\models\UserOnline;
use luya\admin\traits\AdminRestBehaviorTrait;
use luya\rest\Controller;
use luya\rest\UserBehaviorInterface;
use Yii;
use yii\base\Action;
use yii\web\ForbiddenHttpException;

/**
 * Base class for RestControllers.
 *
 * > Read more about permissions: [[app-admin-module-permission.md]]
 * > Rest Controllers use `routes` as permission.
 *
 * Provides the basic functionality to access and serialize this controller via REST Api.
 *
 * An example action in the RestController:
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
 * > **An action is by default visible by authenticated users unless an {{luya\admin\base\Module::extendPermissionRoutes()}} is defined**
 *
 * In order to make permission for a certain actions or the whole controller the {{luya\admin\base\Module::extendPermissionRoutes()}} can be configured as followed:
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
 * As route `mymodule/test/secure-action` is now registered as permission the users group needs permission to run this route:
 *
 * ```php
 * class TestController extends \luya\admin\base\RestController
 * {
 *     public function actionSecureAction()
 *     {
 *          // do stuff as its protected by extend permission
 *     }
 * }
 * ```
 *
 * If there is **no extend route permission** entry this action endpoint is available to all authenticated users.
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
     * public function permissionRoute(Action $action)
     * {
     *      if ($action->id == 'my-index-action') {
     *           return 'module/index/action';
     *      }
     *
     *      return 'module/index/another-action';
     * }
     * ```
     *
     * Keep in mind this permission route check is mainly to determine if an action exists.
     *
     * > If the permission route returns false, this means the given action does not require a permission.
     *
     * @param \yii\base\Action $action
     * @return string
     * @since 2.2.0
     */
    public function permissionRoute(Action $action)
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
            if ($this->isActionAuthOptional($action->id)) {
                return true;
            }

            $route = $this->permissionRoute($action);

            // check whether for the current route exists a permission entry
            // if the permission entry exists, a checkRouteAccess() must be done.
            // otherwise just check whether api user can access the api without permission entry.
            if ($route && Yii::$app->auth->isInRoutePermissionTable($route)) {
                $this->checkRouteAccess($route);
            } else {
                $this->canApiUserAccess();
            }

            return true;
        }

        return false;
    }
}
