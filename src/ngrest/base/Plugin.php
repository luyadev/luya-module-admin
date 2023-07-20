<?php

namespace luya\admin\ngrest\base;

use luya\admin\base\TypesInterface;
use luya\admin\helpers\Angular;
use luya\admin\helpers\I18n;
use luya\Exception;
use luya\helpers\ArrayHelper;
use Yii;
use yii\base\Component;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 * Base class for NgRest Plugins.
 *
 * + renderList, renderCreate, renderUpdate are execute once when the crud template is generated
 * + the on* methods are triggered for every model on the given events.
 *
 * Event trigger cycle for different use cases, the following use cases are available with its
 * event cycles.
 *
 * Async:
 * + onCollectServiceData: A collection bag where you can provide data and access them trough angular, its not available inside the same model as this process runs async
 *
 * Data foreach:
 * + onFind: The model is used in your controller frontend logic to display and assign data into the view (developer use case)
 * + onListFind: The model is populated for the Admin Table list view where you can see all your items and click the edit/delete icons.
 * + onExpandFind: Equals to onFind but only for the view api of the model, which means the data which is used for edit.
 * + onSave: Before Update / Create of the new data set.
 *
 * @property string|array $sortField Sort field definition (since 2.0.0)
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
abstract class Plugin extends Component implements TypesInterface
{
    public const CREATE_CONTEXT_PREFIX = 'create.';

    public const UPDATE_CONTEXT_RPEFXI = 'update.';

    public const LIST_CONTEXT_PREFIX = 'item.';

    /**
     * @var string The name of the field corresponding to the ActiveRecord (also known as fieldname)
     */
    public $name;

    /**
     * @var string The alias name of the plugin choosen by the user (also known as label)
     */
    public $alias;

    /**
     * @var boolean Whether the plugin is in i18n context or not.
     */
    public $i18n = false;

    /**
     * @var boolean Whether the the value is only readable in EDIT scope or not. If enabled the data is displayed in the edit form as value instead
     * of the edit form. This has no effect to the create scope ony to the edit scope. Internally when enabled, the plugin will use {{renderList()}} in
     * update context instead of {{renderUpdate()}}.
     * @since 3.0.0
     */
    public $readonly = false;

    /**
     * @var boolean Whether this column should be hidden in the list. If the column is hidden in the list the data will be loaded from the api and can
     * be used by other fields, but its not visible in list view.
     * @since 2.0.0
     */
    public $hideInList = false;

    /**
     * @var mixed This value will be used when the i18n decodes the given value but is not set yet, default value.
     */
    public $i18nEmptyValue = '';

    /**
     * @var string Provide a condition in order to show or hide a given field. The condition relies on other fields from the forms. In order
     * to make sure the right context is used (create, update) put the fieldname into curly brackets `{field1}`.
     *
     * ```php
     * 'myText' => 'text',
     * 'otherText' => ['text', 'condition' => "{myText}"], // which is equals to when {myText} is not empt display the `otherText` field.
     * ```
     *
     * The above example would hide the `otherText` elment until `myText` is not empty. The condition is inside the `ng-show` element and the field
     * must be declared inside `{}` this will return the field name based on the current context like `data.create.myText` or `data.update.myText`.
     *
     * + display when not empty: `{field}`
     * + display when empty: `!{field}`
     * + display when has a given value: `{field}==1` (could be used when field is a select with values).
     *
     * @since 1.2.0
     */
    public $condition;

    /**
     * @var \luya\admin\ngrest\render\RenderCrudInterface The render context object when rendering
     * + {{Plugin::renderList()}}
     * + {{Plugin::renderCreate()}}
     * + {{Plugin::renderUpdate()}}
     * @since 1.1.1
     */
    public $renderContext;

    /**
     * @var callable A callable which will return before the ngrest list view for a given attribute.
     *
     * The function annotation contains the value of the attribute and second the model (event sender argument).
     *
     * ```php
     * 'beforeListFind' => function($value, $model) {
     *    return I18n::decode($value)['en']; // returns always the english language key value.
     * }
     * ```
     *
     * Return return value of the callable function will be set as attribute value to display.
     *
     * > Keep in mind that the return value of the function won't be processed by any further events.
     * > For example the i18n property might not have any effect anymore even when {{$i18n}} is enabled.
     *
     * @since 2.2.2
     */
    public $beforeListFind;

