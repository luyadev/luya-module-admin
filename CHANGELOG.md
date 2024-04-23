# CHANGELOG

All notable changes to this project will be documented in this file. This project adheres to [Semantic Versioning](http://semver.org/).
In order to read more about upgrading and BC breaks have a look at the [UPGRADE Document](UPGRADE.md).

## 5.0.4 (23. April 2024)

+ [#760](https://github.com/luyadev/luya-module-admin/issues/760) To enhance collaboration, we now display the name of the user who locks a row.

## 5.0.3 (23. April 2024)

+ [#759](https://github.com/luyadev/luya-module-admin/pull/759) Prevent `Bcrypt password must not contain null character` errors by using generateRandomString for auth_key encryption.

## 5.0.2 (28. March 2024)

+ [#758](https://github.com/luyadev/luya-module-admin/pull/758) Enhanced the functionality to reorganize folders within the folder hierarchy, allowing for movement to the root or placement into a different subfolder.
+ [#756](https://github.com/luyadev/luya-module-admin/pull/756) Fixed collapsing tooltips due empty content in this case the tooltip is no longer displayed
+ [#755](https://github.com/luyadev/luya-module-admin/pull/755) Provided popup delay `tooltip-popup-delay` for tooltip directive.
+ [#754](https://github.com/luyadev/luya-module-admin/pull/754) Improved `admin/setup` command outputs.

## 5.0.1 (7. February 2024)

+ [#748](https://github.com/luyadev/luya-module-admin/pull/748) Do not clean up user lockouts when the user is an API-based user. This will enhance the performance for API user requests.
+ [#749](https://github.com/luyadev/luya-module-admin/pull/749) Added a new property, `luya\admin\Module::$apiUserTrackLastActivity`, which controls the update of the last activity timestamp for API users. By default, this feature is enabled to maintain backward compatibility. For larger systems, disabling this property can prevent unnecessary database writes.
+ [#751](https://github.com/luyadev/luya-module-admin/pull/751) Fixed parameter placeholders in translations (hu, nl, pl).
+ [#752](https://github.com/luyadev/luya-module-admin/pull/752) Updated links to new guide.
+ [#753](https://github.com/luyadev/luya-module-admin/pull/753) Enhanced error handling for scenarios where the proxy command attempts to synchronize a non-existent file.

## 5.0.0 (30. November 2023)

> **Check the [UPGRADE document](UPGRADE.md) to read more about breaking changes.**

+ Support for PHP 7.x has been discontinued. The minimum required PHP version is now **8.0**.
+ The file upload is now restricted by default to a max total of pixels 6553600. This can be changed by setting the `maxTotalPixels` property of the `luya\admin\storage\BaseFileSystemStorage` class. In order to restore the old behavior turn off the `maxTotalPixels` property by setting it to `false`.

## 4.9.1 (7. June 2023)

+ [#746](https://github.com/luyadev/luya-module-admin/issues/746) Remove WhichBrowser/Parser because of missing PHP 8.2 support.
+ [#745](https://github.com/luyadev/luya-module-admin/pull/745) Fixed bug in exception when image could not find the file.

## 4.9.0 (8. March 2023)

+ [#744](https://github.com/luyadev/luya-module-admin/pull/744) The internal CRUD export system no longer caches data in the @runtime folder, but uses the cache component instead. This could potentially cause problems when exporting large CRUD tables, but it fixes the problem when LUYA Admin is used in a cloud-scaled architecture.
+ [#743](https://github.com/luyadev/luya-module-admin/pull/743) Add option to ignore the pool context inside CRUD relation loader for `CheckboxRelation`, `SelectModel` and `SelectRelationActiveQuery` plugins.

## 4.8.0 (5. January 2023)

+ [#740](https://github.com/luyadev/luya-module-admin/pull/740) AdminLanguage component will throw an Exception if no default language is found.
+ [#739](https://github.com/luyadev/luya-module-admin/pull/739) Added option to disable the multiple input type controls (add, remove and sort).

## 4.7.2 (26. October 2022)

+ [#738](https://github.com/luyadev/luya-module-admin/pull/738) Fixed a bug where global search items where not clickable.

## 4.7.1 (20. October 2022)

+ [#736](https://github.com/luyadev/luya-module-admin/issues/736) Fixed issue with relation calls, introduced in 4.7.0

## 4.7.0 (19. October 2022)

+ Fixed PHP 7.4 as minimum version in composer.json, this was previously done by the `bizley/jwt` dependency. Therefore using `rector` to align functionality with PHP 7.4.
+ [#735](https://github.com/luyadev/luya-module-admin/pull/735) Ensure a none existing filter does not throw an exception. This can be the case when generting dynamic filters and those are stored in the user settings.
+ [#733](https://github.com/luyadev/luya-module-admin/pull/733) Added PT translations.
+ [#734](https://github.com/luyadev/luya-module-admin/pull/734) Added ID (Bahasa Indonesia) translations.

## 4.6.0 (5. October 2022)

+ [#732](https://github.com/luyadev/luya-module-admin/pull/732) NgRest `Text` Plugins can now be turned into an interactive text field on the list (overview) of the CRUD with option `$inline = true`.
+ [#730](https://github.com/luyadev/luya-module-admin/pull/730) New NgRest Plugin `MultipleInputs` to generate data with different types storing as a json.

## 4.5.0 (24. August 2022)

> This release contains a huge BC Break when using LUYA JWT user auth. Check the [UPGRADE document](UPGRADE.md) to read more

+ [#719](https://github.com/luyadev/luya-module-admin/pull/719) Prepare for PHP 8.1. Increased minium PHP Version to 7.4. Replaced `sizeg/jwt` with `bizley/jwt`. 
+ [#726](https://github.com/luyadev/luya-module-admin/pull/726) The signature of `luya\admin\base\JwtIdentityInterface` has changed from: `loginByJwtToken(Lcobucci\JWT\Token $token)` to `loginByJwtToken(Lcobucci\JWT\Token\Plain $token)` 
+ [#728](https://github.com/luyadev/luya-module-admin/pull/728) Added new `NgRestActiveQuery` method `jsonOrderBy()` to sort by a field which contains a json object.

## 4.4.1 (20. July 2022)

+ [#713](https://github.com/luyadev/luya-module-admin/issues/713) Blacklist SVG mime type by default. In order to enable SVG Upload use `'whitelistMimeTypes' => ['image/svg+xml']`.
+ [#723](https://github.com/luyadev/luya-module-admin/pull/723) Added "Toggle all" button to zaaCheckboxArray plugin.
+ [#724](https://github.com/luyadev/luya-module-admin/pull/724) Fixed issues with search and sortable when using Pools.
+ [#725](https://github.com/luyadev/luya-module-admin/pull/725) `SortableTrait` prefixes the table name now in the find querys in order to prevent issue when have two sortindex fields with the same name.

## 4.4.0 (19. May 2022)

+ [#714](https://github.com/luyadev/luya-module-admin/pull/714) Improve the sorting ability by adding create, update and delete events which are attached from the SortableTrait. Sorting over pagination or swap index from form input is now possible too.
+ [#711](https://github.com/luyadev/luya-module-admin/pull/711) Add option to disable the auto logout when the user ip changes.
+ [#712](https://github.com/luyadev/luya-module-admin/pull/712) Fix issue where field labels where not used from models `getAttributeLabel()` when using `ngRestExport()`.

## 4.3.2 (13. April 2022)

+ [#710](https://github.com/luyadev/luya-module-admin/pull/710) Fixed a bug where it was not possible to export more then 100 rows when using `ngRestExport()` configuration.
+ [#709](https://github.com/luyadev/luya-module-admin/pull/709) Added `initvalue` for `zaaColor` element.
+ [#707](https://github.com/luyadev/luya-module-admin/pull/707) Fix issue when creating a new record inside an ngrest relation call form, losing they context data (from the relation).

## 4.3.1 (22. February 2022)

+ [#705](https://github.com/luyadev/luya-module-admin/pull/705) Fix issue with SelectArrayGently when using a closure
+ Changed default queueFiltersList to medium-thumbnail instead of meidum-cop
+ [#706](https://github.com/luyadev/luya-module-admin/pull/706) Use `ImageInterface::THUMBNAIL_FLAG_NOCLONE` when creating thumbnails and fix issue with ngrest logger when uploading files.

## 4.3.0 (15. February 2022)

+ [#700](https://github.com/luyadev/luya-module-admin/pull/700) Fixed syntax error in crud generate command.
+ [#702](https://github.com/luyadev/luya-module-admin/pull/702) The `ngRestExport()` method will be used to sort and restrict all sortable attributes.
+ [#704](https://github.com/luyadev/luya-module-admin/pull/704) Use model for ngrest logger in order to prevent errors for to long strings.

## 4.2.0 (9. December 2021)

> This release changes the admin menu order in terms of admin UI but not in terms API breaks. Check the [UPGRADE document](UPGRADE.md) to read more about breaking changes.

+ [#698](https://github.com/luyadev/luya-module-admin/pull/698) Fixed reversed order of modules in admin menu.
+ [#695](https://github.com/luyadev/luya-module-admin/pull/695) Fixed placeholders within `zaaMultipleInputs`.
+ [#694](https://github.com/luyadev/luya-module-admin/pull/694) Fixed CRUD title involving NgRestPools.
+ [#690](https://github.com/luyadev/luya-module-admin/pull/690) Option to pass additional variables to the dashboard objects.
+ [#676](https://github.com/luyadev/luya-module-admin/issues/676) Fixed hidden NgRest attributes issue in the group-by-field select.
+ [#678](https://github.com/luyadev/luya-module-admin/pull/678) Added `icon` property to any NgRest attribute. It allows setting additional icons in CRUD table header and CRUD edit form.
+ [#668](https://github.com/luyadev/luya-module-admin/issues/668) Fixed misleading use of the `note_add` icon.
+ [#660](https://github.com/luyadev/luya-module-admin/pull/660) Introduced `<luyaElements>` analogues for some `<zaaElements>`. This allows getting simple AngularJs form elements styled to match the rest of the interface elements.
+ [#666](https://github.com/luyadev/luya-module-admin/pull/666) Fixed injection issue with zaaDecimal within zaaMultipleInputs.
+ [#665](https://github.com/luyadev/luya-module-admin/pull/665) Replaced `<span>` tags with `<a>` tags in main admin menu and submenus.
+ [#663](https://github.com/luyadev/luya-module-admin/issues/663) A new `TagRelation::cleanup(ActiveRecord $model)` method to remove all tag relations for a certain model. 
+ [#481](https://github.com/luyadev/luya-module-admin/issues/481) Fixed issue where file manager files count is not updated accordingly after uploading new files.
+ [#674](https://github.com/luyadev/luya-module-admin/pull/674) Throw an exception when the image can not find the requested file.
+ [#697](https://github.com/luyadev/luya-module-admin/issues/697) Fix issue with sorting of admin UI language.

## 4.1.0 (21. September 2021)

+ [#661](https://github.com/luyadev/luya-module-admin/pull/661) Added a `cellColor` property to any NgRest attribute. It allows setting color of CRUD table cells.
+ [#662](https://github.com/luyadev/luya-module-admin/pull/662) Fix display issue with radio buttons in multiple radio button fields (zaaRadio directive).
+ [#658](https://github.com/luyadev/luya-module-admin/pull/658) Added new SelectArrayGently Plugin, which is the same as SelectArray Plugin, but do not override the values from the database.
+ [#657](https://github.com/luyadev/luya-module-admin/pull/657) Fix problem with global admin UI search when model does not exist, this could be due to old controller structure or custom code.
+ [#656](https://github.com/luyadev/luya-module-admin/pull/656) Ensure queue items are removed when schedule item is deleted, improved filter of upcoming queue events, fix issue with scheduler log for multiple attributes on the same model.
+ [#654](https://github.com/luyadev/luya-module-admin/pull/654) Fix issue with ngrest detail view when json is not an array.
+ [#496](https://github.com/luyadev/luya-module-admin/issues/496) Added default color for link elements in NgRest CRUD table.

## 4.0.0 (27. July 2021)

> **This release contains new migrations and requires to run the migrate command after updating. Check the [UPGRADE document](UPGRADE.md) to read more about breaking changes.**

+ [#599](https://github.com/luyadev/luya-module-admin/issues/599) Files are now downloaded via stream instead of reading its content, Therefore any custom storage systems requires a new method `fileSystemStream()`. 
+ [#601](https://github.com/luyadev/luya-module-admin/issues/601) The `luya\admin\events\FileDownloadEvent::$file` does not receive a `luya\admin\file\Item` anymore, instead its a `luya\admin\models\StorageFile`.
+ [#647](https://github.com/luyadev/luya-module-admin/pull/647) Lazy data load for ngrest plugin.
+ [#635](https://github.com/luyadev/luya-module-admin/pull/635) New migration database file, as new attributes are available for properties, images and files
+ [#298](https://github.com/luyadev/luya-module-admin/issues/298) Added new NgRest Plugin `SelectAsyncApi` which can be used to change the select based on certain context variables in real time, its also known as "dependent select".
+ [#642](https://github.com/luyadev/luya-module-admin/pull/642) Example usage for admin/proxy machine in detail window.
+ [#610](https://github.com/luyadev/luya-module-admin/pull/610) Added API endpoint to display all groups where the current logged-in user belongs to `admin/api-user-group/me`.
+ [#602](https://github.com/luyadev/luya-module-admin/issues/602) Fixed bug in ngrest model detail view.
+ [#605](https://github.com/luyadev/luya-module-admin/pull/605) Add new LUYA Test Suite for wider PHP Testing Support. Added Tests for PHP 8.
+ [#608](https://github.com/luyadev/luya-module-admin/pull/608) Use FileHelper::unlink method instead of PHP's `unlink` in order to prevent thrown exceptions.
+ [#609](https://github.com/luyadev/luya-module-admin/issues/609) Fixed generic issues when using PostgreSQL.
+ [#620](https://github.com/luyadev/luya-module-admin/pull/620) Changed echarts library to major [Version 5.0](https://github.com/apache/echarts/releases/tag/5.0.0)
+ [#603](https://github.com/luyadev/luya-module-admin/issues/603) Added option to disable the login form and display a maintenance message instead.
+ [#623](https://github.com/luyadev/luya-module-admin/pull/623) New command to cleanup ngrest log and cms log tables `./luya admin/log/cleanup all`.
+ [#626](https://github.com/luyadev/luya-module-admin/pull/626) Fixed issue with blameable behavior in console command context.
+ [#627](https://github.com/luyadev/luya-module-admin/pull/627) Added option to set a button condition to show or hide crud update, delete, ngRestActiveButtons and ngRestActiveWindows buttons.
+ [#630](https://github.com/luyadev/luya-module-admin/pull/630) Added option to define permission level for ActiveWindows and ActiveButtons, default behavior `Auth::CAN_UPDATE` is maintained for BC.
+ [#604](https://github.com/luyadev/luya-module-admin/issues/604) Added "Save", "Save and close" and "Create", "Create and close" buttons next to CRUD forms.
+ [#313](https://github.com/luyadev/luya-module-admin/issues/313) Add option to disable text and filter in image array zaa directive and ngrest plugin.
+ [#638](https://github.com/luyadev/luya-module-admin/pull/638) Added new `orderBy()` method for `file`, `image` and `folder` iterator of storage system, this fixes the issue that `fileArray` and `imageArray` plugin where sorted wrong when `$fileIterator` is enabled.
+ [#639](https://github.com/luyadev/luya-module-admin/pull/639) The LUYA `Config::set()` can now store larger amount of data, using `text()` instead of `string()`.
+ [#640](https://github.com/luyadev/luya-module-admin/pull/640) Added new `--only` option ( or `-o`) for `admin/proxy` command. Possible values are `db` or `storage`.
+ [#641](https://github.com/luyadev/luya-module-admin/pull/641) Added loading indicator for filemanager file detail.
+ [#643](https://github.com/luyadev/luya-module-admin/issues/643) Fixed issue where the root folder where displaying all files from the storage system.
+ [#645](https://github.com/luyadev/luya-module-admin/pull/645) New predifined radio input page property `luya\admin\base\RadioProperty`
+ [#648](https://github.com/luyadev/luya-module-admin/pull/648) The storage contains now an option which can generate filter version of images async in the queue after uploading. To do see take a look at `luya\admin\storage\BaseFileSystemStorage::$queueFilters`.
+ [#649](https://github.com/luyadev/luya-module-admin/pull/649) New `ngRestActiveSelections` API which allows developers to interact with a certain selection in the CRUD list view. For example to archive or delete multiple items. The API is similar to Active Buttons.

## 3.9.0 (24. November 2020)

+ [#596](https://github.com/luyadev/luya-module-admin/pull/596) Added new migration for session storage, the table name is `admin_session` and can be configured as `session' => ['class' => 'yii\web\DbSession', 'sessionTable' => 'admin_session']`.
+ [#595](https://github.com/luyadev/luya-module-admin/pull/595) Added new NgRest Plugin `JsonObject` which will store a JSON object in the database and return an assoc array in the model.
+ [#170](https://github.com/luyadev/luya-module-admin/issues/170) Added new `ngRestExport()` method to NgRestModel in order to apply formatting of values to the downloadable export.

## 3.8.0 (11. November 2020)

+ [#589](https://github.com/luyadev/luya-module-admin/pull/589) Add new help() method for page properties, added CRUD view for properties.
+ [#591](https://github.com/luyadev/luya-module-admin/pull/591) Disable session login for rest api calls.
+ [#592](https://github.com/luyadev/luya-module-admin/pull/592) Fixed a bug where storage uploader without selected files throws an exception.

## 3.7.1 (4. November 2020)

+ [#587](https://github.com/luyadev/luya-module-admin/pull/587) Fixed a bug where it was not possible to update the current admin user due to wrong unique email validation.
+ [#588](https://github.com/luyadev/luya-module-admin/pull/588) Fixed a bug where the CRUD Tags filter does not appear anymore.

## 3.7.0 (26. October 2020)

> This release contains a behavior change where MysqlMutex is default instead of FileMutex. Check the [UPGRADE document](UPGRADE.md) to read more about breaking changes.

+ [#576](https://github.com/luyadev/luya-module-admin/pull/576) Use `MysqlMutex` as default Mutex class for the Admin Queue instead of `FileMutex` due to people have problems with file permissions when running the queue in cli mode. MysqlMutex is also the better approach when multiple works might run on different workloads.
+ [#578](https://github.com/luyadev/luya-module-admin/pull/578) New bahasa (Indonesian) language option.
+ [#575](https://github.com/luyadev/luya-module-admin/pull/575) New hungarian language option.
+ [#574](https://github.com/luyadev/luya-module-admin/pull/574) Add new toasts design (stronger colors).
+ [#577](https://github.com/luyadev/luya-module-admin/pull/577) Queue Scheduler Job loads only the target attribute into the model.
+ [#579](https://github.com/luyadev/luya-module-admin/pull/579) Updated Portuguese translation files.
+ [#580](https://github.com/luyadev/luya-module-admin/pull/580) Fix issue where the OpenApi parser does not return models which are instance of `yii\base\Model`.
+ [#581](https://github.com/luyadev/luya-module-admin/pull/581) Ensure the proxy api to synchronise files uses the `sendContentAsFile` in order to support 3rd party storage systems like AWS.
+ [#583](https://github.com/luyadev/luya-module-admin/pull/583) Its now possible to export CRUD data for a given filter. Using `ngRestFilters()` data to display the filters in the export mask in preselect the current active filter.
+ [#504](https://github.com/luyadev/luya-module-admin/issues/504) Fixed a bug where images in CRUD list disappear when switch from list to create form and back again.
+ [#585](https://github.com/luyadev/luya-module-admin/pull/585) Added new `zaa-select-crud` directive which allows to select a row from an existing ngrest crud in a modal dialog.

## 3.6.1 (1. October 2020)

+ [#572](https://github.com/luyadev/luya-module-admin/pull/572) Fixed "zaa-date" datepicker width issue.

## 3.6.0 (30. September 2020)

> This requires LUYA core 1.7

+ [#567](https://github.com/luyadev/luya-module-admin/pull/567) Do not marke i18n values as dirty when they are populated from the database, store the original json value from the database in a new `setI18nOldValue()` method instead.
+ [#533](https://github.com/luyadev/luya-module-admin/pull/553) Use new `Yii::$app->getAdminModulesMenus()`, `Yii::$app->getAdminModulesJsTranslationMessages()` and `Yii::$app->getAdminModulesAssets()` method in order to retrieve module data. This fixes a bug with admin modules which does not have an `admin` in the module name f.e. `'usertoken' => 'luya\admin\usertoken\Module'`.
+ [#561](https://github.com/luyadev/luya-module-admin/pull/561) Disable LUYA admin auth checks when cors is enabled and request method is options.
+ [#562](https://github.com/luyadev/luya-module-admin/pull/562) New `luya\admin\validators\I18nRequiredValidator` validator in order to validate i18n attributes an its content. The validator checks if all language short codes exist and if the corresponding value is empty.
+ [#577](https://github.com/luyadev/luya-module-admin/pull/566/) Ensure the `admin/proxy` command also works with different file systems then the local storage.
+ [#565](https://github.com/luyadev/luya-module-admin/pull/565) Add new `luya\admin\validators\StorageUploadValidator` which assignes the file absolute path as value.
+ [#569](https://github.com/luyadev/luya-module-admin/pull/569) Improve the view when using TextArray ngrest plugin in CRUD overview.
+ [#571](https://github.com/luyadev/luya-module-admin/pull/571) Ensure the user_id is selected in any SQL query mode, therefore fixed `yii\base\ErrorException: Undefined index: user_id` bug when open a CRUD. [see #551](https://github.com/luyadev/luya-module-admin/issues/551)
+ [#570](https://github.com/luyadev/luya-module-admin/pull/570) Added border radius to all form input fields. Improved CRUD search and group by buttons.

## 3.5.2 (26. August 2020)

+ [#559](https://github.com/luyadev/luya-module-admin/pull/559) Add method to return a language specific NgRest Model value.
+ [#556](https://github.com/luyadev/luya-module-admin/issues/556) Generate unique OpenApi operationIds.

## 3.5.1 (12. August 2020)

+ [#551](https://github.com/luyadev/luya-module-admin/issues/551) Added missing `user_id` column in select condition which throws an error for certrain sql mode configurations.

## 3.5.0 (11. August 2020)

+ [#545](https://github.com/luyadev/luya-module-admin/issues/545) Fix issue where primary key values where not correct type casted (a string was returned instead of integer). This was due to Text NgRestPlugin encoding its value when assigning to the model for security reasons.
+ [#542](https://github.com/luyadev/luya-module-admin/issues/542) Add tablename in the where condition to support join relations with `luya\admin\traits\SoftDeleteTrait`.
+ [#543](https://github.com/luyadev/luya-module-admin/issues/543) Ensure all images are routed trough LUYA file controller in order to fix issue with cropping images when working with 3rd party storage systems.
+ [#541](https://github.com/luyadev/luya-module-admin/pull/541) Fix memory problem in OpenApi generator because of circular references.
+ [#537](https://github.com/luyadev/luya-module-admin/pull/537) Add new event to customize the params for an OpenApi generated Path.
  
## 3.4.1 (28. July 2020)

+ [#539](https://github.com/luyadev/luya-module-admin/issues/539) Fix issue with angularjs directive closing tags which has been introduced in version 1.8.0
+ [#536](https://github.com/luyadev/luya-module-admin/issues/536) Fix issue where security schemas where added but not applied to the Operations.
+ [#534](https://github.com/luyadev/luya-module-admin/pull/534) Using `fields()` when working with ActiveRecords as it represents the REST resource information. 
+ [#533](https://github.com/luyadev/luya-module-admin/pull/533) Fixed a bug where OpenApi property relations won't expand.

## 3.4.0 (21. July 2020)

+ [#530](https://github.com/luyadev/luya-module-admin/pull/530) Attach query behaviors in `luya\admin\ngrest\base\NgRestModel::find`.
+ [#529](https://github.com/luyadev/luya-module-admin/pull/529) Fixed an issue with OpenApi path params.
+ [#527](https://github.com/luyadev/luya-module-admin/issues/527) Fixed a bug where deleted user emails where not validated when save or update an existing user account.

## 3.3.2 (28. June 2020)

+ [#522](https://github.com/luyadev/luya-module-admin/issues/522) Fixed issue with not normalized attribute types in OpenApi file.
+ [#523](https://github.com/luyadev/luya-module-admin/issues/523) Fixed a bug where an empty options array in Angular Helper class leads into an error while setting the default $scope.model state in Radio Buttons.
+ [#520](https://github.com/luyadev/luya-module-admin/issues/520) Checking class existance in getDiffCount() method in order to ensure, a row badge is only handled when the class exists. This might be a problem if a module has been removed but the notification information still persists.

## 3.3.1 (17. June 2020)

+ [#497](https://github.com/luyadev/luya-module-admin/issues/497) Do not render the dropdown values in `luyaSelect` until the dropdown is expanded. 
+ [#519](https://github.com/luyadev/luya-module-admin/pull/519) Use `@method` PhpDoc to override Yii Framework defined actions in `actions()` method, otherwise those will always have the same Summary and Description Text in the OpenApi file.
+ [#517](https://github.com/luyadev/luya-module-admin/pull/517) Fix problem with OpenApi generator URL tokens like `<identifier:[a-z0-9]+>` which are now rendered correctly as `<identifier>`
+ [#515](https://github.com/luyadev/luya-module-admin/pull/515) If property `luya\admin\ngrest\base\Api::$filterSearchModelClass` is defined, the filter model will be taken into account for `filter` request param.
+ [#511](https://github.com/luyadev/luya-module-admin/issues/511) Fixed a bug where OpenApi IndexAction should return an array instead of an object.
+ [#512](https://github.com/luyadev/luya-module-admin/pull/512) Fixed a bug with multiple input types and zaaLink directives (none unique elements).
+ [#510](https://github.com/luyadev/luya-module-admin/pull/510) Fixed regression from issue [#459](https://github.com/luyadev/luya-module-admin/issues/459) regarding user change history active window.

## 3.3.0 (26. Mai 2020)

+ [#503](https://github.com/luyadev/luya-module-admin/pull/503) An option to assign defined `yii\web\UrlRule` into the `luya\admin\openapi\Generator`.
+ [#501](https://github.com/luyadev/luya-module-admin/pull/501) Implement PhpDoc `@uses` for handling OpenApi request body informations when verb type is `POST`.
+ [#500](https://github.com/luyadev/luya-module-admin/pull/500) Trigger an event (eventUserAccessTokenLogin) when an access token is requesting for a login.
+ [#499](https://github.com/luyadev/luya-module-admin/pull/499) Added missing RU translations.
+ [#489](https://github.com/luyadev/luya-module-admin/issues/489) Hide default LUYA NgRest CRUD actions for OpenApi generator. Improve overall performance to generate OpenApi.

## 3.2.0 (29. April 2020)

> This release requires LUYA Core version 1.3 and contains a signature change for a method. Check the [UPGRADE document](UPGRADE.md) to read more about breaking changes.

+ [#467](https://github.com/luyadev/luya-module-admin/issues/467) Improve performance of applying multiple filters on an image.
+ [#478](https://github.com/luyadev/luya-module-admin/pull/478) Corrected `implode()` in `ngRestScopes()` in create model command template.
+ [#475](https://github.com/luyadev/luya-module-admin/pull/475) Added new option to return a none empty tag title.
+ [#476](https://github.com/luyadev/luya-module-admin/pull/476) Ensure importers skip objects which are not of the certain type. This is importend when a folder is used for other data.
+ [#459](https://github.com/luyadev/luya-module-admin/issues/459) New dropdown option to truncate the whole model data, if enabled.
+ [#284](https://github.com/luyadev/luya-module-admin/issues/284) Added initvalue option for zaaRadio directive.
+ [#349](https://github.com/luyadev/luya-module-admin/issues/349) Option to include ApiUsers log entries into the admin dashboard.
+ [#466](https://github.com/luyadev/luya-module-admin/issues/466) New view for all images which are generated from files applying a filter.

## 3.1.0 (24. March 2020)

> This release requires LUYA Core version 1.1 to work.

+ [#464](https://github.com/luyadev/luya-module-admin/issues/464) Change behavior of how i18n values are encoded, by using $app->language instead of $composition->langShortCode. Automatically provided ContentNegotation $languages from LUYA admin language table (requires luya core version 1.1)
+ [#470](https://github.com/luyadev/luya-module-admin/issues/470) Improved the performance of the file manager folder tree, when a lot of data is available.
+ [#472](https://github.com/luyadev/luya-module-admin/issues/472) Added new controller for website uptime tests (Route: `admin/uptime`) 
+ [#196](https://github.com/luyadev/luya-module-admin/issues/196) Enable croping for images.

## 3.0.3 (5. March 2020)

+ [#468](https://github.com/luyadev/luya-module-admin/issues/468) Removed window on load from login (was not needed) in order to fix a bug where Safari does not render the login form.


## 3.0.2 (28. Feburary 2020)

+ [#463](https://github.com/luyadev/luya-module-admin/issues/463) Fixed an issue with luya-content container height.

## 3.0.1 (25. February 2020)

+ [#462](https://github.com/luyadev/luya-module-admin/pull/462) Deleted exception on hasOne() relation in ngrest/base/Api for working ngRestRelations with hasOne relation
+ [#461](https://github.com/luyadev/luya-module-admin/issues/461) Fixed an overflow issue on admin pages.

## 3.0 (20. February 2020)

> This release contains new migrations and requires to run the migrate command after updating. Check the [UPGRADE document](UPGRADE.md) to read more about breaking changes.

### Changed

+ [#429](https://github.com/luyadev/luya-module-admin/issues/429) Removed deprecated methods.
+ [#440](https://github.com/luyadev/luya-module-admin/issues/440) Show vertical scrollbars.
+ [#428](https://github.com/luyadev/luya-module-admin/issues/428) Update to latest echarts version (from 3.5 to 4.5) for backwards compatbility problems take a look at https://www.echartsjs.com/en/changelog.html#v4-0-0.

### Added

+ [#320](https://github.com/luyadev/luya-module-admin/issues/320) If `$resetPassword` is enabled and `mail` component is configured properly, the user can enter the email address to reset his password.
+ [#265](https://github.com/luyadev/luya-module-admin/issues/265) Session based lockout has been replaced for ip based lockout.
+ [#446](https://github.com/luyadev/luya-module-admin/issues/446) Added option to remember a device which will then auto login the user (unless logout is clicked, or an auto logout happens due to inactivity).
+ [#287](https://github.com/luyadev/luya-module-admin/issues/287) Added 2FA trough OTP for users accounts, if enabled the secure token will not be sent.
+ [#411](https://github.com/luyadev/luya-module-admin/issues/411) Queue log errors are now tracked in a seperate table (queue log error).
+ [#437](https://github.com/luyadev/luya-module-admin/pull/437) Option to display only the color dot in the Color plugin not the the selected color value.
+ [#434](https://github.com/luyadev/luya-module-admin/pull/434) New Badge plugin to generate badge views in list overview.
+ [#380](https://github.com/luyadev/luya-module-admin/issues/380) New `index` ngrest plugins in order to generate a sequential row numbering in list views.
+ [#264](https://github.com/luyadev/luya-module-admin/issues/264) Added new `readonly` option to NgRest Plugins which will render the list value in update scope.
+ [#443](https://github.com/luyadev/luya-module-admin/issues/443) Added option to disable model validation when using duplicate button.
+ [#364](https://github.com/luyadev/luya-module-admin/issues/364) Added the user agent to the user login table to display more detailed informations.

### Fixed

+ [#453](https://github.com/luyadev/luya-module-admin/issues/453) When visiting dashboard in mobile view, the title was not set correctly.
+ [#439](https://github.com/luyadev/luya-module-admin/issues/439) Add option to dissabled auto assign of select data after find.
+ [#441](https://github.com/luyadev/luya-module-admin/issues/441) Fixed switching of the "check_circle" text to the real icon after successful authentication
+ [#435](https://github.com/luyadev/luya-module-admin/issues/435) Problem when clicking on checkboxes when initvalue is true (active) state.
+ [#426](https://github.com/luyadev/luya-module-admin/issues/426) Fixed a bug where color plugin does not work when model is empty.
+ [#448](https://github.com/luyadev/luya-module-admin/issues/448) Added checking if the user is not a guest, for the 'view' action to work in $authOptional on Api Controllers
+ [#330](https://github.com/luyadev/luya-module-admin/issues/330) Mark required i18n fields with bold label, like none i18n fields.

## 2.4.1 (16. December 2019)

+ [#424](https://github.com/luyadev/luya-module-admin/pull/424) Fixed bug of wrong variable in active buttons.

## 2.4.0 (13. December 2019)

+ [#415](https://github.com/luyadev/luya-module-admin/issues/415) Storage createImage works only when image mimetype is provided.
+ [#385](https://github.com/luyadev/luya-module-admin/issues/385) Fixed issue with varchar primary keys when working with relations.
+ [#421](https://github.com/luyadev/luya-module-admin/issues/421) Lazyload ngrest config informations to reduce memory usage and sql requests.
+ [#420](https://github.com/luyadev/luya-module-admin/pull/420) Fix a bug when using group by option in crud and values where not rendered trough ngrest plugins mechanism. 
+ [#419](https://github.com/luyadev/luya-module-admin/pull/419) NgRest Filters are rendered by LUYA admin select dropdown instead of browser dropdown, this allows to search in the list.
+ [#233](https://github.com/luyadev/luya-module-admin/issues/233) Improve error message for empty active window callback parameters.
+ [#413](https://github.com/luyadev/luya-module-admin/pull/413) Option to disable the auto bootstrap of the queue command in conflict siutations.
+ [#409](https://github.com/luyadev/luya-module-admin/issues/409) Bootstrap the native Yii Queue console command in order to use run and listen commands.

## 2.3.0 (12. November 2019)

+ [#394](https://github.com/luyadev/luya-module-admin/issues/394) Do not run fake cronjob for admin queue if it was not enabled by module's config.
+ [#407](https://github.com/luyadev/luya-module-admin/pull/407) Add new ngrest `raw` plugin which won't change input/output.
+ [#404](https://github.com/luyadev/luya-module-admin/issues/404) NgRest API delete action use ngRestFind() instead of find().
+ [#395](https://github.com/luyadev/luya-module-admin/issues/395) New Active Window to delete tags.
+ [#401](https://github.com/luyadev/luya-module-admin/issues/401) Fixed logout bug for users without file permission.
+ [#397](https://github.com/luyadev/luya-module-admin/issues/397) i18nAttributeFallbackValue() require to run the onFind() context of the given attribute plugin in order to ensure plugin specific options like `markdown`. 
+ [#389](https://github.com/luyadev/luya-module-admin/issues/389) Do not throw an exception by default when pool identifier does not exists in the list of pools.
+ [#403](https://github.com/luyadev/luya-module-admin/pull/403) Use ngRestFind() method for duplicate button instead of find().

## 2.2.2 (23. October 2019)

+ [#388](https://github.com/luyadev/luya-module-admin/pull/388) Fixed bug when using DuplicateActiveButton with properties which resolve an object in the after find event.
+ [#383](https://github.com/luyadev/luya-module-admin/issues/383) Added `beforeListFind` callable property for ngrest plugins.

## 2.2.1 (3. October 2019)

+ [#373](https://github.com/luyadev/luya-module-admin/pull/373) Added new methods to batch insert tag relations, ensure tag relation table does not contain db prefix when saving.
+ [#370](https://github.com/luyadev/luya-module-admin/pull/370) Added new `zaa-tag-array` directive which generates an array of selected tag ids assigned to the model.
+ [#369](https://github.com/luyadev/luya-module-admin/pull/369) Added `toggleRelation` option for Tags model and common api to toggle tags on a certain item.
+ [#367](https://github.com/luyadev/luya-module-admin/issues/367) Fixed bug with checkbox properties and default values in admin context.

## 2.2.0 (17. September 2019)

> When you make Api Requests trough Api Users, turn on `apiUserAllowActionsWithoutPermissions` on order to allow access to actions without permissions entry (behavior of version 2.1 and below) or add permissions, read more in the [Upgrade document](UPGRADE.md). This change was required in order to make Api Users more secure.

### Changed

+ [#358](https://github.com/luyadev/luya-module-admin/pull/358) Forbid the call of actions without permission entries when authorized as Api User. Along with this permission improvement both RestActiveController and RestController now perform an `beforeAction()` check against `actionPermissions()` or `permissionRoute()`.

### Fixed

+ [#363](https://github.com/luyadev/luya-module-admin/issues/363) Fixed bug when display package infos in LUYA Developer mode.
+ [#2](https://github.com/luyadev/luya-module-admin/issues/2) Fixed UX issue with none clickable clock icon.
+ [#356](https://github.com/luyadev/luya-module-admin/issues/356) Hide NgRest attribute groups when no fields are contained.
+ [#361](https://github.com/luyadev/luya-module-admin/issues/361) Disabled tag filter bar when any filter is active.
+ [#343](https://github.com/luyadev/luya-module-admin/pull/343) Fixed bug with migrations when using PostgreSQL.

### Added

+ [#339](https://github.com/luyadev/luya-module-admin/issues/339) Show public download link to file in file manager detail view.
+ [#340](https://github.com/luyadev/luya-module-admin/issues/340) Added new authentification system for JWT based on ApiUser. 
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
