<?php

namespace luya\admin;

use Yii;
use luya\console\interfaces\ImportControllerInterface;
use luya\base\CoreModuleInterface;
use luya\admin\components\AdminLanguage;
use luya\admin\components\AdminUser;
use luya\admin\components\AdminMenu;
use luya\admin\components\Auth;
use luya\admin\components\AdminMenuBuilder;
use luya\admin\importers\AuthImporter;
use luya\admin\importers\FilterImporter;
use luya\admin\importers\PropertyImporter;
use luya\admin\importers\StorageImporter;
use luya\admin\filesystem\LocalFileSystem;

/**
 * Admin Module.
 *
 * The Admin Module provides options to configure. In order to add the Admin module to your config use:
 *
 * ```php
 * 'modules' => [
 *     // ...
 *     'admin' => [
 *         'class' => 'luya\admin\Module',
 *         'secureLogin' => true,
 *     ]
 * ]
 * ```
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
final class Module extends \luya\admin\base\Module implements CoreModuleInterface
{
    /**
     * This event gets trigger before some trys to download a file.
     *
     * @var string Event Name
     */
    const EVENT_BEFORE_FILE_DOWNLOAD = 'EVENT_BEFORE_FILE_DOWNLOAD';
    
    /**
     * @var boolean Whether CORS filter is enabled or not. By default its disabled but you can enable this option
     * when using LUYA admin APIs for usage trough direct xhr requests from another Domain.
     */
    public $cors = false;
    
    /**
     * @var string The default language for the admin interrace (former known as luyaLanguage).
     * Currently supported: en, de, ru, es, fr, ua, it, el, vi, pt, fa, cn, nl, pl
     */
    public $interfaceLanguage = 'en';
    
    /**
     * @var array Available translation messages.
     */
    public $interfaceLanguageDropdown = [
        'en' => 'English',
        'de' => 'Deutsch',
        'ru' => 'Pусский',
        'es' => 'Español',
        'fr' => 'Français',
        'ua' => 'Українська',
        'it' => 'Italiano',
        'el' => 'Ελληνικά',
        'vi' => 'Việt Nam',
        'pl' => 'Polski',
        'pt' => 'Português',
        'tr' => 'Türkçe',
        'fa' => 'فارسی',
        'cn' => '中文简体',
        'nl' => 'Dutch',
    ];
    
    /**
     * @array Provide dashboard objects from last user logins.
     */
    public $dashboardObjects = [
        [
            'class' => 'luya\admin\dashboard\ListDashboardObject',
            'template' => '<li class="list-group-item" ng-repeat="item in data">{{item.user.firstname}} {{item.user.lastname}}<span class="badge badge-info float-right">{{item.maxdate * 1000 | date:\'short\'}}</span></li>',
            'dataApiUrl' => 'admin/api-admin-common/last-logins',
            'title' => ['admin', 'dashboard_lastlogin_title'],
        ],
    ];
    
    /**
     * @var boolean Enables a 2FA system before logging into the admin panel sending a token to the given email adress of the user. If the system is not able to send
     * mails (No configuration or missconfiguration) then you are not able to login. You should test the mail system before enabling secureLogin. To test your smtp
     * connection you can use `./vendor/bin/luya health/mailer` command.
     */
    public $secureLogin = false;
    
    /**
     * @var boolean Whether each json rest response contains an unparsable cruft in order to prevent JSON Vulnerabilities.
     * @since 1.2.0
     */
    public $jsonCruft = true;
    
    /**
     * @var boolean If enabled an user can only change the email adresse by entering the secure code which is sent to the users given (current) email adresse.
     * @since 1.2.0
     */
    public $emailVerification = false;
    
    /**
     * @var integer If {{luya\admin\Module::$emailVerification}} is enabled this property defines the number seconds until the validation token expires.
     * @since 1.2.0
     */
    public $emailVerificationTokenExpirationTime = 600;
    
    /**
     * @var boolean If enabled, the admin user passwords require strength input with special chars, lower, upper, digits and numbers. If disabled just a min length of 8 chars is required.
     * @since 1.1.1
     */
    public $strongPasswordPolicy = true;

    /**
     * @var integer The number of attempts a user can make without knowing the login email. Clearing the session cookie
     * will allow next 20 attempts. But if an user email is known the attempt will swap to a user based attempt lockout handled by {{luya\admin\Module::$loginUserAttemptCount}}.
     * @since 1.2.0
     */
    public $loginSessionAttemptCount = 15;
    
    /**
     * @var integer If the session based {{luya\admin\Module::$loginSessionAttemptCount}} expire the user is locked out for this given time in seconds, defaults to 30min.
     * @since 1.2.0
     */
    public $loginSessionAttemptLockoutTime = 1800;
    
    /**
     * @var integer When the username is identified correctly this property limit number of attempts for the given user and lock out the user for a given time defined in {{luya\admin\Module::$loginUserAttemptLockoutTime}}.
     * The {{luya\admin\Module::$loginUserAttemptCount}} stores the login attempts in the database. Keep in mind that the {{luya\admin\Module::$loginSessionAttemptCount}} can lock out the user before or while entering a wrong password.
     * @since 1.2.0
     */
    public $loginUserAttemptCount = 7;
    
    /**
     * @var integer When the {{luya\admin\Module::$loginUserAttemptCount}} exceeded the number of seconds where the user is locked out, defaults to 30 min.
     * @since 1.2.0
     */
    public $loginUserAttemptLockoutTime = 1800;
    
    /**
     * @var integer When {{luya\admin\Module::$secureLogin}} is enabled a secure token is sent to the users email, the expiration time is defined in seconds and defaults to 10 min.
     * @since 1.2.0
     */
    public $secureTokenExpirationTime = 600;
    
    /**
     * @var integer The number of seconds inactivity until the user is logged out.
     * @since 1.2.0
     */
    public $userIdleTimeout = 1800;
    
    /**
     * @var integer The number of rows which should be transferd for each request.
     */
    public $proxyRowsPerRequest = 100;

    /**
     * @var integer The expiration timeout for a proxy build in seconds. Default value is 1800 seconds which is 30 minutes.
     */
    public $proxyExpirationTime = 6200;
    
    /**
     * @var array A configuration array with all tags shipped by default with the admin module.
     */
    public $tags = [
        'file' => ['class' => 'luya\admin\tags\FileTag'],
    ];
    
    /**
     * @var array The available api endpoints within the admin module.
     */
    public $apis = [
        'api-admin-logger' => 'luya\admin\apis\LoggerController',
        'api-admin-common' => 'luya\admin\apis\CommonController',
        'api-admin-remote' => 'luya\admin\apis\RemoteController',
        'api-admin-storage' => 'luya\admin\apis\StorageController',
        'api-admin-menu' => 'luya\admin\apis\MenuController',
        'api-admin-timestamp' => 'luya\admin\apis\TimestampController',
        'api-admin-search' => 'luya\admin\apis\SearchController',
        'api-admin-user' => 'luya\admin\apis\UserController',
        'api-admin-apiuser' => 'luya\admin\apis\ApiUserController',
        'api-admin-group' => 'luya\admin\apis\GroupController',
        'api-admin-lang' => 'luya\admin\apis\LangController',
        'api-admin-effect' => 'luya\admin\apis\EffectController',
        'api-admin-filter' => 'luya\admin\apis\FilterController',
        'api-admin-tag' => 'luya\admin\apis\TagController',
        'api-admin-proxymachine' => 'luya\admin\apis\ProxyMachineController',
        'api-admin-proxybuild' => 'luya\admin\apis\ProxyBuildController',
        'api-admin-proxy' => 'luya\admin\apis\ProxyController',
        'api-admin-config' => 'luya\admin\apis\ConfigController',
    ];

    /**
     * @var array An array with all apis from every module, this property is assigned by the {{luya\web\Bootstrap::run()}} method.
     * @since 1.2.2
     */
    public $apiDefintions = [];
    
    /**
     * @var array This property is used by the {{luya\web\Bootstrap::run()}} method in order to set the collected asset files to assign.
     */
    public $assets = [];
    
    /**
     * @var array This property is used by the {{luya\web\Bootstrap::run()}} method in order to set the collected menu items from all admin modules and build the menu.
     */
    public $moduleMenus = [];
    
    public static function onLoad()
    {
        self::registerTranslation('admin*', '@admin/messages', [
            'admin' => 'admin.php',
        ]);
    }
    
    /**
     * @inheritdoc
     */
    public function getUrlRules()
    {
        $rules = [
            ['pattern' => 'file/<id:\d+>/<hash:\w+>/<fileName:(.*?)+>', 'route' => 'admin/file/download'],
            ['pattern' => 'admin', 'route' => 'admin/default/index'],
            ['pattern' => 'admin/login', 'route' => 'admin/login/index'],
        ];
        
        foreach ($this->apiDefintions as $definition) {
            $definition['class'] = 'luya\admin\components\UrlRule';
            $definition['cacheFlag'] = Yii::$app->request->isAdmin;
            $rules[] = $definition;
        }
        
        return $rules;
    }
    
    /**
     * Returns all Asset files to registered in the administration interfaces.
     *
     * As the adminstration UI is written in angular, the assets must be pre assigned to the adminisration there for the `getAdminAssets()` method exists.
     *
     * ```php
     * public function getAdminAssets()
     * {
     *     return [
     *          'luya\admin\assets\Main',
     *          'luya\admin\assets\Flow',
     *     ];
     * }
     * ```
     *
     * @return array An array with with assets files where the array has no key and the value is the path to the asset class.
     */
    public function getAdminAssets()
    {
        return [
            'luya\admin\assets\Main',
            'luya\admin\assets\AngularI18n',
        ];
    }

    /**
     * Returns all message identifier for the current module which should be assigned to the javascript admin interface.
     *
     * As the administration UI is written in angular, translations must also be available in different javascript section of the page.
     *
     * The response array of this method returns all messages keys which will be assigned:
     *
     * Example:
     *
     * ```php
     * public function getJsTranslationMessages()
     * {
     *     return ['js_ngrest_rm_page', 'js_ngrest_rm_confirm', 'js_ngrest_error'],
     * }
     * ```
     *
     * Assuming the aboved keys are also part of the translation messages files.
     *
     * @return array An array with values of the message keys based on the Yii translation system.
     */
    public function getJsTranslationMessages()
    {
        return [
            'js_ngrest_rm_page', 'js_ngrest_rm_confirm', 'js_ngrest_error', 'js_ngrest_rm_update', 'js_ngrest_rm_success', 'js_tag_exists', 'js_tag_success', 'js_admin_reload', 'js_dir_till', 'js_dir_set_date', 'js_dir_table_add_row', 'js_dir_table_add_column', 'js_dir_image_description',
            'js_dir_no_selection', 'js_dir_image_upload_ok', 'js_dir_image_filter_error', 'js_dir_upload_wait', 'js_dir_manager_upload_image_ok', 'js_dir_manager_rm_file_confirm', 'js_dir_manager_rm_file_ok', 'js_zaa_server_proccess',
            'ngrest_select_no_selection', 'js_ngrest_toggler_success', 'js_filemanager_count_files_overlay', 'js_link_set_value', 'js_link_not_set', 'js_link_change_value', 'aws_changepassword_succes', 'js_account_update_profile_success', 'layout_filemanager_remove_dir_not_empty',
            'ngrest_button_delete', 'layout_btn_reload', 'js_dir_manager_rm_file_confirm_title', 'ngrest_crud_search_text', 'js_dir_manager_rm_folder_confirm_title', 'js_pagination_page',
        ];
    }
    
    private $_jsTranslations = [];
    
    /**
     * Getter method for the js translations array.
     *
     * @return array An array with all translated messages to store in the and access from the admin js scripts.
     */
    public function getJsTranslations()
    {
        $translations = [];
        foreach ($this->_jsTranslations as $module => $data) {
            foreach ($data as $key) {
                $translations[$key] = Yii::t($module, $key, [], Yii::$app->language);
            }
        }
        return $translations;
    }
    
    /**
     * Setter for js translations files.
     *
     * This setter method is used by the {{luya\web\Bootstrap::run()}} to assign all js transaltion files from the admin modules.
     *
     * @param array $translations
     */
    public function setJsTranslations(array $translations)
    {
        $this->_jsTranslations = $translations;
    }
    
    /**
     * Get the admin module interface menu.
     *
     * @see \luya\admin\base\Module::getMenu()
     * @return \luya\admin\components\AdminMenuBuilderInterface Get the menu builder object.
     */
    public function getMenu()
    {
        return (new AdminMenuBuilder($this))
            ->nodeRoute('menu_node_filemanager', 'cloud_upload', 'admin/storage/index')
            ->node('menu_node_system', 'settings_applications')
                ->group('menu_group_access')
                    ->itemApi('menu_access_item_user', 'admin/user/index', 'person', 'api-admin-user')
                    ->itemApi('menu_access_item_apiuser', 'admin/api-user/index', 'device_hub', 'api-admin-apiuser')
                    ->itemApi('menu_access_item_group', 'admin/group/index', 'group', 'api-admin-group')
                ->group('menu_group_system')
                    ->itemApi('menu_system_item_config', 'admin/config/index', 'storage', 'api-admin-config')
                    ->itemApi('menu_system_item_language', 'admin/lang/index', 'language', 'api-admin-lang')
                    ->itemApi('menu_system_item_tags', 'admin/tag/index', 'view_list', 'api-admin-tag')
                    ->itemApi('menu_system_logger', 'admin/logger/index', 'notifications', 'api-admin-logger')
                ->group('menu_group_images')
                    ->itemApi('menu_images_item_effects', 'admin/effect/index', 'blur_circular', 'api-admin-effect')
                    ->itemApi('menu_images_item_filters', 'admin/filter/index', 'adjust', 'api-admin-filter')
                ->group('menu_group_contentproxy')
                    ->itemApi('menu_group_contentproxy_machines', 'admin/proxy-machine/index', 'devices', 'api-admin-proxymachine')
                    ->itemApi('menu_group_contentproxy_builds', 'admin/proxy-build/index', 'import_export', 'api-admin-proxybuild');
    }

    /**
     * Registering application components on bootstraping proccess.
     *
     * @return array An array where the key is the application component name and value the configuration.
     */
    public function registerComponents()
    {
        return [
            'adminLanguage' => [
                'class' => AdminLanguage::class,
            ],
            'adminuser' => [
                'class' => AdminUser::class,
                'defaultLanguage' => $this->interfaceLanguage,
            ],
            'adminmenu' => [
                'class' => AdminMenu::class,
            ],
            'storage' => [
                'class' => LocalFileSystem::class,
            ],
            'auth' => [
                'class' => Auth::class,
            ],
        ];
    }

    /**
     * Setup the admin importer classes.
     *
     * @param \luya\console\interfaces\ImportControllerInterface $import The import controller interface.
     * @return array An array with all importer classes registered for this module.
     */
    public function import(ImportControllerInterface $import)
    {
        return [
            AuthImporter::class,
            FilterImporter::class,
            PropertyImporter::class,
        ];
    }
    
    /**
     * Admin Module translation helper.
     *
     * @param string $message The message key to translation
     * @param array $params Optional parameters to pass to the translation.
     * @return string The translated message.
     */
    public static function t($message, array $params = [], $language = null)
    {
        return parent::baseT('admin', $message, $params, $language);
    }
}
