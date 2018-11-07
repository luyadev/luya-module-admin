

	zaa.controller("DefaultDashboardObjectController", ['$scope', '$http', '$sce', function($scope, $http, $sce) {

		$scope.data;

		$scope.loadData = function(dataApiUrl) {
			$http.get(dataApiUrl).then(function(success) {
				$scope.data = success.data;
			});
		};
	}]);

	/**
	 * Base Crud Controller
	 *
	 * Assigned config variables from the php view assigned from child to parent:
	 *
	 * + bool $config.inline Determines whether this crud is in inline mode orno
	 */
	zaa.controller("CrudController", ['$scope', '$rootScope', '$filter', '$http', '$sce', '$state', '$timeout', '$injector', '$q', 'AdminLangService', 'LuyaLoading', 'AdminToastService', 'CrudTabService', 'ServiceImagesData', function($scope, $rootScope, $filter, $http, $sce, $state, $timeout, $injector, $q, AdminLangService, LuyaLoading, AdminToastService, CrudTabService, ServiceImagesData) {

		$scope.toast = AdminToastService;

		$scope.AdminLangService = AdminLangService;

		$scope.tabService = CrudTabService;

		/***** TABS AND SWITCHES *****/

		/**
		 * 0 = list
		 * 1 = add
		 * 2 = edit
		 */
		$scope.crudSwitchType = 0;

		$scope.switchToTab = function(tab) {
			angular.forEach($scope.tabService.tabs, function(item) {
				item.active = false;
			});

			tab.active = true;

			$scope.switchTo(4);
		};

		$scope.addAndswitchToTab = function(pk, route, index, label, model) {
			$scope.tabService.addTab(pk, route, index, label, model);

			$scope.switchTo(4);
		}

		$scope.closeTab = function(tab, index) {
			$scope.tabService.remove(index, $scope);
		};

		$scope.switchTo = function(type, reset) {
			if ($scope.config.relationCall) {
				$scope.crudSwitchType = type;
				return;
			}

			if (reset) {
				$scope.resetData();
			}

			if (type == 0) {
				$http.get($scope.config.apiEndpoint + '/unlock', {ignoreLoadingBar: true});
			}

			if (type == 0 || type == 1) {
				if (!$scope.config.inline) {
					$state.go('default.route');
				}
			}
			$scope.crudSwitchType = type;

			if (type !== 4 && !$scope.config.inline) {
				angular.forEach($scope.tabService.tabs, function(item) {
					item.active = false;
				});
			}
		};

		$scope.closeUpdate = function () {
			$scope.switchTo(0, true);
	    };

		$scope.closeCreate = function() {
			$scope.switchTo(0, true);
		};

		$scope.activeWindowModal = true;

		$scope.openActiveWindow = function() {
			$scope.activeWindowModal = false;
		};

		$scope.closeActiveWindow = function() {
			$scope.activeWindowModal = true;
		};

		$scope.changeGroupByField = function() {
			if ($scope.config.groupByField == 0) {
				$scope.config.groupBy = 0;
			} else {
				$scope.config.groupBy = 1;
			}
		};

		/********* SETTINGS DROPDOWN MENU ******/

		$scope.isSettingsVisible = false;

		$scope.toggleSettingsMenu = function() {
			$scope.isSettingsVisible = !$scope.isSettingsVisible;
		};

		$scope.hiddeSettingsMenu = function() {
			$scope.isSettingsVisible = false;
		};

		/********* NEW EXPORT MODAL ******/

		$scope.isExportModalHidden = true;

		$scope.exportdata = {header:1,type:"xlsx"};

		$scope.toggleExportModal = function() {
			$scope.isExportModalHidden = !$scope.isExportModalHidden;
		};

		$scope.exportResponse = false;

		$scope.generateExport = function() {
			$http.post($scope.config.apiEndpoint + '/export', $scope.exportdata).then(function(response) {
				$scope.exportResponse = response.data;
			});
		};

		$scope.downloadExport = function() {
			var url = $scope.exportResponse.url;
			$scope.exportResponse = false;
			window.open(url);
			return false;
		};

		/********** CRUD LIST *******/

		$scope.applySaveCallback = function() {
			if ($scope.config.saveCallback) {
				$injector.invoke($scope.config.saveCallback, this);
			}
		};

		/*********** ORDER **********/

		$scope.isOrderBy = function(field) {
			if (field == $scope.config.orderBy) {
				return true;
			}

			return false;
		};

		$scope.changeOrder = function(field, sort) {
			$scope.config.orderBy = sort + field;
			$http.post('admin/api-admin-common/ngrest-order', {'apiEndpoint' : $scope.config.apiEndpoint, sort: sort, field: field}, { ignoreLoadingBar: true });
			$scope.loadList();
		};

		/****************** ACTIVE BUTTON ***********/

		$scope.callActiveButton = function(hash, id, event) {
			var elmn = angular.element(event.currentTarget);
			elmn.addClass('crud-buttons-button-loading');
			$http.get($scope.config.apiEndpoint + '/active-button?hash=' + hash + '&id=' + id.join()).then(function(success) {
				elmn.removeClass('crud-buttons-button-loading');
				elmn.addClass('crud-buttons-button-success');
				$timeout(function() {
					elmn.removeClass('crud-buttons-button-success');
				}, 5000);

				angular.forEach(success.data.events, function(value) {
					// event names
					if (value == 'loadList') {
						$scope.loadList();
					}
				});

				AdminToastService.success(success.data.message);
			}, function(error) {
				elmn.removeClass('crud-buttons-button-loading');
				elmn.addClass('crud-buttons-button-danger');
				$timeout(function() {
					elmn.removeClass('crud-buttons-button-danger');
				}, 5000);
				AdminToastService.error(error.data.message);
			});
		};

		/***************** ACTIVE WINDOW *********/

		$scope.reloadActiveWindow = function() {
			$scope.getActiveWindow($scope.data.aw.hash, $scope.data.aw.itemId);
		}

		$scope.getActiveWindow = function (activeWindowId, id, $event) {
			$http.post($scope.config.activeWindowRenderUrl, { itemId : id, activeWindowHash : activeWindowId , ngrestConfigHash : $scope.config.ngrestConfigHash })
			.then(function(response) {
				$scope.openActiveWindow();
				$scope.data.aw.itemId = id;
				$scope.data.aw.configCallbackUrl = $scope.config.activeWindowCallbackUrl;
				$scope.data.aw.configHash = $scope.config.ngrestConfigHash;
				$scope.data.aw.hash = activeWindowId;
				$scope.data.aw.content = $sce.trustAsHtml(response.data.content);
				$scope.data.aw.title = response.data.title;
				$scope.$broadcast('awloaded', {id: activeWindowId});
			})
		};

		$scope.getActiveWindowCallbackUrl = function(callback) {
			return $scope.data.aw.configCallbackUrl + '?activeWindowCallback=' + callback + '&ngrestConfigHash=' + $scope.data.aw.configHash + '&activeWindowHash=' + $scope.data.aw.hash;
		};

		/**
		 * new returns a promise promise.hten(function(answer) {
		 * 
		 * }, function(error) {
		 * 
		 * }, function(progress) {
		 * 
		 * });
		 *
		 * instead of return variable
		 */
		$scope.sendActiveWindowCallback = function(callback, data) {
			var data = data || {};
			return $http.post($scope.getActiveWindowCallbackUrl(callback), $.param(data), {
				headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'}
			});
		};

		/*************** SEARCH ******************/

		$scope.searchPromise = null;

		$scope.$watch('config.searchQuery', function(n, o) {
			if (n == o || n == undefined || n == null) {
				return;
			}
			$scope.applySearchQuery(n);
		});

		$scope.applySearchQuery = function(n) {
			if (n == undefined || n == null) {
				return;
			}
			$timeout.cancel($scope.searchPromise);
			if (n.length == 0) {
				$scope.loadList(1);
			} else {
				$scope.searchPromise = $timeout(function() {
					$http.post($scope.generateUrlWithParams('search'), {query: n}).then(function(response) {
						$scope.parseResponseQueryToListArray(response);
					});
				}, 1000)
			}
		};


		/******* RELATION CALLLS *********/

		/**
		 * Modal view select a value from a modal into its parent plugin.
		 */
		$scope.parentSelectInline = function(item) {
			$scope.$parent.$parent.$parent.setModelValue($scope.getRowPrimaryValue(item), item);
		};

		/**
		 * Check if a field exists in the parents relation list, if yes hide the field
		 * for the given form and return the relation call value instead in order to auto store those.
		 */
		$scope.checkIfFieldExistsInParentRelation = function(field) {
			// this call is relation call, okay check for the parent relation defition
			if ($scope.config.relationCall) {
				var relations = $scope.$parent.$parent.config.relations;

				var definition = relations[parseInt($scope.config.relationCall.arrayIndex)];

				var linkDefintion = definition.relationLink;

				if (linkDefintion !== null && linkDefintion.hasOwnProperty(field)) {
					return parseInt($scope.config.relationCall.id);
				}
			}

			return false;
		}

		$scope.relationItems = [];

		/****** DELETE, UPDATE, CREATE */

		$scope.deleteItem = function(id, $event) {
			AdminToastService.confirm(i18n['js_ngrest_rm_page'], i18n['ngrest_button_delete'], ['$toast', function($toast) {
				$http.delete($scope.config.apiEndpoint + '/'+id).then(function(response) {
					$scope.loadList();
					$toast.close();
					AdminToastService.success(i18n['js_ngrest_rm_confirm']);
				}, function(data) {
					$scope.printErrors(data);
				});
			}]);
		};

		$scope.toggleUpdate = function(id) {
			$scope.resetData();
			$http.get($scope.config.apiEndpoint + '/'+id+'?' + $scope.config.apiUpdateQueryString).then(function(response) {
				var data = response.data;
				$scope.data.update = data;

				if ($scope.config.relationCall) {

					$scope.crudSwitchType = 2;
				} else {
					$scope.switchTo(2);
				}
				if (!$scope.config.inline) {
					$state.go('default.route.detail', {id : id});
				}
				$scope.data.updateId = id;
			}, function(data) {
				AdminToastService.error(i18n['js_ngrest_error']);
			});
		};

		$scope.submitUpdate = function () {
			$http.put($scope.config.apiEndpoint + '/' + $scope.data.updateId, angular.toJson($scope.data.update, true)).then(function(response) {
				AdminToastService.success(i18n['js_ngrest_rm_update']);
				$scope.loadList();
				$scope.applySaveCallback();
				$scope.switchTo(0, true);
			}, function(response) {
				$scope.printErrors(response.data);
			});
		};

		$scope.submitCreate = function() {
			$http.post($scope.config.apiEndpoint, angular.toJson($scope.data.create, true)).then(function(response) {
				AdminToastService.success(i18n['js_ngrest_rm_success']);
				$scope.loadList();
				$scope.applySaveCallback();
				$scope.switchTo(0, true);
				$scope.resetData();
			}, function(data) {
				$scope.printErrors(data.data);
			});
		};

		$scope.printErrors = function(data) {
			angular.forEach(data, function(value, key) {
				AdminToastService.error(value.message);
			});
		};

		$scope.resetData = function() {
			$scope.data.create = angular.copy({});
			$scope.data.update = angular.copy({});
		};

		$scope.changeNgRestFilter = function() {
			$http.post('admin/api-admin-common/ngrest-filter', {'apiEndpoint' : $scope.config.apiEndpoint, 'filterName': $scope.config.filter}, { ignoreLoadingBar: true });
			$scope.loadList(1);
		};


		/*** PAGINIATION ***/

        $scope.$watch('pager.currentPage', function(newVal, oldVal) {
            if (newVal != oldVal) {
                $scope.loadList($scope.pager.currentPage);
            }
        });

        $scope.pager = {
			'currentPage': 1,
			'pageCount': 1,
			'perPage': 0,
			'totalItems': 0,
		};

		$scope.setPagination = function(currentPage, pageCount, perPage, totalItems) {
			$scope.totalRows = totalItems;
			$scope.pager = {
				'currentPage': currentPage,
				'pageCount': pageCount,
				'perPage': perPage,
				'totalItems': totalItems,
			};
		};

		/***** TOGGLER PLUGIN *****/


		$scope.toggleStatus = function(row, fieldName, fieldLabel, bindValue) {
			var invertValue = !bindValue;
			var invert = invertValue ? 1 : 0;
			var rowId = row[$scope.config.pk];
			var json = {};
			json[fieldName] = invert;
			$http.put($scope.config.apiEndpoint + '/' + rowId +'?ngrestCallType=update&fields='+fieldName, angular.toJson(json, true)).then(function(response) {
				row[fieldName] = invert;
				AdminToastService.success(i18nParam('js_ngrest_toggler_success', {field: fieldLabel}));
			}, function(data) {
				$scope.printErrors(data);
			});
		};

		/**** SORTABLE PLUGIN ****/

		$scope.sortableUp = function(index, row, fieldName) {
			var switchWith = $scope.data.listArray[index-1];
			$scope.data.listArray[index-1] = row;
			$scope.data.listArray[index] = switchWith;
			$scope.updateSortableIndexPositions(fieldName);
		};

		$scope.sortableDown = function(index, row, fieldName) {
			var switchWith = $scope.data.listArray[index+1];
			$scope.data.listArray[index+1] = row;
			$scope.data.listArray[index] = switchWith;
			$scope.updateSortableIndexPositions(fieldName);
		};

		$scope.updateSortableIndexPositions = function(fieldName) {
			angular.forEach($scope.data.listArray, function(value, key) {
				var json = {};
				json[fieldName] = key;
				var pk = $scope.getRowPrimaryValue(value);
				$http.put($scope.config.apiEndpoint + '/' + pk +'?ngrestCallType=update&fields='+fieldName, angular.toJson(json, true), {
					  ignoreLoadingBar: true
				});
			});
		};

		/***** LIST LOADERS ********/

		/**
		 * This method is triggerd by the crudLoader directive to reload service data.
		 */
		$scope.loadService = function() {
			$scope.initServiceAndConfig();
		};

		$scope.evalSettings = function(settings) {
			if (settings.hasOwnProperty('order')) {
				$scope.config.orderBy = settings['order'];
			}

			if (settings.hasOwnProperty('filterName')) {
				$scope.config.filter = settings['filterName'];
			}
		};

		$scope.getRowPrimaryValue = function(row) {
			var pk = $scope.config.pk;

			if (angular.isArray(pk)) {
				var values = [];
				angular.forEach(pk, function(name) {
					values.push(row[name]);
				});

				return values.join();
			}

			return row[$scope.config.pk];
		};

		$scope.initServiceAndConfig = function() {
			var deferred = $q.defer();
			$http.get($scope.config.apiEndpoint + '/services').then(function(serviceResponse) {
				$scope.service = serviceResponse.data.service;
				$scope.serviceResponse = serviceResponse.data;
				$scope.evalSettings(serviceResponse.data._settings);
				deferred.resolve();
			});

			return deferred.promise;
		};

		$scope.getFieldHelp = function(fieldName) {
			if ($scope.serviceResponse && $scope.serviceResponse['_hints'] && $scope.serviceResponse._hints.hasOwnProperty(fieldName)) {
				return $scope.serviceResponse._hints[fieldName];
			}

			return false;
		}

		$scope.loadList = function(pageId) {
			if (pageId == undefined && $scope.pager) {
				$scope.reloadCrudList($scope.pager.currentPage);
			} else {
				$scope.reloadCrudList(pageId);
			}
		};

		$scope.totalRows = 0;

		$scope.requestedImages = [];		

		/**
		 * Parse an Pagination (or not pagination) object into a response.
		 */
		$scope.parseResponseQueryToListArray = function(response) {
			$scope.setPagination(
				response.headers('X-Pagination-Current-Page'),
				response.headers('X-Pagination-Page-Count'),
				response.headers('X-Pagination-Per-Page'),
				response.headers('X-Pagination-Total-Count')
			);
			$scope.data.listArray = response.data;

			$scope.requestedImages = [];
			angular.forEach($scope.service, function(value, key) {
				// fix check for lazyload images property for service
				if (value.hasOwnProperty('lazyload_images')) {
					// yes
					angular.forEach(response.data, function(row) {
						$scope.requestedImages.push(row[key]);
					});
				}
			});
			
			$timeout(function() {
				ServiceImagesData.loadImages($scope.requestedImages).then(function() {
					$scope.$broadcast('requestImageSourceReady');
					$scope.requestedImages = [];
				});
			});
		};
		
		/**
		 * Exmaple
		 * 
		 * generateUrlWithParams('search', 1);
		 * generateUrlWithParams('list', 2);
		 */
		$scope.generateUrlWithParams = function(endpoint, pageId) {
			var url = $scope.config.apiEndpoint + '/'+endpoint+'?' + $scope.config.apiListQueryString;
			
			if ($scope.config.orderBy) {
				url = url + '&sort=' + $scope.config.orderBy.replace("+", "");
			}
			
			if (pageId !== undefined) {
				url = url + '&page=' + pageId;
			}
			
			return url;
		};
		
		// this method is also used withing after save/update events in order to retrieve current selecter filter data.
		$scope.reloadCrudList = function(pageId) {
			if (parseInt($scope.config.filter) == 0) {
				if ($scope.config.relationCall) {
					var url = $scope.generateUrlWithParams('relation-call', pageId);
					url = url + '&arrayIndex=' + $scope.config.relationCall.arrayIndex + '&id=' + $scope.config.relationCall.id + '&modelClass=' + $scope.config.relationCall.modelClass;
				} else {
					var url = $scope.generateUrlWithParams('list', pageId);
				}
				
				$http.get(url).then(function(response) {
					$scope.parseResponseQueryToListArray(response);
				});
			} else {
				var url = $scope.generateUrlWithParams('filter', pageId);
				var url = url + '&filterName=' + $scope.config.filter;
				$http.get(url).then(function(response) {
					$scope.parseResponseQueryToListArray(response);
				});
			}
		};

		$scope.service = false;

		/***** CONFIG AND INIT *****/

		$scope.data = {
			create : {},
			update : {},
			aw : {},
			list : {},
			updateId : 0
		};

		$scope.$watch('config', function(n, o) {
			$timeout(function() {
				$scope.initServiceAndConfig().then(function() {
					$scope.loadList();
				});
			})
		});
	}]);

