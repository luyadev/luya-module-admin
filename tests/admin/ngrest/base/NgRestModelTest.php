<?php

namespace admintests\admin\ngrest\base;

use admintests\AdminTestCase;
use admintests\data\fixtures\TagFixture;
use admintests\data\models\TestNewNotationNgRestModel;
use admintests\data\models\TestNgRestModel;

class NgRestModelTest extends AdminTestCase
{
    public function testScenarios()
    {
        $model = new TagFixture();
        $model->load();
        $tag = $model->getModel('tag1');
        $scenes = $tag->scenarios();

        $this->assertSame(3, count($scenes));

        $this->assertSame($scenes['default'], $scenes['restcreate']);
        $this->assertSame($scenes['default'], $scenes['restupdate']);
        $this->assertSame($scenes['restcreate'], $scenes['restcreate']);
    }

    public function testBehaviorIsAttached()
    {
        $model = new TestNgRestModel();
        $behaviors = $model->getBehaviors();

        $this->assertArrayHasKey('NgRestEventBehavior', $behaviors);
        $this->assertArrayHasKey('LogBehavior', $behaviors);
    }

    public function testQueryBehaviorsAreAttached()
    {
        $query = TestNgRestModel::find();
        $behaviors = $query->behaviors;

        $this->assertArrayHasKey('DummyBehavior', $behaviors);
    }

    public function testGenericSearchFields()
    {
        $model = new TagFixture();
        $model->load();
        $tag = $model->getModel('tag1');

        $this->assertSame(['{{%admin_tag}}.id', '{{%admin_tag}}.name', '{{%admin_tag}}.translation'], $tag->genericSearchFields());
    }

    public function testGenericSearch()
    {
        $model = new TagFixture();
        $model->load();
        $tag = $model->getModel('tag1');
        $results = $tag->genericSearch('John');

        $this->assertSame('john', $results->one()->name);
        $this->assertSame('john', $results->one()->i18nAttributeFallbackValue('name'));
    }

    public function testGetNgRestConfig()
    {
        $model = new TestNgRestModel();

        $array = $model->getNgRestConfig()->getConfig();

        $this->assertArrayHasKey('foo', $array['list']);
        $this->assertArrayHasKey('bar', $array['list']);
        $this->assertArrayHasKey('extraAttr', $array['list']);

        $this->assertArrayHasKey('foo', $array['update']);
        $this->assertArrayNotHasKey('bar', $array['update']);
        $this->assertArrayNotHasKey('extraAttr', $array['update']);

        $this->assertArrayHasKey('foo', $array['create']);
        $this->assertArrayNotHasKey('bar', $array['create']);
        $this->assertArrayNotHasKey('extraAttr', $array['create']);

        $this->assertTrue($array['delete']);

        $this->assertArrayHasKey('aw', $array);
        $this->assertArrayHasKey('1f7228610892e760f9f28dd133da5a25100dbf1c', $array['aw']);
    }

    public function testCompareNewAndOldConfig()
    {
        $old = new TestNgRestModel();
        $oldArray = $old->getNgRestConfig()->getConfig();

        unset($oldArray['aw']['1f7228610892e760f9f28dd133da5a25100dbf1c']['objectConfig']['ngRestModelClass']);

        $new = new TestNewNotationNgRestModel();
        $newArray = $new->getNgRestConfig()->getConfig();

        unset($newArray['aw']['1f7228610892e760f9f28dd133da5a25100dbf1c']['objectConfig']['ngRestModelClass']);

        $this->assertSame($oldArray, $newArray);
    }
}
