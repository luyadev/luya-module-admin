<?php

namespace admintests\admin\ngrest\base;

use admintests\AdminTestCase;
use admintests\data\fixtures\TagFixture;
use admintests\data\models\TestNgRestModel;
use admintests\data\models\TestNewNotationNgRestModel;
use luya\admin\models\Tag;
use luya\testsuite\fixtures\NgRestModelFixture;
use luya\admin\models\Lang;

class NgRestModelTest extends AdminTestCase
{
    public function testI18nAttributeMethods()
    {
        $lang = new NgRestModelFixture([
            'modelClass' => Lang::class,
            'fixtureData' => [
                'id1' => [
                    'id' => 1,
                    'name' => 'English',
                    'short_code' => 'en',
                    'is_default' => 1,
                    'is_deleted' => 0,
                ]
            ]
        ]);

        $lang->getModel('id1');

        $fixture = new NgRestModelFixture([
            'modelClass' => Tag::class,
            'fixtureData' => [
                'id1' => [
                    'id' => 1,
                    'name' => 'name',
                    'translation' => '{"de":"Deutsch", "en": "English"}',
                ]
            ]
        ]);

        $model = $fixture->getModel('id1');

        $this->assertSame('English', $model->i18nAttributeValue('translation'));
    }
    
/*
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

    public function testGenericSearchFields()
    {
        $model = new TagFixture();
        $model->load();
        $tag = $model->getModel('tag1');
        
        $this->assertSame(['{{%admin_tag}}.id', '{{%admin_tag}}.name'], $tag->genericSearchFields());
    }
    
    public function testGenericSearch()
    {
        $model = new TagFixture();
        $model->load();
        $tag = $model->getModel('tag1');
        $results = $tag->genericSearch('John');
        
        $this->assertSame('john', $results->one()->name);
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
    */
}
