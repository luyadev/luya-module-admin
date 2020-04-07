<?php

namespace luya\admin\openapi;

use luya\helpers\Inflector;

/**
 * AbsoluteRoute: admin/user/index or admin/api-cms-admin/create
 * controllerMapRoute: admin/api-cms-admin or admin/api-cms-nav
 */
abstract class BasePathParser implements RouteParserInterface
{
    public function routeToTag($route)
    {
        $route = str_replace(["admin/api", "admin/"], '', $route);

        return Inflector::camel2words(Inflector::id2camel($route));
    }
}