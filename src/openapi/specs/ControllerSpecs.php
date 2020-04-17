<?php

namespace luya\admin\openapi\specs;

use ReflectionClass;
use yii\base\Controller;

/**
 * Specs of a Controller.
 *
 * @author Basil Suter <git@nadar.io>
 * @since 3.2.0
 */
class ControllerSpecs extends BaseSpecs
{
    protected $reflection;

    protected $controller;

    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
        $this->reflection = new ReflectionClass(get_class($controller));
    }

    /**
     * {@inheritDoc}
     */
    public function getControllerObject()
    {
        return $this->controller;
    }

    /**
     * {@inheritDoc}
     */
    public function getActionObject()
    {
        return $this->controller;
    }

    /**
     * {@inheritDoc}
     */
    public function getReflection()
    {
        return $this->reflection;
    }
}
