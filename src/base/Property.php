<?php

namespace luya\admin\base;

use luya\admin\helpers\I18n;
use luya\helpers\Json;
use yii\base\Component;

/**
 * Abstract Page Property Class.
 *
 * Each property must implement this class. Reade more in the Guide [[app-cmsproperties.md]].
 *
 * Example integration requires it least {{varName()}}, {{label()}} and {{type()}}.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
abstract class Property extends Component implements TypesInterface
{
    /**
     * @var string The name of the event will be triggered before rendering. Triggers an {{luya\cms\frontend\events\BeforeRenderEvent}} event.
     */
    public const EVENT_BEFORE_RENDER = 'EVENT_BEFORE_RENDER';

    /**
     * @var string The module where the property is located.
     */
    public $moduleName;

    /**
     * @var mixed The value from the database assigned into the property object.
     */
    public $value;

    /**
     * @var boolean Whether the property is used for an i18n use case or not, this will
     * serialize the input as json into the database and the getValue/getAdminValue methods will
     * automatically unserialize the correctly value.
     */
    public $i18n = false;

    /**
     * @var boolean Whether a json object value should be auto decoded when retrieving data via {{Property::getValue()}} and
     * {{Property::getAdminValue()}}. This is used when storing json data like link type.
     * @since 1.0.3
     */
    public $autoDecodeJson = true;

    /**
     * The internal variable name for this property.
     *
     * This is like a variable name identifer, this should be unique value across all properties. Allowed
     * chars are `a-zA-Z0-9-_`. The defined variable named will be use when retrieving data from a property
     * in the frontend. For example `Yii::$app->menu->current->getProperty('varName`)` where varName is the
     * varaiable name you choosen as return value of this method.
     *
     * Example:
     *
     * ```php
     * public function varName()
     * {
     *     return 'myVariable';
     * }
     * ```
     *
     * @return string
     */
    abstract public function varName();

    /**
     * The label which is displayed in the administration area.
     *
     * Example:
     *
     * ```php
     * public function label()
     * {
     *     return 'My Variable';
     * }
     * ```
     *
     * @return string
     */
    abstract public function label();

    /**
     * The specifation of what type this property is.
     *
     * There are different types of variables/propertys to create. Sometimes its
     * just a plain text field, textarea or and image or multip image upload. Therefore
     * the method `type()` defines what should be created. All types are available als
     * constants inside the {{\luya\admin\base\TypesInterface}}.
     *
     * Example:
     *
     * ```php
     * public function type()
     * {
     *     return self::TYPE_SELECT;
     * }
     * ```
     *
     * @see \luya\admin\base\TypesInterface
     * @return string
     */
    abstract public function type();

    /**
     * {@inheritDoc}
     */
    final public function __construct($config = [])
    {
        parent::__construct($config);
    }

    /**
     * When the object is force to return as string the `getValue()` method is returned.
     *
     * @return mixed
     */
    public function __toString()
    {
        return empty($this->getValue()) ? '' : $this->getValue();
    }

    /**
     * Options you may have to pass to the selected type.
     *
     * Sometimes the type of property requires more informations and optional data
     * those datas needs to be returned. Example of options to return when using
     * the TYPE_SELECT property type:
     *
     * ```php
     * public function options()
     * {
     *     return [
     *         ['value' => 'ul', 'label' => 'Pointed List'],
     *         ['value' => 'ol', 'label' => 'Nummeic List'],
     *     ];
     * }
     * ```
     *
     * @return mixed
     */
    public function options()
    {
        return [];
    }

    /**
     * Return a help text which will be display along with the attribute.
     *
     * @return string
     * @since 3.8.0
     */
    public function help()
    {
        return null;
    }

    /**
     * If the property is requested in admin context and there is no value
     * the `defaultValue()` value response will be used.
     *
     * For example a preselecting item from a list select dropdown:
     *
     * ```php
     * public function defaultValue()
     * {
     *     return 'default';
     * }
     * ```
     *
     * @return mixed
     */
    public function defaultValue()
    {
        return false;
    }

    /**
     * This value is used to determine the administration interface value to render the
     * angular directive "model" values.
     *
     * @return mixed
     */
    public function getAdminValue()
    {
        if ($this->i18n) {
            $this->value = I18n::decode($this->value);
        } elseif ($this->autoDecodeJson && Json::isJson($this->value)) {
            $this->value = Json::decode($this->value);
        }

        return $this->value;
    }

    /**
     * This is what will be returned when the property is requested in the frontend.
     *
     * You can override this function in order to provide your own output logic.
     *
     * Make sure to call the parent implementation of getValue when overriding this function in
     * order to make sure the usage of i18n variables:
     *
     * ```php
     * public function getValue()
     * {
     *     $value = parent::getValue();
     *     // do something with value and return
     *     return Yii::$app->storage->getImage($value);
     * }
     * ```
     *
     * @return mixed The value stored in the database for this property.
     */
    public function getValue()
    {
        if ($this->i18n) {
            $this->value = I18n::decode($this->value);
        } elseif ($this->autoDecodeJson && Json::isJson($this->value)) {
            $this->value = Json::decode($this->value);
        }

        return $this->value;
    }

    /**
     * Returns the identifier of the property.
     *
     * This allows a more dynamic approach of embed property by using the class name instead of
     * the hardcoded var name.
     *
     * ```php
     * Yii::$app->cms->current->getProperty(YourProperty::identifier())->getValue();
     * ```
     *
     * @return string The {{Property::varName()}} wil be retunred.
     * @since 2.1.0
     */
    public static function identifier()
    {
        return (new static())->varName();
    }
}
