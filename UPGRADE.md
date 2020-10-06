# LUYA ADMIN MODULE UPGRADE

This document will help you upgrading from a LUYA admin module version into another. For more detailed informations about the breaking changes **click the issue detail link**, there you can examples of how to change your code.

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
