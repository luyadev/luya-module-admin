<?php

namespace luya\admin\openapi;

use cebe\openapi\spec\Operation;
use cebe\openapi\spec\Parameter;
use cebe\openapi\spec\PathItem;
use cebe\openapi\spec\Schema;
use luya\helpers\Inflector;
use Yii;
use yii\web\UrlRule;

class RouteParser
{
    protected $rulePattern;
    protected $route;
    protected $rules;
    protected $controllerMap;

    public function __construct($rulePattern, $route, array $rules, array $controllerMap)
    {
        $this->rulePattern = $rulePattern;
        $this->route = $route;
        $this->rules = $rules;
        $this->controllerMap = $controllerMap;    
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
            'summary' => $this->route . ' Short Summary' . get_class($this->getController()),
            'description' => $this->route . 'Long Long Long Long Description',
        ];

        foreach ($this->getOperations() as $verb => $operation) {
            $config[strtolower($verb)] = $operation;
        }

        return new PathItem($config);
    }

    public function getOperations()
    {
        $operations = [];
        /** @var UrlRule $urlRule */
        foreach ($this->rules as $urlRule) {
            $verbName = current($urlRule->verb);
            
            /*     parameters:
      - schema:
          type: string
        name: id
        in: path
        required: true
        */
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

            $operations[$verbName] = new Operation([
                'tags' => ['admin'],
                'summary' => $verbName . ' Summary Operation',
                'description' => $verbName . ' Summary Description',
                'operationId' => Inflector::slug($verbName . '-' . $this->getPath()),
                'parameters' => $params,
            ]);
        }

        return $operations;
        /*return new Operation([
            'tags' => ['foo', 'bar'],
            'summary' => $verbName . ' Summary Operation',
            'description' => $verbName . ' Summary Description',
            'operationId' => Inflector::slug($verbName . '-' . $this->getPath()),
        ]);*/
        return [];
    }
}