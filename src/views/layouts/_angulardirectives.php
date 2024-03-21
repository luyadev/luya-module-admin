<?php
use luya\admin\Module as Admin;

?>
<div class="loading-overlay" ng-if="LuyaLoading.getState()">
    <div class="loading-overlay-content">
        <h3 class="loading-overlay-title">
            {{LuyaLoading.getStateMessage()}}
        </h3>
        <div class="loading-overlay-loader">
            <div class="loading-indicator">
                <div class="rect1"></div><!--
                --><div class="rect2"></div><!--
                --><div class="rect3"></div><!--
                --><div class="rect4"></div><!--
                --><div class="rect5"></div>
            </div>
        </div>
    </div>
</div>
<script type="text/ng-template" id="modal">
<div class="modal" tabindex="-1" aria-hidden="true" ng-class="{'show':!isModalHidden}" zaa-esc="escModal()">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{title}}</h5>
                <div class="modal-close">
                    <button type="button" class="close" aria-label="Close" ng-click="isModalHidden=1">
                        <span aria-hidden="true"><span class="modal-esc">ESC</span> &times;</span>
                    </button>
                </div>
            </div>
            <div class="modal-body" ng-transclude></div>
        </div>
    </div>
</div>
</script>
<!-- Sub template of zaa-link -->
<script type="text/ng-template" id="linkoptions.html">
<div>
	<div class="form-group form-side-by-side">
		<div class="form-side form-side-label">
			<label><?= Admin::t('link_dir_target'); ?></label>
		</div>
		<div class="form-side">
			<select ng-model="data.target" class="form-control">
                <option value="">-</option>
				<option value="_self"><?= Admin::t('link_dir_target_same'); ?></option>
				<option value="_blank"><?= Admin::t('link_dir_target_blank'); ?></option>
			</select>
		</div>
	</div>
    <div class="form-group form-side-by-side">
        <div class="form-side form-side-label">
            <label><?= Admin::t('view_index_redirect_type'); ?></label>
        </div>
        <div class="form-side">
            <input type="radio" ng-model="data.type" ng-value="1" id="{{uid}}redirect_internal">
            <label for="{{uid}}redirect_internal" ng-click="data.type = 1"><?= Admin::t('view_index_redirect_internal'); ?></label>

            <input type="radio" ng-model="data.type" ng-value="2" id="{{uid}}redirect_external">
            <label for="{{uid}}redirect_external" ng-click="data.type = 2"><?= Admin::t('view_index_redirect_external'); ?></label>

			<input type="radio" ng-model="data.type" ng-value="3" id="{{uid}}to_file">
            <label for="{{uid}}to_file" ng-click="data.type = 3"><?= Admin::t('view_index_redirect_file'); ?></label>

			<input type="radio" ng-model="data.type" ng-value="4" id="{{uid}}to_mail">
            <label for="{{uid}}to_mail" ng-click="data.type = 4"><?= Admin::t('view_index_redirect_mail'); ?></label>

            <input type="radio" ng-model="data.type" ng-value="5" id="{{uid}}to_telepone">
            <label for="{{uid}}to_telephone" ng-click="data.type = 5"><?= Admin::t('view_index_redirect_telephone'); ?></label>
        </div>
    </div>
    <div class="form-group form-side-by-side">
        <div class="form-side form-side-label"></div>
        <div class="form-side">
            <div ng-switch on="data.type">
                <div ng-switch-when="1">
                    <p><?= Admin::t('view_index_redirect_internal_select'); ?></p>
                    <div>
                        <menu-dropdown class="menu-dropdown" nav-id="data.value"></menu-dropdown>
                    </div>
                    <div class="pt-3">
                        <zaa-text model="data.anchor" label="<?= Admin::t('view_index_redirect_anchor_label'); ?>" placeholder="<?= Admin::t('view_index_redirect_anchor_hint'); ?>"></zaa-text>
                    </div>
                </div>
                <div ng-switch-when="2">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i class="material-icons">link</i>
                                </div>
                            </div>
                            <input type="text" class="form-control" ng-model="data.value" placeholder="http://">
                        </div>
                        <small class="form-text text-muted"><?= Admin::t('view_index_redirect_external_link_help'); ?></small>
                    </div>
                </div>
				<div ng-switch-when="3">
					<storage-file-upload ng-model="data.value"></storage-file-upload>
			    </div>
				<div ng-switch-when="4">
					<input class="form-control" type="text" ng-model="data.value" />
					<p class="mt-1"><small><?= Admin::t('view_index_redirect_mail_help'); ?></small></p>
			    </div>
                <div ng-switch-when="5">
                    <input class="form-control" type="tel" ng-model="data.value" placeholder="(\+00)9999-9999" />
                    <p class="mt-1"><small><?= Admin::t('view_index_redirect_telephone_help'); ?></small></p>
                </div>
            </div>
        </div>
    </div>
