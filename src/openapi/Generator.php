<?php

namespace luya\admin\openapi;

use luya\admin\ngrest\base\Api;
use luya\helpers\ArrayHelper;
use luya\helpers\ObjectHelper;
use ReflectionClass;
use Yii;
use yii\base\BaseObject;
use yii\rest\UrlRule;
use yii\web\UrlManager;

/**
 * Generate Path Objects from UrlManager and ControllerMap.
 * 
 * @author Basil Suter <git@nadar.io>
 * @since 3.2.0
 */
class Generator extends BaseObject
{
    protected $urlManager;
    protected $controllerMap;

    /**
     * Add actions (routes) from controller map which are not covered by an urlRule from above
     * ignore those ngrest api actions as they are not used in api context and only available for admin
     *
     * @var array
     */
    public $ignoredApiActions = [
        'active-window-callback',
        'active-window-render',
        'unlock',
        'toggle-notification',
        'export',
        'permissions',
        'services',
        'options',
    ];

    public function __construct(UrlManager $urlManager, array $controllerMap = [])
    {
        $this->urlManager = $urlManager;   
        $this->controllerMap = $controllerMap;
    }

    public function getUrlRules()
    {
        $rules = [];
        // get all rules from the urlManager
        foreach ($this->urlManager->rules as $rule) {
            if ($rule instanceof UrlRule) {
                $reflection = new ReflectionClass($rule);
                $property = $reflection->getProperty('rules');
                $property->setAccessible(true);
                $array = $property->getValue($rule);
                foreach ($array as $rule => $config) {
                    $rules[$rule] = ArrayHelper::index($config, null, 'name');
                }

                unset($array, $reflection, $property);
            }
        }

        return $rules;
    }

    protected function getPathsFromUrlRules()
    {
        foreach ($this->getUrlRules() as $controllerMapRoute => $items) {
            foreach ($items as $patternRoute => $ruleConfig) {
                $this->addPath(new UrlRuleRouteParser($patternRoute, $controllerMapRoute, $ruleConfig));
            }
        }
    }

    protected function getPathsFromControllerMap()
    {
        foreach ($this->controllerMap as $key => $map) {
            $controller = Yii::createObject($map['class'], [$key, $map['module']]);
            $controllerMapRoute = 'admin/'.$key;
            foreach (ObjectHelper::getActions($controller) as $actionName) {
                if ($controller instanceof Api && in_array($actionName, $this->ignoredApiActions)) {
                    continue;
                }
                $absoluteRoute = $controllerMapRoute.'/'.$actionName;
                if (!in_array($absoluteRoute, $this->_routes)) {
                    $this->addPath(new ActionRouteParser($controller, $actionName, $absoluteRoute, $controllerMapRoute));
                }
            }
        }
    }

    private $_paths = [];

    private $_routes = [];

    private function addPath(BasePathParser $pathParser)
    {
        if ($pathParser->isValid()) {
            $this->_paths[$pathParser->getPath()] = $pathParser->getPathItem();
            foreach ($pathParser->routes() as $route) {
                $this->_routes[] = $route;
            }
        }
    } 

    public function getPaths()
    {
        $this->getPathsFromUrlRules();
        $this->getPathsFromControllerMap();

        $paths = $this->_paths;
        
        ksort($paths);

        return $paths;
    }
}