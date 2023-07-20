<?php

namespace luya\admin\commands;

use luya\admin\base\BaseCrudController;
use luya\admin\ngrest\base\NgRestModelInterface;
use luya\helpers\FileHelper;
use luya\helpers\StringHelper;
use Yii;
use yii\console\Exception;
use yii\db\TableSchema;
use yii\helpers\Console;
use yii\helpers\Inflector;

/**
 * Console command to create a NgRest CRUD with Controller, API and Model based on a SQL table.
 *
 * @property string $moduleNameWithoutAdminSuffix Get the $moduleName without admin suffix (if any).
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class CrudController extends BaseCrudController
{
    /**
     * @var boolean Whether the core modules should be hidden from selection list or not.
     */
    public $hideCoreModules = true;

    /**
     * @var boolean Whether the frontend modules should be hidden from selection list or not.
     */
    public $hideFrontendModules = true;

    /**
     * @inheritdoc
     */
    public $defaultAction = 'create';

    /**
     * @var string The name of the module which should be used in order to generate the CRUD structure e.g `cmsadmin`.
     */
    public $moduleName;

    /**
     * @var string The name of the model in camelcase notation e.g `NavItem`.
     */
    public $modelName;

    /**
     * @var string The name of the API endpoint based on the modelName und moduleName selections e.g `api-cms-navitem`.
     */
    public $apiEndpoint;

    /**
     * @var string The name of the corresponding model database table e.g. `cms_navitem`.
     */
    public $dbTableName;

    /**
     * @var boolean Whether the i18n text fields will be casted or not.
     */
    public $enableI18n;

    /**
     * @inheritdoc
     */
    public function options($actionID)
    {
        return ['hideCoreModules', 'hideFrontendModules', 'moduleName', 'modelName', 'apiEndpoint', 'dbTableName', 'enableI18n'];
    }

    /**
     * @inheritdoc
     */
    public function optionAliases()
    {
        return ['hidecore' => 'hideCoreModules', 'hidefrontend' => 'hideFrontendModules'];
    }

    /**
     * Get the $moduleName without admin suffix (if any).
     *
     * @return mixed Return the module name without admin suffix.
     */
    public function getModuleNameWithoutAdminSuffix()
    {
        return preg_replace('/admin$/', '', $this->moduleName);
    }

    /**
     * Get the model name in lower case.
     *
     * @return string Model name in lower case
     */
    public function getModelNameLower()
    {
        return strtolower($this->modelName);
    }

    /**
     * Get the camelized model name.
     *
     * @return string Camelized model name
     */
    public function getModelNameCamelized()
    {
        return Inflector::camelize($this->modelName);
    }

    /**
     * Get the namepsace to the model.
     *
     * @return string The full namepsace with model name itself.
     */
    public function getAbsoluteModelNamespace()
    {
        return $this->getModelNamespace() . '\\models\\' . $this->getModelNameCamelized();
    }

    /**
     * Generate a suggestion for the API endpoint.
     *
     * @return string API endpoint suggestion
     */
    public function getApiEndpointSuggestion()
    {
        return 'api-'.$this->getModuleNameWithoutAdminSuffix().'-'.$this->getModelNameLower();
    }

    /**
     * Generate a suggestion for the model table name.
     *
     * @return string The model table suggestion.
     */
    public function getModelNameSuggestion()
    {
        return Inflector::camelize(StringHelper::replaceFirst($this->getModuleNameWithoutAdminSuffix(), '', $this->dbTableName));
    }

    private $_dbTableShema;

    /**
     * Get the database table schema.
     *
     * @return \yii\db\TableSchema The schmema object
     */
    public function getDbTableShema()
    {
        if ($this->_dbTableShema === null) {
            $this->_dbTableShema = Yii::$app->db->getTableSchema($this->dbTableName, true);
        }

        return $this->_dbTableShema;
    }

    /**
     * The module object.
     *
     * @return \luya\base\Module The module object itself could be the application object as well.
     */
    public function getModule()
    {
        return Yii::$app->getModule($this->moduleName);
    }

    /**
     * Get the base path of the module.
     *
     * @return string The module basepath.
     */
    public function getBasePath()
    {
        return $this->getModule()->basePath;
    }

    private $_modelBasePath;

    /**
     * Get the base path of the module.
     *
     * @return string The module basepath.
     */
    public function getModelBasePath()
    {
        if ($this->_modelBasePath === null) {
            return $this->getModule()->basePath;
        }

        return $this->_modelBasePath;
    }

    public function setModelBasePath($path)
    {
        $this->_modelBasePath = $path;
    }


    /**
     * Get the namepsace of the module.
     *
     * see {{luya\base\Module::getNamespace}}.
     *
     * @return string The module namespace.
     */
    public function getNamespace()
    {
        return $this->getModule()->getNamespace();
    }

    private $_modelNamespace;

    /**
     * Getter method for modelNamespace.
     *
     * @return string
     */
    public function getModelNamespace()
    {
        if ($this->_modelNamespace === null) {
            return $this->getModule()->getNamespace();
        }

        return $this->_modelNamespace;
    }

    /**
     * Setter method for modelNamepsace.
     * @param string $ns
     */
    public function setModelNamespace($ns)
    {
        $this->_modelNamespace = $ns;
    }

    /**
     * Get the controller route for the summary.
     *
     * @return string The summary route like module/controller/action
     */
    protected function getSummaryControllerRoute()
    {
        return strtolower($this->moduleName).'/'.Inflector::camel2id($this->getModelNameCamelized()).'/index';
    }

    public function ensureBasePathAndNamespace()
    {
        $nsItems = explode('\\', $this->getNamespace());
        // if there are more namespace paths than one, it means there is space for a subfolder models
        if (count($nsItems) > 1) {
            $items = explode(DIRECTORY_SEPARATOR, $this->getBasePath());
            $last = array_pop($items);
            // as now we assume we change directory to a subfolder, the removed folder name must be "admin".
            if ($last == 'admin') {
                array_pop($nsItems);
                $this->modelNamespace = implode('\\', $nsItems);
                $this->modelBasePath = implode(DIRECTORY_SEPARATOR, $items);
            }
        }
    }

    /**
     * Generate the API file content based on its view file.
     *
     * @param string $fileNamespace
     * @param string $className
     * @param string $modelClass
     * @return string
     */
    public function generateApiContent($fileNamespace, $className, $modelClass)
    {
        $alias = Inflector::humanize(Inflector::camel2words($className));
        return $this->view->render('@admin/commands/views/crud/create_api.php', [
            'namespace' => $fileNamespace,
            'className' => $className,
            'modelClass' =>  $modelClass,
            'luyaVersion' => $this->getGeneratorText('crud/create'),
            'alias' => $alias,
        ]);
    }

    /**
     * Generate the controller view file based on its view file.
     * @param string $fileNamespace
     * @param string $className
     * @param string $modelClass
     * @return string
     */
    public function generateControllerContent($fileNamespace, $className, $modelClass)
    {
        $alias = Inflector::humanize(Inflector::camel2words($className));
        return $this->view->render('@admin/commands/views/crud/create_controller.php', [
            'namespace' => $fileNamespace,
            'className' => $className,
            'modelClass' =>  $modelClass,
            'luyaVersion' => $this->getGeneratorText('crud/create'),
            'alias' => $alias,
        ]);
    }

    /**
     * Generate the model content based on its view file.
     *
     * @param string $fileNamepsace
     * @param string $className
     * @param string $apiEndpoint
     * @param \yii\db\TableSchema $schema
     * @param boolean $i18nFields
     * @return string
     */
    public function generateModelContent($fileNamepsace, $className, $apiEndpoint, TableSchema $schema, $i18nFields)
    {
        $alias = Inflector::humanize(Inflector::camel2words($className));
        $dbTableName = $schema->fullName;

        $fields = [];
        $textfields = [];
        $properties = [];
        $ngrestFieldConfig = [];
        foreach ($schema->columns as $k => $v) {
            $properties[$v->name] = $v->type;
            if ($v->isPrimaryKey) {
                continue;
            }
            $fields[] = $v->name;

            if ($v->phpType == 'string') {
                $textfields[] = $v->name;
            }

            if ($v->type == 'text') {
                $ngrestFieldConfig[$v->name] = 'textarea';
            }
            if ($v->type == 'string') {
                $ngrestFieldConfig[$v->name] = 'text';
            }
            if ($v->type == 'integer' || $v->type == 'bigint' || $v->type == 'smallint' || $v->type == 'tinyint') {
                $ngrestFieldConfig[$v->name] = 'number';
            }
            if ($v->type == 'decimal' || $v->type == 'float' || $v->type == 'double') {
                $ngrestFieldConfig[$v->name] = 'decimal';
            }
            if ($v->type == 'boolean') {
                $ngrestFieldConfig[$v->name] = 'toggleStatus';
            }
        };

        return $this->view->render('@admin/commands/views/crud/create_model.php', [
            'namespace' => $fileNamepsace,
            'className' => $className,
            'luyaVersion' => $this->getGeneratorText('crud/create'),
            'apiEndpoint' => $apiEndpoint,
            'dbTableName' => $dbTableName,
            'fields' => $fields,
            'textFields' => $textfields,
            'rules' => $this->generateRules($schema),
            'labels' => $this->generateLabels($schema),
            'properties' => $properties,
            'ngrestFieldConfig' => $ngrestFieldConfig,
            'i18nFields' => $i18nFields,
            'alias' => $alias,
        ]);
    }

    /**
     * Generate the block build summary based on its view file.
     *
     * @param string $apiEndpoint
     * @param string $apiClassPath
     * @param string $humanizeModelName
     * @param string $controllerRoute
     * @return string
     */
    public function generateBuildSummary($apiEndpoint, $apiClassPath, $humanizeModelName, $controllerRoute)
    {
        return $this->view->render('@admin/commands/views/crud/build_summary.php', [
            'apiEndpoint' => $apiEndpoint,
            'apiClassPath' => $apiClassPath,
            'humanizeModelName' => $humanizeModelName,
            'controllerRoute' => $controllerRoute,
        ]);
    }

    /**
     * Create Ng-Rest-Model, Controller and API for an existing database table.
     *
     * @return number
     */
    public function actionCreate()
    {
        // 1. ask for module
        if ($this->moduleName === null) {
            Console::clearScreenBeforeCursor();
            $this->moduleName = $this->selectModule(['onlyAdmin' => $this->hideFrontendModules, 'hideCore' => $this->hideCoreModules, 'text' => 'Select the module where the CRUD files should be saved:']);
        }

        // 2. ask for sql table
        if ($this->dbTableName === null) {
            $this->dbTableName = $this->prompt('Enter the name of the database table to generate the model for (? to see all tables):', ['required' => true, 'validator' => function ($input, &$error) {
                if ($input == '?') {
                    foreach ($this->getSqlTablesArray() as $table) {
                        $this->outputInfo("- " . $table);
                    }
                    return false;
                }

                if (!isset($this->getSqlTablesArray()[$input])) {
                    $error = "Table '$input' does not exists. Type '?' to see all tables.";
                    return false;
                }

                return true;
            }]);
        }

        // 3. ask for model name
        if ($this->modelName === null) {
            $modelSelection = true;
            while ($modelSelection) {
                $modelName = $this->prompt('Model Name (e.g. Album):', ['required' => true, 'default' => $this->getModelNameSuggestion()]);
                $camelizeModelName = Inflector::camelize($modelName);
                if ($modelName !== $camelizeModelName) {
                    if ($this->confirm("We have camelized the model name to '$camelizeModelName'. Do you want to continue with this name?")) {
                        $modelName = $camelizeModelName;
                        $modelSelection = false;
                    }
                } else {
                    $modelSelection = false;
                }
                $this->modelName = $modelName;
            }
        }

        // 4. ask for API endpoint name
        if ($this->apiEndpoint === null) {
            $this->apiEndpoint = $this->prompt('Api Endpoint:', ['required' => true, 'default' => $this->getApiEndpointSuggestion()]);
        }

        // 5. ask for i18n
        if ($this->enableI18n === null) {
            $this->enableI18n = $this->confirm("Would you like to enable i18n field input for text fields? It is only required for multilingual pages.");
        }

        $this->ensureBasePathAndNamespace();

        $files = [];

        // API content

        $files['api'] = [
            'path' => $this->getBasePath() . DIRECTORY_SEPARATOR . 'apis',
            'fileName' => $this->getModelNameCamelized() . 'Controller.php',
            'content' => $this->generateApiContent($this->getNamespace() . '\\apis', $this->getModelNameCamelized() . 'Controller', $this->getAbsoluteModelNamespace()),
        ];

        // controller

        $files['controller'] = [
            'path' =>  $this->getBasePath() . DIRECTORY_SEPARATOR . 'controllers',
            'fileName' => $this->getModelNameCamelized() . 'Controller.php',
            'content' => $this->generateControllerContent($this->getNamespace() . '\\controllers', $this->getModelNameCamelized() . 'Controller', $this->getAbsoluteModelNamespace()),
        ];

        // model

        $files['model'] = [
            'path' =>  $this->getModelBasePath() . DIRECTORY_SEPARATOR . 'models',
            'fileName' => $this->getModelNameCamelized() . '.php',
            'content' => $this->generateModelContent(
                $this->getModelNamespace() . '\\models',
                $this->getModelNameCamelized(),
                $this->apiEndpoint,
                $this->getDbTableShema(),
                $this->enableI18n
            ),
        ];

        foreach ($files as $file) {
            $this->generateFile($file);
        }

        return $this->outputSuccess($this->generateBuildSummary($this->apiEndpoint, $this->getNamespace() . '\\apis\\' . $this->getModelNameCamelized() . 'Controller', $this->getModelNameCamelized(), $this->getSummaryControllerRoute()));
    }

    protected function generateFile(array $file)
    {
        FileHelper::createDirectory($file['path']);
        if (file_exists($file['path'] . DIRECTORY_SEPARATOR . $file['fileName'])) {
            if (!$this->confirm("The File '{$file['fileName']}' already exists. Do you want to override the existing file?")) {
                return false;
            }
        }

        if (FileHelper::writeFile($file['path'] . DIRECTORY_SEPARATOR . $file['fileName'], $file['content'])) {
            $this->outputSuccess("Wrote file '{$file['fileName']}'.");
        } else {
            $this->outputError("Error while writing file '{$file['fileName']}'.");
        }
    }

    /**
     * Generate only the NgRest model
     *
     * @param string $model Provide
     * @throws Exception
     */
    public function actionModel($model = null)
    {
        if (!$model) {
            $model = $this->prompt('Namespaced path to the NgRest model (e.g. "app\models\Users"):');
        }
        $object = Yii::createObject(['class' => $model]);

        if (!$object instanceof NgRestModelInterface) {
            throw new Exception("Model must be instance of NgRestModelInterface.");
        }

        $reflector = new \ReflectionClass($model);
        $fileName = $reflector->getFileName();
        $path = dirname($fileName);
        $apiEndpoint = $object->ngrestApiEndpoint();
        $i18n = !empty($object->i18n);
        $data = [
            'path' =>  $path,
            'fileName' => basename($fileName),
            'content' => $this->generateModelContent(
                $reflector->getNamespaceName(),
                $reflector->getShortName(),
                $apiEndpoint,
                Yii::$app->db->getTableSchema($object->tableName(), true),
                $i18n
            ),
        ];

        $this->generateFile($data);
    }
}
