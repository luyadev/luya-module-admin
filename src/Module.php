<?php

namespace luya\admin;

use luya\admin\base\ReloadButton;
use luya\admin\components\AdminLanguage;
use luya\admin\components\AdminMenu;
use luya\admin\components\AdminMenuBuilder;
use luya\admin\components\AdminUser;
use luya\admin\components\Auth;
use luya\admin\filesystem\LocalFileSystem;
use luya\admin\importers\AuthImporter;
use luya\admin\importers\FilterImporter;
use luya\admin\importers\PropertyImporter;
use luya\base\CoreModuleInterface;
use luya\console\interfaces\ImportControllerInterface;
use Yii;
use yii\console\Application;
use yii\queue\db\Command;

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
 * @property array $reloadButtons Take a look at {{luya\admin\Module::setReloadButtons()}}.
 * @property-read array $jsTranslations Take a look at {{luya\admin\Module::getJsTranslations()}}.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
final class Module extends \luya\admin\base\Module implements CoreModuleInterface
{
    /**
     * This event is triggered when an access token is trying to login.
     *
     * @var string User login by Access-Token event.
     * @since 3.3.0
     */
    public const EVENT_USER_ACCESS_TOKEN_LOGIN = 'eventUserAccessTokenLogin';

    /**
     * This event is triggered before a file is downloaded through the {{luya\admin\controllers\FileController}}.
     *
     * @var string Before File Download Event
     */
    public const EVENT_BEFORE_FILE_DOWNLOAD = 'EVENT_BEFORE_FILE_DOWNLOAD';

    /**
     * @var boolean Whether CORS filter is enabled or not. By default its disabled but you can enable this option
     * when using LUYA admin APIs for usage trough direct xhr requests from another Domain.
     */
    public $cors = false;

    /**
     * @var string The default language for the admin interrace (former known as luyaLanguage).
     * Currently supported: en, de, ru, es, fr, ua, it, el, vi, pl, pt, tr, fa, cn, nl, th, hu, id, bg
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
        'th' => 'ภาษาไทย',
        'hu' => 'Magyar',
        'id' => 'Bahasa',
        'bg' => 'Български',
    ];

    /**
     * @var array Provide dashboard objects from last user logins.
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
     * @var boolean If enabled, the login screen contains a "lost password" form, where the user can enter his email adresse and recieves
     * an email with a link where the user can enter a new password.
     * @since 3.0.0
     */
    public $resetPassword = false;

    /**
     * @var integer The number of seconds the reset token link is valid.
     * @since 3.0.0
     */
    public $resetPasswordExpirationTime = 900; // 15min

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
     * @var string The component/connection name from application.
     * @since 2.0.0
     */
    public $proxyDbConnection = 'db';

    /**
     * @var integer The number of rows which should be transferd for each request.
     */
    public $proxyRowsPerRequest = 100;

    /**
     * @var integer The expiration timeout for a proxy build in seconds. Default value is 1800 seconds which is 30 minutes.
     */
    public $proxyExpirationTime = 6200;

    /**
     * @var boolean If enabled, the admin bootstrap process will check whether the queue job was runing within the last 30min or not. If you are not setting up any cronjob to run
     * the scheduler and you need to rely on the queue/scheulder system you enable this property which will then do a "dummy frontend user cronjob". So on every request it will
     * check whether to run queue or not. By default this is disabled in order to prevent to have more memory and database usage. If disable setup a cronjob with `admin/queue`
     * command using {{luya\admin\commands\QueueController}}.
     * @since 2.0.0
     * @see {{luya\admin\commands\QueueController}}
     */
    public $autoBootstrapQueue = false;

    /**
     * @var boolean Whether the `queue` command should be bootstraped automatically. Defaults to true. If already a queue is configured, this might conflict and override
     * those settings. Therefore you can disable the bootstrap of `queue` command.
     * @since 2.0.4
     */
    public $bootstrapQueueCli = true;

    /**
     * @var string The mutex class which should be used for the admin queue component. Changed from `yii\mutex\FileMutex` to `yii\mutex\MysqlMutex` as a database connection
     * is required in the admin area and there are no conflicts with file permissions when running in cli mode. In order to ensure the old behavior use the FileMutex class.
     * @since 3.7.0
     */
    public $queueMutexClass = 'yii\mutex\MysqlMutex';

    /**
     * @var boolean The default value for {{luya\admin\models\StorageFile::$inline_disposition}} when uploading a new file. By default this is display which will force a download
     * when opening the file url, in order to enable inline disposition (will try to display the file in the browser) set true.
     * > This property will only have an effect when uploading new files and won't work for existing uploaded files or a general default behavior.
     * @since 2.0.0
     */
    public $fileDefaultInlineDisposition = false;