// activeWindowController.js

	zaa.controller("ActiveWindowTagController", ['$scope', '$http', 'AdminToastService', function($scope, $http, AdminToastService) {

		$scope.crud = $scope.$parent; // {{ data.aw.itemId }}

		$scope.tags = [];

		$scope.relation = {};

		$scope.newTagName = null;

		$scope.loadTags = function() {
			$http.get($scope.crud.getActiveWindowCallbackUrl('LoadTags')).then(function(transport) {
				$scope.tags = transport.data;
			});
		};

		$scope.loadRelations = function() {
			$http.get($scope.crud.getActiveWindowCallbackUrl('LoadRelations')).then(function(transport) {
				$scope.relation = {};
				transport.data.forEach(function(value, key) {
					$scope.relation[value.tag_id] = 1;
				});
			});
		};

		$scope.saveTag = function() {
			var tagName = $scope.newTagName;

			if (tagName !== "") {
				$scope.crud.sendActiveWindowCallback('SaveTag', {'tagName': tagName}).then(function(response) {
					if (response.data) {
						$scope.tags.push({id: response.data, name: tagName});
						AdminToastService.success(tagName + ' wurde gespeichert.');
					} else {
						AdminToastService.error(tagName + ' ' + i18n['js_tag_exists']);
					}
					$scope.newTagName = null;
				});
			}
		};

		$scope.saveRelation = function(tag, value) {
			$scope.crud.sendActiveWindowCallback('SaveRelation', {'tagId': tag.id, 'value': value}).then(function(response) {

				$scope.relation[tag.id] = response.data;

				AdminToastService.success(i18n['js_tag_success']);
			});
		};

		$scope.$watch(function() { return $scope.data.aw.itemId }, function(n, o) {
			$scope.loadRelations();
		});

		$scope.loadTags();

	}]);

	/**
	 * ActiveWindow GalleryController
	 *
	 * Ability to upload images, removed images from index, add new images via selecting from
	 * filemanager.
	 *
	 * Changes content when parent crud controller changes value for active aw.itemId.
	 */
	zaa.controller("ActiveWindowGalleryController", ['$scope', '$http', '$filter', function($scope, $http, $filter) {

		$scope.crud = $scope.$parent;

		$scope.files = [];

		$scope.select = function(id) {
			var exists = $filter('filter')($scope.files, {'fileId' : id}, true);

			if (exists.length == 0) {
				$scope.crud.sendActiveWindowCallback('AddImageToIndex', {'fileId' : id }).then(function(response) {
					var data = response.data;
					$scope.files.push(data);
				});
			}
		};

		$scope.loadImages = function() {
			$http.get($scope.crud.getActiveWindowCallbackUrl('loadAllImages')).then(function(response) {
				$scope.files = response.data;
			})
		};

		$scope.changePosition = function(file, index, direction) {
			var index = parseInt(index);
			var oldRow = $scope.files[index];
			if (direction == 'up') {
                $scope.files[index] = $scope.files[index-1];
                $scope.files[index-1] = oldRow;
			} else if (direction == 'down') {
                $scope.files[index] = $scope.files[index+1];
                $scope.files[index+1] = oldRow;
			}
			var newRow = $scope.files[index];

			$scope.crud.sendActiveWindowCallback('ChangeSortIndex', {'new': newRow, 'old': oldRow});
		};

		$scope.moveUp = function(file, index) {
			$scope.changePosition(file, index, 'up');
		};

		$scope.moveDown = function(file, index) {
			$scope.changePosition(file, index, 'down');
		}

		$scope.remove = function(file, index) {
			$scope.crud.sendActiveWindowCallback('RemoveFromIndex', {'imageId' : file.originalImageId }).then(function(response) {
				$scope.files.splice(index, 1);
			});
		};

		$scope.$watch(function() { return $scope.data.aw.itemId }, function(n, o) {
			$scope.loadImages();
		});

	}]);

	zaa.controller("ActiveWindowGroupAuth", ['$scope', '$http', 'CacheReloadService', function($scope, $http, CacheReloadService) {

		$scope.crud = $scope.$parent; // {{ data.aw.itemId }}

		$scope.reload = function() {
			CacheReloadService.reload();
		};

		$scope.rights = [];

		$scope.auths = [];

		$scope.save = function(data) {
			$scope.crud.sendActiveWindowCallback('saveRights', {'data' : data }).then(function(response) {
				$scope.getRights();
				$scope.reload();
			});
		};

		$scope.clearModule = function(items) {
			angular.forEach(items, function(value) {
				$scope.rights[value.id] = {base: 0, create: 0, update: 0, delete: 0};
			});
		};

		$scope.toggleModule = function(items) {
			angular.forEach(items, function(value) {
				$scope.rights[value.id] = {base: 1, create: 1, update: 1, delete: 1};
			});
		};

		$scope.toggleGroup = function(id) {

			objectGroup = $scope.rights[id];

			if (objectGroup.base == 1) {
				objectGroup.create = 1;
				objectGroup.update = 1;
				objectGroup.delete = 1;
			} else if (objectGroup.base == 0) {
				objectGroup.create = 0;
				objectGroup.update = 0;
				objectGroup.delete = 0;
			}
		};

		$scope.toggleAll = function() {
			angular.forEach($scope.auths,function(items) {
				angular.forEach(items, function(value) {
					$scope.rights[value.id] = {base: 1, create: 1, update: 1, 'delete': 1 };
				});
			});
		};

		$scope.untoggleAll = function() {
			angular.forEach($scope.auths,function(items) {
				angular.forEach(items, function(value) {
					$scope.rights[value.id] = {base: 0, create: 0, update: 0, 'delete': 0 };
				});
			});
		};

		$scope.getRights = function() {
			$http.get($scope.crud.getActiveWindowCallbackUrl('getRights')).then(function(response) {
				$scope.rights = response.data.rights;
				$scope.auths = response.data.auths;
			});
		};

		$scope.$on('awloaded', function(e, d) {
			$scope.getRights();
		});

		$scope.$watch(function() { return $scope.data.aw.itemId }, function(n, o) {
			$scope.getRights();
		});
	}]);

