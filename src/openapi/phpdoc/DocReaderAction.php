<?php

namespace luya\admin\openapi\phpdoc;

use luya\helpers\Inflector;
use ReflectionClass;
use yii\base\Controller;
use yii\base\InlineAction;

class DocReaderAction extends BaseDocReader
{
    protected $controller;

    protected $reflection;
 
    protected $actionObject;

    public function __construct(Controller $controller, $actionName)
    {
        $this->controller = $controller;
        $this->actionObject = $controller->createAction($actionName);
        if ($this->actionObject instanceof InlineAction) {
            // read data from: actionMethodName()
            $reflector = new ReflectionClass(get_class($controller));
            $this->reflection = $reflector->getMethod('action'.Inflector::id2camel($actionName));
        } elseif ($this->actionObject) {
            $this->reflection = new ReflectionClass(get_class($this->actionObject));
        }
    }

    public function getControllerObject()
    {
        return $this->controller;
    }

    public function getActionObject()
    {
        return $this->actionObject;
    }

    public function getReflection()
    {
        return $this->reflection;
    }
}