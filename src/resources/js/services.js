// service resolver
adminServiceResolver = ['ServiceFoldersData', 'ServiceFiltersData', 'ServiceLanguagesData', 'ServicePropertiesData', 'AdminLangService', 'ServiceFoldersDirectoryId', function(ServiceFoldersData, ServiceFiltersData, ServiceLanguagesData, ServicePropertiesData, AdminLangService, ServiceFoldersDirectoryId) {
	ServiceFiltersData.load();
	ServiceFoldersData.load();
	ServiceLanguagesData.load();
	ServicePropertiesData.load();
	AdminLangService.load();
	ServiceFoldersDirectoryId.load();
}];

/**
 * A promise which is resolved when all queue job ids are don:
 * 
 * ServiceQueueWaiting.waitFor(response.data.queueIds).then({
 *  ....
 * })
 */
zaa.factory("ServiceQueueWaiting", ['$http', '$q', '$timeout', function($http, $q, $timeout) {
	var service = {
		ids: [],
	};

	service.waitFor = function(ids) {
		return $q(function(resolve, reject) {
			if (ids.length == 0) {
				resolve()
			} else {

				ids.forEach(jobId => service.ids.push(jobId))

				const promises = []
				ids.forEach(jobId => {
					promises.push(service.waitForJobId(jobId))
				})

				$q.all(promises).then(resolver => {
					resolve()
				})
			}
		});
	};

	service.waitForJobId = function(jobId) {
		return $q(function(resolve, reject) {
			$http.get('admin/api-admin-common/queue-job?jobId=' + jobId, {ignoreLoadingBar: true}).then(response => {
				if (response.data.is_done) {
					const index = service.ids.indexOf(jobId);
					if (index > -1) {
						service.ids.splice(index, 1);
						resolve()
					}
				} else {
					setTimeout(() => {
						service.waitForJobId(jobId).then(xr => {
							resolve()
						})
					}, 500);
				}
			})
		})
	};

	return service
}])

/**
 * Global LUYA Angular Services:
 * 
 * controller resolve: https://github.com/johnpapa/angular-styleguide#style-y080
 * 
 * Service Inheritance:
 * 
 * 1. Service must be prefix with Service
 * 2. Service must contain a forceReload state
 * 3. Service must broadcast an event 'service:FoldersData'
 * 4. Controller integration must look like
 * 
 * ```js
 * $scope.foldersData = ServiceFoldersData.data;
 *				
 * $scope.$on('service:FoldersData', function(event, data) {
 *      $scope.foldersData = data;
 * });
 *				
 * $scope.foldersDataReload = function() {
 *     return ServiceFoldersData.load(true);
 * }
 * ```
 */
	
/**
 * A service to retrieve and hold all admin tags.
 * 
 * The main purpose is to display the tags an several parts of the admin area.
 * 
 * The service also needs an option to refresh the data as its it needs a refresh when adding new tags
 * + Tag Active Window Form.
 * + Tag CRUD Add/Edit Form.
 * @since 1.3.0
 */
zaa.factory("ServiceAdminTags", ['$http', '$q', '$rootScope', function($http, $q, $rootScope) {
	var service = {};

	service.data = null;
	
	service.load = function(forceReload) {
		return $q(function(resolve, reject) {
			if (service.data !== null && forceReload !== true) {
				resolve(service.data);
			} else {
				$http.get("admin/api-admin-common/tags").then(function(response) {
					service.data = response.data;
					$rootScope.$broadcast('service:AdminTags', service.data);
					resolve(service.data);
				});
			}
		});
	};

	return service;
}]);

/*

$scope.foldersData = ServiceFoldersData.data;
					
$scope.$on('service:FoldersData', function(event, data) {
	$scope.foldersData = data;
});

$scope.foldersDataReload = function() {
	return ServiceFoldersData.load(true);
}

*/
zaa.factory("ServiceFoldersData", ['$http', '$q', '$rootScope', function($http, $q, $rootScope) {
	
	var service = [];
	
	service.data = null;
	
	service.load = function(forceReload) {
		return $q(function(resolve, reject) {
			if (service.data !== null && forceReload !== true) {
				resolve(service.data);
			} else {
				$http.get("admin/api-admin-storage/data-folders").then(function(response) {
					service.data = response.data;
					$rootScope.$broadcast('service:FoldersData', service.data);
					resolve(service.data);
				});
			}
		});
	};
	
	return service;
}]);

