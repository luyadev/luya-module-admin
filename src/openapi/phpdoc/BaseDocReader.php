<?php

namespace luya\admin\openapi\phpdoc;

use cebe\openapi\spec\MediaType;
use cebe\openapi\spec\Response;
use cebe\openapi\spec\Schema;
use luya\helpers\ArrayHelper;
use ReflectionClass;
use ReflectionMethod;

/**
 * Read PHP docs and interprets those.
 * 
 * + works with the class php doc block
 * + works with the method php doc block
 */
abstract class BaseDocReader
{
    /**
     * @return ReflectionClass|ReflectionMethod
     */
    abstract public function getReflection();

    public function getRows($reflection)
    {
        $rows = [
            'texts' => [],
            'params' => [],
        ];
        foreach(explode(PHP_EOL, $reflection->getDocComment()) as $row) {
            $row = ltrim($row);
            if (in_array($row, ["/**", "/*", "*/"])) {
                continue;
            }
            $row = ltrim($row, "* ");

            if (substr($row, 0, 1) == '@') {
                preg_match("/^(@[a-z]+)\s+([^\s]+)\s*(.*)$/", $row, $matches, 0, 0);
                unset($matches[0]);
                $rows['params'][] = array_values($matches);
            } else {
                $rows['texts'][] = $row;
            }
        }

        return $rows;
    }

    public function getResponses()
    {
        $response200 = new Response([]);
        $response200->description = 'OK';

        if ($this->getResponseContent()) {
            $response200->content = $this->getResponseContent();
        }

        return [
            200 => $response200,
        ];
    }

    public function getResponseContent()
    {
        $params = $this->getRows($this->getReflection())['params'];

        $return = ArrayHelper::searchColumn($params, 0, '@return');

        if (!$return) {
            return [];
        }

        $type = isset($return[1]) ? $return[1] : null;
        $description = isset($return[2]) ? $return[2] : '';

        if (empty($type)) {
            return [];
        }

        if (in_array($type, [
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
        ])) {
            if ($type == 'bool') {
                $type = 'boolean';
            }
            if ($type == 'int') {
                $type = 'integer';
            }
            if ($type == 'array') {
                return [
                    'application/json' => new MediaType([
                        'schema' => [
                            'type' => $type,
                            'items' => [],
                            'description' => $description,
                        ],
                    ])
                ];
                
            } else {
                return [
                    'application/json' => new MediaType([
                        'schema' => [
                            'type' => $type,
                            'description' => $description,
                        ],
                    ])
                ];
            }
            
        }

        // if type object including []
        /*
        schema:
                type: array
                items:
        */

        // return the object
        /*
        schema:
            properties
                id
                  - type
                    string
        */
        return [
            'application/json' => new MediaType([
                'schema' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => new Schema([
                            'type' => 'string',
                            'description' => 'Das ist der Primary KEY?',
                        ])
                    ]
                ],
            ])
        ];
    }

    public function generateShortSummary($reflection)
    {
        return current($this->getRows($reflection)['texts']);
    }

    public function generateLongDescription($reflection)
    {
        $content = [];
        foreach ($this->getRows($reflection)['texts'] as $key => $row) {
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
     * @return string An optional, string summary, intended to apply to all operations in this path.
     */
    public function getSummary()
    {
        return $this->generateShortSummary($this->getReflection());
    }

    /**
     * @return string An optional, string description, intended to apply to all operations in this path. CommonMark syntax MAY be used for rich text representation.
     */
    public function getDescription()
    {
        return $this->generateLongDescription($this->getReflection());
    }
}