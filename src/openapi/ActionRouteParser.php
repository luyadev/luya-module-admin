<?php

namespace luya\admin\openapi;

use cebe\openapi\spec\Operation;
use cebe\openapi\spec\PathItem;
use cebe\openapi\spec\Responses;
use luya\admin\openapi\phpdoc\DocReaderAction;
use luya\admin\openapi\phpdoc\DocReaderController;
use luya\helpers\Inflector;
use ReflectionClass;
use yii\base\Controller;
use yii\base\InlineAction;

class ActionRouteParser implements RouteParserInterface
{
    protected $controllerDoc;
    protected $absoluteRoute;
    protected $route;
    protected $actionObject;
    protected $controller;
    protected $actionName;

    public function __construct(Controller $controller, $actionName, $absoluteRoute, $route)
    {
        $this->controller = $controller;
        $this->controllerDoc = new DocReaderController($controller);
        $this->absoluteRoute = $absoluteRoute;
        $this->route = $route;
        $this->actionName = $actionName;

        $this->actionObject = $controller->createAction($actionName);

        if ($this->actionObject instanceof InlineAction) {
            // read data from: actionMethodName()
            $reflector = new ReflectionClass(get_class($controller));
            $this->reflection = $reflector->getMethod('action'.Inflector::id2camel($actionName));
        } elseif ($this->actionObject) {
            $this->reflection = new ReflectionClass(get_class($this->actionObject));
        }
    }

    public function getPathItem(): PathItem
    {
        $actionDoc = new DocReaderAction($this->controller, $this->actionName);

        $config = [
            'summary' => $this->controllerDoc->getSummary(),
            'description' => $this->controllerDoc->getDescription(),
            'get' => new Operation([
                'tags' => [$this->route],
                'summary' => $actionDoc->getSummary(),
                'description' => $actionDoc->getDescription(),
                'operationId' => Inflector::slug('get' . '-' . $this->getPath()),
                'responses' => new Responses($actionDoc->getResponses())
            ])
        ];

        return new PathItem($config);
    }

    public function getPath(): string
    {
        return '/'.$this->absoluteRoute;
    }
}