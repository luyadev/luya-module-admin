<?php

namespace admintests\admin\ngrest\base;

use admintests\AdminModelTestCase;
use luya\admin\models\Lang;
use luya\admin\models\Tag;
use luya\admin\models\User;
use luya\testsuite\fixtures\NgRestModelFixture;

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
        $this->assertSame(['translation' => 'English'], $model->i18nAttributesValue(['translation']));

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

        $lang->cleanup();
        $fixture->cleanup();
    }

    public function testI18nAttributeLanguageValue()
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
        $this->assertSame('Deutsch', $model->i18nAttributeLanguageValue('translation', 'de'));
        $this->assertSame('Francais', $model->i18nAttributeLanguageValue('translation', 'fr'));
        $this->assertSame('Francais', $model->i18nAttributeLanguageValue('translation', 'fr', true));
        $this->assertNull($model->i18nAttributeLanguageValue('translation', 'xyz'));

        $lang->cleanup();
        $fixture->cleanup();
    }

    public function testI18nAttributeFallbackValueWithMarkdownTextConverter()
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
            'modelClass' => I18nMarkdownTagTest::class,
            'fixtureData' => [
                'id1' => [
                    'id' => 1,
                    'email' => 'foobar@test.com',
                    'firstname' => 'name',
                    'lastname' => '{"de":"Deutsch *foo*", "en": "", "fr": "Francais *foo*"}',
                    'is_deleted' => 0,
                ]
            ]
        ]);

        $this->assertSameTrimmed('<p>Deutsch <em>foo</em></p>', $fixture->getModel('id1')->i18nAttributeFallbackValue('lastname', 'de'));
        $this->assertSameTrimmed('', $fixture->getModel('id1')->lastname); // <p>Francais <em>foo</em></p>
        $this->assertSameTrimmed('<p>Francais <em>foo</em></p>', $fixture->getModel('id1')->i18nAttributeFallbackValue('lastname', 'fr')); // <p>Francais <em>foo</em></p>
        $this->assertSameTrimmed('', $fixture->getModel('id1')->lastname); // <p>Francais <em>foo</em></p>

        $fixture->cleanup();
        $lang->cleanup();
    }
}

class I18nMarkdownTagTest extends User
{
    public $i18n = ['lastname', 'firstname'];

    public static function tableName()
    {
        return 'user_copy';
    }

    public function ngRestAttributeTypes()
    {
        $at = parent::ngRestAttributeTypes();
        $at['firstname'] = ['textarea', 'markdown' => true];
        $at['lastname'] = ['textarea', 'markdown' => true];

        return $at;
    }
}
