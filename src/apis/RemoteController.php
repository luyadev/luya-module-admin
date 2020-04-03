<?php

namespace luya\admin\apis;

use ReflectionClass;
use Yii;
use luya\Boot;
use luya\Exception;
use luya\admin\models\UserOnline;
use luya\admin\openapi\ActionRouteParser;
use luya\admin\openapi\UrlRuleRouteParser;
use luya\admin\components\UrlRule;
use luya\helpers\ArrayHelper;
use luya\helpers\ObjectHelper;
use luya\rest\Controller;
use yii\web\Response;
use cebe\openapi\spec\Info;
use cebe\openapi\spec\OpenApi;
use cebe\openapi\Writer;
use luya\admin\ngrest\base\Api;

/**
 * Remove API, allows to collect system data with a valid $token.
 *
 * The remote api can only access with the oken but is not secured by a loggged in user.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class RemoteController extends Controller
{
    /**
     * Disabled the auth methods.
     *
     * @return boolean When false the authentication is disabled.
     */
    public function userAuthClass()
    {
        return false;
    }

    /**
     * https://www.php.net/manual/en/reflectionclass.getdoccomment.php
     * https://github.com/phpDocumentor/ReflectionDocBlock
     * https://github.com/PHP-DI/PhpDocReader
     *
     * @return string Openapi as json
     * @since 3.2.0
     */
    public function actionOpenapi()
    {
        $rules = [];
        // get all rules from the urlManager
        foreach (Yii::$app->urlManager->rules as $rule) {
            if ($rule instanceof UrlRule) {
                $reflection = new ReflectionClass($rule);
                $property = $reflection->getProperty('rules');
                $property->setAccessible(true);
                $array = $property->getValue($rule);
                foreach ($array as $rule => $config) {
                    $rules[$rule] = ArrayHelper::index($config, null, 'name');
                }
            }
        }

        $paths = [];
        $routesProcessed = [];
        // generate all paths from the urlManager rules
        foreach ($rules as $route => $items) {
            foreach ($items as $rulePattern => $ruleConfig) {
                $parser = new UrlRuleRouteParser($rulePattern, $route, $ruleConfig);
                if ($parser->getIsValid()) {
                    $paths[$parser->getPath()] = $parser->getPathItem();
                    $routesProcessed = array_merge($routesProcessed, $parser->getAllIncludedRoutes());
                }
                unset($parser);
            }
        }

        // add actions (routes) from controller map which are not covered by an urlRule from above
         // ignore those ngrest api actions as they are not used in api context and only available for admin
        $ignoreActions = [
            'active-window-callback',
            'active-window-render',
            'unlock',
            'toggle-notification',
            'export',
            'permissions',
            'services',
            'options',
        ];
        foreach ($this->module->controllerMap as $key => $map) {
            $controller = Yii::createObject($map['class'], [$key, $map['module']]);
            $route = 'admin/'.$key;
            foreach (ObjectHelper::getActions($controller) as $actionName) {
                if ($controller instanceof Api && in_array($actionName, $ignoreActions)) {
                    continue;
                }
                $absoluteRoute = $route.'/'.$actionName;
                if (!in_array($absoluteRoute, $routesProcessed)) {
                    $parser = new ActionRouteParser($controller, $actionName, $absoluteRoute, $route);
                    $paths[$parser->getPath()] = $parser->getPathItem();
                }
            }
        }

        ksort($paths);

        // generate the openapi file
        $definition = [
            'openapi' => '3.0.2',
            'info' => new Info([
                'title' => Yii::$app->siteTitle,
                'version' => '1.0.0',
            ]),
            'paths' => $paths,
        ];

        Yii::$app->response->format = Response::FORMAT_RAW;

        // write the json file
        $openapi = new OpenApi($definition);
        return Writer::writeToJson($openapi);
    }

    /**
     * Retrieve administration informations if the token is valid.
     *
     * @param string $token The sha1 encrypted access token.
     * @return array If invalid token.
     * @throws Exception
     */
    public function actionIndex($token)
    {
        if (empty(Yii::$app->remoteToken) || sha1(Yii::$app->remoteToken) !== $token) {
            throw new Exception('The provided remote token is wrong.');
        }
        
        UserOnline::clearList($this->module->userIdleTimeout);

        $packages = [];

        foreach (Yii::$app->getPackageInstaller()->getConfigs() as $name => $config) {
            $packages[$name] = [
                'package' => $config->package,
                'time' => Yii::$app->getPackageInstaller()->timestamp,
            ];
        }

        return [
            'yii_version' => Yii::getVersion(),
            'luya_version' => Boot::VERSION,
            'app_title' => Yii::$app->siteTitle,
            'app_debug' => (int) YII_DEBUG,
            'app_env' => YII_ENV,
            'app_transfer_exceptions' => (int) Yii::$app->errorHandler->transferException,
            'admin_online_count' => UserOnline::find()->count(),
            'app_elapsed_time' => Yii::getLogger()->getElapsedTime(),
            'packages' => $packages,
            'packages_update_timestamp' => Yii::$app->getPackageInstaller()->getTimestamp(),
        ];
    }
}
