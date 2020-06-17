<?php

namespace luya\admin\tests\admin\openapi;

use admintests\AdminModelTestCase;
use cebe\openapi\spec\PathItem;
use luya\admin\openapi\Generator;
use luya\admin\openapi\OpenApiGenerator;
use luya\admin\tests\data\controllers\OpenApiController;
use luya\testsuite\traits\DatabaseTableTrait;
use luya\web\UrlManager;
use yii\rest\UrlRule;

class UrlRulePhpDocTest extends AdminModelTestCase
{
    use DatabaseTableTrait;

    public function testUrlRuleWithMethodPhpDoc()
    {
        $this->createAdminLangFixture();

        $urlManager = new UrlManager();
        $urlManager->addRules([
            [
                'class' => UrlRule::class,
                'controller' => 'open-api',
            ]
        ]);

        $this->app->controllerMap = [
            'open-api' => OpenApiController::class,
        ];

        $generator = new Generator($urlManager);
        

        $paths = $generator->getPaths();

        $this->assertArrayHasKey('/open-apis', $paths);

        /** @var PathItem $path */
        $path = $paths['/open-apis'];

        $this->assertSame('Controller Description', $path->description);
        $this->assertSame('Controller Summary', $path->summary);

        $this->assertSame('Index', $path->get->summary);
        $this->assertSame('Return the data models ..', $path->get->description);

        $openapi = new OpenApiGenerator($generator);
        $json = $openapi->create()->getSerializableData();

        //var_dump($json);
    }
}