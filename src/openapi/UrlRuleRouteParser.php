<?php

namespace luya\admin\openapi;

use cebe\openapi\spec\MediaType;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\Parameter;
use cebe\openapi\spec\PathItem;
use cebe\openapi\spec\RequestBody;
use cebe\openapi\spec\Responses;
use cebe\openapi\spec\Schema;
use luya\admin\openapi\phpdoc\PhpDocUses;
use luya\admin\openapi\specs\ControllerActionSpecs;
use luya\admin\openapi\specs\ControllerSpecs;
use luya\helpers\ObjectHelper;
use Yii;
use yii\base\Controller;
use yii\rest\CreateAction;
use yii\rest\OptionsAction;
use yii\rest\UpdateAction;
use yii\web\UrlRule;

/**
 * Generate a Path for a $rules array containg UrlRule objects.
 *
 * @author Basil Suter <git@nadar.io>
 * @since 3.2.0
 */
class UrlRuleRouteParser extends BasePathParser
{
    /**
     * @var Controller The created controller object from {{$controllerMapRoute}}.
     */
    protected $controller;

    /**
     * @var ControllerSpecs
     */
    protected $controllerSpecs;

    /**
     * Generate new UrlRule Route Parser
     *
     * @param string $patternRoute The pattern which will be taken to generate the path for all routes, f.e. `my-users`.
     * @param string $controllerMapRoute The controller map which will be taken to generate a new controller, f.e. `user/index`.
     * @param array $rules An array with {{yii\web\UrlRule}} objects f.e `[new UrlRule(['pattern' => 'my-users', 'route' => 'user/index', 'verb' => 'GET'])]`
     * @param string $endpointName The endpoint name is used to categorize all operations together into this tag f.e. `v1/my-users`.
     */
    public function __construct(protected $patternRoute, protected $controllerMapRoute, protected array $rules, protected $endpointName)
    {
        $createController = Yii::$app->createController($controllerMapRoute);

        if ($createController) {
            $this->controller = $createController[0];
        }

        if ($this->controller) {
            $this->controllerSpecs = new ControllerSpecs($this->controller);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getPath(): string
    {
        $route = $this->patternRoute;
        preg_match_all('/\<(\w+)(.*?)\>/', $route, $matches);

        if (isset($matches[2])) {
            foreach ($matches[2] as $regex) {
                $route = str_replace($regex, '', $route);
            }
        }

        return '/'.ltrim(str_replace(['<','>'], ['{', '}'], $route), '/');
    }

    /**
     * {@inheritDoc}
     */
    public function getPathItem(): PathItem
    {
        $config = [
            'summary' => $this->controllerSpecs->getSummary(),
            'description' => $this->controllerSpecs->getDescription(),
        ];

        foreach ($this->getOperations() as $verb => $operation) {
            $config[strtolower($verb)] = $operation;
        }

        return new PathItem($config);
    }

    /**
     * {@inheritDoc}
     */
    public function isValid(): bool
    {
        return $this->controller && !empty($this->getOperations()) ? true : false;
    }

    /**
     * {@inheritDoc}
     */
    public function routes(): array
    {
        return $this->_coveredRoutes;
    }

    public function getActionNameFromRoute($route)
    {
        $parts = explode("/", $route);
        return end($parts);
    }

    private array $_coveredRoutes = [];

    private $_operations;

    /**
     * Return all operations
     *
     * @return array
     */
    public function getOperations()
    {
        if ($this->_operations !== null) {
            return $this->_operations;
        }

        $operations = [];

        /** @var UrlRule $urlRule */
        foreach ($this->rules as $urlRule) {
            $verbName = current($urlRule->verb);
            $operation = $this->getOperation($urlRule, $verbName);

            if ($operation) {
                $operations[$verbName] = $operation;
                $this->_coveredRoutes[] = $urlRule->route;
            }
        }

        $this->_operations = $operations;

        return $operations;
    }

    /**
     * Get the Operation
     *
     * @param UrlRule $urlRule
     * @param string $verbName
     * @return Operation|boolean Either the operation or false is returned.
     */
    protected function getOperation(UrlRule $urlRule, $verbName)
    {
        if (empty($urlRule->verb)) {
            return false;
        }

        Yii::debug("Get Operation '{$urlRule->route}' with pattern '{$urlRule->pattern}'", __METHOD__);

        $actionSpecs = new ControllerActionSpecs($this->controller, $this->getActionNameFromRoute($urlRule->route), $verbName);

        $actionObject = $actionSpecs->getActionObject();

        if (!$actionObject || ObjectHelper::isInstanceOf($actionObject, OptionsAction::class, false)) {
            return false;
        }

        $params = [];
        $registeredParams = [];
        preg_match_all('/{+(.*?)}/', $this->getPath(), $matches);

        if (isset($matches[1])) {
            foreach ($matches[1] as $param) {
                $registeredParams[] = $param;
                $params[] = new Parameter([
                    'name' => $param,
                    'in' => 'path',
                    'required' => true,
                    'schema' => new Schema(['type' => 'string'])
                ]);
            }
        }

        foreach ($actionSpecs->getParameters() as $param) {
            if ($param instanceof Parameter && !in_array($param->name, $registeredParams)) {
                $params[] = $param;
            }
        }

        $requestBody = false;
        if ($actionObject instanceof UpdateAction || $actionObject instanceof CreateAction) {
            $schema = $actionSpecs->createActiveRecordSchemaFromObject($actionObject, false);

            if (!$schema) {
                return false;
            }

            $requestBody = new RequestBody([
                'required' => true,
                'content' => [
                    'application/json' => new MediaType([
                        'schema' => $schema,
                    ])
                ]
            ]);
        } elseif (strtoupper($verbName) == 'POST') {
            // if its a post request endpoint and @uses is defined use this
            // information as request body.
            $useProperties = [];

            /** @var PhpDocUses $use */
            foreach ($actionSpecs->getPhpDocParser()->getUses() as $use) {
                if ($use->getType()->getIsClass()) {
                    $schema = $actionSpecs->createActiveRecordSchemaObjectFromClassName($use->getType()->getClassName());
                    if ($schema) {
                        $requestBody = new RequestBody([
                            'content' => [
                                'application/json' => new MediaType([
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => $schema->getProperties()
                                    ]
                                ])
                            ]
                        ]);
                    }
                } else {
                    $useProperties[$use->getDescription()] = new Schema([
                        'type' => $use->getType()->getNoramlizeName(),
                        'title' => $use->getDescription(),
                    ]);
                }
            }

            if (!empty($useProperties)) {
                $requestBody = new RequestBody([
                    'content' => [
                        'application/json' => new MediaType([
                            'schema' => [
                                'type' => 'object',
                                'properties' => $useProperties,
                            ]
                        ])
                    ]
                ]);
            }
        }

        // check if an @method phpdoc is available for the current controller:
        $phpDocMethod = $this->controllerSpecs->getPhpDocParser()->getMethod($actionSpecs->getActionName());

        if ($phpDocMethod) {
            $summary = $phpDocMethod->getNormalizedMethodName();
            $description = $phpDocMethod->getDescription();
        } else {
            $summary = $actionSpecs->getSummary();
            $description = $actionSpecs->getDescription();
        }

        return new Operation(array_filter([
            'tags' => [$this->normalizeTag($this->endpointName)],
            'summary' => $summary,
            'description' => $description,
            'operationId' => $this->generateOperationId($verbName),
            'parameters' => $params,
            'responses' => new Responses($actionSpecs->getResponses()),
            'requestBody' => $requestBody,
        ]));
    }
}
