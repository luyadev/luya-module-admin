<?php

namespace luya\admin\openapi\phpdoc;

use luya\helpers\StringHelper;
use ReflectionClass;

/**
 * A Type Object whether for Return or Param.
 *
 * @author Basil Suter <git@nadar.io>
 * @since 3.2.0
 */
class PhpDocType
{
    /**
     * @var string Contains the type like `integer`, `string`, noramlized as lowercase value.
     */
    public $name;

    /**
     * @var string Contains the original raw name.
     */
    public $rawName;

    /**
     * @var PhpDocParser
     */
    protected $phpDocParser;

    public function __construct(PhpDocParser $phpDocParser, $type)
    {
        $this->rawName = $type;
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

    public function getIsClass()
    {
        return !empty($this->getClassName());
    }

    private $_className;

    public function getClassName()
    {
        if ($this->_className !== null) {
            return $this->_className;
        }

        if ($this->getIsScalar() || $this->getIsEmpty()) {
            $this->_className = false;
            return false;
        }

        if (class_exists($this->rawName)) {
            $this->_className = $this->rawName;
            return $this->name;
        }

        // test relative classNames when objects are in the same namespace
        $absoluteClassName = $this->phpDocParser->reflection->getNamespaceName() . '\\' . $this->rawName;
        if (class_exists($absoluteClassName)) {
            $this->_className = $absoluteClassName;
            return $this->name;
        }

        // get the

        $ensureClassName = $this->phpDocParser->ensureClassName($this->name);

        if ($ensureClassName && class_exists($ensureClassName)) {
            $this->_className = $ensureClassName;
            return $ensureClassName;
        }

        $this->_className = false;
        return false;
    }

    private $_phpDocParser;

    /**
     * Get PhpDocParser from className definition.
     *
     * @return PhpDocParser
     */
    public function getClassPhpDocParser()
    {
        if ($this->_phpDocParser === null) {
            $this->_phpDocParser = new PhpDocParser(new ReflectionClass($this->getClassName()));
        }

        return $this->_phpDocParser;
    }

    public function getNoramlizeName()
    {
        return $this->phpDocParser->typesTotype($this->name);
    }
}