    /**
     * @var mixed A background color of a CRUD table cell where plugin data is shown.
     *
     * The value can be any string with CSS valid color such as `'red'` or `'#35FD87'` or `'rgba (124, 255, 23, 0.2)'`.
     * Or the value can be a simple AngularJS expression e.g. `'colorData'` or `'gender ? "purple" : "blue"'` assuming `colorData` or `gender` is declared in {{luya\admin\ngrest\base\NgRestModel::ngRestScopes()}} list scope.
     * In the second case the cell color will be automatically changed when the corresponding attribute changes.
     *
     * ```php
     * 'cellColor' => 'gender ? "purple" : "blue"'
     * ```
     *
     * @see https://docs.angularjs.org/guide/expression
     * @since 4.1.0
     */
    public $cellColor = false;

    /**
     * @var mixed An additional icon of a CRUD table column and CRUD edit form item.
     *
     * The value is the icon name based on https://material.io/icons or false if no icon is specified.
     *
     * ```php
     * 'icon' => 'account_circle'
     * ```
     *
     * @since 4.2.0
     */
    public $icon = false;

    /**
     * Renders the element for the CRUD LIST overview for a specific type.
     *
     * @param string $id The ID of the element in the current context
     * @param string $ngModel The name to access the data in angular context.
     * @return string|array Returns the html element as a string or an array which will be concated
     */
    abstract public function renderList($id, $ngModel);

    /**
     * Renders the element for the CRUD CREATE FORM for a specific type.
     *
     * @param string $id The ID of the element in the current context
     * @param string $ngModel The name to access the data in angular context.
     * @return string|array Returns the html element as a string or an array which will be concated
     */
    abstract public function renderCreate($id, $ngModel);

    /**
     * Renders the element for the CRUD UPDATE FORM for a specific type.
     *
     * @param string $id The ID of the element in the current context
     * @param string $ngModel The name to access the data in angular context.
     * @return string|array Returns the html element as a string or an array which will be concated
     */
    abstract public function renderUpdate($id, $ngModel);

    /**
     * @inheritdoc
     */
    public function init()
    {
        // call parent initializer
        parent::init();

        if ($this->name === null || $this->alias === null || $this->i18n === null) {
            throw new Exception("Plugin attributes name, alias and i18n must be configured.");
        }

        $this->addEvent(NgRestModel::EVENT_BEFORE_VALIDATE, 'onSave');
        $this->addEvent(NgRestModel::EVENT_AFTER_INSERT, 'onAssign');
        $this->addEvent(NgRestModel::EVENT_AFTER_UPDATE, 'onAssign');
        $this->addEvent(NgRestModel::EVENT_AFTER_REFRESH, 'onAssign');
        $this->addEvent(NgRestModel::EVENT_AFTER_FIND, 'onFind');
        $this->addEvent(NgRestModel::EVENT_AFTER_NGREST_FIND, 'onListFind');
        $this->addEvent(NgRestModel::EVENT_AFTER_NGREST_UPDATE_FIND, 'onExpandFind');
        $this->addEvent(NgRestModel::EVENT_SERVICE_NGREST, 'onCollectServiceData');
    }

    private $_sortField;

    /**
     * Setter method for sortField
     *
     * @param string|array $field A sort field definition, this can be either a string `firstname` or an array with a definition or multiple definitions
     *
     * ```php
     * 'sortField' => [
     *     'asc' => ['fist_name' => SORT_ASC, 'last_name' => SORT_ASC],
     *     'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
     * ]
     * ```
     *
     * Or you have an ngrest attribute which does not exists in the database, so you can define the original sorting attribute:
     *
     * ```php
     * 'sortField' => 'field_name_inside_the_table'
     * ```
     *
     * which is equals to
     *
     * ```php
     * 'sortField' => [
     *    'asc' => ['field_name_inside_the_table' => SORT_ASC],
     *    'desc' => ['field_name_inside_the_table' => SORT_DESC],
     * ]
     * ```
     *
     * A very common scenario when define sortField is when display a value from a relation, therefore you need to prepare the data provider
     * inside the API in order to **join** the relation (joinWith(['relationName])) aftewards you can use the table name
     *
     * ```php
     * 'sortField' => [
     *      'asc' => ['city_table.name' => SORT_ASC],
     *      'desc' => ['city_table.name' => SORT_DESC],
     * ]
     * ```
     *
     * There are situations you might turn of the sorting for the given attribute therefore just sortfield to false:
     *
     * ```php
     * 'sortField' => false,
     * ```
     *
     * In order to enable order by counting of relation tables update the APIs {{luya\admin\ngrest\base\Api::prepareListQuery()}} with the given sub
     * select condition where the alias name is equal to the field to sort:
     *
     * ```php
     * public function prepareListQuery()
     * {
     *     return parent::prepareListQuery()->select(['*', '(SELECT count(*) FROM admin_tag_relation WHERE tag_id = id) as relationsCount']);
     * }
     * ```
     *
     * Where in the above example `relationsCount` would be the sortField name.
     *
     * @see https://www.yiiframework.com/doc/api/2.0/yii-data-sort#$attributes-detail
     * @since 2.0.0
     */
    public function setSortField($field)
    {
        $this->_sortField = $field;
    }

