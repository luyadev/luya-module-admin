<?php

namespace luyas\admin\validators;

use luya\admin\models\Lang;
use luya\helpers\Json;
use yii\validators\Validator;

/**
 * Validate Required i18n Attributes.
 * 
 * Ensure the i18n attribute is correctly sent to the api and also checks if all language keys are given and NOT EMPTY.
 * 
 * @author Basil Suter <git@nadar.io>
 * @since 1.0.0
 */
class I18nRequiredValidator extends Validator
{
    public function validateAttribute($model, $attribute)
    {
        $array = $model->{$attribute};

        if (Json::isJson($array)) {
            $array = Json::decode($array);
        }

        if (!is_array($array)) {
            return $this->addError($model, $attribute, "The given attribute {attribute} must be type of array.", ['attribute' => $attribute]);
        }

        foreach (Lang::find()->select(['short_code'])->asArray()->column() as $langShortCode) {
            if (!array_key_exists($langShortCode, $array)) {
                $this->addError($model, $attribute, "The language key \"{lang}\" is missing and is required.",  ['lang' => $langShortCode]);
            } elseif (empty($array[$langShortCode])) {
                $this->addError($model, $attribute, "The value for language \"{lang}\" can not be empty.", ['lang' => $langShortCode]);
            }
        }
    }

}