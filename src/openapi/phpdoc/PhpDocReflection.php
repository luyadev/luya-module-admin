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
    /**
     *
     * @param ReflectionMethod|ReflectionClass $reflection
     */
    public function __construct(protected Reflector $reflection)
    {
    }

    public function getDocComment()
    {
        return $this->reflection->getDocComment();
    }

    public function getFileName()
    {
        return $this->reflection->getFileName();
    }

    public function getNamespaceName()
    {
        return $this->reflection->getNamespaceName();
    }
}
