<?php

namespace admintests\admin\buttons;

use luya\admin\buttons\ToggleStatusActiveButton;
use luya\admin\models\Lang;
use luya\admin\tests\NgRestTestCase;
use luya\testsuite\fixtures\NgRestModelFixture;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;

class ToggleStatusActiveButtonTest extends NgRestTestCase
{
    public $modelClass = Lang::class;

    public function testWrongConfiguration()
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage("The attribute property must be set.");

        $button = new ToggleStatusActiveButton([
            'attribute' => '',
        ]);
    }

    public function testHandleNoneUnique()
    {
        $fixture = new NgRestModelFixture([
            'modelClass' => Lang::class,
        ]);

        /** @var Lang $modelEn */
        $modelEn = $fixture->newModel;
        $modelEn->short_code = 'en';
        $modelEn->name = 'English';
        $modelEn->is_default = 1;
        $modelEn->insert(false);
        $modelEn->refresh();

        $this->assertTrue($modelEn->is_default, "English must be default before toggle.");

        /** @var Lang $modelFr */
        $modelFr = $fixture->newModel;
        $modelFr->short_code = 'fr';
        $modelFr->name = 'Francais';
        $modelFr->is_default = 0;
        $modelFr->insert(false);
        $modelFr->refresh();

        $this->assertFalse($modelFr->is_default, "Francais must not be default before toggle.");

        $button = new ToggleStatusActiveButton([
            'attribute' => 'is_default',
        ]);
        $result = $button->handle($modelFr);

        $this->assertTrue($result['success']);
        $this->assertEquals('3 enabled', $result['message']);

        $modelFr->refresh();
        $this->assertTrue($modelFr->is_default, "Francais must be default after toggle too.");

        $modelEn->refresh();
        $this->assertTrue($modelEn->is_default, "English must be still default after toggle.");
    }

    public function testHandleUniqueStatus()
    {
        $fixture = new NgRestModelFixture([
            'modelClass' => Lang::class,
        ]);

        /** @var Lang $modelEn */
        $modelEn = $fixture->newModel;
        $modelEn->short_code = 'en';
        $modelEn->name = 'English';
        $modelEn->is_default = 1;
        $modelEn->is_deleted = 0;
        $modelEn->insert(false);
        $modelEn->refresh();

        $this->assertTrue($modelEn->is_default, "English must be default before toggle.");

        /** @var Lang $modelFr */
        $modelFr = $fixture->newModel;
        $modelFr->short_code = 'fr';
        $modelFr->name = 'Francais';
        $modelFr->is_default = 0;
        $modelFr->is_deleted = 0;
        $modelFr->insert(false);
        $modelFr->refresh();

        $this->assertFalse($modelFr->is_default, "Francais must not be default before toggle.");

        $button = new ToggleStatusActiveButton([
            'attribute' => 'is_default',
            'uniqueStatus' => true,
        ]);
        $result = $button->handle($modelFr);

        $this->assertTrue($result['success']);
        $this->assertEquals('3 enabled', $result['message']);

        $modelFr->refresh();
        $this->assertTrue($modelFr->is_default, "Francais must be default after toggle.");

        $modelEn->refresh();
        $this->assertFalse($modelEn->is_default, "English must not be default after toggle.");
    }

    public function testHandleCustomValues()
    {
        $fixture = new NgRestModelFixture([
            'modelClass' => Lang::class,
        ]);

        /** @var Lang $model */
        $model = $fixture->newModel;
        $model->name = 'English';
        $model->is_default = 'off';

        $button = new ToggleStatusActiveButton([
            'attribute' => 'is_default',
            'enableValue' => 'on',
            'disableValue' => 'off',
        ]);

        // toggle on
        $result = $button->handle($model);
        $this->assertTrue($result['success']);
        $this->assertEquals(' enabled', $result['message']);
        $this->assertEquals('on', $model->is_default, "Default value must be on");

        // toggle off
        $result = $button->handle($model);
        $this->assertTrue($result['success']);
        $this->assertEquals(' disabled', $result['message']);
        $this->assertEquals('off', $model->is_default, "Default value must be off");
    }

    public function testInvalidValue()
    {
        $fixture = new NgRestModelFixture([
            'modelClass' => Lang::class,
        ]);

        /** @var Lang $model */
        $model = $fixture->newModel;
        $model->name = 'English';
        $model->is_default = 1;

        $button = new ToggleStatusActiveButton([
            'attribute' => 'is_default',
            'enableValue' => 'on',
            'disableValue' => 'off',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The value '1' could not toggled.");
        $button->handle($model);
    }

    public function testUnknownAttribute()
    {
        $fixture = new NgRestModelFixture([
            'modelClass' => Lang::class,
        ]);

        /** @var Lang $model */
        $model = $fixture->newModel;
        $model->name = 'English';
        $model->is_default = 1;

        $button = new ToggleStatusActiveButton([
            'attribute' => 'foo',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $button->handle($model);
    }

    public function testLabelAndIcon()
    {
        $button = new ToggleStatusActiveButton([
            'attribute' => 'foo',
        ]);

        $this->assertEquals("Toggle status", $button->getDefaultLabel());
        $this->assertEquals("toggle_on", $button->getDefaultIcon());
    }
}