/*

$scope.folderId = ServiceFoldersDirectoryId.folderId;
					
$scope.$on('service:FoldersDirectoryId', function(event, folderId) {
	$scope.folderId = folderId;
});

$scope.foldersDirectoryIdReload = function() {
	return ServiceFoldersDirectoryId.load(true);
}

*/
zaa.factory("ServiceFoldersDirectoryId", ['$http', '$q', '$rootScope', function($http, $q, $rootScope) {
	
	var service = [];
	
	service.folderId = false;
	
	service.load = function(forceReload) {
		return $q(function(resolve, reject) {
			if (service.folderId !== false && forceReload !== true) {
				resolve(service.folderId);
			} else {
				$http.get("admin/api-admin-common/get-filemanager-folder-state").then(function(response) {
					service.folderId = response.data;
					$rootScope.$broadcast('service:FoldersDirectoryId', service.folderId);
					resolve(service.folderId);
				});
			}
		});
	};
	
	return service;
}]);

/*

$scope.imagesData = ServiceImagesData.data;
				
$scope.$on('service:ImagesData', function(event, data) {
	$scope.imagesData = data;
});

$scope.imagesDataReload = function() {
	return ServiceImagesData.load(true);
}

*/
zaa.factory("ServiceImagesData", ['$http', '$q', '$rootScope', '$log', function($http, $q, $rootScope, $log) {
	var service = [];
	
	service.data = {};
	
	/**
	 * Get a given file from the storage system by its id.
	 * 
	 * ```js
	 * ServiceImagesData.getImage(1).then(function(response) {
	 *     console.log(response);
	 * });
	 */
	service.getImage = function(id, forceAsyncRequest) {
		return $q(function(resolve, reject) {
			
			if (id == 0) {
				return reject(id);
			}
			
			if (service.data.hasOwnProperty(id) && forceAsyncRequest !== true) {
				return resolve(service.data[id]);
			}
			
			$http.get('admin/api-admin-storage/image-info?id='+id).then(function(response) {
				var data = response.data;
    			service.data[data.id] = data;
    			return resolve(data);
    		});
		});
	};

	service.loadImages = function(imagesArray) {
		return $q(function(resolve, reject) {
			if (imagesArray.length == 0) {
				return resolve();
			}
			$http.post('admin/api-admin-storage/images-info?expand=source,tinyCropImage', {ids: imagesArray}).then(function(response) {
				angular.forEach(response.data, function(value) {
					service.data[value.id] = value;
				});
				return resolve();
			});
		});
	};
	
	return service;
}]);

/*

$scope.filesData = ServiceFilesData.data;
				
$scope.$on('service:FilesData', function(event, data) {
	$scope.filesData = data;
});

$scope.filesDataReload = function() {
	return ServiceFilesData.load(true);
}
				
*/
zaa.factory("ServiceFilesData", ['$http', '$q', '$rootScope', '$log', function($http, $q, $rootScope, $log) {
	var service = [];
	
	service.data = {};
	
	service._promises = {};

	/**
	 * Get a given file from the storage system by its id.
	 * 
	 * ```js
	 * ServiceFilesData.getFile(1).then(function(response) {
	 *     console.log(response);
	 * });
	 */
	service.getFile = function(id, forceAsyncRequest) {
		// this ensures to not have two promises at the same time
		if (service._promises.hasOwnProperty(id)) {
			return service._promises[id];
		}

		var promise = service.newPromise(id, forceAsyncRequest);
		service._promises[id] = promise;
		return promise;
	};

	/**
	 * Generate a promise to resolve
	 */
	service.newPromise = function(id, forceAsyncRequest) {
		return $q(function(resolve, reject) {
			
			if (id == 0) {
				return reject(id);
			}
			
			if (service.data.hasOwnProperty(id) && forceAsyncRequest !== true) {
				return resolve(service.data[id]);
			}

			$http.get('admin/api-admin-storage/file-info?id='+id).then(function(response) {
				var data = response.data;
				service.data[data.id] = data;
				delete service._promises[id];
    			return resolve(data);
    		});
		});
	}
	
	return service;
}]);

/*

$scope.filtersData = ServiceFiltersData.data;
				
$scope.$on('service:FiltersData', function(event, data) {
	$scope.filtersData = data;
});

$scope.filtersDataReload = function() {
	return ServiceFiltersData.load(true);
}
				
*/
zaa.factory("ServiceFiltersData", ['$http', '$q', '$rootScope', function($http, $q, $rootScope) {
	var service = [];
	
	service.data = null;
	
	service.load = function(forceReload) {
		return $q(function(resolve, reject) {
			if (service.data !== null && forceReload !== true) {
				resolve(service.data);
			} else {
				$http.get("admin/api-admin-storage/data-filters").then(function(response) {
					service.data = response.data;
					$rootScope.$broadcast('service:FiltersData', service.data);
					resolve(service.data);
				});
			}
		});
	};
	
	return service;
}]);

