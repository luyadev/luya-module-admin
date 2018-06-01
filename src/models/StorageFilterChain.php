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
 * @property int $id
 * @property int $sort_index
 * @property int $filter_id
 * @property int $effect_id
 * @property string $effect_json_values
 * @property \luya\admin\models\StorageEffect $effect
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
final class StorageFilterChain extends ActiveRecord
{
    /**
     * @var array An array containing all effect definitions with the options and required params.
     */
    protected $effectDefinitions = [
        'crop' => [
            'required' => ['width', 'height'],
            'options' => ['start' => [0, 0], 'saveOptions' => []],
        ],
        'thumbnail' => [
            'required' => ['width', 'height'],
            'options' => ['mode' => ManipulatorInterface::THUMBNAIL_OUTBOUND, 'saveOptions' => []]
        ],
        'watermark' => [], // TODO: provide watermark definition
        'text' => [], // TODO: provide text definition
        'frame' => [], // TODO: provide frame definition
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
            [['sort_index', 'filter_id', 'effect_id'], 'integer'],
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
            throw new InvalidConfigException('The requested effect mode ' . $imagineEffectName . ' is not supported');
        }
        
        if ($this->hasMissingRequiredEffectDefinition($imagineEffectName)) {
            throw new InvalidConfigException("The requested effect mode does require some parameters which are not provided.");
        }
        
        switch ($imagineEffectName) {
            
            // apply crop effect
            case FilterInterface::EFFECT_CROP:
                // run imagine crop method
                $image = Image::crop($loadFromPath, $this->effectChainValue($imagineEffectName, 'width'), $this->effectChainValue($imagineEffectName, 'height'));
                // try to auto rotate based on exif data
                Image::autoRotate($image)->save($imageSavePath, $this->effectChainValue($imagineEffectName, 'saveOptions'));
                break;
                
            // apply thumbnail effect
            case FilterInterface::EFFECT_THUMBNAIL:
                // run imagine thumbnail method
                $image = Image::thumbnail($loadFromPath, $this->effectChainValue($imagineEffectName, 'width'), $this->effectChainValue($imagineEffectName, 'height'), $this->effectChainValue($imagineEffectName, 'mode'));
                // try to auto rotate based on exif data
                Image::autoRotate($image)->save($imageSavePath, $this->effectChainValue($imagineEffectName, 'saveOptions'));
                break;
        }
    }

    /**
     * Get the definition for a given effect name.
     *
     * @param string $effect
     * @param string $key
     * @return mixed
     * @since 1.2.0
     */
    public function effectDefinition($effect, $key = null)
    {
        $definition = isset($this->effectDefinitions[$effect]) ? $this->effectDefinitions[$effect] : false;
        
        if (!$definition) {
            return false;
        }
        
        if ($key) {
            return $definition[$key];
        }
        
        return $definition;
    }
    
    /**
     * Check if a missing required param is not provided in the chain.
     *
     * @param string $effect
     * @return boolean
     * @since 1.2.0
     */
    public function hasMissingRequiredEffectDefinition($effect)
    {
        foreach ($this->effectDefinition($effect, 'required') as $param) {
            if ($this->getJsonValue($param) === false) {
                return true;
            }
        }

        return false;
    }
    
    /**
     * Get the value from the effect chain for a given param/option.
     *
     * @param string $effect
     * @param string $param
     * @return mixed
     * @since 1.2.0
     */
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
    
    /**
     * Get the value for a effect json key.
     *
     * @param string $key
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
}
