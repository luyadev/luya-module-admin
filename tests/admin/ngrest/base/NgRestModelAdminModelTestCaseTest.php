<?php

namespace admintests\admin\ngrest\base;

use admintests\AdminModelTestCase;
use luya\admin\models\Tag;
use luya\testsuite\fixtures\NgRestModelFixture;
use luya\admin\models\Lang;

class NgRestModelAdminModelTestCaseTest extends AdminModelTestCase
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

        $this->assertSame('English', $model->translation);
        $this->assertSame('English', $model->i18nAttributeValue('translation'));

        $lang->cleanup();
        $fixture->cleanup();
    }
}