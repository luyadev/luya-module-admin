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
    public function __construct(protected Controller $controller, protected $actioName, protected $verbName)
    {
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

    /**
     * Returns a full qualified action name.
     *
     * This is mainly used for {{yii\base\Controller::actions()}} definition.
     *
     * @return string Assuming the actionName is `update-row` then it will return `actionUpdateRow`.
     * @since 3.3.1
     */
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
            $reflector = new ReflectionClass($this->getControllerObject()::class);
            return $reflector->getMethod($this->getActionName());
        }

        return new ReflectionClass($this->getActionObject()::class);
    }
}
