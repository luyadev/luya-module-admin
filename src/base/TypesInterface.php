<?php

namespace luya\admin\base;

/**
 * TypesInterface represents all possible types for properties or blocks.
 *
 * The zaa types array are angular directives in order to build forms.
 *
 * + If a type contain `-array` the return is an array with selections (array)
 * + Otherwise only 1 value can be returned (scalar)
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
interface TypesInterface
{
    /**
     * @var string Type text represents a single row input field.
     */
    public const TYPE_TEXT = 'zaa-text';

    /**
     * @var string Type textarea represents a multi row textarea input field.
     */
    public const TYPE_TEXTAREA = 'zaa-textarea';

    /**
     * @var string Type password redpresents an input field which hiddes the input with type password.
     */
    public const TYPE_PASSWORD = 'zaa-password';

    /**
     * @var string
     */
    public const TYPE_NUMBER = 'zaa-number';

    /**
     * @var string
     */
    public const TYPE_DECIMAL = 'zaa-decimal';

    /**
     * @var string The example out for the link type:
     *
     * ```php
     * ['type' => 2, 'value' => 'https://luya.io']
     * ```
     */
    public const TYPE_LINK = 'zaa-link';

    /**
     * @var string Generates a Color-Wheel Input.
     */
    public const TYPE_COLOR = 'zaa-color';

    /**
     * @var string
     */
    public const TYPE_WYSIWYG = 'zaa-wysiwyg';

    /**
     * @var string Retuns the selected value from the options array. Where key is what will be stored and returned and value
     * will be display in the admin interfaces dropdown.
     */
    public const TYPE_SELECT = 'zaa-select';

    /**
     * @var string Returns a list of radio inputs based on an options array, but allows only a selection of one. Therefore its
     * **not** `radios` or `radio-array` as the return value is not an array.
     */
    public const TYPE_RADIO = 'zaa-radio';

    /**
     * @var string
     */
    public const TYPE_DATE = 'zaa-date';

    /**
     * @var string
     */
    public const TYPE_DATETIME = 'zaa-datetime';

    /**
     * @var string The directive returns an array of tag ids assigned to the model.
     * @since 2.2.1
     */
    public const TYPE_TAG_ARRAY = 'zaa-tag-array';

    /**
     * @var integer If value is set (checkbox is checked) `1` will return otherwise `0`.
     */
    public const TYPE_CHECKBOX = 'zaa-checkbox';

    /**
     * @var string
     */
    public const TYPE_CHECKBOX_ARRAY = 'zaa-checkbox-array';

    /**
     * @var string
     */
    public const TYPE_FILEUPLOAD = 'zaa-file-upload';

    /**
     * @var string
     */
    public const TYPE_FILEUPLOAD_ARRAY = 'zaa-file-array-upload';

    /**
     * @var string
     */
    public const TYPE_IMAGEUPLOAD = 'zaa-image-upload';

    /**
     * @var string
     */
    public const TYPE_IMAGEUPLOAD_ARRAY = 'zaa-image-array-upload';

    /**
     * @var string The arrayable json output would be:
     *
     * ```php
     * [['value' => 1], ['value' => 2]]]
     * ```
     */
    public const TYPE_LIST_ARRAY = 'zaa-list-array';

    /**
     * @var string Generates a table view similar to a json input.
     */
    public const TYPE_TABLE = 'zaa-table';

    /**
     * @var string Generates a selection of all cms page, works only if cms module is present.
     */
    public const TYPE_CMS_PAGE = 'zaa-cms-page';

    /**
     * @var string Generates a slugified input field which removes not valid url "link" chars like whitespaces.
     */
    public const TYPE_SLUG = 'zaa-slug';

    /**
     * @var string Create an expandable list with plugins for each row.
     *
     * ```php
     * ['var' => 'people', 'label' => 'People', 'type' => self::TYPE_MULTIPLE_INPUTS, 'options' =>
     *     [
     *          [
     *              'type' => self::TYPE_SELECT,
     *              'var' => 'salutation',
     *              'label' => 'Salutation',
     *              'options' => [
     *                  ['value' => 1, 'label' => 'Mr.'],
     *                  ['value' => 2, 'label' => 'Mrs.'],
     *              ]
     *          ], [
     *              'type' => self::TYPE_TEXT,
     *              'var' => 'name',
     *              'label' => 'Name'
     *          ]
     *      ]
     * ]
     * ```
     */
    public const TYPE_MULTIPLE_INPUTS = 'zaa-multiple-inputs';

    /**
     * @var string Create a dynamic form input based on Angular Directives.
     * @since 1.2.3
     */
    public const TYPE_INJECTOR = 'zaa-injector';

    /**
     * @var string Generates a multi selection and sortable list and returns a json array with the selected values.
     */
    public const TYPE_SORT_RELATION_ARRAY = 'zaa-sort-relation-array';

    /**
     * @var string Generates a field which is going to lookup the value for a given id with an xhr request to an API.
     */
    public const TYPE_ASYNC_VALUE = 'zaa-async-value';

    /**
     * @var string A read only attribute field which just displayes the ng model value.
     * @since 1.2.1
     */
    public const TYPE_READONLY = 'zaa-readonly';

    /**
     * @var string A a flat json object creating directive lets you define key and value and store as object instead of array.
     * @since 2.0.3
     */
    public const TYPE_JSON_OBJECT = 'zaa-json-object';

    /**
     * @var string A select based on an existing CRUD, therefore the route to the controller and the api must be declared.
     * ```
     * 'options' => ['route' => '<module>/<controller>/index', 'api' => 'admin/<api-endpoint-name>', 'fields' => ['title']]]
     * ```
     * @since 3.7.0
     */
    public const TYPE_SELECT_CRUD = 'zaa-select-crud';
}
