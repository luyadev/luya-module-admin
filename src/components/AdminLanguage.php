<?php

namespace luya\admin\components;

use Yii;
use yii\base\Component;
use luya\admin\models\Lang;
use luya\traits\CacheableTrait;
use luya\helpers\ArrayHelper;

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
    const CACHE_KEY_QUERY_ALL = 'adminLanguageCacheKey';

    /**
     * Containg the default language assoc array.
     *
     * @var array
     */
    private $_activeLanguage;
    
    /**
     * Containg all availabe languages from Lang Model.
     *
     * @var array
     */
    private $_languages;
    
    /**
     * Get the array of the current active language (its not an AR object!)
     *
     * Determines active language by:
     *
     * 1. langShortCode of composite component
     * 2. language which has is_default flag enabled.
     *
     * @return array
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
        }
        
        return $this->_activeLanguage;
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
            $this->_languages = $this->getOrSetHasCache(self::CACHE_KEY_QUERY_ALL, function () {
                return Lang::getQuery();
            });
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
        return isset($this->getLanguages()[$shortCode]) ? $this->getLanguages()[$shortCode] : false;
    }

    /**
     * Clear the cache data for admin language
     *
     * @return boolean whether clearing was successfull or not.
     * @since 2.1.0
     */
    public function clearCache()
    {
        return $this->deleteHasCache(self::CACHE_KEY_QUERY_ALL);
    }
}
