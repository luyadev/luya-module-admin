<?php

namespace luya\admin\openapi\phpdoc;

use luya\helpers\StringHelper;
use Reflector;
use Yii;

/**
 * Create Parser Object from reflection class to read PhpDocs.
 *
 * @author Basil Suter <git@nadar.io>
 * @since 3.2.0
 */
class PhpDocParser
{
    /**
     * @var PhpDocReflection
     */
    public $reflection;

    public $rows = [];

    public function __construct(Reflector $reflection)
    {
        $this->reflection = new PhpDocReflection($reflection);
        $this->rows = $this->parseDocBlockToArrays($this->reflection);

        Yii::debug("PhpDocParser for file '{$reflection->name}'", __METHOD__);
    }

    protected function parseDocBlockToArrays(PhpDocReflection $reflection)
    {
        $rows = [
            'texts' => [],
            'return' => [], // @return <type> <description>
            'author' => [],
            'param' => [], // @param <type> $firstname <description>
            'deprecated' => [],
            'see' => [],
            'link' => [],
            'since' => [],
            'var' => [],
            'property' => [], // @property <type> $firstname <description>,
            'uses' => [], // @uses <type> <description>,
            'method' => [], // @method <returnType> <actionName> <description>
        ];

        foreach (explode(PHP_EOL, $reflection->getDocComment()) as $row) {
            $row = ltrim($row);
            if (in_array($row, ["/**", "/*", "*/"])) {
                continue;
            }
            $row = ltrim($row, "* ");

            if (substr($row, 0, 1) == '@') {
                if (StringHelper::startsWith($row, '@param') || StringHelper::startsWith($row, '@property')) {
                    preg_match("/^(@[a-z]+)\s+([^\s]+)\s+([^\s]+)\s*(.*)$/", $row, $matches, 0, 0);
                    unset($matches[0]);
                } elseif (StringHelper::startsWith($row, '@method')) {
                    preg_match("/^(@[a-z]+)\s+([^\s]+)\s+([a-zA-Z]+)\((.*)\)\s+(.*)$/", $row, $matches, 0, 0);
                    unset($matches[0]);
                } else {
                    preg_match("/^(@[a-z]+)\s+([^\s]+)\s*(.*)$/", $row, $matches, 0, 0);
                    unset($matches[0]);
                }

                if (isset($matches[1])) {
                    $rows[substr($matches[1], 1)][] = array_values($matches);
                }
            } else {
                $rows['texts'][] = $row;
            }
        }

        return $rows;
    }

    /**
     * Returns all PhpDoc @method definitions
     *
     * @return PhpDocMethod[] An array where the key is the methodName like `actionIndex`
     * @since 3.3.1
     */
    public function getMethods()
    {
        $methods = [];
        foreach ($this->rows['method'] as $method) {
            $phpDocMethod = new PhpDocMethod($this, $method);
            $methods[$phpDocMethod->getMethodName()] = $phpDocMethod;
        }

        return $methods;
    }

    /**
     * Get a PhpDoc @method definition by its name
     *
     * @param string $name The name of the action which is defined, for example `actionIndex`
     * @return PhpDocMethod
     * @since 3.3.1
     */
    public function getMethod($name)
    {
        return $this->getMethods()[$name] ?? false;
    }

    /**
     * Get all @property
     *
     * @return PhpDocParam[]
     */
    public function getProperties()
    {
        $properties = [];
        foreach ($this->rows['property'] as $param) {
            $properties[] = new PhpDocParam($this, $param);
        }

        return $properties;
    }

    /**
     *
     *
     * @param string $paramName
     * @return PhpDocParam
     */
    public function getProperty($propertyName)
    {
        $properties = $this->rows['property'];

        foreach ($properties as $p) {
            if (isset($p[2]) && ltrim(strtolower($p[2]), '$') == strtolower($propertyName)) {
                return new PhpDocParam($this, $p);
            }
        }

        return new PhpDocParam($this, []);
    }

    /**
     * Get all @param
     *
     * @return PhpDocParam[]
     */
    public function getParams()
    {
        $params = [];
        foreach ($this->rows['param'] as $param) {
            $params[] = new PhpDocParam($this, $param);
        }

        return $params;
    }

