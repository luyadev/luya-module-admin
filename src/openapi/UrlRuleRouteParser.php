<?php

namespace luya\admin\openapi;

use cebe\openapi\spec\Operation;
use cebe\openapi\spec\Parameter;
use cebe\openapi\spec\PathItem;
use cebe\openapi\spec\Responses;
use cebe\openapi\spec\Schema;
use luya\admin\openapi\specs\ControllerActionSpecs;
use luya\admin\openapi\specs\ControllerSpecs;
use luya\helpers\Inflector;
use Yii;
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

    /**
     * @var ControllerSpecs
     */
    protected $controllerSpecs;

    public function __construct($patternRoute, $controllerMapRoute, array $rules)
    {
        $this->patternRoute = $patternRoute;
        $this->controllerMapRoute = $controllerMapRoute;
        $this->rules = $rules;
        $this->controller = Yii::$app->createController($controllerMapRoute)[0];
        $this->controllerSpecs = new ControllerSpecs($this->controller);
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
        return !empty($this->getOperations());
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

        if (!$actionSpecs->getActionObject()) {
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

        return new Operation([
            'tags' => [$this->normalizeTag($this->controllerMapRoute)],
            'summary' => $actionSpecs->getSummary(),
            'description' => $actionSpecs->getDescription(),
            'operationId' => Inflector::slug($verbName . '-' . $this->getPath()),
            'parameters' => $params,
            'responses' => new Responses($actionSpecs->getResponses())
        ]);
    }
}