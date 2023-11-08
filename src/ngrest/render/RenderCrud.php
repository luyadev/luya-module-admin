<?php

namespace luya\admin\ngrest\render;

use luya\admin\components\Auth;
use luya\admin\helpers\Angular;
use luya\admin\ngrest\base\NgRestModelInterface;
use luya\admin\ngrest\base\Plugin;
use luya\admin\ngrest\base\Render;
use luya\admin\ngrest\Config;
use luya\admin\ngrest\NgRest;
use luya\helpers\ArrayHelper;
use luya\helpers\Html;
use luya\helpers\Inflector;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\ViewContextInterface;
use yii\di\Instance;

/**
 * Render the Crud view.
 *
 * @property \luya\admin\ngrest\render\RenderCrudView $view
 * @property \luya\admin\ngrest\base\NgRestModelInterface $model
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class RenderCrud extends Render implements ViewContextInterface, RenderCrudInterface
{
    public const TYPE_LIST = 'list';

    public const TYPE_CREATE = 'create';

    public const TYPE_UPDATE = 'update';

    /**
     * @var string A description for the CRUD Title which is display below the title.
     * @since 3.2.0
     */
    public $description;

    /**
     * @var string The view file for create and update form rendering. Aliases are supported.
     * By default the location for this file is the viewPath {@see getViewPath()}
     *
     * **IMPORTANT NOTE.** You *can* use this property to customize the form, but only if you know what you're doing.
     * This approach **is discouraged** by LUYA maintainers. One of the strongest arguments for the LUYA admin is that you can
     * run composer update, and you get fresh updates, improvements UI fixes, new button s, etc. - if you override the view files a next update
     * can break the application, as there's no backward compatibility guarantee in view files and UI-controllers.
     *
     * @since 2.0.0
     */
    public $crudFormView = '_crudform';

    /**
     * @var string The view file for active window form rendering. Aliases are supported.
     * By default the location for this file is the viewPath {@see getViewPath()}
     *
     * **NOT RECOMMENDED** to override {@see `crudFormView`}
     *
     * @since 2.0.0
     */
    public $awFormView = '_awform';

    /**
     * @var \luya\admin\ngrest\render\RenderCrudView The view object for crud interface rendering.
     * It may be set as a full classname, config array or object {@see setView()} {@see getView()}
     */
    private $_view;

    /**
     * Returns the current view object.
     *
     * @return \luya\admin\ngrest\render\RenderCrudView
     */
    public function getView()
    {
        if ($this->_view === null) {
            $this->_view = new RenderCrudView();
        }

        return $this->_view;
    }

    /**
     * Set crud render view object
     *
     * @param \luya\admin\ngrest\render\RenderCrudView|string|array $view Object, classname string or Yii object config array
     * @since 2.0.0
     */
    public function setView($value)
    {
        $this->_view = Instance::ensure($value, '\yii\base\View');
    }

    /**
     * @inheritdoc
     */
    public function getViewPath()
    {
        return '@admin/views/ngrest';
    }

    /**
     * @inheritdoc
     */
    public function render()
    {
        $currentMenu = Yii::$app->adminmenu->getApiDetail($this->getConfig()->getApiEndpoint(), Yii::$app->request->get('pool'));

        if (!$currentMenu) {
            throw new InvalidConfigException("The current menu item does not exists or you have no permissions to access this area.");
        }

        return $this->view->render('crud', [
            'canCreate' => $this->can(Auth::CAN_CREATE),
            'canUpdate' => $this->can(Auth::CAN_UPDATE),
            'canDelete' => $this->can(Auth::CAN_DELETE),
            'config' => $this->config,
            'isInline' => $this->getIsInline(),
            'modelSelection' => $this->getModelSelection(),
            'hasActiveSelections' => count($this->config->getActiveSelections()),
            'relationCall' => $this->getRelationCall(), // this is currently only used for the crud relation view file, there for split the RenderCrud into two sepeare renderes.
            'currentMenu' => $currentMenu,
            'downloadAttributes' => $this->generateDownloadAttributes(),
        ], $this);
    }

    /**
     * Generates an array with all attributes an the corresponding label.
     *
     * @return array
     * @since 1.2.2
     */
    public function generateDownloadAttributes()
    {
        $exportAttributes = $this->model->ngRestExport();
        $fields = empty($exportAttributes) ? $this->model->attributes() : array_keys($exportAttributes);
        $attributes = [];
        foreach ($fields as $key) {
            $attributes[$key] = $this->model->getAttributeLabel($key);
        }

        asort($attributes);

        return $attributes;
    }

    // RenderCrudInterface

    private $_model;

    /**
     * @inheritdoc
     */
    public function setModel(NgRestModelInterface $model)
    {
        $this->_model = $model;
    }

    /**
     * @inheritdoc
     */
    public function getModel()
    {
        return $this->_model;
    }

    /**
     * @inheritdoc
     */
    public function canCreate()
    {
        return $this->can(Auth::CAN_CREATE);
    }

    /**
     * @inheritdoc
     */
    public function canUpdate()
    {
        return $this->can(Auth::CAN_UPDATE);
    }

    /**
     * @inheritdoc
     */
    public function canDelete()
    {
        return $this->can(Auth::CAN_DELETE);
    }

    private $_relationCall;

    /**
     * @inheritdoc
     */
    public function getRelationCall()
    {
        return $this->_relationCall;
    }

    /**
     * @inheritdoc
     */
    public function setRelationCall(array $options)
    {
        $this->_relationCall = $options;
    }

    private bool $_isInline = false;

    /**
     * @inheritdoc
     */
    public function getIsInline()
    {
        return $this->_isInline;
    }

    /**
     * @inheritdoc
     */
    public function setIsInline($inline)
    {
        $this->_isInline = $inline;
    }

    private $_modelSelection;

    /**
     * @inheritdoc
     */
    public function setModelSelection($selection)
    {
        $this->_modelSelection = $selection;
    }

    /**
     * @inheritdoc
     */
    public function getModelSelection()
    {
        return $this->_modelSelection;
    }

    private array $_settingButtonDefinitions = [];

    /**
     * @inheritdoc
     */
    public function setSettingButtonDefinitions(array $buttons)
    {
        $elements = [];
        foreach ($buttons as $config) {
            $innerContent = '<i class="material-icons">' . ArrayHelper::getValue($config, 'icon', 'extension') .'</i><span> '. $config['label'] . '</span>';

            $tagName = ArrayHelper::remove($config, 'tag', 'a');

            if (!array_key_exists('class', $config)) {
                $config['class'] = 'dropdown-item';
            }

            $elements[] = Html::tag($tagName, $innerContent, $config);
        }

        $this->_settingButtonDefinitions = $elements;
    }

    /**
     * Indicates whether the current plugin config is sortable or not.
     *
     * @param array $item
     * @return boolean
     * @since 2.0.0
     */
    public function isSortable(array $item)
    {
        $config = Config::createField($item);

        return $config->getSortField() ? true : false;
    }

    /**
     * Indicates whether the field should be hidden from the list.
     *
     * @param array $item
     * @return boolean
     * @since 2.0.0
     */
    public function isHiddenInList(array $item)
    {
        $config = Config::createField($item);

        return $config->hideInList;
    }


    /**
     * Returns an array with color properties of the ngRest field
     *
     * *cellColor* - the color of the CRUD table cell
     * *highlightColor* - the background color of the text or similar (will be implemented later)
     * *textColor* - the color of the text or similar (will be implemented later)
     *
     * @param array $item
     * @return array Returns an array with key `cellColor`.
     * @since 4.1.0
     */
    public function getColors(array $item)
    {
        $config = Config::createField($item);

        $cellColor = $config->cellColor;

        if ($cellColor === '' or $cellColor === null) {
            $cellColor = false;
        }

        return [
            'cellColor' => $cellColor
        ];
    }


    /**
     * Returns the icon name of the ngRest field
     *
     * @param array $item
     * @return mixed Returns the icon name based on https://material.io/icons or false if no icon is specified  for this field.
     * @since 4.2.0
     */
    public function getIcon(array $item)
    {
        $config = Config::createField($item);

        return empty($config->icon) ? false : $config->icon;
    }


    /**
     * @inheritdoc
     */
    public function getSettingButtonDefinitions()
    {
        return $this->_settingButtonDefinitions;
    }

    // methods used inside the view context: RenderCrudView

    /**
     * Returns the current order by state.
     *
     * @return string angular order by statements like `+id` or `-name`.
     */
    public function getOrderBy()
    {
        if ($this->getConfig()->getDefaultOrderField() === false) {
            return false;
        }

        return $this->getConfig()->getDefaultOrderDirection() . $this->getConfig()->getDefaultOrderField();
    }

    /**
     * Returns the primary key from the config.
     *
     * @return string
     */
    public function getPrimaryKey()
    {
        return implode(",", $this->config->getPrimaryKey());
    }

    /**
     * Returns the api endpoint, but can add appendix.
     *
     * @return string
     */
    public function getApiEndpoint($append = null)
    {
        if ($append) {
            $append = '/' . ltrim($append, '/');
        }

        return 'admin/'.$this->getConfig()->getApiEndpoint() . $append;
    }

    // generic methods

    private array $_canTypes = [];

    /**
     * Checks whether a given type can or can not.
     *
     * @param string $type
     * @return boolean
     */
    protected function can($type)
    {
        if (!array_key_exists($type, $this->_canTypes)) {
            $this->_canTypes[$type] = Yii::$app->auth->matchApi(Yii::$app->adminuser->getId(), $this->config->apiEndpoint, $type);
        }

        return $this->_canTypes[$type];
    }

    /**
     *
     * @param string $modelPrefix In common case its `item`.
     */
    public function getCompositionKeysForButtonActions($modelPrefix)
    {
        $output = [];
        foreach ($this->config->getPrimaryKey() as $key) {
            $output[] = $modelPrefix . '.' . $key;
        }

        return "[". implode(",", $output) . "]";
    }

    /**
     * Get the ng-show condition for a update or delete buttons
     *
     * Conditions needs to be defined in the model's ngRestScopes() along with fields definition
     * indexed by buttonCondition and provided as a string or a [field=>value] array.
     *
     * ```php
     * public function ngRestScopes()
     * {
     *     return [
     *       ['update', ['field1', 'field2', 'field3'], ['buttonCondition' => ['{created_by}' => Yii::$app->adminuser->id]] ],
     *       ['delete', true, ['buttonCondition' => ['{created_by}' => Yii::$app->adminuser->id]] ]
     *     ]
     * }
     * ```
     *
     * Conditions may be defined also in the model ngRestConfigOptions() along with other
     * options
     * Example :
     *
     * ```php
     * public function ngRestConfigOptions()
     * {
     *     return [
     *         // ...
     *         'buttonsCondition' => [
     *             [ ['update', 'delete'], '{created_by}=='. \Yii::$app->adminuser->id  ],
     *         ],
     *     ];
     * }
     * ```
     *
     * This will add an ng-Show = "item.created_by==1" for instance if the logged user is the admin.
     *
     * @param string $scope The scope aka button context like 'create' or 'update'
     * @return string Returns the condition with replaced field context like `item.create_id== 1` or  'true'
     * @throws InvalidConfigException
     * @since 4.0.0
     */
    public function getConfigButtonCondition($scope)
    {
        $buttonConditionConfigOption = $this->config->getOption('buttonCondition');

        // return empty string of no condition is defined
        if (empty($buttonConditionConfigOption)) {
            return '';
        }

        if (!is_array($buttonConditionConfigOption)) { // throw exception if configuration is wrong
            throw new InvalidConfigException(sprintf("Invalid buttonsCondition ngRestConfigOptions definition for '%s' scope.", $scope));
        }

        foreach ($buttonConditionConfigOption as $arrayConfig) {
            // take the first entry of the array config as the config scope
            // and check whether it applys to the given scppe
            $configScope = !is_array($arrayConfig[0]) ? [$arrayConfig[0]] : $arrayConfig[0];
            if (in_array($scope, $configScope)) {
                if (!isset($arrayConfig[1])) {
                    throw new InvalidConfigException("Invalid buttonsCondition ngRestConfigOptions definition. buttonsCondition must be an array with two elements similar to ngRestScopes. Ex. `[ ['update', 'delete'], '{fieldname} == 1'´]`");
                }
                return $arrayConfig[1];
            }
        }

        return '';
    }

    /**
     * Evaluates the condition from a list context. A condition like
     * `{field} == true` would return `item.field == true`.
     *
     * @param type $condition
     * @return string list context based condition
     */
    public function listContextVariablize($condition)
    {
        if (empty($condition)) {
            return 'true';
        }

        // prepend context to $field if in format '{fieldname}'
        return Angular::variablizeContext(Plugin::LIST_CONTEXT_PREFIX, $condition, false);
    }

    /*
     * OLD
     */

    private $_buttons;

    /**
     * collection all the buttons in the crud list.
     *
     * each items required the following keys (ngClick, icon, label):
     *
     * ```php
     * return [
     *     ['ngClick' => 'toggle(...)', 'icon' => 'fa fa-fw fa-edit', 'label' => 'Button Label']
     * ];
     * ```
     * @return array An array with all buttons for this crud
     * @throws InvalidConfigException
     */
    public function getButtons()
    {
        // if already assigned return the resutl
        if ($this->_buttons) {
            return $this->_buttons;
        }

        $buttons = [];

        // ngrest relation buttons
        foreach ($this->getConfig()->getRelations() as $rel) {
            $api = Yii::$app->adminmenu->getApiDetail($rel['apiEndpoint']);

            if (!$api) {
                throw new InvalidConfigException("The configured api relation '{$rel['apiEndpoint']}' does not exists in the menu elements. Maybe you have no permissions to access this API.");
            }

            $label = empty($rel['tabLabelAttribute']) ? "'{$rel['label']}'" : 'item.'.$rel['tabLabelAttribute'];

            $buttons[] = [
                'ngShow' => 'true',
                'ngClick' => 'addAndswitchToTab(item.'.$this->getPrimaryKey().', \''.$api['route'].'\', \''.$rel['arrayIndex'].'\', '.$label.', \''.$rel['modelClass'].'\')',
                'icon' => 'chrome_reader_mode',
                'label' => $rel['label'],
            ];
        }

        // get all activeWindows assign to the crud
        foreach ($this->getActiveWindows() as $hash => $config) {
            if ((isset($config['objectConfig']['permissionLevel']) && $config['objectConfig']['permissionLevel'] == '') || $this->can($config['objectConfig']['permissionLevel'] ?? Auth::CAN_UPDATE)) {
                $buttons[] = [
                    'ngShow' => $this->listContextVariablize($config['objectConfig']['condition'] ?? ''),
                    'ngClick' => 'getActiveWindow(\''.$hash.'\', '.$this->getCompositionKeysForButtonActions('item').')',
                    'icon' => $config['objectConfig']['icon'] ?? $config['icon'],
                    'label' => $config['objectConfig']['label'] ?? $config['label'],
                ];
            }
        }
        // add active buttons.
        foreach ($this->config->getActiveButtons() as $btn) {
            if (($btn['permissionLevel'] == '') || ($btn['permissionLevel'] !== '' && $this->can($btn['permissionLevel']))) {
                $buttons[] = [
                    'ngShow' => $this->listContextVariablize($btn['condition']),
                    'ngClick' => "callActiveButton('{$btn['hash']}', ".$this->getCompositionKeysForButtonActions('item').", \$event)",
                    'icon' => $btn['icon'],
                    'label' => $btn['label'],
                ];
            }
        }

        // check if deletable is enabled
        if ($this->config->isDeletable() && $this->can(Auth::CAN_DELETE)) {
            $buttons[] = [
                'ngShow' => $this->listContextVariablize($this->getConfigButtonCondition('delete')),
                'ngClick' => 'deleteItem('.$this->getCompositionKeysForButtonActions('item').')',
                'icon' => 'delete',
                'label' => '',
            ];
        }
        // do we have an edit button
        if (count($this->getFields('update')) > 0 && $this->can(Auth::CAN_UPDATE)) {
            $buttons[] = [
                'ngShow' => $this->listContextVariablize($this->getConfigButtonCondition('update')),
                'ngClick' => 'toggleUpdate('.$this->getCompositionKeysForButtonActions('item').')',
                'icon' => 'mode_edit',
                'label' => '',
            ];
        }

        $this->_buttons = $buttons;
        return $buttons;
    }

    /**
     * {@inheritDoc}
     */
    public function getActivePoolConfig()
    {
        $pools = $this->getModel()->ngRestPools();
        $pool = Yii::$app->request->get('pool');
        return $pools[$pool] ?? [];
    }

    /**
     * Generate the api query string for a certain context type
     *
     * @param string $type
     * @return string
     */
    public function apiQueryString($type)
    {
        // basic query
        $query = ['ngrestCallType' => $type];

        $fields = [];
        foreach ($this->model->primaryKey() as $n) {
            $fields[] = $n;
        }
        // see if we have fields for this type
        if (count($this->getFields($type)) > 0) {
            foreach ($this->getFields($type) as $field) {
                $fields[] = $field;
            }
        }
        // do we have extra fields to expand
        if ((is_countable($this->config->getPointerExtraFields($type)) ? count($this->config->getPointerExtraFields($type)) : 0) > 0) {
            $query['expand'] = implode(',', $this->config->getPointerExtraFields($type));
        }

        array_unique($fields);
        $query['fields'] = implode(",", $fields);

        if (Yii::$app->request->get('pool')) {
            $query['pool'] = Yii::$app->request->get('pool');
        }

        // return url decoded string from http_build_query
        return http_build_query($query, '', '&');
    }

    private array $_fields = [];

    /**
     * Short hand method to get all fields for a certain type context
     *
     * @param string $type
     * @return array
     */
    public function getFields($type)
    {
        if (!array_key_exists($type, $this->_fields)) {
            $fields = [];
            if ($this->config->hasPointer($type)) {
                foreach ($this->config->getPointer($type) as $item) {
                    $fields[] = $item['name'];
                }
            }
            $this->_fields[$type] = $fields;
        }

        return $this->_fields[$type];
    }

    /**
     * Short hand method to get all active windows (if available).
     *
     * @return array
     */
    public function getActiveWindows()
    {
        return ($activeWindows = $this->config->getPointer('aw')) ? $activeWindows : [];
    }

    /**
     * Generate an array with elements grouped by groups (...).
     *
     * @param string $pointer
     * @return array
     */
    public function forEachGroups($pointer)
    {
        $data = [];
        foreach ($this->evalGroupFields($this->config->getPointer($pointer)) as $group) {
            $data[] = [
                'fields' => $this->config->getFields($pointer, $group[0]),
                'name' => $group[1],
                'collapsed' => isset($group['collapsed']) ? (bool) $group['collapsed'] : false,
                'is_default' => isset($group['is_default']) ? (bool) $group['is_default'] : false,
            ];
        }

        return $data;
    }

    /**
     * Generate the HTML code for the plugin element based on the current context.
     *
     * @param array $element
     * @param string $configContext
     * @return string
     * @since 2.0
     */
    public function generatePluginHtml(array $element, $configContext)
    {
        if ($element['i18n'] && $configContext !== self::TYPE_LIST) {
            return $this->view->render('_crudform_i18n_pluginhtml', [
                'element' => $element,
                'configContext' => $configContext,
                'languages' => Yii::$app->adminLanguage->languages,
                'helpButtonHtml' => $this->createFieldHelpButton($element, $configContext),
                'isRequired' => $this->getModel()->isAttributeRequired($element['name']),
            ]);
        }

        $ngModel = $this->ngModelString($configContext, $element['name']);

        return $this->createFieldHelpButton($element, $configContext) .
            $this->renderElementPlugins($configContext, $element['type'], Inflector::slug($ngModel), $element['name'], $ngModel, $element['alias'], false);
    }

    /**
     * Render the input element
     *
     * @param string $configContext
     * @param array $typeConfig
     * @param string $uniqueId Example unique field id: id="id-50eef582a7330e93b86b55ffed379965"
     * @param string $attribute
     * @param string $ngRestModel
     * @param string $label
     * @param boolean $elmni18n
     * @return string The rendered element
     */
    public function renderElementPlugins($configContext, $typeConfig, $uniqueId, $attribute, $ngRestModel, $label, $elmni18n)
    {
        $args = $typeConfig['args'];
        $args['renderContext'] = $this;
        $obj = NgRest::createPluginObject($typeConfig['class'], $attribute, $label, $elmni18n, $args);

        if ($obj->readonly && $configContext == self::TYPE_UPDATE) {
            $html = $obj->renderList($uniqueId, $ngRestModel);
        } else {
            $method = 'render'.ucfirst($configContext);
            $html = $obj->$method($uniqueId, $ngRestModel);
        }

        // parsed the element output content to a string
        $content = is_array($html) ? implode(" ", $html) : $html;

        // wrapp a new tag around fields which are required.
        if ($configContext !== self::TYPE_LIST) {
            $isRequired = $this->getModel()->isAttributeRequired($attribute);
            if ($isRequired) {
                $content = '<span class="bold-form-label">'.$content.'</span>';
            }
        }

        // if read only, wrap from group around read only value:
        if ($obj->readonly && $configContext == self::TYPE_UPDATE) {
            $content = '<div class="form-group form-side-by-side">
                <div class="form-side form-side-label">'.$label.'</div>
                <div class="form-side">'.$content.'</div>
            </div>';
        }

        return $content;
    }

    /**
     * Get the ngRestModel string for a certain attribute
     *
     * @param string $configContext
     * @param string $attribute
     * @return string
     */
    public function ngModelString($configContext, $attribute)
    {
        return $configContext == self::TYPE_LIST ? 'item.'.$attribute : 'data.'.$configContext.'.'.$attribute;
    }

    /**
     * Generate the ngrest model for an i18n field
     *
     * @param string $configContext
     * @param string $attribute
     * @param string $lang
     * @return string
     */
    public function i18nNgModelString($configContext, $attribute, $lang)
    {
        $context = $configContext == self::TYPE_LIST ? "item." : "data.{$configContext}.";
        return $context . $attribute.'[\''.$lang.'\']';
    }

    /**
     * generate the field help button which is placed next to the element.
     *
     * @param array $element
     * @param string $configContext
     * @return string
     */
    private function createFieldHelpButton(array $element, $configContext)
    {
        if ($configContext !== self::TYPE_LIST) {
            return '<span ng-if="getFieldHelp(\''.$element['name'].'\')" class="help-button btn btn-icon btn-help" tooltip tooltip-expression="getFieldHelp(\''.$element['name'].'\')" tooltip-position="left"></span>';
        }

        return '';
    }

    /**
     * Generate an array for every group withing the given pointer elemenets.
     *
     * If there is no group definition, it will generate a "default" group.
     *
     * @param [type] $pointerElements
     * @return array
     */
    private function evalGroupFields($pointerElements)
    {
        $groups = [];
        if (!$pointerElements) {
            return [];
        }

        $names = [];
        foreach ($pointerElements as $elmn) {
            $names[$elmn['name']] = $elmn['name'];
        }

        foreach ($this->getConfig()->getAttributeGroups() as $group) {
            foreach ($group[0] as $item) {
                if (in_array($item, $names)) {
                    unset($names[$item]);
                }
            }
        }

        $groups[] = [$names, '__default', 'collapsed' => true, 'is_default' => true];


        return array_merge($groups, $this->getConfig()->getAttributeGroups());
    }
}
