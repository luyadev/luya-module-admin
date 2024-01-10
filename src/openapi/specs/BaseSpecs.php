<?php

namespace luya\admin\openapi\specs;

use cebe\openapi\spec\Example;
use cebe\openapi\spec\MediaType;
use cebe\openapi\spec\Parameter;
use cebe\openapi\spec\Response;
use cebe\openapi\spec\Schema;
use luya\admin\ngrest\base\Api;
use luya\admin\openapi\events\PathParametersEvent;
use luya\admin\openapi\Generator;
use luya\admin\openapi\phpdoc\PhpDocParser;
use luya\admin\openapi\phpdoc\PhpDocType;
use luya\helpers\ObjectHelper;
use ReflectionClass;
use ReflectionMethod;
use Yii;
use yii\base\Action as BaseAction;
use yii\base\Controller;
use yii\base\Event;
use yii\base\Model;
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
     * Get the context verbname:
     *
     * + get
     * + post
     * + delete
     * + put
     * + option§
     *
     * @return string
     */
    abstract public function getVerbName();

    /**
     * @return BaseAction
     */
    abstract public function getActionObject();

    /**
     * @return Controller
     */
    abstract public function getControllerObject();

    private $_phpDocParser;

    /**
     * @return PhpDocParser
     */
    public function getPhpDocParser()
    {
        if ($this->_phpDocParser === null) {
            $this->_phpDocParser = new PhpDocParser($this->getReflection());
        }

        return $this->_phpDocParser;
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

        if (ObjectHelper::isInstanceOf($this->getActionObject(), [IndexAction::class], false)) {
            // fields
            $params['fields'] = new Parameter([
                'name' => 'fields',
                'in' => 'query',
                'required' => false,
                'description' => 'Provide a comma seperated list of fields which should be returned.',
                'example' => 'id,email,firstname,lastname',
                'schema' => new Schema(['type' => 'string']),
            ]);

            $activeRecordClassName = $this->extractModelClassFromObject($this->getActionObject());
            $activeRecord = $this->createObjectFromClassName($activeRecordClassName);

            if ($activeRecord && method_exists($activeRecord, 'extraFields')) {
                $expandExample = implode(",", $activeRecord->extraFields());
            } else {
                $expandExample = null;
            }

            // expand
            $params['expand'] = new Parameter([
                'name' => 'expand',
                'in' => 'query',
                'required' => false,
                'description' => 'A comma seperated list of extra attributes (for example relations) which should be expanded.',
                'example' => $expandExample,
                'schema' => new Schema(['type' => 'string']),
            ]);

            // page
            $params['page'] = new Parameter([
                'name' => 'page',
                'in' => 'query',
                'required' => false,
                'description' => 'The page which should be resolved, page always starts at 1.',
                'example' => '1',
                'schema' => new Schema(['type' => 'integer']),
            ]);

            // per-page
            $params['per-page'] = new Parameter([
                'name' => 'per-page',
                'in' => 'query',
                'required' => false,
                'description' => 'The amount of rows to return by a page. By default its 25 rows and usually can not exceed 100 rows.',
                'example' => '100',
                'schema' => new Schema(['type' => 'integer']),
            ]);
        }

        if (property_exists($this->getControllerObject(), 'filterSearchModelClass')) {
            $dataFilterModelClass = $this->getControllerObject()->filterSearchModelClass;
            if (!empty($dataFilterModelClass)) {
                // filter
                $params['filter'] = new Parameter([
                    'name' => 'filter',
                    'in' => 'query',
                    'required' => false,
                    'description' => 'It allows validating and building a filter condition passed via request. See https://luya.io/guide/ngrest/api.html#filtering',
                    'example' => 'filter[from][gt]=123456&filter[to][lt]=123456',
                    /* Multiple example are not yet rendered by redoc: */
                    /* https://github.com/Redocly/redoc/issues/858 */
                    /*
                    'examples' => [
                    ],
                    */
                    'schema' => $this->createSchemaFromActiveRecordToSchemaObject($this->createActiveRecordSchemaObjectFromClassName($dataFilterModelClass), false)
                ]);
            }
        }

        // _language
        $params['_lang'] = new Parameter([
            'name' => '_lang',
            'in' => 'query',
            'required' => false,
            'description' => 'Defines the application language to format locale specific content or return the language specific content for multi language fields.',
            'example' => '`en`, `fr_FR` or `de-ch`',
            'schema' => new Schema(['type' => 'string']),
        ]);

        $event = new PathParametersEvent([
            'params' => $params,
            'controllerClass' => $this->getControllerObject()::class,
            'actionClass' => $this->getActionObject()::class,
            'verbName' => $this->getVerbName(),
            'contextClass' => $this->getReflection()->getName(),
            'sender' => $this,
        ]);

        Event::trigger(Generator::class, Generator::EVENT_PATH_PARAMETERS, $event);

        return $event->params;
    }

    /**
     * {@inheritDoc}
     */
    public function getResponses(): array
    {
        $return = $this->getPhpDocParser()->getReturn();

        $response = new Response([]);
        $response->description = $return->getDescription();

        $responseContent = $this->getResponseContent();

        if (!empty($responseContent)) {
            $response->content = $responseContent;
            $statusCode = 200;
        } else {
            $statusCode = 204;
        }

        $responseCodes = [
            $statusCode => $response,
            401 => new Response(['description' => 'Authentication failed.']),
            404 => new Response(['description' => 'The requested resource does not exist.']),
            405 => new Response(['description' => 'Method not allowed.']),
            500 => new Response(['description' => 'Internal server error.']),
        ];

        if ($this->getVerbName() == 'post' || $this->getVerbName() == 'put') {
            $responseCodes[422] = $this->getValidationResponseContent();
        }

        return $responseCodes;
    }

    /**
     * Generate the response content
     *
     * @return array
     */
    protected function getResponseContent()
    {
        $modelClass = $this->extractModelClassFromObject($this->getActionObject());

        if ($modelClass) {
            // the index action should return an array of objects
            $isArray = ObjectHelper::isInstanceOf($this->getActionObject(), [IndexAction::class], false);
            return $this->generateResponseArrayFromModel($modelClass, $isArray);
        }

        /** @var PhpDocType $type */
        $type = $this->getPhpDocParser()->getReturn()->getType();

        if (!$type) {
            return [];
        }

        // handle php object type
        if ($type->getIsClass()) {
            return $this->generateResponseArrayFromModel($type->getClassName(), $type->getIsArray());
        }

        // handle type array
        if ($type->getIsArray()) {
            return [
                'application/json' => new MediaType([
                    'schema' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'string'
                        ],
                    ],
                ])
            ];
        }

        if ($type->getIsScalar()) {
            return [
                'application/json' => new MediaType([
                    'schema' => [
                        'type' => $type->getNoramlizeName(),
                    ],
                ])
            ];
        }

        if ($type->getIsObject()) {
            return [
                'application/json' => new MediaType([
                    'schema' => [
                        'type' => 'object',
                    ],
                ])
            ];
        }

        return [];
    }

    /**
     * Get validation response for post requests
     *
     * @return Response
     */
    protected function getValidationResponseContent()
    {
        return new Response([
            'description' => 'Data validation failed. Check the response body for detailed error messages.',
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
                                    'example' => 'Unable to find the given user, email or password is wrong.'
                                ]
                            ]
                        ]

                    ],
                ])
            ]
        ]);
    }

    public static $contexts = [];

    /**
     * Generate an Array Response from ActiveRecord/Model class.
     *
     * @param string $contextModel
     * @param boolean $isArray
     * @return array|boolean
     */
    protected function generateResponseArrayFromModel($modelClassName, $isArray = false)
    {
        $key = implode("", [$modelClassName, (int) $isArray]);

        if (array_key_exists($key, self::$contexts)) {
            return self::$contexts[$key];
        }

        $response = $this->internalGenerateResponseArrayFromModel($modelClassName, $isArray);

        self::$contexts[$key] = $response;

        return $response;
    }

    /**
     * Internal generate the response for a given model class name
     *
     * @param string $modelClassName
     * @param boolean $isArray
     * @return array|boolean
     */
    private function internalGenerateResponseArrayFromModel($modelClassName, $isArray = false)
    {
        $object = $this->createObjectFromClassName($modelClassName);

        $schema = false;

        if ($object instanceof Model) {
            // if its an active record model (which inhertis from model), additionaly check for whether the table exists or not
            if ($object instanceof ActiveRecord) {
                if (Yii::$app->db->getTableSchema($object::tableName(), true)) {
                    $schema = new ActiveRecordToSchema($this, $object);
                }
            } else {
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
                'schema' => $this->createSchemaFromActiveRecordToSchemaObject($schema, $isArray),
            ])
        ];
    }

    /**
     * Extract the `modelClass` property value from any object
     *
     * @param object $actionObject
     * @return string|boolean
     */
    protected function extractModelClassFromObject($actionObject)
    {
        if (is_object($actionObject) && ObjectHelper::isInstanceOf($actionObject, [Api::class, Action::class], false)) {
            return $this->getActionObject()->modelClass;
        }

        return false;
    }

    /**
     * Create the ActiveRecordToSchema object from an ActiveRecord/Model Class Name.
     *
     * @param string|array $activeRecordClassName
     * @param string $senderActiveRecordClassName The class name which has created the new active record, this is used to find circular reference which end in infinite loops.
     * @return ActiveRecordToSchema|boolean
     */
    public function createActiveRecordSchemaObjectFromClassName($activeRecordClassName, $senderActiveRecordClassName = null)
    {
        try {
            Yii::warning("Create object createActiveRecordSchemaObjectFromClassName {$activeRecordClassName}", __METHOD__);
            $object = $this->createObjectFromClassName($activeRecordClassName);
            if ($object instanceof Model) {
                return new ActiveRecordToSchema($this, $object, $senderActiveRecordClassName);
            }
        } catch (\Exception $e) {
        }

        return false;
    }

    /**
     * Create the Object from a ClassName
     *
     * @param string $className
     * @return object|boolean
     */
    public function createObjectFromClassName($className)
    {
        try {
            Yii::info("Create object createObjectFromClassName {$className}", __METHOD__);
            if (!Yii::$container->hasSingleton($className)) {
                Yii::$container->setSingleton($className);
            }
            return Yii::createObject($className);
        } catch (\Exception $e) {
            Yii::warning("Error while creating the model class {$className}", __METHOD__);
        }

        return false;
    }

    /**
     * Create an ActiveRecord Schema Array Response from an Object (Controller or Action Object).
     *
     * @param object $actionObject An Action or Controller object.
     * @param boolean $asArray
     * @return array|false
     */
    public function createActiveRecordSchemaFromObject($actionObject, $asArray = false)
    {
        $class = $this->extractModelClassFromObject($actionObject);

        if ($class) {
            $object = $this->createActiveRecordSchemaObjectFromClassName($class);

            if ($object) {
                return $this->createSchemaFromActiveRecordToSchemaObject($object, $asArray);
            }
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
    public function createSchemaFromActiveRecordToSchemaObject(ActiveRecordToSchema $activeRecord, $isArray = false)
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
}
