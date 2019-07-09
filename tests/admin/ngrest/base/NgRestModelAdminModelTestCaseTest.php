<?php

namespace admintests\admin\ngrest\base;

use Yii;
use admintests\AdminModelTestCase;
use luya\admin\models\Tag;
use luya\testsuite\fixtures\NgRestModelFixture;
use luya\admin\models\Lang;
use luya\web\Composition;

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

    public function testI18nAttributeFallbackValue()
    {
        $lang = new NgRestModelFixture([
            'modelClass' => Lang::class,
            'fixtureData' => [
                'id1' => [
                    'id' => 1,
                    'name' => 'English',
                    'short_code' => 'en',
                    'is_default' => 0,
                    'is_deleted' => 0,
                ],
                'id2' => [
                    'id' => 2,
                    'name' => 'French',
                    'short_code' => 'fr',
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
                    'translation' => '{"de":"Deutsch", "en": "", "fr": "Francais"}',
                ]
            ]
        ]);

        $model = $fixture->getModel('id1');

        $this->assertEmpty($model->translation);
        $this->assertSame('Deutsch', $model->i18nAttributeFallbackValue('translation'));
        $this->assertSame('Francais', $model->i18nAttributeFallbackValue('translation', 'fr'));
    }
}
