# CHANGELOG

All notable changes to this project will be documented in this file. This project adheres to [Semantic Versioning](http://semver.org/).
In order to read more about upgrading and BC breaks have a look at the [UPGRADE Document](UPGRADE.md).

## 2.2.0 (in progress)

### Fixed

+ [#343](https://github.com/luyadev/luya-module-admin/pull/343) Fixed bug with migrations when using PostgreSQL

### Added

+ [#338](https://github.com/luyadev/luya-module-admin/pull/338) Toggle button for ngRestModel to switch between enable and disable status.

## 2.1.0 (22. July 2019)

### Fixed

+ [#334](https://github.com/luyadev/luya-module-admin/issues/334) Fixed bug where api overview does not display correct permission values for given API user.
+ [#254](https://github.com/luyadev/luya-module-admin/issues/254) Do not reset imageArray when using createImage() this fixes a bug when using applyFilter inside a foreach.

### Changed

+ [#332](https://github.com/luyadev/luya-module-admin/issues/332) CheckboxRelationActiveQuery should **not** populate any relation data on find.
+ [#328](https://github.com/luyadev/luya-module-admin/issues/328) Empty default selection for "new value" in scheduler overlay.
 
### Added

+ [#336](https://github.com/luyadev/luya-module-admin/pull/336) Added new identifier() method for page properties.
+ [#333](https://github.com/luyadev/luya-module-admin/pull/333) Allow caching of language data until data is modified.
+ [#331](https://github.com/luyadev/luya-module-admin/issues/331) Add new `relation` property for SelectRelationActiveQuery.

## 2.0.3 (25. June 2019)

### Changed

+ [#326](https://github.com/luyadev/luya-module-admin/issues/236) Revamped pagination to provide a better user experience.

### Fixed

+ [#234](https://github.com/luyadev/luya-module-admin/issues/234) File manager stores page and sort field into local storage to retrieve later.
+ [#321](https://github.com/luyadev/luya-module-admin/issues/321) Dump none scalar values in user summary active window.
+ [#317](https://github.com/luyadev/luya-module-admin/issues/317) Fixed bug with flickering of data list after update item on a certain page.

### Added

+ [#325](https://github.com/luyadev/luya-module-admin/issues/325) Added Angular::schedule() helper to build scheduler tag for a given attribute.
+ [#312](https://github.com/luyadev/luya-module-admin/issues/312) New zaa directive `zaa-json-object` in order to generate a json object with key value pairing.
+ [#318](https://github.com/luyadev/luya-module-admin/issues/318) Enable scheduling for toggleStatus ngrest plugin.

## 2.0.2 (13. June 2019)

### Fixed

+ [#314](https://github.com/luyadev/luya-module-admin/issues/314) Fixed bug where schedule coult not find items when override default ActiveRecord find() method.
+ [#316](https://github.com/luyadev/luya-module-admin/issues/316) Fixed a bug where tempnam() throws a php notice since php 7.1 and higher.

### Added

+ [#315](https://github.com/luyadev/luya-module-admin/issues/315) Added property for login controller in order to display an background image on the login screen.

## 2.0.1 (29. May 2019)

### Changed

+ [#309](https://github.com/luyadev/luya-module-admin/issues/309) Updated Material Design Icons to v47.

### Fixed

+ [#310](https://github.com/luyadev/luya-module-admin/issues/310) Fixed bug with double brackets in user history summary Active Window.
+ [#311](https://github.com/luyadev/luya-module-admin/issues/311) Fix problem with findOne() inside Active Windows.
+ [#306](https://github.com/luyadev/luya-module-admin/issues/306) Fixed bug zaaSelect directive in checking if a values exists in optinos.
+ [#305](https://github.com/luyadev/luya-module-admin/issues/305) Fixed bug with module context in Api Users overview Active Window.
+ [#304](https://github.com/luyadev/luya-module-admin/issues/304) Hide tags title in file manager detail when no tags available.
+ [#303](https://github.com/luyadev/luya-module-admin/issues/303) Show message if a crud view has no entries yet.

## 2.0.0 (27. May 2019)

> This release contains new migrations and requires to run the migrate command after updating. Check the [UPGRADE document](UPGRADE.md) to read more about breaking changes.

### Changed

+ [#263](https://github.com/luyadev/luya-module-admin/issues/263) Moved angularjs-datepicker from vendor to vendorlibs. Fixed the "today" issue & updated colors.
+ [#293](https://github.com/luyadev/luya-module-admin/issues/293) Added new request log table for api calls in order to make metrics about request, this must be enabled in the admin area.
+ [#46](https://github.com/luyadev/luya-module-admin/issues/46) Updated bootstrap to 4.3.1.
+ [#268](https://github.com/luyadev/luya-module-admin/issues/268) Deprecated classes, methods and properties has been removed.
+ [#261](https://github.com/luyadev/luya-module-admin/issues/261) Add ngRestFind() for none $is_api_user Users.
+ [#210](https://github.com/luyadev/luya-module-admin/issues/210) New tag translation option.
+ [#140](https://github.com/luyadev/luya-module-admin/issues/140) Generic Scheduler with Yii Queue integration. The queue is triggered by fake job (frontend) or via cronjob console command.
+ [#61](https://github.com/luyadev/luya-module-admin/issues/61) The `initvalue` attribute of Select plugins is now by default `null` instead of `0`. This is needed cause the require validator won't handle `0` as empty until you configure `isEmpty` option.
+ [#260](https://github.com/luyadev/luya-module-admin/issues/260) All tables and queries include database prefix option `{{%}}`.
+ [#104](https://github.com/luyadev/luya-module-admin/issues/104) NgRestModel behaviors are attached in constructor instead of behaviors() method.

### Fixed

+ [#302](https://github.com/luyadev/luya-module-admin/issues/302) Updated spacing for luya-subnav + content.
+ [#245](https://github.com/luyadev/luya-module-admin/issues/245) Fixed highlight of rows after update and new insert in CRUD.
+ [#294](https://github.com/luyadev/luya-module-admin/issues/294) Fixed bug when replace a file with images.
+ [#291](https://github.com/luyadev/luya-module-admin/issues/291) Fixed search and sub folder behavior in file manager.
+ [#278](https://github.com/luyadev/luya-module-admin/pull/278) ToggleStatus plugin initValue=1 not displayed at frontend
+ [#62](https://github.com/luyadev/luya-module-admin/issues/62) Two-digit display of minutes in datetime fields.
+ [#239](https://github.com/luyadev/luya-module-admin/issues/239) Hide i18n flags when no i18n field is configured, also hide in list view as its not possible to toggle in this context.
+ [#273](https://github.com/luyadev/luya-module-admin/issues/273) Fixed a bug where canceling of the folder renaming where not restoring the old folder name.
+ [#258](https://github.com/luyadev/luya-module-admin/issues/258) NgRest Crud search with pagination problem fixed. 
+ [#226](https://github.com/luyadev/luya-module-admin/issues/226) Fixed search indicator
+ [#267](https://github.com/luyadev/luya-module-admin/pull/267) I18n::decodeFindActive returned empty value for explicitly selected lang
+ [#275](https://github.com/luyadev/luya-module-admin/issues/275) Search for file IDs in file manager.

### Added

+ [#300](https://github.com/luyadev/luya-module-admin/issues/300) Added new reload button option and split cache and window reload buttons into two.
+ [#240](https://github.com/luyadev/luya-module-admin/issues/240) New notification system for visited CRUD views.
+ [#213](https://github.com/luyadev/luya-module-admin/issues/213) Added noscript message and <=IE9 warning.
+ [#288](https://github.com/luyadev/luya-module-admin/issues/288) Filemanager add file id in tooltip.
+ [#225](https://github.com/luyadev/luya-module-admin/issues/225) Added configuration for default behavior with inline disposition when uploading new files.
+ [#289](https://github.com/luyadev/luya-module-admin/issues/289) Added new input field methods checkbox, checkboxList, radioList, imageUpload, fileUpload, date and datetime picker.
+ [#283](https://github.com/luyadev/luya-module-admin/pull/283) The possibility to extend standard ngrest crud views
+ [#236](https://github.com/luyadev/luya-module-admin/issues/236) Added multiple menu entries and CRUD view for same models (data pools).
+ [#228](https://github.com/luyadev/luya-module-admin/issues/228) New `sortField` attribute option for plugins.
+ [#94](https://github.com/luyadev/luya-module-admin/issues/94) Required CRUD fields are now highlight as bold text.
+ [#277](https://github.com/luyadev/luya-module-admin/issues/277) Using [unglue.io](https://unglue.io) to compile admin resources.
+ [#205](https://github.com/luyadev/luya-module-admin/issues/205) CRUD search works now in filters and relation calls, sorting and pagination works in searching.
+ [#216](https://github.com/luyadev/luya-module-admin/issues/216) File manager file detail view provides option to tag files.
+ [#259](https://github.com/luyadev/luya-module-admin/pull/259) SelectRelationActiveQuery supports related i18n label fields
+ [#253](https://github.com/luyadev/luya-module-admin/pull/253) Added command action to reset password for users via cli.
+ [#270](https://github.com/luyadev/luya-module-admin/pull/270) Custom date format for date plugin in listing.
+ [#271](https://github.com/luyadev/luya-module-admin/pull/271) Proxy with different database connection.

## 1.2.3 (21. November 2018)

### Changed

+ [#248](https://github.com/luyadev/luya-module-admin/issues/248) Changed file upload response status code on error.

### Added

+ [#249](https://github.com/luyadev/luya-module-admin/issues/249) Add image upload endpoint.
+ [#247](https://github.com/luyadev/luya-module-admin/issues/247) Add new option to enable API caching by defining a cache dependency for the API.
+ [#18](https://github.com/luyadev/luya-module-admin/issues/18) Active Buttons for NgRest.
+ [#238](https://github.com/luyadev/luya-module-admin/issues/238) Storage image models rename methods.
+ [#230](https://github.com/luyadev/luya-module-admin/pull/230) Add WYSIWYG NgRest plugin.
+ [#232](https://github.com/luyadev/luya-module-admin/pull/232) Add tooltip option to load content from xhr request.
+ [#235](https://github.com/luyadev/luya-module-admin/pull/235) Add Injector NgRest plugin.

### Fixed

+ [#243](https://github.com/luyadev/luya-module-admin/issues/243) Fix issue when file id is not a numeric value.
+ [#242](https://github.com/luyadev/luya-module-admin/issues/242) Reset add form values when saving.
+ [#241](https://github.com/luyadev/luya-module-admin/issues/241) Fix problem with composite key detecting.
+ [#221](https://github.com/luyadev/luya-module-admin/issues/221) Fixed problem with zaa slug directive when editing existing value.
+ [#231](https://github.com/luyadev/luya-module-admin/issues/231) Ensure Loggable behavior only runs when admin module exists.

## 1.2.2.1 (8. October 2018)

+ [#211](https://github.com/luyadev/luya-module-admin/issues/211) Try to load all images in crud list in one request and access them trough images service afterwards.
+ [#222](https://github.com/luyadev/luya-module-admin/issues/222) Do not lock data on ngrest view if api user.
+ [#223](https://github.com/luyadev/luya-module-admin/issues/223) Removed auto expand of fields join with `withRelation()` in API ViewAction as it can make problem with sub relations. Use expand instead.
+ [#212](https://github.com/luyadev/luya-module-admin/issues/212) Ensure search action for ngrest is used trough get param instad of post.
+ [#137](https://github.com/luyadev/luya-module-admin/issues/137) Fixed issue with search and timeout.
+ [#217](https://github.com/luyadev/luya-module-admin/pull/217) Added possibility to set if will be grouped items expanded or not when is table shown.

## 1.2.2 (3. September 2018)

### Changed

+ [#137](https://github.com/luyadev/luya-module-admin/issues/137) Rewritten the file system in order to support large amount of data, therefore images create an xhr request for every file info, instead of preloading those trough `data-files` directive. This is can be slower for small system, but is much faster for systems with over 20k images and files.
+ [#160](https://github.com/luyadev/luya-module-admin/issues/160) Renmaed full-response to search.
+ [#191](https://github.com/luyadev/luya-module-admin/issues/191) Added angularjs-slider dep; Added pagination directive (uses angularjs-slider) for use in crud and filemanager; Removed old pagination code
+ [#184](https://github.com/luyadev/luya-module-admin/issues/184) Changed active and hover color for zaaselect.
+ [#159](https://github.com/luyadev/luya-module-admin/issues/159) Refactor generic search behavior to return ActiveQuery object, improved speed and remove global search ngrest api endpoints.
+ [#199](https://github.com/luyadev/luya-module-admin/issues/199) Change signature or UserOnline::refreshUser() in order to not track api request in user online system.
+ [#192](https://github.com/luyadev/luya-module-admin/issues/192) Enabled pagination for all api responses.
+ [#208](https://github.com/luyadev/luya-module-admin/issues/208) Renamed TagsTrait to TaggableTrait and TagsActiveWindow to TaggableActiveWindow, changed getTags() to a relation definition which can be preloaded.

### Added

+ [#209](https://github.com/luyadev/luya-module-admin/pull/209) Polish translations for admin and core modules.
+ [#207](https://github.com/luyadev/luya-module-admin/issues/207) New option to whitelist extensions for file uploads.
+ [#200](https://github.com/luyadev/luya-module-admin/issues/200) Crud generator command asks for sql table instead of model. Also Updated the gii generator methods.
+ [#197](https://github.com/luyadev/luya-module-admin/pull/197) Added dutch language, thanks to @mahkali
+ [#74](https://github.com/luyadev/luya-module-admin/issues/74) Added index for FK fields.
+ [#188](https://github.com/luyadev/luya-module-admin/issues/188) Renamed CallbackFormWidget to ActiveWindowFromWidget, added initValue() method and new dropDownSelect method.
+ [#183](https://github.com/luyadev/luya-module-admin/issues/183) Added `i18nWhere()` for ngrest models and json fields.
+ [#179](https://github.com/luyadev/luya-module-admin/issues/179) Added angular evaluation ngrest plugin to run angular code inside lists and forms.
+ [#174](https://github.com/luyadev/luya-module-admin/issues/174) Implementation of new luya base module $apiRules.
+ [#164](https://github.com/luyadev/luya-module-admin/pull/164) Turkish translations for admin and core modules.
+ [#161](https://github.com/luyadev/luya-module-admin/issues/161) NgRest CRUD exporter provides now the option to download xlsx.
+ [#157](https://github.com/luyadev/luya-module-admin/issues/157) Added `getTitle()` method for Active Windows in order to provide model context modal titles.

### Fixed

+ [#186](https://github.com/luyadev/luya-module-admin/issues/186) Add option to display code in wysiwyg editor.
+ [#198](https://github.com/luyadev/luya-module-admin/issues/198) Typo in password length information for $minCharLength in ChangePasswordActiveWindow.
+ [#202](https://github.com/luyadev/luya-module-admin/issues/202) Changed sorting of image filters.
+ [#201](https://github.com/luyadev/luya-module-admin/issues/201) Escape filter name input.
+ [#204](https://github.com/luyadev/luya-module-admin/issues/204) Fixed problem with filter generator and chain values.
+ [#169](https://github.com/luyadev/luya-module-admin/issues/168) Fixed missing log message in dashboard for deleted records.
+ [#177](https://github.com/luyadev/luya-module-admin/issues/177) NgRest SelectModel problem with where statements on the same model class.
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
