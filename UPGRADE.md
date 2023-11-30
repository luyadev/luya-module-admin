# LUYA ADMIN MODULE UPGRADE

This document will help you upgrading from a LUYA admin module version into another. For more detailed informations about the breaking changes **click the issue detail link**, there you can examples of how to change your code.

## from 4.x to 5.0

+ Support for PHP 7.x has been discontinued. The minimum required PHP version is now 8.0.

## from 4.8 to 4.9

+ [#744](https://github.com/luyadev/luya-module-admin/pull/744) Make sure you have a valid cache component registered to use the LUYA admin CRUD export system. If large tables are exported, the caching system must support such data sizes, the `file` or `redis` cache component supports large amounts of data.

## from 4.4 to 4.5

+ [#726](https://github.com/luyadev/luya-module-admin/pull/726) With the new [replaced jwt auth](https://github.com/bizley/yii2-jwt) library (which is required in order to support php 8.1) we use [lcobucci/jwt v4](https://github.com/lcobucci/jwt/releases/tag/4.0.0) which massivly changed the API. Therfore the main change for LUYA users is that `Lcobucci\JWT\Token` has been replaced with `Lcobucci\JWT\Token\Plain`. The signature of `luya\admin\base\JwtIdentityInterface` has changed from: `loginByJwtToken(Lcobucci\JWT\Token $token)` to `loginByJwtToken(Lcobucci\JWT\Token\Plain $token)` and in order to to claim the user id in the login process you have to use `$userId = $token->claims()->get('uid');` instead of `$userId = $token->getClaim('uid');`. Take a look at the [JWT Guide Diff](https://github.com/luyadev/luya/commit/74118e94ac4130226b925f6d2312a028287418c0)

## from 4.2 to 4.3

+ [#702](https://github.com/luyadev/luya-module-admin/pull/702) The `ngRestExport()` method will be used to sort and restrict all sortable attributes. This means, if `ngRestExport()` is defined, only the attributes in the array will be available in the export, but therfore and order of the export is equals to the defintion list in `ngRestExport()`. 

## from 4.1 to 4.2

+ Invert the `modules` order in your config file (e.g. `config.php`) to preserve the old admin menu order.

from:
```php
'modules' => [
   /*...*/, // bottommost module in admin menu
   'admin' => [ /*...*/ ],
   'cmsadmin' => [ /*...*/ ], // topmost module in admin menu
],
```
to:
```php
'modules' => [
   'cmsadmin' => [ /*...*/ ], // topmost module in admin menu
   'admin' => [ /*...*/ ],
   /*...*/, // bottommost module in admin menu
],
```

## from 3.x to 4.0

+ Run the migrate command, as new migrations are available.
+ Admin 4.0 requires luya core 2.0, which is part of the new minimum requirement.
+ [#601](https://github.com/luyadev/luya-module-admin/issues/601) The `luya\admin\events\FileDownloadEvent::$file` does not recieve a `luya\admin\file\Item` anymore, instead its a `luya\admin\models\StorageFile` Active Record model instead.
+ [#634](https://github.com/luyadev/luya-module-admin/pull/634) Removed deprecated properties and methods
  - `luya\admin\Module::$assets` removed
  - `luya\admin\Module::$moduleMenus` removed
  - `luya\admin\Module::setJsTranslations()` removed
+ [#599](https://github.com/luyadev/luya-module-admin/issues/599) The base filesystem requires a new method to send a file as stream (resource). Therefore `fileSystemStream()` must be implemented if you have any custom file system.

## from 3.6 to 3.7

+ [#576](https://github.com/luyadev/luya-module-admin/pull/576) Using the `yii\mutex\MysqlMutex` over `yii\mutex\FileMutex` as default mutex handler for admin queue components. This is due to less file permission conflicts when running cli consoles and runtime folder. In order to ensure the old behavior use the configuration below:
```php
'modules' => [
    'admin' => [
        'class' => 'luya\admin\Module',
        'queueMutexClass' => 'yii\mutex\FileMutex',
    ]
]
```

## from 3.1 to 3.2

+ [#484](https://github.com/luyadev/luya-module-admin/pull/484) Changed `applyFilter()` method signature in class `luya\admin\modles\StorageFilterChain` from `applyFilter($loadFromPath, $imageSavePath)` to `applyFilter(ImageInterface $image, array $saveOptions)`. Since version 3.2 the applyFilter requires an instance of `Imagine\Image\ImageInterface` and returns an array containing two elements, the image object and the saving options. This method is internally used to apply the filter chain and is typically not used in an application. If you are, for some reason, calling this method update to the new signature.

## from 2.x to 3.0

+ Run the migrate command, as new migrations are available.
+ Deprecated class `can()` has been removed. (https://github.com/luyadev/luya-module-admin/issues/429)

## from 2.1 to 2.2

+ The {{luya\admin\Module::$apiUserAllowActionsWithoutPermissions}} disables the access for **none permission protected actions** by default. This means that actions which does not have a permission system entry (like the global search) are disabled unless `$apiUserAllowActionsWithoutPermissions` is enabled. This ensures the Api Users which can be used for SPA applications won't have access to system APIs.
+ As {{luya\admin\base\RestActiveController::can()}} is deprecated you should define those permissions in {{luya\admin\base\RestActiveController::actionPermissions()}} instead. Assuming your code was looking like this:
```php
public function actionLogin()
{
    $this->can(Auth::CAN_UPDATE);
    // ... code of login
}
```
Remove the `can()` part and define in actionPermissions() instead:

```php
public function actionPermissions()
{
    return [
        'login' => Auth::CAN_UPDATE,
    ];
}
```

## from 1.2 to 2.0

+ The `table-responsive-wrapper` class got removed and replaced by the Bootstrap version of responsive tables: https://getbootstrap.com/docs/4.3/content/tables/#responsive-tables. Make sure to update your Markup accordingly.
+ Change version constraint as we follow semver (from `~1.2` to `^2.0`)
+ Change the ngRestRelation `apiEndpoint` to `targetModel`. From `'apiEndpoint' => Sale::ngRestApiEndpoint()` to `'targetModel' => Sale::class` inside of `ngRestRelations()`.
+ [#268](https://github.com/luyadev/luya-module-admin/issues/268) Deprecated classes and methods haven been removed:
    + luya\admin\aws\TagActiveWindow replaced by luya\admin\aws\TaggableActiveWindow
    + luya\admin\importers\StorageImporter
    + luya\admin\models\StorageImage::getThumbnail() replaced by luya\admin\models\StorageImage::getTinyCropImage()
    + luya\admin\ngrest\aw\ActiveField replaced by luya\admin\ngrest\aw\ActiveWindowFormField
    + luya\admin\ngrest\aw\CallbackFormWidget replaced by luya\admin\ngrest\aw\ActiveWindowFormWidget
    + luya\admin\traits\TagsTrait replaced by luya\admin\traits\TaggableTrait

## from 1.1 to 1.2

+ This release contains the new migrations. The migrations are requried in order to make the admin module more secure. Therefore make sure to run the `./vendor/bin/luya migrate` command after `composer update`.
+ [#90](https://github.com/luyadev/luya-module-admin/issues/90) Due to uglification of all javascrip files, the angularjs strict di mode is enabled. Therefore change angular controllers from `.controller(function($scope) { ... })` to `.controller(['$scope', function($scope) { }])`. Read more about strict di: https://docs.angularjs.org/guide/di or https://stackoverflow.com/a/33494259 
+ [#69](https://github.com/luyadev/luya-module-admin/issues/69) Removed deprecated `luya\admin\helpers\I18n::decodeActive` use `luya\admin\helpers\I18n::decodeFindActive` instead. Removed deprecated `luya\admin\helpers\I18n::::decodeActiveArray` use `luya\admin\helpers\I18n::decodeFindActiveArray` instead.
+ [#122](https://github.com/luyadev/luya-module-admin/issues/122) Signature change of base file system. If you are using a custom file system you should take a look at the issue description!

## from 1.0 to 1.1

+ This release contains the new migrations which are required for the user and file table. Therefore make sure to run the `./vendor/bin/luya migrate` command after `composer update`.

## Unit Testing

1. `cp phpunit.xml.dist phpunit.xml`
2. `docker-compose up`
3. `docker-compose run luyaadminphpunit tests` to run all tests or `docker-compose run luyaadminphpunit tests/src/helpers/UrlTest.php` to run a specific test.