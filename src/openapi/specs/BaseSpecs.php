<?php

namespace luya\admin\openapi\specs;

use cebe\openapi\spec\MediaType;
use cebe\openapi\spec\Parameter;
use cebe\openapi\spec\Response;
use cebe\openapi\spec\Schema;
use luya\admin\ngrest\base\Api;
use luya\admin\openapi\phpdoc\PhpDocParser;
use luya\admin\openapi\phpdoc\PhpDocType;
use luya\helpers\ObjectHelper;
use ReflectionClass;
use ReflectionMethod;
use Yii;
use yii\base\Action as BaseAction;
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

    /**
     * @return BaseAction
     */
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
            foreach ($this->getReflection()->getParameters() as $arg) {
                $paramDoc = $this->getPhpDocParser()->getParam($arg->getName());

                $paramType = $paramDoc->getType()->getNoramlizeName();
                $params[] = new Parameter([
                    'name' => $arg->getName(),
                    'in' => 'query',
                    'required' => !$arg->isOptional(),
                    'description' => $paramDoc->getDescription(),
                    'schema' => new Schema([
                        'type' => in_array($paramType, ['integer', 'string']) ? $paramType : 'string', // only integer and string allowed
                    ])
                ]);
            }
        }

        // @TODO add query params:
        // fields=
        // expand=
        // page=
        // per-page
        // _language
        // _format
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
            201 => new Response(['description' => 'A resource was successfully created in response to a POST request. The Location header contains the URL pointing to the newly created resource.']),
            204 => new Response(['description' => 'The request was handled successfully and the response contains no body content (like a DELETE request).']),
            304 => new Response(['description' => 'The resource was not modified. You can use the cached version.']),
            400 => new Response(['description' => 'Bad request. This could be caused by various actions by the user, such as providing invalid JSON data in the request body, providing invalid action parameters, etc.']),
            401 => new Response(['description' => 'Authentication failed.']),
            403 => new Response(['description' => 'The authenticated user is not allowed to access the specified API endpoint.']),
            404 => new Response(['description' => 'The requested resource does not exist.']),
            405 => new Response(['description' => 'Method not allowed. Please check the Allow header for the allowed HTTP methods.']),
            422 => new Response([
                'description' => 'Data validation failed (in response to a POST request, for example). Please check the response body for detailed error messages.',
                'content' => [
                    'application/json' => new MediaType([
                        'schema' => [
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'field' => [
                                        'type' => 'string',
                                        'example' => 'email',
                                    ],
                                    'message' => [
                                        'type' => 'string',
                                        'example' => 'Unable to find the given email or password is wrong.'
                                    ]
                                ]
                            ]
                            
                        ],
                    ])
                ]
            ]),
            429 => new Response(['description' => 'Too many requests. The request was rejected due to rate limiting.']),
            500 => new Response(['description' => 'Internal server error. This could be caused by internal program errors.'])
        ];
    }

    protected function modelContextToResponse($contextModel, $isArray = false)
    {
        $object = Yii::createObject($contextModel);

        $schema = false;

        if ($object instanceof ActiveRecord) {
            // ensure the active record table exists
            if (Yii::$app->db->getTableSchema($object::tableName(), true)) {
                $schema = new ActiveRecordToSchema($this, $object);
            }
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

        return [
            'application/json' => new MediaType([
                'schema' => $this->activeRecordToSchema($schema, $isArray),
            ])
        ];
    }

    /**
     * create an ActiveRecordSchema from a className
     *
     * @param string|array $activeRecordClassName
     * @return ActiveRecordToSchema
     */
    public function createActiveRecordSchema($activeRecordClassName)
    {
        $object = Yii::createObject($activeRecordClassName);

        if ($object instanceof ActiveRecord) {
            return new ActiveRecordToSchema($this, $object);
        }

        return false;
    }
    
    /**
     * Generate OpenAPI schema structure from ActiveRecordToSchema Object
     *
     * @param ActiveRecordToSchema $activeRecord
     * @param boolean $isArray
     * @return array
     */
    public function activeRecordToSchema(ActiveRecordToSchema $activeRecord, $isArray = false)
    {
        if ($isArray) {
            return [
                'type' => 'array',
                'items' => [
                    'type' => 'object',
                    'properties' => $activeRecord->getProperties()
                ]
            ];
        }
        return [
            'type' => 'object',
            'properties' => $activeRecord->getProperties()
        ];
    }

    protected function getNgRestApiModelClass($actionObject)
    {
        if (ObjectHelper::isInstanceOf($actionObject, [Api::class, Action::class], false)) {
            return $this->getActionObject()->modelClass;
        }

        return false;
    }

    public function createSchemaFromClass($actionObject, $asArray = false)
    {
        $class = $this->getNgRestApiModelClass($actionObject);

        if ($class) {
            $object = $this->createActiveRecordSchema($class);

            if ($object) {
                return $this->activeRecordToSchema($object, $asArray);
            }
        }

        return false;
    }

    protected function getResponseContent()
    {
        $modelClass = $this->getNgRestApiModelClass($this->getActionObject());

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
            return $this->modelContextToResponse($type->getClassName(), $type->getIsArray());
        }

        // handle type array
        if ($type->getIsArray()) {
            return [
                'application/json' => new MediaType([
                    'schema' => [
                        'type' => 'array',
                        'items' => [],
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
