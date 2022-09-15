<?php

namespace admintests\admin\ngrest\base;

use admintests\AdminModelTestCase;
use admintests\data\models\PoolModel;
use luya\admin\models\Lang;
use luya\admin\models\TagRelation;
use luya\admin\models\User;
use luya\admin\ngrest\base\NgRestActiveQuery;
use luya\testsuite\fixtures\NgRestModelFixture;

class NgRestActiveQueryTest extends AdminModelTestCase
{
    public function testJsonWhere()
    {
        new NgRestModelFixture([
            'modelClass' => User::class,
        ]);

        $query = new NgRestActiveQuery(User::class);
        $this->assertSame([
            '>=',
            'JSON_EXTRACT(jsonfield, "$.key")',
            'value'
        ], $query->jsonWhere('>=', 'jsonfield', 'key', 'value')->where);
    }

    public function testJsonArrayWhere()
    {
        new NgRestModelFixture([
            'modelClass' => User::class,
        ]);

        $query = new NgRestActiveQuery(User::class);
        $this->assertSame([
            '>',
            'JSON_CONTAINS(JSON_EXTRACT(jsonfield, "$[*].key"), "value")',
            0
        ], $query->jsonArrayWhere('jsonfield', 'key', 'value')->where);
    }

    public function testI18nWhere()
    {
        new NgRestModelFixture([
            'modelClass' => Lang::class,
        ]);

        $query = new NgRestActiveQuery(Lang::class);
        $this->assertSame([
            '=',
            'JSON_EXTRACT(key, "$.en")',
            'value'
        ], $query->i18nWhere('key', 'value')->where);
    }

    public function testJsonOrderBy()
    {
        new NgRestModelFixture([
            'modelClass' => Lang::class,
        ]);

        $query = new NgRestActiveQuery(Lang::class);
        $exp = $query->jsonOrderBy('attribute', 'key', 'asc')->orderBy;

        $this->assertSame('attribute->"$.key" asc', $exp[0]->expression);
    }

    public function testInPool()
    {
        new NgRestModelFixture([
            'modelClass' => User::class,
        ]);

        $query = new NgRestActiveQuery(User::class);

        $this->assertSame(null, $query->inPool()->where);

        $q = $query->inPool('notexisting');
        $this->assertInstanceOf(NgRestActiveQuery::class, $q);
    }

    public function testInPoolException()
    {
        new NgRestModelFixture([
            'modelClass' => User::class,
        ]);

        $query = new NgRestActiveQuery(User::class);

        $this->expectException("yii\base\InvalidConfigException");
        $query->inPool('notexisting', true);
    }

    public function testInPoolWhereCondition()
    {
        new NgRestModelFixture([
            'modelClass' => PoolModel::class,
        ]);

        $query = new NgRestActiveQuery(PoolModel::class);

        $this->assertSame(['field' => 'value'], $query->inPool('pool1')->where);
    }

    public function testFindByPrimaryKey()
    {
        new NgRestModelFixture([
            'modelClass' => User::class,
        ]);

        $query = new NgRestActiveQuery(User::class);
        $this->assertSame(['id' => 1], $query->byPrimaryKey(1)->where);

        $query = new NgRestActiveQuery(User::class);
        $this->assertSame(['id' => 1], $query->byPrimaryKey([1])->where);

        new NgRestModelFixture([
            'modelClass' => TagRelation::class,
            'primaryKey' => ['tag_id' => 'int(11)', 'table_name' => 'int(11)', 'pk_id' => 'int(11)', 'PRIMARY KEY (tag_id, table_name, pk_id)'],
        ]);

        $query = new NgRestActiveQuery(TagRelation::class);
        $this->assertSame(['tag_id' => 1, 'table_name' => 'name', 'pk_id' => 3], $query->byPrimaryKey("1,name,3")->where);

        $query = new NgRestActiveQuery(TagRelation::class);
        $this->assertSame(['tag_id' => 1, 'table_name' => 'name', 'pk_id' => 3], $query->byPrimaryKey([1,'name',3])->where);
    }

    public function testFailingFindByPrimaryKey()
    {
        new NgRestModelFixture([
            'modelClass' => User::class,
        ]);
        $query = new NgRestActiveQuery(User::class);
        $this->expectException("yii\db\Exception");
        $this->assertSame(['and', ['id' => '1'], ['id' => '1']], $query->byPrimaryKey("1,2")->where);
        $this->assertSame(['and', ['id' => '1'], ['id' => '1']], $query->byPrimaryKey([1,2])->where);
    }
}