    /**
     * Getter method for a sortField definition.
     *
     * If no sortField definition has been set, the plugin attribute name is used.
     *
     * @return array
     * @since 2.0.0
     */
    public function getSortField()
    {
        if ($this->_sortField === false) {
            return [];
        }

        if ($this->_sortField) {
            //
            if (is_array($this->_sortField)) {
                return [$this->name => $this->_sortField];
            }

            return [$this->name => [
                'asc' => [$this->_sortField => SORT_ASC],
                'desc' => [$this->_sortField => SORT_DESC]
            ]];
        }

        return [$this->name];
    }

    /**
     * Return the defined constant for a angular service instance in the current object.
     *
     * @param string $name The name of the service defined as array key in `serviceData()`.
     * @return string
     */
    public function getServiceName($name)
    {
        return 'service.'.$this->name.'.'.$name;
    }

    /**
     * Define the service data which will be called when creating the ngrest crud view. You may override
     * this method in your plugin.
     *
     * Example Response
     *
     * ```php
     * return [
     *     'titles' => ['mr, 'mrs'];
     * ];
     * ```
     *
     * The above service data can be used when creating the tags with `$this->getServiceName('titles')`.
     *
     * @param \yii\base\Event $event The event sender which triggers the event.
     * @return boolean|array
     */
    public function serviceData($event)
    {
        return false;
    }

    /**
     * Decodes the given JSON string into a PHP data structure and verifys if its empty, cause this can thrown an json decode exception.
     *
     * @param string $value The string to decode from json to php.
     * @return array The PHP array.
     */
    public function jsonDecode($value)
    {
        return empty($value) ? [] : Json::decode($value);
    }

    // I18N HELPERS

    /**
     * Encode from PHP to Json.
     *
     * See {{luya\admin\helpers\I18n::encode}}
     *
     * @param string|array $value
     * @return string Returns a string
     * @deprecated Deprecated since version 3.1, will trigger an deprecated warning in 4.0, will be removed in version 5.0
     */
    public function i18nFieldEncode($value)
    {
        trigger_error('deprecated, use I18n::encode instead. Will be removed in version 5.0', E_USER_DEPRECATED);
        return I18n::encode($value);
    }

    /**
     * Decode from Json to PHP.
     *
     * See {{luya\admin\helpers\I18n::decode}}
     *
     * @param string|array $value The value to decode (or if alreay is an array already)
     * @param string $onEmptyValue Defines the value if the language could not be found and a value will be returns, this value will be used.
     * @return array returns an array with decoded field value
     * @deprecated Deprecated since version 3.1, will trigger an deprecated warning in 4.0, will be removed in version 5.0
     */
    public function i18nFieldDecode($value, $onEmptyValue = '')
    {
        trigger_error('deprecated, use I18n::decode instead. Will be removed in version 5.0', E_USER_DEPRECATED);
        return I18n::decode($value, $onEmptyValue);
    }

    /**
     * Encode the current value from a language array.
     *
     * See {{luya\admin\helpers\I18n::findActive}}
     *
     * @param array $fieldValues
     * @return string
     * @deprecated Deprecated since version 3.1, will trigger an deprecated warning in 4.0, will be removed in version 5.0
     */
    public function i18nDecodedGetActive(array $fieldValues)
    {
        trigger_error('deprecated, use I18n::findActive instead. Will be removed in version 5.0', E_USER_DEPRECATED);
        return I18n::findActive($fieldValues);
    }

    // HTML TAG HELPERS

    /**
     * Wrapper for Yii Html::tag method
     *
     * @param string $name The name of the tag
     * @param string $content The value inside the tag.
     * @param array $options Options to passed to the tag generator.
     * @return string The generated html string tag.
     */
    public function createTag($name, $content, array $options = [])
    {
        return Html::tag($name, $content, $options);
    }

