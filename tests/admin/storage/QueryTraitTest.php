<?php

namespace tests\admin\storage;

use admintests\AdminTestCase;
use luya\admin\storage\ItemAbstract;
use luya\admin\storage\IteratorAbstract;
use luya\admin\storage\QueryTrait;
use Yii;
use yii\base\BaseObject;

class FixtureQueryTrait extends BaseObject
{
    use QueryTrait;

    public function getDataProvider()
    {
        return [
            1 => ['id' => 1, 'name' => 'Item 1', 'group' => 'A'],
            2 => ['id' => 2, 'name' => 'Item 2', 'group' => 'A'],
            3 => ['id' => 3, 'name' => 'Item 3', 'group' => 'B'],
        ];
    }

    public function getItemDataProvider($id)
    {
        return (isset($this->getDataProvider()[$id])) ? $this->getDataProvider()[$id] : false;
    }

    public function createItem(array $itemArray)
    {
        return FixtureItem::create($itemArray);
    }

    public function createIteratorObject(array $data)
    {
        return Yii::createObject(['class' => FixtureIterator::className(), 'data' => $data]);
    }
}

class FixtureItem extends ItemAbstract
{
    public function getId()
    {
        return $this->itemArray['id'];
    }

    public function getName()
    {
        return $this->itemArray['name'];
    }

    public function getGroup()
    {
        return $this->itemArray['group'];
    }

    public function fields()
    {
        return ['id', 'name', 'group'];
    }
}

class FixtureIterator extends IteratorAbstract
{
    /**
     * Iterator get current element, generates a new object for the current item on access.
     *
     * @return \luya\admin\storage\ItemAbstract
     */
    public function current()
    {
        return FixtureItem::create(current($this->data));
    }
}

// TEST CASE

class QueryTraitTest extends AdminTestCase
{
    public function testQueryCount()
    {
        $this->assertEquals(3, (new FixtureQueryTrait())->count());
        $this->assertEquals(false, (new FixtureQueryTrait())->where(['id' => 0])->count());
        $this->assertEquals(false, (new FixtureQueryTrait())->where(['id' => 0])->one());
        $this->assertEquals(0, count((new FixtureQueryTrait())->where(['id' => 0])->all()));
        $this->assertEquals(3, count((new FixtureQueryTrait())->all()));
        $this->assertTrue(is_object((new FixtureQueryTrait())->one()));
        $this->assertEquals(1, (new FixtureQueryTrait())->findOne(1)->id);
        $this->assertEquals(3, (new FixtureQueryTrait())->findOne(3)->id);
        $this->assertTrue(is_object((new FixtureQueryTrait())->findOne(1)));
        $this->assertEquals(false, (new FixtureQueryTrait())->findOne(0));
        $this->assertEquals(1, (new FixtureQueryTrait())->where(['id' => 1])->count());
        $this->assertEquals(1, count((new FixtureQueryTrait())->where(['id' => 1])->all()));
    }

    public function testWhereConditions()
    {
        $this->assertEquals(0, (new FixtureQueryTrait())->where(['id' => 1, 'name' => 'Item 2'])->count());
        $this->assertEquals(1, (new FixtureQueryTrait())->where(['id' => 1, 'name' => 'Item 1'])->count());
        $this->assertEquals(2, (new FixtureQueryTrait())->where(['group' => 'A'])->count());
        $this->assertEquals(2, count((new FixtureQueryTrait())->where(['group' => 'A'])->all()));
        $this->assertTrue(is_object((new FixtureQueryTrait())->where(['group' => 'A'])->one()));
    }

