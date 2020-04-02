<?php

namespace luya\admin\openapi\phpdoc;

use ReflectionClass;
use yii\base\Controller;

class DocReaderController extends BaseDocReader
{
    protected $reflection;

    protected $controller;

    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
        $this->reflection = new ReflectionClass(get_class($controller));
    }

    public function getControllerObject()
    {
        return $this->controller;
    }

    public function getActionObject()
    {
        return $this->controller;
    }

    public function getReflection()
    {
        return $this->reflection;
    }
}