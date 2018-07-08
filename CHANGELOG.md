# CHANGELOG

All notable changes to this project will be documented in this file. This project make usage of the [Yii Versioning Strategy](https://github.com/yiisoft/yii2/blob/master/docs/internals/versions.md). In order to read more about upgrading and BC breaks have a look at the [UPGRADE Document](UPGRADE.md).

## 1.2.2 (in progress)

### Changed

+ [#159](https://github.com/luyadev/luya-module-admin/issues/159) Refactor generic search behavior to return ActiveQuery object, improved speed and remove global search ngrest api endpoints.

### Added

+ [#164](https://github.com/luyadev/luya-module-admin/pull/164) Turkish translations for admin and core modules.
+ [#161](https://github.com/luyadev/luya-module-admin/issues/161) NgRest CRUD exporter provides now the option to download xlsx.
+ [#157](https://github.com/luyadev/luya-module-admin/issues/157) Added `getTitle()` method for Active Windows in order to provide model context modal titles.

### Fixed

+ [#172](https://github.com/luyadev/luya-module-admin/issues/172) API users should not have default language from UI.
+ [#171](https://github.com/luyadev/luya-module-admin/issues/171) Fixed issue with link label when link has been set already.
+ [#70](https://github.com/luyadev/luya-module-admin/issues/70) Added styles and "disabled" click event for the active zaaselect dropdown item
+ [#167](https://github.com/luyadev/luya-module-admin/issues/167) NgRest FileArray plugin override the filesystem caption.
+ [#156](https://github.com/luyadev/luya-module-admin/issues/156) Do not display API users in global search.
+ [#158](https://github.com/luyadev/luya-module-admin/issues/158) Searching in CRUD pagination fallback applied the angular filter, therefore values found in `ngRestFullQuerySearch()` where hidden by angular filter if they where not in the ngrest list scope.
+ [#152](https://github.com/luyadev/luya-module-admin/issues/152) Added proper `Content-Type` header with MIME type when delivering download files.
+ [#166](https://github.com/luyadev/luya-module-admin/issues/166) Fixed bug with old admin user table structure where is_deleted has no default value.

## 1.2.1 (5. June 2018)

### Added

+ [#147](https://github.com/luyadev/luya-module-admin/issues/147) Add new read only directive returning the model value.
+ [#134](https://github.com/luyadev/luya-module-admin/issues/134) Add new listener option for slug plugin type. Slug plugin can now list to another attribute while typing and transform the value to a slugable string.
+ [#128](https://github.com/luyadev/luya-module-admin/issues/128) A new indicator display the amount of time left until the user is logged out automatically. Also every keystroke inside any text field will reset the logout timer to null. No more timeouts while working!
+ [#126](https://github.com/luyadev/luya-module-admin/issues/126) Provide option to eager load api model relations.
+ [#20](https://github.com/luyadev/luya-module-admin/issues/20) New option `--sync-requests-count` for proxy command.
+ [#142](https://github.com/luyadev/luya-module-admin/issues/142) Proxy command can skip tables with `!` negation.
+ [#144](https://github.com/luyadev/luya-module-admin/pull/144) Proxy command ask for large table sync.
+ [#141](https://github.com/luyadev/luya-module-admin/pull/141) Add telephone option to redirect form.
+ [#49](https://github.com/luyadev/luya-module-admin/issues/49) Add autocomplete to text and password fields, implemented password manager annotation in account settings.

### Fixed

+ [#133](https://github.com/luyadev/luya-module-admin/issues/133) Cms Page ngrest plugin display now the button to the page on the list overview.
+ [#139](https://github.com/luyadev/luya-module-admin/issues/139) Provide new option for ngRestConfigOptions fixes problem where after saving of a new language the language service does not reload.
+ [#135](https://github.com/luyadev/luya-module-admin/issues/135) Fixed login problem when connecting with ipv6 address.
+ [#129](https://github.com/luyadev/luya-module-admin/issues/129) Fixed problem when ngrest plugin try to write in object property instead of active record attribute.
+ [#125](https://github.com/luyadev/luya-module-admin/issues/125) Detach cruft behavior for global search response.
+ [#132](https://github.com/luyadev/luya-module-admin/pull/132) Fixed validation error on single file upload with the StorageUploadValidator.
+ [#101](https://github.com/luyadev/luya-module-admin/pull/101) Proxy command disable now FOREIGN_KEY_CHECKS, UNIQUE_CHECKS and SQL_MODE while data sync.
+ [#20](https://github.com/luyadev/luya-module-admin/issues/20) Proxy command need now less memory and a bit faster.

## 1.2.0 (17. May 2018)

This release contains new migrations and requires to run the `migrate` command after updating. Check the [UPGRADE Document](UPGRADE.md).

### Changed

+ [#122](https://github.com/luyadev/luya-module-admin/issues/122) Change base file system signature in order to support external file systems like amazon S3.
+ [#121](https://github.com/luyadev/luya-module-admin/issues/121) Reduce the xhr response content for admin images and files in order to speedup admin usage.
+ [#118](https://github.com/luyadev/luya-module-admin/issues/118) Updated outline-config default styles; Added btn--active class to force hover status
+ [#33](https://github.com/luyadev/luya-module-admin/issues/33) Updated textarea min-height from 46 to 86px
+ [#93](https://github.com/luyadev/luya-module-admin/issues/93) Unparseable cruft is enabled by default and uses the angular js built in json encoding mechanism to remove the prepend string.
+ [#90](https://github.com/luyadev/luya-module-admin/issues/90) Minification and Uglification of angularjs files requires strict di.
+ [#69](https://github.com/luyadev/luya-module-admin/issues/69) Remove deprecated `luya\admin\helpers\I18n` methods.
+ [#123](https://github.com/luyadev/luya-module-admin/issues/123) Improved storage filter chain model.

### Added

+ [#113](https://github.com/luyadev/luya-module-admin/issues/113) Add option to configure ngrest attribute conditions when to display/hide a given field based on another field.
+ [#64](https://github.com/luyadev/luya-module-admin/issues/64) Add option to configure the file delivery (download or display in browser).
+ [#27](https://github.com/luyadev/luya-module-admin/issues/27) Filemanager file detail option to rename the original download file name.
+ [#64](https://github.com/luyadev/luya-module-admin/issues/64) Whether file should be download or display in browser.
+ [#100](https://github.com/luyadev/luya-module-admin/issues/100) Option to configure the max idle time of an user until logout.
+ [#86](https://github.com/luyadev/luya-module-admin/issues/86) When a user changes the email, a token will be sent to the old email which has to be entered in order to change the email.
+ [#85](https://github.com/luyadev/luya-module-admin/issues/85) Added option to limit login attempts from session and for when the email is detected correctly. This reduce the possibility to brute force any login credentials. The 2FA security token lifetime can be configured. When the login password is false, the login fields are cleared out.

### Fixed

+ [#119](https://github.com/luyadev/luya-module-admin/issues/119) Fixed bug with falsely commited migration file in version 1.1.1.3.
+ [#111](https://github.com/luyadev/luya-module-admin/issues/111) Fixed scroll-behavior for file detail view (filemanager).
+ [#102](https://github.com/luyadev/luya-module-admin/issues/102) Fixed bug with empty attributes_json in NgRest logger for delete actions.
+ [#80](https://github.com/luyadev/luya-module-admin/issues/80) Added roboto Latin (+Extended), Cyrillic (+Extended), Greek (+Extended), Vietnamese.

## 1.1.1.1 (12. April 2018)

+ [#23](https://github.com/luyadev/luya-kickstarter/issues/23) Fix issue with not writable attributes on assign.

## 1.1.1 (11. April 2018)

### Added

+ [#89](https://github.com/luyadev/luya-module-admin/issues/89) Added module property `strongPasswordPolicy` to enable strong passwords for admin users. Each password must have lower, upper, digit, number and a special char with a min length of 8 chars.

### Fixed

+ [#91](https://github.com/luyadev/luya-module-admin/issues/91) Remove spell check for filemanager upload button.
+ [#92](https://github.com/luyadev/luya-module-admin/issues/92) Add option to bind values while storage querys in order to fix imageArray captions in NgRest plugin.
+ [#88](https://github.com/luyadev/luya-module-admin/issues/88) Clean up change password fields after validation error or success.
+ [#87](https://github.com/luyadev/luya-module-admin/issues/87) Fixed bug when change the password.
+ [#83](https://github.com/luyadev/luya-module-admin/issues/83) Provide after assign event for NgRest plugins.
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
+ [#67](https://github.com/luyadev/luya-module-admin/issues/67) Fixed issue where CRUD loader (relation button) can not edit items.
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
