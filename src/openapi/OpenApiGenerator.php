<?php

namespace luya\admin\openapi;

use Yii;
use yii\base\InvalidConfigException;
use cebe\openapi\spec\Info;
use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\SecurityScheme;
use cebe\openapi\spec\Server;
use cebe\openapi\spec\Components;
use luya\helpers\Url;

/**
 * Generate the OpenApi Instance.
 *
 * Usage example of how to create a custom OpenApi file generator.
 *
 * ```php
 * $generator = new Generator(Yii::$app->urlManager, [
 *     // additional not yii\web\RestRule Endpoints
 *     'user' => UserRestController::class,
 *     'groups' => GroupRestController::class,
 * ]);
 *
 * $openapi = new OpenApiGenerator($generator);
 *
 * // always return as json
 * return $this->asJson($openapi->create()->getSerializableData());
 * ```
 *
 * @author Basil Suter <git@nadar.io>
 * @since 3.2.0
 */
class OpenApiGenerator
{
    /**
     * @var Generator
     */
    public $generator;

    /**
     * Constructor with Generator Object
     *
     * @param Generator $generator
     */
    public function __construct(Generator $generator)
    {
        if (!class_exists(OpenApi::class)) {
            throw new InvalidConfigException("The composer package `cebe/php-openapi` must be installed to generate the OpenAPI file.");
        }
        
        $this->generator = $generator;
    }

    /**
     * Get Info Object
     *
     * @return Info
     */
    public function getInfo()
    {
        return new Info([
            'title' => Yii::$app->siteTitle,
            'version' => Yii::$app->version,
        ]);
    }

    /**
     * Get Components Object array
     *
     * @return Components
     */
    public function getComponents()
    {
        return new Components([
            'securitySchemes' => [
                'BearerAuth' => new SecurityScheme([
                    'type' => 'http',
                    'scheme' => 'bearer',
                    'bearerFormat' => 'AuthToken and JWT Format' # optional, arbitrary value for documentation purposes
                ])
            ],
        ]);
    }

    /**
     * Get Security Array
     *
     * @return array
     */
    public function getSecurity()
    {
        return [
            'BearerAuth' => [],
        ];
    }

    /**
     * Get Servers
     *
     * @return array An array with Server objects
     */
    public function getServers()
    {
        return [
            new Server([
                'url' => Url::base(true),
                'description' => Yii::$app->siteTitle . ' Server',
            ])
        ];
    }

    /**
     * Get base definition.
     *
     * @return array
     */
    public function getDefinition()
    {
        return [
            'openapi' => '3.0.2',
            'info' => $this->getInfo(),
            'paths' => $this->generator->getPaths(),
            'components' => $this->getComponents(),
            'security' => $this->getSecurity(),
            'servers' => $this->getServers(),
        ];
    }

    /**
     * Create the OpenApi Instance.
     *
     * @return OpenApi
     */
    public function create()
    {
        return new OpenApi($this->getDefinition());
    }
}
