<?php

namespace admintests\admin\ngrest\base;

use admintests\AdminTestCase;
use luya\admin\ngrest\base\Api;
use luya\admin\ngrest\ConfigBuilder;
use luya\admin\ngrest\Config;

class ApiTest extends AdminTestCase
{
    
    private function getConfig()
    {
        $config = new ConfigBuilder('model\class\name');

        $config->list->field('create_var_1', 'testlabel in list')->text();
        $config->list->field('list_var_1', 'testlabel')->textarea();
        $config->list->field('list_var_2', 'testlabel', true)->textarea(); // This var is i18n

        return $config->getConfig();
    }
    
    public function testWithRelations()
    {
        $rel = new TestApi('test-api', $this->app);
        $this->app->request->setQueryParams(['expand' => 'bar,foo,news']);
        $f = $rel->getWithRelation('index');

        $this->assertSame(['bar', 'foo', 'news.image'], $f);
    }

    public function testWithRelatiosnAction()
    {
        $rel = new TestActionApi('test-api', $this->app);
        $this->app->request->setQueryParams(['expand' => 'image,news']);
        $f = $rel->getWithRelation('index');

        $this->assertSame(['images', 'news.image'], $f);
    }

    public function testGenerateSortAttributes()
    {
        $configData = $this->getConfig();
        $ngRest = new Config(['apiEndpoint' => 'api-admin-test', 'primaryKey' => ['id']]);
        $ngRest->setConfig($configData);

        $rel = new TestApi('test-api', $this->app);

        $f = $rel->generateSortAttributes($ngRest);

        $this->assertTrue(array_key_exists('list_var_2', $f));

        $expected = [
            'list_var_2' => [
                'asc' => ['(JSON_EXTRACT(`list_var_2`, "$.en"))' => SORT_ASC],
                'desc' => ['(JSON_EXTRACT(`list_var_2`, "$.en"))' => SORT_DESC]
            ],
            'list_var_1',
            'create_var_1'
        ];


        $this->assertSame($expected, $f);
    }

}

class TestApi extends Api
{
    public function withRelations()
    {
        return ['bar', 'foo', 'news.image'];
    }

    public $modelClass = 'null';
}

class TestActionApi extends TestApi
{
    public function withRelations()
    {
        return [
             'index' => ['images', 'news.image', 'not'],
             'list' => ['user'],
        ];
    }
}
