<?php

namespace luya\admin\ngrest\plugins;

use luya\admin\helpers\I18n;
use luya\admin\ngrest\base\Plugin;
use luya\admin\ngrest\traits\CrudLoaderPluginTrait;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\helpers\Json;

/**
 * Performance optimised select relation plugin.
 *
 * This plugin is for CRUD tables with large amount of tables, there for you can not access the ActiveRecord object.
 *
 * ```php
 * 'client_id' => [
 *     'class' => SelectRelationActiveQuery::class,
 *     'query' => $this->getClient(),
 *     'relation' => 'client',
 *     'labelField' => ['client_number', 'firstname', 'lastname']
 * ],
 * ```
 *
 * The above definition assumes `getClient()` is defined for example as:
 *
 * ```php
 * public function getClient()
 * {
 *     return $this->hasOne(Client::class, ['id' => 'client_id']);
 * }
 * ```
 *
 * > Important: Keep in mind that the relation class which is used inside the query definition for `Client` must be an NgRest CRUD model with controller and API!
 *
 * If you have composite keys or large to with big list, in order to preserve the assign on list find you can enable `asyncList`.
 *
 * @property string|array $labelField Provide the sql fields to display.
 * @property yii\db\ActiveQuery $query The query with the relation.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class SelectRelationActiveQuery extends Plugin
{
    use CrudLoaderPluginTrait;

    /**
     * @var string This value will be displayed in the ngrest list overview if the given value is empty().
     */
    public $emptyListValue = "-";

    /**
     * @var boolean If enabled, the frontend value will be loaded from async request in order to keep original list
     * values, this is mainly used when working with composite keys.
     * @since 1.2.2
     */
    public $asyncList = false;

    /**
     * @var string The name of the relation which should be used to load the data. For example if you have `getPerson()` the relation name would be `person`.
     * In order to reduce sql queries you can eager load the given relation in {{luya\admin\ngrest\base\Api::prepareListQuery()}}:
     *
     * ```php
     * public function prepareListQuery()
     * {
     *     return parent::prepareListQuery()->with(['person']);
     * }
     * ```
     * @since 2.1.0
     */
    public $relation;

    private $_labelField;

    /**
     * Setter method for Label Field.
     *
     * @param string|array $labelField The fields to display based on the sql table seperated by commas or as array.
     */
    public function setLabelField($labelField)
    {
        if (is_string($labelField)) {
            $labelField = explode(",", $labelField);
        }

        $this->_labelField = $labelField;
    }

    /**
     * Getter method for Label Field.
     *
     * @return array
     */
    public function getLabelField()
    {
        return $this->_labelField;
    }

    /**
     * @var \yii\db\ActiveQuery
     */
    private $_query;

    /**
     *
     * @param yii\db\ActiveQuery $query
     */
    public function setQuery(ActiveQuery $query)
    {
        $this->_query = $query;
    }

    /**
     *
     * @return \yii\db\ActiveQuery
     */
    public function getQuery()
    {
        return $this->_query;
    }

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        parent::init();

        if (empty($this->labelField)) {
            throw new InvalidConfigException("The labelField property can not be empty.");
        }
    }

    /**
     * @inheritdoc
     */
    public function renderList($id, $ngModel)
    {
        if ($this->asyncList) {
            return $this->createTag('async-value', null, ['api' => $this->getRelationApiEndpoint(), 'model' => $ngModel, 'fields' => Json::encode($this->labelField)]);
        }

        return $this->createListTag($ngModel);
    }

    /**
     * @inheritdoc
     */
    public function renderCreate($id, $ngModel)
    {
        return [
            $this->createCrudLoaderTag($this->_query->modelClass, $ngModel, [], $this->crudLoaderPoolContext),
            $this->createFormTag('zaa-async-value', $id, $ngModel, ['api' => $this->getRelationApiEndpoint(), 'fields' => Json::encode($this->labelField)])
        ];
    }

    /**
     * Get the admin api endpoint name.
     *
     * @return string
     * @since 1.2.2
     */
    protected function getRelationApiEndpoint()
    {
        // build class name
        $class = $this->_query->modelClass;
        // fetch menu api detail from endpoint name
        $menu = Yii::$app->adminmenu->getApiDetail($class::ngRestApiEndpoint());

        if (!$menu) {
            throw new InvalidConfigException("Unable to find the API endpoint, maybe insufficent permission or missing admin module context (admin module prefix).");
        }

        // @todo what about: admin/
        return 'admin/'.$menu['permissionApiEndpoint'];
    }

    /**
     * @inheritdoc
     */
    public function renderUpdate($id, $ngModel)
    {
        return $this->renderCreate($id, $ngModel);
    }

    /**
     * @inheritdoc
     */
    public function onListFind($event)
    {
        // async list ignores the onListFind event
        if ($this->asyncList) {
            return;
        }

        // ensure a value exists for the given field.
        $value = $event->sender->getAttribute($this->name);

        // render empty list value
        if ($this->emptyListValue && empty($value)) {
            return $this->writeAttribute($event, $this->emptyListValue);
        }

        // if a relation is defined, take the relation to render the value
        if ($this->relation) {
            $model = $event->sender->{$this->relation};
            // unable to find the model relation. Maybe an old entry
            if (!$model) {
                return;
            }

            $values = $model->toArray($this->labelField);
            $row = [];
            foreach ($this->labelField as $name) {
                $row[] = $values[$name];
            }

            return $this->writeAttribute($event, implode(" ", $row));
        }

        // fallback solution to load relation by a query, not recommend.
        $model = $this->_query->modelClass;
        $row = $model::ngRestFind()->select($this->labelField)->where(['id' => $value])->asArray(true)->one();

        // unable to find the given model
        if (!$row) {
            return;
        }

        $row = array_map(function ($fieldValue, $fieldName) use ($model) {
            if ((new $model())->isI18n($fieldName)) {
                return I18n::decodeFindActive($fieldValue);
            }
            return $fieldValue;
        }, $row, array_keys($row));

        return $this->writeAttribute($event, implode(" ", $row));
    }
}
