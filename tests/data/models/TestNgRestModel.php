<?php

namespace admintests\data\models;

use admintests\data\stubs\StubBehavior;
use luya\admin\aws\TaggableActiveWindow;
use luya\admin\ngrest\base\NgRestModel;

class TestNgRestModel extends NgRestModel
{
    public static function getTableSchema()
    {
        return [];
    }

    public static function primaryKey()
    {
        return 'id';
    }

    public static function ngRestApiEndpoint()
    {
        return 'foo-bar';
    }

    public static function findActiveQueryBehaviors()
    {
        return [
            'DummyBehavior' => StubBehavior::class
        ];
    }

    public function rules()
    {
        return [
            [['foo', 'bar', 'extraAttr'], 'safe'],
        ];
    }

    public function ngRestAttributeTypes()
    {
        return [
            'foo' => 'text',
            'bar' => 'textarea',
        ];
    }

    public function ngRestExtraAttributeTypes()
    {
        return [
            'extraAttr' => 'datetime',
        ];
    }

    public function ngRestConfig($config)
    {
        $this->ngRestConfigDefine($config, 'list', ['foo', 'bar', 'extraAttr']);
        $this->ngRestConfigDefine($config, ['create', 'update'], ['foo']);
        $config->delete = true;
        $config->aw->load(['class' => TaggableActiveWindow::class]);
        return $config;
    }
}

class TestNewNotationNgRestModel extends NgRestModel
{
    public static function getTableSchema()
    {
        return [];
    }

    public static function primaryKey()
    {
        return ['id'];
    }

    public static function ngRestApiEndpoint()
    {
        return 'foo-bar';
    }

    public function rules()
    {
        return [
            [['foo', 'bar', 'extraAttr'], 'safe'],
        ];
    }

    public function ngRestAttributeTypes()
    {
        return [
            'foo' => 'text',
            'bar' => 'textarea',
        ];
    }

    public function ngRestExtraAttributeTypes()
    {
        return [
            'extraAttr' => 'datetime',
        ];
    }

    public function ngRestScopes()
    {
        return [
            ['list', ['foo', 'bar', 'extraAttr']],
            [['create', 'update'], ['foo']],
            ['delete', true],
        ];
    }

    public function ngRestActiveWindows()
    {
        return [
            ['class' => TaggableActiveWindow::class],
        ];
    }
}
