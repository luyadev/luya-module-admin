<?php

namespace luya\admin\openapi\specs;

use luya\helpers\Inflector;
use ReflectionClass;
use yii\base\Controller;
use yii\base\InlineAction;

/**
 * Specs from an Action.
 *
 * @author Basil Suter <git@nadar.io>
 * @since 3.2.0
 */
class ControllerActionSpecs extends BaseSpecs
{
    protected $controller;
 
    protected $actioName;

    protected $verbName;

    public function __construct(Controller $controller, $actionName, $verbName)
    {
        $this->controller = $controller;
        $this->actioName = $actionName;
        $this->verbName = $verbName;
    }

    /**
     * {@inheritDoc}
     */
    public function getVerbName()
    {
        return strtolower($this->verbName);
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

    public function getActionName()
    {
        return 'action'.Inflector::id2camel($this->actioName);
    }

    /**
     * {@inheritDoc}
     */
    public function getReflection()
    {
        if ($this->getActionObject() instanceof InlineAction) {
            // read data from: actionMethodName()
            $reflector = new ReflectionClass(get_class($this->getControllerObject()));
            return $reflector->getMethod($this->getActionName());
        }

        return new ReflectionClass(get_class($this->getActionObject()));
    }
}
