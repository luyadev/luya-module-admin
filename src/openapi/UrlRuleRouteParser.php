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

class UrlRuleRouteParser extends BaseParser implements RouteParserInterface
{
    protected $rulePattern;
    protected $route;
    protected $rules;

    protected $controllerDoc;

    public function __construct($rulePattern, $route, array $rules)
    {
        $this->rulePattern = $rulePattern;
        $this->route = $route;
        $this->rules = $rules;
        $this->controllerDoc = new DocReaderController($this->getController());
    }

    public function getPath() : string
    {
        // <id:\d[\d,]*>
        return '/'.str_replace(['<', ':\d[\d,]*>'], ['{', '}'], $this->rulePattern);
    }

    public function getController()
    {
        return Yii::$app->createController($this->route)[0];
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

    public function getIsValid()
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
            $params = [];
            preg_match_all('/{+(.*?)}/', $this->getPath(), $matches);


            if (isset($matches[1])) {
                foreach ($matches[1] as $param) {
                    $params[] = new Parameter([
                        'name' => $param,
                        'in' => 'path',
                        'required' => true,
                        'schema' => new Schema(['type' => 'string'])
                    ]);
                }
            }

            if (empty($urlRule->verb)) {
                continue;
            }

            $actionDoc = new DocReaderAction($this->getController(), $this->getActionNameFromRoute($urlRule->route));

            if (!$actionDoc->getActionObject()) {
                // this action does not exists
                continue;
            }

            $params = array_merge($params, $actionDoc->getParameters());

            $this->_coveredRoutes[] = $urlRule->route;

            $operations[$verbName] = new Operation([
                'tags' => [$this->routeToTag($this->route)],
                'summary' => $actionDoc->getSummary(),
                'description' => $actionDoc->getDescription(),
                'operationId' => Inflector::slug($verbName . '-' . $this->getPath()),
                'parameters' => $params,
                'responses' => new Responses($actionDoc->getResponses())
            ]);

            unset($actionDoc);
        }

        $this->_operations = $operations;
        return $operations;
    }

    public function getAllIncludedRoutes()
    {
        return $this->_coveredRoutes;
    }
}