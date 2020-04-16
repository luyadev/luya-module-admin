<?php

namespace luya\admin\openapi;

use cebe\openapi\spec\MediaType;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\Parameter;
use cebe\openapi\spec\PathItem;
use cebe\openapi\spec\RequestBody;
use cebe\openapi\spec\Responses;
use cebe\openapi\spec\Schema;
use luya\admin\openapi\specs\ControllerActionSpecs;
use luya\admin\openapi\specs\ControllerSpecs;
use Yii;
use yii\rest\CreateAction;
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
    protected $patternRoute;
    protected $controllerMapRoute;
    protected $rules;
    protected $controller;
    protected $endpointName;

    /**
     * @var ControllerSpecs
     */
    protected $controllerSpecs;

    public function __construct($patternRoute, $controllerMapRoute, array $rules, $endpointName)
    {
        $this->patternRoute = $patternRoute;
        $this->controllerMapRoute = $controllerMapRoute;
        $this->rules = $rules;
        $this->controller = Yii::$app->createController($controllerMapRoute)[0];

        if ($this->controller) {
            $this->controllerSpecs = new ControllerSpecs($this->controller);
        }
        $this->endpointName = $endpointName;
    }

    /**
     * {@inheritDoc}
     */
    public function getPath() : string
    {
        return '/'.str_replace(['<', ':\d[\d,]*>'], ['{', '}'], $this->patternRoute);
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

    private $_coveredRoutes = [];

    private $_operations;

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

    protected function getOperation(UrlRule $urlRule, $verbName)
    {
        if (empty($urlRule->verb)) {
            return false;
        }

        $actionSpecs = new ControllerActionSpecs($this->controller, $this->getActionNameFromRoute($urlRule->route));

        $actionObject = $actionSpecs->getActionObject();
        if (!$actionObject) {
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
            if (!in_array($param->name, $registeredParams)) {
                $params[] = $param;
            }
        }

        $requestBody = false;
        if ($actionObject instanceof UpdateAction || $actionObject instanceof CreateAction) {
            $requestBody = new RequestBody([
                'required' => true,
                'content' => [
                    'application/json' => new MediaType([
                        'schema' => $actionSpecs->createSchemaFromClass($actionObject, false),
                    ])
                ]
            ]);
        }

        return new Operation(array_filter([
            'tags' => [$this->normalizeTag($this->controllerMapRoute)],
            'summary' => $actionSpecs->getSummary(),
            'description' => $actionSpecs->getDescription(),
            'operationId' => $this->generateOperationId($verbName),
            'parameters' => $params,
            'responses' => new Responses($actionSpecs->getResponses()),
            'requestBody' => $requestBody,
        ]));
    }
}