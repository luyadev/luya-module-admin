<?php

namespace luya\admin\openapi\phpdoc;

use luya\helpers\StringHelper;

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
            'array',
            'object',
            'callable',
            'resource',
            'mixed',
            'iterable',
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

    public function getIsClass()
    {
        if (class_exists($this->name)) {
            return true;
        }

        return false;
    }

    public function getNoramlizeName()
    {
        return $this->phpDocParser->typesTotype($this->name);
    }
}