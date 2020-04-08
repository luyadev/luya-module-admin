<?php

namespace luya\admin\openapi\phpdoc;

use luya\helpers\StringHelper;

/**
 * A Type Object whether for Return or Param.
 * 
 * @author Basil Suter <git@nadar.io>
 * @since 3.2.0
 */
class PhpDocType
{
    /**
     * @var string Contains the type like `integer`, `string`
     */
    public $name;

    protected $phpDocParser;

    public function __construct(PhpDocParser $phpDocParser, $type)
    {
        $this->name = $phpDocParser->normalizeTypes($type);   
        $this->phpDocParser = $phpDocParser;
    }

    public function getIsScalar()
    {
        return in_array($this->name, [
            'bool',
            'boolean',
            'string',
            'int',
            'integer',
            'float',
            'double',
            'mixed',
        ]);
    }

    public function getIsEmpty()
    {
        return empty($this->name) || $this->name == 'void';
    }

    public function getIsVoid()
    {
        return $this->name == 'void';
    }

    public function getIsObject()
    {
        return in_array($this->name, [
            'object',
            'resource',
        ]);
    }

    public function getIsArray()
    {
        return StringHelper::contains('[]', $this->name) || in_array($this->name, [
            'array',
            'iterable',
        ]);
    }

    public function getClassName()
    {
        if ($this->getIsScalar() || $this->getIsEmpty()) {
            return false;
        }

        if (class_exists($this->name)) {
            return $this->name;
        }

        // get the 

        $ensureClassName = $this->phpDocParser->ensureClassName($this->name);

        if ($ensureClassName && class_exists($ensureClassName)) {
            return $ensureClassName;
        }

        return false;
    }

    public function getNoramlizeName()
    {
        return $this->phpDocParser->typesTotype($this->name);
    }
}