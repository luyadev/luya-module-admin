# CHANGELOG

All notable changes to this project will be documented in this file. This project make usage of the [Yii Versioning Strategy](https://github.com/yiisoft/yii2/blob/master/docs/internals/versions.md). In order to read more about upgrading and BC breaks have a look at the [UPGRADE Document](UPGRADE.md).

## 1.2.0 (in progress)

This release contains new migrations and requires to run the `migrate` command after updating. Check the [UPGRADE Document](UPGRADE.md).

### Changed

+ [#90](https://github.com/luyadev/luya-module-admin/issues/90) Minification and Uglification of angularjs files requires strict di.
+ [#69](https://github.com/luyadev/luya-module-admin/issues/69) Remove deprecated `luya\admin\helpers\I18n` methods.

### Added

+ [#100](https://github.com/luyadev/luya-module-admin/issues/100) Option to configure the max idle time of an user until logout.
+ [#86](https://github.com/luyadev/luya-module-admin/issues/86) When a user changes the email, a token will be sent to the old email which has to be entered in order to change the email.
+ [#85](https://github.com/luyadev/luya-module-admin/issues/85) Added option to limit login attempts from session and for when the email is detected correctly. This reduce the possibilty to brufe force any login credentials. The 2FA security token lifetime can be configured. When the loggin password is false, the login fields are cleared out.

### Fixed

+ [#102](https://github.com/luyadev/luya-module-admin/issues/102) Fixed bug with empty attributes_json in ngrest logger for delete actions.
+ [#80](https://github.com/luyadev/luya-module-admin/issues/80) Added roboto Latin (+Extended), Cyrillic (+Extended), Greek (+Extended), Vietnamese

## 1.1.1.1 (12. April 2018)

+ [#23](https://github.com/luyadev/luya-kickstarter/issues/23) Fix issue with not writeable attributes on assign.

## 1.1.1 (11. April 2018)

### Added

+ [#89](https://github.com/luyadev/luya-module-admin/issues/89) Added module property `strongPasswordPolicy` to enable strong passwords for admin users. Each password must have lower, upper, digit, number and a special char with a min length of 8 chars.

### Fixed

+ [#91](https://github.com/luyadev/luya-module-admin/issues/91) Remove spellcheck for filemanager upload button.
+ [#92](https://github.com/luyadev/luya-module-admin/issues/92) Add option to bind values while storage querys in order to fix imageArray captions in ngrest plugin.
+ [#88](https://github.com/luyadev/luya-module-admin/issues/88) Clean up change password fields after validation error or success.
+ [#87](https://github.com/luyadev/luya-module-admin/issues/87) Fixed bug when change the password.
+ [#83](https://github.com/luyadev/luya-module-admin/issues/83) Provide after assign event for ngrest plugins.
+ [#77](https://github.com/luyadev/luya-module-admin/issues/77) Ensure if user has edit permissions in order to trigger the interactive toggleStatus plugin.
+ [#76](https://github.com/luyadev/luya-module-admin/issues/76) Create random access token when creating new user in order to prevent unique column exception.

## 1.1.0 (26. March 2018)

This release contains new migrations and requires to run the `migrate` command after updating. Check the [UPGRADE Document](UPGRADE.md).

### Added

+ [#64](https://github.com/luyadev/luya-module-admin/issues/64) Added migration for content disposition.
+ [#66](https://github.com/luyadev/luya-module-admin/issues/66) Provide option to whitelist mimetypes for admin file upload.
+ [#58](https://github.com/luyadev/luya-module-admin/issues/58) New api user level to make system api calls. Provide basic endpoint overview and tester.
+ [#59](https://github.com/luyadev/luya-module-admin/issues/59) NgRest log events are now tracked by the log behavior.
+ [#56](https://github.com/luyadev/luya-module-admin/issues/56) User summary active window with diff view, sessions and user infos.

### Fixed

+ [#68](https://github.com/luyadev/luya-module-admin/issues/68) Fixed caching problem with Yii verison 2.0.14.
+ [#67](https://github.com/luyadev/luya-module-admin/issues/67) Fixed issue where crud loader (relation button) can not edit items.
+ [#1571](https://github.com/luyadev/luya/issues/1571) If Active Window label/icon from config is given ues this instead of object defaultLabel and defaultIcon.
+ [#69](https://github.com/luyadev/luya-module-admin/issues/69) Fixed i18n helper naming, mark old methods as deprecated, add language option.

## 1.0.3 (13. February 2018)

### Fixed

+ [#47](https://github.com/luyadev/luya-module-admin/issues/47) Fixed issue with decoding json page property values.
+ [#40](https://github.com/luyadev/luya-module-admin/pull/40) Apply chart dashboard styling according to cards element.

### Added

+ [#50](https://github.com/luyadev/luya-module-admin/issues/50) Added DummyFileSystem class to fake storage system.

## 1.0.2 (20. January 2018)

### Added

+ [#40](https://github.com/luyadev/luya-module-admin/pull/40) Added the ChartDashboardObject whit echarts.js
+ [#44](https://github.com/luyadev/luya-module-admin/issues/44) Provide packages from LUYA composer plugin (installer.php) for developer toolbar and remote admin endpoint.

### Fixed

+ [#38](https://github.com/luyadev/luya-module-admin/issues/38) Fixed ActiveWindow render composition keys problem with PHP 7.2.
+ [#42](https://github.com/luyadev/luya-module-admin/issues/42) Fixed logout item click behaviour in mainnav tooltip.

## 1.0.1 (5. January 2018)

### Added

+ [#34](https://github.com/luyadev/luya-module-admin/pull/34) Added chinese translations.

### Fixed

+ [#36](https://github.com/luyadev/luya-module-admin/issues/36) Fixed bug in NgRestRelation ActiveQuery link source identification.
+ [#30](https://github.com/luyadev/luya-module-admin/issues/30) Fixed non-unique input fields in account overview.
+ [#5](https://github.com/luyadev/luya-module-admin/issues/5) Fixed issue where modal body listenere could have negativ values and therefore the modal still exists on element which has been closed by esc key.
+ [#7](https://github.com/luyadev/luya-module-admin/issues/7) Repair login input label click function
+ [#1](https://github.com/luyadev/luya-module-admin/issues/1) Improve event listener for floating labels at login input

## 1.0.0 (12. December 2017)

- First stable release.