/*

$scope.languagesData = ServiceLanguagesData.data;
				
$scope.$on('service:LanguagesData', function(event, data) {
	$scope.languagesData = data;
});

$scope.languagesDataReload = function() {
	return ServiceLanguagesData.load(true);
}
				
*/
zaa.factory("ServiceLanguagesData", ['$http', '$q', '$rootScope', function($http, $q, $rootScope) {
	var service = [];
	
	service.data = [];
	
	service.load = function(forceReload) {
		return $q(function(resolve, reject) {
			if (service.data.length > 0 && forceReload !== true) {
				resolve(service.data);
			} else {
				$http.get("admin/api-admin-common/data-languages").then(function(response) {
					service.data = response.data;
					$rootScope.$broadcast('service:LanguagesData', service.data);
					resolve(service.data);
				})
			}
		});
	};
	
	return service;
}]);

/*

$scope.propertiesData = ServicePropertiesData.data;
				
$scope.$on('service:PropertiesData', function(event, data) {
	$scope.propertiesData = data;
});

$scope.propertiesDataReload = function() {
	return ServicePropertiesData.load(true);
}
				
*/
zaa.factory("ServicePropertiesData", ['$http', '$q', '$rootScope', function($http, $q, $rootScope) {
	var service = [];
	
	service.data = null;
	
	service.load = function(forceReload) {
		return $q(function(resolve, reject) {
			if (service.data !== null && forceReload !== true) {
				resolve(service.data);
			} else {
				$http.get("admin/api-admin-common/data-properties").then(function(response) {
					service.data = response.data;
					$rootScope.$broadcast('service:PropertiesData', service.data);
					resolve(service.data);
				})
			}
		});
	};
	
	return service;
}]);

/**
 * Crud Tab Service
 * 
 * This service is mainly used by the NgRest CRUD system in order to inject or remove new/existing tabs.
 * 
 * This service is used by the NgRest relations service.
 */
zaa.factory("CrudTabService", function() {
	
	var service = [];
	
	service.tabs = [];
	
	service.remove = function(index, $scope) {
		service.tabs.splice(index, 1);
		
		if (service.tabs.length > 0) {
			var lastTab = service.tabs.slice(-1)[0];
			lastTab.active = true;
		} else {
			$scope.switchTo(0);
		}
	};
	
	service.addTab = function(id, api, arrayIndex, name, modelClass) {
		var tab = {id: id, api: api, arrayIndex: arrayIndex, active: true, name: name, modelClass:modelClass};
		
		angular.forEach(service.tabs, function(item) {
			item.active = false;
		});
		
		service.tabs.push(tab);
		
	};
	
	service.clear = function() {
		service.tabs = [];
	};
	
	return service;
});

/**
 * Admin Language Service
 * 
 * This service provides you information about all available languages of the admin, and whether a current language
 * is selected and display. The selection is mainly used by forms in order to determine whether a language field should
 * be displayed or not.
 * 
 * 
 */
zaa.factory("AdminLangService", ['ServiceLanguagesData', '$rootScope', function(ServiceLanguagesData, $rootScope) {
	
	var service = [];
	
	service.data = [];
	
	service.selection = [];
	
	service.toggleSelection = function(lang) {
		var exists = service.selection.indexOf(lang.short_code);
		
		if (exists == -1) {
			service.selection.push(lang.short_code);
			$rootScope.$broadcast('service:LoadLanguage', lang);
		} else {
			/* #531: unable to deselect language, as at least 1 langauge must be activated. */
			if (service.selection.length > 1) {
				service.selection.splice(exists, 1);
			}
		}
	};
	
	service.isInSelection = function(langShortCode) {
		var exists = service.selection.indexOf(langShortCode);
		if (exists == -1) {
			return false;
		}
		return true;
	};
	
	service.resetDefault = function() {
		service.selection = [];
		angular.forEach(ServiceLanguagesData.data, function(value, key) {
			if (value.is_default == 1) {
				if (!service.isInSelection(value.short_code)) {
					service.toggleSelection(value);
				}
			}
		})
	};
	
	service.load = function() {
		ServiceLanguagesData.load().then(function(data) {
			service.data = data;
			
			angular.forEach(data, function(value) {
				if (value.is_default == 1) {
					if (!service.isInSelection(value.short_code)) {
						service.toggleSelection(value);
					}
				}
			})
			
		});
	};
	
	return service;
}]);

/*
 * Admin Debug Bar provides an array with debug information from the last request in order to find bugs without the developer tools of the browser 
 */
zaa.factory("AdminDebugBar", function() {
	
	var service = [];
	
	service.data = [];
	
	service.clear = function() {
		service.data = [];
	};
	
	service.pushRequest = function(request) {
		return service.data.push({'url': request.url, 'requestData': request.data, 'responseData': null, 'responseStatus' : null, start:new Date(), end:null, parseTime: null});
	};
	
	service.pushResponse = function(response) {
		var responseCopy = response;
		
		var serviceData = service.data[responseCopy.config.debugId];
		
		if (serviceData) {
			serviceData.responseData = responseCopy.data;
			serviceData.responseStatus = responseCopy.status;
			serviceData.end = new Date();
			serviceData.parseTime = new Date() - serviceData.start;
		}
		
		return response;
	};
	
	return service;
});

