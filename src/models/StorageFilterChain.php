<?php

namespace luya\admin\models;

use Imagine\Image\ImageInterface;
use Imagine\Image\ManipulatorInterface;
use luya\admin\base\FilterInterface;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\helpers\Json;
use yii\imagine\Image;

/**
 * Contains all information about filter effects for a single Chain element (like: thumbnail, 200x200).
 *
 * @property int $id
 * @property int $sort_index
 * @property int $filter_id
 * @property int $effect_id
 * @property array $effect_json_values
 * @property StorageEffect $effect
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
final class StorageFilterChain extends ActiveRecord
{
    /**
     * @var array An array containing all effect definitions with the options and required params.
     */
    protected array $effectDefinitions = [
        'crop' => [
            'required' => ['width', 'height'],
            'options' => ['start' => [0, 0], 'saveOptions' => []],
        ],
        'thumbnail' => [
            'required' => ['width', 'height'],
            'options' => ['mode' => ManipulatorInterface::THUMBNAIL_OUTBOUND, 'saveOptions' => []]
        ],
        'watermark' => [
            'required' => ['image'],
            'options' => ['start' => [0, 0]],
        ],
        'text' => [
            'required' => ['text', 'fontFile'],
            'options' => ['start' => [0, 0]],
        ],
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admin_storage_filter_chain}}';
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
     * Encode the effect_json_values array to a json structure.
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
     * Apply the current filter chain to the given Image Instance.
     *
     * @param ImageInterface $image The image instance to apply the filter.
     * @param array $saveOptions The saving options passed from previous steps.
     * @return array An array with two elements, the first returns the manipulated image object, the second the new or existing $savingOptions.
     * @since 3.2.0 The method signature has changed see UPGRADE.md
     * @throws InvalidConfigException
     */
    public function applyFilter(ImageInterface $image, array $saveOptions)
    {
        gc_collect_cycles();

        $imagineEffectName = $this->effect->getImagineEffectName();

        if (!$this->effectDefinition($imagineEffectName)) {
            throw new InvalidConfigException('The requested effect mode ' . $imagineEffectName . ' is not supported');
        }

        if ($this->hasMissingRequiredEffectDefinition($imagineEffectName)) {
            throw new InvalidConfigException("The requested effect \"$imagineEffectName\" require some parameters which are not provided.");
        }

        if ($imagineEffectName == FilterInterface::EFFECT_CROP) {
            // crop
            $image = Image::crop($image, $this->effectChainValue($imagineEffectName, 'width'), $this->effectChainValue($imagineEffectName, 'height'));
            return [$image, $this->effectChainValue($imagineEffectName, 'saveOptions')];
        } elseif ($imagineEffectName == FilterInterface::EFFECT_THUMBNAIL) {
            // thumbnail
            $image = Image::thumbnail($image, $this->effectChainValue($imagineEffectName, 'width'), $this->effectChainValue($imagineEffectName, 'height'), $this->effectChainValue($imagineEffectName, 'mode') | ImageInterface::THUMBNAIL_FLAG_NOCLONE);
            return [$image, $this->effectChainValue($imagineEffectName, 'saveOptions')];
        } elseif ($imagineEffectName == FilterInterface::EFFECT_WATERMARK) {
            // watermark
            $image = Image::watermark($image, $this->effectChainValue($imagineEffectName, 'image'), $this->effectChainValue($imagineEffectName, 'start'));
            return [$image, $saveOptions];
        } elseif ($imagineEffectName == FilterInterface::EFFECT_TEXT) {
            // text
            $image = Image::text($image, $this->effectChainValue($imagineEffectName, 'text'), $this->effectChainValue($imagineEffectName, 'fontFile'), $this->effectChainValue($imagineEffectName, 'start'));
            return [$image, $saveOptions];
        }

        throw new InvalidConfigException("Missing effect.");
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
        $definition = $this->effectDefinitions[$effect] ?? false;

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

        // if there is no value defined, we used the default definition from options
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
     * @return boolean|mixed If existing the value is returned
     */
    protected function getJsonValue($key)
    {
        return array_key_exists($key, $this->effect_json_values) ? $this->effect_json_values[$key] : false;
    }
}
