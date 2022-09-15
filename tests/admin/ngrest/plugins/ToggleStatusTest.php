<?php

namespace admintests\admin\ngrest\plugins;

use luya\admin\apis\UserController;
use luya\admin\controllers\UserController as LuyaUserController;
use luya\admin\models\User;
use luya\admin\ngrest\Config;
use luya\admin\ngrest\plugins\ToggleStatus;
use luya\admin\ngrest\render\RenderCrud;
use luya\admin\tests\NgRestTestCase;

class ToggleStatusTest extends NgRestTestCase
{
    public $modelClass = User::class;
    public $apiClass = UserController::class;
    public $controllerClass = LuyaUserController::class;

    public function testNotAllowed()
    {
        $config = new Config();
        $config->setApiEndpoint(User::ngRestApiEndpoint());

        $context = new RenderCrud([
            'config' => $config,
        ]);
        $plugin = new ToggleStatus([
            'renderContext' => $context,
            'alias' => 'test',
            'name' => 'test',
            'i18n' => false,
        ]);
        $this->assertSame([
            0 => '<i class="material-icons" ng-if="model == 1">check</i>',
            1 => '<i class="material-icons" ng-if="model == 0">close</i>',
        ], $plugin->renderList(1, 'model'));
    }

    public function testAllowed()
    {
        $this->apiCanUpdate();
        $config = new Config();
        $config->setApiEndpoint(User::ngRestApiEndpoint());

        $context = new RenderCrud([
            'config' => $config,
        ]);
        $plugin = new ToggleStatus([
            'renderContext' => $context,
            'alias' => 'test',
            'name' => 'test',
            'i18n' => false,
        ]);
        $this->assertSame([
            0 => '<i class="material-icons" style="cursor:pointer;" ng-if="model == 1" ng-click="toggleStatus(item, &#039;test&#039;, &#039;test&#039;, model)">check</i>',
            1 => '<i class="material-icons" style="cursor:pointer;" ng-if="model == 0" ng-click="toggleStatus(item, &#039;test&#039;, &#039;test&#039;, model)">close</i>',
        ], $plugin->renderList(1, 'model'));
    }

    public function testScheduling()
    {
        $this->apiCanUpdate();

        $config = new Config();
        $config->setApiEndpoint(User::ngRestApiEndpoint());

        $context = new RenderCrud([
            'config' => $config,
            'model' => new $this->modelClass(),
        ]);

        $plugin = new ToggleStatus([
            'renderContext' => $context,
            'alias' => 'test',
            'name' => 'test',
            'i18n' => false,
            'scheduling' => true,
        ]);

        $this->assertSame('<luya-schedule value="model" title="test" model-class="luya\admin\models\User" attribute-name="test" attribute-values=\'[{"label":"No","value":0},{"label":"Yes","value":1}]\' primary-key-value="getRowPrimaryValue(item)"></luya-schedule>', $plugin->renderList(1, 'model'));

        $this->assertSame([
            0 => '<div class="crud-loader-tag crud-loader-tag-for-checkbox"><luya-schedule value="model" title="test" model-class="luya\admin\models\User" attribute-name="test" attribute-values=\'[{"label":"No","value":0},{"label":"Yes","value":1}]\' primary-key-value="getRowPrimaryValue(data.update)" only-icon="1"></luya-schedule></div>',
            1 => '<zaa-checkbox options="{&quot;true-value&quot;:1,&quot;false-value&quot;:0}" initvalue="0" fieldid="id" model="model" label="test" fieldname="test" i18n=""></zaa-checkbox>'
        ], $plugin->renderUpdate('id', 'model'));
    }
}
