<?php

namespace luya\admin\helpers;

use luya\admin\base\TypesInterface;
use luya\helpers\StringHelper;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\helpers\Inflector;

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
     * Type Cast values.
     *
     * This is important as the angularjs admin is required to have integer values for boolean values,
     * since pgsql does return boolean values we have to type cast those value, this is the purpose of
     * this function.
     *
     * @param mixed $value
     * @return mixed
     * @since 4.0.0
     */
    public static function typeCast($value)
    {
        if (is_bool($value)) {
            $value = (int) $value;
        }

        return $value;
    }

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
     * @return AngularObject:
     */
    protected static function injector($type, $ngModel, $label, $options = [], array $mergeOptions = [])
    {
        // parse boolean values to integer values is it would not bind the values correctly to the angular directive.
        foreach ($mergeOptions as $key => $value) {
            if (!is_array($value) && is_bool($value)) {
                $mergeOptions[$key] = (int) $value;
            }
        }

        $opt = array_merge($mergeOptions, [
            'model' => $ngModel,
            'label' => $label,
            'options' => empty($options) ? null : $options,
            'fieldid' => Inflector::camel2id(Inflector::camelize($ngModel.'-'.$type)),
            'fieldname' => Inflector::camel2id(Inflector::camelize($ngModel)),
        ]);

        return new AngularObject([
            'type' => $type,
            'options' => $opt,
        ]);
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
     * Filter empty values like `null`, `''` and false but keep 0, as its common to use 0 as default value.
     *
     * @param array $array Filter empty values from array, but keep integer values.
     * @return array An array with filtered values.
     * @since 2.0.1
     */
    public static function optionsFilter(array $array)
    {
        return array_filter($array, function ($value) {
            if (is_null($value) || $value === '' || $value === false) {
                return false;
            }

            return true;
        });
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
     * Generate the LUYA admin scheduler tag.
     *
     * ```php
     * Angular::schedule('data.is_online', 'Online Status', 1, [0 => 'No', 1 => 'Yes'], 'path/to/model', 'is_online');
     * ```
     *
     * @param string $ngModel The angular model to read the data from.
     * @param string $label
     * @param string $primaryKeyValue The primary key value like `1` or `1,3` for composite keys
     * @param array $values An array with values to schedule.
     * @param string $modelClass The full class path of the model implementing NgRestModelInterface.
     * @param string $attributeName The name of the attribute inside the model to change the value. This is commonly the same as $ngModel.
     * @return AngularObject
     * @since 2.0.3
     */
    public static function schedule($ngModel, $label, $primaryKeyValue, array $values, $modelClass, $attributeName, array $options = [])
    {
        return new AngularObject([
            'type' => 'luya-schedule',
            'options' => array_merge([
                'value' => $ngModel,
                'model-class' => $modelClass,
                'title' => $label,
                'attribute-name' => $attributeName,
                'attribute-values' => Angular::optionsArrayInput($values),
                'primary-key-value' => $primaryKeyValue,
            ], $options)
        ]);
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
     * @return AngularObject
     */
    public static function sortRelationArray($ngModel, $label, array $sourceData, array $options = [])
    {
        return self::injector(TypesInterface::TYPE_SORT_RELATION_ARRAY, $ngModel, $label, ['sourceData' => static::optionsArrayInput($sourceData)], $options);
    }

    /**
     * Generate a directive which assignes an array of selected tag ids to the model.
     *
     * @param string $ngModel The name of the ng model which should be used for data binding.
     * @param string $label The label to display for the form input.
     * @param array $options An array with optional properties for the tag creation, where key is the property name and value its content.
     * @return AngularObject
     * @since 2.2.1
     */
    public static function tagArray($ngModel, $label, array $options = [])
    {
        return self::injector(TypesInterface::TYPE_TAG_ARRAY, $ngModel, $label, [], $options);
    }

    /**
     * zaaText directive
     *
     * @param string $ngModel The name of the ng model which should be used for data binding.
     * @param string $label The label to display for the form input.
     * @param array $options An array with optional properties for the tag creation, where key is the property name and value its content.
     * + placeholder: Optionally add placeholder value to text inputs.
     * @return AngularObject
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
     * @return AngularObject
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
     * @return AngularObject
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
     * @return AngularObject
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
     * @return AngularObject
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
     * @param array|string $data If a string is given it will be taken as two-way bind condition, otherwise an array input will be correctly convereted.
     * @param array $options An array with optional properties for the tag creation, where key is the property name and value its content.
     * @return AngularObject
     */
    public static function select($ngModel, $label, $data, array $options = [])
    {
        return self::injector(TypesInterface::TYPE_SELECT, $ngModel, $label, is_scalar($data) ? $data : self::optionsArrayInput($data), $options);
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
     * @param array|string $data If a string is given it will be taken as two way binding value. An array with data where the array key is what is stored in the model e.g. `[1 => 'Mrs', 2 => 'Mr']`
     * @param array $options Additonal arguments to create the tag.
     * @return AngularObject
     */
    public static function radio($ngModel, $label, array $data, array $options = [])
    {
        return self::injector(TypesInterface::TYPE_RADIO, $ngModel, $label, is_scalar($data) ? $data : self::optionsArrayInput($data), $options);
    }

    /**
     * zaaCheckbox directive
     *
     * @param string $ngModel The name of the ng model which should be used for data binding.
     * @param string $label The label to display for the form input.
     * @param array $options An array with optional properties for the tag creation, where key is the property name and value its content.
     * @return AngularObject
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
     * @param array|string $data If a string is given it will be taken as two-way bind condition, otherwise an array input will be correctly convereted.
     * @param array $options An array with optional properties for the tag creation, where key is the property name and value its content.
     * + preselect: If true all entires from the checkbox will be preselect by default whether its update or add.
     * @return AngularObject
     */
    public static function checkboxArray($ngModel, $label, $data, array $options = [])
    {
        return self::injector(TypesInterface::TYPE_CHECKBOX_ARRAY, $ngModel, $label, ['items' => is_scalar($data) ? $data : self::optionsArrayInput($data)], $options);
    }

    /**
     * zaaDate directive
     *
     * @param string $ngModel The name of the ng model which should be used for data binding.
     * @param string $label The label to display for the form input.
     * @param array $options An array with optional properties for the tag creation, where key is the property name and value its content.
     * + resetable: boolean, Whether the date can be reseted to null or not.
     * @return AngularObject
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
     * @return AngularObject
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
     * @return AngularObject
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
     * @return AngularObject
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
     * @return AngularObject
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
     * @return AngularObject
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
     * @param boolean $noFilter Whether the user can select a filter not, if not the original is taken.
     * @return AngularObject
     */
    public static function imageUpload($ngModel, $label, array $options = [], $noFilter = false)
    {
        return self::injector(TypesInterface::TYPE_IMAGEUPLOAD, $ngModel, $label, ['no_filter' => (int) $noFilter], $options);
    }

    /**
     * zaaFileUpload directive
     *
     * @param string $ngModel The name of the ng model which should be used for data binding.
     * @param string $label The label to display for the form input.
     * @param array $options An array with optional properties for the tag creation, where key is the property name and value its content.
     * @return AngularObject
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
     * @return AngularObject
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
     * @return AngularObject
     */
    public static function readonly($ngModel, $label, array $options = [])
    {
        return self::injector(TypesInterface::TYPE_READONLY, $ngModel, $label, [], $options);
    }

    /**
      * Extract the context attribute name from the ngModel and replace with given $field name.
      *
      * If an empty field value is provided no content will be returned.
      *
      * @param string $ngModel Context like `data.create.fieldname` or `data.update.fieldname`.
      * @param string $field The new field name to replace with the context field name.
      * @param boolean $angularJsVariable Whether the output should be enclosed in curly brackets or not {{}}
      * @return string Returns the string with the name field name like `data.create.$field`.
      * @since 4.0.0
      */
    public static function replaceFieldFromNgModelContext($ngModel, $field, $angularJsVariable = false)
    {
        if (empty($field)) {
            return;
        }

        // get all keys
        $parts = explode(".", $ngModel);
        $key = array_key_last($parts);
        // old last $field name
        $oldField = $parts[$key];
        if (StringHelper::endsWith($oldField, ']')) {
            // its an i18n field which has ['en'] suffix, we should extra this and add to $field
            if (preg_match('/\[.*\]/', $oldField, $matches) === 1) {
                $field .= $matches[0];
            }
        }

        // replace the last key with the new fieldname
        $parts[$key] = $field;

        $variable = implode(".", $parts);

        return $angularJsVariable ? '{{'.$variable.'}}' : $variable;
    }


    /**
     * The given string will be variablized and prefixed with current condition.
     *
     * For example when you like to access an attribute you can use the variable name in curly
     * braces. This will ensure the correct angularjs context value  will be taken.
     *
     * For example:
     *
     * ```
     * admin/api-admin-user/search?query={firstname}
     * ```
     *
     * This will replace `{firstname}` by `data.create.firstname` or if enabled it will be `{{data.create.firstname}}`
     *
     * @param string $ngModel
     * @param string $string
     * @param boolean $angularJsVariable Whether the output should be enclosed in curly brackets or not {{}}
     * @return string
     * @since 4.0.0
     */
    public static function variablizeContext($ngModel, $string, $angularJsVariable)
    {
        if (!$string) {
            return '';
        }

        preg_match_all('/{(.*?)}/', $string, $matches, PREG_SET_ORDER);
        $search = [];
        $replace = [];
        foreach ($matches as $match) {
            $search[] = $match[0];
            $replace[] = self::replaceFieldFromNgModelContext($ngModel, $match[1], $angularJsVariable);
        }

        return str_replace($search, $replace, $string);
    }
}
