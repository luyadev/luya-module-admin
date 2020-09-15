<?php

namespace luya\admin\validators;

use luya\admin\models\Lang;
use luya\admin\Module;
use luya\helpers\Json;
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
 * @author Basil Suter <git@nadar.io>
 * @since 3.6.0
 */
class I18nRequiredValidator extends Validator
{
    public $invalidFormatMessage = "i18n_required_validator_invalid_format";

    public $missingKeyMessage = "i18n_required_validator_missing_key";

    public $emptyValueMessage = "i18n_required_validator_invalid_empty_value";

    /**
     * {@inheritDoc}
     */
    public function validateAttribute($model, $attribute)
    {
        $array = $model->{$attribute};

        if (Json::isJson($array)) {
            $array = Json::decode($array);
        }

        if (!is_array($array)) {
            return $this->addError($model, $attribute, Module::t($this->invalidFormatMessage, ['attribute' => $attribute]));
        }

        foreach (Lang::find()->select(['short_code'])->asArray()->column() as $langShortCode) {
            if (!array_key_exists($langShortCode, $array)) {
                $this->addError($model, $attribute, Module::t($this->missingKeyMessage,  ['lang' => $langShortCode]));
            } elseif (empty($array[$langShortCode])) {
                $this->addError($model, $attribute, Module::t($this->emptyValueMessage, ['lang' => $langShortCode]));
            }
        }
    }
}