    /**
     * Preprends the context name for a certain ng model.
     *
     * As the angular attributes have different names in different contexts, this will append the correct context.
     *
     * ```php
     * $this->appendFieldNgModelContext('myfieldname', self::LIST_CONTEXT_PREFIX);
     * ```
     *
     * @param string $field
     * @param string $context
     * @return string
     */
    protected function appendFieldNgModelContext($field, $context)
    {
        return $context . ltrim($field, '.');
    }

    /**
      * Get the ng-show condition from a given ngModel context.
      *
      * Evaluates the ng-show condition from a given ngModel context. A condition like
      * `{field} == true` would return `data.create.field == true`.
      *
      * @param string $ngModel The ngModel to get the context informations from.
      * @return string Returns the condition with replaced field context like `data.create.fieldname == 0`
      * @since 1.2.0
      */
    public function getNgShowCondition($ngModel)
    {
        return Angular::variablizeContext($ngModel, $this->condition, false);
    }

    /**
     * Helper method to create a form tag based on current object.
     *
     * @param string $name Name of the form tag.
     * @param string $id The id tag of the tag.
     * @param string $ngModel The ngrest model name of the tag.
     * @param array $options Options to passes to the tag creator.
     * @return string The generated tag content.
     */
    public function createFormTag($name, $id, $ngModel, array $options = [])
    {
        $defaultOptions = [
            'fieldid' => $id,
            'model' => $ngModel,
            'label' => $this->alias,
            'fieldname' => $this->name,
            'i18n' => $this->i18n ? 1 : '',
        ];

        // if a condition is available, evalute from given context
        if ($this->condition) {
            $defaultOptions['ng-show'] = $this->getNgShowCondition($ngModel);
        }

        return $this->createTag($name, null, array_merge($options, $defaultOptions));
    }

    /**
     * Helper method to create a span tag with the ng-model in angular context for the crud overview
     * @param string $ngModel
     * @param array $options An array with options to pass to the list tag
     * @return string
     */
    public function createListTag($ngModel, array $options = [])
    {
        return $this->createTag('span', null, ArrayHelper::merge(['ng-bind' => $ngModel], $options));
    }

    /**
     * Create a tag for relation window toggler with directive crudLoader based on a ngrest model class.
     *
     * @param NgRestModel $ngrestModelClass
     * @param string|null $ngRestModelSelectMode
     * @param array $options
     * @param boolean $propagatePoolContext
     * @return string
     */
    public function createCrudLoaderTag($ngrestModelClass, $ngRestModelSelectMode = null, array $options = [], $propagatePoolContext = false)
    {
        if (!method_exists($ngrestModelClass, 'ngRestApiEndpoint')) {
            return null;
        }

        $menu = Yii::$app->adminmenu->getApiDetail($ngrestModelClass::ngRestApiEndpoint(), $propagatePoolContext ? Yii::$app->request->get('pool') : false);

        if ($menu) {
            if ($ngRestModelSelectMode) {
                $options['model-setter'] = $ngRestModelSelectMode;
                $options['model-selection'] = 1;
            } else {
                $options['model-selection'] = 0;
            }

            return $this->createTag('crud-loader', null, array_merge(['api' => $menu['route'], 'alias' => $menu['alias']], $options));
        }

        return null;
    }

    /**
     * Create the Scheulder tag for a given field.
     *
     * The scheduler tag allows you to change the given field value based on input values for a given field if a model is ailable.
     *
     * @param string $ngModel The string to
     * @param array $values An array with values to display
     * @param string $dataRow The data row context (item)
     * @param array $options `only-icon` => 1
     * @return string
     * @since 2.0.0
     */
    public function createSchedulerListTag($ngModel, array $values, $dataRow, array $options = [])
    {
        return Angular::schedule($ngModel, $this->alias, 'getRowPrimaryValue('.$dataRow.')', $values, $this->renderContext->getModel()::class, $this->name, $options)->render();
    }

    // EVENTS

    private $_events = [];

    /**
     * Add an event to the list of events
     *
     * @param string $trigger ActiveRecord event name
     * @param string $handler Method-Name inside this object
     */
    public function addEvent($trigger, $handler)
    {
        $this->_events[$trigger] = $handler;
    }