    /**
     * Get uses
     *
     * @return PhpDocUses[]
     * @since 3.3.0
     */
    public function getUses()
    {
        $uses = [];

        foreach ($this->rows['uses'] as $use) {
            $uses[] = new PhpDocUses($this, $use);
        }

        return $uses;
    }

    /**
     *
     *
     * @param string $paramName
     * @return PhpDocParam
     */
    public function getParam($paramName)
    {
        $params = $this->rows['param'];

        foreach ($params as $p) {
            if (isset($p[2]) && ltrim(strtolower($p[2]), '$') == strtolower($paramName)) {
                return new PhpDocParam($this, $p);
            }
        }

        return new PhpDocParam($this, []);
    }

    /**
     * Return types to a single type.
     *
     * Assuming `boolean|string|array` it will return the `boolean` unless $preferred is configured.
     *
     * @param string $schema
     * @param string $preferred
     * @return string
     */
    public function typesTotype($schema, $preferred = null)
    {
        $values = explode("|", $schema);

        if (count($values) > 1) {
            return $this->normalizeTypes(current($values));
        }

        return $schema;
    }

    /**
     * Ensure types are always the same not in short form.
     *
     * @param string $type
     * @return string
     */
    public function normalizeTypes($type)
    {
        $type = strtolower((string) $type);

        // fix not supported short form types
        if ($type == 'bool') {
            $type = 'boolean';
        }
        if ($type == 'int') {
            $type = 'integer';
        }

        // convert types to number
        if (in_array($type, ['float', 'double', 'int32', 'int64'])) {
            $type = 'number';
        }

        // ensure uncovered types
        if (!in_array($type, ['array', 'boolean', 'integer', 'number', 'object', 'string'])) {
            return 'string';
        }

        return $type;
    }

    /**
     * Return
     *
     * @return PhpDocReturn
     */
    public function getReturn()
    {
        $params = $this->rows['return'];
        // as it should have only 1 return!

        return new PhpDocReturn($this, empty($params) ? [] : current($params));
    }

    public function getShortSummary()
    {
        return current($this->rows['texts']);
    }

    public function getLongDescription()
    {
        $content = [];
        foreach ($this->rows['texts'] as $key => $row) {
            if ($key == 0) {
                continue;
            }

            if ($key == 1 && empty($row)) {
                continue;
            }

            $content[] = $row;
        }

        return implode(PHP_EOL, $content);
    }

    /**
     * Returns all use statements from a class file.
     *
     * + luya\cms\models\NavContainer
     * + luya\cms\models\NavItemModule
     * + luya\base\DynamicModel as FooBar
     *
     * @return array
     */
    public function getUseClasses()
    {
        $file = $this->reflection->getFileName();
        $tokens = \PhpToken::tokenize(file_get_contents($file));

        $parts = [];
        $startCapture = 0;
        foreach ($tokens as $token) {
            if (is_array($token)) {
                if ($token[0] == T_DOC_COMMENT) {
                    break;
                }

                if ($token[0] == T_USE) {
                    // use starts
                    $startCapture = $startCapture + 1;
                }

                if ($startCapture > 0) {
                    $parts[$startCapture][] = $token->text;
                }
            }
        }

        foreach ($parts as $k => $part) {
            $parts[$k] = str_replace("use ", "", trim(implode("", $part)));
        }

        return $parts;
    }

    /**
     * Search for a given class inside the use statement and return the fully qualified path.
     *
     * @param string $className Search for `User` should return `app\models\User`
     * @return string
     */
    public function ensureClassName($className)
    {
        $className = strtolower($className);
        foreach ($this->getUseClasses() as $name) {
            if (StringHelper::contains(' as ', strtolower($name))) {
                $items = explode(" as ", $name);
                $aliasName = end($items);
                if (trim($aliasName) == $className) {
                    return $name;
                }
            } else {
                $items = explode("\\", strtolower($name));

                $lastItem = end($items);

                if (trim($lastItem) == $className) {
                    return $name;
                }
            }
        }

        return false;
    }
}
