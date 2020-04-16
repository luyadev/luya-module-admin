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
 * Generate the OpenApi Instance
 * 
 * @author Basil Suter <git@nadar.io>
 * @since 3.2.0
 */
class OpenApiGenerator
{
    public $generator;

    public function __construct(Generator $generator)
    {
        if (!class_exists(OpenApi::class)) {
            throw new InvalidConfigException("The composer package cebe/php-openapi must be installed to generate the OpenAPI file.");
        }
        
        $this->generator = $generator;    
    }

    public function getInfo()
    {
        return new Info([
            'title' => Yii::$app->siteTitle,
            'version' => '1.0.0',
        ]);
    }

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

    public function getSecurity()
    {
        return [
            'BearerAuth' => [],
        ];
    }

    public function getServers()
    {
        return [
            new Server([
                'url' => Url::base(true),
                'description' => Yii::$app->siteTitle . ' Server',
            ])
        ];
    }

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
     * Create the OpenApi Instance
     * @return OpenApi
     */
    public function create()
    {
        return new OpenApi($this->getDefinition());
    }

}