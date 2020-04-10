<?php

namespace luya\admin\openapi;

use cebe\openapi\spec\Operation;
use cebe\openapi\spec\PathItem;
use cebe\openapi\spec\Responses;
use luya\admin\openapi\specs\ControllerActionSpecs;
use luya\admin\openapi\specs\ControllerSpecs;
use luya\helpers\Inflector;
use yii\base\Controller;

/**
 * Generate a path for a absolute route to an action.
 * 
 * @author Basil Suter <git@nadar.io>
 * @since 3.2.0
 */
class ActionRouteParser extends BasePathParser
{
    /**
     * @var ControllerActionSpecs
     */
    protected $actionSpecs;
    
    /**
     * @var ControllerSpecs
     */
    protected $controllerSpecs;
    protected $absoluteRoute;
    protected $controllerMapRoute;

    public function __construct(Controller $controller, $actionName, $absoluteRoute, $controllerMapRoute)
    {
        $this->controllerSpecs = new ControllerSpecs($controller);
        $this->actionSpecs = new ControllerActionSpecs($controller, $actionName);
        $this->absoluteRoute = $absoluteRoute;
        $this->controllerMapRoute = $controllerMapRoute;
    }

    /**
     * {@inheritDoc}
     */
    public function getPathItem(): PathItem
    {
        return new PathItem([
            'summary' => $this->controllerSpecs->getSummary(),
            'description' => $this->controllerSpecs->getDescription(),
            'get' => new Operation([
                'tags' => [$this->normalizeTag($this->controllerMapRoute)],
                'summary' => $this->actionSpecs->getSummary(),
                'description' => $this->actionSpecs->getDescription(),
                'operationId' => $this->generateOperationId('get'),
                'parameters' => $this->actionSpecs->getParameters(),
                'responses' => new Responses($this->actionSpecs->getResponses())
            ])
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getPath(): string
    {
        return '/'.$this->absoluteRoute;
    }

    /**
     * {@inheritDoc}
     */
    public function routes(): array
    {
        return [$this->controllerMapRoute];
    }

    /**
     * {@inheritDoc}
     */
    public function isValid(): bool
    {
        return true;
    }
}