<?php

namespace luya\admin\openapi\phpdoc;

use ReflectionClass;
use ReflectionMethod;
use Reflector;

/**
 * PHP Doc Reflection object.
 * 
 * @author Basil Suter <git@nadar.io>
 * @since 3.2.0
 */
class PhpDocReflection
{
    protected $reflection;

    /**
     *
     * @param ReflectionMethod|ReflectionClass $reflection
     */
    public function __construct(Reflector $reflection)
    {
        $this->reflection = $reflection;
    }

    public function getDocComment()
    {
        return $this->reflection->getDocComment();
    }
}