<?php

namespace luya\admin\openapi\phpdoc;

use cebe\openapi\spec\MediaType;
use cebe\openapi\spec\Parameter;
use cebe\openapi\spec\Response;
use cebe\openapi\spec\Schema;
use luya\admin\ngrest\base\Api;
use luya\helpers\ArrayHelper;
use luya\helpers\ObjectHelper;
use luya\helpers\StringHelper;
use ReflectionClass;
use ReflectionMethod;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\rest\Action;
use yii\rest\IndexAction;

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

    abstract public function getActionObject();

    abstract public function getControllerObject();

    public function getRows()
    {
        $reflection = $this->getReflection();
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

    public function getPhpDocParam($paramName)
    {
        $params = $this->getRows()['param'];

        foreach ($params as $p) {
            if (isset($p[2]) && ltrim(strtolower($p[2]), '$') == strtolower($paramName)) {
                return $p;
            }
        }

        return false;
    }

    public function getFirstSchemaType($schema)
    {
        $values = explode("|", $schema);

        return current($values);
    }

    public function getParameters()
    {
        $params = [];
        if ($this->getReflection() instanceof ReflectionMethod) {
            foreach ($this->getReflection()->getParameters() AS $arg) {

                $paramDoc = $this->getPhpDocParam($arg->getName());

                $params[] = new Parameter([
                    'name' => $arg->getName(),
                    'in' => 'query',
                    'required' => !$arg->isOptional(),
                    'description' => isset($paramDoc[3]) ? $paramDoc[3] : '',
                    'schema' => new Schema([
                        'type' => $this->getFirstSchemaType($paramDoc[1]),
                    ])
                ]);
            }
        }

        return $params;
        /*
        - in: query
        name: offset
        schema:
          type: integer
        description: The number of items to skip before starting to collect the result set
        */
    }

    public function getResponses()
    {
        $response = new Response([]);
        $response->description = $this->getPhpDocReturnDescription();

        
        if ($this->getResponseContent()) {
            $response->content = $this->getResponseContent();
            $statusCode = 200;
        } else {
            $statusCode = 204;
        }

        return [
            $statusCode => $response,
            500 => new Response([
                'description' => 'Unexpected error',
                'content' => [
                    'application/json' => new MediaType([
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'message' => [
                                    'type' => 'string',
                                    'title' => 'Message',
                                    'description' => 'Message of the exception',
                                ]
                            ],
                        ],
                    ])
                ]
            ])
        ];
    }

    public function getPhpDocReturn()
    {
        $params = $this->getRows()['return'];

        // as it should have only 1 return!
        return current($params);
    }

    public function getPhpDocReturnDescription()
    {
        $return = $this->getPhpDocReturn();

        return isset($return[2]) ? $return[2] : '';
    }

    public function getPhpDocReturnType()
    {
        $return = $this->getPhpDocReturn();

        $type = isset($return[1]) ? $return[1] : null;

        // fix not supported short form types
        if ($type == 'bool') {
            $type = 'boolean';
        }
        if ($type == 'int') {
            $type = 'integer';
        }

        return $type;
    }

    public function getIsPhpDocReturnObject($type)
    {
        if (empty($type) || $type === false) {
            return false;
        }

        return !in_array($type, [
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
            'void',
        ]);
    }

    public function modelContextToResponse($contextModel, $isArray = false)
    {
        $object = Yii::createObject($contextModel);

        $schema = false;

        if ($object instanceof ActiveRecord) {
            $schema = new ActiveRecordToSchema($object);
        } elseif ($object instanceof ActiveDataProvider) {
            return [
                'application/json' => new MediaType([
                    'schema' => [
                        'type' => 'array',
                    ],
                ])
            ];
        }
        
        if (!$schema) {
            return [];
        }

        if ($this->getActionObject() instanceof IndexAction) {
            $isArray = true;
        }

        if ($isArray) {
            return [
                'application/json' => new MediaType([
                    'schema' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'object',
                            'properties' => $schema->getProperties()
                        ]
                    ],
                ])
            ];
        }
        
        return [
            'application/json' => new MediaType([
                'schema' => [
                    'type' => 'object',
                    'properties' => $schema->getProperties(),
                ],
            ])
        ];
    }

    public function getNgRestApiModelClass()
    {
        if (ObjectHelper::isInstanceOf($this->getActionObject(), [Api::class, Action::class], false)) {
            return $this->getActionObject()->modelClass;
        }

        return false;
    }

    public function typeToObjectAnnotation($type)
    {
        $arrayResponse = false;
        if (StringHelper::endsWith($type, '[]')) {
            $arrayResponse = true;
        }

        return [
            'arrayResponse' => $arrayResponse,
            'object' => str_replace('[]', '', $type),
        ];
    }

    public function getResponseContent()
    {
        $type = $this->getPhpDocReturnType();

        // if void is requested it will be returned.
        if ($type == 'void') {
            return [];
        }

        // handle php object type
        if ($this->getIsPhpDocReturnObject($type)) {

            $format = $this->typeToObjectAnnotation($type);
            if (class_exists($format['object'])) {
                return $this->modelContextToResponse($format['object'], $format['arrayResponse']);
            }
            // if $type is a class which exists, use this instead.
            return [
                'application/json' => new MediaType([
                    'schema' => [
                        'type' => $this->getFirstSchemaType($type),
                    ],
                ])
            ];
        } 

        // handle type array
        if ($type == 'array') {
            return [
                'application/json' => new MediaType([
                    'schema' => [
                        'type' => 'array',
                        'items' => [],
                        // @todo option for force empty array?
                    ],
                ])
            ];
        }

        $modelClass = $this->getNgRestApiModelClass();

        if ($modelClass) {
            return $this->modelContextToResponse($modelClass);
        }

        if (empty($type)) {
            return [];
        }

        // handle scalar return types
        return [
            'application/json' => new MediaType([
                'schema' => [
                    'type' => $this->getFirstSchemaType($type),
                ],
            ])
        ];
    }

    public function generateShortSummary($reflection)
    {
        return current($this->getRows()['texts']);
    }

    public function generateLongDescription($reflection)
    {
        $content = [];
        foreach ($this->getRows()['texts'] as $key => $row) {
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