    public function testWhereOperatorConditions()
    {
        $this->assertEquals(1, (new FixtureQueryTrait())->where(['==', 'id', 1])->count());
        $this->assertEquals(0, (new FixtureQueryTrait())->where(['==', 'id', '1'])->count());
        $this->assertEquals(1, (new FixtureQueryTrait())->where(['=', 'id', 1])->count());
        $this->assertEquals(1, (new FixtureQueryTrait())->where(['=', 'id', '1'])->count());
        $this->assertEquals(3, (new FixtureQueryTrait())->where(['>=', 'id', 1])->count());
        $this->assertEquals(2, (new FixtureQueryTrait())->where(['>', 'id', 1])->count());
        $this->assertEquals(3, (new FixtureQueryTrait())->where(['<=', 'id', 3])->count());
        $this->assertEquals(2, (new FixtureQueryTrait())->where(['<', 'id', 3])->count());

        $this->assertEquals(1, (new FixtureQueryTrait())->where(['>', 'id', 1])->andWhere(['==', 'group', 'B'])->count());
    }

    public function testWhereInOperatorConditions()
    {
        $x = (new FixtureQueryTrait())->where(['in', 'id', [1, 3]])->all();

        $this->assertEquals(2, count($x));


        $i = 0;
        foreach ($x as $k => $v) {
            if ($i == 0) {
                $this->assertEquals(1, $v->id);
            } else {
                $this->assertEquals(3, $v->id);
            }

            $i++;
        }
    }

    public function testIterator()
    {
        $b = (new FixtureQueryTrait())->where(['group' => 'B'])->all();

        $this->assertEquals(1, count($b));
        foreach ($b as $item) {
            $this->assertEquals(3, $item->id);
            $this->assertEquals('B', $item->group);
            $this->assertEquals('Item 3', $item->name);

            $this->assertArrayHasKey('id', $item->toArray());
            $this->assertArrayHasKey('group', $item->toArray());
            $this->assertArrayHasKey('name', $item->toArray());
        }
    }

    public function testLimit()
    {
        $this->assertEquals(2, count((new FixtureQueryTrait())->limit(2)->all()));
        $this->assertEquals(1, count((new FixtureQueryTrait())->limit(1)->all()));

        $items = (new FixtureQueryTrait())->where(['>', 'id', 1])->limit(1)->all();

        $this->assertEquals(1, count($items));

        foreach ($items as $item) {
            $this->assertEquals(2, $item->id);
        }
    }

    public function testOffset()
    {
        $items = (new FixtureQueryTrait())->where(['>', 'id', 1])->offset(1)->limit(1)->all();

        $this->assertEquals(1, count($items));

        foreach ($items as $item) {
            $this->assertEquals(3, $item->id);
        }
    }

    public function testSorting()
    {
        $x = (new FixtureQueryTrait())->where(['in', 'id', [1,2,3]])->all();

        $arr = array_values(iterator_to_array($x));
        $this->assertSame(1, $arr[0]->id);
        $this->assertSame(2, $arr[1]->id);
        $this->assertSame(3, $arr[2]->id);

        // sort now
        $y = (new FixtureQueryTrait())->where(['in', 'id', [1,2,3]])->orderBy(['id' => SORT_DESC])->all();

        $arr2 = array_values(iterator_to_array($y));
        $this->assertSame(3, $arr2[0]->id);
        $this->assertSame(2, $arr2[1]->id);
        $this->assertSame(1, $arr2[2]->id);

        // sort by id ordering
        $z = (new FixtureQueryTrait())->where(['in', 'id', [1,2,3]])->orderBy(['id' => [3,1,2]])->all();

        $arr3 = array_values(iterator_to_array($z));
        $this->assertSame(3, $arr3[0]->id);
        $this->assertSame(1, $arr3[1]->id);
        $this->assertSame(2, $arr3[2]->id);

        // sort by id ordering but remove one
        $a = (new FixtureQueryTrait())->where(['in', 'id', [1,2,3]])->orderBy(['id' => [3,2]])->all();

        $arr4 = array_values(iterator_to_array($a));
        $this->assertSame(3, $arr2[0]->id);
        $this->assertSame(2, $arr2[1]->id);
        $this->assertSame(2, count($arr4));
    }
}