    /**
     * @var boolean Defines whether Api Users can access a method which is not protected from the permission system. When working with JWT or SPA
     * applications this should be disabled.
     * @since 2.2.0
     */
    public $apiUserAllowActionsWithoutPermissions = false;

    /**
     * @var boolean Whether the api user log entries should be display in the module dashboard or not. This is disabled by default as ApiUsers might
     * create and update a lot of data.
     * @since 3.2.0
     */
    public $dashboardLogDisplayApiUserData = false;

    /**
     * @var boolean If enabled, the login form is diabled and maintenance message is displayed, which can be configured trough $disableLoginMessage.
     * @since 4.0.0
     */
    public $disableLogin = false;

    /**
     * @var string An optional message which is disapyled when the login is disabled.
     * @since 4.0.0
     */
    public $disableLoginMessage;

    /**
     * @var boolean If enabled (which is default) the user will be logged out when the ip changes, this makes it harder to overtake tokens as they will be destroyed
     * when the user is logged out. There can be situations with loadbalancers or vpns where the ip changes a lot. But in generall its recommend
     * to keep this setting enabled.
     * @since 4.4
     */
    public $logoutOnUserIpChange = true;

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
        'api-admin-queuelog' => 'luya\admin\apis\QueueLogController',
        'api-admin-queuelogerror' => 'luya\admin\apis\QueueLogErrorController',
        'api-admin-ngrestlog' => 'luya\admin\apis\NgrestLogController',
        'api-admin-storageimage' => 'luya\admin\apis\StorageImageController',
        'api-admin-property' => 'luya\admin\apis\PropertyController',
    ];

    public $apiRules = [
        'api-admin-timestamp' => [
            'patterns' => [
                'POST' => 'index',
            ]
        ],
        'api-admin-user' => [
            'extraPatterns' => [
                'POST change-password' => 'change-password',
            ]
        ]
    ];

    /**
     * @var array An array with all apis from every module, this property is assigned by the {{luya\web\Bootstrap::run()}} method.
     * @since 1.2.2
     */
    public $apiDefintions = []; // typo...

    /**
     * @var boolean Whether a **PUBLIC** available endpoint should created returning an OpenAPI definition for current LUYA System (including all registered modules) or not.
     * @since 3.2.0
     */
    public $publicOpenApi = false;

    /**
     * @var array An array with filter conditions, see {{luya\admin\openapi\Generator::$filterPaths}} for more detailes
     * @since 3.2.0
     * @see {{luya\admin\openapi\Generator::$filterPaths}}
     */
    public $filterOpenApiPaths = [];

    /**
     * @var boolean This property, when enabled, triggers the update of the api_last_activity timestamp with each API request made by the specified API user. However, in systems
     * that experience a high volume of API requests, this feature can potentially impact system performance.
     * @since 5.0.1
     */
    public $apiUserTrackLastActivity = true;

    /**
     * @inheritDoc
     */
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
            'ngrest_button_delete', 'layout_btn_reload', 'js_dir_manager_rm_file_confirm_title', 'ngrest_crud_search_text', 'js_dir_manager_rm_folder_confirm_title', 'js_pagination_page', 'js_dir_manager_rename_success',
            'js_scheduler_show_datepicker', 'js_scheduler_new_value', 'js_scheduler_time', 'js_scheduler_save', 'js_scheduler_title_upcoming', 'js_scheduler_title_completed', 'js_scheduler_table_newvalue', 'js_scheduler_table_timestamp', 'js_dir_manager_file_replace_ok',
            'js_jsonobject_newkey', 'menu_dashboard', 'file_caption_success', 'ngrest_delete_all_button_confirm_message', 'ngrest_delete_all_button_label',
            // cropping
            'crop_source_image', 'crop_preview', 'crop_btn_as_copy', 'crop_btn_as_copy_hint', 'crop_btn_save_copy', 'crop_btn_save_replace','crop_size_free','crop_size_1to1','crop_size_desktop','crop_size_mobile', 'crop_success', 'crop_quality_high', 'crop_quality_medium', 'crop_quality_low'
        ];
    }

    /**
     * Getter method for the js translations array.
     *
     * @return array An array with all translated messages to store in the and access from the admin js scripts.
     */
    public function getJsTranslations()
    {
        $translations = [];
        foreach (Yii::$app->getAdminModulesJsTranslationMessages() as $module => $data) {
            foreach ($data as $key) {
                $translations[$key] = Yii::t($module, $key, [], Yii::$app->language);
            }
        }
        return $translations;
    }

    private array $_reloadButtons = [];

    /**
     * Set an array of relaod buttons with a callback function to run on click.
     *
     * Every array item needs at least:
     *
     * + label: The label which is displayed in the mnu
     * + icon: A material icon value from https://material.io/tools/icons/
     * + callback: A php callable function which is executed when clicking the button.
     *
     * ```php
     * 'reloadButtons' => [
     *     ['label' => 'Clear Frontpage Cache', 'icon' => 'clear', 'callback' => function($button) {
     *         (new \Curl\Curl())->get('https://luya/clear/this/cache');
     *     }]
     * ]
     * ```
     *
     * The first paramter of the callback function is the ReloadButton object itself, this allwos you to
     * change the response message.
     *
     * ```php
     * 'callback' => function(\luya\admin\base\ReloadButton $button) {
     *     // do something
     *     // ...
     *
     *     // change response (success) message.
     *     $button->response = 'Running this button was a full success!';
     * }
     * ```
     *
     * @param array $buttons
     * @since 2.0.0
     */
    public function setReloadButtons(array $buttons)
    {
        foreach ($buttons as $buttonConfig) {
            $this->_reloadButtons[] = new ReloadButton($buttonConfig);
        }
    }

    /**
     * Return array with {{luya\admin\base\ReloadButton}} objects
     *
     * @return array
     * @since 2.0.0
     */
    public function getReloadButtons()
    {
        return $this->_reloadButtons;
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
            ->node('menu_node_system', 'settings_system_daydream')
                ->group('menu_group_access')
                    ->itemApi('menu_access_item_user', 'admin/user/index', 'person', 'api-admin-user')
                    ->itemApi('menu_access_item_apiuser', 'admin/api-user/index', 'device_hub', 'api-admin-apiuser')
                    ->itemApi('menu_access_item_group', 'admin/group/index', 'group', 'api-admin-group')
                ->group('menu_group_system')
                    ->itemApi('menu_system_item_config', 'admin/config/index', 'storage', 'api-admin-config')
                    ->itemApi('menu_system_item_language', 'admin/lang/index', 'language', 'api-admin-lang')
                    ->itemApi('menu_system_item_property', 'admin/property/index', 'widgets', 'api-admin-property')
                    ->itemApi('menu_system_item_tags', 'admin/tag/index', 'tag', 'api-admin-tag')
                    ->itemApi('menu_system_queue', 'admin/queue-log/index', 'schedule', 'api-admin-queuelog')
                ->group('menu_group_log')
                    ->itemApi('menu_system_queue_errors', 'admin/queue-log-error/index', 'bug_report', 'api-admin-queuelogerror')
                    ->itemApi('menu_system_logger', 'admin/logger/index', 'notifications', 'api-admin-logger')
                    ->itemApi('menu_system_model_event_log', 'admin/ngrest-log/index', 'archive', 'api-admin-ngrestlog')
                ->group('menu_group_contentproxy')
                    ->itemApi('menu_group_contentproxy_machines', 'admin/proxy-machine/index', 'devices', 'api-admin-proxymachine')
                    ->itemApi('menu_group_contentproxy_builds', 'admin/proxy-build/index', 'import_export', 'api-admin-proxybuild')
                ->group('menu_group_storage')
                    ->itemApi('menu_images_item_images', 'admin/storage-image/index', 'photo', 'api-admin-storageimage')
                    ->itemApi('menu_images_item_filters', 'admin/filter/index', 'adjust', 'api-admin-filter')
                    ->itemApi('menu_images_item_effects', 'admin/effect/index', 'blur_circular', 'api-admin-effect');
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
            'adminqueue' => [
                'class' => 'yii\queue\db\Queue',
                'db' => 'db',
                'mutex' => $this->queueMutexClass,
                'tableName' => 'admin_queue',
                'channel' => 'default',
                'as log' => 'luya\admin\behaviors\QueueLogBehavior',
                'attempts' => 5, // allow to attempt 5 times
                'ttr' => 300, // wait 5 minutes, also its the max amount a job can take: The ttr (Time to reserve, TTR) option defines the number of seconds during which a job must be successfully completed.
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

    public function luyaBootstrap(\yii\base\Application $app)
    {
        // if console application bootstrap the yii2 queue cli command.
        if ($this->bootstrapQueueCli && $app instanceof Application) {
            $app->controllerMap['queue'] = [
                'class' => Command::class,
                'queue' => $app->adminqueue,
            ];
        }
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
