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
     * @var boolean Defines whether the automatic pagination should be enabled if more then 200 rows of data stored in this table or not. You can also
     * enable pagination by setting the pagination property like:
     *
     * ```php
     * public $pagination = ['defaultPageSize' => 100];
     * ```
     *
     * If its enabled like the example above, the {{$pageSize}} param is ignored.
     */
    public $autoEnablePagination = true;
    
    /**
     * @var integer When {{$autoEnablePagination}} is enabled this value will be used for page size. If you are enabling pagination by setting
     * the {{$pagination}} property `$pagination = ['defaultPageSize' => 100]` this {{$pageSize}} property will be ignored!
     * ```
     */
    public $pageSize = 100;
    
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
     * Enables the pagination for the current API for a given circumstances.
     *
     * @since 1.2.2
     */
    public function ensureAutoPagination()
    {
        // pagination is disabled by default, lets verfy if there are more then 400 rows in the table and auto enable
        if ($this->pagination === false && $this->autoEnablePagination) {
            if ($this->model->ngRestFind()->count() > ($this->pageSize*2)) {
                $this->pagination = ['defaultPageSize' => $this->pageSize];
            }
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
     *     'view' => ['images', 'files'],
     * ];
     * ```
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
     * @param string $actionName The action name like `index`, `list` or `view`.
     * @return array An array with relation names.
     * @since 1.2.2
     */
    public function getWithRelation($actionName)
    {
        $rel = $this->withRelations();
        
        foreach ($rel as $relationName) {
            // it seem to be the advance strucutre for given actions.
            if (is_array($relationName)) {
                return isset($rel[$actionName]) ?: [];
            }
        }
        // simple structure
        return $rel;
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
     * Make sure to call the parent implementation!
     *
     * @return \yii\db\ActiveQuery
     * @since 1.2.1
     */
    public function prepareIndexQuery()
    {
        /* @var $modelClass \yii\db\BaseActiveRecord */
        $modelClass = $this->modelClass;
        return $modelClass::ngRestFind()->with($this->getWithRelation('index'));
    }
    
    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = [
            'index' => [
                'class' => 'luya\admin\ngrest\base\actions\IndexAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'prepareActiveDataQuery' => [$this, 'prepareIndexQuery'],
            ],
            'list' => [ // for ngrest list
                'class' => 'luya\admin\ngrest\base\actions\IndexAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'prepareActiveDataQuery' => [$this, 'prepareIndexQuery'],
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
        $class = $this->modelClass;
        $model = $class::findOne((int) $id);
        
        if (!$model) {
            throw new NotFoundHttpException("Unable to find the Model for the given ID");
        }
        
        return $model;
    }
    
    /**
     * Unlock the useronline locker.
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
     * @return \yii\data\ActiveDataProvider
     */
    public function actionFullResponse()
    {
        $this->checkAccess('full-response');
        
        $query = Yii::$app->request->post('query');
        
        $find = $this->model->ngRestFullQuerySearch($query);
        
        return new ActiveDataProvider([
            'query' => $find,
            'pagination' => false,
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
        
        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
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

        $this->ensureAutoPagination();
        
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
        $render->setItemId(Yii::$app->request->post('itemId', false));
        $render->setActiveWindowHash(Yii::$app->request->post('activeWindowHash', false));
        
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
}
