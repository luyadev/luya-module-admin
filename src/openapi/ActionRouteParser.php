<?php

namespace luya\admin\openapi;

use cebe\openapi\spec\Operation;
use cebe\openapi\spec\PathItem;
use cebe\openapi\spec\Responses;
use luya\admin\openapi\phpdoc\DocReaderAction;
use luya\admin\openapi\phpdoc\DocReaderController;
use luya\helpers\Inflector;
use yii\base\Controller;

class ActionRouteParser extends BasePathParser
{
    protected $actionDoc;
    protected $controllerDoc;
    protected $absoluteRoute;
    protected $controllerMapRoute;

    public function __construct(Controller $controller, $actionName, $absoluteRoute, $controllerMapRoute)
    {
        $this->controllerDoc = new DocReaderController($controller);
        $this->actionDoc = new DocReaderAction($controller, $actionName);
        $this->absoluteRoute = $absoluteRoute;
        $this->controllerMapRoute = $controllerMapRoute;
    }

    public function getPathItem(): PathItem
    {
        return new PathItem([
            'summary' => $this->controllerDoc->getSummary(),
            'description' => $this->controllerDoc->getDescription(),
            'get' => new Operation([
                'tags' => [$this->routeToTag($this->controllerMapRoute)],
                'summary' => $this->actionDoc->getSummary(),
                'description' => $this->actionDoc->getDescription(),
                'operationId' => Inflector::slug('get' . '-' . $this->getPath()),
                'parameters' => $this->actionDoc->getParameters(),
                'responses' => new Responses($this->actionDoc->getResponses())
            ])
        ]);
    }

    public function getPath(): string
    {
        return '/'.$this->absoluteRoute;
    }

    public function routes(): array
    {
        return [$this->controllerMapRoute];
    }

    public function isValid(): bool
    {
        return true;
    }
}