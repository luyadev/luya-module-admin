<?php

namespace luya\admin\apis;

use cebe\openapi\spec\Info;
use cebe\openapi\spec\OpenApi;
use luya\admin\components\UrlRule;
use Yii;
use luya\Boot;
use luya\Exception;
use luya\admin\models\UserOnline;
use luya\admin\openapi\ControllerParser;
use luya\admin\openapi\RouteParser;
use luya\helpers\ArrayHelper;
use luya\rest\Controller;
use ReflectionClass;
use yii\web\Response;

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
     * @return void
     */
    public function actionOpenapi()
    {
        Yii::$app->response->format = Response::FORMAT_RAW;
        $paths = [];

        $rules = [];

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

        foreach ($rules as $route => $items) {

            foreach ($items as $rulePattern => $ruleConfig) {

                $parser = new RouteParser($rulePattern, $route, $ruleConfig, $this->module->controllerMap);
                $paths[$parser->getPath()] = $parser->getPathItem();
                unset($parser);
            }
        }

        $definition = [
            'openapi' => '3.0.2',
            'info' => new Info([
                'title' => Yii::$app->siteTitle,
                'version' => '1.0.0',
            ]),
            'paths' => $paths,
        ];

        $openapi = new OpenApi($definition);
        return \cebe\openapi\Writer::writeToJson($openapi);
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
