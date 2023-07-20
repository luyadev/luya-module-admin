<?php

namespace luya\admin\openapi\phpdoc;

use luya\helpers\Inflector;
use luya\helpers\StringHelper;

/**
 * Php DOc Method
 *
 * The @method phpdoc describer.
 *
 * @author Basil <git@nadar.io>
 * @since 3.3.1
 */
class PhpDocMethod
{
    /**
     * Constructor
     *
     * @param PhpDocParser $phpDocParser
     * @param array $definition
     */
    public function __construct(protected PhpDocParser $phpDocParser, protected array $definition)
    {
    }

    /**
     * Type
     *
     * Returns a Type definition.
     *
     * @return PhpDocType
     */
    public function getReturnType()
    {
        return new PhpDocType($this->phpDocParser, $this->definition[1] ?? null);
    }

    /**
     * Method Name
     *
     * Returns only the method name without arguments, for example actionView($id) should return actionView
     *
     * @return string
     */
    public function getMethodName()
    {
        return isset($this->definition[2]) ? trim($this->definition[2]) : false;
    }

    /**
     * Returns a readable name for an action
     *
     * For example the action is actionFooBar it will return `Foo Bar`.
     *
     * @return void
     */
    public function getNormalizedMethodName()
    {
        $methodName = StringHelper::replaceFirst('action', '', $this->getMethodName());

        return Inflector::camel2words($methodName);
    }

    /**
     * Method Param Description
     *
     * Returns the definition of the action inside the () params.
     *
     * @return string
     */
    public function getMethodParams()
    {
        return isset($this->definition[3]) ? trim($this->definition[3]) : false;
    }

    /**
     * Description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->definition[4] ?? '';
    }
}
