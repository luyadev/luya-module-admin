<?php

namespace luya\admin\openapi\phpdoc;

use luya\helpers\Inflector;
use ReflectionClass;
use yii\base\Controller;
use yii\base\InlineAction;

class DocReaderAction extends BaseDocReader
{
    protected $controller;
 
    protected $actioName;

    public function __construct(Controller $controller, $actionName)
    {
        $this->controller = $controller;
        $this->actioName = $actionName;
    }

    /**
     * {@inheritDoc}
     */
    public function getControllerObject()
    {
        return $this->controller;
    }

    private $_actionObject;
    /**
     * {@inheritDoc}
     */
    public function getActionObject()
    {
        if ($this->_actionObject === null) {
            $this->_actionObject = $this->getControllerObject()->createAction($this->actioName);
        }

        return $this->_actionObject;
    }

    private $_reflection;
    /**
     * {@inheritDoc}
     */
    public function getReflection()
    {
        if ($this->getActionObject() instanceof InlineAction) {
            // read data from: actionMethodName()
            $reflector = new ReflectionClass(get_class($this->getControllerObject()));
            return $reflector->getMethod('action'.Inflector::id2camel($this->actioName));
        }

        return new ReflectionClass(get_class($this->getActionObject()));
    }
}