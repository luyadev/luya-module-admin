<?php

namespace luya\admin\ngrest\base;

use luya\admin\base\GenericSearchInterface;
use luya\admin\base\RestActiveController;
use luya\admin\behaviors\LogBehavior;
use luya\admin\helpers\I18n;
use luya\admin\ngrest\Config;
use luya\admin\ngrest\ConfigBuilder;
use luya\helpers\Json;
use Yii;
use yii\base\Event;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\db\conditions\OrCondition;

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
    public const EVENT_AFTER_NGREST_UPDATE_FIND = 'afterNgrestUpdateFind';

    /**
     * @var string This event will be trigger after the find poulation of each row when ngrest load the overview list (crud).
     */
    public const EVENT_AFTER_NGREST_FIND = 'afterNgrestFind';

    /**
     * @var string This event will be trigger when findin the service data of a plugin
     */
    public const EVENT_SERVICE_NGREST = 'serviceNgrest';

    /**
     * @var string The constant for the rest create scenario
     * @since 1.2.2
     */
    public const SCENARIO_RESTCREATE = RestActiveController::SCENARIO_RESTCREATE;

    /**
     * @var string The constant for the rest update scenario
     * @since 1.2.2
     */
    public const SCENARIO_RESTUPDATE = RestActiveController::SCENARIO_RESTUPDATE;

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
     * {@inheritDoc}
     */
    public function __construct($config = [])
    {
        $this->attachBehaviors([
            'NgRestEventBehavior' => [
                'class' => NgRestEventBehavior::class,
                'plugins' => $this->getNgRestConfig()->getPlugins(),
            ],
            'LogBehavior' => [
                'class' => LogBehavior::class,
                'api' => static::ngRestApiEndpoint(),
            ],
        ]);
        parent::__construct($config);
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
     * Attach behaviors to the Active Query.
     *
     * Attach behaviours to every new {{\luya\admin\ngrest\base\NgRestActiveQuery}} on find() but **not ngRestFind()**.
     * Returns a list of behaviors that the query component should behave as.
     *
     * As behavior methods can be access from the inner class, use full functions can be used inside the active query.
     * It also enables the option to share standardized behaviors with functions (conditions), for example a soft delete condition.
     *
     * A behavior example:
     *
     * ```php
     * class MySuperBehavioir extends yii\base\Behavior
     * {
     *     public function active($isActive = true)
     *     {
     *          return $this->andWhere(['is_active' => $isActive]);
     *     }
     * }
     * ```
     *
     * After attaching this behavior, it can be used like `MyModel::find()->active()->one()`.
     *
     * > Whenever possible, directly create a custom Active Query, as it provides full IDE support. The behavior
     * > does not, the example above will even show an IDE error because the mmethod `andWhere()` does not exsist
     * > in the yii\base\Behavior class.
     *
     * The return value of this method should be an array of behavior objects or configurations
     * indexed by behavior names. A behavior configuration can be either a string specifying
     * the behavior class or an array of the following structure:
     *
     * ```php
     * public static function findActiveQueryBehaviors()
     * {
     *     return [
     *         'behaviorName' => [
     *             'class' => 'BehaviorClass',
     *             'property1' => 'value1',
     *             'property2' => 'value2',
     *          ]
     *     ]
     * }
     * ```
     *
     * @see {{\yii\base\Component::behaviors}}
     * @return array
     * @since 3.4.0
     */
    public static function findActiveQueryBehaviors()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     *
     * @return NgRestActiveQuery
     */
    public static function find()
    {
        $config = [];

        foreach (static::findActiveQueryBehaviors() as $name => $class) {
            $config['as ' . $name] = $class;
        }

        return new NgRestActiveQuery(static::class, $config);
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

    private array $_i18nOldValues = [];

    /**
     * Set the old json value from a i18n database value.
     *
     * This method is used when the ngrest plugins are overiding the values from the database. Therefore the original database
     * values can be stored here in order to retrieve those informations in later stage. f.e. when accessing language values for another
     * language the current application language
     *
     * @param string $attributeName The attribute name associated with the json value
     * @param string $value A json with the values f.e. `{"de":"foobar","en":"foobaz"}`
     * @since 3.6.0
     * @see {{getI18nOldValue()}}
     * @see https://github.com/luyadev/luya-module-admin/pull/567
     */
    public function setI18nOldValue($attributeName, $value)
    {
        $this->_i18nOldValues[$attributeName] = $value;
    }

    /**
     * Get the old/original i18n value from the database.
     *
     * @param string $attributeName
     * @return string The json value from either the old value setter array or the active record getOldAttribute() method.
     * @since 3.6.0
     * @see {{setI18nOldValue()}}
     * @see https://github.com/luyadev/luya-module-admin/pull/567
     */
    public function getI18nOldValue($attributeName)
    {
        return array_key_exists($attributeName, $this->_i18nOldValues) ? $this->_i18nOldValues[$attributeName] : $this->getOldAttribute($attributeName);
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
            $array = I18n::decode($this->getI18nOldValue($attributeName));

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
     * Returns the value of an i18n attribute for the given language.
     *
     * This method is commonly used in order to retrieve a value for a given language even though
     * the application language is different.
     *
     * @param string $attributeName The name of the attribute to find the value.
     * @param string $language The language short code.
     * @param boolean $raw If enabled the value will not be parsed through the assigned ngRestAttribute plugin and just returns the raw value from the database.
     * @return mixed|null Returns the value, either raw or converted trough the assigned plugin. If the language is not found (maybe not set already) null is returned.
     * @since 3.5.2
     */
    public function i18nAttributeLanguageValue($attributeName, $language, $raw = false)
    {
        // get the decoded value from old attribute value.
        $array = I18n::decode($this->getI18nOldValue($attributeName));

        // if language is available in the array
        if (isset($array[$language])) {
            // return the raw value from the database
            if ($raw) {
                return $array[$language];
            }

            return $this->runI18nContextOnFindPlugin($attributeName, $array[$language]);
        }

        return null;
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
        return new NgRestActiveQuery(static::class);
    }

    /**
     * The a single object from a primary key definition.
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
        $find = static::ngRestFind();

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
        foreach (static::getTableSchema()->columns as $name => $object) {
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
                throw new InvalidConfigException("The NgRestModel '".self::class."' requires at least one primaryKey in order to work.");
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
     * The NgRest config has an options property which can contain a variaty of definitions.
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
     * Format the values for export generator.
     *
     * **since 4.3 the ngRestExport() takes precendence regarding what attributes can be exported and how they are sorted! This means that when ngRestExport()
     * is used, only the given fields will be available to export and the will be exported in order they are defined in the array.**
     *
     * When exporting data, it might be convient to format certain values, by default the {{luya\components\Formatter}} will be used. Its
     * also possible to provide a closure function to interact with the model. When handling large amount of data to export, this might
     * make problems because it will generate a model for each row. Therefore an empty array response will improve performance because {{ngRestExport()}}
     * will be ignored.
     *
     * ```php
     * public function ngRestExport()
     * {
     *     return [
     *         'created_at' => 'datetime',
     *         'comment' => 'ntext',
     *         'category_id' => function($model) {
     *             return $model->category->title;
     *         }
     *     ];
     * }
     * ```
     *
     * When using a relation call `$model->category->title` it might usefull to eager load this relation, therefore take a look at {{luya\admin\ngrest\base\Api::withRelations()}}.
     * Therefore the withRelations() can be configured in the api controller of the given NgRest model:
     *
     * ```php
     * public function withRelations()
     * {
     *     return [
     *          'export' => ['category'],
     *     ];
     * }
     * ```
     *
     * @return array An array where the key is the attribute and value is either the formatter to use or a closure where the first param is the model itself. Only the given attributes will be available in the export and are ordered in the list they are defined in the array.
     * @since 3.9.0
     */
    public function ngRestExport()
    {
        return [];
    }

    /**
     * Define Active Selections.
     *
     * Active Selections are buttons which are visible in the CRUD list and can interact with the selected items (through a checkbox).
     *
     * Active Selections can be either defined inline or as instance of {{luya\admin\ngrest\base\ActiveSelection}}. There are also predefined
     * Selections Available.
     *
     * ```php
     * public function ngRestActiveSelections()
     * {
     *     return [
     *         [
     *             'label' => 'Archive Rows',
     *             'action' => function(array $items, \luya\admin\ngrest\base\ActiveSelection $context) {
     *                 foreach ($items as $item) {
     *                     // do something with item. Each item is an ActiveRecord of the method implementation itself.
     *                 }
     *
     *                 // if the selection interacts with the items, it might be necessary to reload the CRUD.
     *                 $context->sendReloadEvent();
     *
     *                 return $context->sendSuccess('Done!');
     *             }
     *         ],
     *         [
     *             'class' => 'luya\admin\selections\DeleteActiveSelection'
     *         ]
     *     ];
     * }
     * ```
     *
     * > Keep in mind that `$this` does not result in a certain Active Record context as there is no model assigned when `ngRestActiveSelections()` is called.
     *
     * When generating multiple buttons f.e. from another model its recommend to use array notation, otherwise its possible to create an infinite circular reference.
     *
     * ```php
     *  public function ngRestActiveSelections()
     * {
     *     $selections = [];
     *     foreach (MyModel::find()->asArray()->all() as $myModel) {
     *         $selections[] = [
     *             'label' => $myModel['title'],
     *             'action' => function(array $items, \luya\admin\ngrest\base\ActiveSelection $context) use ($myModel) {
     *                 // accessing $myModel
     *             }
     *         ];
     *     }
     *
     *     return $selections;
     * }
     * ```
     *
     * @return array An array with definitions which eithe requires `action` and `label` or using classed based action defined via `class`.
     * @since 4.0.0
     */
    public function ngRestActiveSelections()
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
     * You can also use an array definition to handle booth types at the same time
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
     * @param \luya\admin\ngrest\ConfigBuilder $config The config which the definition should be append
     * @param string|array $assignedType This can be a string with a type or an array with multiple types
     * @param array $fields An array with fields assign to types type based on the an `ngRestAttributeTypes` definition.
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
                throw new InvalidConfigException("Invalid ngRestScope definition. Definition must contain an array with two elements: `['create', []]`");
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
        // get the scope based config options if no ngRestConfigOptions() are defined
        $configOptions = empty($this->ngRestConfigOptions()) ? $this->getNgRestScopeConfigOptions($config) : $this->ngRestConfigOptions();
        if (!empty($configOptions)) {
            $config->options = $configOptions;
        }
    }

    /**
     * Return the scope definition third entry looking for button condition
     * Currently support only buttonCondition
     *
     * Example of returned array :
     *
     * ```php
     * [
     *    "buttonCondition" => [
     *       ["update",  "{title}>1"],
     *       ["delete",  "{title}==2 && {firstname}=='bar'"]
     *    ]
     * ]
     * ```
     *
     * @return array buttonCondition indexed array
     * @since 4.0.0
     */
    public function getNgRestScopeConfigOptions($config)
    {
        $configOptions = [];
        foreach ($this->ngRestScopes() as $arrayConfig) {
            $scope = is_string($arrayConfig[0]) ? [$arrayConfig[0]] : $arrayConfig[0];
            $buttonConditionConfig =  $this->ngRestConfigButtonCondition($arrayConfig);

            if (!empty($buttonConditionConfig)) {
                foreach ($scope as $single_scope) {
                    $configOptions['buttonCondition'][] = [$single_scope, $buttonConditionConfig];
                }
            }
        }
        return $configOptions;
    }

    /**
     * Lookup butoon condition from config
     * If condition is a set of field=>value array, return an AND linked string
     * @return string extracted buttonCondtion
     */
    private function ngRestConfigButtonCondition($arrayConfig)
    {
        if (!isset($arrayConfig[2]) || !isset($arrayConfig[2]['buttonCondition'])) {
            $buttonCondition = '';
        } elseif (is_string($arrayConfig[2]['buttonCondition'])) {
            $buttonCondition = $arrayConfig[2]['buttonCondition'];
        } elseif (is_array($arrayConfig[2]['buttonCondition'])) {
            $conditions = [];
            foreach ($arrayConfig[2]['buttonCondition'] as $field => $value) {
                $conditions [] = sprintf('%s==%s', $field, $value);
            }
            $buttonCondition = implode(' && ', $conditions);
        } else {
            $buttonCondition = '';
        }
        return $buttonCondition;
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
            $config->setTableName(static::tableName());
            $config->setAttributeLabels($this->attributeLabels());
            $config->setActiveSelections($this->ngRestActiveSelections());

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
            $item->setModelClass($this::class); // former: $item->setModelClass($this->className());
            $item->setArrayIndex($key);

            $relations[$key] = $item;
        }

        return $relations;
    }

    /**
     * Get the NgRest Relation definition object.
     *
     * @param integer $index
     * @return NgRestRelationInterface
     * @since 2.0.0
     */
    public function getNgRestRelationByIndex($index)
    {
        $relations = $this->generateNgRestRelations();

        return $relations[$index] ?? false;
    }
}
