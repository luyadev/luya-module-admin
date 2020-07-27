<?php

namespace luya\admin\openapi\events;

use yii\base\Event;

class PathParametersEvent extends Event
{
    public $params = [];

    public $controllerClass;

    public $actionClass;

    public $verbName;
}