// DefaultController.js.

	zaa.controller("DefaultController", ['$scope', '$http', '$state', '$stateParams', 'CrudTabService', function ($scope, $http, $state, $stateParams, CrudTabService) {

		$scope.moduleId = $state.params.moduleId;

		$scope.loadDashboard = function() {
			$scope.currentItem = null;
			return $state.go('default', { 'moduleId' : $scope.moduleId});
		}

		$scope.isOpenModulenav = false;

		$scope.items = [];

		$scope.itemRoutes = [];

		$scope.currentItem = null;

		$scope.dashboard = [];

		$scope.itemAdd = function (name, items) {

			$scope.items.push({name : name, items : items});

			for(var i in items) {
				var data = items[i];
				$scope.itemRoutes[data.route] = {
					alias : data.alias, icon : data.icon
				}
			}
		};

		$scope.getDashboard = function(nodeId) {
			$http.get('admin/api-admin-menu/dashboard', { params : { 'nodeId' : nodeId }} ).then(function(data) {
				$scope.dashboard = data.data;
			});
		};

		$scope.init = function() {
			$scope.get();
			$scope.getDashboard($scope.moduleId);
		};

		$scope.resolveCurrentItem = function() {
			if (!$scope.currentItem) {
				if ($state.current.name == 'default.route' || $state.current.name == 'default.route.detail') {
					var params = [$stateParams.moduleRouteId, $stateParams.controllerId, $stateParams.actionId];
					var route = params.join("/");
					if ($scope.itemRoutes.indexOf(route)) {
						$scope.currentItem = $scope.itemRoutes[route];
						$scope.currentItem.route = route;
					}
				}
			}
		};

		$scope.click = function(item) {
			$scope.isOpenModulenav = false;
			$scope.currentItem = item;

			var id = item.route;
			var res = id.split("/");
			CrudTabService.clear();

			$state.go('default.route', { moduleRouteId : res[0], controllerId : res[1], actionId : res[2]});
		};

		$scope.get = function () {
			$http.get('admin/api-admin-menu/items', { params : { 'nodeId' : $scope.moduleId }} ).then(function(response) {
				var data = response.data;
				for (var itm in data.groups) {
					var grp = data.groups[itm];
					$scope.itemAdd(grp.name, grp.items);
				}
				$scope.resolveCurrentItem();
			})
		};

		$scope.$on('topMenuClick', function(e) {
			$scope.currentItem = null;
		});

		$scope.init();
	}]);

	zaa.controller("DashboardController", ['$scope', function ($scope) {
		$scope.logItemOpen = false;
	}]);

	zaa.filter('lockFilter', function() {
		return function(data, table, pk) {
			var has = false;
			angular.forEach(data, function(value) {
				if (value.lock_table == table && value.lock_pk == pk) {
					has = value;
				}
			});

			return has;
        };
	});

	zaa.controller("LayoutMenuController", [
		'$scope', '$document', '$http', '$state', '$location', '$timeout', '$window', '$filter', 'HtmlStorage', 'CacheReloadService', 'AdminDebugBar', 'LuyaLoading', 'AdminToastService', 'AdminClassService',
		function ($scope, $document, $http, $state, $location, $timeout, $window, $filter, HtmlStorage, CacheReloadService, AdminDebugBar, LuyaLoading, AdminToastService, AdminClassService) {

		$scope.AdminClassService = AdminClassService;

		$scope.AdminDebugBar = AdminDebugBar;

		$scope.LuyaLoading = LuyaLoading;

		$scope.toastQueue = AdminToastService.queue;

		$scope.reload = function() {
			CacheReloadService.reload();
		};

		/* Main nav sidebar toggler */

		$scope.isHover = HtmlStorage.getValue('sidebarToggleState', false);

		$scope.toggleMainNavSize = function() {
			$scope.isHover = !$scope.isHover;
			HtmlStorage.setValue('sidebarToggleState', $scope.isHover);
		};

		/* PROFIL SETTINS */

		$scope.profile = {};
		$scope.settings = {};
		$scope.packages = [];

		$scope.getProfileAndSettings = function() {
			$http.get('admin/api-admin-user/session').then(function(success) {
				$scope.profile = success.data.user;
				$scope.settings = success.data.settings;
				$scope.packages = success.data.packages;
			});
		};

		/* Browser infos */

		$scope.browser = null;

		$scope.detectBrowser = function() {
            $scope.browser = [
                bowser.name.replace(' ', '-').toLowerCase() + '-' + bowser.version,
                (bowser.mac ? 'mac-os-' + (bowser.osversion ? bowser.osversion : '') : 'windows-' + (bowser.osversion ? bowser.osversion : ''))
            ].join(' ');
		};

		$scope.detectBrowser();

		$scope.getProfileAndSettings();

		$scope.debugDetail = null;

		$scope.debugDetailKey = null;

		$scope.loadDebugDetail = function(debugDetail, key) {
			$scope.debugDetail = debugDetail;
			$scope.debugDetailKey = key;
		};

		$scope.closeDebugDetail = function() {
			$scope.debugDetail = null;
			$scope.debugDetailKey = null;
		};

		$scope.notify = null;

		$scope.forceReload = 0;

		$scope.showOnlineContainer = false;

		$scope.searchDetailClick = function(itemConfig, itemData) {
			if (itemConfig.type == 'custom') {
				$scope.click(itemConfig.menuItem).then(function() {
					if (itemConfig.stateProvider) {
						var params = {};
						angular.forEach(itemConfig.stateProvider.params, function(value, key) {
							params[key] = itemData[value];
						})

						$state.go(itemConfig.stateProvider.state, params).then(function() {
							$scope.closeSearchInput();
						})
					} else {
						$scope.closeSearchInput();
					}
				});

			} else {
				$scope.click(itemConfig.menuItem.module).then(function() {
					var res = itemConfig.menuItem.route.split("/");
					$state.go('default.route', { moduleRouteId : res[0], controllerId : res[1], actionId : res[2]}).then(function() {
						if (itemConfig.stateProvider) {
							var params = {};
							angular.forEach(itemConfig.stateProvider.params, function(value, key) {
								params[key] = itemData[value];
							})
							$state.go(itemConfig.stateProvider.state, params).then(function() {
								$scope.closeSearchInput();
							})
						} else {
							$scope.closeSearchInput();
						}
					})
				});
			}
		};

		$scope.visibleAdminReloadDialog = false;

		$scope.lastKeyStroke = Date.now();

		$document.bind('keyup', function (e) {
			$scope.lastKeyStroke = Date.now();
		});

		(function tick(){
			$http.post('admin/api-admin-timestamp/index', {lastKeyStroke: $scope.lastKeyStroke}, {ignoreLoadingBar: true}).then(function(response) {
				$scope.forceReload = response.data.forceReload;
				if ($scope.forceReload && !$scope.visibleAdminReloadDialog) {
					$scope.visibleAdminReloadDialog = true;
					AdminToastService.confirm(i18n['js_admin_reload'], i18n['layout_btn_reload'], function() {
						$scope.reload();
						$scope.visibleAdminReloadDialog = false;
					});
				}

				$scope.locked = response.data.locked;
				$scope.notify = response.data.useronline;
				$scope.idleStrokeDashoffset = response.data.idleStrokeDashoffset;
				$scope.idleTimeRelative = response.data.idleTimeRelative;
				$timeout(tick, 20000);
			})
		})();

		$scope.isLocked = function(table, pk) {
			return $filter('lockFilter')($scope.locked, table, pk);
		};

		$scope.getLockedName = function(table, pk) {
			var response = $scope.isLocked(table, pk);

			return response.firstname + ' ' + response.lastname;
		};

		$scope.searchQuery = null;

	    $scope.searchInputOpen = false;

	    $scope.escapeSearchInput = function() {
	        if ($scope.searchInputOpen) {
	            $scope.closeSearchInput();
	        }
	    };

	    $scope.toggleSearchInput = function() {
	    	$scope.searchInputOpen = !$scope.searchInputOpen;
	    };

	    $scope.openSearchInput = function() {
	        $scope.searchInputOpen = true;
	    };

	    $scope.closeSearchInput = function() {
	        $scope.searchInputOpen = false;
	    };

		$scope.searchResponse = null;

		$scope.searchPromise = null;

		$scope.$watch(function() { return $scope.searchQuery}, function(n, o) {
			if (n !== o) {
				if (n.length > 2) {
					$timeout.cancel($scope.searchPromise);
					$scope.searchPromise = $timeout(function() {
						$http.get('admin/api-admin-search', { params : { query : n}}).then(function(response) {
							$scope.searchResponse = response.data;
						});
					}, 1000)
				} else {
	                $scope.searchResponse = null;
				}
			}
		});

		$scope.items = [];

		$scope.currentItem = {};

		$scope.isOpen = false;

		$scope.click = function(menuItem) {
			$scope.isOpen = false;
			$scope.$broadcast('topMenuClick', { menuItem : menuItem });
			if (menuItem.template) {
				return $state.go('custom', { 'templateId' : menuItem.template });
			} else {
				return $state.go('default', { 'moduleId' : menuItem.id});
			}
		};

		$scope.isActive = function(item) {
			if (item.template) {
				if ($state.params.templateId == item.template) {
					$scope.currentItem = item;
					return true;
				}
			} else {
				if ($state.params.moduleId == item.id) {
					$scope.currentItem = item;
					return true;
				}
			}
		};

		$scope.get = function () {
			$http.get('admin/api-admin-menu').then(function(response) {
				$scope.items = response.data;
			});
		};

		$scope.get();
	}]);

	zaa.controller("AccountController", ['$scope', '$http', '$window', 'AdminToastService', function($scope, $http, $window, AdminToastService) {

		$scope.pass = {};

		$scope.changePassword = function() {
			$http.post('admin/api-admin-user/change-password', $scope.pass).then(function(response) {
				AdminToastService.success(i18n['aws_changepassword_succes']);
				$scope.pass = {};
			}, function(error) {
				AdminToastService.errorArray(error.data);
				$scope.pass = {};
			});
		};

		$scope.changeSettings = function(settings) {
			$http.post('admin/api-admin-user/change-settings', settings).then(function(response) {
				$window.location.reload();
			});
		};

		$scope.profile = {};
		$scope.settings = {};
		$scope.activities = {};

		$scope.email = {};

		$scope.getProfile = function() {
			$http.get('admin/api-admin-user/session').then(function(success) {
				$scope.profile = success.data.user;
				$scope.settings = success.data.settings;
				$scope.activities = success.data.activities;
			});
		};

		$scope.changePersonData = function(data) {
			$http.put('admin/api-admin-user/session-update', data).then(function(success) {
				AdminToastService.success(i18n['js_account_update_profile_success']);
				$scope.getProfile();
			}, function(error) {
				AdminToastService.errorArray(error.data);
			});
		};

		$scope.changeEmail = function() {
			$http.put('admin/api-admin-user/change-email', {token: $scope.email.token}).then(function(success) {
				AdminToastService.success(i18n['js_account_update_profile_success']);
				$scope.getProfile();
			}, function(error) {
				AdminToastService.errorArray(error.data);
			});
		};

		$scope.getProfile();
	}]);