<?php

namespace luya\admin\ngrest;

use luya\admin\Module;
use luya\admin\ngrest\base\ActiveSelection;
use luya\admin\ngrest\base\NgRestModel;
use luya\admin\ngrest\base\NgRestRelation;
use luya\helpers\ArrayHelper;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;

/**
 * Defines and holds an NgRest Config.
 *
 * Example config array to set via `setConfig()`.
 *
 * ```php
 * $array = [
 *     'list' => [
 *         'firstname' => [
 *             'name' => 'firstname',
 *             'alias' => 'Vorname',
 *             'i18n' => false,
 *             'extraField' => false,
 *             'type' => [
 *                 'class' => '\\admin\\ngrest\\plugins\\Text',
 *                 'args' => ['arg1' => 'arg1_value', 'arg2' => 'arg2_value']
 *             ]
 *         ]
 *     ],
 *     'create' => [
 *         //...
 *     ]
 * ];
 * ```
 *
 * @property NgRestModel $model
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class Config extends BaseObject implements ConfigInterface
{
    private $_model;

    /**
     * Setter methdo for ngrest model context.
     *
     * The model that can be lazy loaded on request instead of preloading from model.
     *
     * @param NgRestModel $model
     * @since 2.4.0
     */
    public function setModel(NgRestModel $model)
    {
        $this->_model = $model;
    }

    /**
     * Getter method for the model
     *
     * @return NgRestModel
     * @since 2.4.0
     */
    public function getModel()
    {
        return $this->_model;
    }

    private array $_config = [];

    /**
     * @inheritdoc
     */
    public function setConfig(array $config)
    {
        if (!empty($this->_config)) {
            throw new InvalidConfigException("Unable to override an already provided Config.");
        }

        $this->_config = $config;
    }

    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        return $this->_config;
    }

    private $_relations = null;

    /**
     * @inheritdoc
     */
    public function getRelations()
    {
        if ($this->_relations === null) {
            // ensure relations are made not on composite table.
            if ($this->model->ngRestRelations() && (is_countable($this->getPrimaryKey()) ? count($this->getPrimaryKey()) : 0) > 1) {
                throw new InvalidConfigException("Its not allowed to have ngRestRealtions() on models with composite primary keys.");
            }

            // generate relations
            $relations = [];
            foreach ($this->model->generateNgRestRelations() as $relation) {
                /** @var $relation \luya\admin\ngrest\base\NgRestRelationInterface */
                $relations[] = [
                    'label' => $relation->getLabel(),
                    'apiEndpoint' => $relation->getApiEndpoint(),
                    'arrayIndex' => $relation->getArrayIndex(),
                    'modelClass' => $relation->getModelClass(),
                    'tabLabelAttribute' => $relation->getTabLabelAttribute(),
                    'relationLink' => $relation->getRelationLink(),
                ];
            }

            $this->_relations = $relations;
        }

        return $this->_relations;
    }

    /**
     *
     * @param array $relations
     */
    public function setRelation(NgRestRelation $relation)
    {
        $this->_relations[] = $relation;
    }

    private array $_activeSelections = [];

    /**
     * Set all active selection definitions
     *
     * @param array $buttons
     * @since 4.0.0
     */
    public function setActiveSelections(array $buttons)
    {
        $objects = [];
        foreach ($buttons as $button) {
            if (!array_key_exists('class', $button)) {
                $button['class'] = ActiveSelection::class;
            }

            $objects[] = Yii::createObject($button);
        }

        $this->_activeSelections = $objects;
    }

    /**
     * {@inheritDoc}
     */
    public function getActiveSelections()
    {
        return $this->_activeSelections;
    }

    private $_activeButtons;

    /**
     * Setter method for the active button array from the model
     *
     * @param array
     * @since 1.2.3
     */
    public function setActiveButtons(array $buttons)
    {
        $this->_activeButtons = $buttons;
    }

    /**
     * @inheritDoc
     */
    public function getActiveButtons()
    {
        if ($this->_activeButtons === null) {
            $buttons = $this->model->ngRestActiveButtons();

            $btns = [];
            foreach ($buttons as $button) {
                $hash = sha1($button['class']);
                $object = Yii::createObject($button);
                $btns[] = [
                    'hash' => $hash,
                    'label' => $object->getLabel(),
                    'icon' => $object->getIcon(),
                    'condition' => $object->getCondition(),
                    'permissionLevel' => $object->getPermissionLevel(),
                ];
            }

            $this->_activeButtons = $btns;
        }

        return $this->_activeButtons;
    }


    private $_apiEndpoint;

    /**
     * @inheritdoc
     */
    public function getApiEndpoint()
    {
        return $this->_apiEndpoint;
    }

    /**
     *
     * @param string $apiEndpoint
     */
    public function setApiEndpoint($apiEndpoint)
    {
        $this->_apiEndpoint = $apiEndpoint;
    }

    private array $_attributeGroups = [];

    /**
     * @inheritdoc
     */
    public function getAttributeGroups()
    {
        return $this->_attributeGroups;
    }

    /**
     *
     * @param array $groups
     */
    public function setAttributeGroups(array $groups)
    {
        $this->_attributeGroups = $groups;
    }

    private array $_attributeLabels = [];

    /**
     * @inheritdoc
     */
    public function setAttributeLabels(array $labels)
    {
        $this->_attributeLabels = $labels;
    }

    private $_filters;

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        if ($this->_filters === null) {
            $this->_filters = $this->model->ngRestFilters();
        }

        return $this->_filters;
    }

    /**
     * Setter method for filters.
     *
     * @param array $filters
     */
    public function setFilters(array $filters)
    {
        $this->_filters = $filters;
    }

    private $_defaultOrder;

    /**
     * Returns an array with default order options.
     *
     * @return array
     */
    public function getDefaultOrder()
    {
        return $this->_defaultOrder;
    }

    /**
     *
     * {@inheritDoc}
     * @see \luya\admin\ngrest\ConfigInterface::setDefaultOrder()
     */
    public function setDefaultOrder($defaultOrder)
    {
        $this->_defaultOrder = $defaultOrder;
    }

    private $_groupByField;

    /**
     * @inheritdoc
     */
    public function getGroupByField()
    {
        return $this->_groupByField;
    }

    /**
     *
     * @param string $groupByField
     */
    public function setGroupByField($groupByField)
    {
        $this->_groupByField = $groupByField;
    }

    private $_groupByExpanded;

    /**
     * @inheritdoc
     */
    public function getGroupByExpanded()
    {
        return $this->_groupByExpanded;
    }

    /**
     *
     * @param bool $groupByExpanded
     * @since 1.2.2.1
     */
    public function setGroupByExpanded($groupByExpanded)
    {
        $this->_groupByExpanded = $groupByExpanded;
    }

    private $_tableName;

    public function getTableName()
    {
        return $this->_tableName;
    }

    public function setTableName($tableName)
    {
        $this->_tableName = $tableName;
    }

    private $_primaryKey;

    /**
     * @inheritdoc
     */
    public function getPrimaryKey()
    {
        if ($this->_primaryKey === null) {
            $this->_primaryKey = $this->model->getNgRestPrimaryKey();
        }

        return $this->_primaryKey;
    }

    /**
     *
     * @param string $key
     */
    public function setPrimaryKey(array $key)
    {
        $this->_primaryKey = $key;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultOrderField()
    {
        if (!$this->getDefaultOrder()) {
            return false;
        }

        return key($this->getDefaultOrder());
    }

    /**
     * @inheritdoc
     */
    public function getDefaultOrderDirection()
    {
        if (!$this->getDefaultOrder()) {
            return false;
        }

        $direction = is_array($this->getDefaultOrder()) ? current($this->getDefaultOrder()) : null; // us preg split to find in string?

        if ($direction == SORT_ASC || strtolower($direction) == 'asc') {
            return '+';
        }

        if ($direction == SORT_DESC || strtolower($direction) == 'desc') {
            return '-';
        }

        return '+';
    }

    private $_hash;

    /**
     * @inheritdoc
     */
    public function getHash()
    {
        if ($this->_hash === null) {
            $this->_hash = md5((string) $this->getApiEndpoint());
        }

        return $this->_hash;
    }

    /**
     *
     * @param string $pointer
     * @return boolean
     */
    public function hasPointer($pointer)
    {
        return array_key_exists($pointer, $this->_config);
    }

    /**
     *
     * @param string $pointer
     * @param boolean $defaultValue
     * @return string|array If default value is an array, an array is returned as default value
     */
    public function getPointer($pointer, $defaultValue = false)
    {
        return $this->hasPointer($pointer) ? $this->_config[$pointer] : $defaultValue;
    }

    /**
     *
     * @param string $pointer
     * @param string $field
     * @return boolean
     */
    public function hasField($pointer, $field)
    {
        return $this->getPointer($pointer) ? array_key_exists($field, $this->_config[$pointer]) : false;
    }

    /**
     *
     * @param string $pointer
     * @param string $field
     * @return boolean
     */
    public function getField($pointer, $field)
    {
        return $this->hasField($pointer, $field) ? $this->_config[$pointer][$field] : false;
    }

    /**
     * Create a plugin object cased on a field array config.
     *
     * @param array $plugin
     * @return \luya\admin\ngrest\base\Plugin
     * @since 2.0.0
     */
    public static function createField(array $plugin)
    {
        return NgRest::createPluginObject($plugin['type']['class'], $plugin['name'], $plugin['alias'], $plugin['i18n'], $plugin['type']['args']);
    }

    /**
     * Get all plugin objects for a given pointer.
     *
     * @param string $pointer The name of the pointer (list, create, update).
     * @return \luya\admin\ngrest\base\Plugin An array with plugin objects
     * @since 2.0.0
     */
    public function getPointerPlugins($pointer)
    {
        $plugins = [];
        foreach ($this->getPointer($pointer, []) as $field) {
            $plugins[] = self::createField($field);
        }

        return $plugins;
    }

    /**
     *
     * @param string $pointer
     * @param array $fields
     * @return boolean[]
     */
    public function getFields($pointer, array $fields)
    {
        $data = [];
        foreach ($fields as $fieldName) {
            if ($this->getField($pointer, $fieldName)) {
                $data[$fieldName] = $this->getField($pointer, $fieldName);
            }
        }
        return $data;
    }

    /**
     * Get an option by its key from the options pointer. Define options like
     *
     * ```php
     * $configBuilder->options = ['saveCallback' => 'console.log(this)'];
     * ```
     *
     * Get the option parameter
     *
     * ```php
     * $config->getOption('saveCallback');
     * ```
     *
     * @param string $key
     * @return boolean
     */
    public function getOption($key)
    {
        return ($this->hasPointer('options') && array_key_exists($key, $this->_config['options'])) ? $this->_config['options'][$key] : false;
    }

    /**
     *
     * @param string $pointer
     * @param string $field
     * @param array $options
     * @return boolean
     */
    public function addField($pointer, $field, array $options = [])
    {
        if ($this->hasField($pointer, $field)) {
            return false;
        }

        $options = ArrayHelper::merge([
            'name' => null,
            'i18n' => false,
            'alias' => null,
            'type' => null,
            'extraField' => false,
        ], $options);

        // can not unshift non array value, create array for this pointer.
        if (empty($this->_config[$pointer])) {
            $this->_config[$pointer] = [];
        }

        $this->_config[$pointer] = ArrayHelper::arrayUnshiftAssoc($this->_config[$pointer], $field, $options);

        return true;
    }

    /**
     *
     * @param string $fieldName
     * @param string $optionKey
     * @param string $optionValue
     */
    public function appendFieldOption($fieldName, $optionKey, $optionValue)
    {
        foreach ($this->getConfig() as $pointer => $fields) {
            if (is_array($fields)) {
                foreach ($fields as $field) {
                    if (isset($field['name'])) {
                        if ($field['name'] == $fieldName) {
                            $this->_config[$pointer][$field['name']][$optionKey] = $optionValue;
                        }
                    }
                }
            }
        }
    }

    /**
     * Whether delete is enabled or not.
     *
     * @return boolean
     */
    public function isDeletable()
    {
        return $this->getPointer('delete') === true ? true : false;
    }

    private $_plugins;

    /**
     * Get all plugins.
     *
     * @return array
     */
    public function getPlugins()
    {
        if ($this->_plugins === null) {
            $plugins = [];
            foreach ($this->getConfig() as $pointer => $fields) {
                if (is_array($fields)) {
                    foreach ($fields as $field) {
                        if (isset($field['type'])) {
                            $fieldName = $field['name'];
                            if (!array_key_exists($fieldName, $plugins)) {
                                $plugins[$fieldName] = $field;
                            }
                        }
                    }
                }
            }
            $this->_plugins = $plugins;
        }

        return $this->_plugins;
    }

    private $_extraFields;

    /**
     * Get all extra fields.
     *
     * @return array
     */
    public function getExtraFields()
    {
        if ($this->_extraFields === null) {
            $extraFields = [];
            foreach ($this->getConfig() as $pointer => $fields) {
                if (is_array($fields)) {
                    foreach ($fields as $field) {
                        if (isset($field['extraField']) && $field['extraField']) {
                            if (!array_key_exists($field['name'], $extraFields)) {
                                $extraFields[] = $field['name'];
                            }
                        }
                    }
                }
            }
            $this->_extraFields = $extraFields;
        }

        return $this->_extraFields;
    }

    /**
     * @inheritdoc
     */
    public function getPointerExtraFields($pointer)
    {
        $extraFields = [];
        foreach ($this->getPointer($pointer, []) as $field) {
            if (isset($field['extraField']) && $field['extraField']) {
                $extraFields[] = $field['name'];
            }
        }
        return $extraFields;
    }

    /**
     * @inheritdoc
     */
    public function onFinish()
    {
        foreach ($this->getPrimaryKey() as $pk) {
            if (!$this->hasField('list', $pk)) {
                $alias = $pk;

                if (array_key_exists($alias, $this->_attributeLabels)) {
                    $alias = $this->_attributeLabels[$alias];
                } elseif (strtolower($alias) == 'id') {
                    $alias = Module::t('model_pk_id'); // use default translation for IDs if not label is given
                }

                $this->addField('list', $pk, [
                    'name' => $pk,
                    'alias' => $alias,
                    'type' => [
                        'class' => 'luya\admin\ngrest\plugins\Number',
                        'args' => [],
                    ],
                ]);
            }
        }
    }
}
