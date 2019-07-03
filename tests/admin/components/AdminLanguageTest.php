<?php

namespace luya\admin\tests\admin\components;

use admintests\AdminModelTestCase;
use luya\testsuite\fixtures\NgRestModelFixture;
use luya\admin\models\Lang;
use luya\admin\components\AdminLanguage;

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

    /**
     * @runInSeparateProcess
     */
    public function testGetLanguages()
    {
        $component = new AdminLanguage();
        $data = $component->getLanguages();
        $this->assertSame(2, count($data));

        // !important: This will resolve english language because composite language is english, even when german is_default=1
        $this->assertSame(2, $component->getActiveId());
        $this->assertSame('en', $component->getActiveShortCode());
    }
}