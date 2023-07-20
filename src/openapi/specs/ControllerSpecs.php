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

    public function __construct(public Controller $controller)
    {
        $this->reflection = new ReflectionClass($controller::class);
    }

    /**
     * {@inheritDoc}
     */
    public function getVerbName()
    {
        return 'get';
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