    /**
     * Remove an event from the events stack by its trigger name.
     *
     * In order to remove an event trigger from stack you have to do this right
     * after the initializer.
     *
     * ```php
     * public function init()
     * {
     *     parent::init();
     *     $this->removeEvent(NgRestModel::EVENT_AFTER_FIND);
     * }
     * ```
     *
     * @param string $trigger The event trigger name from the EVENT constants.
     */
    public function removeEvent($trigger)
    {
        if (isset($this->_events[$trigger])) {
            unset($this->_events[$trigger]);
        }
    }

    /**
     * An override without calling the parent::events will stop all other events used by default.
     *
     * @return array
     */
    public function events()
    {
        return $this->_events;
    }

    // ON SAVE

    /**
     * This event will be triggered before `onSave` event.
     *
     * @param \yii\db\AfterSaveEvent $event AfterSaveEvent represents the information available in yii\db\ActiveRecord::EVENT_AFTER_INSERT and yii\db\ActiveRecord::EVENT_AFTER_UPDATE.
     * @return boolean
     */
    public function onBeforeSave($event)
    {
        return true;
    }

    /**
    * This event will be triggered `onSave` event. If the model property is not writeable the event will not trigger.
    *
    * If the beforeSave method returns true and i18n is enabled, the value will be json encoded.
    *
    * @param \yii\base\ModelEvent $event ModelEvent represents the information available in yii\db\ActiveRecord::EVENT_BEFORE_VALIDATE.
    * @return void
    */
    public function onSave($event)
    {
        if ($this->isAttributeWriteable($event) && $this->onBeforeSave($event)) {
            if ($this->i18n) {
                $event->sender->setAttribute($this->name, I18n::encode($event->sender->getAttribute($this->name)));
            }
        }
    }

    // ON ASSIGN

    /**
     * After attribute value assignment.
     *
     * This event will trigger on:
     *
     * + AfterInsert
     * + AfterUpdate
     * + AfterRefresh
     *
     * The main purpose is to ensure html encoding when the model is not populated with after find from the database.
     *
     * @param \yii\base\ModelEvent $event When the database entry after insert, after update, after refresh.
     * @since 1.1.1
     */
    public function onAssign($event)
    {
    }

    // ON LIST FIND

    /**
     * This event will be triger before `onListFind`.
     *
     * @param \luya\admin\ngrest\base\NgRestModel::EVENT_AFTER_NGREST_FIND $event The NgRestModel after ngrest find event.
     * @return boolean
     */
    public function onBeforeListFind($event)
    {
        if ($this->beforeListFind) {
            $this->writeAttribute($event, call_user_func_array($this->beforeListFind, [$this->getAttributeValue($event), $event->sender]));
            return false;
        }

        return true;
    }

    /**
     * This event is only trigger when returning the ngrest crud list data.
     *
     * If the attribute is not inside the model (property not writeable), the event will not be triggered. Ensure its a
     * public property or contains getter/setter methods.
     *
     * @param \luya\admin\ngrest\base\NgRestModel::EVENT_AFTER_NGREST_FIND $event The NgRestModel after ngrest find event.
     */
    public function onListFind($event)
    {
        if ($this->isAttributeWriteable($event) && $this->onBeforeListFind($event)) {
            if ($this->i18n) {
                $event->sender->setAttribute(
                    $this->name,
                    I18n::decodeFindActive($event->sender->getAttribute($this->name), $this->i18nEmptyValue, Yii::$app->adminLanguage->defaultLanguageShortCode)
                );
            }
            $this->onAfterListFind($event);
        }
    }

    /**
     * This event will be triggered after `onListFind`.
     *
     * @param \luya\admin\ngrest\base\NgRestModel::EVENT_AFTER_NGREST_FIND $event The NgRestModel after ngrest find event.
     * @return boolean
     */
    public function onAfterListFind($event)
    {
        return true;
    }

    // ON FIND

    /**
     * This event will be trigger before `onFind`.
     *
     * @param \yii\base\Event $event An event that is triggered after the record is created and populated with query result.
     * @return boolean
     */
    public function onBeforeFind($event)
    {
        return true;
    }

    /**
     * ActiveRecord afterFind event. If the property of this plugin inside the model, the event will not be triggered.
     *
     * @param \yii\base\Event $event An event that is triggered after the record is created and populated with query result.
     */
    public function onFind($event)
    {
        if ($this->isAttributeWriteable($event) && $this->onBeforeFind($event)) {
            if ($this->i18n) {
                $oldValue = $event->sender->getAttribute($this->name);
                $event->sender->setI18nOldValue($this->name, $oldValue);
                // get the new array value from an i18n json attribute
                $value = I18n::decodeFindActive($oldValue, $this->i18nEmptyValue);
                // set the new attribute value
                $event->sender->setAttribute($this->name, $value);
                // override the old attribute value in order to ensure this attribute is not marked as dirty
                // see: https://github.com/luyadev/luya-module-admin/pull/567
                $event->sender->setOldAttribute($this->name, $value);
            }

            $this->onAfterFind($event);
        }
    }

