<?php

namespace luya\admin\validators;

use luya\admin\models\Lang;
use luya\admin\Module;
use luya\helpers\Json;
use yii\db\BaseActiveRecord;
use yii\validators\Validator;

/**
 * Validate Required i18n Attributes.
 *
 * Ensure the i18n attribute is correctly and also checks if all language keys are given and the values for each language is not empty.
 *
 * Example usage:
 *
 * ```php
 * [['title', 'location'], I18nRequiredValidator::class],
 * ```
 *
 * In {{luya\admin\ngrest\base\NgRestModel}} rules() method it might be common to use:
 *
 * ```php
 * [$this->i18n, I18nRequiredValidator::class],
 * ```
 *
 * The validator will only validate if the attribute is available, therefore set the required validator if the attribute is required.
 *
 * @author Basil Suter <git@nadar.io>
 * @since 3.6.0
 */
class I18nRequiredValidator extends Validator
{
    /**
     * @var string Message if the input format is invalid. This message will be passed trough Yii::t.
     */
    public $invalidFormatMessage = "i18n_required_validator_invalid_format";

    /**
     * @var string Message if an language key does not exists in the array. This message will be passed trough Yii::t.
     */
    public $missingKeyMessage = "i18n_required_validator_missing_key";

    /**
     * @var string Message if a given value is empty. This message will be passed trough Yii::t.
     */
    public $emptyValueMessage = "i18n_required_validator_invalid_empty_value";

    /**
     * @var boolean If enabled and the attribute value has not changed (not dirty) the validation will be skipped.
     */
    public $skipIfUnchanged = false;

    /**
     * {@inheritDoc}
     */
    public function validateAttribute($model, $attribute)
    {
        // if skip if unchanged is enabled and active record and the attribute has not changed, skip this validation rule.
        if ($this->skipIfUnchanged && $model instanceof BaseActiveRecord) {
            if (!$model->isAttributeChanged($attribute)) {
                return;
            }
        }

        $array = $model->{$attribute};

        // As due to the ngrest plugin concept the value is already parsed from array to json.
        if (Json::isJson($array)) {
            $array = Json::decode($array);
        }

        if (!is_array($array)) {
            return $this->addError($model, $attribute, Module::t($this->invalidFormatMessage, ['attribute' => $attribute]));
        }

        /** @var $langShortCode The language short code */
        foreach (Lang::find()->select(['short_code'])->asArray()->column() as $langShortCode) {
            if (!array_key_exists($langShortCode, $array)) {
                $this->addError($model, $attribute, Module::t($this->missingKeyMessage, ['lang' => $langShortCode]));
            } elseif (empty($array[$langShortCode])) {
                $this->addError($model, $attribute, Module::t($this->emptyValueMessage, ['lang' => $langShortCode]));
            }
        }
    }
}
