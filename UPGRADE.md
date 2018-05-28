# LUYA ADMIN MODULE UPGRADE

This document will help you upgrading from a LUYA admin module version into another. For more detailed informations about the breaking changes **click the issue detail link**, there you can examples of how to change your code.

## 1.2.x (17. May 2018)

+ This release contains the new migrations. The migrations are requried in order to make the admin module more secure. Therefore make sure to run the `./vendor/bin/luya migrate` command after `composer update`.
+ [#90](https://github.com/luyadev/luya-module-admin/issues/90) Due to uglification of all javascrip files, the angularjs strict di mode is enabled. Therefore change angular controllers from `.controller(function($scope) { ... })` to `.controller(['$scope', function($scope) { }])`. Read more about strict di: https://docs.angularjs.org/guide/di or https://stackoverflow.com/a/33494259 
+ [#69](https://github.com/luyadev/luya-module-admin/issues/69) Removed deprecated `luya\admin\helpers\I18n::decodeActive` use `luya\admin\helpers\I18n::decodeFindActive` instead. Removed deprecated `luya\admin\helpers\I18n::::decodeActiveArray` use `luya\admin\helpers\I18n::decodeFindActiveArray` instead.
+ [#122](https://github.com/luyadev/luya-module-admin/issues/122) Signature change of base file system. If you are using a custom file system you should take a look at the issue description!

## 1.1.x (26. March 2018)

+ This release contains the new migrations which are required for the user and file table. Therefore make sure to run the `./vendor/bin/luya migrate` command after `composer update`.