</div>
</script>
<!-- /UPDATE REDIRECT FORM -->


<script type="text/ng-template" id="menuDropdownReverse">
    <span class="treeview-label treeview-label-page" ng-click="changeModel(data)">
        <span class="treeview-icon">
            <input type="radio" ng-checked="data.id == navId" id="toggler-for-{{data.id}}" />
            <label for="toggler-for-{{data.id}}"></label>
        </span>
        <span class="treeview-link" >
            <span class="google-chrome-font-offset-fix">{{data.title}}</span>
        </span>
    </span>
    <ul class="treeview-items">
        <li class="treeview-item" ng-class="{'treeview-item-has-children' : (menuData.items | menuparentfilter:container.id:data.id).length}" ng-repeat="data in menuData.items | menuparentfilter:container.id:data.id track by data.id" ng-include="'menuDropdownReverse'"></li>
    </ul>
</script>

<script type="text/ng-template" id="storageFileUpload">
    <div class="link-selector">
        <?php if (Yii::$app->adminuser->canRoute('admin/storage/index')): ?>
        <div class="link-selector-actions">
            <div class="link-selector-btn btn btn-secondary" ng-click="toggleModal()">
                <i class="material-icons left" ng-show="!fileinfo.name_original">file_upload</i>
                <i class="material-icons left" ng-show="fileinfo.name_original">attach_file</i>
                <span ng-if="fileinfo.name_original">{{fileinfo.name_original | truncateMiddle: 30}}</span>
                <span ng-if="!fileinfo.name_original">
                    <?= Admin::t('layout_select_file'); ?>
                </span>
            </div>
            <span class="link-selector-reset" ng-click="reset()" ng-show="fileinfo!=null">
                <i class="material-icons">remove_circle</i>
            </span>
        </div>
        <modal is-modal-hidden="modal.state" modal-title="<?= Admin::t('layout_select_file'); ?>">
			<div ng-if="!modal.state">
				<storage-file-manager selection="true"></storage-file-manager>
			</div>
		</modal>
        <?php else: ?>
            <p class="text-muted"><?= Admin::t('missing_file_upload_permission'); ?></p>
        <?php endif; ?>
    </div>
</script>

<script type="text/ng-template" id="storageImageUpload">
    <div class="imageupload">
        <div ng-if="imageNotFoundError" class="alert alert-danger" style="margin-top:0px;">The requested image id ({{ngModel}}) could not be found anymore. The orignal file has been deleted in the filemanager!</div>
        <div ng-show="originalFileIsRemoved">
            <div class="alert alert-danger"><?= Admin::t('layout_deleted_file'); ?></div>
        </div>
        <div class="imageupload-preview" ng-show="imageinfo != null">
            <div class="imageupload-preview-sizer"></div>
            <img ng-src="{{thumb.source}}" ng-show="imageinfo != null" class="imageupload-preview-image" />
            <div class="imageupload-infos">
                <div class="imageupload-size" ng-show="!imageLoading">{{ imageinfo.resolution_width }} x {{ imageinfo.resolution_height }}</div>
            </div>
        </div>
        <div class="imageupload-upload">
            <storage-file-upload ng-model="fileId"></storage-file-upload>
        </div>
        <?php if (Yii::$app->adminuser->canRoute('admin/storage/index')): ?>
        <div class="imageupload-filter" ng-show="!noFilters() && imageinfo != null">
            <select name="filterId" ng-model="filterId" ng-change="changeFilter()" convert-to-number>
                <option value="0"><?= Admin::t('layout_no_filter'); ?></option>
                <option ng-repeat="item in filtersData" value="{{ item.id }}">{{ item.name }} ({{ item.identifier }})</option>
            </select>
        </div>
        <?php endif; ?>
    </div>
