<?php

namespace luya\admin\openapi;

use luya\helpers\Inflector;

abstract class BaseParser
{
    public function routeToTag($route)
    {
        $route = str_replace(["admin/api", "admin/"], '', $route);

        return Inflector::camel2words(Inflector::id2camel($route));
    }
}