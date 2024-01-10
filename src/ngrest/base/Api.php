<?php

namespace luya\admin\ngrest\base;

use luya\admin\base\RestActiveController;
use luya\admin\components\Auth;
use luya\admin\models\Tag;
use luya\admin\models\UserAuthNotification;
use luya\admin\models\UserOnline;
use luya\admin\ngrest\Config;
use luya\admin\ngrest\NgRest;
use luya\admin\ngrest\render\RenderActiveWindow;
use luya\admin\ngrest\render\RenderActiveWindowCallback;
use luya\admin\traits\TaggableTrait;
use luya\helpers\ArrayHelper;
use luya\helpers\ExportHelper;
use luya\helpers\Json;
use luya\helpers\ObjectHelper;
use luya\helpers\StringHelper;
use luya\helpers\Url;
use Yii;
use yii\base\ErrorException;
use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\ActiveQueryInterface;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\web\NotFoundHttpException;

/**
 * The RestActiveController for all NgRest implementations.
 *
 * When pagination is enabled (by setting {{$pagination}} property or if {{$autoEnablePagination}} apply) the crud search will be performed trough an
 * async request instead of angular filtering. Angular filtering is searching for the string in the response, while async full search does
 * call the {{actionFullResponse()}} method trough the api, which will the call the {{luya\admin\ngrest\base\NgRestModel::ngRestFullQuerySearch()}} method.
 *
 * @property \luya\admin\ngrest\NgRestModel $model Get the model object based on the $modelClass property.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class Api extends RestActiveController
{
    /**
     * @var string Defines the related model for the NgRest Controller. The full qualiefied model name
     * is required.
     *
     * ```php
     * public $modelClass = 'admin\models\User';
     * ```
     */
    public $modelClass;

    /**
     * @var array An array with default pagination configuration
     * @since 1.2.2
     */
    public $pagination = ['defaultPageSize' => 25];

    /**
     * @var string When a filter model is provided filter is enabled trough json request body, works only for index and list.
     * @see https://luya.io/guide/ngrest/api.html#filtering
     * @see https://www.yiiframework.com/doc/guide/2.0/en/output-data-providers#filtering-data-providers-using-data-filters
     * @since 1.2.2
     */
    public $filterSearchModelClass;

    /**
     * @var array|string Define a yii caching depency will enable the caching for this API.
     *
     * Example usage:
     *
     * ```php
     * public $cacheDependency = [
     *     'class' => 'yii\caching\DbDependency',
     *     'sql' => 'SELECT MAX(update_ad) FROM news',
     * ];
     * ```
     *
     * This should be used very carefully as the ngrest crud list data is cached too. Therefore this should be used together
     * with a Timestamp behavior!
     *
     * @see https://www.yiiframework.com/doc/guide/2.0/en/caching-data#cache-dependencies
     * @since 1.2.3
     */
    public $cacheDependency;

    /**
     * @var boolean If enabled, the truncate action is attached to the API. In order to run the truncate action the delete permission is required.
     * @since 3.2.0
     */
    public $truncateAction = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->modelClass === null) {
            throw new InvalidConfigException("The property `modelClass` must be defined by the Controller.");
        }

        $this->addActionPermission(Auth::CAN_VIEW, [
            'index', 'view', 'services', 'search', 'relation-call', 'filter', 'export', 'list', 'toggle-notification',
        ]);

        $this->addActionPermission(Auth::CAN_CREATE, [
            'create',
        ]);

        $this->addActionPermission(Auth::CAN_UPDATE, [
            'active-window-render', 'active-window-callback', 'active-button', 'update',
        ]);

        $this->addActionPermission(Auth::CAN_DELETE, [
            'delete', 'truncate',
        ]);
    }

    /**
     * Auto add those relations to queries.
     *
     * > The relation will only eager load when available in **expand** get params. `?expand=user` f.e.
     *
     * This can be either an array with relations which will be passed to `index, list and view` or an array with a subdefinition in order to define
     * which relation should be us when.
     *
     * basic:
     *
     * ```php
     * return ['user', 'images'];
     * ```
     *
     * The above relations will be auto added trough {{yii\db\ActiveQuery::with()}}. In order to define view specific actions:
     *
     * ```php
     * return [
     *     'index' => ['user', 'images'],
     *     'list' => ['user'],
     * ];
     * ```
     *
     * Possible action column names:
     *
     * + index
     * + list
     * + search
     * + export
     *
     * @return array
     * @since 1.2.2
     */
    public function withRelations()
    {
        return [];
    }

    /**
     * Get the relations for the corresponding action name.
     *
     * > Since version 1.2.3 it also checks if the $expand get param is provided for the given relations, otherwise
     * > the relation will not be joined trough `with`. This reduces the database querie time.
     *
     * @param string $actionName The action name like `index`, `list`, `search`, `relation-call`.
     * @return array An array with relation names.
     * @since 1.2.2
     */
    public function getWithRelation($actionName)
    {
        $expand = Yii::$app->request->get('expand', '');
        $relationPrefixes = [];
        foreach (StringHelper::explode($expand, ',', true, true) as $relation) {
            // check for subrelation dot notation.
            $relationPrefixes[] = current(explode(".", $relation));
        }

        // no expand param found, return empty array which means no relations to load
        if (empty($relationPrefixes)) {
            return [];
        }

        $withRelations = $this->withRelations();

        foreach ($withRelations as $relationName) {
            // it seem to be the advance strucutre for given actions.
            if (is_array($relationName) && isset($withRelations[$actionName])) {
                return $this->relationsFromExpand($withRelations[$actionName], $relationPrefixes);
            }
        }
        // simple structure
        return $this->relationsFromExpand($withRelations, $relationPrefixes);
    }

    /**
     * Ensure if the expand prefix exists in the relation.
     *
     * @param array $relations The available relations
     * @param array $expandPrefixes The available expand relation names
     * @return array
     * @since 1.2.3
     */
    private function relationsFromExpand(array $relations, array $expandPrefixes)
    {
        $valid = [];
        foreach ($expandPrefixes as $prefix) {
            foreach ($relations as $relation) {
                if (StringHelper::startsWith($relation, $prefix)) {
                    $valid[] = $relation;
                }
            }
        }
        return $valid;
    }

    /**
     * Prepare Index Query.
     *
     * You can override the prepare index query to preload relation data like this:
     *
     * ```php
     * public function prepareIndexQuery()
     * {
     *     return parent::prepareIndexQuery()->with(['relation1', 'relation2']);
     * }
     * ```
     *
     * Make sure to call the parent implementation.
     *
     * > This will call the `find()` method of the model.
     *
     * @return \yii\db\ActiveQuery
     * @since 1.2.1
     */
    public function prepareIndexQuery()
    {
        /* @var $modelClass \yii\db\BaseActiveRecord */
        $modelClass = $this->modelClass;
        return $modelClass::find()->with($this->getWithRelation('index'));
    }

    /**
     * Prepare the NgRest List Query.
     *
     * > This will call the `ngRestFind()` method of the model.
     *
     * Use in list, export
     *
     * @see {{prepareIndexQuery()}}
     * @return \yii\db\ActiveQuery
     * @since 1.2.2
     */
    public function prepareListQuery()
    {
        /* @var $modelClass \yii\db\BaseActiveRecord */
        $modelClass = $this->modelClass;

        $find = $modelClass::ngRestFind();

        $this->handleNotifications($modelClass, $this->authId);

        // check if a pool id is requested:
        $this->appendPoolWhereCondition($find);

        // add tags condition
        $tagIds = Yii::$app->request->get('tags');
        if ($tagIds) {
            $subQuery = clone $find;
            $inQuery = $subQuery->joinWith(['tags tags'])->andWhere(['tags.id' => array_unique(explode(",", $tagIds))])->select(['pk_id']);
            $find->andWhere(['in', $modelClass::primaryKey(), $inQuery]);
        }

        return $find->with($this->getWithRelation('list'));
    }

    /**
     * Add new notification or update to latest primary key if exists
     *
     * @param string $modelClass
     * @param integer $authId
     * @return boolean
     * @since 2.0.1
     */
    protected function handleNotifications($modelClass, $authId)
    {
        // find the latest primary key value and store into row notifications user auth table
        $pkValue = Json::encode($modelClass::findLatestPrimaryKeyValue());

        $model = UserAuthNotification::find()->where(['user_id' => Yii::$app->adminuser->id, 'auth_id' => $authId])->one();

        if ($model) {
            $model->model_latest_pk_value = $pkValue;
            $model->model_class = $modelClass::className();
            return $model->save();
        }

        $model = new UserAuthNotification();
        $model->auth_id = $authId;
        $model->user_id = Yii::$app->adminuser->id;
        $model->model_latest_pk_value = $pkValue;
        $model->model_class = $modelClass::className();
        return $model->save();
    }

    /**
     * Append the pool where condition to a given query.
     *
     * If the pool identifier is not found, an exception will be thrown.
     *
     * @param NgRestActiveQuery|ActiveQuery $query
     * @since 2.0.0
     */
    private function appendPoolWhereCondition($query)
    {
        if ($query instanceof NgRestActiveQuery) {
            $query->inPool(Yii::$app->request->get('pool'));
        }
    }

    /**
     * Returns whether the `$dataFilter` property of IndexAction should be set with the according value.
     *
     * @return array|boolean
     * @since 1.2.2
     */
    public function getDataFilter()
    {
        if ($this->filterSearchModelClass) {
            return [
                'class' => 'yii\data\ActiveDataFilter',
                'searchModel' => $this->filterSearchModelClass,
            ];
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = [
            'index' => [ // for casual api request behavior
                'class' => 'luya\admin\ngrest\base\actions\IndexAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'prepareActiveDataQuery' => [$this, 'prepareIndexQuery'],
                'dataFilter' => $this->getDataFilter(),
            ],
            'list' => [ // for ngrest list
                'class' => 'luya\admin\ngrest\base\actions\IndexAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'prepareActiveDataQuery' => [$this, 'prepareListQuery'],
                'dataFilter' => $this->getDataFilter(),
            ],
            'view' => [
                'class' => 'luya\admin\ngrest\base\actions\ViewAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
            ],
            'create' => [
                'class' => 'luya\admin\ngrest\base\actions\CreateAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'scenario' => $this->createScenario,
            ],
            'update' => [
                'class' => 'luya\admin\ngrest\base\actions\UpdateAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'scenario' => $this->updateScenario,
            ],
            'delete' => [
                'class' => 'luya\admin\ngrest\base\actions\DeleteAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
            ],
            'options' => [
                'class' => 'luya\admin\ngrest\base\actions\OptionsAction',
            ],
        ];

        if ($this->truncateAction) {
            $actions['truncate'] = [
                'class' => 'luya\admin\ngrest\base\actions\TruncateAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
            ];
        }

        if ($this->enableCors) {
            $actions['options']['class'] = 'luya\admin\ngrest\base\actions\OptionsAction';
        }

        return $actions;
    }

    private $_model;

    /**
     * Get the ngrest model object (unloaded).
     *
     * @return NgRestModel
     * @throws InvalidConfigException
     */
    public function getModel()
    {
        if ($this->_model === null) {
            $this->_model = Yii::createObject($this->modelClass);

            if (!$this->_model instanceof NgRestModelInterface) {
                throw new InvalidConfigException("The modelClass '$this->modelClass' must be an instance of NgRestModelInterface.");
            }
        }

        return $this->_model;
    }

    /**
     * Get the Model for the API based on a given Id.
     *
     * If not found a NotFoundHttpException will be thrown.
     *
     * @param integer|string $id The id to performe the findOne() method.
     * @throws NotFoundHttpException
     * @return \luya\admin\ngrest\base\NgRestModel
     */
    public function findModel($id)
    {
        $model = $this->findModelClassObject($this->modelClass, $id, 'view');

        if (!$model) {
            throw new NotFoundHttpException("Unable to find the Model for the given ID");
        }

        return $model;
    }


    /**
     * Find the model for a given class and id.
     *
     * @param string $modelClass the full qualified path to the model
     * @param string $id The id which is a string, for example 1 or for composite keys its 1,4
     * @param string $relationContext The name of the context, which is actually the action like `search` or `index`.
     * @return yii\db\ActiveRecord|boolean
     */
    public function findModelClassObject($modelClass, $id, $relationContext)
    {
        // returns an array with the names of the primary keys
        $keys = $modelClass::primaryKey();
        if ((is_countable($keys) ? count($keys) : 0) > 1) {
            $values = explode(',', $id);
            if ((is_countable($keys) ? count($keys) : 0) === count($values)) {
                return $this->findModelFromCondition($values, $keys, $modelClass, $relationContext);
            }
        } elseif ($id !== null) {
            return $this->findModelFromCondition([$id], $keys, $modelClass, $relationContext);
        }

        return false;
    }

    /**
     * This equals to the ActieRecord::findByCondition which is sadly a protected method.
     *
     * @param array $values An array with values for the given primary keys
     * @param array $keys An array holding all primary keys
     * @param string $modelClass The full qualified namespace to the model
     * @param string $relationContext The name of the context like "search", "index", "list". Its acutally the action name
     * @since 1.2.3
     * @return yii\db\ActiveRecord
     */
    protected function findModelFromCondition(array $values, array $keys, $modelClass, $relationContext)
    {
        $condition = array_combine($keys, $values);
        // If an api user the internal find methods are used to find items.
        if (!Yii::$app->adminuser->isGuest && Yii::$app->adminuser->identity->is_api_user) {
            // api calls will always use the "original" find method which is based on yii2 guide the best approach to hide given data by default.
            $findModelInstance = $modelClass::find();
        } else {
            // if its an admin user which is browsing the ui the internal ngRestFind method is used.
            $findModelInstance = $modelClass::ngRestFind();
        }

        return $findModelInstance->andWhere($condition)->with($this->getWithRelation($relationContext))->one();
    }

    /**
     * User Unlock
     *
     * Unlock this API for the current logged in user.
     *
     * @return void
     */
    public function actionUnlock()
    {
        UserOnline::unlock(Yii::$app->adminuser->id);
    }

    /**
     * Service Data
     *
     * @return array An array with all services information for the given ngrest model.
     */
    public function actionServices()
    {
        $settings = [];
        $apiEndpoint = $this->model->ngRestApiEndpoint();
        $userSortSettings = Yii::$app->adminuser->identity->setting->get('ngrestorder.admin/'.$apiEndpoint, false);

        if ($userSortSettings && is_array($userSortSettings)) {
            if ($userSortSettings['sort'] == SORT_DESC) {
                $order = '-'.$userSortSettings['field'];
            } else {
                $order = '+'.$userSortSettings['field'];
            }

            $settings['order'] = $order;
        }

        $userFilter = Yii::$app->adminuser->identity->setting->get('ngrestfilter.admin/'.$apiEndpoint, false);
        if ($userFilter) {
            $settings['filterName'] = $userFilter;
        }

        $modelClass = $this->modelClass;

        // check if taggable exists, if yes return all used tags for the
        if (ObjectHelper::isTraitInstanceOf($this->model, TaggableTrait::class)) {
            $tags = ArrayHelper::toArray($this->model->findTags(), [
                Tag::class => ['id', 'name']
            ]);
        } else {
            $tags = false;
        }

        $notificationMuteState = false;

        $userAuthNotificationModel = UserAuthNotification::find()->where(['user_id' => Yii::$app->adminuser->id, 'auth_id' => $this->authId])->one();
        if ($userAuthNotificationModel) {
            $notificationMuteState = $userAuthNotificationModel->is_muted;
        }

        return [
            'service' => $this->model->getNgRestServices(),
            '_authId' => $this->authId,
            '_tags' => $tags,
            '_hints' => $this->model->attributeHints(),
            '_settings' => $settings,
            '_notifcation_mute_state' => $notificationMuteState,
            '_locked' => [
                'data' => UserOnline::find()
                    ->select(['user_id', 'lock_pk', 'last_timestamp', 'firstname', 'lastname', 'admin_user.id'])
                    ->where(['lock_table' => $modelClass::tableName()])
                    ->joinWith('user')
                    ->asArray()
                    ->all(),
                'userId' => Yii::$app->adminuser->id,
            ],
        ];
    }

    /**
     * Toggle Notifications
     *
     * @uses integer mute Whether should be muted or not
     * @return UserAuthNotification The user auth notification model. If model does not exists a new model will be created.
     * @since 2.0.0
     */
    public function actionToggleNotification()
    {
        $newMuteState = Yii::$app->request->getBodyParam('mute');

        $model = UserAuthNotification::find()->where(['user_id' => Yii::$app->adminuser->id, 'auth_id' => $this->authId])->one();

        if ($model) {
            $model->is_muted = (int) $newMuteState;
            $model->save();
        } else {
            $model = new UserAuthNotification();
            $model->is_muted = (int) $newMuteState;
            $model->auth_id = $this->authId;
            $model->user_id = Yii::$app->adminuser->id;
            $model->model_class = $this->modelClass;
        }

        return $model;
    }

    /**
     * Search
     *
     * Search querys with Pagination will be handled by this action.
     *
     * @uses string $query The search term as post request.
     * @param string $query The query to lookup the database, if query is empty a post request with `query` can be used instead.
     * @return \yii\data\ActiveDataProvider
     */
    public function actionSearch($query = null)
    {
        if (empty($query)) {
            $query = Yii::$app->request->post('query');
        }

        $find = $this->model->ngRestFullQuerySearch($query);
        $this->appendPoolWhereCondition($find);

        return new ActiveDataProvider([
            'query' => $find->with($this->getWithRelation('search')),
            'pagination' => $this->pagination,
            'sort' => [
                'attributes' => $this->generateSortAttributes($this->model->getNgRestConfig()),
            ]
        ]);
    }

    /**
     * Generate Sort attributes
     *
     * Generate an array of sortable attribute definitions from a ngrest config object.
     *
     * @param Config $config The Ngrest Config object
     * @return array Returns an array with sortable attributes.
     * @since 2.0.0
     */
    public function generateSortAttributes(Config $config)
    {
        $sortAttributes = [];
        foreach ($config->getPointerPlugins('list') as $plugin) {
            $sortAttributes = ArrayHelper::merge($plugin->getSortField(), $sortAttributes);
        }

        return $sortAttributes;
    }

    /**
     * Get Relation Data
     *
     * @param mixed $arrayIndex
     * @param mixed $id
     * @param string $modelClass The name of the model where the ngRestRelation is defined.
     * @param string $query An optional query to filter the response for the given search term (since 2.0.0)
     * @throws InvalidCallException
     * @return \yii\data\ActiveDataProvider
     */
    public function actionRelationCall($arrayIndex, $id, $modelClass, $query = null)
    {
        $modelClass = base64_decode($modelClass);

        if (!class_exists($modelClass)) {
            throw new InvalidCallException("Unable to find the given class \"$modelClass\".");
        }

        // `findOne((int) $id)`: (int) $id is not required as the value is safed by action param $id
        $model = $modelClass::findOne($id);

        if (!$model) {
            throw new InvalidCallException("Unable to resolve relation call model.");
        }

        /** @var \luya\admin\ngrest\base\NgRestRelationInterface $relation */
        $relation = $model->getNgRestRelationByIndex($arrayIndex);

        if (!$relation) {
            throw new InvalidCallException("Unable to find the given ng rest relation for this index value.");
        }

        $find = $relation->getDataProvider();

        if ($find instanceof ActiveQueryInterface) {
            $find->with($this->getWithRelation('relation-call'));
            $this->appendPoolWhereCondition($find);
        }

        $targetModel = Yii::createObject(['class' => $relation->getTargetModel()]);

        if ($query) {
            foreach ($targetModel->getNgRestPrimaryKey() as $pkName) {
                $searchQuery = $targetModel->ngRestFullQuerySearch($query)->select([$targetModel->tableName() . '.' . $pkName]);
                $find->andWhere(['in', $targetModel->tableName() . '.' . $pkName, $searchQuery]);
            }
        }

        return new ActiveDataProvider([
            'query' => $find,
            'pagination' => $this->pagination,
            'sort' => [
                'attributes' => $this->generateSortAttributes($targetModel->getNgRestConfig()),
            ]
        ]);
    }

    /**
     * Filter
     *
     * @param string $filterName The filter name to apply.
     * @param string $query An optional query to search inside the data.
     * @throws InvalidCallException
     * @return \yii\data\ActiveDataProvider
     */
    public function actionFilter($filterName, $query = null)
    {
        $model = $this->model;
        $filterName = Html::encode($filterName);
        $filtersList = $model->ngRestFilters();

        if (!array_key_exists($filterName, $filtersList)) {
            throw new InvalidCallException("The requested filter '$filterName' does not exists in the filter list.");
        }

        $this->handleNotifications($this->modelClass, $this->authId);

        $find = $filtersList[$filterName];

        if ($query) {
            foreach ($model->getNgRestPrimaryKey() as $pkName) {
                $searchQuery = $model->ngRestFullQuerySearch($query)->select([$model->tableName() . '.' . $pkName]);
                $find->andWhere(['in', $model->tableName() . '.' . $pkName, $searchQuery]);
            }
        }

        $this->appendPoolWhereCondition($find);

        return new ActiveDataProvider([
            'query' => $find,
            'pagination' => $this->pagination,
            'sort' => [
                'attributes' => $this->generateSortAttributes($model->getNgRestConfig()),
            ]
        ]);
    }

    /**
     * Renders ActiveWindow Callback
     *
     * @return string Returns the rendered string from the callback.
     */
    public function actionActiveWindowCallback()
    {
        $config = $this->model->getNgRestConfig();
        $render = new RenderActiveWindowCallback();
        $ngrest = new NgRest($config);

        return $ngrest->render($render);
    }

    /**
     * Render ActiveWindow
     *
     * @return array An array with content, icon, label, title and requestDate
     */
    public function actionActiveWindowRender()
    {
        // generate ngrest active window
        $render = new RenderActiveWindow();
        $render->setItemId(Yii::$app->request->getBodyParam('itemId', false));
        $render->setActiveWindowHash(Yii::$app->request->getBodyParam('activeWindowHash', false));

        // process ngrest render view with config context
        $ngrest = new NgRest($this->model->getNgRestConfig());

        return [
            'content' => $ngrest->render($render),
            'icon' => $render->getActiveWindowObject()->getIcon(),
            'label' => $render->getActiveWindowObject()->getLabel(),
            'title' => $render->getActiveWindowObject()->getTitle(),
            'requestDate' => time(),
        ];
    }

    /**
     * Export Data.
     *
     * See {{luya\admin\ngrest\base\NgRestModel::ngRestExport()}} in order to configure custom export behavior for attributes.
     *
     * @uses integer header Whether header should be exported or not
     * @uses string type The type csv oder xlsx
     * @uses array attributes A list of attributes to export
     * @return array An array with the key `url` which contains the download path to the file
     * @throws ErrorException
     */
    public function actionExport()
    {
        $extension = null;
        $mime = null;
        $header = Yii::$app->request->getBodyParam('header', 1);
        $type = Yii::$app->request->getBodyParam('type');
        $attributes = Yii::$app->request->getBodyParam('attributes', []);
        $filter = Yii::$app->request->getBodyParam('filter', null);
        $fields = ArrayHelper::getColumn($attributes, 'value');

        if (!in_array($type, ['xlsx', 'csv'])) {
            throw new InvalidConfigException("Invalid export type");
        }

        switch (strtolower($type)) {
            case "csv":
                $mime = 'application/csv';
                $extension = 'csv';
                break;
            case "xlsx":
                $mime = 'application/vnd.ms-excel';
                $extension = 'xlsx';
                break;
        }

        if (!empty($filter)) {
            $filter = Html::encode($filter);
            $filtersList = $this->model->ngRestFilters();

            if (!array_key_exists($filter, $filtersList)) {
                throw new InvalidCallException("The requested filter '$filter' does not exists in the filter list.");
            }

            $query = $filtersList[$filter]->select($fields)->with($this->getWithRelation('export'));
        } else {
            $query = $this->prepareListQuery()->with($this->getWithRelation('export'))->select($fields);
        }

        $this->appendPoolWhereCondition($query);

        $exportFormatter = $this->model->ngRestExport();

        if (!empty($exportFormatter)) {
            $query = $this->formatExportValues($query, $exportFormatter);
            foreach ($fields as $fieldIndex => $fieldValue) {
                $fields[$fieldIndex] = $this->model->getAttributeLabel($fieldValue);
            }
        }

        $tempData = ExportHelper::$type($query, $fields, (bool) $header, ['sort' => empty($exportFormatter)]);

        $key = uniqid('ngrestexport', true);

        if (!Yii::$app->cache->set(['download', $key], $tempData, 60 * 60)) {
            throw new ErrorException("Unable to write the temporary file. Make sure the runtime folder is writeable.");
        }

        $menu = Yii::$app->adminmenu->getApiDetail($this->model->ngRestApiEndpoint());
        $route = str_replace("/index", "/export-download", $menu['route']);

        Yii::$app->session->set('tempNgRestFileName', Inflector::slug($this->model->tableName())  . '-export-'.date("Y-m-d-H-i").'.' . $extension);
        Yii::$app->session->set('tempNgRestFileMime', $mime);
        Yii::$app->session->set('tempNgRestFileKey', $key);

        $url = Url::toRoute(['/'.$route], true);
        $param = http_build_query(['key' => base64_encode($key), 'time' => time()]);

        if (StringHelper::contains('?', $url)) {
            $route = $url . "&" . $param;
        } else {
            $route = $url . "?" . $param;
        }
        return [
            'url' => $route,
        ];
    }

    /**
     * Format the ActiveQuery values based on formatter array definition see {{luya\admin\ngrest\base\NgRestModel::ngRestExport()}}.
     *
     * @param ActiveQuery $query
     * @param array $formatter
     * @return array
     * @since 3.9.0
     */
    private function formatExportValues(ActiveQuery $query, array $formatter)
    {
        Yii::$app->formatter->nullDisplay = '';
        $data = [];
        $rowId = 0;
        foreach ($query->batch() as $batch) {
            foreach ($batch as $model) {
                foreach ($formatter as $formatterAttribute => $formatAs) {
                    if (is_callable($formatAs)) {
                        $data[$rowId][$model->getAttributeLabel($formatterAttribute)] = call_user_func($formatAs, $model);
                    } else {
                        $data[$rowId][$model->getAttributeLabel($formatterAttribute)] = Yii::$app->formatter->format($model[$formatterAttribute], $formatAs);
                    }
                }
                $rowId++;
            }
        }

        return $data;
    }

    /**
     * Active Button
     *
     * @param string $hash The hash from the class name.
     * @param string|integer $id
     * @return array|boolean
     * @since 1.2.3
     */
    public function actionActiveButton($hash, $id)
    {
        $model = $this->findModel($id);

        return $model->handleNgRestActiveButton($hash);
    }

    /**
     * Run the active selection handler for the given item
     *
     * @param integer $index
     * @return array
     * @since 4.0.0
     * @throws NotFoundHttpException
     */
    public function actionActiveSelection($index)
    {
        $class = $this->modelClass;
        $items = $class::findAll(Yii::$app->request->getBodyParam('ids', [0]));

        /** @var NgRestModel $model */
        $model = new $class();

        $activeSelections = $model->getNgRestConfig()->getActiveSelections();

        if (!array_key_exists($index, $activeSelections)) {
            throw new NotFoundHttpException("Unable to find the active selection handler.");
        }

        $object = $activeSelections[$index];

        return $object->handle($items);
    }
}
