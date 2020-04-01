<?php

namespace luya\admin\openapi\phpdoc;

use luya\helpers\Inflector;
use ReflectionClass;
use yii\base\Controller;
use yii\base\InlineAction;

class DocReaderAction extends BaseDocReader
{
    protected $reflection;
 
    protected $actionObject;

    public function __construct(Controller $controller, $actionName)
    {
        $this->actionObject = $controller->createAction($actionName);
        if ($this->actionObject instanceof InlineAction) {
            // read data from: actionMethodName()
            $reflector = new ReflectionClass(get_class($controller));
            $this->reflection = $reflector->getMethod('action'.Inflector::id2camel($actionName));
        } elseif ($this->actionObject) {
            $this->reflection = new ReflectionClass(get_class($this->actionObject));
        }
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