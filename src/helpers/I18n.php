<?php

namespace luya\admin\helpers;

use Yii;
use yii\base\InvalidArgumentException;
use yii\helpers\Json;

/**
 * I18n Encode/Decode helper method
 *
 * General infos about the provided methods:
 *
 * + `decode`: Means the input must be raw json code which will be decoded.
 * + `array` suffix: Input must be an array of values and output will be an array of values.
 * + `findActive`: The value for a given language is returned, if no language is provided the admin ui language is used.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class I18n
{
    /**
     * Encode from PHP to Json
     *
     * @param string|array $value The value to encode from php to json.
     * @return string Returns the json encoded string.
     */
    public static function encode($value)
    {
        return is_array($value) ? Json::encode($value) : $value;
    }

    /**
     * Decode from Json to PHP
     *
     * @param string|array $value The value to decode from json to php.
     * @param string $onEmptyValue Defines the value if the language could not be found and a value will be returns, this value will be used.
     * @return array Return the decoded php value.
     */
    public static function decode($value, $onEmptyValue = '')
    {
        $languages = Yii::$app->adminLanguage->getLanguages();

        // if its not already unserialized, decode it
        if (!is_array($value) && !empty($value)) {
            try {
                $value = Json::decode($value);
            } catch (InvalidArgumentException $e) {
                $value = [];
            }
        }

        // if value is empty, we create an empty array
        if (empty($value)) {
            $value = [];
        }

        // fall back for not transformed values
        if (!is_array($value)) {
            $value = (array) $value;
        }

        // add all not existing languages to the array (for example a language has been added after the database item has been created)
        foreach ($languages as $lang) {
            if (!array_key_exists($lang['short_code'], $value)) {
                $value[$lang['short_code']] = $onEmptyValue;
            } elseif (empty($value[$lang['short_code']])) {
                $value[$lang['short_code']] = $onEmptyValue;
            }
        }

        return $value;
    }

    /**
     * Decode an array with i18n values.
     *
     * In order to decode an arry with json values you can use this function istead of iterator trough
     * the array items by yourself and calling {{luya\admin\helpers\I18n::decode}}.
     *
     * ```php
     * $array = ['{"de:"Hallo","en":"Hello"}', '{"de:"Ja","en":"Yes"}'];
     *
     * $decoded = I18n::decodeArray($array);
     *
     * print_r($decoded); // dump: array(['de' => 'Hallo', 'en' => 'Hello'], ['de' => 'Ja', 'en' => 'Yes']);
     * ```
     *
     * @param array $array The array to iterate trough and call the {{luya\admin\helpers\I18n::decode}} for each value.
     * @param string $onEmptyValue If the decoded value is not existing or empty, this default value will be used instead of null.
     * @return array
     */
    public static function decodeArray(array $array, $onEmptyValue = '')
    {
        $decoded = [];
        foreach ($array as $key => $value) {
            $decoded[$key] = static::decode($value, $onEmptyValue);
        }

        return $decoded;
    }

    /**
     * Decodes a json string and returns the current active language item.
     *
     * ```php
     * // assume the default language is `en`
     * $output = I18n::decodeFindActive('{"de":"Hallo","en":"Hello"}');
     *
     * echo $output; // output is "Hello"
     * ```
     *
     * @param string $input The json string
     * @param string $onEmptyValue If element is not found, this value is returned instead.
     * @param string $lang The language to return, if no lang is provided, the language resolved trough the admin ui (or user language) is used by default.
     * @return string The value from the json for the current active language or if not found the value from onEmptyValue.
     */
    public static function decodeFindActive($input, $onEmptyValue = '', $lang = null)
    {
        return static::findActive(static::decode($input, $onEmptyValue), $onEmptyValue, $lang);
    }

    /**
     * Decodes an array with json strings and returns the current active language item for each entry.
     *
     * ```php
     * // assume the default language is `en`
     * $output = I18n::decodeFindActiveArray(['{"de":"Hallo","en":"Hello"}'], ['{"de":"Katze","en":"Cat"}']);
     *
     * var_dump($output); // dump: array('Hello', 'Cat')
     * ```
     *
     * @param array $input
     * @param mixed $onEmptyValue The value to use when the requested language could not be found.
     * @param string $lang The language to return, if no lang is provided, the language resolved trough the admin ui (or user language) is used by default.
     * @return array
     */
    public static function decodeFindActiveArray(array $input, $onEmptyValue = '', $lang = null)
    {
        return static::findActiveArray(static::decodeArray($input, $onEmptyValue), $onEmptyValue, $lang);
    }

    /**
     * Find the corresponding element inside an array for the current active language.
     *
     * ```php
     * // assume the default language is `en`
     * $output = I18n::findActive(['de' => 'Hallo', 'en' => 'Hello']);
     *
     * echo $output; // output is "Hello"
     * ```
     *
     * @param array $fieldValues The array you want to to find the current
     * @param mixed $onEmptyValue The value you can set when the language could not be found
     * @param string $lang The language to return, if no lang is provided, the language resolved trough the admin ui (or user language) is used by default.
     * @return mixed
     */
    public static function findActive(array $fieldValues, $onEmptyValue = '', $lang = null)
    {
        $langShortCode = $lang ?: Yii::$app->adminLanguage->getActiveShortCode();

        return array_key_exists($langShortCode, $fieldValues) ? $fieldValues[$langShortCode] : $onEmptyValue;
    }

    /**
     * Find the corresponding element inside an array for the current active language
     *
     * ```php
     * // assume the default language is `en`
     * $output = I18n::findActiveArray([
     *     ['de' => 'Hallo', 'en' => 'Hello'],
     *     ['de' => 'Katze', 'en' => 'Cat'],
     * ]);
     *
     * var_dump($output); // dump: array('Hello', 'Cat')
     * ```
     *
     * @param array $fieldValues The array you want to to find the current
     * @param mixed $onEmptyValue The value you can set when the language could not be found.
     * @param string $lang The language to return, if no lang is provided, the language resolved trough the admin ui (or user language) is used by default.
     * @return array
     */
    public static function findActiveArray(array $array, $onEmptyValue = '', $lang = null)
    {
        $output = [];
        foreach ($array as $key => $value) {
            $output[$key] = static::findActive($value, $onEmptyValue, $lang);
        }

        return $output;
    }
}
