<?php

namespace luya\admin\ngrest\base;

use Exception;
use luya\admin\components\Auth;
use luya\admin\Module;
use luya\admin\ngrest\NgRest;
use luya\admin\ngrest\render\RenderCrud;
use Yii;
use yii\base\InvalidConfigException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * Base Controller for all NgRest Controllers.
 *
 * @property NgRestModel $model The model based from the modelClass instance
 * @property string $description A text descirption for the CRUD which is display below the CRUD title
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class Controller extends \luya\admin\base\Controller
{
    /**
     * @inheritdoc
     */
    public $layout = false;

    /**
     * @var string Defines the related model for the NgRest Controller. The full qualified model name is required.
     *
     * ```php
     * public $modelClass = 'admin\models\User';
     * ```
     */
    public $modelClass;

    /**
     * @var boolean Disables the permission
     */
    public $disablePermissionCheck = true;

    /**
     * @var array Define global ngrest controller buttons you can choose in the drop down menu of an ngrest page.
     *
     * ```php
     * 'globalButtons' => [
     *     ['icon' => 'extension', 'label' => 'My Button', 'ng-click' => 'callMyFunction()'],
     * ];
     * ```
     *
     * An example for using the global buttons could be an action inside the controller
     *
     * ```php
     * class MyCrudController extends Controller
     * {
     *     public $modelClass = 'app\modules\myadmin\models\MyModel';
     *
     *     public $globalButtons = [
     *	       [
     *              'icon' => 'file_download',
     *              'label' => 'XML Download',
     *              'ui-sref' => "custom({templateId:'myadmin/mycrudcontroller/the-action'})"
     *         ]
     *     ];
     *
     *     public function actionTheAction()
     *     {
     *         return $this->render('hello world!');
     *     }
     * }
     * ```
     *
     * Properties to make links with angular:
     *
     * + ui-sref: custom({templateId:'myadmin/mycrudcontroller/the-action'}) (display the view without module navigation)
     * + ui-sref: default.route({moduleRouteId:'mymodule', controllerId:'mycrudcontroller', actionId:'the-action'}); (display the view inside the default layout)
     * + ng-href: 'katalogadmin/produkt/xml-download' (an example if you like to use yii response sendContentAsFile)
     */
    public $globalButtons = [];

    /**
     * @var boolean Whether the CRUD dropdown shows a button with "Delete all data" which will then truncate the whole table content. Usefull for tables
     * which contain only temporary informations like logs and errors. If the user does not have delete permission, the button is hidden. In order to
     * make the clear buutton (truncates all the data) work, the {{luya\admin\ngrest\base\Api::$truncateAction}} must be enabled.
     * @since 3.0.0
     */
    public $clearButton = false;

    /**
     * @var string|array|\luya\admin\ngrest\render\RenderCrudInterface
     *
     * You can customize crud rendering using this property. To do so you may use standard `\luya\admin\ngrest\render\RenderCrud`, but customize
     * it's `view` property. In this case you may simply omit the `class` key, like this:
     *
     * ```
     * public $renderCrud = [
     *     'view' => '\mymodule\admin\render\MyCustomCrudView',
     * ];
     * ```
     *
     * Or you can override `\luya\admin\ngrest\render\RenderCrud` with your custom class, let say `MyCustomCrud`, and provide it within `class` key.
     *
     * ```
     * public $renderCrud = [
     *    'class' => '\mymodule\admin\render\MyCustomCrud',
     *    'view' => '\mymodule\admin\render\MyCustomCrudView',
     * ];
     * ```
     *
     * If you omit the `view` property, then `\luya\admin\ngrest\render\RenderCrudView` class object will be used.
     *
     * Possibly, you may need to do something very exotic (may be risky, though). In such case you'll need to create your
     * implementation of `\luya\admin\ngrest\render\RenderCrudInterface`.
     *
     * For more information {@see \luya\admin\ngrest\render\RenderCrud} {@see \luya\admin\ngrest\render\RenderCrudView}
     *
     * @since 2.0.0
     */
    public $renderCrud = RenderCrud::class;

    private $_description;

    /**
     * Setter method for description
     *
     * @param string $description
     * @since 3.2.0
     */
    public function setDescription($description)
    {
        $this->_description = $description;
    }

    /**
     * Getter method for description
     *
     * @return string
     * @since 3.2.0
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->modelClass === null) {
            throw new InvalidConfigException("The property `modelClass` must be defined by the Controller.");
        }
        if (is_array($this->renderCrud) && empty($this->renderCrud['class'])) {
            $this->renderCrud['class'] = RenderCrud::class;
        }
    }

    private $_model;

    /**
     * Get Model Object
     *
     * @return \luya\admin\ngrest\base\NgRestModel
     */
    public function getModel()
    {
        if ($this->_model === null) {
            $this->_model = Yii::createObject($this->modelClass);
        }

        return $this->_model;
    }

    /**
     * Render the ngrest default index template.
     *
     * @param string $inline
     * @param string $relation
     * @param string $arrayIndex
     * @param string $modelClass
     * @param string $modelSelection
     * @throws Exception
     * @return string
     */
    public function actionIndex($inline = false, $relation = false, $arrayIndex = false, $modelClass = false, $modelSelection = false)
    {
        $apiEndpoint = $this->model->ngRestApiEndpoint();

        $config = $this->model->getNgRestConfig();

        $userSortSettings = Yii::$app->adminuser->identity->setting->get('ngrestorder.admin/'.$apiEndpoint, false);

        if ($userSortSettings && is_array($userSortSettings) && $config->getDefaultOrder() !== false) {
            $config->defaultOrder = [$userSortSettings['field'] => $userSortSettings['sort']];
        }

        // check if delete permissions exists for the current user in this crud.
        if ($this->clearButton && Yii::$app->auth->matchApi(Yii::$app->adminuser->getId(), $config->apiEndpoint, Auth::CAN_DELETE)) {
            array_push($this->globalButtons, [
                'icon' => 'delete',
                'label' => Module::t('ngrest_delete_all_button_label'),
                'ng-click' => "clearData()"
            ]);
        }

        // generate crud renderer
        $crud = Yii::createObject($this->renderCrud);
        $crud->description = $this->description;
        $crud->setModel($this->model);
        $crud->setSettingButtonDefinitions($this->globalButtons);
        $crud->setIsInline($inline);
        $crud->setModelSelection($modelSelection);
        if ($relation && is_scalar($relation) && $arrayIndex !== false && $modelClass !== false) {
            $crud->setRelationCall(['id' => $relation, 'arrayIndex' => $arrayIndex, 'modelClass' => $modelClass]);
        }

        // generate ngrest object from config and render renderer
        $ngrest = new NgRest($config);
        return $ngrest->render($crud);
    }

    /**
     * Get the file content response for a given key.
     *
     * @param string $key
     * @throws ForbiddenHttpException
     * @return \yii\web\Response
     */
    public function actionExportDownload($key)
    {
        $sessionkey = Yii::$app->session->get('tempNgRestFileKey');
        $fileName = Yii::$app->session->get('tempNgRestFileName');
        $mimeType = Yii::$app->session->get('tempNgRestFileMime');

        if ($sessionkey !== base64_decode($key)) {
            throw new ForbiddenHttpException('Invalid download key.');
        }

        $content = Yii::$app->cache->get(['download', $sessionkey]);

        if (!$content) {
            throw new NotFoundHttpException();
        }

        Yii::$app->session->remove('tempNgRestFileKey');
        Yii::$app->session->remove('tempNgRestFileName');
        Yii::$app->session->remove('tempNgRestFileMime');

        Yii::$app->cache->delete(['download', $sessionkey]);

        return Yii::$app->response->sendContentAsFile($content, $fileName, ['mimeType' => $mimeType]);
    }
}