/**
 * Notifcation Toasts
 * 
 * This services allows you to send toast message into the admin UI. This is commonly used for error and success messages.
 * 
 * + success: `AdminToastService.success('Hello success!');`
 * + error: `AdminToastService.error('This is an error');`
 * + info: `AdminToastService.info('Just an info message');`
 * + warning: `AdminToastService.warning('Warning message here!');`
 * 
 * But you can also make confirm dialogs where the user have to say YES or NO. Not will just close the confirm prompt,
 * but YES will run the defined callback:
 * 
 * Example with simple console log after yes has been close:
 * 
 * ```js
 * AdminToastService.confirm('Are you sure?', 'Dialog Title', function() {
 *	  console.log('The user has clicked yes!');
 *    this.close();
 * });
 * ```
 * 
 * Instead of `this.close` you can also invoke the $toast service:
 * 
 * ```js
 * AdminToastService.confirm('Are you sure?', 'Dialog Title', ['$toast', function($toast) {
 *	  console.log('The user has clicked yes!');
 *    console.log('Toast:', $toast);
 *    $toast.close();
 * });
 * ```
 * 
 * You can also use promises:
 * 
 * ```js
 * AdminToastService.confirm('Hello i am a callback and wait for your...', 'Dialog Title', ['$q', '$http', function($q, $http) {
 * 	  // do some ajax call
 * 	  $http.get('admin/api-go-here').then(function() {
 * 		  promise.resolve();
 * 	  }).error(function() {
 * 		  promise.reject();
 * 	  });
 * }]);
 * ```
 */
zaa.factory("AdminToastService", ['$q', '$timeout', '$injector', function($q, $timeout, $injector) {
	var service = [];
	
	service.notify = function(message, timeout, type) {
		
		if (timeout == undefined) {
			timeout = 6000;
		}
		
		var uuid = guid();
		
		service.queue[uuid] = {message: message, timeout: timeout, uuid: uuid, type: type, close: function() {
			delete service.queue[this.uuid];
		}};
		
		$timeout(function() {
			delete service.queue[uuid];
		}, timeout);
	};
	
	service.success = function(message, timeout) {
		service.notify(message, timeout, 'success');
	};

    service.info = function(message, timeout) {
        service.notify(message, timeout, 'info');
    };

    service.warning = function(message, timeout) {
        service.notify(message, timeout, 'warning');
    };
	
	service.error = function(message, timeout) {
		service.notify(message, timeout, 'error');
	};
	
	service.errorArray = function(array, timeout) {
		angular.forEach(array, function(value, key) {
			service.error(value.message, timeout);
		});
	};
	
	service.confirm = function(message, title, callback) {
		var uuid = guid();
		service.queue[uuid] = {message: message, title:title, click: function() {
			var queue = this;
			var response = $injector.invoke(this.callback, this, { $toast : this });
			if (response !== undefined) {
				response.then(function(r) {
					queue.close();
				}, function(r) {
					queue.close();
				}, function(r) {
					/* call or load at later time */
				});
			}
		}, uuid: uuid, callback: callback, type: 'confirm', close: function() {
			delete service.queue[this.uuid];
		}}
	};
	
	service.queue = {};
	
	return service;
}]);

/**
 * Saving data in Html Storage
 * 
 * This service allows you to store and retrieve data from the html5 storage system:
 * 
 * Retrieve a value:
 * 
 * ```js
 * HtmlStorage.getValue('sidebarToggleState', false); 
 * ```
 * 
 * Where false is the default value if no the provided key could not find any data
 * in the html storage system.
 * 
 * Set a given value with a key:
 * 
 * ```js
 * HtmlStorage.setValue('sidebarToggleState', $scope.isHover);
 * ```
 */
zaa.factory('HtmlStorage', function() {
	var service = {
		
		data: {},
		
		isLoaded : false,
		
		loadData : function() {
			if (!service.isLoaded) {
				if (localStorage.getItem("HtmlStorage")) {
					var data = angular.fromJson(localStorage.getItem('HtmlStorage'));
					
					service.data = data;
				}
			}
		},
		
		saveData : function() {
			localStorage.removeItem('HtmlStorage');
			localStorage.setItem('HtmlStorage', angular.toJson(service.data));
		},
		
		getValue : function(key, defaultValue) {
			service.loadData();
			
			if (service.data.hasOwnProperty(key)) {
				return service.data[key];
			}
			
			return defaultValue;
		},
		
		setValue : function(key, value) {
			service.loadData();
			
			service.data[key] = value;
			
			service.saveData();
		}
	};
	
	return service;
});
