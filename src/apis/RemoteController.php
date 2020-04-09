<?php

namespace luya\admin\apis;

use cebe\openapi\spec\Components;
use Yii;
use luya\Boot;
use luya\Exception;
use luya\admin\models\UserOnline;
use luya\rest\Controller;
use cebe\openapi\spec\Info;
use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\SecurityScheme;
use luya\admin\openapi\Generator;
use yii\web\ForbiddenHttpException;

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
     * Verify the remote token, if enabled.
     *
     * @param string $token
     * @throws ForbiddenHttpException
     */
    protected function verifyToken($token)
    {
        if (empty(Yii::$app->remoteToken) || sha1(Yii::$app->remoteToken) !== $token) {
            throw new ForbiddenHttpException('The provided remote token is wrong.');
        }
    }

    /**
     * Generate OpenApi Json File.
     * 
     * You can either enable {{luya\module\Admin::$publicOpenApi}} or provider the {{luya\web\Application::$remoteToken}} to get
     * an on-the-fly generated Json formated Open Api file.
     *
     * @param string $token The remote token to view the api.
     * @return array The OpenApi Json Data.
     * @since 3.2.0
     */
    public function actionOpenapi($token = null)
    {
        if ($token) {
            $this->verifyToken($token);
        } elseif (!$this->module->publicOpenApi) {
            throw new ForbiddenHttpException("Rendering openApi is disabled by the module.");
        }
        $generator = new Generator(Yii::$app->urlManager, $this->module->controllerMap);

        // generate the openapi file
        $definition = [
            'openapi' => '3.0.2',
            'info' => new Info([
                'title' => Yii::$app->siteTitle,
                'version' => '1.0.0',
            ]),
            'paths' => $generator->getPaths(),
            'components' => new Components([
                'securitySchemes' => [
                    'BearerAuth' => new SecurityScheme([
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'JWT'    # optional, arbitrary value for documentation purposes
                    ])
                ],
            ]),
            'security' => [
                'BearerAuth' => [],
            ]
        ];

        // write the json file
        $openapi = new OpenApi($definition);

        // returns an array with the openapi specs
        return $openapi->getSerializableData();
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
        $this->verifyToken($token);
        
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
