<?php

namespace luya\admin\ngrest\base;

use Yii;
use yii\db\ActiveRecord;
use yii\db\conditions\OrCondition;
use yii\base\InvalidConfigException;
use luya\helpers\Json;
use luya\admin\helpers\I18n;
use luya\admin\behaviors\LogBehavior;
use luya\admin\base\GenericSearchInterface;
use luya\admin\ngrest\Config;
use luya\admin\ngrest\ConfigBuilder;
use luya\admin\base\RestActiveController;
use yii\base\Event;

/**
 * NgRest Model.
 *
 * Read the Guide to understand [[ngrest-concept.md]].
 *
 * This class extends the {{yii\db\ActiveRecord}}.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
abstract class NgRestModel extends ActiveRecord implements GenericSearchInterface, NgRestModelInterface
{
    /**
     * @var string This event will be trigger after the find population of each row when ngrest loads the data from the server to edit data. (When click on edit icon)
     */
    const EVENT_AFTER_NGREST_UPDATE_FIND = 'afterNgrestUpdateFind';
    
    /**
     * @var string This event will be trigger after the find poulation of each row when ngrest load the overview list (crud).
     */
    const EVENT_AFTER_NGREST_FIND = 'afterNgrestFind';

    /**
     * @var string This event will be trigger when findin the service data of a plugin
     */
    const EVENT_SERVICE_NGREST = 'serviceNgrest';

    /**
     * @var string The constant for the rest create scenario
     * @since 1.2.2
     */
    const SCENARIO_RESTCREATE = RestActiveController::SCENARIO_RESTCREATE;
    
    /**
     * @var string The constant for the rest update scenario
     * @since 1.2.2
     */
    const SCENARIO_RESTUPDATE = RestActiveController::SCENARIO_RESTUPDATE;
    
    /**
     * @var array Defines all fields which should be casted as i18n fields. This will transform the defined fields into
     * json language content parings and the plugins will threat the fields different when saving/updating or request
     * informations.
     *
     * ```php
     * public $i18n = ['textField', 'anotherTextField', 'imageField']);
     * ```
     *
     * In order to build where conditions for i18n fields you can use `find()->i18nWhere('fieldname', 'value')`.
     */
    public $i18n = [];

    protected $ngRestServiceArray = [];

    /**
     * Attach behaviors at constructor to prevent override them with call of behaviors() at subclass model.
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->attachBehaviors([
            'NgRestEventBehavior' => [
                'class' => NgRestEventBehavior::className(),
                'plugins' => $this->getNgRestConfig()->getPlugins(),
            ],
            'LogBehavior' => [
                'class' => LogBehavior::className(),
                'api' => static::ngRestApiEndpoint(),
            ],
        ]);
        return parent::__construct($config);
    }
    
    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_RESTCREATE] = $scenarios[self::SCENARIO_DEFAULT];
        $scenarios[self::SCENARIO_RESTUPDATE] = $scenarios[self::SCENARIO_DEFAULT];
        return $scenarios;
    }
    
    /**
     * @inheritdoc
     */
    public function extraFields()
    {
        $extraFieldKeys = array_keys($this->ngRestExtraAttributeTypes());
        return array_merge(parent::extraFields(), $this->extractRootFields($extraFieldKeys));
    }
    
    /**
     * {@inheritdoc}
     *
     * @return NgRestActiveQuery
     */
    public static function find()
    {
        return new NgRestActiveQuery(get_called_class());
    }

    /**
     * Get an array with the latest primary key value.
     *
     * @return array An array with latest primary key value, for example [10] or if composite keys [10,4]
     * @since 2.0.0
     */
    public static function findLatestPrimaryKeyValue()
    {
        $orderBy = [];
        foreach (static::primaryKey() as $pkName) {
            $orderBy[$pkName] = SORT_DESC;
        }

        return self::ngRestFind()->select(static::primaryKey())->orderBy($orderBy)->asArray()->limit(1)->column();
    }
    
    /**
     * Whether a field is i18n or not.
     *
     * @param string $attributeName The name of the field which is
     * @return boolean
     */
    public function isI18n($attributeName)
    {
        return in_array($attributeName, $this->i18n);
    }

    /**
     * Returns the value for an i18n field before it was casted to the output for the current active language if empty.
     *
     * The main purpose of this method is to retrieve any value from this attribute event when the current value is empty.
     *
     * The value is determined by:
     *
     * 1. Is the i18n casted value empty continue or return value.
     * 2. If preffered language is given and a none empty value exists for the preferred language return the value or continue.
     * 3. Foreach the array and return the first value which is not empty.
     *
     * @param string $attributeName The attribute to return the fallback.
     * @param string $preferredLanguage The prefered language short code name which should be checked whether it has a value or not.
     * @return string|null
     * @since 2.1.0
     */
    public function i18nAttributeFallbackValue($attributeName, $preferredLanguage = null)
    {
        $value = $this->{$attributeName};

        if (empty($value) && $this->isI18n($attributeName)) {
            // get the decoded value from old attribute value.
            $array = I18n::decode($this->getOldAttribute($attributeName));

            if ($preferredLanguage && isset($array[$preferredLanguage]) && !empty($array[$preferredLanguage])) {
                return $this->runI18nContextOnFindPlugin($attributeName, $array[$preferredLanguage]);
            }

            foreach ($array as $value) {
                if (!empty($value)) {
                    return $this->runI18nContextOnFindPlugin($attributeName, $value);
                }
            }
        }

        return $value;
    }

    /**
     * Run an attribute plugin in i18n context in order to ensure plugin functions.
     *
     * This method will return the plugin of the given attribute with the context of the
     * new value. This allows you to re-run plugin options like `markdown` on a given attribute.
     *
     * This is mainly used when the {{i18nAttributeFallbackValue()}} method finds an i18n value
     * and needs to re-run the configured plugin options like nl2br, markdown, conver to link object.
     *
     * @param string $attributeName
     * @param mixed $value
     * @return mixed
     * @since 2.3.0
     */
    protected function runI18nContextOnFindPlugin($attributeName, $value)
    {
        // create the plugin without i18n context as the plugin can handle whether its i18n or not
        $plugin = clone $this->getPluginObject($attributeName);
        $plugin->i18n = false;
        // prepare the context for the event with the current model.
        $senderContext = clone $this;
        $senderContext->{$attributeName} = $value;
        $plugin->onFind(new Event(['sender' => $senderContext]));
        // as the plugin as run the onFind event the sender context will have the new value
        $convertedValue = $senderContext->{$attributeName};
        // clear variables to help with memory issues
        unset($plugin, $senderContext);

        return $convertedValue;
    }

    /**
     * Checks whether given attribute is in the list of i18n fields, if so
     * the field value will be decoded and the value for the current active
     * language is returned.
     *
     * If the attribute is not in the list of attributes values, the value of the attribute is returned. So
     * its also safe to use this function when dealing with none i18n fields.
     *
     * @param string $attributeName The attribute of the ActiveRecord to check and return its decoded i18n value.
     * @return string A json decoded value from an i18n field.
     * @since 2.0.0
     */
    public function i18nAttributeValue($attributeName)
    {
        $value = $this->{$attributeName};
        if ($this->isI18n($attributeName) && Json::isJson($value)) {
            return I18n::decodeFindActive($value);
        }

        return $this->{$attributeName};
    }

    /**
     * Returns the decoded i18n value for a set of attributes.
     *
     * @param array $attributes An array with attributes to return its value
     * @return An array with where the key is the attribute name and value is the decoded i18n value
     * @see {{luya\admin\ngrest\base\NgRestModel::i18nAttributeValue()}}.
     * @since 2.0.0
     */
    public function i18nAttributesValue(array $attributes)
    {
        $values = [];
        foreach ($attributes as $attribute) {
            $values[$attribute] = $this->i18nAttributeValue($attribute);
        }

        return $values;
    }

    /**
     * Define an array with filters you can select from the CRUD list.
     *
     * ```php
     * return [
     *     'deleted' => self::ngRestFind()->andWhere(['is_deleted' => 0]),
     *     'year2016' => self::ngRestFind()->andWhere(['between', 'date', 2015, 2016]),
     * ];
     * ```
     *
     * > Keep in mind to use andWhere() otherwise an existing where() condition could be overriden.
     *
     * @return array Return an array where key is the name and value is the ngRestFind() condition for the filters.
     * @since 1.0.0
     */
    public function ngRestFilters()
    {
        return [];
    }

    /**
     * Define data pools.
     *
     * > The difference between ngRestFilters() and ngRestPools() is that the pool identifer must be provided in the menu component and is not visible in the
     * > UI, it is like an invisible filter, only available to developers.
     *
     * A data pool can be used to retrieve only a subset of data. The identifier for the pool is passed trough to all subrelation
     * calls. Related models will filter their data by the same pool identifier, if configured accordingly.
     *
     * The following is an example of a pool identifier for a table with cars:
     *
     * ```php
     * return [
     *     'poolAudi' => ['car_brand' => 'Audi'],
     *     'poolBMW' => ['car_brand' => 'BMW'],
     * ];
     * ```
     *
     * If the pool identifier is defined in the menu, all subrelation calls will receive the identifer. Thefore, in the above example, you could have a model for
     * car parts that only returns parts with the same pool identifier in relation calls:
     *
     * ```php
     * return [
     *     'poolAudi' => ['parts_brand' => 'Audi'],
     *     'poolBMW' => ['parts_brand' => 'BMW'],
     * ];
     * ```
     *
     * The identifiers `poolAudi` and `poolBMW` are passed to the `parts` table to only return parts for the given car brand.
     *
     * > The pool condition is threaded as where condition, the above example would be `where(['car_brand' => 'BMW'])`. Only hash format expression with "equal" operators are allowed.
     *
     * @return array
     * @since 2.0.0
     */
    public function ngRestPools()
    {
        return [];
    }

    /**
     * Define the default ordering for the ngrest list when loading, by default the primary key
     * sorted ascending is used. To override the method for example sorting by a timestamp field:
     *
     * ```php
     * public function ngRestListOrder()
     * {
     *     return ['created_at' => SORT_ASC];
     * }
     * ```
     *
     * If the return value is `false` the sorting **is disabled** for this NgRest CRUD.
     *
     * @return array Return an Array where the key is the field and value the direction. Example `['timestamp' => SORT_ASC]`.
     * @since 1.0.0
     */
    public function ngRestListOrder()
    {
        return [$this->getNgRestPrimaryKey()[0] => SORT_DESC];
    }
    
    /**
     * Grouping fields into fieldset similar group names which can be collapsed by default or not:
     *
     * ```php
     * public function ngRestAttributeGroups()
     * {
     *    return [
     *       [['timestamp_create', 'timestamp_display_from', 'timestamp_display_until'], 'Timestamps', 'collapsed' => true],
     *       [['image_list', 'file_list'], 'Images', 'collapsed' => false],
     *    ];
     * }
     * ```
     *
     * If collapsed is `true` then the form group is hidden when opening the form, otherwhise its open by default (which is default value when not provided).
     *
     * @return array An array with groups where offset 1 are the fields, 2 the name of the group `collapsed` key if default collapsed or not.
     * @since 1.0.0
     */
    public function ngRestAttributeGroups()
    {
        return [];
    }
    
    /**
     * Enable the Grouping by a field option by default. Allows you to predefine the default group field.
     *
     * ```php
     * public function ngRestGroupByField()
     * {
     *     return 'cat_id';
     * }
     * ```
     *
     * Now by default the fields are grouped by the cat_id field, the admin user can always reset the group by filter
     * to none.
     *
     * @return string The field of what the default grouping should be, false disables the default grouping (default).
     * @since 1.0.0
     */
    public function ngRestGroupByField()
    {
        return false;
    }

    /**
     * When enabled, the field groups in the form are by default expanded (open) or not (closed).
     *
     * This has no effect unless {{luya\admin\ngrest\base\NgRestModel::ngRestGroupByField()}} is configured.
     *
     * @return bool Whether the group field should be expanded or not (default is true).
     * @since 1.2.2.1
     */
    public function ngRestGroupByExpanded()
    {
        return true;
    }
    
    /**
     * Define your relations in order to access the relation data and manage them directly in the same view.
     *
     * Example of how to use two relation buttons based on models which as to be ngrest model as well with apis!
     *
     * ```php
     * public function ngRestRelations()
     * {
     * 	   return [
     *          ['label' => 'The Label', 'targetModel' => Model::class, 'dataProvider' => $this->getSales()],
     *     ];
     * }
     * ```
     *
     * The above example will use the `getSales()` method of the current model where you are implementing this relation. The `getSales()` must return
     * an {{yii\db\QueryInterface}} Object, for example you can use `$this->hasMany(Model, ['key' => 'rel'])` or `new \yii\db\Query()`.
     *
     * You can also define the `tabLabelAttribute` key with the name of a field you like the display as tab name. Assuming your table as a column `title` you
     * can set `'tabLabelAttribute'  => 'title'` in order to display this value in the tab label.
     *
     * @return array
     */
    public function ngRestRelations()
    {
        return [];
    }
    
    /**
     * {@inheritDoc}
     */
    public static function ngRestFind()
    {
        return new NgRestActiveQuery(get_called_class());
    }

    /**
     * The a single object from a primary key defintion.
     *
     * @param string $condition The condition for primary keys like `1,23` or `1`.
     * @return NgRestActiveQuery
     * @since 2.0.1
     */
    public static function ngRestByPrimaryKeyOne($condition)
    {
        return static::ngRestFind()->byPrimaryKey($condition)->one();
    }

    /**
     * Search trough the whole table as ajax fallback when pagination is enabled.
     *
     * This method is used when the angular crud view switches to a pages view and a search term is entered into
     * the query field. By default it will also take the fields from {{genericSearchFields()}}.
     *
     * When you have relations to lookup you can extend the parent implementation, for example:
     *
     * ```php
     * public function ngRestFullQuerySearch($query)
     * {
     *	return parent::ngRestFullQuerySearch($query)
     *		->joinWith(['production'])
     *		->orFilterWhere(['like', 'title', $query]);
     * }
     * ```
     *
     * @param string $query The query which will be used in order to make the like statement request.
     * @return \yii\db\ActiveQuery Returns an ActiveQuery instance in order to send to the ActiveDataProvider.
     */
    public function ngRestFullQuerySearch($query)
    {
        $find = $this->ngRestFind();
        
        $operand = [];
        foreach ($this->genericSearchFields() as $column) {
            $operand[] = ['like', $column, $query];
        }
        
        $find->andWhere(new OrCondition($operand));
        
        return $find;
    }

    /**
     * @inheritdoc
     */
    public function genericSearchFields()
    {
        $fields = [];
        foreach ($this->getTableSchema()->columns as $name => $object) {
            if ($object->phpType == 'string' || $object->phpType == 'integer') {
                $fields[] = static::tableName() . '.' . $object->name;
            }
        }

        return $fields;
    }
    
    /**
     * @inheritdoc
     */
    public function genericSearchHiddenFields()
    {
        return [];
    }
    
    /**
     * @inheritdoc
     */
    public function genericSearchStateProvider()
    {
        return [
            'state' => 'default.route.detail',
            'params' => [
                'id' => 'id',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function genericSearch($searchQuery)
    {
        return $this->ngRestFullQuerySearch($searchQuery)->select($this->genericSearchFields());
    }
    
    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        if ($this->getNgRestCallType()) {
            if ($this->getNgRestCallType() == 'list') {
                $this->trigger(self::EVENT_AFTER_NGREST_FIND);
            }
            if ($this->getNgRestCallType() == 'update') {
                $this->trigger(self::EVENT_AFTER_NGREST_UPDATE_FIND);
            }
        } else {
            return parent::afterFind();
        }
    }

    private $_ngrestCallType;
    
    /**
     * Determine the current call type based on get params as they can change the output behavior to make the ngrest crud list view.
     *
     * @return boolean|string
     */
    public function getNgRestCallType()
    {
        if ($this->_ngrestCallType === null) {
            $this->_ngrestCallType = (!Yii::$app instanceof \yii\web\Application) ? false : Yii::$app->request->get('ngrestCallType', false);
        }

        return $this->_ngrestCallType;
    }
    
    /**
     * Whether the current model is in api context (REST SCENARIOS or CALL TYPE) context or not.
     *
     * @return boolean Whether the current model is in api context or not.
     */
    public function getIsNgRestContext()
    {
        if ($this->scenario == RestActiveController::SCENARIO_RESTCREATE
            || $this->scenario == RestActiveController::SCENARIO_RESTUPDATE
            || $this->getNgRestCallType()) {
            return true;
        }
        
        return false;
    }

    private $_ngRestPrimaryKey;

    /**
     * Getter method for NgRest Primary Key.
     * @return string
     * @throws InvalidConfigException
     */
    public function getNgRestPrimaryKey()
    {
        if ($this->_ngRestPrimaryKey === null) {
            $keys = static::primaryKey();
            if (!isset($keys[0])) {
                throw new InvalidConfigException("The NgRestModel '".__CLASS__."' requires at least one primaryKey in order to work.");
            }
            
            return (array) $keys;
        }

        return $this->_ngRestPrimaryKey;
    }

    /**
     * Setter method for NgRest Primary Key
     *
     * @param string $key
     */
    public function setNgRestPrimaryKey($key)
    {
        $this->_ngRestPrimaryKey = $key;
    }
    
    /**
     *
     * @param string $field
     * @param mixed $data
     */
    public function addNgRestServiceData($field, $data)
    {
        $this->ngRestServiceArray[$field] = $data;
    }
    
    /**
     * Triggers the event service event and returns the resolved data.
     *
     * @return mixed The service data.
     */
    public function getNgRestServices()
    {
        $this->trigger(self::EVENT_SERVICE_NGREST);

        return $this->ngRestServiceArray;
    }
    
    /**
     * Define the field types for ngrest, to use `ngRestConfigDefine()`.
     *
     * The definition can contain properties, but does not have to.
     *
     * ```php
     * public function ngRestAttributeTypes()
     * {
     *     return [
     *         'firstname' => 'text',
     *         'lastname' => 'text',
     *         'description' => 'textarea',
     *         'position' => ['selectArray', [0 => 'Mr', 1 => 'Mrs']],
     *         'image_id' => 'image',
     *         'image_id_2' => ['image'], // is equal to `image_id` field.
     *         'image_id_with_no_filter' => ['image', true],
     *     ];
     * }
     * ```
     *
     * @return array
     * @since 1.0.0-RC1
     */
    public function ngRestAttributeTypes()
    {
        return [];
    }

    /**
     * Same as ngRestAttributeTypes() but used for extraField instead of field.
     *
     * @see ngRestAttributeTypes()
     * @return array
     * @since 1.0.0-RC2
     */
    public function ngRestExtraAttributeTypes()
    {
        return [];
    }

    /**
     * Defines the scope which field should be used for what situation.
     *
     * ```php
     * public function ngRestScopes()
     * {
     *     return [
     *         ['list', ['firstname', 'lastname']],
     *         [['create', 'update'], ['firstname', 'lastname', 'description', 'image_id']],
     *         ['delete', true],
     *     ]:
     * }
     * ```
     *
     * The create and update scopes can also be written in seperated notation in order to configure
     * different forms for create and update:
     *
     * ```php
     * public function ngRestScopes()
     * {
     *     return [
     *         ['list', ['firstname', 'lastname']],
     *         ['create', ['firstname', 'lastname', 'description', 'image_id']],
     *         ['update', ['description']],
     *     ];
     * }
     * ```
     */
    public function ngRestScopes()
    {
        return [];
    }
    
    /**
     * Define Active Window configurations.
     *
     * ```php
     * public function ngRestActiveWindows()
     * {
     *     return [
     *         ['class' => 'luya\admin\aws\TagActiveWindow', 'label' => 'Tags Label'],
     *         ['class' => luya\admin\aws\ChangePasswordActiveWindow::class, 'label' => 'Change your Password'],
     *     ];
     * }
     * ```
     */
    public function ngRestActiveWindows()
    {
        return [];
    }

    /**
     * Define Active Buttons.
     *
     * An array with active button elements:
     *
     * ```php
     * public function ngRestActiveButton()
     * {
     *     return [
     *          ['class' => 'luya\admin\activebuttons\MyActiveButton', 'property' => 'value'],
     *     ];
     * }
     * ```
     *
     * @return array
     * @since 1.2.3
     */
    public function ngRestActiveButtons()
    {
        return [];
    }

    /**
     * Handle a given active button based on the hash (classname in sha1).
     *
     * @param string $hash The hash name, equals to the class name of the button
     * @return array|boolean Returns the button array response or false if not found.
     * @since 1.2.3
     */
    public function handleNgRestActiveButton($hash)
    {
        foreach ($this->ngRestActiveButtons() as $item) {
            if (sha1($item['class']) == $hash) {
                $button = Yii::createObject($item);
                return $button->handle($this);
            }
        }

        return false;
    }
    
    /**
     * The NgRest config has an options property which can contain a variaty of defintions.
     *
     * + saveCallback: This will trigere an angular callback after save/update of a new/existing record.
     *
     * Here an example for the predefined option saveCallback:
     *
     * ```php
     * public function ngRestConfigOptions()
     * {
     *     return [
     *         'saveCallback' => "['ServiceMenuData', function(ServiceMenuData) { ServiceMenuData.load(true); }]",
     *     ];
     * }
     * ```
     *
     * This specific example will reload the menu service from the cms, see the services.js files in the modules to find all
     * possible angular js services.
     *
     * @return array Must be an array with an option identifier and value for the given key.
     * @since 1.2.1
     */
    public function ngRestConfigOptions()
    {
        return [];
    }
    
    
    /**
     * Inject data from the model into the config, usage exmple in ngRestConfig method context:
     *
     * ```php
     * public function ngRestConfig($config)
     * {
     *     // ...
     *     $this->ngRestConfigDefine($config, 'list', ['firstname', 'lastname', 'image_id']);
     *     $this->ngRestConfigDefine($config, 'create', ['firstname', 'lastname', 'description', 'position', 'image_id']);
     *     $this->ngRestConfigDefine($config, 'update', ['firstname', 'lastname', 'description', 'position', 'image_id']);
     *     // ....
     *     return $config;
     * }
     * ```
     *
     * You can also use an array defintion to handle booth types at the same time
     *
     * ```php
     * public function ngRestConfig($config)
     * {
     *     // ...
     *     $this->ngRestConfigDefine($config, ['create', 'update'], ['firstname', 'lastname', 'description', 'position', 'image_id']);
     *     // ....
     *     return $config;
     * }
     * ```
     *
     * @param \luya\admin\ngrest\ConfigBuilder $config The config which the defintion should be append
     * @param string|array $assignedType This can be a string with a type or an array with multiple types
     * @param array $fields An array with fields assign to types type based on the an `ngRestAttributeTypes` defintion.
     * @throws \yii\base\InvalidConfigException
     * @since 1.0.0
     */
    public function ngRestConfigDefine(ConfigBuilder $config, $assignedType, array $fields)
    {
        $types = $this->ngRestAttributeTypes();
        $extraTypes = $this->ngRestExtraAttributeTypes();
        
        $scenarios = $this->scenarios();
        
        $assignedType = (array) $assignedType;
        
        foreach ($assignedType as $type) {
            $scenario = false;
            $scenarioFields = [];
            if ($type == 'create' || $type == 'update') {
                $scenario = 'rest'.$type;
                if (!isset($scenarios[$scenario])) {
                    throw new InvalidConfigException("The scenario '$scenario' does not exists in your scenarios list, have you forgot to defined the '$scenario' in the scenarios() method?");
                } else {
                    $scenarioFields = $scenarios[$scenario];
                }
            }
            
            foreach ($fields as $field) {
                if (!isset($types[$field]) && !isset($extraTypes[$field])) {
                    throw new InvalidConfigException("The ngrest attribue '$field' does not exists in ngRestAttributeTypes() nor in ngRestExtraAttributeTypes() method.");
                }
                
                if ($scenario && !in_array($field, $scenarioFields)) {
                    throw new InvalidConfigException("The field '$field' does not exists in the scenario '$scenario'. You have to define them in the scenarios() method.");
                }
    
                if (isset($extraTypes[$field])) {
                    $typeField = 'extraField';
                    $definition = $extraTypes[$field];
                } else {
                    $typeField = 'field';
                    $definition = $types[$field];
                }
                
                $args = [];
                if (is_array($definition)) {
                    if (array_key_exists('class', $definition)) {
                        $method = $definition['class'];
                        unset($definition['class']);
                        $args = $definition;
                    } else {
                        $method = $config->prepandAdminPlugin($definition[0]);
                        $args = array_slice($definition, 1);
                    }
                } else {
                    $method = $config->prepandAdminPlugin($definition);
                }
                
                $config->$type->$typeField($field, $this->getAttributeLabel($field))->addPlugin($method, $args);
            }
        }
    }
    
    /**
     * @inheritdoc
     */
    public function ngRestConfig($config)
    {
        foreach ($this->ngRestScopes() as $arrayConfig) {
            if (!isset($arrayConfig[0]) && !isset($arrayConfig[1])) {
                throw new InvalidConfigException("Invalid ngRestScope defintion. Definition must contain an array with two elements: `['create', []]`");
            }
            
            $scope = $arrayConfig[0];
            $fields = $arrayConfig[1];

            if ($scope == 'delete' || (is_array($scope) && in_array('delete', $scope))) {
                $config->delete = $fields;
            } else {
                $this->ngRestConfigDefine($config, $scope, $fields);
            }
        }
        
        foreach ($this->ngRestActiveWindows() as $windowConfig) {
            $config->aw->load($windowConfig);
        }
        
        if (!empty($this->ngRestConfigOptions())) {
            $config->options = $this->ngRestConfigOptions();
        }
    }
    
    private $_config;
    
    /**
     * Build and call the full config object if not build yet for this model.
     *
     * @return \luya\admin\ngrest\Config
     */
    public function getNgRestConfig()
    {
        if ($this->_config == null) {
            $config = new Config();
            
            // Generate config builder object
            $configBuilder = new ConfigBuilder(static::class);
            $this->ngRestConfig($configBuilder);
            $config->setConfig($configBuilder->getConfig());
            foreach ($this->i18n as $fieldName) {
                $config->appendFieldOption($fieldName, 'i18n', true);
            }

            // set model as context in order to lazy load data like (https://github.com/luyadev/luya-module-admin/pull/422)
            // - ngRestFilters
            // - getNgRestPrimaryKey
            // - ngRestActiveButtons
            // - ngRestRelations (trough: generateNgRestRelations())
            $config->setModel($this);
            
            // copy model data into config
            $config->setApiEndpoint(static::ngRestApiEndpoint());
            $config->setDefaultOrder($this->ngRestListOrder());
            $config->setAttributeGroups($this->ngRestAttributeGroups());
            $config->setGroupByField($this->ngRestGroupByField());
            $config->setGroupByExpanded($this->ngRestGroupByExpanded());
            $config->setTableName($this->tableName());
            $config->setAttributeLabels($this->attributeLabels());

            $config->onFinish();
            $this->_config = $config;
        }

        return $this->_config;
    }

    /**
     * Generate an array with NgRestRelation objects
     *
     * @return array
     * @since 2.0.0
     */
    public function generateNgRestRelations()
    {
        $relations = [];
        // generate relations
        foreach ($this->ngRestRelations() as $key => $item) {
            /** @var $item \luya\admin\ngrest\base\NgRestRelationInterface */
            if (!$item instanceof NgRestRelation) {
                if (!isset($item['class'])) {
                    $item['class'] = 'luya\admin\ngrest\base\NgRestRelation';
                }
                $item = Yii::createObject($item);
            }
            $item->setModelClass($this->className());
            $item->setArrayIndex($key);
            
            $relations[$key] = $item;
        }

        return $relations;
    }

    /**
     * Get the NgRest Relation defintion object.
     *
     * @param integer $index
     * @return NgRestRelationInterface
     * @since 2.0.0
     */
    public function getNgRestRelationByIndex($index)
    {
        $relations = $this->generateNgRestRelations();

        return isset($relations[$index]) ? $relations[$index] : false;
    }
}
