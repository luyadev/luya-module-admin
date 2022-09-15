<?php

namespace luya\admin\tests\admin\validators;

use admintests\AdminModelTestCase;
use luya\admin\validators\I18nRequiredValidator;
use luya\base\DynamicModel;

class I18nRequiredValidatorTest extends AdminModelTestCase
{
    public function testValidatorInvalidFormat()
    {
        $model = new DynamicModel(['i18n' => null]);
        $validator = new I18nRequiredValidator();
        $validator->validateAttribute($model, 'i18n');
        $this->assertSame('The given attribute \"i18n\" must be type of array.', $model->getFirstError('i18n'));
    }

    public function testValidatorMissingLanguage()
    {
        $this->createAdminLangFixture([
            1 => [
                'id' => 1,
                'short_code' => 'en',
                'is_deleted' => 0,
            ]
        ]);
        $model = new DynamicModel(['i18n' => []]);
        $validator = new I18nRequiredValidator();
        $validator->validateAttribute($model, 'i18n');
        $this->assertSame('The language key \"en\" is missing and is required.', $model->getFirstError('i18n'));
    }

    public function testValidatorMissingLanguageAsJson()
    {
        $this->createAdminLangFixture([
            1 => [
                'id' => 1,
                'short_code' => 'en',
                'is_deleted' => 0,
            ]
        ]);
        $model = new DynamicModel(['i18n' => '{}']);
        $validator = new I18nRequiredValidator();
        $validator->validateAttribute($model, 'i18n');
        $this->assertSame('The language key \"en\" is missing and is required.', $model->getFirstError('i18n'));
    }

    public function testValidatorEmptyLanguage()
    {
        $this->createAdminLangFixture([
            1 => [
                'id' => 1,
                'short_code' => 'en',
                'is_deleted' => 0,
            ]
        ]);
        $model = new DynamicModel(['i18n' => ['en' => '']]);
        $validator = new I18nRequiredValidator();
        $validator->validateAttribute($model, 'i18n');
        $this->assertSame('The value for language \"en\" can not be empty.', $model->getFirstError('i18n'));
    }

    public function testSkipIfUnchanged()
    {
        $fixture = $this->createAdminLangFixture([
            1 => [
                'id' => 1,
                'short_code' => 'en',
                'is_deleted' => 0,
            ]
        ]);

        $model = $fixture->newModel;

        $validator = new I18nRequiredValidator();
        $validator->skipIfUnchanged = true;
        $this->assertNull($validator->validateAttribute($model, 'short_code'));
        $this->assertEmpty($model->getFirstError('i18n'));
    }
}
