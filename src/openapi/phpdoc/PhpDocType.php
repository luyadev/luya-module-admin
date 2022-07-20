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
        $this->rawName = (string) $type;
        $this->name = $phpDocParser->normalizeTypes($type);
        $this->phpDocParser = $phpDocParser;
    }

    public function getIsScalar()
    {
        return in_array($this->rawName, [
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
        return StringHelper::contains('[]', $this->rawName) || in_array($this->name, [
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

        // array notation like Users[]
        if (StringHelper::contains('[]', $this->rawName)) {
            $className = str_replace("[]", '', $this->rawName);
            if (($class = $this->testValidClassName($className))) {
                $this->_className = $class;
                return $class;
            }
        }

        if (($class = $this->testValidClassName($this->rawName))) {
            $this->_className = $class;
            return $class;
        }

        if (($class = $this->testValidClassName($this->name))) {
            $this->_className = $class;
            return $class;
        }

        $this->_className = false;
        return false;
    }

    protected function testValidClassName($className)
    {
        if (class_exists($className)) {
            return $className;
        }

        // test relative classNames when objects are in the same namespace
        $absoluteClassName = $this->phpDocParser->reflection->getNamespaceName() . '\\' . $className;
        if (class_exists($absoluteClassName)) {
            return $absoluteClassName;
        }

        // Find alias definition `XYZ as ABC`
        $ensureClassName = $this->phpDocParser->ensureClassName($className);
        if ($ensureClassName && class_exists($ensureClassName)) {
            return $ensureClassName;
        }

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
