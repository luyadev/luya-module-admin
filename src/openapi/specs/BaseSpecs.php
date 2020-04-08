<?php

namespace luya\admin\openapi\specs;

use cebe\openapi\spec\MediaType;
use cebe\openapi\spec\Parameter;
use cebe\openapi\spec\Response;
use cebe\openapi\spec\Schema;
use luya\admin\ngrest\base\Api;
use luya\admin\openapi\phpdoc\PhpDocParser;
use luya\admin\openapi\phpdoc\PhpDocType;
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
 * Generate Specs Details.
 * 
 * + works with the class php doc block
 * + works with the method php doc block
 * 
 * @author Basil Suter <git@nadar.io>
 * @since 3.2.0
 */
abstract class BaseSpecs implements SpecInterface
{
    /**
     * @return ReflectionClass|ReflectionMethod
     */
    abstract public function getReflection();

    abstract public function getActionObject();

    abstract public function getControllerObject();

    /**
     * @return PhpDocParser
     */
    protected function getPhpDocParser()
    {
        return new PhpDocParser($this->getReflection());
    }

    /**
     * {@inheritDoc}
     */
    public function getSummary(): string
    {
        return $this->getPhpDocParser()->getShortSummary();
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription(): string
    {
        return $this->getPhpDocParser()->getLongDescription();
    }

    /**
     * {@inheritDoc}
     */
    public function getParameters(): array
    {
        $params = [];
        if ($this->getReflection() instanceof ReflectionMethod) {
            foreach ($this->getReflection()->getParameters() AS $arg) {

                $paramDoc = $this->getPhpDocParser()->getParam($arg->getName());

                $params[] = new Parameter([
                    'name' => $arg->getName(),
                    'in' => 'query',
                    'required' => !$arg->isOptional(),
                    'description' => $paramDoc->getDescription(),
                    'schema' => new Schema([
                        'type' => $paramDoc->getType(),
                    ])
                ]);
            }
        }

        return $params;
    }

    /**
     * {@inheritDoc}
     */
    public function getResponses(): array
    {
        $return = $this->getPhpDocParser()->getReturn();

        $response = new Response([]);
        $response->description = $return->getDescription();

        
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
                /*
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
                */
            ])
        ];
    }

    protected function modelContextToResponse($contextModel, $isArray = false)
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

    protected function getNgRestApiModelClass()
    {
        if (ObjectHelper::isInstanceOf($this->getActionObject(), [Api::class, Action::class], false)) {
            return $this->getActionObject()->modelClass;
        }

        return false;
    }

    protected function getResponseContent()
    {
        $modelClass = $this->getNgRestApiModelClass();

        if ($modelClass) {
            return $this->modelContextToResponse($modelClass);
        }

        /** @var PhpDocType $type */
        $type = $this->getPhpDocParser()->getReturn()->getType();

        if (!$type) {
            return [];
        }

        // handle php object type
        if ($type->getIsClass()) {
            return $this->modelContextToResponse($type->getNoramlizeName(), $type->getIsArray());
        } 

        // handle type array
        if ($type->getIsArray()) {
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

        if ($type->getIsScalar()) {
            return [
                'application/json' => new MediaType([
                    'schema' => [
                        'type' => $type->name,
                    ],
                ])
            ];
        }

        return [];
    }
}