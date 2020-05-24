<?php

namespace luya\admin\openapi;

use Yii;
use ReflectionClass;
use luya\admin\ngrest\base\Api;
use luya\helpers\ArrayHelper;
use luya\helpers\ObjectHelper;
use luya\helpers\StringHelper;
use yii\base\BaseObject;
use yii\rest\UrlRule;
use yii\web\UrlManager;
use yii\web\UrlRule as WebUrlRule;

/**
 * Generate Path Objects from UrlManager and ControllerMap.
 *
 * @author Basil Suter <git@nadar.io>
 * @since 3.2.0
 */
class Generator extends BaseObject
{
    /**
     * @var UrlManager
     */
    protected $urlManager;

    /**
     * @var array
     */
    protected $controllerMap;

    /**
     * @var string Prefix all controllers with this key `admin/` or `v1/`.
     */
    public $controllerMapEndpointPrefix;

    /**
     * @var array A list of of paths which should be filtered out. See {{luya\helpers\StringHelper::filterMatch()}} for more condition syntax docs. Assuming to have
     * a path `/admin/api-admin-user` a filter path to filter out all apis with `api-admin` would be `'filterPaths' => ['/admin/api-admin*']`.
     * @see {{luya\helpers\StringHelper::filterMatch()}} for function details.
     */
    public $filterPaths = [];

    /**
     * Add actions (routes) from controller map which are not covered by an urlRule from above
     * ignore those ngrest api actions as they are not used in api context and only available for admin
     *
     * @var array
     */
    public $ignoredApiActions = [
        'active-window-callback',
        'active-window-render',
        'active-button',
        'unlock',
        'toggle-notification',
        'export',
        'permissions',
        'services',
        'options',
        'list',
        'relation-call',
    ];

    /**
     * Constructor.
     *
     * @param UrlManager $urlManager
     * @param array $controllerMap The controllerMap is an array including the resolve path and object conifugration for the rest controller:
     * ```php
     * $controllerMap = [
     *   // string annotation
     *   'my-api-endpoint' => MyTestRestController::class,
     *
     *   // with optional module declaration
     *   'the-endpoint-resolve-name' => [
     *       'class' => TheEndpointController::class, // should be an instance of yii\rest\Controller
     *       'module' => Yii::$app->getModule('foobar'), // the module which should be invoken for the controller',
     *   ]
     * ]
     * ```
     * + The key is the endpointName to resolve, lets say the url.
     * + Value is an array containing `class` and `module`
     */
    public function __construct(UrlManager $urlManager, array $controllerMap = [])
    {
        $this->urlManager = $urlManager;
        $this->controllerMap = $controllerMap;
    }

    /**
     * Get all Url Rules.
     *
     * @return array
     */
    public function getUrlRules()
    {
        $rules = [];
        // get all rules from the urlManager
        foreach ($this->urlManager->rules as $rule) {
            if ($rule instanceof UrlRule) {
                $controllerMap = $rule->controller;
                $reflection = new ReflectionClass($rule);
                $property = $reflection->getProperty('rules');
                $property->setAccessible(true);
                $array = $property->getValue($rule);

                // as rules can have multiple controllers defined
                // we have to find the absolute endpointName in the map
                // and associated the route with the controller name
                foreach ($controllerMap as $endpointName => $controller) {
                    // get the rule for the corresponing endpointName
                    if (isset($array[$endpointName])) {
                        $rules[$controller][] = ['rules' => ArrayHelper::index($array[$endpointName], null, 'name'), 'endpointName' => $endpointName];
                    }
                }

                unset($array, $reflection, $property);
            } elseif ($rule instanceof WebUrlRule) {
                // add as config? 
                // 'urlRule' => ['<RULE>', 'VERB', 'endpointName'];
                $rule->verb = ['GET'];
                $rules[$rule->route][] = ['rules' => [
                    $rule->name => [$rule]
                ], 'endpointName' => $rule->route];

            }

            unset($rule);
        }

        return $rules;
    }

    /**
     * Generate paths from url rules.
     */
    protected function getPathsFromUrlRules()
    {
        foreach ($this->getUrlRules() as $controllerName => $config) {
            foreach ($config as $item) {
                foreach ($item['rules'] as $patternRoute => $ruleConfig) {
                    Yii::debug("Create new UrlRuleRouteParser for '{$patternRoute}', '{$controllerName}' and '{$item['endpointName']}'", __METHOD__);
                    $this->addPath(new UrlRuleRouteParser($patternRoute, $controllerName, $ruleConfig, $item['endpointName']));
                }
            }
        }
    }

    /**
     * Generate paths from controller map.
     */
    protected function getPathsFromControllerMap()
    {
        foreach ($this->controllerMap as $key => $map) {
            if (is_array($map)) {
                $controller = Yii::createObject($map['class'], [$key, $map['module']]);
            } else {
                $controller = Yii::createObject($map, [$key, Yii::$app]);
            }
            
            $controllerMapRoute = $this->controllerMapEndpointPrefix.$key;
            foreach (ObjectHelper::getActions($controller) as $actionName) {
                if ($controller instanceof Api && in_array($actionName, $this->ignoredApiActions)) {
                    continue;
                }
                $absoluteRoute = $controllerMapRoute.'/'.$actionName;
                if (!in_array($absoluteRoute, $this->_routes)) {
                    Yii::debug("Create new ActionRouteParser for '{$actionName}' and '{$absoluteRoute}'", __METHOD__);
                    $this->addPath(new ActionRouteParser($controller, $actionName, $absoluteRoute, $controllerMapRoute));
                }
            }

            unset($controller);
            gc_collect_cycles();
        }
    }

    private $_paths = [];

    private $_routes = [];

    /**
     * Add a path to the paths array
     *
     * @param BasePathParser $pathParser
     */
    private function addPath(BasePathParser $pathParser)
    {
        if ($pathParser->isValid()) {

            if (!empty($this->filterPaths)) {
                if (StringHelper::filterMatch($pathParser->getPath(), $this->filterPaths)) {
                    return;
                }
            }
            $this->_paths[$pathParser->getPath()] = $pathParser->getPathItem();
            foreach ($pathParser->routes() as $route) {
                $this->_routes[] = $route;
            }
        }

        unset($pathParser);
        gc_collect_cycles();
    }

    /**
     * Get paths for given config.
     *
     * @return array
     */
    public function getPaths()
    {
        $this->getPathsFromUrlRules();
        $this->getPathsFromControllerMap();

        $paths = $this->_paths;
        
        ksort($paths);

        return $paths;
    }
}
