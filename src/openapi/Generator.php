<?php

namespace luya\admin\openapi;

use luya\admin\ngrest\base\Api;
use luya\helpers\ArrayHelper;
use luya\helpers\ObjectHelper;
use luya\helpers\StringHelper;
use ReflectionClass;
use Yii;
use yii\base\Component;
use yii\rest\UrlRule;
use yii\web\UrlManager;
use yii\web\UrlRule as WebUrlRule;

/**
 * Generate Path Objects from UrlManager and ControllerMap.
 *
 * @author Basil Suter <git@nadar.io>
 * @since 3.2.0
 */
class Generator extends Component
{
    /**
     * @event luya\admin\openapi\events\PathParametersEvent This event will be triggered when the paramters are collected for a given path. The main purpose
     * is to add, remove or override existing params.
     * @since 3.5.0
     */
    public const EVENT_PATH_PARAMETERS = 'pathParameters';

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
    public function __construct(protected UrlManager $urlManager, protected array $controllerMap = [])
    {
    }

    private array $_assignedUrlRules = [];

    /**
     * Assign an existing Rule to the UrlRules
     *
     * By default only {{yii\rest\UrlRule}} will be take, as they contain informations about Verbs. In order to
     * assign a "casual" {{yii\web\UrlRule}} this method can be used. Assuming a defined url rule `['v1/users/register' => 'user/register']`
     * it can be assigned with `assignUrlRule('user/register', 'POST')`.
     *
     * @param string $route The route which is handled by the rule
     * @param string|array $verb The verb or a liste of verbs, use `POST`, `GET`, `PUT`, `DELETE`. If not defined, GET will be used.
     * @param string $endpointName An additional name for the endpoint, this name will be taken for groupping the rule. If not defined the rule pattern will be taken
     * @since 3.3.0
     */
    public function assignUrlRule($route, $verb = 'GET', $endpointName = null)
    {
        $this->_assignedUrlRules[$route] = ['route' => $route, 'verb' => (array) $verb, 'endpointName' => $endpointName];
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
            } elseif ($rule instanceof WebUrlRule && array_key_exists($rule->route, $this->_assignedUrlRules)) {
                $assignedRule = $this->_assignedUrlRules[$rule->route];
                $rule->verb = $assignedRule['verb'];
                $rules[$rule->route][] = ['rules' => [
                    $rule->name => [$rule]
                ], 'endpointName' => empty($assignedRule['endpointName']) ? $rule->name : $assignedRule['endpointName']];
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

    private array $_paths = [];

    private array $_routes = [];

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

    public static $_operationIds = [];

    /**
     * Generate an unique operation Id
     *
     * If a given operationId is already used a number will be added to the end and increased whenever the same operation appears.
     *
     * @param string $operationId
     * @return string
     * @since 3.5.2
     */
    public static function generateUniqueOperationId($operationId)
    {
        if (in_array($operationId, self::$_operationIds)) {
            // is numeric last char, means we can count up
            $lastChar = substr($operationId, -1);

            if (is_numeric($lastChar)) {
                $lastChar++;

                return self::generateUniqueOperationId(substr($operationId, 0, -1) . $lastChar);
            } else {
                return self::generateUniqueOperationId($operationId . "1");
            }
        }

        self::$_operationIds[] = $operationId;
        return $operationId;
    }
}
