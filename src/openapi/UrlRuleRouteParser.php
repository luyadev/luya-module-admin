<?php

namespace luya\admin\openapi;

use cebe\openapi\spec\Operation;
use cebe\openapi\spec\Parameter;
use cebe\openapi\spec\PathItem;
use cebe\openapi\spec\Responses;
use cebe\openapi\spec\Schema;
use luya\admin\openapi\phpdoc\DocReaderAction;
use luya\admin\openapi\phpdoc\DocReaderController;
use luya\helpers\Inflector;
use Yii;
use yii\web\UrlRule;

class UrlRuleRouteParser extends BasePathParser
{
    protected $patternRoute;
    protected $controllerMapRoute;
    protected $rules;
    protected $controller;
    protected $controllerDoc;

    public function __construct($patternRoute, $controllerMapRoute, array $rules)
    {
        $this->patternRoute = $patternRoute;
        $this->controllerMapRoute = $controllerMapRoute;
        $this->rules = $rules;
        $this->controller = Yii::$app->createController($controllerMapRoute)[0];
        $this->controllerDoc = new DocReaderController($this->controller);
    }

    public function getPath() : string
    {
        return '/'.str_replace(['<', ':\d[\d,]*>'], ['{', '}'], $this->patternRoute);
    }

    public function getPathItem(): PathItem
    {
        $config = [
            'summary' => $this->controllerDoc->getSummary(),
            'description' => $this->controllerDoc->getDescription(),
        ];

        foreach ($this->getOperations() as $verb => $operation) {
            $config[strtolower($verb)] = $operation;
        }

        return new PathItem($config);
    }

    public function isValid(): bool
    {
        return !empty($this->getOperations());
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

    public function routes(): array
    {
        return $this->_coveredRoutes;
    }

    protected function getOperation(UrlRule $urlRule, $verbName)
    {
        if (empty($urlRule->verb)) {
            return false;
        }

        $actionDoc = new DocReaderAction($this->controller, $this->getActionNameFromRoute($urlRule->route));

        if (!$actionDoc->getActionObject()) {
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

        foreach ($actionDoc->getParameters() as $param) {
            if (!in_array($param->name, $registeredParams)) {
                $params[] = $param;
            }
        }

        return new Operation([
            'tags' => [$this->routeToTag($this->controllerMapRoute)],
            'summary' => $actionDoc->getSummary(),
            'description' => $actionDoc->getDescription(),
            'operationId' => Inflector::slug($verbName . '-' . $this->getPath()),
            'parameters' => $params,
            'responses' => new Responses($actionDoc->getResponses())
        ]);
    }
}