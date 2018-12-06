<?php

namespace luya\admin\ngrest\base;

use Yii;
use yii\helpers\Inflector;
use yii\helpers\Html;
use yii\base\InvalidCallException;
use yii\base\ErrorException;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use luya\helpers\FileHelper;
use luya\helpers\Url;
use luya\helpers\ExportHelper;
use luya\admin\base\RestActiveController;
use luya\admin\models\UserOnline;
use luya\admin\ngrest\render\RenderActiveWindow;
use luya\admin\ngrest\render\RenderActiveWindowCallback;
use luya\admin\ngrest\NgRest;
use yii\web\NotFoundHttpException;
use yii\db\ActiveQuery;
use luya\helpers\ArrayHelper;
use luya\admin\ngrest\base\actions\IndexAction;
use luya\helpers\StringHelper;
use yii\db\ActiveQueryInterface;

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
     * @var string When a filter model is provided filter is enabled trough json request body, works only for index,list
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
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    
        if ($this->modelClass === null) {
            throw new InvalidConfigException("The property `modelClass` must be defined by the Controller.");
        }
    }
    
    /**
     * Auto add those relations to queries.
     * 
     * This can be either an array with relations which will be passed to `index, list and view` or an array with a subdefintion in order to define
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
     * Since version 1.2.3 it also checks if the $expand get param is provided for the given relations, otherwise
     * the relation will not be joined trough `with`. This reduces the database querie time.
     * 
     * @param string $actionName The action name like `index`, `list`, `search`, `relation-call`.
     * @return array An array with relation names.
     * @since 1.2.2
     */
    public function getWithRelation($actionName)
    {
        $rel = $this->withRelations();
        
        $expand = Yii::$app->request->get('expand', null);
        $relationPrefixes = [];
        foreach (StringHelper::explode($expand, ',', true, true) as $relation) {
            // check for subrelation dot notation.
            $relationPrefixes[] = current(explode(".", $relation));
        }

        // no expand param found, return empty join with array.
        if (empty($relationPrefixes)) {
            return [];
        }

        foreach ($rel as $relationName) {
            // it seem to be the advance strucutre for given actions.
            if (is_array($relationName) &&  isset($rel[$actionName])) {
                return $this->relationsFromExpand($rel[$actionName], $relationPrefixes);
            }
        }
        // simple structure
        return $this->relationsFromExpand($rel, $relationPrefixes);
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
     * @see {{prepareIndexQuery()}}
     * @return \yii\db\ActiveQuery
     * @since 1.2.2
     */
    public function prepareListQuery()
    {
        /* @var $modelClass \yii\db\BaseActiveRecord */
        $modelClass = $this->modelClass;
        return $modelClass::ngRestFind()->with($this->getWithRelation('list'));
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
                'class' => 'yii\rest\OptionsAction',
            ],
        ];
        
        if ($this->enableCors) {
            $actions['options']['class'] = 'luya\admin\ngrest\base\actions\OptionsAction';
        }
        
        return $actions;
    }
    
    private $_model;

    /**
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
     * @params integer|string $id The id to performe the findOne() method.
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
     * @param [type] $modelClass
     * @param [type] $id
     * @return void
     */
    public function findModelClassObject($modelClass, $id, $relationContext)
    {
        $keys = $modelClass::primaryKey();
        if (count($keys) > 1) {
            $values = explode(',', $id);
            if (count($keys) === count($values)) {
                return $this->findModelFromCondition(array_combine($keys, $values), $keys, $modelClass, $relationContext);
            }
        } elseif ($id !== null) {
            return $this->findModelFromCondition($id, $keys, $modelClass, $relationContext);
        }

        return false;
    }

    /**
     * This equals to the ActieRecord::findByCondition which is sadly a protected method.
     *  
     * @since 1.2.3
     * @return yii\db\ActiveRecord
     */
    protected function findModelFromCondition($condition, $primaryKey, $modelClass, $relationContext)
    {
        $condition = [$primaryKey[0] => is_array($condition) ? array_values($condition) : $condition];

        return $modelClass::find()->andWhere($condition)->with($this->getWithRelation($relationContext))->one();
    }
    
    /**
     * Unlock this API for the current logged in user.
     */
    public function actionUnlock()
    {
        UserOnline::unlock(Yii::$app->adminuser->id);
    }
    
    /**
     * Service Action provides mutliple CRUD informations.
     *
     * @return array
     */
    public function actionServices()
    {
        $this->checkAccess('services');
        
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
        return [
            'service' => $this->model->getNgRestServices(),
            '_hints' => $this->model->attributeHints(),
            '_settings' => $settings,
            '_locked' => [
                'data' => UserOnline::find()->select(['lock_pk', 'last_timestamp', 'u.firstname', 'u.lastname', 'u.id'])->joinWith('user as u')->where(['lock_table' => $modelClass::tableName()])->createCommand()->queryAll(),
                'userId' => Yii::$app->adminuser->id,
            ],
        ];
    }
    
    /**
     * Generate a response with pagination disabled.
     *
     * Search querys with Pagination will be handled by this action.
     *
     * @param string $query The search paramter, if empty post will be used.
     * @return \yii\data\ActiveDataProvider
     */
    public function actionSearch($query = null)
    {
        $this->checkAccess('search');
        
        if (empty($query)) {
            $query = Yii::$app->request->post('query');
        }
        
        $find = $this->model->ngRestFullQuerySearch($query);
        
        return new ActiveDataProvider([
            'query' => $find->with($this->getWithRelation('search')),
            'pagination' => $this->pagination,
        ]);
    }
    
    /**
     * Call the dataProvider for a foreign model.
     *
     * @param mixed $arrayIndex
     * @param mixed $id
     * @param string $modelClass The name of the model where the ngRestRelation is defined.
     * @throws InvalidCallException
     * @return \yii\data\ActiveDataProvider
     */
    public function actionRelationCall($arrayIndex, $id, $modelClass)
    {
        $this->checkAccess('relation-call');
        
        $modelClass = base64_decode($modelClass);
        $model = $modelClass::findOne((int) $id);
        
        if (!$model) {
            throw new InvalidCallException("unable to resolve relation call model.");
        }
        
        /** @var $query \yii\db\Query */
        $arrayItem = $model->ngRestRelations()[$arrayIndex];
        
        if ($arrayItem instanceof NgRestRelation) {
            $query = $arrayItem->getDataProvider();
        } else {
            $query = $arrayItem['dataProvider'];
        }
        
        if ($query instanceof ActiveQuery && !$query->multiple) {
            throw new InvalidConfigException("The relation defintion must be a hasMany() relation.");
        }
        
        if ($query instanceof ActiveQueryInterface) {
            $query->with($this->getWithRelation('relation-call'));
        }

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => $this->pagination,
        ]);
    }
    
    /**
     * Filter the Api response by a defined Filtername.
     *
     * @param string $filterName
     * @throws InvalidCallException
     * @return \yii\data\ActiveDataProvider
     */
    public function actionFilter($filterName)
    {
        $this->checkAccess('filter');
        
        $model = $this->model;
        
        $filterName = Html::encode($filterName);
        
        if (!array_key_exists($filterName, $model->ngRestFilters())) {
            throw new InvalidCallException("The requested filter does not exists in the filter list.");
        }
        
        return new ActiveDataProvider([
            'query' => $model->ngRestFilters()[$filterName],
            'pagination' => $this->pagination,
        ]);
    }
    
    /**
     * Renders the Callback for an ActiveWindow.
     *
     * @return string
     */
    public function actionActiveWindowCallback()
    {
        $this->checkAccess('active-window-callback');
        
        $config = $this->model->getNgRestConfig();
        $render = new RenderActiveWindowCallback();
        $ngrest = new NgRest($config);
    
        return $ngrest->render($render);
    }
    
    /**
     * Renders the index page of an ActiveWindow.
     *
     * @return array
     */
    public function actionActiveWindowRender()
    {
        $this->checkAccess('active-window-render');
        
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
     * Export the Data into a temp CSV.
     *
     * @return array
     * @throws ErrorException
     */
    public function actionExport()
    {
        $this->checkAccess('export');
        
        $header = Yii::$app->request->getBodyParam('header', 1);
        $type = Yii::$app->request->getBodyParam('type');
        $attributes = Yii::$app->request->getBodyParam('attributes', []);
        $fields = ArrayHelper::getColumn($attributes, 'value');
        
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
        
        $tempData = ExportHelper::$type($this->model->find()->select($fields), $fields, (bool) $header);
        
        $key = uniqid('ngre', true);
        
        $store = FileHelper::writeFile('@runtime/'.$key.'.tmp', $tempData);
        
        $menu = Yii::$app->adminmenu->getApiDetail($this->model->ngRestApiEndpoint());
        
        $route = $menu['route'];
        $route = str_replace("/index", "/export-download", $route);
        
        if ($store) {
            Yii::$app->session->set('tempNgRestFileName', Inflector::slug($this->model->tableName())  . '-export-'.date("Y-m-d-H-i").'.' . $extension);
            Yii::$app->session->set('tempNgRestFileMime', $mime);
            Yii::$app->session->set('tempNgRestFileKey', $key);
            return [
                'url' => Url::toRoute(['/'.$route, 'key' => base64_encode($key)]),
            ];
        }
        
        throw new ErrorException("Unable to write the temporary file. Make sure the runtime folder is writeable.");
    }

    /**
     * Trigger an Active Button handler.
     *
     * @param string $hash The hash from the class name.
     * @param string|integer $id
     * @return void
     * @since 1.2.3
     */
    public function actionActiveButton($hash, $id)
    {
        $this->checkAccess('active-button');
        $model = $this->findModel($id);

        return $model->handleNgRestActiveButton($hash);
    }
}
