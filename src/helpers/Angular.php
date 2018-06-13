<?php

namespace luya\admin\helpers;

use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\base\InvalidConfigException;
use luya\admin\base\TypesInterface;

/**
 * Helper Method to create angular tags.
 *
 * The LUYA admin provides some default angular directives which are prefixed with `zaa`. In order to create custom
 * NgRest Plugins sometimes you may want to reuse those. There is also a helper method called `directive` which
 * allows you the quickly generate a Html Tag for directives.
 *
 * If the method has the suffix `array` it means the assigned model will contain an array of values. So
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class Angular
{
    /**
     * Internal method to use to create the angular injector helper method like in angular context of directives.js
     *
     * ```
     * "dir": "=",
     * "model": "=",
     * "options": "=",
     * "label": "@label",
     * "grid": "@grid",
     * "fieldid": "@fieldid",
     * "fieldname": "@fieldname",
     * "placeholder": "@placeholder",
     * "initvalue": "@initvalue"
     * ```
     *
     * @param string $type
     * @param string $ngModel
     * @param string $label
     * @param array $options The options parameter is mostly a data provider for the directive an is depending on the type.
     * @param array $mergeOptions Additonal attributes to be set for the tag $type.
     * + fieldid:
     * + fieldname:
     * + placeholder:
     * + initvalue:
     * @return string:
     */
    protected static function injector($type, $ngModel, $label, $options = [], array $mergeOptions = [])
    {
        // parse boolean values to integer values is it would not bind the values correctly to the angular directive.
        foreach ($mergeOptions as $key => $value) {
            if (!is_array($value) && is_bool($value)) {
                $mergeOptions[$key] = (int) $value;
            }
        }
        return static::directive($type, array_merge($mergeOptions, [
            'model' => $ngModel,
            'label' => $label,
            'options' => $options,
            'fieldid' => Inflector::camel2id(Inflector::camelize($ngModel.'-'.$type)),
            'fieldname' => Inflector::camel2id(Inflector::camelize($ngModel)),
        ]));
    }
    
    /**
     * Ensures the input structure for optional data for selects, radios etc.
     *
     * Following options are possible:
     *
     * + An array with key => values.
     * + An array with a nested array ['label' => , 'value' => ] format.
     * + A string, this can be used when two way data binding should be used instead of array genertion
     *
     * @param array $data|string Key value Paring or an array with label and value key.
     * @return array
     */
    public static function optionsArrayInput($data)
    {
        // seems to be a two way data binind, thefore direct return the string and do not transform.
        if (is_scalar($data)) {
            return $data;
        }
        
        $output = [];
        
        foreach ($data as $value => $label) {
            if (is_array($label)) {
                if (!isset($label['label']) || !isset($label['value'])) {
                    throw new InvalidConfigException("The options array data for the given element must contain a label and value key.");
                }
                
                $output[] = $label;
            } else {
                $output[] = ['label' => $label, 'value' => $value];
            }
        }
        
        return $output;
    }

    /**
     * Create a Angular Directive tag based on the name.
     *
     * ```php
     * Angular::directive('my-input', 'name');
     * ```
     *
     * would produce the my input directive tag:
     *
     * ```html
     * <my-input ng-model="name"></my-input>
     * ```
     *
     * @param string $name The name for the generated direcitve tag which will be converted from camelcase to id notation.
     * @param array $options An array with optional properties for the tag creation, where key is the property name and value its content.
     * @return string
     */
    public static function directive($name, array $options = [])
    {
        return Html::tag(Inflector::camel2id($name), null, $options);
    }
    
    /**
     * Sort Relation Array.
     *
     * Generates a multi selection and sortable list and returns a json array with the selected values.
     *
     * @param string $ngModel The name of the ng model which should be used for data binding.
     * @param string $label The label to display for the form input.
     * @param array $sourceData
     * @param array $options An array with optional properties for the tag creation, where key is the property name and value its content.
     * @return string
     */
    public static function sortRelationArray($ngModel, $label, array $sourceData, array $options = [])
    {
        return self::injector(TypesInterface::TYPE_SORT_RELATION_ARRAY, $ngModel, $label, ['sourceData' => $sourceData], $options);
    }
        
    /**
     * zaaText directive
     *
     * @param string $ngModel The name of the ng model which should be used for data binding.
     * @param string $label The label to display for the form input.
     * @param array $options An array with optional properties for the tag creation, where key is the property name and value its content.
     * + placeholder: Optionally add placeholder value to text inputs.
     * @return string
     */
    public static function text($ngModel, $label, array $options = [])
    {
        return self::injector(TypesInterface::TYPE_TEXT, $ngModel, $label, [], $options);
    }
    
    /**
     * Passwort
     * @param string $ngModel The ng-model attribute which should be used as reference to be bound into the element.
     * @param string $label The label which should be displayed for the given field.
     * @param array $options An array with optional properties for the tag creation, where key is the property name and value its content.
     * @return \luya\admin\helpers\string:
     */
    public static function password($ngModel, $label, array $options = [])
    {
        return self::injector(TypesInterface::TYPE_PASSWORD, $ngModel, $label, [], $options);
    }
    
    /**
     * zaaTextarea directive
     *
     * @param string $ngModel The name of the ng model which should be used for data binding.
     * @param string $label The label to display for the form input.
     * @param array $options An array with optional properties for the tag creation, where key is the property name and value its content.
     * @return string
     */
    public static function textarea($ngModel, $label, array $options = [])
    {
        return self::injector(TypesInterface::TYPE_TEXTAREA, $ngModel, $label, [], $options);
    }
    
    /**
     * zaaNumber directive
     *
     * @param string $ngModel The name of the ng model which should be used for data binding.
     * @param string $label The label to display for the form input.
     * @param array $options An array with optional properties for the tag creation, where key is the property name and value its content.
     * @return string
     */
    public static function number($ngModel, $label, array $options = [])
    {
        return self::injector(TypesInterface::TYPE_NUMBER, $ngModel, $label, [], $options);
    }
    
    /**
     * zaaDecimal directive
     *
     * @param string $ngModel The name of the ng model which should be used for data binding.
     * @param string $label The label to display for the form input.
     * @param array $options An array with optional properties for the tag creation, where key is the property name and value its content.
     * @return string
     */
    public static function decimal($ngModel, $label, array $options = [])
    {
        return self::injector(TypesInterface::TYPE_DECIMAL, $ngModel, $label, [], $options);
    }
    
    /**
     * Select directive
     *
     * ```php
     * return AngularInput::zaaSelect($ngModel, $this->alias, [['value' => 123, 'label' => 123123]]);
     * ```
     *
     * @param string $ngModel The name of the ng model which should be used for data binding.
     * @param string $label The label to display for the form input.
     * @param array $data
     * @param array $options An array with optional properties for the tag creation, where key is the property name and value its content.
     * @return string
     */
    public static function select($ngModel, $label, array $data, array $options = [])
    {
        return self::injector(TypesInterface::TYPE_SELECT, $ngModel, $label, self::optionsArrayInput($data), $options);
    }
    
    /**
     * Radio Input.
     *
     * Generate a list of radios where you can select only one element.
     *
     * Example:
     *
     * ```php
     * Angular::radio('exportType', 'Format', ['csv' => 'CSV', 'xlss' => 'XLSX'])
     * ```
     *
     * @param string $ngModel The name of the ng model which should be used for data binding.
     * @param string $label The label to display for the form input.
     * @param array $data An array with data where the array key is what is stored in the model e.g. `[1 => 'Mrs', 2 => 'Mr']`
     * @param array $options Additonal arguments to create the tag.
     * @return \luya\admin\helpers\string:
     */
    public static function radio($ngModel, $label, array $data, array $options = [])
    {
        return self::injector(TypesInterface::TYPE_RADIO, $ngModel, $label, self::optionsArrayInput($data), $options);
    }
    
    /**
     * zaaCheckbox directive
     *
     * @param string $ngModel The name of the ng model which should be used for data binding.
     * @param string $label The label to display for the form input.
     * @param array $options An array with optional properties for the tag creation, where key is the property name and value its content.
     * @return string
     */
    public static function checkbox($ngModel, $label, array $options = [])
    {
        return self::injector(TypesInterface::TYPE_CHECKBOX, $ngModel, $label, [], $options);
    }
    
    /**
     * zaaCheckboxArray directive
     *
     * ```php
     * AngularInput::zaaCheckboxArray($ngModel, $this->alias, ['123' => 'OneTwoTrhee', 'foo' => 'Bar']);
     * ```
     *
     * If you like to build your custom angular directive to use two way binding without items data you can use something like this:
     *
     * ```php
     * Angular::directive('zaa-checkbox-array', $ngModel, ['options' => $this->getServiceName('myDataService')]);
     * ```
     *
     * But make sure the service you call returns the data within ['items' => $data].
     *
     * @param string $ngModel The name of the ng model which should be used for data binding.
     * @param string $label The label to display for the form input.
     * @param array $data
     * @param array $options An array with optional properties for the tag creation, where key is the property name and value its content.
     * + preselect: If true all entires from the checkbox will be preselect by default whether its update or add.
     * @return string
     */
    public static function checkboxArray($ngModel, $label, array $data, array $options = [])
    {
        return self::injector(TypesInterface::TYPE_CHECKBOX_ARRAY, $ngModel, $label, ['items' => self::optionsArrayInput($data)], $options);
    }
    
    /**
     * zaaDate directive
     *
     * @param string $ngModel The name of the ng model which should be used for data binding.
     * @param string $label The label to display for the form input.
     * @param array $options An array with optional properties for the tag creation, where key is the property name and value its content.
     * + resetable: boolean, Whether the date can be reseted to null or not.
     * @return string
     */
    public static function date($ngModel, $label, array $options = [])
    {
        return self::injector(TypesInterface::TYPE_DATE, $ngModel, $label, [], $options);
    }
    
    /**
     * zaaDatetime directive
     *
     * @param string $ngModel The name of the ng model which should be used for data binding.
     * @param string $label The label to display for the form input.
     * @param array $options An array with optional properties for the tag creation, where key is the property name and value its content.
     * + resetable: boolean, Whether the datetime can be reseted to null or not.
     * @return string
     */
    public static function datetime($ngModel, $label, array $options = [])
    {
        return self::injector(TypesInterface::TYPE_DATETIME, $ngModel, $label, [], $options);
    }
    
    /**
     * zaaTable directive
     *
     * @param string $ngModel The name of the ng model which should be used for data binding.
     * @param string $label The label to display for the form input.
     * @param array $options An array with optional properties for the tag creation, where key is the property name and value its content.
     * @return string
     */
    public static function table($ngModel, $label, array $options = [])
    {
        return self::injector(TypesInterface::TYPE_TABLE, $ngModel, $label, [], $options);
    }
    
    /**
     * zaaListArray directive
     *
     * @param string $ngModel The name of the ng model which should be used for data binding.
     * @param string $label The label to display for the form input.
     * @param array $options An array with optional properties for the tag creation, where key is the property name and value its content.
     * @return string
     */
    public static function listArray($ngModel, $label, array $options = [])
    {
        return self::injector(TypesInterface::TYPE_LIST_ARRAY, $ngModel, $label, [], $options);
    }
    
    /**
     * zaaFileArrayUpload directive
     *
     * @param string $ngModel The name of the ng model which should be used for data binding.
     * @param string $label The label to display for the form input.
     * @param array $options An array with optional properties for the tag creation, where key is the property name and value its content.
     * @return string
     */
    public static function fileArrayUpload($ngModel, $label, array $options = [])
    {
        return self::injector(TypesInterface::TYPE_FILEUPLOAD_ARRAY, $ngModel, $label, [], $options);
    }
    
    /**
     * zaaImageArrayUpload directive
     *
     * @param string $ngModel The name of the ng model which should be used for data binding.
     * @param string $label The label to display for the form input.
     * @param array $options An array with optional properties for the tag creation, where key is the property name and value its content.
     * @return string
     */
    public static function imageArrayUpload($ngModel, $label, array $options = [])
    {
        return self::injector(TypesInterface::TYPE_IMAGEUPLOAD_ARRAY, $ngModel, $label, [], $options);
    }
    
    /**
     * zaaImageUpload directive
     *
     * @param string $ngModel The name of the ng model which should be used for data binding.
     * @param string $label The label to display for the form input.
     * @param array $options An array with optional properties for the tag creation, where key is the property name and value its content.
     * @return string
     */
    public static function imageUpload($ngModel, $label, array $options = [])
    {
        return self::injector(TypesInterface::TYPE_IMAGEUPLOAD, $ngModel, $label, [], $options);
    }
    
    /**
     * zaaFileUpload directive
     *
     * @param string $ngModel The name of the ng model which should be used for data binding.
     * @param string $label The label to display for the form input.
     * @param array $options An array with optional properties for the tag creation, where key is the property name and value its content.
     * @return string
     */
    public static function fileUpload($ngModel, $label, array $options = [])
    {
        return self::injector(TypesInterface::TYPE_FILEUPLOAD, $ngModel, $label, [], $options);
    }
    
    /**
     * Generates a directive which requies a value from an api where the model is the primary key field of the api.
     * Angular code example
     *
     * ```js
     * zaa-async-value model="theModel" label="Hello world" api="admin/api-admin-users" fields="[foo,bar]" />
     * ```
     *
     * @param string $ngModel The name of the ng model which should be used for data binding.
     * @param string $label The label to display for the form input.
     * @param string $api The path to the api endpoint like `admin/api-admin-users`.
     * @param array $fields An array with all fiels which should be requested from the api
     * @param array $options
     * @since 1.2.1
     */
    public static function asyncRequest($ngModel, $label, $api, $fields, array $options = [])
    {
        return self::injector(TYpesInterface::TYPE_ASYNC_VALUE, $ngModel, $label, [], ['api' => $api, 'fields' => $fields]);
    }
    
    /**
     * Generate a read only attribute tag.
     *
     * @param string $ngModel The name of the ng model which should be used for data binding.
     * @param string $label The label to display for the form input.
     * @param array $options An array with optional properties for the tag creation, where key is the property name and value its content.
     * @since 1.2.1
     */
    public static function readonly($ngModel, $label, array $options = [])
    {
        return self::injector(TypesInterface::TYPE_READONLY, $ngModel, $label, [], $options);
    }
}
