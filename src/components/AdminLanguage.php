<?php

namespace luya\admin\components;

use luya\admin\models\Lang;
use luya\Exception;
use luya\helpers\ArrayHelper;
use luya\traits\CacheableTrait;
use Yii;
use yii\base\Component;

/**
 * Admin Language Component.
 *
 * The component is registered by the admin module and provides methods to collect language data.
 *
 * @property string $getLanguageByShortCode Get the language from a shortCode..
 * @property array $languages Get an array of all languages (its not an AR object!).
 * @property integer $activeId Get the current active language ID.
 * @property string $activeShortCode Get the current active langauge Short-Code.
 * @property array $activeLanguage Get the array of the current active language (its not an AR object!).
 * @property array $defaultLanguage The admin_lang is_default=1 table entry.
 * @property string $defaultLanguageShortCode The short code from the admin_lang is_default=1 item.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class AdminLanguage extends Component
{
    use CacheableTrait;

    /**
     * @var callable A callable which can be configured in order to define where to take the default language short code from
     * as this has changed from version 3.0.x to 3.1. The active language is recieved trough Yii::$app->language since
     * version 3.1. In order to restore the old behavior use:
     *
     * ```php
     * 'activeShortCodeCallable' => function($adminLanguageObject) {
     *     return Yii::$app->composition->langShortCode;
     * }
     * ```
     * @since 3.1.0
     */
    public $activeShortCodeCallable;

    /**
     * @var string The cache key name
     * @since 2.1.0
     */
    public const CACHE_KEY_QUERY_ALL = 'adminLanguageCacheKey';

    private $_activeLanguage;

    /**
     * Get the array of the current active language (it's not an AR object!)
     *
     * Determines active language by:
     *
     * 1. langShortCode of composite component
     * 2. language which has is_default flag enabled.
     *
     * @return array
     * @throws Exception
     */
    public function getActiveLanguage()
    {
        if ($this->_activeLanguage === null) {
            if ($this->activeShortCodeCallable && is_callable($this->activeShortCodeCallable)) {
                $langShortCode = call_user_func($this->activeShortCodeCallable, $this);
            } else {
                $langShortCode = Yii::$app->language;
            }

            // find the current language for the composition lang short code
            if ($langShortCode) {
                $this->_activeLanguage = ArrayHelper::searchColumn($this->getLanguages(), 'short_code', $langShortCode);
            }

            // if $langShortCode is empty (_activeLanguage is still null) or searchColumn returns false, the default language will be taken.
            if (empty($this->_activeLanguage)) {
                $this->_activeLanguage = ArrayHelper::searchColumn($this->getLanguages(), 'is_default', 1);
            }

            // if _activeLanguage is still empty, then the system does not have a default language
            if (empty($this->_activeLanguage)) {
                throw new Exception("The system must have a default language set.");
            }
        }

        return $this->_activeLanguage;
    }

    /**
     * Returns the admin default language.
     *
     * This represents the default language of the admin `admin_lang` table with is_default=1 flag.
     *
     * @return array|boolean If default language is not defiend false is returned.
     * @since 3.1
     */
    public function getDefaultLanguage()
    {
        return ArrayHelper::searchColumn($this->getLanguages(), 'is_default', 1);
    }

    /**
     * Returns the default short code from the admin active language table.
     *
     * @return string
     * @since 3.1
     */
    public function getDefaultLanguageShortCode()
    {
        return $this->getDefaultLanguage()['short_code'];
    }

    /**
     * Get the current active langauge Short-Code
     *
     * @return string
     */
    public function getActiveShortCode()
    {
        return $this->getActiveLanguage()['short_code'];
    }

    /**
     * Get the current active language ID
     *
     * @return int
     */
    public function getActiveId()
    {
        return (int) $this->getActiveLanguage()['id'];
    }

    private $_languages;

    /**
     * Get an array of all languages (its not an AR object!)
     *
     * An Example response.
     *
     * array(
     *    'de' => ['name' => Deutsch', 'short_code' => 'de', 'is_default' => 1],
     *    'en' => ['name' => English', 'short_code' => 'en', 'is_default' => 0],
     * )
     *
     * @return array An array with languages indexed by the short code of the language.
     */
    public function getLanguages()
    {
        if ($this->_languages === null) {
            $this->_languages = $this->getOrSetHasCache(self::CACHE_KEY_QUERY_ALL, fn () => Lang::find()
                ->where(['is_deleted' => false])
                ->indexBy('short_code')
                ->orderBy(['is_default' => SORT_DESC])
                ->asArray()
                ->all());
        }

        return $this->_languages;
    }

    /**
     * Get the language from a shortCode.
     *
     * @param string $shortCode
     * @return boolean|mixed
     */
    public function getLanguageByShortCode($shortCode)
    {
        return $this->getLanguages()[$shortCode] ?? false;
    }

    /**
     * Clear the cache data for admin language
     *
     * @return boolean whether clearing was successful or not.
     * @since 2.1.0
     */
    public function clearCache()
    {
        $this->_languages = null;
        $this->_activeLanguage = null;
        return $this->deleteHasCache(self::CACHE_KEY_QUERY_ALL);
    }
}
