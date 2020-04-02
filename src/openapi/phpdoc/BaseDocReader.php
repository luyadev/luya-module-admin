<?php

namespace luya\admin\openapi\phpdoc;

use cebe\openapi\spec\MediaType;
use cebe\openapi\spec\Response;
use cebe\openapi\spec\Schema;
use luya\admin\ngrest\base\Api;
use luya\helpers\ArrayHelper;
use luya\helpers\ObjectHelper;
use ReflectionClass;
use ReflectionMethod;
use Yii;
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
        $response200->description = $this->getPhpDocReturnDescription();

        if ($this->getResponseContent()) {
            $response200->content = $this->getResponseContent();
        }

        return [
            200 => $response200,
        ];
    }

    public function getPhpDocReturn()
    {
        $params = $this->getRows($this->getReflection())['params'];

        return ArrayHelper::searchColumn($params, 0, '@return');
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

    public function modelContextToResponse($contextModel)
    {
        $schema = new ActiveRecordToSchema(Yii::createObject($contextModel));

        if ($this->getActionObject() instanceof IndexAction) {
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


    public function getResponseContent()
    {
        $type = $this->getPhpDocReturnType();

        // if void is requested it will be returned.
        if ($type == 'void') {
            return [];
        }

        // handle php object type
        if ($this->getIsPhpDocReturnObject($type)) {
            // if $type is a class which exists, use this instead.
            return [
                'application/json' => new MediaType([
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => new Schema([
                                'type' => $type,
                                'description' => 'Php doc return type ' . $type,
                            ])
                        ]
                    ],
                ])
            ];
        } 

        // handle type array
        if ($type == 'array') {
            return [
                'application/json' => new MediaType([
                    'schema' => [
                        'type' => $type,
                        'items' => [],
                    ],
                ])
            ];
        }

        $modelClass = $this->getNgRestApiModelClass();

        if ($modelClass) {
            return $this->modelContextToResponse($modelClass);
        }

        // handle scalar return types
        return [
            'application/json' => new MediaType([
                'schema' => [
                    'type' => $type,
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