    /**
     * This event will be trigger after `onFind`.
     *
     * @param \yii\base\Event $event An event that is triggered after the record is created and populated with query result.
     * @return boolean
     */
    public function onAfterFind($event)
    {
        return true;
    }

    // ON EXPAND FIND

    /**
     * This event will be triggered before `onExpandFind`.
     *
     * @param \luya\admin\ngrest\base\NgRestModel::EVENT_AFTER_NGREST_UPDATE_FIND $event NgRestModel event EVENT_AFTER_NGREST_UPDATE_FIND.
     * @return boolean
     */
    public function onBeforeExpandFind($event)
    {
        return true;
    }

    /**
     * NgRest Model crud list/overview event after find. If the property of this plugin inside the model, the event will not be triggered.
     * @param \luya\admin\ngrest\base\NgRestModel::EVENT_AFTER_NGREST_UPDATE_FIND $event NgRestModel event EVENT_AFTER_NGREST_UPDATE_FIND.
     */
    public function onExpandFind($event)
    {
        if ($this->isAttributeWriteable($event) && $this->onBeforeExpandFind($event)) {
            if ($this->i18n) {
                $event->sender->setAttribute(
                    $this->name,
                    I18n::decode($event->sender->getAttribute($this->name), $this->i18nEmptyValue)
                );
            }

            $this->onAfterExpandFind($event);
        }
    }

    /**
     * This event will be triggered after `onExpandFind`.
     *
     * @param \luya\admin\ngrest\base\NgRestModel::EVENT_AFTER_NGREST_UPDATE_FIND $event NgRestModel event EVENT_AFTER_NGREST_UPDATE_FIND.
     * @return boolean
     */
    public function onAfterExpandFind($event)
    {
        return true;
    }

    // ON COLLECT SERVICE DATA

    /**
     * This event will be triggered before `onCollectServiceData`.
     *
     * @param \luya\admin\ngrest\base\NgRestModel::EVENT_SERVICE_NGREST $event NgRestModel event EVENT_SERVICE_NGREST.
     * @return boolean
     */
    public function onBeforeCollectServiceData($event)
    {
        return true;
    }

    /**
     * The ngrest services collector.
     *
     * > The service event is async to the other events, which means the service event collects data before the other events are called.
     *
     * @param \luya\admin\ngrest\base\NgRestModel::EVENT_SERVICE_NGREST $event NgRestModel event EVENT_SERVICE_NGREST.
     */
    public function onCollectServiceData($event)
    {
        if ($this->onBeforeCollectServiceData($event)) {
            $data = $this->serviceData($event);
            if (!empty($data)) {
                $event->sender->addNgRestServiceData($this->name, $data);
            }
        }
    }

    /**
     * Check whether the current plugin attribute is writeable in the Model class or not. If not writeable some events will be stopped from
     * further processing. This is mainly used when adding extraFields to the grid list view.
     *
     * @param \yii\base\Event $event The current base event object.
     * @return boolean Whether the current plugin attribute is writeable or not.
     */
    protected function isAttributeWriteable($event)
    {
        return ($event->sender->hasAttribute($this->name) || $event->sender->canSetProperty($this->name));
    }

    /**
     * Write a value to a plugin attribute or property.
     *
     * As setAttribute() does only write to attributes therefore this method allwos you to write to
     * a property or attribute value. As {{isAttributeWriteAble()}} returns true whether its a property
     * or attribute.
     *
     * @param \yii\base\Event $event The event to retrieve the values from (via $sender property).
     * @param mixed $value The value to writte on the attribute or property.
     * @since 1.2.1
     */
    protected function writeAttribute($event, $value)
    {
        $property = $this->name;
        $event->sender->{$property} = $value;
    }

    /**
     * Get the value from the plugin attribute or property.
     *
     * @param \yii\base\Event $event The event to retrieve the values from (via $sender property).
     * @return mixed
     * @since 1.2.1
     */
    protected function getAttributeValue($event)
    {
        $property = $this->name;

        return $event->sender->{$property};
    }
}