</script>

<script type="text/ng-template" id="reverseFolders">
    <div class="folders-folder" ng-init="editFolderLabel = false; oldFolder = folder.name" ng-class="{'folders-folder--edit': editFolderLabel && !showFoldersToMove, 'folders-folder--move-to': showFoldersToMove}" tooltip tooltip-expression="folderCountMessage(folder)" tooltip-position="right">
        <div class="folder-left">
            <button class="folder-toggler" ng-click="toggleFolderItem(folder)" ng-if="folder.subfolder == true">
                <i class="material-icons" ng-if="folder.toggle_open">keyboard_arrow_down</i>
                <i class="material-icons" ng-if="!folder.toggle_open">keyboard_arrow_right</i>
            </button>
        </div>
        <div class="folder-middle">
            <span ng-click="changeCurrentFolderId(folder.id)">
                <div class="folder-icon">
                    <i class="material-icons" ng-if="currentFolderId == folder.id">folder</i>
                    <i class="material-icons" ng-if="currentFolderId != folder.id">folder_open</i>
                </div>
                <div class="folder-label" ng-class="{'is-active' : hasFolderActiveChild(folder.id)}">{{ folder.name }}</div>
            </span>
            <div class="folder-edit">
                <input class="folder-edit-input" ng-model="folder.name" type="text" />
            </div>
        </div>
        <div class="folder-right folder-action-default">
            <button class="folder-button folder-button--edit" ng-click="editFolderLabel=!editFolderLabel; currentEditFolder=folder"><i class="material-icons">edit</i></button>
            <button class="folder-button folder-button--delete" ng-hide="folder.subfolder || folder.filesCount > 0" ng-click="deleteFolder(folder)"><i class="material-icons">delete</i></button>
        </div>
        <div class="folder-right folder-action-edit">
            <button class="folder-button folder-button--save" ng-click="updateFolder(folder); editFolderLabel=!editFolderLabel"><i class="material-icons">check</i></button>
            <button class="folder-button" ng-click="isFolderMoveModalHidden=!isFolderMoveModalHidden"><i class="material-icons">subdirectory_arrow_right</i></button>
            <button class="folder-button folder-button--abort" ng-click="cancelFolderEdit(folder, oldFolder); editFolderLabel=!editFolderLabel;"><i class="material-icons">cancel</i></button>
        </div>
        <div class="folder-right folder-action-move-to">
            <button class="folder-button folder-button--move-to" ng-click="moveFilesTo(folder.id)"><i class="material-icons">subdirectory_arrow_left</i></button>
        </div>
        <modal is-modal-hidden="isFolderMoveModalHidden" modal-title="{{ folder.name }}">
            <div ng-if="!isFolderMoveModalHidden">
                <button type="button" class="btn my-1 btn-icon btn-outline-folder" ng-click="moveFolderTo(currentEditFolder, 0)"><?= Admin::t('layout_filemanager_root_dir'); ?></button>
                <div ng-repeat="folderlist in getFolderData(0) track by folderlist.id" ng-include="'folderMoveTemplate'"></div>
            </div>
        </modal>
    </div>
    <ul class="folders" ng-if="folder.subfolder === true && folder.toggle_open==1">
        <li class="folders-folder-item" ng-class="{'is-movable' : showFoldersToMove}" ng-repeat="folder in getFolderData(folder.id) track by folder.id" ng-include="'reverseFolders'"></li>
    </ul>
</script>

<script type="text/ng-template" id="folderMoveTemplate">
    <div>
        <button type="button" class="btn my-1 btn-icon btn-outline-folder" ng-click="moveFolderTo(currentEditFolder, folderlist.id)" ng-disabled="folderlist.id == currentEditFolder.id || folderlist.id == currentEditFolder.parentId">{{ folderlist.name }}</button>
        <div style="margin-left:20px;" ng-repeat="folderlist in getFolderData(folderlist.id) track by folderlist.id" ng-include="'folderMoveTemplate'"></div>
    </div>
</script>

