<?php

namespace luya\admin\openapi\phpdoc;

use luya\helpers\StringHelper;
use Reflector;

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
    protected $reflection;

    public $rows = [];

    public function __construct(Reflector $reflection)
    {
        $this->reflection = new PhpDocReflection($reflection);
        $this->rows = $this->parseDocBlockToArrays($this->reflection);
    }

    protected function parseDocBlockToArrays(PhpDocReflection $reflection)
    {
        $rows = [
            'texts' => [],
            'return' => [],
            'author' => [],
            'param' => [],
            'deprecated' => [],
            'see' => [],
            'link' => [],
            'since' => [],
            'var' => [],
        ];

        foreach(explode(PHP_EOL, $reflection->getDocComment()) as $row) {
            $row = ltrim($row);
            if (in_array($row, ["/**", "/*", "*/"])) {
                continue;
            }
            $row = ltrim($row, "* ");

            if (substr($row, 0, 1) == '@') {

                if (StringHelper::startsWith($row, '@param')) {
                    preg_match("/^(@[a-z]+)\s+([^\s]+)\s+([^\s]+)\s*(.*)$/", $row, $matches, 0, 0);
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

        return current($values);
    }

    /**
     * Ensure types are always the same not in short form.
     *
     * @param string $type
     * @return string
     */
    public function normalizeTypes($type)
    {
        // fix not supported short form types
        if ($type == 'bool') {
            $type = 'boolean';
        }
        if ($type == 'int') {
            $type = 'integer';
        }

        return strtolower($type);
    }

    /**
     * Undocumented function
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
}