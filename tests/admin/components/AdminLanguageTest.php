<?php

namespace luya\admin\tests\admin\components;

use admintests\AdminModelTestCase;
use luya\admin\components\AdminLanguage;
use luya\admin\models\Lang;
use luya\testsuite\fixtures\NgRestModelFixture;
use luya\web\Composition;

class AdminLanguageTest extends AdminModelTestCase
{
    protected $fixture;

    public function afterSetup()
    {
        $this->fixture = new NgRestModelFixture([
            'modelClass' => Lang::class,
            'fixtureData' => [
                1 => [
                    'id' => 1,
                    'name' => 'Deutsch',
                    'short_code' => 'de',
                    'is_default' => 1,
                    'is_deleted' => 0,
                ],
                2 => [
                    'id' => 2,
                    'name' => 'English',
                    'short_code' => 'en',
                    'is_default' => 0,
                    'is_deleted' => 0,
                ]
            ]
        ]);
    }

    public function testGetLanguages()
    {
        $component = new AdminLanguage();
        $component->clearCache();
        $data = $component->getLanguages();
        $this->assertSame(2, count($data));

        // !important: This will resolve english language because composite language is english, even when german is_default=1
        $this->assertSame(2, $component->getActiveId());
        $this->assertSame('en', $component->getActiveShortCode());
    }

    public function testEmptyCompositionLangShortCode()
    {
        // since version 3.1.0 this won't have any effect as the language is handeld trought Yii::$app->language
        $this->app->composition->setKey(Composition::VAR_LANG_SHORT_CODE, null);

        $component = new AdminLanguage();
        $data = $component->getLanguages();
        // This will resolve is_default = 1 because lang short code is null.
        $this->assertSame(2, $component->getActiveId());
        $this->assertSame('en', $component->getActiveShortCode());

        $this->assertSame('de', $component->getDefaultLanguageShortCode());
    }

    public function testGetbyShortCode()
    {
        $component = new AdminLanguage();
        $this->assertSame([
            'id' => "2",
            'name' => 'English',
            'short_code' => 'en',
            'is_default' => "0",
            'is_deleted' => "0",
        ], $component->getLanguageByShortCode('en'));
        $this->assertSame([
            'id' => "1",
            'name' => 'Deutsch',
            'short_code' => 'de',
            'is_default' => "1",
            'is_deleted' => "0",
        ], $component->getLanguageByShortCode('de'));
        $this->assertTrue($component->clearCache()); // cache not defined... delete will faile
    }

    public function testActiveShortCodeCallable()
    {
        $component = new AdminLanguage();
        $lang = $component->getActiveLanguage();
        $this->assertSame(2, $component->getActiveId());

        $component = new AdminLanguage();
        $component->activeShortCodeCallable = function () {
            return 'de';
        };
        $lang = $component->getActiveLanguage();
        $this->assertSame(1, $component->getActiveId());
    }
}
