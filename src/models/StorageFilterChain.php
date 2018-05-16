<?php

namespace luya\admin\models;

use yii\helpers\Json;
use yii\base\InvalidConfigException;
use yii\imagine\Image;
use yii\db\ActiveRecord;
use luya\admin\base\FilterInterface;
use Imagine\Image\ManipulatorInterface;

/**
 * Contains all information about filter effects for a single Chain element (like: thumbnail, 200x200).
 *
 * @property \luya\admin\models\StorageEffect $effect
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
final class StorageFilterChain extends ActiveRecord
{
    protected $effectDefinitions = [
        'crop' => [
            'required' => ['width', 'height'],
            'options' => ['start' => [0, 0], 'saveOptions' => []],
        ],
        'thumbnail' => [
            'required' => ['width', 'height'],
            'options' => ['mode' => ManipulatorInterface::THUMBNAIL_OUTBOUND, 'saveOptions' => []]
        ],
        'watermark' => [
            // TODO: provide watermark definition
        ],
        'text' => [
            // TODO: provide text definition
        ],
        'frame' => [
            // TODO: provide frame definition
        ]
    ];
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'admin_storage_filter_chain';
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->on(self::EVENT_BEFORE_VALIDATE, [$this, 'eventBeforeValidate']);
        $this->on(self::EVENT_AFTER_FIND, [$this, 'eventAfterFind']);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['filter_id', 'effect_id'], 'required'],
            [['effect_json_values'], 'safe'],
        ];
    }

    /**
     * Encode the the effect_json_values array to a json structure.
     */
    public function eventBeforeValidate()
    {
        if (is_array($this->effect_json_values)) {
            $this->effect_json_values = Json::encode($this->effect_json_values);
        }
    }

    /**
     * Decode the json structure into a php array.
     */
    public function eventAfterFind()
    {
        $this->effect_json_values = Json::decode($this->effect_json_values);
    }

    /**
     * StorageEffect Active Query.
     *
     * @return \luya\admin\models\StorageEffect
     */
    public function getEffect()
    {
        return $this->hasOne(StorageEffect::class, ['id' => 'effect_id']);
    }
    
    /**
     * Load an image from a given path, apply all effects (filters) from effect_json_values and save the file.
     * 
     * @param string $loadFromPath The absolute path to the existing file.
     * @param string $imageSavePath The absolute path to the new location where the file should be stroed.
     * @throws InvalidConfigException
     */
    public function applyFilter($loadFromPath, $imageSavePath)
    {
        $imagineEffectName = $this->effect->getImagineEffectName();
        
        if (!$this->effectDefinition($imagineEffectName)) {
            throw new InvalidConfigException('The requested effect mode ' . $this->effect->imagine_name . ' is not supported');
        }
        
        if ($this->hasMissingRequiredEffectDefinition($imagineEffectName)) {
            throw new InvalidConfigException("The requested effect mode does require some parameters which are not provided.");
        }
        
        switch ($imagineEffectName) {
            
            // apply crop effect
            case FilterInterface::EFFECT_CROP:
                // run imagine crop method
                $image = Image::crop($loadFromPath, $this->effectChainValue('width'), $this->effectChainValue('height'));
                // try to auto rotate based on exif data
                Image::autoRotate($image)->save($imageSavePath, $this->effectChainValue('saveOptions'));
                break;
                
            // apply thumbnail effect
            case FilterInterface::EFFECT_THUMBNAIL:
                // run imagine thumbnail method
                $image = Image::thumbnail($loadFromPath, $this->effectChainValue('width'), $this->effectChainValue('height'), $this->effectChainValue('mode'));
                // try to auto rotate based on exif data
                Image::autoRotate($image)->save($imageSavePath, $this->effectChainValue('saveOptions'));
                
                break;
        }
    }

    
    public function effectDefinition($effect, $key = null)
    {
        $definition = isset($this->effectDefinitions[$effect]) ? $this->effectDefinitions[$effect] : false;
        
        if (!$definition) {
            return false;
        }
        
        if ($key) {
            return $definition[$key];
        }
        
        return $key;
    }
    
    public function hasMissingRequiredEffectDefinition($effect)
    {
        foreach ($this->effectDefinition($effect, 'required') as $param) {
            if ($this->getJsonValue($param) === false) {
                return true;
            }
        }

        return false;
    }
    
    public function effectChainValue($effect, $param)
    {
        $value = $this->getJsonValue($param);
        
        // if there is no value defined, we used the default defintion from options
        if ($value === false) {
            $options = $this->effectDefinition($effect, 'options');
            $value = array_key_exists($param, $options) ? $options[$param] : false;
        }
        
        return $value;
    }
    
    /*
    protected function evalMethod()
    {
        return isset($this->effectDefinitions[$this->effect->imagine_name]) ? $this->effectDefinitions[$this->effect->imagine_name] : false;
    }
    */
    
    /*
    protected function evalRequiredMethodParams()
    {
        foreach ($this->evalMethod()['required'] as $param) {
            if ($this->getJsonValue($param) === false) {
                return false;
            }
        }
        
        return true;
    }
    */
    
    /**
     * Get the value for a effect json key.
     * 
     * @param unknown $key
     * @return boolean
     */
    protected function getJsonValue($key)
    {
        return array_key_exists($key, $this->effect_json_values) ? $this->effect_json_values[$key] : false;
    }
    
    /**
     * Get the value from an effect for a given param.
     * 
     * If the the is no value, try to return the defaultValue from options definition.
     * 
     * @param string $name
     * @return string|array|number[]|boolean
     */
    /*
    protected function getMethodParam($name)
    {
        $value = $this->getJsonValue($name);
        
        if ($value === false) {
            if (isset($this->evalMethod()['options'][$name])) {
                return $this->evalMethod()['options'][$name];
            }
        }
        
        return $value;
    }
    */
}