<!-- FILEMANAGER -->
<script type="text/ng-template" id="storageFileManager">
<div class="filemanager"  ng-paste="pasteUpload($event)">
        <!-- Folders -->
        <div class="filemanager-folders">
            <div class="filemanager-add-folder">
                <div class="btn btn-icon btn-add-folder btn-success" ng-click="folderFormToggler()" ng-if="!showFolderForm">
                   <?= Admin::t('layout_filemanager_add_folder'); ?>
                </div>
                <div class="filemanager-add-folder-form" ng-if="showFolderForm">
                    <input class="filemanager-add-folder-input" type="text" placeholder="<?php echo Admin::t('layout_filemanager_folder'); ?>" title="<?php echo Admin::t('layout_filemanager_folder'); ?>" ng-model="newFolderName" />
                    <div class="filemanager-add-folder-actions">
                        <button class="btn btn-icon btn-save" ng-click="createNewFolder(newFolderName)"></button>
                        <button class="btn btn-icon btn-cancel" ng-click="folderFormToggler()"></button>
                    </div>
                </div>
            </div>
            <ul class="folders mt-4">
                <li class="folders-folder folders-folder-main" ng-class="{'is-active' : currentFolderId == 0}">
                    <div class="folder-middle">
                        <span ng-click="changeCurrentFolderId(0)">
                            <div class="folder-icon">
                                <i class="material-icons" ng-if="currentFolderId == 0">folder</i>
                                <i class="material-icons" ng-if="currentFolderId != 0">folder_open</i>
                            </div>

                            <div class="folder-label"><?= Admin::t('layout_filemanager_root_dir'); ?></div>
                        </span>
                    </div>
                    <ul class="folders">
                        <li class="folders-folder-item" ng-class="{'is-movable' : showFoldersToMove}" ng-repeat="folder in getFolderData(0) track by folder.id" ng-include="'reverseFolders'"></li>
                    </ul>
                </li>
            </ul>
        </div>
        <!-- /Folders -->
        <!-- Files -->
        <div class="filemanager-files">
            <div class="filemanager-file-actions">
                <div class="filemanager-file-actions-left" ng-class="{'filemanager-file-actions-left-spacing': selectedFiles.length > 0}">
                    <div spellcheck="false" class="btn btn-icon btn-upload filemanager-upload-file" ngf-enable-firefox-paste="true" ngf-drag-over-class="'dragover'" ngf-drop ngf-select ngf-multiple="true" ng-model="uploadingfiles">
                        <?= Admin::t('layout_filemanager_upload_files'); ?>
                    </div>
                    <input class="filemanager-search" type="text" ng-change="runSearch()" ng-model="searchQuery" placeholder="<?= Admin::t('layout_filemanager_search_text') ?>" />
                </div>
                <div class="filemanager-file-actions-right" ng-show="selectedFiles.length > 0">
                    <button class="btn btn-icon btn-move" ng-class="{'btn-move-active' : showFoldersToMove}" ng-click="showFoldersToMove=!showFoldersToMove">
                       <?= Admin::t('layout_filemanager_move_selected_files'); ?>
                    </button>
                    <button type="button" class="btn btn-icon btn-delete" ng-click="removeFiles()">
                        <?= Admin::t('layout_filemanager_remove_selected_files'); ?> ({{selectedFiles.length}})
                    </button>
                </div>
            </div>
            <div class="filemanager-files-table">
                <ol class="breadcrumb small mb-0 mt-3" ng-show="!searchQuery">
                    <li class="breadcrumb-item"><a ng-click="changeCurrentFolderId(0)"><?= Admin::t('layout_filemanager_root_dir'); ?></a></li>
                    <li class="breadcrumb-item" ng-repeat="fo in folderInheritance | reverse"><a ng-click="changeCurrentFolderId(fo.id)">{{ fo.name }}</a></li>
                </ol>
                <div class="table-responsive" ng-show="filesData.length > 0">
                    <table class="table table-hover table-striped table-align-middle">
                        <thead class="thead-default">
                            <tr>
                                <th>
                                    <span ng-hide="allowSelection == 'true'" class="filemanager-check-all" ng-click="toggleSelectionAll()">
                                        <i class="material-icons">done_all</i>
                                    </span>
                                </th>
                                <th></th><!-- image thumbnail / file icon -->
                                <th>
                                    <span ng-if="sortField!='name_original' && sortField!='-name_original'" ng-click="changeSortField('-name_original')"><?= Admin::t('layout_filemanager_col_name'); ?></span>    
                                    <div class="table-sorter-wrapper is-active">
                                        <div ng-if="sortField=='name_original'" class="table-sorter table-sorter-up is-sorting" ng-click="changeSortField('-name_original')">
                                            <span><?= Admin::t('layout_filemanager_col_name'); ?></span>    
                                            <i class="material-icons">keyboard_arrow_up</i>
                                        </div>
                                        <div ng-if="sortField=='-name_original'" class="table-sorter table-sorter-up is-sorting" ng-click="changeSortField('name_original')">
                                            <span><?= Admin::t('layout_filemanager_col_name'); ?></span>    
                                            <i class="material-icons">keyboard_arrow_down</i>
                                        </div>
                                    </div>
                                </th>
                                <th>
                                    <span ng-if="sortField!='extension' && sortField!='-extension'" ng-click="changeSortField('-extension')"><?= Admin::t('layout_filemanager_col_type'); ?></span>    
                                    <div class="table-sorter-wrapper is-active">
                                        <div ng-if="sortField=='extension'" class="table-sorter table-sorter-up is-sorting" ng-click="changeSortField('-extension')">
                                            <span><?= Admin::t('layout_filemanager_col_type'); ?></span>    
                                            <i class="material-icons">keyboard_arrow_up</i>
                                        </div>
                                        <div ng-if="sortField=='-extension'" class="table-sorter table-sorter-up is-sorting" ng-click="changeSortField('extension')">
                                            <span><?= Admin::t('layout_filemanager_col_type'); ?></span>    
                                            <i class="material-icons">keyboard_arrow_down</i>
                                        </div>
                                    </div>
                                </th>
                                <th>
                                    <span ng-if="sortField!='upload_timestamp' && sortField!='-upload_timestamp'" ng-click="changeSortField('-upload_timestamp')"><?= Admin::t('layout_filemanager_col_date'); ?></span>    
                                    <div class="table-sorter-wrapper is-active">
                                        <div ng-if="sortField=='upload_timestamp'" class="table-sorter table-sorter-up is-sorting" ng-click="changeSortField('-upload_timestamp')">
                                            <span><?= Admin::t('layout_filemanager_col_date'); ?></span>    
                                            <i class="material-icons">keyboard_arrow_up</i>
                                        </div>
                                        <div ng-if="sortField=='-upload_timestamp'" class="table-sorter table-sorter-up is-sorting" ng-click="changeSortField('upload_timestamp')">
                                            <span><?= Admin::t('layout_filemanager_col_date'); ?></span>    
                                            <i class="material-icons">keyboard_arrow_down</i>
                                        </div>
                                    </div>
                                </th>
                                <th>
                                    <span ng-if="sortField!='file_size' && sortField!='-file_size'" ng-click="changeSortField('-file_size')"><?= Admin::t('layout_filemanager_col_size'); ?></span>    
                                    <div class="table-sorter-wrapper is-active">
                                        <div ng-if="sortField=='file_size'" class="table-sorter table-sorter-up is-sorting" ng-click="changeSortField('-file_size')">
                                            <span><?= Admin::t('layout_filemanager_col_size'); ?></span>    
                                            <i class="material-icons">keyboard_arrow_up</i>
                                        </div>
                                        <div ng-if="sortField=='-file_size'" class="table-sorter table-sorter-up is-sorting" ng-click="changeSortField('file_size')">
                                            <span><?= Admin::t('layout_filemanager_col_size'); ?></span>    
                                            <i class="material-icons">keyboard_arrow_down</i>
                                        </div>
                                    </div>
                                </th>
                                <th class="tab-padding-right text-right filemanager-actions-column"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                ng-repeat="file in filesData" class="filemanager-file"
                                ng-class="{ 'clickable selectable' : allowSelection == 'false', 'filemanager-file-selected': selectedFileFromParent && selectedFileFromParent.id == file.id, 'filemanager-file-detail-open': fileDetail.id === file.id}"
                            >
                                <th scope="row" ng-click="toggleSelection(file)">
                                    <div class="form-check filemanager-toggle-selection" ng-class="{'form-check-active': inSelection(file)}">
                                        <input type="checkbox" ng-checked="inSelection(file)" class="form-check-input">
                                        <label></label>
                                    </div>
                                </th>
                                <td class="text-center" ng-click="toggleSelection(file)" tooltip tooltip-position="left" tooltip-image-url="{{file.createThumbnailMedium.source}}?{{file.upload_timestamp}}" tooltip-disabled="!file.isImage">
                                    <span ng-if="file.isImage"><img class="responsive-img filmanager-thumb" ng-src="{{file.createThumbnail.source}}?{{file.upload_timestamp}}" /></span>
                                    <span ng-if="!file.isImage"><i class="material-icons custom-color-icon">attach_file</i></span>
                                </td>
                                <td ng-click="toggleSelection(file)" tooltip tooltip-position="left" tooltip-text="{{file.id}}">{{file.name_original | truncateMiddle: 50}}</td>
                                <td ng-click="openFileDetail(file)">{{file.extension}}</td>
                                <td ng-click="openFileDetail(file)">{{file.upload_timestamp * 1000 | date:"short"}}</td>
                                <td ng-click="openFileDetail(file)">{{file.sizeReadable}}</td>
                                <td class="text-right">
                                    <button type="button" class="btn btn-icon btn-sm filemanager-info-btn" ng-click="openFileDetail(file)">
                                        <i class="material-icons">info</i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-4" ng-show="filesData.length == 0">
                    <p ng-show="searchQuery && !searchLoading"><?= Admin::t('layout_filemanager_search_no_results'); ?></p>
                    <p ng-show="!searchQuery" class="mb-4 text-muted"><?= Admin::t('layout_filemanager_empty_folder'); ?></p>
                    <div ng-show="!searchQuery" spellcheck="false" class="btn btn-outline-success p-4" ngf-enable-firefox-paste="true" ngf-drag-over-class="'dragover'" ngf-drop ngf-select ngf-multiple="true" ng-model="uploadingfiles">
                        <i class="material-icons">file_upload</i> <?= Admin::t('layout_filemanager_upload_files'); ?>
                    </div>
                </div>
                <div class="filemanager-pagination">
                    <pagination current-page="currentPageId" page-count="pageCount"></pagination>
                </div>
            </div>
        </div>
        <!-- /Files -->
    </div>

    <div class="file-detail-view shadow" ng-class="{'open': fileDetail}">
        <div ng-if="detailLoading">
            <div class="loading-indicator mt-5" style="height:30px;">
                <div class="rect1"></div><!--
                --><div class="rect2"></div><!--
                --><div class="rect3"></div><!--
                --><div class="rect4"></div><!--
                --><div class="rect5"></div>
            </div>
        </div>
        <div ng-if="!detailLoading">
            <div class="file-detail-view-head">
                <a class="btn btn-icon btn-download" ng-href="{{fileDetailFull.file.href}}?{{fileDetailFull.upload_timestamp}}" target="_blank">Download</a>
                <button type="button" class="btn btn-icon btn-replace ml-2" type="file" ngf-keep="false" ngf-select="replaceFile($file, $invalidFiles)">Replace</button>
                <button type="button" class="btn ml-2" ng-click="editFile(fileDetail)" ng-show="fileDetail.isImage"><i class="material-icons">crop</i></button>
                <button type="button" class="btn btn-icon btn-delete ml-2" ng-click="removeFile(fileDetail)"></button>
                <button type="button" class="btn btn-icon btn-cancel file-detail-view-close" ng-click="closeFileDetail()"></button>
            </div>

            <p class="mt-3" ng-show="!nameEditMode">{{ fileDetailFull.name_original }}</p>


            <modal is-modal-hidden="isFileEditHidden" modal-title="<?= Admin::t('crop_modal_title'); ?>">
                <image-edit ng-if="!isFileEditHidden" file-id="fileDetailFull.id" on-success="cropSuccess()"></image-edit>
            </modal>

            <div ng-if="fileDetail.isImage" class="mt-3 text-center">
                <modal is-modal-hidden="largeImagePreviewState" modal-title="{{ fileDetailFull.file.name }}">
                    <div class="text-center">
                        <img class="img-fluid" alt="{{ fileDetailFull.file.name }}" ng-src="{{fileDetailFull.file.source}}?{{fileDetailFull.upload_timestamp}}" />
                    </div>
                </modal>
                <img class="img-fluid" alt="{{ fileDetail.name }}" ng-click="largeImagePreviewState=!largeImagePreviewState" title="{{ fileDetailFull.name }}" style="border:1px solid #F0F0F0" ng-src="{{fileDetail.createThumbnailMedium.source}}?{{fileDetailFull.upload_timestamp}}" />
            </div>
            <collapse-container class="mt-3" title="<?= Admin::t('layout_filemanager_detail_details'); ?>">
            <input type="text" class="form-control form-control-sm" readonly select-on-click ng-model="fileDetailFull.source" />
            <table class="table table-hover table-align-middle mt-3">
                <tbody>
                <tr>
                    <td><small><?= Admin::t('model_pk_id'); ?></small></td>
                    <td>{{ fileDetail.id }}</td>
                </tr>
                <tr>
                    <td><small>Folder</small></td>
                    <td>
                        <a ng-show="fileDetailFolder" ng-click="changeCurrentFolderId(fileDetailFolder.id)">{{ fileDetailFolder.name }}</a>
                        <a ng-show="!fileDetailFolder" ng-click="changeCurrentFolderId(0)"><?= Admin::t('layout_filemanager_root_dir'); ?></a>
                    </td>
                </tr>
                <tr>
                    <td><small><?= Admin::t('layout_filemanager_col_date'); ?></small></td>
                    <td>{{fileDetailFull.upload_timestamp * 1000 | date:"dd.MM.yyyy, HH:mm"}}</td>
                </tr>
                <tr>
                    <td><small><?= Admin::t('layout_filemanager_col_type'); ?></small></td>
                    <td>{{ fileDetailFull.extension }}</td>
                </tr>
                <tr>
                    <td><small><?= Admin::t('layout_filemanager_col_size'); ?></small></td>
                    <td>{{ fileDetail.sizeReadable }}</td>
                </tr>
                <tr>
                    <td><small><?= Admin::t('layout_filemanager_col_downloads'); ?></small></td>
                    <td>{{ fileDetailFull.passthrough_file_stats }}</td>
                </tr>
                <tr>
                    <td><small><?= Admin::t('layout_filemanager_col_upload_user'); ?></small></td>
                    <td>{{ fileDetailFull.user.firstname}} {{ fileDetailFull.user.lastname}}</td>
                </tr>
                <tr ng-if="fileDetailFull">
                    <td><small><?= Admin::t('layout_filemanager_col_file_disposition'); ?></small></td>
                    <td>
                        <select class="form-control form-control-sm" ng-model="fileDetailFull.inline_disposition">
                            <option ng-value="0"><?= Admin::t('layout_filemanager_col_file_disposition_download'); ?></option>
                            <option ng-value="1"><?= Admin::t('layout_filemanager_col_file_disposition_browser'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <input class="form-control form-control-sm" type="text" ng-model="fileDetailFull.name_original" />
                    </td>
                </tr>
                </tbody>
            </table>
            
            <button type="button" class="btn btn-icon btn-save" ng-click="updateFileData()"><?= Admin::t('layout_filemanager_file_captions_save_btn'); ?></button>
            </collapse-container>
            <collapse-container class="mt-3" title="<?= Admin::t('layout_filemanager_file_captions'); ?>">
            <form class="bg-faded">
                <div class="form-group" ng-repeat="(key, cap) in fileDetailFull.file.captionArray">
                    <div class="input-group">
                        <input type="text" class="form-control" ng-model="fileDetailFull.file.captionArray[key]">
                        <span class="flag flag--{{key}}">
                            <span class="flag-fallback">{{key}}</span>
                        </span>
                    </div>
                </div>
                <button type="button" class="btn btn-icon btn-save" ng-click="storeFileCaption(fileDetailFull.file)"><?= Admin::t('layout_filemanager_file_captions_save_btn'); ?></button>
            </form>
            </collapse-container>
            <collapse-container class="mt-3" ng-show="tags.length > 0" title="<?= Admin::t('menu_system_item_tags'); ?>">
            <span style="font-size:15px;" 
                ng-repeat="tag in tags"
                ng-click="saveTagRelation(tag, fileDetailFull)"
                ng-class="{'badge-primary font-weight-bold text-bold': fileHasTag(tag), 'badge-secondary': !fileHasTag(tag)}"
                class="badge badge-pill mx-1 mb-2"
            >{{tag.name}}</span>
            </collapse-container>
        </div>
    </div>
</script>

<!-- /ANGULAR SCRIPTS -->
