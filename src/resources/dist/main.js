angular.module('dnd', []).factory('dndFactory', function () {
  return {
    /**
     * variables to write
     */
    data: {
      content: null,
      pos: null,
      element: null
    },

    /**
     * Element Getter
     */
    getElement: function getElement() {
      return this.data.element;
    },

    /**
     * Elementer Setter
     */
    setElement: function setElement(e) {
      this.data.element = e;
    },

    /**
     * Content Setter
     */
    setContent: function setContent(value) {
      this.data.content = value;
    },

    /**
     * Content Getter
     */
    getContent: function getContent() {
      return this.data.content;
    },

    /**
     * Pos Setter
     */
    setPos: function setPos(pos) {
      this.data.pos = pos;
    },

    /**
     * Pos Getter
     */
    getPos: function getPos() {
      return this.data.pos;
    }
  };
})
/**
 * Usage:
 * 
 * ```js
 * dnd dnd-model="data" dnd-isvalid="isValid(hover,dragged)" dnd-drag-disabled dnd-diable-drag-middle dnd-drop-disabled dnd-ondrop="dropItem(dragged,dropped,position,element)" dnd-css="{onDrag: 'drag-start', onHover: 'red', onHoverTop: 'red-top', onHoverMiddle: 'red-middle', onHoverBottom: 'red-bottom'}"
 * ```
 * 
 * + dnd-model: This is the model which will be used as "dropped", when drag is disabled this model is not needed
 * + dnd-disable-drag-middle
 * + dnd-drag-disabled
 * + dnd-is-valid
 * 
 * Parts of the scripts are inspired by: https://github.com/marceljuenemann/angular-drag-and-drop-lists
 */
.directive('dnd', ['dndFactory', 'AdminClassService', function (dndFactory, AdminClassService) {
  return {
    restrict: 'A',
    transclude: false,
    replace: false,
    template: false,
    templateURL: false,
    scope: {
      dndModel: '=',
      dndCss: '=',
      dndOndrop: '&',
      dndIsvalid: '&'
    },
    link: function link(scope, element, attrs) {
      // In standard-compliant browsers we use a custom mime type and also encode the dnd-type in it.
      // However, IE and Edge only support a limited number of mime types. The workarounds are described
      // in https://github.com/marceljuenemann/angular-drag-and-drop-lists/wiki/Data-Transfer-Design
      var MIME_TYPE = 'application/x-dnd'; // EDGE MIME TYPE

      var EDGE_MIME_TYPE = 'application/json'; // IE MIME TYPE

      var MSIE_MIME_TYPE = 'Text'; // if current droping is valid, defaults to true

      var isValid = true; // whether middle dropping is disabled or not

      var disableMiddleDrop = attrs.hasOwnProperty('dndDisableDragMiddle');
      /* DRAGABLE */

      /**
       * Enable dragging if not disabled.
       */

      if (!attrs.hasOwnProperty('dndDragDisabled')) {
        element.attr("draggable", "true");
      }
      /**
       * Add a class to the current element
       */


      scope.addClass = function (className) {
        element.addClass(className);
      };
      /**
       * Remove a class from the current element, including timeout delay.
       */


      scope.removeClass = function (className, delay) {
        element.removeClass(className);
      };
      /**
       * DRAG START
       */


      element.on('dragstart', function (e) {
        e = e.originalEvent || e;
        e.stopPropagation(); // Check whether the element is draggable, since dragstart might be triggered on a child.

        if (element.attr('draggable') == 'false') {
          return true;
        }

        isValid = true;
        dndFactory.setContent(scope.dndModel);
        dndFactory.setElement(element[0]);
        scope.addClass(scope.dndCss.onDrag);
        var mimeType = 'text';
        var data = "1";

        try {
          e.dataTransfer.setData(mimeType, data);
        } catch (e) {
          try {
            e.dataTransfer.setData(EDGE_MIME_TYPE, data);
          } catch (e) {
            e.dataTransfer.setData(MSIE_MIME_TYPE, data);
          }
        }
      });
      /**
       * DRAG END
       */

      element.on('dragend', function (e) {
        e = e.originalEvent || e;
        scope.removeClass(scope.dndCss.onDrag);
        e.stopPropagation();
      });
      /* DROPABLE */

      /**
       * DRAG OVER ELEMENT
       */

      element.on('dragover', function (e) {
        e = e.originalEvent || e;

        try {
          e.dataTransfer.dropEffect = 'move';
        } catch (e) {// catch ie exceptions
        }

        e.preventDefault();
        e.stopPropagation();

        if (!scope.dndIsvalid({
          hover: scope.dndModel,
          dragged: dndFactory.getContent()
        })) {
          isValid = false;
          return false;
        }

        var re = element[0].getBoundingClientRect();
        var height = re.height;
        var mouseHeight = e.clientY - re.top;
        var percentage = 100 / height * mouseHeight;

        if (disableMiddleDrop) {
          if (percentage <= 50) {
            scope.addClass(scope.dndCss.onHoverTop);
            scope.removeClass(scope.dndCss.onHoverMiddle);
            scope.removeClass(scope.dndCss.onHoverBottom);
            dndFactory.setPos('top');
          } else {
            scope.removeClass(scope.dndCss.onHoverTop);
            scope.removeClass(scope.dndCss.onHoverMiddle);
            scope.addClass(scope.dndCss.onHoverBottom);
            dndFactory.setPos('bottom');
          }
        } else {
          if (percentage <= 25) {
            scope.addClass(scope.dndCss.onHoverTop);
            scope.removeClass(scope.dndCss.onHoverMiddle);
            scope.removeClass(scope.dndCss.onHoverBottom);
            dndFactory.setPos('top');
          } else if (percentage >= 65) {
            scope.removeClass(scope.dndCss.onHoverTop);
            scope.removeClass(scope.dndCss.onHoverMiddle);
            scope.addClass(scope.dndCss.onHoverBottom);
            dndFactory.setPos('bottom');
          } else {
            scope.removeClass(scope.dndCss.onHoverTop);
            scope.addClass(scope.dndCss.onHoverMiddle);
            scope.removeClass(scope.dndCss.onHoverBottom);
            dndFactory.setPos('middle');
          }
        }

        scope.addClass(scope.dndCss.onHover);
        return false;
      });
      /**
       * DRAG ENTER element
       */

      element.on('dragenter', function (e) {
        e = e.originalEvent || e;
        scope.addClass(scope.dndCss.onHover);
        e.preventDefault();
      });
      /**
       * DRAG LEAVE
       */

      element.on('dragleave', function (e) {
        scope.removeClass(scope.dndCss.onHover, true);
        scope.removeClass(scope.dndCss.onHoverTop, true);
        scope.removeClass(scope.dndCss.onHoverMiddle, true);
        scope.removeClass(scope.dndCss.onHoverBottom, true);
      });
      /**
       * DROP (if enabled)
       */

      if (!attrs.hasOwnProperty('dndDropDisabled')) {
        element.on('drop', function (e) {
          e = e.originalEvent || e; // The default behavior in Firefox is to interpret the dropped element as URL and
          // forward to it. We want to prevent that even if our drop is aborted.

          e.preventDefault();
          e.stopPropagation();
          scope.removeClass(scope.dndCss.onHover, true);
          scope.removeClass(scope.dndCss.onHoverTop, true);
          scope.removeClass(scope.dndCss.onHoverMiddle, true);
          scope.removeClass(scope.dndCss.onHoverBottom, true);

          if (isValid) {
            scope.$apply(function () {
              scope.dndOndrop({
                dragged: dndFactory.getContent(),
                dropped: scope.dndModel,
                position: dndFactory.getPos(),
                element: dndFactory.getElement()
              });
            });
            return true;
          }

          return false;
        });
      }
    }
  };
}]);/**
 * guid creator
 * @returns {String}
 */
function guid() {
  function s4() {
    return Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1);
  }

  return s4() + s4() + '-' + s4() + '-' + s4() + '-' + s4() + '-' + s4() + s4() + s4();
}
/**
 * i18n localisation with params.
 *
 * ```js
 * i18nParam('js_i18n_translation_name', {variable: value});
 * ```
 *
 * Translations File:
 *
 * ```php
 * 'js_i18n_translation_name' => 'Hello %variable%',
 * ```
 * @param varName
 * @param params
 * @returns
 */


function i18nParam(varName, params) {
  var varValue = i18n[varName];
  angular.forEach(params, function (value, key) {
    varValue = varValue.replace("%" + key + "%", value);
  });
  return varValue;
}
/**
 * Type cast numeric values to integer
 *
 * @param value
 * @returns
 */


function typeCastValue(value) {
  return angular.isNumber(value) ? parseInt(value) : value;
}
/* DEFINE LUYA ADMIN ANGULAR VAR */


var zaa = angular.module("zaa", ["ui.router", "dnd", "angular-loading-bar", "ngFileUpload", "ngWig", "flow", "angular.filter", "720kb.datepicker", "directive.ngColorwheel", "uiCropper"]);
/* CONFIG */

zaa.config(['$httpProvider', '$stateProvider', '$controllerProvider', '$urlMatcherFactoryProvider', function ($httpProvider, $stateProvider, $controllerProvider, $urlMatcherFactoryProvider) {
  $httpProvider.interceptors.push("authInterceptor"); // used to bootstrap the angularjs controllers in the view 

  zaa.bootstrap = $controllerProvider;
  $urlMatcherFactoryProvider.strictMode(false);
  /**
   * resolvers: https://github.com/angular-ui/ui-router/wiki#resolve
   */

  $stateProvider.state("default", {
    url: "/default/:moduleId",
    templateUrl: function templateUrl($stateParams) {
      return "admin/template/default";
    }
  }).state("default.route", {
    url: "/:moduleRouteId/:controllerId/:actionId",
    templateUrl: function templateUrl($stateParams) {
      return $stateParams.moduleRouteId + "/" + $stateParams.controllerId + "/" + $stateParams.actionId;
    },
    parent: 'default',
    resolve: {
      adminServiceResolver: adminServiceResolver
    }
  }).state("custom", {
    url: "/template/:templateId",
    templateUrl: function templateUrl($stateParams) {
      return $stateParams.templateId;
    },
    resolve: {
      adminServiceResolver: adminServiceResolver,
      resolverProvider: ['resolver', function (resolver) {
        return resolver.then;
      }]
    }
  }).state("home", {
    url: "",
    templateUrl: "admin/default/dashboard",
    controller: ['$scope', function ($scope) {
      $scope.$parent.currentItem = {
        'icon': 'home',
        'alias': i18n['menu_dashboard']
      };
    }]
  }) // ngrest crud detail view
  .state("default.route.detail", {
    url: "/:id",
    parent: 'default.route',
    template: '<ui-view/>',
    controller: ['$scope', '$stateParams', function ($scope, $stateParams) {
      $scope.crud = $scope.$parent;

      $scope.init = function () {
        if (!$scope.crud.config.inline) {
          if ($scope.crud.data.updateId != $stateParams.id) {
            $scope.crud.toggleUpdate($stateParams.id);
          }
        }
      };

      $scope.init();
    }]
  });
}]);
/* PROVIDERS */

/**
 * resolver (or resolverProvider).
 * 
 * > Warning: The config part is known injected `resolverProvider` even when the provider name is `resolver`.
 * > Info: can not rename this in admin 1.2 release due to usage in cms module old version branch
 * 
 * Attach custom callback function to the custom state resolve. Use the resolverProvider in
 * your configuration part:
 *
 * ```js
 * zaa.config(function(resolverProvider) {
 *		resolverProvider.addCallback(function(ServiceMenuData, ServiceBlocksData) {
 *			ServiceMenuData.load();
 *			ServiceBlocksData.load();
 *		});
 * });
 * ```
 * 
 * @see https://github.com/angular-ui/ui-router/wiki#resolve
 */

zaa.provider("resolver", [function () {
  var list = [];

  this.addCallback = function (callback) {
    list.push(callback);
  };

  this.$get = ['$injector', '$q', '$state', function ($injector, $q, $state) {
    return $q(function (resolve, reject) {
      for (var i in list) {
        $injector.invoke(list[i]);
      }
    });
  }];
}]);
/* FACTORIES */

/**
 * LUYA Admin Loader.
 * 
 * A fullscreen loading bar which display a loader icon on a black full screen.
 * 
 * ```js
 * LuyaLoading.start('We are loading something ...');
 * ```
 * 
 * In order to hide the above loading screen use:
 * 
 * ```js
 * LuyaLoading.stop();
 * ```
 */

zaa.factory("LuyaLoading", ['$timeout', function ($timeout) {
  var state = false;
  var stateMessage = null;
  var timeoutPromise = null;
  return {
    start: function start(myMessage) {
      if (myMessage == undefined) {
        stateMessage = i18n['js_zaa_server_proccess'];
      } else {
        stateMessage = myMessage;
      } // rm previous timeouts


      $timeout.cancel(timeoutPromise);
      state = true;
      timeoutPromise = $timeout(function () {
        state = true;
      }, 1000);
    },
    stop: function stop() {
      $timeout.cancel(timeoutPromise);
      state = false;
    },
    getStateMessage: function getStateMessage() {
      return stateMessage;
    },
    getState: function getState() {
      return state;
    }
  };
}]);
/**
 * Inside your Directive or Controller:
 * 
 * ```js
 * AdminClassService.setClassSpace('modalBody', 'modal-open')
 * ```
 * 
 * Inside your HTML layout file:
 * 
 * ```html
 * <div class="{{AdminClassService.getClassSpace('modalBody')}}" />
 * ```
 * 
 * In order to clear the class space afterwards:
 * 
 * ```js
 * AdminClassService.clearSpace('modalBody');
 * ```
 */

zaa.factory("AdminClassService", function () {
  var service = [];
  service.vars = {};

  service.getClassSpace = function (spaceName) {
    if (service.vars.hasOwnProperty(spaceName)) {
      return service.vars[spaceName];
    }
  };

  service.hasClassSpace = function (spaceName) {
    if (service.vars.hasOwnProperty(spaceName)) {
      return true;
    }

    return false;
  };

  service.setClassSpace = function (spaceName, className) {
    service.vars[spaceName] = className;
  };

  service.clearSpace = function (spaceName) {
    if (service.vars.hasOwnProperty(spaceName)) {
      service.vars[spaceName] = null;
    }
  };

  service.removeSpace = function (spaceName) {
    if (service.hasClassSpace(spaceName)) {
      delete service.vars[spaceName];
    }
  };

  service.stack = 0;

  service.modalStackPush = function () {
    service.stack += 1;
  };

  service.modalStackRemove = function () {
    if (service.stack <= 1) {
      service.stack = 0;
    } else {
      service.stack -= 1;
    }
  };

  service.modalStackRemoveAll = function () {
    service.stack = 0;
  };

  service.modalStackIsEmpty = function () {
    if (service.stack == 0) {
      return true;
    }

    return false;
  };

  return service;
});
/**
 * A factory recipe to provide cache reload.
 * 
 * ```js
 * CacheReloadService.reload();
 * ```
 */

zaa.factory('CacheReloadService', ['$http', '$window', function ($http, $window) {
  var service = [];

  service.reload = function () {
    $http.get("admin/api-admin-common/cache").then(function (response) {
      $window.location.reload();
    });
  };

  return service;
}]);
/**
 * Intercept the http request in order to provide the bearer token and write debug infos.
 * 
 * + Handling authentification trough Bearer Auth
 * + Redirect to logout page on 401, 403 or 405 response status
 * + Provide data for AdminDebugBar.
 */

zaa.factory("authInterceptor", ['$rootScope', '$q', 'AdminToastService', 'AdminDebugBar', function ($rootScope, $q, AdminToastService, AdminDebugBar) {
  return {
    request: function request(config) {
      if (!config.hasOwnProperty('ignoreLoadingBar')) {
        config.debugId = AdminDebugBar.pushRequest(config);
      }

      if (config.hasOwnProperty('authToken')) {
        var authToken = config.authToken;
      } else {
        var authToken = $rootScope.luyacfg.authToken;
      }

      config.headers = config.headers || {};
      config.headers.Authorization = "Bearer " + authToken;
      var csrfObject = document.head.querySelector("[name=csrf-token]");

      if (csrfObject !== null) {
        config.headers['X-CSRF-Token'] = csrfObject.content;
      }

      return config || $q.when(config);
    },
    response: function response(config) {
      if (!config.hasOwnProperty('ignoreLoadingBar')) {
        AdminDebugBar.pushResponse(config);
      }

      return config || $q.when(config);
    },
    responseError: function responseError(data) {
      if (data.status == 401 || data.status == 403 || data.status == 405) {
        if (!data.config.hasOwnProperty('authToken')) {
          window.location = "admin/default/logout?autologout=1";
        }
      } else if (data.status == 404) {
        var message = data.data.hasOwnProperty('message');

        if (message) {
          AdminToastService.info(data.data.message, 10000);
        } else {
          AdminToastService.info("Response Error: " + data.status + " " + data.statusText, 10000);
        }
      } else if (data.status != 422) {
        var message = data.data.hasOwnProperty('message');

        if (message) {
          AdminToastService.error(data.data.message, 10000);
        } else {
          AdminToastService.error("Response Error: " + data.status + " " + data.statusText, 10000);
        }
      }

      return $q.reject(data);
    }
  };
}]);// service resolver
adminServiceResolver = ['ServiceFoldersData', 'ServiceFiltersData', 'ServiceLanguagesData', 'ServicePropertiesData', 'AdminLangService', 'ServiceFoldersDirecotryId', function (ServiceFoldersData, ServiceFiltersData, ServiceLanguagesData, ServicePropertiesData, AdminLangService, ServiceFoldersDirecotryId) {
  ServiceFiltersData.load();
  ServiceFoldersData.load();
  ServiceLanguagesData.load();
  ServicePropertiesData.load();
  AdminLangService.load();
  ServiceFoldersDirecotryId.load();
}];
/**
 * A promise which is resolved when all queue job ids are don:
 * 
 * ServiceQueueWaiting.waitFor(response.data.queueIds).then({
 *  ....
 * })
 */

zaa.factory("ServiceQueueWaiting", ['$http', '$q', '$timeout', function ($http, $q, $timeout) {
  var service = {
    ids: []
  };

  service.waitFor = function (ids) {
    return $q(function (resolve, reject) {
      if (ids.length == 0) {
        resolve();
      } else {
        ids.forEach(function (jobId) {
          return service.ids.push(jobId);
        });
        var promises = [];
        ids.forEach(function (jobId) {
          promises.push(service.waitForJobId(jobId));
        });
        $q.all(promises).then(function (resolver) {
          resolve();
        });
      }
    });
  };

  service.waitForJobId = function (jobId) {
    return $q(function (resolve, reject) {
      $http.get('admin/api-admin-common/queue-job?jobId=' + jobId, {
        ignoreLoadingBar: true
      }).then(function (response) {
        if (response.data.is_done) {
          var index = service.ids.indexOf(jobId);

          if (index > -1) {
            service.ids.splice(index, 1);
            resolve();
          }
        } else {
          setTimeout(function () {
            service.waitForJobId(jobId).then(function (xr) {
              resolve();
            });
          }, 500);
        }
      });
    });
  };

  return service;
}]);
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

zaa.factory("ServiceAdminTags", ['$http', '$q', '$rootScope', function ($http, $q, $rootScope) {
  var service = {};
  service.data = null;

  service.load = function (forceReload) {
    return $q(function (resolve, reject) {
      if (service.data !== null && forceReload !== true) {
        resolve(service.data);
      } else {
        $http.get("admin/api-admin-common/tags").then(function (response) {
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

zaa.factory("ServiceFoldersData", ['$http', '$q', '$rootScope', function ($http, $q, $rootScope) {
  var service = [];
  service.data = null;

  service.load = function (forceReload) {
    return $q(function (resolve, reject) {
      if (service.data !== null && forceReload !== true) {
        resolve(service.data);
      } else {
        $http.get("admin/api-admin-storage/data-folders").then(function (response) {
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

$scope.folderId = ServiceFoldersDirecotryId.folderId;
					
$scope.$on('service:FoldersDirectoryId', function(event, folderId) {
	$scope.folderId = folderId;
});

$scope.foldersDirecotryIdReload = function() {
	return ServiceFoldersDirecotryId.load(true);
}

*/

zaa.factory("ServiceFoldersDirecotryId", ['$http', '$q', '$rootScope', function ($http, $q, $rootScope) {
  var service = [];
  service.folderId = false;

  service.load = function (forceReload) {
    return $q(function (resolve, reject) {
      if (service.folderId !== false && forceReload !== true) {
        resolve(service.folderId);
      } else {
        $http.get("admin/api-admin-common/get-filemanager-folder-state").then(function (response) {
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

zaa.factory("ServiceImagesData", ['$http', '$q', '$rootScope', '$log', function ($http, $q, $rootScope, $log) {
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

  service.getImage = function (id, forceAsyncRequest) {
    return $q(function (resolve, reject) {
      if (id == 0) {
        return reject(id);
      }

      if (service.data.hasOwnProperty(id) && forceAsyncRequest !== true) {
        return resolve(service.data[id]);
      }

      $http.get('admin/api-admin-storage/image-info?id=' + id).then(function (response) {
        var data = response.data;
        service.data[data.id] = data;
        return resolve(data);
      });
    });
  };

  service.loadImages = function (imagesArray) {
    return $q(function (resolve, reject) {
      if (imagesArray.length == 0) {
        return resolve();
      }

      $http.post('admin/api-admin-storage/images-info?expand=source,tinyCropImage', {
        ids: imagesArray
      }).then(function (response) {
        angular.forEach(response.data, function (value) {
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

zaa.factory("ServiceFilesData", ['$http', '$q', '$rootScope', '$log', function ($http, $q, $rootScope, $log) {
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

  service.getFile = function (id, forceAsyncRequest) {
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


  service.newPromise = function (id, forceAsyncRequest) {
    return $q(function (resolve, reject) {
      if (id == 0) {
        return reject(id);
      }

      if (service.data.hasOwnProperty(id) && forceAsyncRequest !== true) {
        return resolve(service.data[id]);
      }

      $http.get('admin/api-admin-storage/file-info?id=' + id).then(function (response) {
        var data = response.data;
        service.data[data.id] = data;
        delete service._promises[id];
        return resolve(data);
      });
    });
  };

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

zaa.factory("ServiceFiltersData", ['$http', '$q', '$rootScope', function ($http, $q, $rootScope) {
  var service = [];
  service.data = null;

  service.load = function (forceReload) {
    return $q(function (resolve, reject) {
      if (service.data !== null && forceReload !== true) {
        resolve(service.data);
      } else {
        $http.get("admin/api-admin-storage/data-filters").then(function (response) {
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

zaa.factory("ServiceLanguagesData", ['$http', '$q', '$rootScope', function ($http, $q, $rootScope) {
  var service = [];
  service.data = [];

  service.load = function (forceReload) {
    return $q(function (resolve, reject) {
      if (service.data.length > 0 && forceReload !== true) {
        resolve(service.data);
      } else {
        $http.get("admin/api-admin-common/data-languages").then(function (response) {
          service.data = response.data;
          $rootScope.$broadcast('service:LanguagesData', service.data);
          resolve(service.data);
        });
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

zaa.factory("ServicePropertiesData", ['$http', '$q', '$rootScope', function ($http, $q, $rootScope) {
  var service = [];
  service.data = null;

  service.load = function (forceReload) {
    return $q(function (resolve, reject) {
      if (service.data !== null && forceReload !== true) {
        resolve(service.data);
      } else {
        $http.get("admin/api-admin-common/data-properties").then(function (response) {
          service.data = response.data;
          $rootScope.$broadcast('service:PropertiesData', service.data);
          resolve(service.data);
        });
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

zaa.factory("CrudTabService", function () {
  var service = [];
  service.tabs = [];

  service.remove = function (index, $scope) {
    service.tabs.splice(index, 1);

    if (service.tabs.length > 0) {
      var lastTab = service.tabs.slice(-1)[0];
      lastTab.active = true;
    } else {
      $scope.switchTo(0);
    }
  };

  service.addTab = function (id, api, arrayIndex, name, modelClass) {
    var tab = {
      id: id,
      api: api,
      arrayIndex: arrayIndex,
      active: true,
      name: name,
      modelClass: modelClass
    };
    angular.forEach(service.tabs, function (item) {
      item.active = false;
    });
    service.tabs.push(tab);
  };

  service.clear = function () {
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

zaa.factory("AdminLangService", ['ServiceLanguagesData', '$rootScope', function (ServiceLanguagesData, $rootScope) {
  var service = [];
  service.data = [];
  service.selection = [];

  service.toggleSelection = function (lang) {
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

  service.isInSelection = function (langShortCode) {
    var exists = service.selection.indexOf(langShortCode);

    if (exists == -1) {
      return false;
    }

    return true;
  };

  service.resetDefault = function () {
    service.selection = [];
    angular.forEach(ServiceLanguagesData.data, function (value, key) {
      if (value.is_default == 1) {
        if (!service.isInSelection(value.short_code)) {
          service.toggleSelection(value);
        }
      }
    });
  };

  service.load = function () {
    ServiceLanguagesData.load().then(function (data) {
      service.data = data;
      angular.forEach(data, function (value) {
        if (value.is_default == 1) {
          if (!service.isInSelection(value.short_code)) {
            service.toggleSelection(value);
          }
        }
      });
    });
  };

  return service;
}]);
/*
 * Admin Debug Bar provides an array with debug information from the last request in order to find bugs without the developer tools of the browser 
 */

zaa.factory("AdminDebugBar", function () {
  var service = [];
  service.data = [];

  service.clear = function () {
    service.data = [];
  };

  service.pushRequest = function (request) {
    return service.data.push({
      'url': request.url,
      'requestData': request.data,
      'responseData': null,
      'responseStatus': null,
      start: new Date(),
      end: null,
      parseTime: null
    });
  };

  service.pushResponse = function (response) {
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

zaa.factory("AdminToastService", ['$q', '$timeout', '$injector', function ($q, $timeout, $injector) {
  var service = [];

  service.notify = function (message, timeout, type) {
    if (timeout == undefined) {
      timeout = 6000;
    }

    var uuid = guid();
    service.queue[uuid] = {
      message: message,
      timeout: timeout,
      uuid: uuid,
      type: type,
      close: function close() {
        delete service.queue[this.uuid];
      }
    };
    $timeout(function () {
      delete service.queue[uuid];
    }, timeout);
  };

  service.success = function (message, timeout) {
    service.notify(message, timeout, 'success');
  };

  service.info = function (message, timeout) {
    service.notify(message, timeout, 'info');
  };

  service.warning = function (message, timeout) {
    service.notify(message, timeout, 'warning');
  };

  service.error = function (message, timeout) {
    service.notify(message, timeout, 'error');
  };

  service.errorArray = function (array, timeout) {
    angular.forEach(array, function (value, key) {
      service.error(value.message, timeout);
    });
  };

  service.confirm = function (message, title, callback) {
    var uuid = guid();
    service.queue[uuid] = {
      message: message,
      title: title,
      click: function click() {
        var queue = this;
        var response = $injector.invoke(this.callback, this, {
          $toast: this
        });

        if (response !== undefined) {
          response.then(function (r) {
            queue.close();
          }, function (r) {
            queue.close();
          }, function (r) {
            /* call or load at later time */
          });
        }
      },
      uuid: uuid,
      callback: callback,
      type: 'confirm',
      close: function close() {
        delete service.queue[this.uuid];
      }
    };
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

zaa.factory('HtmlStorage', function () {
  var service = {
    data: {},
    isLoaded: false,
    loadData: function loadData() {
      if (!service.isLoaded) {
        if (localStorage.getItem("HtmlStorage")) {
          var data = angular.fromJson(localStorage.getItem('HtmlStorage'));
          service.data = data;
        }
      }
    },
    saveData: function saveData() {
      localStorage.removeItem('HtmlStorage');
      localStorage.setItem('HtmlStorage', angular.toJson(service.data));
    },
    getValue: function getValue(key, defaultValue) {
      service.loadData();

      if (service.data.hasOwnProperty(key)) {
        return service.data[key];
      }

      return defaultValue;
    },
    setValue: function setValue(key, value) {
      service.loadData();
      service.data[key] = value;
      service.saveData();
    }
  };
  return service;
});zaa.filter("filemanagerdirsfilter", function () {
  return function (input, parentFolderId) {
    var result = [];
    angular.forEach(input, function (value, key) {
      if (value.parentId == parentFolderId) {
        result.push(value);
      }
    });
    result.sort(function (a, b) {
      return a.name.toLowerCase() > b.name.toLowerCase() ? 1 : -1;
    });
    return result;
  };
});
zaa.filter("findthumbnail", function () {
  return function (input, fileId, thumbnailFilterId) {
    var result = false;
    angular.forEach(input, function (value, key) {
      if (!result) {
        if (value.fileId == fileId && value.filterId == thumbnailFilterId) {
          result = value;
        }
      }
    });
    return result;
  };
});
zaa.filter("findidfilter", function () {
  return function (input, id) {
    var result = false;
    angular.forEach(input, function (value, key) {
      if (value.id == id) {
        result = value;
      }
    });
    return result;
  };
});
zaa.filter("filemanagerfilesfilter", function () {
  return function (input, folderId, onlyImages) {
    var result = [];
    angular.forEach(input, function (data) {
      if (onlyImages) {
        if (data.folderId == folderId && data.isImage == true) {
          result.push(data);
        }
      } else {
        if (data.folderId == folderId) {
          result.push(data);
        }
      }
    });
    return result;
  };
});
zaa.filter('trustAsUnsafe', ['$sce', function ($sce) {
  return function (val, enabled) {
    return $sce.trustAsHtml(val);
  };
}]);
zaa.filter('srcbox', function () {
  return function (input, search) {
    if (!input) return input;
    if (!search) return input;
    var expected = ('' + search).toLowerCase();
    var result = {};
    angular.forEach(input, function (value, key) {
      angular.forEach(value, function (kv, kk) {
        var actual = ('' + kv).toLowerCase();

        if (actual.indexOf(expected) !== -1) {
          result[key] = value;
        }
      });
    });
    return result;
  };
});
zaa.filter('trustAsResourceUrl', ['$sce', function ($sce) {
  return function (val, enabled) {
    if (!enabled) {
      return null;
    }

    return $sce.trustAsResourceUrl(val);
  };
}]);
zaa.filter('truncateMiddle', function () {
  return function (val, length, placeholder) {
    if (!length) {
      length = 30;
    }

    if (!placeholder) {
      placeholder = '...';
    }

    if (val.length <= length) {
      return val;
    }

    var targetLength = length - placeholder.length;
    var partLength = targetLength / 2;
    return val.substring(0, partLength) + placeholder + val.substring(val.length - partLength, val.length);
  };
});/**
 * Directive to generate e chart diagrams.
 *
 * uses echarts.js component.
 *
 * ```js
 * <echarts id="chart" data="data"></echarts>
 * ```
 *
 * Where data is a variable bound by angular! So it a variable from angular $scope.data
 *
 */
zaa.directive('echarts', [function () {
  return {
    scope: {
      id: "@",
      theme: "@",
      data: "="
    },
    restrict: 'E',
    template: '<div style="min-height:300px;height:auto;width:100%;"></div>',
    replace: true,
    controller: ['$scope', function ($scope) {
      if ($scope.theme) {
        $scope.theme = 'macarons';
      }
    }],
    link: function link($scope) {
      // generate echars document
      var echartElement = echarts.init(document.getElementById($scope.id), $scope.theme);
      $scope.$watch('data', function (n) {
        if (n && n != undefined) {
          echartElement.setOption(angular.fromJson(n));
        }
      }); // ensure resize happens

      angular.element(window).bind('resize', function () {
        echartElement.resize();
      });
    }
  };
}]);
/**
 * Controller: $scope.content = $sce.trustAsHtml(response.data);
 *
 * Usage:
 *
 * ```
 * <div compile-html ng-bind-html="content | trustAsUnsafe"></div>
 * ```
 */

zaa.directive("compileHtml", ['$compile', '$parse', function ($compile, $parse) {
  return {
    restrict: "A",
    link: function link(scope, element, attr) {
      var parsed = $parse(attr.ngBindHtml);
      scope.$watch(function () {
        return (parsed(scope) || "").toString();
      }, function () {
        $compile(element, null, -9999)(scope); //The -9999 makes it skip directives so that we do not recompile ourselves
      });
    }
  };
}]);
/**
 * Select a given element when clicking on it
 * 
 * Usage:
 * 
 * ```
 * <input type="text" class="form-control form-control-sm mt-3" readonly select-on-click ng-model="fileDetailFull.source" />
 * ```
 * 
 * @since 2.1.0
 */

zaa.directive('selectOnClick', function () {
  // Linker function
  return function (scope, element, attrs) {
    element.bind('click', function () {
      this.select();
    });
  };
});
/**
 * Usage:
 *
 * ```
 * <div zaa-esc="methodClosesThisDiv()" />
 * ```
 */

zaa.directive("zaaEsc", ['$document', function ($document) {
  return function (scope, element, attrs) {
    $document.on("keyup", function (e) {
      if (e.keyCode == 27) {
        scope.$apply(function () {
          scope.$eval(attrs.zaaEsc);
        });
      }
    });
  };
}]);
/**
 * Returns the link options as value.
 */

zaa.directive("linkObjectToString", function () {
  return {
    restrict: 'E',
    relace: true,
    scope: {
      'link': '='
    },
    template: function template() {
      return '<span>' + '<span ng-if="link.type==1"><show-internal-redirection nav-id="link.value" /></span>' + '<span ng-if="link.type==2">{{link.value}}</span>' + '<span ng-if="link.type==3"><storage-file-display file-id="{{link.value}}"></storage-file-display></span>' + '<span ng-if="link.type==4">{{link.value}}</span>' + '<span ng-if="link.type==5">{{link.value}}</span>' + '</span>';
    }
  };
});
/**
 * Generate a Tool Tip – usage:
 *
 * The default tooltip is positioned on the right side of the element:
 *
 * ```html
 * <span tooltip tooltip-text="Tooltip">...</span>
 * ```
 *
 *
 * You can provide an Image URL beside or instead of text.
 *
 * ```html
 * <span tooltip tooltip-image-url="http://image.url">...</span>
 * ```
 *
 * Change the position (`top`, `right`, `bottom` or `left`):
 *
 * ```html
 * <span tooltip tooltip-text="Tooltip" tooltip-position="top">...</span>
 * ```
 *
 *
 * Add an offset to the generated position. The example below adds 5px offset from left and pulls the tooltip 5px up.
 *
 * ```html
 * <span tooltip tooltip-text="Tooltip" tooltip-offset-left="5" tooltip-offset-top="-5">...</span>
 * ```
 *
 *
 * In order to trigger an expression call instead of a static text use:
 *
 * ```html
 * <span tooltip tooltip-expression="scopeFunction(fooBar)">Span Text</span>
 * ```
 *
 *
 * Disable tooltip based on variable (two way binding):
 *
 * ```html
 * <span tooltip tooltip-text="Tooltip" tooltip-disabled="variableMightBeTrueMightBeFalseMightChange">Span Text</span>
 * ```
 */

zaa.directive("tooltip", ['$document', '$http', function ($document, $http) {
  return {
    restrict: 'A',
    scope: {
      'tooltipText': '@',
      'tooltipExpression': '=',
      'tooltipPosition': '@',
      'tooltipOffsetTop': '@',
      'tooltipOffsetLeft': '@',
      'tooltipImageUrl': '@',
      'tooltipPreviewUrl': '@',
      'tooltipDisabled': '='
    },
    link: function link(scope, element, attr) {
      var defaultPosition = 'right';
      var positions = {
        top: function top() {
          var bcr = element[0].getBoundingClientRect();
          return {
            top: bcr.top - scope.pop.outerHeight(),
            left: bcr.left + bcr.width / 2 - scope.pop.outerWidth() / 2
          };
        },
        bottom: function bottom() {
          var bcr = element[0].getBoundingClientRect();
          return {
            top: bcr.top + bcr.height,
            left: bcr.left + bcr.width / 2 - scope.pop.outerWidth() / 2
          };
        },
        right: function right() {
          var bcr = element[0].getBoundingClientRect();
          return {
            top: bcr.top + bcr.height / 2 - scope.pop.outerHeight() / 2,
            left: bcr.left + bcr.width
          };
        },
        left: function left() {
          var bcr = element[0].getBoundingClientRect();
          return {
            top: bcr.top + bcr.height / 2 - scope.pop.outerHeight() / 2,
            left: bcr.left - scope.pop.outerWidth()
          };
        }
      };

      var onScroll = function onScroll() {
        var offset = {};

        if (typeof positions[scope.tooltipPosition] === 'function') {
          offset = positions[scope.tooltipPosition]();
        } else {
          offset = positions[defaultPosition]();
        }

        var tooltipOffsetTop = parseInt(scope.tooltipOffsetTop);

        if (tooltipOffsetTop) {
          offset.top = offset.top + tooltipOffsetTop;
        }

        var tooltipOffsetLeft = parseInt(scope.tooltipOffsetLeft);

        if (tooltipOffsetLeft) {
          offset.left = offset.left + tooltipOffsetLeft;
        }

        scope.pop.css(offset);
      };

      element.on('mouseenter', function () {
        // Generate tooltip HTML for the first time
        if (!scope.pop && (typeof scope.tooltipDisabled === 'undefined' || scope.tooltipDisabled === false)) {
          if (scope.tooltipExpression) {
            scope.tooltipText = scope.tooltipExpression;
          }

          var html = '<div class="tooltip tooltip-' + (scope.tooltipPosition || defaultPosition) + (scope.tooltipImageUrl ? ' tooltip-image' : '') + '" role="tooltip">' + '<div class="tooltip-arrow"></div>' + '<div class="tooltip-inner">' + (scope.tooltipText ? '<span class="tooltip-text">' + scope.tooltipText + '</span>' : '') + '</div>' + '</div>';
          var $html = $(html);

          if (scope.tooltipImageUrl) {
            var image = new Image();

            image.onload = function () {
              onScroll();
            };

            image.src = scope.tooltipImageUrl;
            $html.find('.tooltip-inner').append(image);
          }

          if (scope.tooltipPreviewUrl) {
            $http.get(scope.tooltipPreviewUrl).then(function (response) {
              $html.find('.tooltip-inner').append('<div class="tooltip-preview">' + response.data + '</div>');
            });
          }

          scope.pop = $html;
          $document.find('body').append(scope.pop);
          scope.pop.hide();
        } // If tooltip shall be display...


        if (scope.pop && (typeof scope.tooltipDisabled === 'undefined' || scope.tooltipDisabled === false)) {
          // ..check position
          onScroll(); // todo: Improve performance ...? x)
          // ..register scroll listener

          element.parents().on('scroll', onScroll); // ..show popup

          scope.pop.show();
        }
      });
      element.on('mouseleave', function () {
        element.parents().off('scroll', onScroll);

        if (scope.pop) {
          scope.pop.hide();
        }
      });
      scope.$on('$destroy', function () {
        if (scope.pop) {
          scope.pop.remove();
        }
      });
    }
  };
}]);
/**
 * Convert a string to number value, usefull in selects.
 *
 * ```
 * <select name="filterId" ng-model="filterId" convert-to-number>
 * ```
 */

zaa.directive('convertToNumber', function () {
  return {
    require: 'ngModel',
    link: function link(scope, element, attrs, ngModel) {
      ngModel.$parsers.push(function (val) {
        return val != null ? parseInt(val, 10) : null;
      });
      ngModel.$formatters.push(function (val) {
        return val != null ? '' + val : null;
      });
    }
  };
});
/**
 * Apply auto generated height for textareas based on input values
 */

zaa.directive('autoGrow', function () {
  return function (scope, element, attr) {
    var $shadow = null;

    var destroy = function destroy() {
      if ($shadow != null) {
        $shadow.remove();
        $shadow = null;
      }
    };

    var update = function update() {
      if ($shadow == null) {
        $shadow = angular.element('<div></div>').css({
          position: 'absolute',
          top: -10000,
          left: -10000,
          resize: 'none'
        });
        angular.element(document.body).append($shadow);
      }

      $shadow.css({
        fontSize: element.css('font-size'),
        fontFamily: element.css('font-family'),
        lineHeight: element.css('line-height'),
        width: element.width(),
        paddingTop: element.css('padding-top'),
        paddingBottom: element.css('padding-bottom')
      });

      var times = function times(string, number) {
        for (var i = 0, r = ''; i < number; i++) {
          r += string;
        }

        return r;
      };

      var val = element.val().replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/&/g, '&amp;').replace(/\n$/, '<br/>&nbsp;').replace(/\n/g, '<br/>').replace(/\s{2,}/g, function (space) {
        return times('&nbsp;', space.length - 1) + ' ';
      });
      $shadow.html(val);
      element.css('height', $shadow.outerHeight() + 10 + 'px');
    };

    element.bind('keyup keydown keypress change click', update);
    element.bind('blur', destroy);
    update();
  };
});
/**
 * Resize the given element
 */

zaa.directive('resizer', ['$document', function ($document) {
  return {
    scope: {
      trigger: '@'
    },
    link: function link($scope, $element, $attrs) {
      $scope.$watch('trigger', function (n, o) {
        if (n == 0) {
          $($attrs.resizerLeft).removeAttr('style');
          $($attrs.resizerRight).removeAttr('style');
        }
      });
      $element.on('mousedown', function (event) {
        event.preventDefault();
        $document.on('mousemove', mousemove);
        $document.on('mouseup', mouseup);
      });

      function mousemove(event) {
        $($attrs.resizerCover).show(); // Handle vertical resizer

        var x = event.pageX;
        var i = window.innerWidth;

        if (x < 600) {
          x = 600;
        }

        if (x > i - 400) {
          x = i - 400;
        }

        var wl = $($attrs.resizerLeft).width();
        var wr = $($attrs.resizerRight).width();
        $($attrs.resizerLeft).css({
          width: x + 'px'
        });
        $($attrs.resizerRight).css({
          width: i - x + 'px'
        });
      }

      function mouseup() {
        $($attrs.resizerCover).hide();
        $document.unbind('mousemove', mousemove);
        $document.unbind('mouseup', mouseup);
      }
    }
  };
}]);
/**
 * Readded ng-confirm-click in order to provide quick ability to implement confirm boxes.
 *
 * ```
 * <button ng-confirm-click="Are you sure you want to to delete {{data.title}}?" confirmed-click="remove(data)">Remove</button>
 * ```
 */

zaa.directive("ngConfirmClick", function () {
  return {
    link: function link(scope, element, attr) {
      var msg = attr.ngConfirmClick || "Are you sure?";
      var clickAction = attr.confirmedClick;
      element.bind("click", function (event) {
        if (window.confirm(msg)) {
          scope.$eval(clickAction);
        }
      });
    }
  };
});
/**
 * Focus a given input field if the statement is true.
 *
 * ```
 * <input type="text" focus-me="searchInputOpen" />
 * ```
 */

zaa.directive('focusMe', ['$timeout', '$parse', function ($timeout, $parse) {
  return {
    link: function link(scope, element, attrs) {
      var model = $parse(attrs.focusMe);
      scope.$watch(model, function (value) {
        if (value === true) {
          $timeout(function () {
            element[0].focus();
          });
        }
      });
    }
  };
}]);
/**
 * ```
 * <a href="#" click-paste-pusher="foobar">Test</a>
 * ```
 */

zaa.directive("clickPastePusher", ['$rootScope', '$compile', function ($rootScope, $compile) {
  return {
    restrict: 'A',
    replace: false,
    link: function link(scope, element, attrs) {
      element.bind('click', function () {
        $rootScope.$broadcast('insertPasteListener', attrs['clickPastePusher']);
      });
    }
  };
}]);
/**
 *
 * ```
 * $rootScope.$broadcast('insertPasteListener', $scope.someInput);
 * ```
 *
 * ```
 * <textarea insert-paste-listener></textarea>
 * ```
 */

zaa.directive('insertPasteListener', ['$rootScope', function ($rootScope) {
  return {
    restrict: 'A',
    link: function link(scope, element, attrs) {
      element.bind("focus", function () {
        $rootScope.lastElement = element[0];
        var offCallFn = $rootScope.$on('insertPasteListener', function (e, val) {
          var domElement = $rootScope.lastElement;

          if (domElement != element[0] || !domElement) {
            return false;
          }

          $rootScope.$$listeners.insertPasteListener = [];

          if (document.selection) {
            domElement.focus();
            var sel = document.selection.createRange();
            sel.text = val;
            domElement.focus();
          } else if (domElement.selectionStart || domElement.selectionStart === 0) {
            var startPos = domElement.selectionStart;
            var endPos = domElement.selectionEnd;
            var scrollTop = domElement.scrollTop;
            domElement.value = domElement.value.substring(0, startPos) + val + domElement.value.substring(endPos, domElement.value.length);
            domElement.focus();
            domElement.selectionStart = startPos + val.length;
            domElement.selectionEnd = startPos + val.length;
            domElement.scrollTop = scrollTop;
          } else {
            domElement.value += val;
            domElement.focus();
          }
        });
      });
    }
  };
}]);
/**
 * Example usage of luya admin modal:
 *
 * ```js
 * <button ng-click="modalState=!modalState">Toggle Modal</button>
 * <modal is-modal-hidden="modalState" modal-title="I am the Title">
 *     <h1>Modal Container</h1>
 *     <p>Hello world!</p>
 * </modal>
 * ```
 *
 * If you want to hidden use ng-if with modals, you have to use ng-if inside the modal like:
 *
 * ```js
 * <modal is-modal-hidden="modalState">
 *    <div ng-if="!modalState">
 *        <p>This is only linked when modalState is visible</p>
 *    </div>
 * </modal>
 * ```
 *
 * > Using the ng-if outside of the modal wont work as it does not trigger the modalState due to child scope creation each time
 * > the ng-if is visible.
 *
 */

zaa.directive("modal", ['$timeout', function ($timeout) {
  return {
    restrict: "E",
    scope: {
      isModalHidden: "=",
      title: '@modalTitle'
    },
    replace: true,
    transclude: true,
    templateUrl: "modal",
    controller: ['$scope', 'AdminClassService', function ($scope, AdminClassService) {
      $scope.$watch('isModalHidden', function (n, o) {
        if (n !== o) {
          if (n) {
            // is hidden
            AdminClassService.modalStackRemove();
          } else {
            // is visible
            AdminClassService.modalStackPush();
          }
        }
      });
      /* ESC Key will close ALL modals, therefore we ensure the correct spaces */

      $scope.escModal = function () {
        $scope.isModalHidden = true;
        AdminClassService.modalStackRemoveAll();
      };
    }],
    link: function link(scope, element) {
      scope.$on('$destroy', function () {
        element.remove();
      });
      angular.element(document.body).append(element);
    }
  };
}]);
/**
 * A transclude element for an collapsible (accordian similar) container.
 *
 * Usage example:
 *
 * ```
 * <collapse-container title="Advanced Settings">
 *  <h1>Title</h1>
 *  <div>do stuff here ..</div>
 * </collapse-container>
 * ```
 *
 * @since 2.0.3
 */

zaa.directive("collapseContainer", [function () {
  return {
    restrict: "E",
    scope: {
      "title": "@",
      "icon": "@"
    },
    replace: true,
    transclude: true,
    controller: ['$scope', function ($scope) {
      $scope.visible = false;

      $scope.toggleVisibility = function () {
        $scope.visible = !$scope.visible;
      };
    }],
    template: function template() {
      return '<div class="card" ng-class="{\'card-closed\': !visible}">' + '<div class="card-header" ng-click="toggleVisibility()">' + '<span class="material-icons card-toggle-indicator">keyboard_arrow_down</span>' + '<i class="material-icons" ng-show="icon">{{icon}}</i>' + '<span>{{title}}</span>' + '</div>' + '<div class="card-body" ng-transclude></div>' + '</div>';
    }
  };
}]);
/* CRUD, FORMS & FILE MANAGER */

/**
 * If modelSelection and modelSetter is enabled, you can select a given row based in its primary key which will triggered the ngrest of the parent CRUD form.
 *
 * ```
 * <crud-loader api="forms/form/index" alias="Name of the CRUD Active Window"></crud-loader>
 * ```
 * 
 * > It actuall does not take the api endpoint, because it needs to render all the html and therefore the api parameter takes the ngrest controller route like `<module>/<apicontroller>/index`.
 */

zaa.directive("crudLoader", ['$http', '$sce', function ($http, $sce) {
  return {
    restrict: "E",
    replace: true,
    transclude: false,
    scope: {
      "api": "@",
      "alias": "@",
      "modelSelection": "@",
      "modelSetter": "="
    },
    controller: ['$scope', function ($scope) {
      $scope.input = {
        showWindow: true
      };
      $scope.content = null;

      $scope.toggleWindow = function () {
        if ($scope.input.showWindow) {
          if ($scope.api.indexOf('?') > -1) {
            var url = $scope.api + '&inline=1';
          } else {
            var url = $scope.api + '?inline=1';
          }

          var modelSelection = parseInt($scope.modelSelection);

          if (modelSelection) {
            url = url + '&modelSelection=' + $scope.modelSetter;
          }

          $http.get(url).then(function (response) {
            $scope.content = $sce.trustAsHtml(response.data);
            $scope.input.showWindow = false;
          });
        } else {
          if (typeof $scope.$parent.loadService == 'function') {
            $scope.$parent.loadService();
          }

          $scope.input.showWindow = true;
        }
      };

      $scope.$watch('input.showWindow', function (n, o) {
        if (n !== o && n == 1) {
          if (typeof $scope.$parent.loadService == 'function') {
            $scope.$parent.loadService();
          }
        }
      });
      /**
       * @param integer $value contains the primary key
       * @param array $row contains the full row from the crud loader model in order to display data.
       */

      $scope.setModelValue = function (value, row) {
        $scope.modelSetter = value;
        $scope.toggleWindow();
      };
    }],
    template: function template() {
      return '<div class="crud-loader-tag"><button ng-click="toggleWindow()" type="button" class="btn btn-info btn-icon"><i class="material-icons">playlist_add</i></button><modal is-modal-hidden="input.showWindow" modal-title="{{alias}}"><div class="modal-body" compile-html ng-bind-html="content"></modal></div>';
    }
  };
}]);
/**
 * Directive to load crud relations.
 */

zaa.directive("crudRelationLoader", ['$http', '$sce', function ($http, $sce) {
  return {
    restrict: "E",
    replace: true,
    transclude: false,
    scope: {
      "api": "@api",
      "arrayIndex": "@arrayIndex",
      "modelClass": "@modelClass",
      "id": "@id"
    },
    controller: ['$scope', function ($scope) {
      $scope.content = null;

      if ($scope.api.indexOf('?') > -1) {
        var url = $scope.api + '&inline=1';
      } else {
        var url = $scope.api + '?inline=1';
      }

      $http.get(url + '&relation=' + $scope.id + '&arrayIndex=' + $scope.arrayIndex + '&modelClass=' + $scope.modelClass).then(function (response) {
        $scope.content = $sce.trustAsHtml(response.data);
      });
    }],
    template: function template() {
      return '<div compile-html ng-bind-html="content"></div>';
    }
  };
}]);
/** ZAA ANGULAR FORM INPUT DIRECTIVES */

/**
 * Generate form input types based on ZAA Directives.
 *
 * Usage inside another Angular Template:
 *
 * ```php
 * <zaa-injector dir="zaa-text" options="{}" fieldid="myFieldId" initvalue="0" label="My Label" model="mymodel"></zaa-injector>
 * ```
 */

zaa.directive("zaaInjector", ['$compile', function ($compile) {
  return {
    restrict: "E",
    replace: true,
    transclude: false,
    scope: {
      "dir": "=",
      "model": "=",
      "options": "=",
      "label": "@label",
      "grid": "@grid",
      "fieldid": "@fieldid",
      "placeholder": "@placeholder",
      "initvalue": "@initvalue",
      "autocomplete": "@autocomplete"
    },
    link: function link($scope, $element) {
      var elmn = $compile(angular.element('<' + $scope.dir + ' options="options" initvalue="{{initvalue}}" fieldid="{{fieldid}}" placeholder="{{placeholder}}" autocomplete="{{autocomplete}}" model="model" label="{{label}}" i18n="{{grid}}" />'))($scope);
      $element.replaceWith(elmn);
    }
  };
}]);
/**
 * @var object $model Contains existing data for the displaying the existing relations
 *
 * ```js
 * [
 * 	{'sortpos': 1, 'value': 1},
 *  {'sortpos': 2, 'value': 4},
 * ]
 * ```
 *
 * @var object $options Provides options to build the sort relation array:
 *
 * ```js
 * {
 * 	'sourceData': [
 * 		{'value': 1, 'label': 'Source Entry #1'}
 * 		{'value': 2, 'label': 'Source Entry #2'}
 * 		{'value': 3, 'label': 'Source Entry #3'}
 * 		{'value': 4, 'label': 'Source Entry #4'}
 * 	]
 * }
 * ```
 */

zaa.directive("zaaSortRelationArray", function () {
  return {
    restrict: "E",
    scope: {
      "model": "=",
      "options": "=",
      "label": "@label",
      "i18n": "@i18n",
      "id": "@fieldid"
    },
    controller: ['$scope', '$filter', function ($scope, $filter) {
      $scope.searchString;
      $scope.sourceData = [];
      $scope.dropdownOpen = false;
      $scope.$watch(function () {
        return $scope.model;
      }, function (n, o) {
        if (n == undefined) {
          $scope.model = [];
        }
      });
      $scope.$watch(function () {
        return $scope.options;
      }, function (n, o) {
        if (n !== undefined && n !== null) {
          $scope.sourceData = n.sourceData;
        }
      });

      $scope.getSourceOptions = function () {
        return $scope.sourceData;
      };

      $scope.getModelItems = function () {
        return $scope.model;
      };

      $scope.addToModel = function (option) {
        var match = false;
        angular.forEach($scope.model, function (value, key) {
          if (value.value == option.value) {
            match = true;
          }
        });

        if (!match) {
          $scope.model.push({
            'value': option.value,
            'label': option.label
          });
        }
      };

      $scope.removeFromModel = function (key) {
        $scope.model.splice(key, 1);
      };

      $scope.moveUp = function (index) {
        index = parseInt(index);
        var oldRow = $scope.model[index];
        $scope.model[index] = $scope.model[index - 1];
        $scope.model[index - 1] = oldRow;
      };

      $scope.moveDown = function (index) {
        index = parseInt(index);
        var oldRow = $scope.model[index];
        $scope.model[index] = $scope.model[index + 1];
        $scope.model[index + 1] = oldRow;
      };

      $scope.elementInModel = function (item) {
        var match = false;
        angular.forEach($scope.model, function (value, key) {
          if (value.value == item.value) {
            match = true;
          }
        });
        return !match;
      };
    }],
    template: function template() {
      return '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' + '<div class="form-side form-side-label"><label for="{{id}}">{{label}}</label></div>' + '<div class="form-side">' + '<div class="list">' + '<div class="list-item" ng-repeat="(key, item) in getModelItems() track by key">' + '<div class="list-buttons">' + '<i ng-show="!$first" ng-click="moveUp(key)" class="material-icons" style="transform: rotate(270deg);">play_arrow</i>' + '<i ng-show="!$last" ng-click="moveDown(key)" class="material-icons" style="transform: rotate(90deg);">play_arrow</i>' + '</div>' + '<span>{{item.label}}</span>' + '<div class="float-right">' + '<i ng-click="removeFromModel(key)" class="material-icons">delete</i>' + '</div>' + '</div>' + '<div class="list-item" ng-show="sourceData.length != model.length">' + '<input class="form-control" type="search" ng-model="searchString" ng-focus="dropdownOpen = true" />' + '<ul class="list-group">' + '<li class="list-group-item list-group-item-action" ng-repeat="option in getSourceOptions() | filter:searchString" ng-show="dropdownOpen && elementInModel(option)" ng-click="addToModel(option)">' + '<i class="material-icons">add_circle</i><span>{{ option.label }}</span>' + '</li>' + '</ul>' + '<div class="list-chevron">' + '<i ng-click="dropdownOpen=!dropdownOpen" class="material-icons" ng-show="dropdownOpen">arrow_drop_up</i>' + '<i ng-click="dropdownOpen=!dropdownOpen" class="material-icons" ng-show="!dropdownOpen">arrow_drop_down</i>' + '</div>' + '</div>' + '</div>' + '</div>';
    }
  };
});
/**
 * Generate an array of tag ids which are selected from the list of tags.
 * 
 * An example content of model could be `var model = [1,3,4]` where values are the TAG IDs.
 * 
 * @since 2.2.1
 */

zaa.directive("zaaTagArray", function () {
  return {
    restrict: "E",
    scope: {
      "model": "=",
      "label": "@label",
      "i18n": "@i18n",
      "id": "@fieldid"
    },
    controller: ['$scope', '$http', function ($scope, $http) {
      $scope.tags = [];
      $http.get('admin/api-admin-common/tags').then(function (response) {
        angular.forEach(response.data, function (value) {
          value.id = parseInt(value.id);
          $scope.tags.push(value);
        });
      });

      if ($scope.model == undefined) {
        $scope.model = [];
      } else {
        angular.forEach($scope.model, function (value, key) {
          $scope.model[key] = parseInt(value);
        });
      }

      $scope.isInSelection = function (id) {
        id = parseInt(id);

        if ($scope.model.indexOf(id) == -1) {
          return false;
        }

        return true;
      };

      $scope.toggleSelection = function (id) {
        var i = $scope.model.indexOf(id);

        if (i > -1) {
          $scope.model.splice(i, 1);
        } else {
          $scope.model.push(id);
        }
      };
    }],
    template: function template() {
      return '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' + '<div class="form-side form-side-label"><label for="{{id}}">{{label}}</label></div><div class="form-side">' + '<span ng-click="toggleSelection(tag.id)" ng-repeat="tag in tags" ng-class="{\'badge-primary\' : isInSelection(tag.id), \'badge-secondary\' : !isInSelection(tag.id)}" class="badge badge-pill mx-1 mb-2">{{tag.name}}</span>' + '</div>' + '</div>';
    }
  };
});
/**
 * <zaa-link model="linkinfo"></zaa-link>
 */

zaa.directive("zaaLink", ['$filter', function ($filter) {
  return {
    restrict: "E",
    scope: {
      "model": "=",
      "options": "=",
      "label": "@label",
      "i18n": "@i18n",
      "id": "@fieldid"
    },
    controller: ['$scope', function ($scope) {
      $scope.unset = function () {
        $scope.model = false;
        $scope.data.model = null;
      };

      $scope.data = {
        modalState: 1,
        model: null
      };
      $scope.$watch('model', function (n, o) {
        if (n) {
          $scope.data.model = n;
        }
      }, true);
      $scope.$watch('data.model', function (n, o) {
        if (n) {
          $scope.model = n;
        }
      }, true);

      $scope.isEmpty = function (value) {
        if (value) {
          return $filter('isEmpty')(value);
        }

        return true;
      };
    }],
    template: function template() {
      return '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' + '<div class="form-side form-side-label">' + '<labelfor="{{id}}">{{label}}</label>' + '</div>' + '<div class="form-side">' + '<div ng-if="!isEmpty(data.model)">' + '<div class="link-selector">' + '<div class="link-selector-actions">' + '<div class="link-selector-btn btn btn-secondary" ng-click="data.modalState=0">' + '<i class="material-icons left">insert_link</i>' + '<span>' + i18n['js_link_change_value'] + '</span>' + '</div>' + '<span ng-hide="model | isEmpty" class="link-selector-reset" ng-click="unset()"><i class="material-icons">remove_circle</i></span>' + '</div>' + '<link-object-to-string class="ml-2" link="model"></link-object-to-string>' + '</div>' + '</div>' + '<div ng-if="isEmpty(data.model)">' + '<div class="link-selector">' + '<div class="link-selector-actions">' + '<div class="link-selector__btn btn btn-secondary" ng-click="data.modalState=0">' + '<i class="material-icons left">insert_link</i>' + '<span>' + i18n['js_link_set_value'] + '</span>' + '</div>' + '<span style="margin-left:10px;">' + i18n['js_link_not_set'] + '</span>' + '</div>' + '</div>' + '</div>' + '<modal is-modal-hidden="data.modalState" modal-title="{{label}}"><form ng-submit="data.modalState=1">' + '<zaa-link-options data="data.model" uid="id" ng-if="!data.modalState"></zaa-link-options>' + '<button ng-click="data.modalState=1" class="btn btn-icon btn-save" type="submit">' + i18n['js_link_set_value'] + '</button></form>' + '</modal>' + '</div>' + '</div>';
    }
  };
}]);
/**
 * Provides all linkable object options.
 * 
 * + internal redirect
 * + external redirect
 * + to file
 * + to email
 * + to telephone
 */

zaa.directive("zaaLinkOptions", function () {
  return {
    restrict: 'EA',
    scope: {
      data: '=',
      uid: '='
    },
    templateUrl: 'linkoptions.html',
    controller: ['$scope', function ($scope) {
      $scope.$watch(function () {
        return $scope.data;
      }, function (n, o) {
        if (angular.isArray(n)) {
          $scope.data = {};
        }
      });
    }]
  };
});
/**
 * Generates slug from a given model input.
 *
 * If a listener attribute is provided i will take the information from there.
 */

zaa.directive("zaaSlug", function () {
  return {
    restrict: "E",
    scope: {
      "model": "=",
      "options": "=",
      "listener": "=",
      "label": "@label",
      "i18n": "@i18n",
      "id": "@fieldid"
    },
    controller: ['$scope', '$filter', function ($scope, $filter) {
      $scope.$watch('listener', function (n, o) {
        if (n !== undefined) {
          $scope.model = $filter('slugify')(n);
        }
      });
      $scope.$watch('model', function (n, o) {
        if (n != o) {
          $scope.model = $filter('slugify')(n);
        }
      });
    }],
    template: function template() {
      return '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}"><div class="form-side form-side-label"><label for="{{id}}">{{label}}</label></div><div class="form-side"><input id="{{id}}" insert-paste-listener ng-model="model" type="text" class="form-control" placeholder="{{placeholder}}" /></div></div>';
    }
  };
});
zaa.directive("zaaColor", function () {
  return {
    restrict: "E",
    scope: {
      "model": "=",
      "options": "=",
      "label": "@label",
      "i18n": "@i18n",
      "id": "@fieldid"
    },
    controller: ['$scope', function ($scope) {
      function getTextColor() {
        if (typeof $scope.model === 'undefined' || !$scope.model) {
          return '#000';
        }

        var hex = $scope.model;

        if (typeof $scope.model === 'string') {
          hex = hex.substr(1);
        }

        if (hex.length === 3) {
          var shorthandRegex = /^#?([a-f\d])([a-f\d])([a-f\d])$/i;
          hex = hex.replace(shorthandRegex, function (m, r, g, b) {
            return r + r + g + g + b + b;
          });
        }

        if (hex.length === 6) {
          var r = parseInt(hex.substr(0, 2), 16);
          var g = parseInt(hex.substr(2, 2), 16);
          var b = parseInt(hex.substr(4, 2), 16);
          var yiq = (r * 299 + g * 587 + b * 114) / 1000;
          return yiq >= 128 ? '#000' : '#fff';
        }

        return '#000';
      }

      $scope.textColor = getTextColor();
      $scope.$watch(function () {
        return $scope.model;
      }, function (n, o) {
        $scope.textColor = getTextColor();
      });
    }],
    template: function template() {
      return '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' + '<div class="form-side form-side-label">' + '<label for="{{id}}">{{label}}</label>' + '</div>' + '<div class="form-side">' + '<div class="colorwheel">' + '<div class="colorwheel-background" style="background-color: {{model}};">' + '<input class="colorwheel-input" type="text" ng-model="model" style="color: {{textColor}}; border-color: {{textColor}};" maxlength="7" />' + '</div>' + '<div class="colorwheel-wheel"><div ng-colorwheel="{ size: 150, segments: 120 }" ng-model="model"></div></div>' + '</div>' + '</div>' + '</div>';
    }
  };
});
zaa.directive("zaaWysiwyg", function () {
  return {
    restrict: "E",
    scope: {
      "model": "=",
      "options": "=",
      "label": "@label",
      "i18n": "@i18n",
      "id": "@fieldid"
    },
    template: function template() {
      return '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}"><div class="form-side form-side-label"><label for="{{id}}">{{label}}</label></div><div class="form-side"><ng-wig ng-disabled="false" ng-model="model" buttons="bold, italic, link, list1, list2" source-mode-allowed></ng-wig></div></div>';
    }
  };
});
zaa.directive("zaaNumber", function () {
  return {
    restrict: "E",
    scope: {
      "model": "=",
      "options": "=",
      "label": "@label",
      "i18n": "@i18n",
      "id": "@fieldid",
      "placeholder": "@placeholder",
      "initvalue": "@initvalue"
    },
    link: function link($scope) {
      $scope.$watch(function () {
        return $scope.model;
      }, function (n, o) {
        if (n == undefined) {
          $scope.model = parseInt($scope.initvalue);
        }

        if (angular.isNumber($scope.model)) {
          $scope.isValid = true;
        } else {
          $scope.isValid = false;
        }
      });
    },
    template: function template() {
      return '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}"><div class="form-side form-side-label"><label for="{{id}}">{{label}}</label></div><div class="form-side"><input id="{{id}}" ng-model="model" type="number" min="0" class="form-control" ng-class="{\'invalid\' : !isValid }" placeholder="{{placeholder}}" /></div></div>';
    }
  };
});
zaa.directive("zaaDecimal", function () {
  return {
    restrict: "E",
    scope: {
      "model": "=",
      "options": "=",
      "label": "@label",
      "i18n": "@i18n",
      "id": "@fieldid",
      "placeholder": "@placeholder"
    },
    controller: ['$scope', function ($scope) {
      if ($scope.options === null) {
        $scope.steps = 0.01;
      } else {
        $scope.steps = $scope.options['steps'];
      }
    }],
    link: function link($scope) {
      $scope.$watch(function () {
        return $scope.model;
      }, function (n, o) {
        if (angular.isNumber($scope.model)) {
          $scope.isValid = true;
        } else {
          $scope.isValid = false;
        }
      });
    },
    template: function template() {
      return '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}"><div class="form-side form-side-label"><label for="{{id}}">{{label}}</label></div><div class="form-side"><input id="{{id}}" ng-model="model" type="number" min="0" step="{{steps}}" class="form-control" ng-class="{\'invalid\' : !isValid }" placeholder="{{placeholder}}" /></div></div>';
    }
  };
});
/**
 * <zaa-text model="itemCopy.title" label="<?= Module::t('view_index_page_title'); ?>"></zaa-text>
 */

zaa.directive("zaaText", function () {
  return {
    restrict: "E",
    scope: {
      "model": "=",
      "options": "=",
      "label": "@label",
      "i18n": "@i18n",
      "id": "@fieldid",
      "placeholder": "@placeholder",
      "autocomplete": "@autocomplete"
    },
    template: function template() {
      return '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}"><div class="form-side form-side-label"><label for="{{id}}">{{label}}</label></div><div class="form-side"><input id="{{id}}" insert-paste-listener ng-model="model" type="text" class="form-control" autocomplete="{{autocomplete}}" placeholder="{{placeholder}}" /></div></div>';
    }
  };
});
/**
 * Returns a field which just returns the value from model, like a read only attribute.
 *
 * @since 1.2.1
 */

zaa.directive("zaaReadonly", function () {
  return {
    restrict: "E",
    scope: {
      "model": "=",
      "label": "@label",
      "i18n": "@i18n"
    },
    template: function template() {
      return '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}"><div class="form-side form-side-label"><label>{{label}}</label></div><div class="form-side"><span class="text-muted">{{model}}</span></div></div>';
    }
  };
});
/**
 * <zaa-async-value model="theModel" label="Hello world" api="admin/admin-users" fields="[foo,bar]" />
 *
 * Generates a request to the corresponding model item view like the example above would request to:
 *
 * ```
 * /admin/admin-users/{model}?fields=foo,bar
 * ```
 *
 */

zaa.directive("zaaAsyncValue", function () {
  return {
    restrict: "E",
    scope: {
      "model": "=",
      "api": "@",
      "fields": "=",
      "label": "@label",
      "i18n": "@i18n",
      "id": "@fieldid"
    },
    controller: ['$scope', '$timeout', '$http', function ($scope, $timeout, $http) {
      $scope.resetValue = function () {
        $scope.model = 0;
        $scope.value = null;
      };
    }],
    template: function template() {
      return '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}"><div class="form-side form-side-label"><label for="{{id}}">{{label}}</label></div><div class="form-side"><async-value model="model" api="{{api}}" fields="fields"  ng-show="model"></async-value><button type="button" class="btn btn-icon btn-cancel" ng-click="resetValue()" ng-show="model"></button></div></div>';
    }
  };
});
/**
 * Can be used to just fetch a value from an api async.
 *
 * ```
 * <async-value model="theModel" api="admin/admin-users" fields="[foo,bar]"></async-value>
 * ```
 *
 * @since 1.2.2
 */

zaa.directive("asyncValue", function () {
  return {
    restrict: "E",
    scope: {
      "model": "=",
      "api": "@",
      "fields": "="
    },
    controller: ['$scope', '$timeout', '$http', function ($scope, $timeout, $http) {
      $timeout(function () {
        $scope.$watch('model', function (n, o) {
          if (n) {
            $scope.value = '';
            $http.get($scope.api + "/" + n + "?fields=" + $scope.fields.join()).then(function (response) {
              $scope.value;
              angular.forEach(response.data, function (value) {
                if (value) {
                  $scope.value = $scope.value + value + " ";
                }
              });
            });
          }
        });
      });
    }],
    template: function template() {
      return '<span ng-bind="value"></span>';
    }
  };
});
/**
 * Generate a textarea input.
 */

zaa.directive("zaaTextarea", function () {
  return {
    restrict: "E",
    scope: {
      "model": "=",
      "options": "=",
      "label": "@label",
      "i18n": "@i18n",
      "id": "@fieldid",
      "placeholder": "@placeholder"
    },
    template: function template() {
      return '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}"><div class="form-side form-side-label"><label for="{{id}}">{{label}}</label></div><div class="form-side"><textarea id="{{id}}" insert-paste-listener ng-model="model" type="text" class="form-control" auto-grow placeholder="{{placeholder}}"></textarea></div></div>';
    }
  };
});
/**
 * Generate a password input.
 */

zaa.directive("zaaPassword", function () {
  return {
    restrict: "E",
    scope: {
      "model": "=",
      "options": "=",
      "label": "@label",
      "i18n": "@i18n",
      "id": "@fieldid",
      "autocomplete": "@autocomplete"
    },
    template: function template() {
      return '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}"><div class="form-side form-side-label"><label for="{{id}}">{{label}}</label></div><div class="form-side"><input id="{{id}}" ng-model="model" type="password" class="form-control" autocomplete="{{autocomplete}}" placeholder="{{placeholder}}" /></div></div>';
    }
  };
});
/**
 * <zaa-radio model="model" options="[{label:'foo', value: 'bar'}, {...}]"></zaa-radio>
 */

zaa.directive("zaaRadio", function () {
  return {
    restrict: "E",
    scope: {
      "model": "=",
      "options": "=",
      "label": "@label",
      "i18n": "@i18n",
      "id": "@fieldid",
      "initvalue": "@initvalue"
    },
    controller: ['$scope', '$timeout', function ($scope, $timeout) {
      $scope.setModelValue = function (value) {
        $scope.model = value;
      };

      $scope.init = function () {
        if ($scope.model == undefined || $scope.model == null) {
          $scope.model = typeCastValue($scope.initvalue);
        }
      };

      $timeout(function () {
        $scope.init();
      });
    }],
    template: function template() {
      return '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' + '<div class="form-side form-side-label">' + '<label for="{{id}}">{{label}}</label>' + '</div>' + '<div class="form-side">' + '<div ng-repeat="(key, item) in options" class="form-check">' + '<input value="{{item.value}}" type="radio" ng-click="setModelValue(item.value)" ng-checked="item.value == model" name="{{id}}_{{key}}" class="form-check-input" id="{{id}}_{{key}}">' + '<label class="form-check-label" for="{{id}}_{{key}}">' + '{{item.label}}' + '</label>' + '</div>' + '</div>' + '</div>';
    }
  };
});
/**
 *
 * Usage Example:
 *
 * ```js
 * <zaa-select model="data.module_name" label="<?= Module::t('view_index_module_select'); ?>" options="modules" />
 * ```
 *
 * If an initvalue is provided, you can not reset the model to null.
 *
 * Options value definition:
 *
 * ```js
 * options=[{"value":123,"label":123-Label}, {"value":abc,"label":ABC-Label}]
 * ```
 *
 * In order to change the value and label keys which should be used to take the value and label keys within the given array use:
 *
 * ```js
 * <zaa-select model="create.fromVersionPageId" label="My Label" options="typeData" optionslabel="version_alias" optionsvalue="id"></zaa-select>
 * ```
 */

zaa.directive("zaaSelect", function () {
  return {
    restrict: "E",
    scope: {
      "model": "=",
      "options": "=",
      "optionsvalue": "@optionsvalue",
      "optionslabel": "@optionslabel",
      "label": "@label",
      "i18n": "@i18n",
      "id": "@fieldid",
      "initvalue": "@initvalue",
      "clearable": "<"
    },
    controller: ['$scope', '$timeout', '$rootScope', function ($scope, $timeout, $rootScope) {
      if ($scope.optionsvalue == undefined) {
        $scope.optionsvalue = 'value';
      }

      if ($scope.optionslabel == undefined) {
        $scope.optionslabel = 'label';
      }

      if ($scope.clearable == undefined) {
        $scope.clearable = true;
      }
    }],
    template: function template() {
      return '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' + '<div class="form-side form-side-label">' + '<label for="{{id}}">{{label}}</label>' + '</div>' + '<div class="form-side">' + '<luya-select ng-model="model" options="options" id="{{id}}" clearable="clearable" optionsvalue="{{optionsvalue}}" optionslabel="{{optionslabel}}" initvalue="{{initvalue}}"></luya-select>' + '</div>' + '</div>';
    }
  };
});
/**
 * Select form based on API Request
 */

zaa.directive("zaaAsyncApiSelect", function () {
  return {
    restrict: "E",
    scope: {
      "model": "=",
      "api": "@api",
      "optionsvalue": "@optionsvalue",
      "optionslabel": "@optionslabel",
      "label": "@label",
      "i18n": "@i18n",
      "id": "@fieldid",
      "initvalue": "@initvalue"
    },
    controller: ['$scope', '$http', '$timeout', function ($scope, $http, $timeout) {
      $scope.options = [];

      if ($scope.optionsvalue == undefined) {
        $scope.optionsvalue = 'id';
      }

      if ($scope.optionslabel == undefined) {
        $scope.optionslabel = 'title';
      }

      $scope.$watch('api', function (apiUrl) {
        $http.get(apiUrl).then(function (value) {
          var items = [];
          angular.forEach(value.data, function (item) {
            items.push({
              label: item[$scope.optionslabel],
              value: item[$scope.optionsvalue]
            });
          });
          $scope.options = items;
        });
      });
    }],
    template: function template() {
      return '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' + '<div class="form-side form-side-label">' + '<label for="{{id}}">{{label}}</label>' + '</div>' + '<div class="form-side">' + '<luya-select ng-model="model" options="options" id="{{id}}" initvalue="{{initvalue}}"></luya-select>' + '</div>' + '</div>';
    }
  };
});
/**
 * A selection based on a CRUD view.
 * 
 * <zaa-select-crud options={'route': 'module/controller/index', 'api':'admin/api-module-controller', 'fields':['id','title']}></zaa-select-crud>
 * 
 * @since 3.7.0
 */

zaa.directive("zaaSelectCrud", function () {
  return {
    restrict: "E",
    scope: {
      "model": "=",
      "options": "=",
      "api": "@api",
      "label": "@label",
      "i18n": "@i18n",
      "id": "@fieldid",
      "initvalue": "@initvalue"
    },
    template: function template() {
      return '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' + '<div class="form-side form-side-label">' + '<label for="{{id}}">{{label}}</label>' + '</div>' + '<div class="form-side">' + '<async-value model="model" api="{{options.api}}" fields="options.fields"></async-value>' + '<crud-loader api="{{options.route}}" model-setter="model" model-selection="1" alias="{{label}}"></crud-loader>' + '</div>' + '</div>';
    }
  };
});
zaa.directive("luyaSelect", function () {
  return {
    restrict: "E",
    scope: {
      "model": "=ngModel",
      "options": "=",
      "optionsvalue": "@optionsvalue",
      "optionslabel": "@optionslabel",
      "id": "@fieldid",
      "initvalue": "@initvalue",
      "clearable": "<",
      ngChange: "&"
    },
    controller: ['$scope', '$timeout', '$rootScope', function ($scope, $timeout, $rootScope) {
      $scope.isOpen = 0;

      if ($scope.optionsvalue == undefined || $scope.optionsvalue == "") {
        $scope.optionsvalue = 'value';
      }

      if ($scope.optionslabel == undefined || $scope.optionslabel == "") {
        $scope.optionslabel = 'label';
      }

      if (angular.isNumber($scope.model)) {
        $scope.model = typeCastValue($scope.model);
      }
      /* listeners */


      $scope.$on('closeAllSelects', function () {
        if ($scope.isOpen) {
          $scope.closeSelect();
        }
      });
      $timeout(function () {
        $scope.$watch(function () {
          return $scope.model;
        }, function (n, o) {
          if (n == undefined || n == null || n == '') {
            if (angular.isNumber($scope.initvalue)) {
              $scope.initvalue = typeCastValue($scope.initvalue);
            }

            var exists = $scope.valueExistsInOptions(n);

            if (!exists) {
              $scope.model = $scope.initvalue;
            }
          }
        });

        if ($scope.clearable == undefined) {
          $scope.clearable = true;
        }
      });
      /* methods */

      $scope.valueExistsInOptions = function (value) {
        var exists = false;
        angular.forEach($scope.options, function (item) {
          if (value == item[$scope.optionsvalue]) {
            exists = true;
          }
        });
        return exists;
      };

      $scope.toggleIsOpen = function () {
        if (!$scope.isOpen) {
          $rootScope.$broadcast('closeAllSelects');
        }

        $scope.isOpen = !$scope.isOpen;
      };

      $scope.closeSelect = function () {
        $scope.isOpen = 0;
      };

      $scope.setModelValue = function (option) {
        $scope.model = angular.isObject(option) ? option[$scope.optionsvalue] : option;
        $timeout($scope.ngChange, 0);
        $scope.closeSelect();
      };

      $scope.getSelectedLabel = function () {
        var defaultLabel = i18n['ngrest_select_no_selection'];
        angular.forEach($scope.options, function (item) {
          if ($scope.model == item[$scope.optionsvalue]) {
            defaultLabel = item[$scope.optionslabel];
          }
        });
        return defaultLabel;
      };

      $scope.hasSelectedValue = function () {
        var modelValue = $scope.model;

        if ($scope.valueExistsInOptions(modelValue) && modelValue != $scope.initvalue) {
          return true;
        }

        return false;
      };
    }],
    template: function template() {
      return '<div class="zaaselect" ng-class="{\'open\':isOpen, \'selected\':hasSelectedValue()}">' + '<select class="zaaselect-select" ng-model="model">' + '<option ng-repeat="opt in options" ng-value="opt[optionsvalue]">{{opt[optionslabel]}}</option>' + '</select>' + '<div class="zaaselect-selected">' + '<span class="zaaselect-selected-text" ng-click="toggleIsOpen()">{{getSelectedLabel()}}</span>' + '<i class="material-icons zaaselect-clear-icon" ng-show="clearable" ng-click="setModelValue(initvalue)">clear</i>' + '<i class="material-icons zaaselect-dropdown-icon" ng-click="toggleIsOpen()">keyboard_arrow_down</i>' + '</div>' + '<div class="zaaselect-dropdown">' + '<div class="zaaselect-search">' + '<input class="zaaselect-search-input" type="search" focus-me="isOpen" ng-model="searchQuery" />' + '</div>' + '<div class="zaaselect-overflow" ng-if="isOpen">' + '<div class="zaaselect-item" ng-repeat="opt in options | filter:searchQuery">' + '<span class="zaaselect-label" ng-class="{\'zaaselect-label-active\': opt[optionsvalue] == model}" ng-click="opt[optionsvalue] == model ? false : setModelValue(opt)">{{opt[optionslabel]}}</span>' + '</div>' + '</div>' + '</div>' + '</div>';
    }
  };
});
/**
 * options = {'true-value' : 1, 'false-value' : 0};
 */

zaa.directive("zaaCheckbox", function () {
  return {
    restrict: "E",
    scope: {
      "model": "=",
      "options": "=",
      "i18n": "@i18n",
      "id": "@fieldid",
      "label": "@label",
      "initvalue": "@initvalue"
    },
    controller: ['$scope', '$timeout', function ($scope, $timeout) {
      if ($scope.options === null || $scope.options === undefined) {
        $scope.valueTrue = 1;
        $scope.valueFalse = 0;
      } else {
        $scope.valueTrue = $scope.options['true-value'];
        $scope.valueFalse = $scope.options['false-value'];
      }

      $scope.init = function () {
        if ($scope.model == undefined || $scope.model == null) {
          $scope.model = typeCastValue($scope.initvalue);
        }
      };

      $timeout(function () {
        $scope.init();
      });

      $scope.clicker = function () {
        if ($scope.model == $scope.valueTrue) {
          $scope.model = $scope.valueFalse;
        } else {
          $scope.model = $scope.valueTrue;
        }
      };
    }],
    template: function template() {
      return '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' + '<div class="form-side form-side-label">' + '<label for="{{id}}">{{label}}</label>' + '</div>' + '<div class="form-side">' + '<div class="form-check">' + '<input id="{{id}}" ng-true-value="{{valueTrue}}" ng-change="change()" ng-click="clicker()" ng-false-value="{{valueFalse}}" ng-model="model" type="checkbox" class="form-check-input-standalone" ng-checked="model == valueTrue" />' + '<label for="{{id}}"></label>' + '</div>' + '</div>' + '</div>';
    }
  };
});
/**
 * options arg object:
 *
 * options.items[] = [{"value" : 1, "label" => 'Label for Value 1' }]
 *
 * @param preselect boolean if enable all models will be selected by default.
 */

zaa.directive("zaaCheckboxArray", function () {
  return {
    restrict: "E",
    scope: {
      "model": "=",
      "options": "=",
      "i18n": "@i18n",
      "id": "@fieldid",
      "label": "@label",
      "preselect": "@preselect"
    },
    controller: ['$scope', '$filter', function ($scope, $filter) {
      if ($scope.model == undefined) {
        $scope.model = [];
      }

      $scope.preselectOptionValuesToModel = function (options) {
        angular.forEach(options, function (value) {
          $scope.model.push({
            'value': value.value
          });
        });
      };

      $scope.searchString = '';
      $scope.$watch('options', function (n, o) {
        if (n != undefined && n.hasOwnProperty('items')) {
          $scope.optionitems = $filter('orderBy')(n.items, 'label');

          if ($scope.preselect) {
            $scope.preselectOptionValuesToModel(n.items);
          }
        }
      });

      $scope.filtering = function () {
        $scope.optionitems = $filter('filter')($scope.options.items, $scope.searchString);
      };

      $scope.toggleSelection = function (value) {
        if ($scope.model == undefined) {
          $scope.model = [];
        }

        for (var i in $scope.model) {
          if ($scope.model[i]["value"] == value.value) {
            $scope.model.splice(i, 1);
            return;
          }
        }

        $scope.model.push({
          'value': value.value
        });
      };

      $scope.isChecked = function (item) {
        for (var i in $scope.model) {
          if ($scope.model[i]["value"] == item.value) {
            return true;
          }
        }

        return false;
      };
    }],
    link: function link(scope) {
      scope.random = Math.random().toString(36).substring(7);
    },
    template: function template() {
      return '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' + '<div class="form-side form-side-label">' + '<label for="{{id}}">{{label}}</label>' + '</div>' + '<div class="form-side">' + '<div class="position-relative mb-3">' + '<div class="input-group">' + '<div class="input-group-prepend">' + '<div class="input-group-text">' + '<i class="material-icons">search</i>' + '</div>' + '</div>' + '<input class="form-control" type="text" ng-change="filtering()" ng-model="searchString" placeholder="' + i18n['ngrest_crud_search_text'] + '">' + '</div>' + '<span class="zaa-checkbox-array-counter badge badge-secondary">{{optionitems.length}} ' + i18n['js_dir_till'] + ' {{options.items.length}}</span>' + '</div>' + '<div class="form-check" ng-repeat="(k, item) in optionitems track by k">' + '<input type="checkbox" class="form-check-input" ng-checked="isChecked(item)" id="{{random}}_{{k}}" ng-click="toggleSelection(item)" />' + '<label for="{{random}}_{{k}}">{{item.label}}</label>' + '</div>' + '</div>' + '</div>';
    }
  };
});
/**
 * https://github.com/720kb/angular-datepicker#date-validation - Date Picker
 * http://jsfiddle.net/bateast/Q6py9/1/ - Date Parse
 * https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Date - Date Objects
 * https://docs.angularjs.org/api/ng/filter/date - Angular Date Filter
 *
 * resetable: 1/0, This will enable or disable the ability to press the reset (set to null) button. use integer value
 */

zaa.directive("zaaDatetime", function () {
  return {
    restrict: "E",
    scope: {
      "model": "=",
      "options": "=",
      "label": "@label",
      "id": "@fieldid",
      "i18n": "@i18n",
      "resetable": "@resetable"
    },
    controller: ['$scope', '$filter', function ($scope, $filter) {
      $scope.isNumeric = function (num) {
        return !isNaN(num);
      };

      $scope.$watch(function () {
        return $scope.model;
      }, function (n, o) {
        if (n != null && n != undefined) {
          var datep = new Date(n * 1000);
          $scope.pickerPreselect = datep;
          $scope.date = $filter('date')(datep, 'dd.MM.yyyy');
          $scope.hour = $filter('date')(datep, 'HH');
          $scope.min = $filter('date')(datep, 'mm');
        } else {
          $scope.date = null;
          $scope.model = null;
        }
      });

      $scope.refactor = function (n) {
        if (!$scope.isNumeric($scope.hour) || $scope.hour == '') {
          $scope.hour = "0";
        }

        if (!$scope.isNumeric($scope.min) || $scope.min == '') {
          $scope.min = "0";
        }

        if (n == 'Invalid Date' || n == "" || n == 'NaN') {
          $scope.date = null;
          $scope.model = null;
        } else {
          var res = n.split(".");

          if (res.length == 3) {
            if (res[2].length == 4) {
              if (parseInt($scope.hour) > 23) {
                $scope.hour = 23;
              }

              if (parseInt($scope.min) > 59) {
                $scope.min = 59;
              }

              var en = res[1] + "/" + res[0] + "/" + res[2] + " " + $scope.hour + ":" + $scope.min;
              $scope.model = Date.parse(en) / 1000;
              $scope.datePickerToggler = false;
            }
          }
        }
      };

      $scope.$watch(function () {
        return $scope.date;
      }, function (n, o) {
        if (n != o && n != undefined && n != null) {
          $scope.refactor(n);
        }
      });

      $scope.autoRefactor = function () {
        $scope.refactor($scope.date);
      };

      $scope.datePickerToggler = false;

      $scope.toggleDatePicker = function () {
        $scope.datePickerToggler = !$scope.datePickerToggler;
      };

      $scope.openDatePicker = function () {
        $scope.datePickerToggler = true;
      };

      $scope.closeDatePicker = function () {
        $scope.datePickerToggler = false;
      };

      $scope.hour = "0";
      $scope.min = "0";

      $scope.reset = function () {
        $scope.model = null;
      };

      $scope.getIsResetable = function () {
        if ($scope.resetable) {
          return parseInt($scope.resetable);
        }

        return true;
      };
    }],
    template: function template() {
      return '<div class="form-group form-side-by-side zaa-datetime" ng-class="{\'input--hide-label\': i18n, \'input--with-time\': model!=null && date!=null}">' + '<div class="form-side form-side-label">' + '<label>{{label}}</label>' + '</div>' + '<div class="form-side form-inline datepicker-wrapper">' + '<datepicker class="input-group input-group--append-clickable" date-set="{{pickerPreselect.toString()}}" date-week-start-day="1" datepicker-toggle="false" datepicker-show="{{datePickerToggler}}" date-format="dd.MM.yyyy">' + '<input class="form-control datepicker-date-input" ng-model="date" type="text" ng-focus="openDatePicker()" />' + '<div class="input-group-append" ng-click="toggleDatePicker()">' + '<div class="input-group-text">' + '<i class="material-icons" ng-hide="datePickerToggler">date_range</i>' + '<i class="material-icons" ng-show="datePickerToggler">close</i>' + '</div>' + '</div>' + '</datepicker>' + '<div ng-show="model!=null && date!=null" class="hour-selection">' + '<div class="input-group">' + '<input class="form-control zaa-datetime-hour-input" type="text" ng-model="hour" ng-change="autoRefactor()" />' + '</div>' + '<div class="input-group">' + '<div class="input-group-prepend zaa-datetime-time-colon">' + '<div class="input-group-text">:</div>' + '</div>' + '<input class="form-control form-control--force-border zaa-datetime-minute-input" type="text" ng-model="min" ng-change="autoRefactor()" />' + '</div>' + '</div>' + '<div ng-show="model && getIsResetable()"><button type="button" ng-click="reset()" class="ml-2 btn btn-icon btn-cancel"></nutton></div>' + '</div>' + '</div>';
    }
  };
});
/**
 * resetable: whether rest button is enabled or not.
 */

zaa.directive("zaaDate", function () {
  return {
    restrict: "E",
    scope: {
      "model": "=",
      "options": "=",
      "label": "@label",
      "id": "@fieldid",
      "i18n": "@i18n",
      "resetable": "@resetable"
    },
    controller: ['$scope', '$filter', function ($scope, $filter) {
      $scope.$watch(function () {
        return $scope.model;
      }, function (n, o) {
        if (n != null && n != undefined) {
          var datep = new Date(n * 1000);
          $scope.pickerPreselect = datep;
          $scope.date = $filter('date')(datep, 'dd.MM.yyyy');
        } else {
          $scope.date = null;
          $scope.model = null;
        }
      });

      $scope.refactor = function (n) {
        if (n == 'Invalid Date' || n == "") {
          $scope.date = null;
          $scope.model = null;
        } else {
          var res = n.split(".");

          if (res.length == 3) {
            if (res[2].length == 4) {
              var en = res[1] + "/" + res[0] + "/" + res[2];
              $scope.model = Date.parse(en) / 1000;
              $scope.datePickerToggler = false;
            }
          }
        }
      };

      $scope.$watch(function () {
        return $scope.date;
      }, function (n, o) {
        if (n != o && n != undefined && n != null) {
          $scope.refactor(n);
        }
      });

      $scope.autoRefactor = function () {
        $scope.refactor($scope.date);
      };

      $scope.datePickerToggler = false;

      $scope.toggleDatePicker = function () {
        $scope.datePickerToggler = !$scope.datePickerToggler;
      };

      $scope.openDatePicker = function () {
        $scope.datePickerToggler = true;
      };

      $scope.closeDatePicker = function () {
        $scope.datePickerToggler = false;
      };

      $scope.reset = function () {
        $scope.model = null;
      };

      $scope.getIsResetable = function () {
        if ($scope.resetable) {
          return parseInt($scope.resetable);
        }

        return true;
      };
    }],
    template: function template() {
      return '<div class="form-group form-side-by-side zaa-date" ng-class="{\'input--hide-label\': i18n}">' + '<div class="form-side form-side-label">' + '<label>{{label}}</label>' + '</div>' + '<div class="form-side form-inline datepicker-wrapper">' + '<datepicker class="input-group input-group--append-clickable" date-set="{{pickerPreselect.toString()}}" date-week-start-day="1" datepicker-toggle="false" datepicker-show="{{datePickerToggler}}" date-format="dd.MM.yyyy">' + '<input class="form-control datepicker-date-input" ng-model="date" type="text" ng-focus="openDatePicker()" />' + '<div class="input-group-append" ng-click="toggleDatePicker()">' + '<div class="input-group-text">' + '<i class="material-icons" ng-hide="datePickerToggler">date_range</i>' + '<i class="material-icons" ng-show="datePickerToggler">close</i>' + '</div>' + '</div>' + '</datepicker>' + '<div ng-show="model && getIsResetable()"><button type="button" ng-click="reset()" class="ml-2 btn btn-icon btn-cancel"></nutton></div>' + '</div>' + '</div>';
    }
  };
});
zaa.directive("zaaTable", function () {
  return {
    restrict: "E",
    scope: {
      "model": "=",
      "options": "=",
      "label": "@label",
      "i18n": "@i18n",
      "id": "@fieldid"
    },
    controller: ['$scope', function ($scope) {
      if ($scope.model == undefined) {
        $scope.model = [{
          0: ''
        }];
      }

      $scope.addColumn = function () {
        var len = 0;

        for (var o in $scope.model[0]) {
          len++;
        }

        for (var i in $scope.model) {
          $scope.model[i][len] = '';
        }
      };

      $scope.addRow = function () {
        var elmn = $scope.model[0];
        var ins = {};

        for (var i in elmn) {
          ins[i] = '';
        }

        $scope.model.push(ins);
      };

      $scope.removeColumn = function (key) {
        for (var i in $scope.model) {
          var item = $scope.model[i];

          if (item instanceof Array) {
            item.splice(key, 1);
          } else {
            delete item[key];
          }
        }
      };

      $scope.moveLeft = function (index) {
        index = parseInt(index);

        for (var i in $scope.model) {
          var oldValue = $scope.model[i][index];
          $scope.model[i][index] = $scope.model[i][index - 1];
          $scope.model[i][index - 1] = oldValue;
        }
      };

      $scope.moveRight = function (index) {
        index = parseInt(index);

        for (var i in $scope.model) {
          var oldValue = $scope.model[i][index];
          $scope.model[i][index] = $scope.model[i][index + 1];
          $scope.model[i][index + 1] = oldValue;
        }
      };

      $scope.moveUp = function (index) {
        index = parseInt(index);
        var oldRow = $scope.model[index];
        $scope.model[index] = $scope.model[index - 1];
        $scope.model[index - 1] = oldRow;
      };

      $scope.moveDown = function (index) {
        index = parseInt(index);
        var oldRow = $scope.model[index];
        $scope.model[index] = $scope.model[index + 1];
        $scope.model[index + 1] = oldRow;
      };

      $scope.removeRow = function (key) {
        $scope.model.splice(key, 1);
      };

      $scope.showRightButton = function (index) {
        if (parseInt(index) < Object.keys($scope.model[0]).length - 1) {
          return true;
        }

        return false;
      };

      $scope.showDownButton = function (index) {
        if (parseInt(index) < Object.keys($scope.model).length - 1) {
          return true;
        }

        return false;
      };
    }],
    template: function template() {
      return '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' + '<div class="form-side form-side-label">' + '<label ng-if="label">{{label}}</label>' + '<label ng-if="!label">Table</label>' + '</div>' + '<div class="form-side">' + '<div class="zaa-table-wrapper">' + '<table class="zaa-table table table-bordered">' + '<tbody>' + '<tr>' + '<th scope="col" width="35px"></th>' + '<th scope="col" data-ng-repeat="(hk, hr) in model[0] track by hk" class="zaa-table-buttons">' + '<div class="btn-group" role="group">' + '<button type="button" class="btn btn-sm btn-outline-info" ng-click="moveLeft(hk)" ng-if="hk > 0"><i class="material-icons">keyboard_arrow_left</i></button>' + '<button type="button" class="btn btn-sm btn-outline-info" ng-click="moveRight(hk)" ng-if="showRightButton(hk)"><i class="material-icons">keyboard_arrow_right</i></button>' + '<button type="button" class="btn btn-sm btn-outline-danger" ng-click="removeColumn(hk)"><i class="material-icons">remove</i></button>' + '</div>' + '</th>' + '</tr>' + '<tr data-ng-repeat="(key, row) in model track by key">' + '<td width="35px" scope="row" class="zaa-table-buttons">' + '<div class="btn-group-vertical" role="group">' + '<button type="button" class="btn btn-sm btn-outline-info" ng-click="moveUp(key)" ng-if="key > 0"><i class="material-icons">keyboard_arrow_up</i></button>' + '<button type="button" class="btn btn-sm btn-outline-info" ng-click="moveDown(key)" ng-if="showDownButton(key)"><i class="material-icons">keyboard_arrow_down</i></button>' + '<button type="button" class="btn btn-sm btn-outline-danger" ng-click="removeRow(key)"><i class="material-icons">remove</i></button>' + '</div>' + '</td>' + '<td data-ng-repeat="(field,value) in row track by field">' + '<textarea ng-model="model[key][field]" class="zaa-table__textarea"></textarea>' + '</td>' + '</tr>' + '</tbody>' + '</table>' + '<button ng-click="addRow()" type="button" class="zaa-table-add-row btn btn-sm btn-success"><i class="material-icons">add</i></button>' + '<button ng-click="addColumn()" type="button" class="zaa-table-add-column btn btn-sm btn-success"><i class="material-icons">add</i></button>' + '</div>' + '</div>' + '</div>';
    }
  };
});
zaa.directive("zaaFileUpload", function () {
  return {
    restrict: "E",
    scope: {
      "model": "=",
      "options": "=",
      "label": "@label",
      "i18n": "@i18n",
      "id": "@fieldid"
    },
    template: function template() {
      return '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' + '<div class="form-side form-side-label">' + '<label>{{label}}</label>' + '</div>' + '<div class="form-side">' + '<storage-file-upload ng-model="model"></storage-file-upload>' + '</div>' + '</div>';
    }
  };
});
zaa.directive("zaaImageUpload", function () {
  return {
    restrict: "E",
    scope: {
      "model": "=",
      "options": "=",
      "label": "@label",
      "i18n": "@i18n",
      "id": "@fieldid"
    },
    template: function template() {
      return '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' + '<div class="form-side form-side-label">' + '<label>{{label}}</label>' + '</div>' + '<div class="form-side">' + '<storage-image-upload options="options" ng-model="model"></storage-image-upload>' + '</div>' + '</div>';
    }
  };
});
/**
 * options: {
 *     description: true/false,
 *     filter: true/false
 * }
 */

zaa.directive("zaaImageArrayUpload", function () {
  return {
    restrict: "E",
    scope: {
      "model": "=",
      "options": "=",
      "label": "@label",
      "i18n": "@i18n",
      "id": "@fieldid"
    },
    link: function link(scope, element, attributes) {
      scope.$watch('model', function (newValue, oldValue) {
        if (newValue.length >= 1) {
          $(element).removeClass('is-empty').addClass('is-not-empty');
        } else {
          $(element).removeClass('is-not-empty').addClass('is-empty');
        }
      }, true);
    },
    controller: ['$scope', function ($scope) {
      if ($scope.model == undefined) {
        $scope.model = [];
      }

      $scope.add = function () {
        if ($scope.model == null || $scope.model == '' || $scope.model == undefined) {
          $scope.model = [];
        }

        $scope.model.push({
          imageId: 0,
          caption: ''
        });
      };

      $scope.remove = function (key) {
        $scope.model.splice(key, 1);
      };

      $scope.moveUp = function (index) {
        index = parseInt(index);
        var oldRow = $scope.model[index];
        $scope.model[index] = $scope.model[index - 1];
        $scope.model[index - 1] = oldRow;
      };

      $scope.moveDown = function (index) {
        index = parseInt(index);
        var oldRow = $scope.model[index];
        $scope.model[index] = $scope.model[index + 1];
        $scope.model[index + 1] = oldRow;
      };

      $scope.showDownButton = function (index) {
        if (parseInt(index) < Object.keys($scope.model).length - 1) {
          return true;
        }

        return false;
      };

      $scope.isDescriptionEnabled = function () {
        if ($scope.options && $scope.options.hasOwnProperty('description')) {
          return $scope.options.description;
        }

        return true;
      };

      $scope.noFiltersOption = function () {
        if ($scope.options && $scope.options.hasOwnProperty('filter')) {
          return !$scope.options.filter;
        }

        return false;
      };
    }],
    template: function template() {
      return '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' + '<div class="form-side form-side-label">' + '<label>{{label}}</label>' + '</div>' + '<div class="form-side">' + '<div class="list zaa-file-array-upload">' + '<p class="alert alert-info" ng-hide="model.length > 0">' + i18n['js_dir_no_selection'] + '</p>' + '<div ng-repeat="(key,image) in model track by key" class="list-item">' + '<div class="list-section">' + '<div class="list-left">' + '<storage-image-upload ng-model="image.imageId" options="{no_filter: noFiltersOption()}"></storage-image-upload>' + '</div>' + '<div class="list-right" ng-show="isDescriptionEnabled()">' + '<div class="form-group">' + '<label for="{{image.id}}">' + i18n['js_dir_image_description'] + '</label>' + '<textarea ng-model="image.caption" id="{{image.id}}" class="zaa-file-array-upload-description form-control" auto-grow></textarea>' + '</div>' + '</div>' + '</div>' + '<div class="list-buttons">' + '<div class="btn-group" role="group">' + '<button type="button" class="btn btn-sm btn-outline-info" ng-click="moveUp(key)" ng-if="key > 0"><i class="material-icons">keyboard_arrow_up</i></button>' + '<button type="button" class="btn btn-sm btn-outline-info" ng-click="moveDown(key)" ng-if="showDownButton(key)"><i class="material-icons">keyboard_arrow_down</i></button>' + '<button type="button" class="btn btn-sm btn-outline-danger" ng-click="remove(key)"><i class="material-icons">remove</i></button>' + '</div>' + '</div>' + '</div>' + '<button ng-click="add()" type="button" class="btn btn-sm btn-success list-add-button"><i class="material-icons">add</i></button>' + '</div>' + '</div>' + '</div>';
    }
  };
});
/**
 * Multiple selection of files.
 */

zaa.directive("zaaFileArrayUpload", function () {
  return {
    restrict: "E",
    scope: {
      "model": "=",
      "options": "=",
      "label": "@label",
      "i18n": "@i18n",
      "id": "@fieldid"
    },
    controller: ['$scope', '$element', '$timeout', function ($scope, $element, $timeout) {
      if ($scope.model == undefined) {
        $scope.model = [];
      }

      $scope.add = function () {
        if ($scope.model == null || $scope.model == '' || $scope.model == undefined) {
          $scope.model = [];
        }

        $scope.model.push({
          fileId: 0,
          caption: ''
        });
      };

      $scope.remove = function (key) {
        $scope.model.splice(key, 1);
      };

      $scope.moveUp = function (index) {
        index = parseInt(index);
        var oldRow = $scope.model[index];
        $scope.model[index] = $scope.model[index - 1];
        $scope.model[index - 1] = oldRow;
      };

      $scope.moveDown = function (index) {
        index = parseInt(index);
        var oldRow = $scope.model[index];
        $scope.model[index] = $scope.model[index + 1];
        $scope.model[index + 1] = oldRow;
      };

      $scope.showDownButton = function (index) {
        if (parseInt(index) < Object.keys($scope.model).length - 1) {
          return true;
        }

        return false;
      };
    }],
    template: function template() {
      return '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' + '<div class="form-side form-side-label">' + '<label>{{label}}</label>' + '</div>' + '<div class="form-side">' + '<div class="list zaa-file-array-upload">' + '<p class="alert alert-info" ng-hide="model.length > 0">' + i18n['js_dir_no_selection'] + '</p>' + '<div ng-repeat="(key,file) in model track by key" class="list-item">' + '<div class="list-section" ng-if="file.hiddenStorageUploadSource">' + '<a ng-href="{{file.hiddenStorageUploadSource}}" target="_blank" class="btn btn-primary">{{file.hiddenStorageUploadName}}</a>' + '</div>' + '<div class="list-section" ng-if="!file.hiddenStorageUploadSource">' + '<div class="list-left">' + '<storage-file-upload ng-model="file.fileId"></storage-file-upload>' + '</div>' + '<div class="list-right">' + '<div class="form-group">' + '<label for="{{file.id}}">' + i18n['js_dir_image_description'] + '</label>' + '<textarea ng-model="file.caption" id="{{file.id}}" class="zaa-file-array-upload-description form-control" auto-grow></textarea>' + '</div>' + '</div>' + '</div>' + '<div class="list-buttons"  ng-if="!file.hiddenStorageUploadSource">' + '<div class="btn-group" role="group">' + '<button type="button" class="btn btn-sm btn-outline-info" ng-click="moveUp(key)" ng-if="key > 0"><i class="material-icons">keyboard_arrow_up</i></button>' + '<button type="button" class="btn btn-sm btn-outline-info" ng-click="moveDown(key)" ng-if="showDownButton(key)"><i class="material-icons">keyboard_arrow_down</i></button>' + '<button type="button" class="btn btn-sm btn-outline-danger" ng-click="remove(key)"><i class="material-icons">remove</i></button>' + '</div>' + '</div>' + '</div>' + '</div>' + '<button ng-click="add()" type="button" class="btn btn-sm btn-success list-add-button"><i class="material-icons">add</i></button>' + '</div>' + '</div>';
    }
  };
});
/**
 * Generates an array where each array element can contain another directive from zaa types.
 *
 * @retunr array
 */

zaa.directive("zaaMultipleInputs", function () {
  return {
    restrict: "E",
    scope: {
      "model": "=",
      "options": "=",
      "label": "@label",
      "i18n": "@i18n",
      "id": "@fieldid"
    },
    controller: ['$scope', '$timeout', function ($scope, $timeout) {
      $scope.init = function () {
        if ($scope.model == undefined || $scope.model == null) {
          $scope.model = [];
        } else {
          angular.forEach($scope.model, function (value, key) {
            var len = Object.keys(value).length;
            /* issue #1519: if there are no keys, ensure the item is an object */

            if (len == 0) {
              $scope.model[key] = {};
            }
          });
        }
      };

      $scope.add = function () {
        if ($scope.model == null || $scope.model == '' || $scope.model == undefined) {
          $scope.model = [];
        }

        $scope.model.push({});
      };

      $scope.remove = function (key) {
        $scope.model.splice(key, 1);
      };

      $scope.moveUp = function (index) {
        index = parseInt(index);
        var oldRow = $scope.model[index];
        $scope.model[index] = $scope.model[index - 1];
        $scope.model[index - 1] = oldRow;
      };

      $scope.moveDown = function (index) {
        index = parseInt(index);
        var oldRow = $scope.model[index];
        $scope.model[index] = $scope.model[index + 1];
        $scope.model[index + 1] = oldRow;
      };

      $scope.showDownButton = function (index) {
        return parseInt(index) < Object.keys($scope.model).length - 1;
      };

      $timeout(function () {
        $scope.init();
      });
    }],
    template: function template() {
      return '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' + '<div class="form-side form-side-label">' + '<label>{{label}}</label>' + '</div>' + '<div class="form-side">' + '<div class="list zaa-multiple-inputs">' + '<p class="alert alert-info" ng-hide="model.length > 0">' + i18n['js_dir_no_selection'] + '</p>' + '<div ng-repeat="(msortKey,row) in model track by msortKey" class="list-item" ng-init="ensureRow(row)">' + '<div ng-repeat="(mutliOptKey,opt) in options track by mutliOptKey">' + '<zaa-injector dir="opt.type" options="opt.options" fieldid="id-{{msortKey}}-{{mutliOptKey}}" initvalue="{{opt.initvalue}}" label="{{opt.label}}" model="row[opt.var]"></zaa-injector>' + '</div>' + '<div class="list-buttons">' + '<div class="btn-group" role="group">' + '<button type="button" class="btn btn-sm btn-outline-info" ng-click="moveUp(msortKey)" ng-if="msortKey > 0"><i class="material-icons">keyboard_arrow_up</i></button>' + '<button type="button" class="btn btn-sm btn-outline-info" ng-click="moveDown(msortKey)" ng-if="showDownButton(msortKey)"><i class="material-icons">keyboard_arrow_down</i></button>' + '<button type="button" class="btn btn-sm btn-outline-danger" ng-click="remove(msortKey)"><i class="material-icons">remove</i></button>' + '</div>' + '</div>' + '</div>' + '<button ng-click="add()" type="button" class="btn btn-sm btn-success list-add-button"><i class="material-icons">add</i></button>' + '</div>' + '</div>' + '</div>';
    }
  };
});
/**
 * Generates a json OBJECT (!) with a key and a value for the given key. Its like a flat json.
 *
 * ```js
 * <zaa-json-object model="mymodel" label="Key Value Input"></zaa-json-object>
 * ```
 * @since 2.0.3
 */

zaa.directive("zaaJsonObject", function () {
  return {
    restrict: "E",
    scope: {
      "model": "=",
      "options": "=",
      "label": "@label",
      "i18n": "@i18n",
      "id": "@fieldid"
    },
    controller: ['$scope', function ($scope) {
      $scope.$watch('model', function (n) {
        if (angular.isArray(n)) {
          $scope.model = {};
        }

        if (n === undefined || n === null) {
          $scope.model = {};
        }
      });

      $scope.add = function (key) {
        $scope.model[key] = '';
      };

      $scope.remove = function (key) {
        delete $scope.model[key];
      };
    }],
    template: function template() {
      return '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' + '<div class="form-side form-side-label">' + '<label>{{label}}</label>' + '</div>' + '<div class="form-side">' + '<div class="list zaa-json-array">' + '<div ng-repeat="(key,value) in model" class="list-item">' + '<div class="input-group">' + '<div class="input-group-prepend border-right">' + '<div class="input-group-text text-muted">{{key}}</div>' + '</div>' + '<input class="form-control" type="text" ng-model="model[key]" />' + '</div>' + '<div class="list-buttons">' + '<div class="btn-group" role="group">' + '<button type="button" class="btn btn-sm btn-outline-danger" ng-click="remove(key)"><i class="material-icons">remove</i></button>' + '</div>' + '</div>' + '</div>' + '<div class="input-group input-group--append-clickable">' + '<input type="text" class="form-control" placeholder="' + i18n['js_jsonobject_newkey'] + '" aria-label="' + i18n['js_jsonobject_newkey'] + '" ng-model="newKey">' + '<div class="input-group-append">' + '<div class="input-group-text" ng-click="add(newKey);newKey=null;"><i class="material-icons">add</i></div>' + '</div>' + '</div>' + '</div>' + '</div>' + '</div>';
    }
  };
});
zaa.directive("zaaListArray", function () {
  return {
    restrict: "E",
    scope: {
      "model": "=",
      "options": "=",
      "label": "@label",
      "i18n": "@i18n",
      "id": "@fieldid"
    },
    controller: ['$scope', '$element', '$timeout', function ($scope, $element, $timeout) {
      $scope.init = function () {
        if ($scope.model == undefined || $scope.model == null) {
          $scope.model = [];
        }
      };

      $scope.add = function () {
        if ($scope.model == null || $scope.model == '' || $scope.model == undefined) {
          $scope.model = [];
        }

        $scope.model.push({
          value: ''
        });
        $scope.setFocus();
      };

      $scope.remove = function (key) {
        $scope.model.splice(key, 1);
      };

      $scope.refactor = function (key, row) {
        if (key !== $scope.model.length - 1) {
          if (row['value'] == "") {
            $scope.remove(key);
          }
        }
      };

      $scope.setFocus = function () {
        $timeout(function () {
          var input = $element.children('.list').children('.list__item:last-of-type').children('.list__left').children('input');

          if (input.length == 1) {
            input[0].focus();
          }
        }, 50);
      };

      $scope.moveUp = function (index) {
        index = parseInt(index);
        var oldRow = $scope.model[index];
        $scope.model[index] = $scope.model[index - 1];
        $scope.model[index - 1] = oldRow;
      };

      $scope.moveDown = function (index) {
        index = parseInt(index);
        var oldRow = $scope.model[index];
        $scope.model[index] = $scope.model[index + 1];
        $scope.model[index + 1] = oldRow;
      };

      $scope.showDownButton = function (index) {
        if (parseInt(index) < Object.keys($scope.model).length - 1) {
          return true;
        }

        return false;
      };

      $scope.init();
    }],
    template: function template() {
      return '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' + '<div class="form-side form-side-label">' + '<label>{{label}}</label>' + '</div>' + '<div class="form-side">' + '<div class="list zaa-file-array-upload">' + '<p class="alert alert-info" ng-hide="model.length > 0">' + i18n['js_dir_no_selection'] + '</p>' + '<div ng-repeat="(key,row) in model track by key" class="list-item">' + '<input class="form-control list-input" type="text" ng-model="row.value" />' + '<div class="list-buttons">' + '<div class="btn-group" role="group">' + '<button type="button" class="btn btn-sm btn-outline-info" ng-click="moveUp(key)" ng-if="key > 0"><i class="material-icons">keyboard_arrow_up</i></button>' + '<button type="button" class="btn btn-sm btn-outline-info" ng-click="moveDown(key)" ng-if="showDownButton(key)"><i class="material-icons">keyboard_arrow_down</i></button>' + '<button type="button" class="btn btn-sm btn-outline-danger" ng-click="remove(key)"><i class="material-icons">remove</i></button>' + '</div>' + '</div>' + '</div>' + '<button ng-click="add()" type="button" class="btn btn-sm btn-success list-add-button"><i class="material-icons">add</i></button>' + '</div>' + '</div>' + '</div>';
    }
  };
}); // storage.js

zaa.directive('storageFileDisplay', function () {
  return {
    restrict: 'E',
    scope: {
      fileId: '@fileId'
    },
    controller: ['$scope', '$filter', 'ServiceFilesData', function ($scope, $filter, ServiceFilesData) {
      // controller
      $scope.fileId = 0;
      $scope.fileinfo = null;
      $scope.$watch('fileId', function (n, o) {
        if (n == 0 || n == null || n == undefined) {
          return;
        }

        ServiceFilesData.getFile(n).then(function (file) {
          $scope.fileinfo = file;
        }, function () {
          $scope.fileinfo = null;
        });
      });
    }],
    template: function template() {
      return '<a ng-show="fileinfo" href="{{ fileinfo.source }}" target="_blank">{{ fileinfo.name_original }}</a>';
    }
  };
});
zaa.directive('storageImageCrudList', function () {
  return {
    restrict: 'E',
    scope: {
      imageId: '@imageId'
    },
    controller: ['$scope', 'ServiceImagesData', function ($scope, ServiceImagesData) {
      $scope.imageSrc = null;
      $scope.$watch('imageId', function (n, o) {
        if (n != o) {
          $scope.imageSrc = null;
        }

        if (n) {
          $scope.evaluateImages();
        }
      });
      $scope.$on('requestImageSourceReady', function () {
        $scope.evaluateImages();
      });

      $scope.evaluateImages = function () {
        // now access trough getImage of images service
        if ($scope.imageId != 0 && !$scope.imageSrc) {
          ServiceImagesData.getImage($scope.imageId).then(function (response) {
            if (response.tinyCropImage) {
              $scope.imageSrc = response.tinyCropImage.source;
            } else {
              // the thumbnail does not exists, try to force a new xhr request which should generate the thumbnail:
              ServiceImagesData.getImage($scope.imageId, true).then(function (r) {
                if (r.tinyCropImage) {
                  $scope.imageSrc = r.tinyCropImage.source;
                }
              });
            }
          });
        }
      };
    }],
    template: function template() {
      return '<img ng-show="imageSrc" ng-src="{{imageSrc}}" alt="{{imageSrc}}" class="img-fluid rounded border" />';
    }
  };
});
zaa.directive('storageImageThumbnailDisplay', function () {
  return {
    restrict: 'E',
    scope: {
      imageId: '@imageId'
    },
    controller: ['$scope', '$filter', 'ServiceImagesData', 'ServiceFilesData', function ($scope, $filter, ServiceImagesData, ServiceFilesData) {
      $scope.$watch('imageId', function (n, o) {
        if (n != o) {
          $scope.imageSrc = null;
        }
      }); // controller logic

      $scope.$watch(function () {
        return $scope.imageId;
      }, function (n, o) {
        if (n != undefined || n != null) {
          ServiceImagesData.getImage(n).then(function (response) {
            $scope.imageSrc = response.tinyCropImage.source;
          }, function () {
            $scope.imageSrc = null;
          });
        }
      });
      $scope.imageSrc = null;
    }],
    template: function template() {
      return '<div ng-show="imageSrc"><img ng-src="{{imageSrc}}" alt="{{imageSrc}}" class="img-fluid" /></div>';
    }
  };
});
zaa.directive('storageFileUpload', function () {
  return {
    restrict: 'E',
    scope: {
      ngModel: '='
    },
    controller: ['$scope', '$filter', 'ServiceFilesData', function ($scope, $filter, ServiceFilesData) {
      $scope.modal = {
        state: 1
      };
      $scope.modalContainer = false;
      $scope.fileinfo = null;

      $scope.select = function (fileId) {
        $scope.toggleModal();
        $scope.ngModel = fileId;
      };

      $scope.reset = function () {
        $scope.ngModel = 0;
        $scope.fileinfo = null;
      };

      $scope.toggleModal = function () {
        $scope.modalContainer = !$scope.modalContainer;
        $scope.modal.state = !$scope.modal.state;
      };

      $scope.$watch(function () {
        return $scope.ngModel;
      }, function (n) {
        if (n == null || n == undefined || !angular.isNumber(n)) {
          return null;
        }

        ServiceFilesData.getFile(n).then(function (response) {
          $scope.fileinfo = response;
        }, function () {
          $scope.fileinfo = null;
        });
      });
    }],
    templateUrl: 'storageFileUpload'
  };
});
/**
 * Sotrage Image Upload directive.
 *
 * Call cycle when file directive implements the image directive:
 *
 * + reset() in file directive
 * + reset set $scope.fileId = 0
 * + fileId watcher applys filter
 * + filter can not find a file for id 0
 * + ngModel set to 0
 * 
 * options: {
 *    no_filter: true/false
 * }
 */

zaa.directive('storageImageUpload', function () {
  return {
    restrict: 'E',
    scope: {
      ngModel: '=',
      options: '='
    },
    controller: ['$scope', '$http', '$filter', 'ServiceFiltersData', 'ServiceImagesData', 'AdminToastService', 'ServiceFilesData', function ($scope, $http, $filter, ServiceFiltersData, ServiceImagesData, AdminToastService, ServiceFilesData) {
      // ServiceFiltesrData inheritance
      //$scope.ngModel = 0;
      $scope.filtersData = ServiceFiltersData.data;
      $scope.$on('service:FiltersData', function (event, data) {
        $scope.filtersData = data;
      }); // controller logic

      $scope.noFilters = function () {
        if ($scope.options) {
          return $scope.options.no_filter;
        }
      };

      $scope.thumbnailfilter = null;
      $scope.imageLoading = false;
      $scope.fileId = 0;
      $scope.filterId = 0;
      $scope.imageinfo = null;
      $scope.imageNotFoundError = false;
      $scope.thumb = false;

      $scope.filterApply = function () {
        $scope.imageLoading = true;
        ServiceFilesData.getFile($scope.fileId).then(function (response) {
          var images = $filter('filter')(response.images, {
            filter_id: $scope.filterId
          }); // unable to find the image for the given filter, create the image for the filter

          if (images.length == 0) {
            $http.post('admin/api-admin-storage/image-filter', {
              fileId: $scope.fileId,
              filterId: $scope.filterId
            }).then(function (uploadResponse) {
              $scope.ngModel = uploadResponse.data.id;
              AdminToastService.success(i18n['js_dir_image_upload_ok']);
              $scope.imageLoading = false;
            }, function (error) {
              AdminToastService.error(i18n['js_dir_image_filter_error']);
              $scope.imageLoading = false;
            });
          } else {
            $scope.ngModel = images[0].id;
            $scope.imageLoading = false;
          }
        }, function () {
          $scope.imageinfo = null;
          $scope.thumb = false;
          $scope.ngModel = 0;
        });
      };

      $scope.changeFilter = function () {
        $scope.filterApply();
      };

      $scope.$watch(function () {
        return $scope.fileId;
      }, function (n, o) {
        if (n != null && n != undefined) {
          $scope.filterApply();
        }
      });
      $scope.$watch(function () {
        return $scope.ngModel;
      }, function (n, o) {
        if (n != null && n != undefined && n != 0) {
          ServiceImagesData.getImage(n).then(function (response) {
            $scope.applyImageDetails(response);
            $scope.fileId = response.file_id;
            $scope.filterId = response.filter_id;
          }, function () {
            $scope.fileId = 0;
            $scope.filterId = 0;
            $scope.imageinfo = null;
            $scope.thumb = false;
          });
        }
      });

      $scope.applyImageDetails = function (imageInfo) {
        $scope.imageinfo = imageInfo;
        $scope.thumb = imageInfo;
      };
    }],
    templateUrl: 'storageImageUpload'
  };
});
/**
 * FILE MANAGER DIR
 */

zaa.directive("storageFileManager", function () {
  return {
    restrict: 'E',
    transclude: false,
    scope: {
      allowSelection: '@selection',
      onlyImages: '@onlyImages'
    },
    controller: ['$scope', '$http', '$filter', '$timeout', '$q', 'HtmlStorage', 'cfpLoadingBar', 'Upload', 'ServiceFoldersData', 'ServiceFilesData', 'LuyaLoading', 'AdminToastService', 'ServiceFoldersDirecotryId', 'ServiceAdminTags', 'ServiceQueueWaiting', function ($scope, $http, $filter, $timeout, $q, HtmlStorage, cfpLoadingBar, Upload, ServiceFoldersData, ServiceFilesData, LuyaLoading, AdminToastService, ServiceFoldersDirecotryId, ServiceAdminTags, ServiceQueueWaiting) {
      // ServiceFoldersData inheritance
      $scope.foldersData = ServiceFoldersData.data;
      $scope.$on('service:FoldersData', function (event, data) {
        $scope.foldersData = data;
      });

      $scope.foldersDataReload = function () {
        return ServiceFoldersData.load(true);
      }; // Service Tags


      $scope.tags = [];
      ServiceAdminTags.load().then(function (response) {
        $scope.tags = response;
      }); // ServiceFilesData inheritance

      $scope.filesData = [];
      $scope.totalFiles = 0;
      $scope.pageCount = 0;
      $scope.currentPageId = parseInt(HtmlStorage.getValue('filemanager.pageId', 1));
      $scope.$watch('currentPageId', function (pageId, oldPageId) {
        if (pageId !== undefined && pageId != oldPageId) {
          $scope.getFilesForCurrentPage();
        }
      }, true); // load files data for a given folder id

      $scope.$watch('currentFolderId', function (folderId, oldFolderId) {
        if (folderId !== undefined) {
          // generate the current pare info based on folder
          $scope.generateFolderInheritance(folderId);
          $scope.getFilesForPageAndFolder(folderId, 1);
        }
      }, true);
      $scope.folderInheritance = [];

      $scope.generateFolderInheritance = function (folderId) {
        $scope.folderInheritance = [];
        $scope.findFolderInheritance(folderId);
      };

      $scope.findFolderInheritance = function (folderId) {
        if ($scope.foldersData && $scope.foldersData.hasOwnProperty(folderId)) {
          var parent = $scope.foldersData[folderId];
          $scope.folderInheritance.push(parent);

          if (parent && parent.parentId) {
            $scope.findFolderInheritance(parent.parentId);
          }
        }
      };

      $scope.hasFolderActiveChild = function (folderId) {
        var value = false;
        angular.forEach($scope.folderInheritance, function (item) {
          if (item.id == folderId) {
            value = true;
          }
        });
        return value;
      };

      $scope.getFilesForPageAndFolder = function (folderId, pageId) {
        return $q(function (resolve, reject) {
          $http.get($scope.createUrl(folderId, pageId, $scope.sortField, $scope.searchQuery)).then(function (response) {
            // store sortField
            HtmlStorage.setValue('filemanager.sortField', $scope.sortField); // store pageId

            HtmlStorage.setValue('filemanager.pageId', parseInt(pageId));
            $scope.filesResponseToVars(response);
            return resolve(true);
          });
        });
      };

      $scope.createUrl = function (folderId, pageId, sortField, search) {
        return 'admin/api-admin-storage/data-files?folderId=' + folderId + '&page=' + pageId + '&expand=createThumbnail,createThumbnailMedium,isImage,sizeReadable&sort=' + sortField + '&search=' + search;
      };

      $scope.filesResponseToVars = function (response) {
        $scope.filesData = response.data; // meta

        $scope.pageCount = response.headers('X-Pagination-Page-Count');
        $scope.currentPageId = parseInt(response.headers('X-Pagination-Current-Page'));
        $scope.totalFiles = response.headers('X-Pagination-Total-Count');
      };

      $scope.filesMetaToPagination = function (meta) {
        $scope.pageCount = meta.totalPages;
      };

      $scope.getFilesForCurrentPage = function () {
        return $scope.getFilesForPageAndFolder($scope.currentFolderId, $scope.currentPageId);
      }; // ServiceFolderId


      $scope.currentFolderId = ServiceFoldersDirecotryId.folderId;

      $scope.foldersDirecotryIdReload = function () {
        return ServiceFoldersDirecotryId.load(true);
      }; // file replace logic


      $scope.folderCountMessage = function (folder) {
        return i18nParam('js_filemanager_count_files_overlay', {
          count: folder.filesCount
        });
      };

      $scope.errorMsg = null;

      $scope.replaceFile = function (file, errorFiles) {
        $scope.replaceFiled = file;

        if (!file) {
          return;
        }

        LuyaLoading.start();
        Upload.upload({
          url: 'admin/api-admin-storage/file-replace',
          data: {
            file: file,
            fileId: $scope.fileDetail.id,
            pageId: $scope.currentPageId
          }
        }).then(function (response) {
          LuyaLoading.stop();

          if (response.status == 200) {
            $scope.getFilesForCurrentPage().then(function () {
              AdminToastService.success(i18n['js_dir_manager_file_replace_ok']);
            });
            $scope.openFileDetail($scope.fileDetail, true);
          }
        }, function () {
          LuyaLoading.stop();
        });
      }; // upload logic


      $scope.$watch('uploadingfiles', function (uploadingfiles) {
        if (uploadingfiles != null) {
          $scope.uploadResults = 0;
          LuyaLoading.start(i18n['js_dir_upload_wait']);

          for (var i = 0; i < uploadingfiles.length; i++) {
            $scope.errorMsg = null;

            (function (uploadingfiles) {
              $scope.uploadUsingUpload(uploadingfiles);
            })(uploadingfiles[i]);
          }
        }
      });
      $scope.$watch('uploadResults', function (n, o) {
        if ($scope.uploadingfiles != null) {
          if (n == $scope.uploadingfiles.length && $scope.errorMsg == null) {
            $scope.getFilesForCurrentPage().then(function () {
              AdminToastService.success(i18n['js_dir_manager_upload_image_ok']);
              LuyaLoading.stop();
            });
          }
        }
      });

      $scope.pasteUpload = function (e) {
        for (var i = 0; i < e.originalEvent.clipboardData.items.length; i++) {
          var item = e.originalEvent.clipboardData.items[i];

          if (item.kind == 'file') {
            LuyaLoading.start(i18n['js_dir_upload_wait']);
            Upload.upload({
              url: 'admin/api-admin-storage/files-upload',
              fields: {
                'folderId': $scope.currentFolderId
              },
              file: item.getAsFile()
            }).then(function (response) {
              if (response.data.upload) {
                ServiceQueueWaiting.waitFor(response.data.queueIds).then(function (waitForResposne) {
                  $scope.getFilesForCurrentPage().then(function () {
                    AdminToastService.success(i18n['js_dir_manager_upload_image_ok']);
                    LuyaLoading.stop();
                  });
                });
              } else {
                AdminToastService.error(response.data.message);
                LuyaLoading.stop();
              }
            }, function (error) {
              AdminToastService.error(error.data.message);
              LuyaLoading.stop();
            });
          }
        }
      };

      $scope.uploadUsingUpload = function (file) {
        file.upload = Upload.upload({
          url: 'admin/api-admin-storage/files-upload',
          fields: {
            'folderId': $scope.currentFolderId
          },
          file: file
        });
        file.upload.then(function (response) {
          $timeout(function () {
            ServiceQueueWaiting.waitFor(response.data.queueIds).then(function (waitForResposne) {
              $scope.uploadResults++;
              file.processed = true;
              file.result = response.data;

              if (!file.result.upload) {
                AdminToastService.error(file.result.message);
                LuyaLoading.stop();
                $scope.errorMsg = true;
              }
            });
          });
        }, function (response) {
          file = response.data;
          AdminToastService.error(file.message);
          LuyaLoading.stop();
          $scope.errorMsg = true;
        });
        file.upload.progress(function (evt) {
          file.processed = false; // Math.min is to fix IE which reports 200% sometimes

          file.progress = Math.min(100, parseInt(100.0 * evt.loaded / evt.total));
        });
      }; // selector logic


      $scope.selectedFiles = [];

      $scope.toggleSelectionAll = function () {
        $scope.filesData.forEach(function (value, key) {
          $scope.toggleSelection(value);
        });
      };

      $scope.toggleSelection = function (file) {
        if ($scope.allowSelection == 'true') {
          // parent inject
          $scope.$parent.select(file.id);
          return;
        }

        var i = $scope.selectedFiles.indexOf(file.id);

        if (i > -1) {
          $scope.selectedFiles.splice(i, 1);
        } else {
          $scope.selectedFiles.push(file.id);
        }
      };

      $scope.inSelection = function (file) {
        var response = $scope.selectedFiles.indexOf(file.id);

        if (response != -1) {
          return true;
        }

        return false;
      }; // folder add


      $scope.showFolderForm = false;

      $scope.createNewFolder = function (newFolderName) {
        if (!newFolderName) {
          return;
        }

        $http.post('admin/api-admin-storage/folder-create', {
          folderName: newFolderName,
          parentFolderId: $scope.currentFolderId
        }).then(function (response) {
          var folderId = response.data;
          $scope.foldersDataReload().then(function (response) {
            $scope.folderFormToggler();
            $scope.newFolderName = null;
            $scope.changeCurrentFolderId(folderId);
          });
        });
      };

      $scope.folderFormToggler = function () {
        $scope.showFolderForm = !$scope.showFolderForm;
      }; // controller logic


      $scope.searchQuery = '';
      $scope.searchPromise = null;
      $scope.searchLoading = false;

      $scope.runSearch = function () {
        if ($scope.searchQuery.length > 0) {
          $scope.searchLoading = true;
          cfpLoadingBar.start();
          $timeout.cancel($scope.searchPromise);
          $scope.searchPromise = $timeout(function () {
            $scope.getFilesForCurrentPage().then(function () {
              $scope.searchLoading = false;
              cfpLoadingBar.complete();
            });
          }, 1000);
        } else {
          $scope.getFilesForCurrentPage().then(function () {
            $scope.searchLoading = false;
          });
        }
      };

      $scope.sortField = HtmlStorage.getValue('filemanager.sortField', 'name_original');

      $scope.changeSortField = function (name) {
        $scope.sortField = name;
        $scope.getFilesForCurrentPage();
      };

      $scope.changeCurrentFolderId = function (folderId, noState) {
        $scope.searchQuery = '';
        var oldCurrentFolder = $scope.currentFolderId;
        $scope.currentFolderId = folderId;
        $scope.currentPageId = 1;
        $scope.selectedFiles = [];

        if (noState !== true && oldCurrentFolder != folderId) {
          ServiceFoldersDirecotryId.folderId = folderId;
          $http.post('admin/api-admin-common/save-filemanager-folder-state', {
            folderId: folderId
          }, {
            ignoreLoadingBar: true
          });
        }
      };

      $scope.toggleFolderItem = function (data) {
        if (data.toggle_open == undefined) {
          data['toggle_open'] = 1;
        } else {
          data['toggle_open'] = !data.toggle_open;
        }

        $http.post('admin/api-admin-common/filemanager-foldertree-history', {
          data: data
        }, {
          ignoreLoadingBar: true
        });
      };

      $scope.folderUpdateForm = false;
      $scope.folderDeleteForm = false;
      $scope.folderDeleteConfirmForm = false;

      $scope.updateFolder = function (folder) {
        $http.post('admin/api-admin-storage/folder-update?folderId=' + folder.id, {
          name: folder.name
        }).then(function (transport) {
          AdminToastService.success(i18n['js_dir_manager_rename_success']);
        });
      };

      $scope.cancelFolderEdit = function (folder, oldName) {
        folder.name = oldName;
      };

      $scope.deleteFolder = function (folder) {
        $http.post('admin/api-admin-storage/is-folder-empty?folderId=' + folder.id, {
          name: folder.name
        }).then(function (transport) {
          var isEmpty = transport.data.empty;
          var filesCount = transport.data.count;

          if (isEmpty) {
            $http.post('admin/api-admin-storage/folder-delete?folderId=' + folder.id, {
              name: folder.name
            }).then(function (transport) {
              $scope.foldersDataReload().then(function () {
                $scope.currentFolderId = 0;
              });
            });
          } else {
            AdminToastService.confirm(i18nParam('layout_filemanager_remove_dir_not_empty', {
              folderName: folder.name,
              count: filesCount
            }), i18n['js_dir_manager_rm_folder_confirm_title'], ['$timeout', '$toast', function ($timeout, $toast) {
              $http.post('admin/api-admin-storage/folder-delete?folderId=' + folder.id, {
                name: folder.name
              }).then(function () {
                $scope.foldersDataReload().then(function () {
                  $scope.currentFolderId = 0;
                  $toast.close();
                });
              });
            }]);
          }
        });
      };

      $scope.removeFiles = function () {
        AdminToastService.confirm(i18n['js_dir_manager_rm_file_confirm'], i18n['js_dir_manager_rm_file_confirm_title'], ['$timeout', '$toast', function ($timeout, $toast) {
          $http.post('admin/api-admin-storage/filemanager-remove-files', {
            'ids': $scope.selectedFiles,
            'pageId': $scope.currentPageId,
            'folderId': $scope.currentFolderId
          }).then(function (transport) {
            $scope.getFilesForCurrentPage().then(function () {
              $toast.close();
              AdminToastService.success(i18n['js_dir_manager_rm_file_ok']);
              $scope.selectedFiles = [];
              $scope.closeFileDetail();
            });
          });
        }]);
      };

      $scope.moveFilesTo = function (folderId) {
        $http.post('admin/api-admin-storage/filemanager-move-files', {
          'fileIds': $scope.selectedFiles,
          'toFolderId': folderId,
          'currentPageId': $scope.currentPageId,
          'currentFolderId': $scope.currentFolderId
        }).then(function (transport) {
          $scope.getFilesForCurrentPage().then(function () {
            $scope.selectedFiles = [];
            $scope.showFoldersToMove = false;
          });
        });
      };

      $scope.getFolderData = function (parentFolderId) {
        return $filter('filemanagerdirsfilter')($scope.foldersData, parentFolderId);
      };

      $scope.getFilesForCurrentPage();
      /* file detail related stuff */

      $scope.fileDetail = false;
      $scope.showFoldersToMove = false;
      $scope.largeImagePreviewState = true;
      $scope.fileDetailFull = false;
      $scope.nameEditMode = false;
      $scope.fileDetailFolder = false;
      $scope.detailLoading = false;

      $scope.openFileDetail = function (file, force) {
        if ($scope.fileDetail.id == file.id && force !== true) {
          $scope.closeFileDetail();
        } else {
          cfpLoadingBar.start();
          $scope.detailLoading = true;
          ServiceFilesData.getFile(file.id, force).then(function (responseFile) {
            $scope.fileDetailFull = responseFile;
            $scope.fileDetailFolder = $scope.foldersData[responseFile.folder_id];
            $scope.detailLoading = false;
            cfpLoadingBar.complete();
          }, function () {});
          $scope.fileDetail = file;
        }
      };

      $scope.saveTagRelation = function (tag, file) {
        $http.post('admin/api-admin-storage/toggle-file-tag', {
          tagId: tag.id,
          fileId: file.id
        }).then(function (response) {
          $scope.fileDetailFull.tags = response.data;
        });
      };

      $scope.fileHasTag = function (tag) {
        var exists = false;
        angular.forEach($scope.fileDetailFull.tags, function (value) {
          if (value.id == tag.id) {
            exists = true;
          }
        });
        return exists;
      };

      $scope.updateFileData = function () {
        $http.put('admin/api-admin-storage/file-update?id=' + $scope.fileDetailFull.id + '&pageId=' + $scope.currentPageId, $scope.fileDetailFull).then(function (response) {
          var file = $filter('findidfilter')($scope.filesData, $scope.fileDetail.id, true);
          file.name = response.data.name_original;
          $scope.nameEditMode = false;
        });
      };

      $scope.closeFileDetail = function () {
        $scope.fileDetail = false;
        $scope.fileDetailFull = false;
        $scope.nameEditMode = false;
      };

      $scope.removeFile = function (detail) {
        $scope.selectedFiles = [];
        $scope.toggleSelection(detail);
        $scope.removeFiles();
      };

      $scope.isFileEditHidden = true;

      $scope.editFile = function (file) {
        $scope.isFileEditHidden = !$scope.isFileEditHidden;
      };

      $scope.cropSuccess = function () {
        $scope.isFileEditHidden = true;
        $scope.getFilesForCurrentPage().then(function () {
          AdminToastService.success(i18n['crop_success']);
        });
        $scope.openFileDetail($scope.fileDetail, true);
      };

      $scope.storeFileCaption = function (fileDetail) {
        $http.post('admin/api-admin-storage/filemanager-update-caption', {
          'id': fileDetail.id,
          'captionsText': fileDetail.captionArray,
          'pageId': $scope.currentPageId
        }).then(function (transport) {
          AdminToastService.success(i18n['file_caption_success']);
        });
      };

      $scope.selectedFileFromParent = null;

      $scope.init = function () {
        if ($scope.$parent.fileinfo) {
          $scope.selectedFileFromParent = $scope.$parent.fileinfo;
          $scope.changeCurrentFolderId($scope.selectedFileFromParent.folder_id, true);
        }
      };

      $scope.init();
    }],
    templateUrl: 'storageFileManager'
  };
});
zaa.directive("hasEnoughSpace", ['$window', '$timeout', function ($window, $timeout) {
  return {
    restrict: "A",
    scope: {
      "loadingCondition": "=",
      "isFlexBox": "="
    },
    link: function link(scope, element, attrs) {
      scope.elementWidth = 0;

      var getElementOriginalWidth = function getElementOriginalWidth() {
        var elementClone = element.clone().insertAfter(element);
        elementClone.css({
          'position': 'fixed',
          'top': 0,
          'left': 0,
          'visibility': 'hidden'
        });

        if (elementClone.css('display') === 'none') {
          elementClone.css('display', scope.isFlexBox ? 'flex' : 'block');
        }

        var elementOriginalWidth = elementClone.outerWidth();
        elementClone.remove();
        return elementOriginalWidth;
      };

      function checkSize() {
        $timeout(function () {
          if (!scope.elementOriginalWidth) {
            scope.elementOriginalWidth = getElementOriginalWidth();
          }

          if (element.hasClass('not-enough-space')) {
            element.removeClass('not-enough-space');
            element.addClass('has-enough-space');
          }

          var currentElementSpace = element.parent().outerWidth();

          if (currentElementSpace < scope.elementOriginalWidth) {
            element.removeClass('has-enough-space').addClass('not-enough-space');
          } else {
            element.removeClass('not-enough-space').addClass('has-enough-space');
          }
        });
      }

      angular.element($window).on('resize', function () {
        checkSize();
      });
      scope.$watch('loadingCondition', function (n) {
        if (n == true) {
          checkSize();
        }
      });
    }
  };
}]);
zaa.directive('activeClass', function () {
  return {
    restrict: 'A',
    scope: {
      activeClass: '@'
    },
    link: function link(scope, element) {
      element.on('mouseenter', function () {
        element.addClass(scope.activeClass);
      });
      element.on('mouseleave', function () {
        element.removeClass(scope.activeClass);
      });
      element.on('click', function () {
        element.toggleClass(scope.activeClass);
      });
    }
  };
});
/**
 * Image edit div.
 * 
 * @see https://github.com/CrackerakiUA/ui-cropper/wiki/Options
 */

zaa.directive('imageEdit', function () {
  return {
    restrict: 'E',
    scope: {
      fileId: '=',
      onSuccess: '&'
    },
    controller: ['$scope', '$http', '$timeout', function ($scope, $http, $timeout) {
      // the loaded file to crop
      $scope.file; // cropper image

      $scope.cropperImage; // cropper config

      $scope.cropperConfig = {
        distUrl: '',
        areaType: 'rectangle',
        ratio: null,
        resultImageSize: 'max',
        resultImageFormat: 'image/jpeg',
        resultImageQuality: 1.0,
        areaInitSize: 200,
        canvasScalemode: 'full-width'
      };

      $scope.changeQuality = function (value) {
        $scope.cropperConfig.resultImageQuality = value;
      };

      $scope.isCurrentQuality = function (value) {
        return $scope.cropperConfig.resultImageQuality == value;
      };

      $http.get('/admin/api-admin-storage/file-info?id=' + $scope.fileId).then(function (response) {
        $scope.file = response.data;
        $scope.cropperConfig.resultImageFormat = $scope.file.mime_type; // use the LUYA file controller proxy which ensures accessability, which does not work when using s3 filesystem f.e.

        $scope.cropperImage = $scope.file.file.href;
      });
      $scope.saveAsCopy = true;

      $scope.isCurrentRatio = function (value) {
        return $scope.cropperConfig.ratio == value;
      };

      $scope.changeRatio = function (value) {
        $scope.cropperImage = false;
        $scope.cropperConfig.ratio = value;
        $timeout(function () {
          $scope.cropperImage = $scope.file.source;
        });
      };

      $scope.save = function () {
        $http.post('/admin/api-admin-storage/file-crop', {
          distImage: $scope.cropperConfig.distUrl,
          fileName: $scope.file.name_new_compound,
          extension: $scope.file.extension,
          saveAsCopy: $scope.saveAsCopy,
          fileId: $scope.file.id
        }).then(function (response) {
          $scope.onSuccess();
        });
      };
    }],
    template: "\n    <div class=\"row\">\n        <div class=\"col-md-8\">\n            <p class=\"lead\">" + i18n['crop_source_image'] + "</p>\n            <div class=\"bg-light rounded pt-3 pl-3 pr-3 pb-2\">\n            <ui-cropper\n                ng-if=\"cropperImage\" \n                image=\"cropperImage\" \n                result-image=\"cropperConfig.distUrl\"\n                result-image-format=\"{{cropperConfig.resultImageFormat}}\"\n                result-image-quality=\"cropperConfig.resultImageQuality\"\n                result-image-size=\"cropperConfig.resultImageSize\"\n                area-type=\"{{cropperConfig.areaType}}\" \n                area-init-size=\"cropperConfig.areaInitSize\"\n                chargement=\"'Loading'\"\n                canvas-scalemode=\"{{cropperConfig.canvasScalemode}}\"\n                aspect-ratio=\"cropperConfig.ratio\"\n            ></ui-cropper>\n            </div>\n            <ul class=\"list-group list-group-horizontal justify-content-center mt-3\">\n                <li class=\"list-group-item text-center\" ng-class=\"{'active':isCurrentRatio(null)}\" ng-click=\"changeRatio(null)\"><i class=\"material-icons\">crop_free</i><br /><small>" + i18n['crop_size_free'] + "</small></li>\n                <li class=\"list-group-item text-center\" ng-class=\"{'active':isCurrentRatio('1')}\" ng-click=\"changeRatio('1')\"><i class=\"material-icons\">crop_square</i><br /><small>" + i18n['crop_size_1to1'] + "</small></li>\n                <li class=\"list-group-item text-center\" ng-class=\"{'active':isCurrentRatio('1.7')}\" ng-click=\"changeRatio('1.7')\"><i class=\"material-icons\">crop_16_9</i><br /><small>" + i18n['crop_size_desktop'] + "</small></li>\n                <li class=\"list-group-item text-center\" ng-class=\"{'active':isCurrentRatio('0.5')}\" ng-click=\"changeRatio('0.5')\"><i class=\"material-icons\">crop_portrait</i><br /><small>" + i18n['crop_size_mobile'] + "</small></li>\n            </ul>\n        </div>\n        <div class=\"col-md-4\" ng-show=\"cropperImage\">\n            <p class=\"lead\">" + i18n['crop_preview'] + "</p>\n            <img ng-src=\"{{cropperConfig.distUrl}}\" ng-show=\"cropperConfig.distUrl\" class=\"img-fluid border\" />\n\n            <ul class=\"list-group list-group-horizontal justify-content-center mt-3\">\n                <li class=\"list-group-item text-center\" ng-class=\"{'active':isCurrentQuality(1.0)}\" ng-click=\"changeQuality(1.0)\"><i class=\"material-icons\">looks_one</i><br /><small>" + i18n['crop_quality_high'] + "</small></li>\n                <li class=\"list-group-item text-center\" ng-class=\"{'active':isCurrentQuality(0.8)}\" ng-click=\"changeQuality(0.8)\"><i class=\"material-icons\">looks_two</i><br /><small>" + i18n['crop_quality_medium'] + "</small></li>\n                <li class=\"list-group-item text-center\" ng-class=\"{'active':isCurrentQuality(0.5)}\" ng-click=\"changeQuality(0.5)\"><i class=\"material-icons\">looks_3</i><br /><small>" + i18n['crop_quality_low'] + "</small></li>\n            </ul>\n\n            <div class=\"form-check mt-3 rounded border p-2\" ng-click=\"saveAsCopy=!saveAsCopy\" ng-class=\"{'bg-light':saveAsCopy}\">\n                <input class=\"form-check-input\" type=\"checkbox\" ng-model=\"saveAsCopy\">\n                <label class=\"form-check-label\">\n                " + i18n['crop_btn_as_copy'] + "\n                </label>\n                <small class=\"text-muted\">" + i18n['crop_btn_as_copy_hint'] + "</small>\n            </div>\n\n            <button type=\"button\" ng-show=\"saveAsCopy\" class=\"mt-3 btn btn-lg btn-icon btn-save\" ng-click=\"save()\">" + i18n['crop_btn_save_copy'] + "</button>\n            <button type=\"button\" ng-show=\"!saveAsCopy\" class=\"mt-3 btn btn-lg btn-icon btn-save\" ng-click=\"save()\">" + i18n['crop_btn_save_replace'] + "</button>\n        </div>\n    </div>\n        "
  };
});
/**
 * Pagination directive
 *
 * > Currently its not supported to change the current page value from outside the directive. therefore
 * > the pagination always starts on page 1
 */

zaa.directive('pagination', function () {
  return {
    restrict: 'E',
    scope: {
      currentPage: '=',
      pageCount: '='
    },
    controller: ['$scope', '$timeout', function ($scope, $timeout) {
      $scope.pageNumberInputVal = $scope.currentPage;
      $scope.$watch('currentPage', function (newVal) {
        $scope.pageNumberInputVal = newVal;
      });
      $scope.$watch('pageNumberInputVal', function () {
        // Set the input width (ato-grow)
        $scope.inputWidth = 25 + 10 * ($scope.pageNumberInputVal.toString().length <= 0 ? 1 : $scope.pageNumberInputVal.toString().length);
      });
      var timeoutPromise = null;

      $scope.pageNumberInputChange = function () {
        if (timeoutPromise) {
          $timeout.cancel(timeoutPromise);
        } // Debounce


        timeoutPromise = $timeout(function () {
          if (isNaN($scope.pageNumberInputVal)) {
            // Not a number, reset
            $scope.pageNumberInputVal = $scope.currentPage;
          } else {
            // Input is number
            if (parseInt($scope.pageNumberInputVal) > parseInt($scope.pageCount) || parseInt($scope.pageNumberInputVal) <= 0) {
              // Out of range, reset
              $scope.pageNumberInputVal = $scope.currentPage;
            } else {
              $scope.currentPage = $scope.pageNumberInputVal;
            }
          }
        }, 500);
      };

      $scope.next = function () {
        if ($scope.currentPage < $scope.pageCount) {
          $scope.currentPage += 1;
        }
      };

      $scope.prev = function () {
        if ($scope.currentPage > 1) {
          $scope.currentPage -= 1;
        }
      };

      $scope.first = function () {
        $scope.currentPage = 1;
      };

      $scope.last = function () {
        $scope.currentPage = $scope.pageCount;
      };
    }],
    template: "\n            <div class=\"pagination\" ng-show=\"pageCount > 1\">\n                <button class=\"pagination-btn pagination-btn-first btn btn-icon btn-first-page\" ng-click=\"first()\" ng-disabled=\"currentPage == 1\"></button>\n                <button class=\"pagination-btn pagination-btn-prev btn btn-icon btn-prev\" ng-click=\"prev()\" ng-disabled=\"currentPage == 1\"></button>\n                <div class=\"pagination-page\">\n                    <input ng-style=\"{'max-width': inputWidth + 'px'}\" class=\"form-control pagination-input\" type=\"text\" ng-model=\"pageNumberInputVal\" ng-change=\"pageNumberInputChange()\" />\n                    <span class=\"pagination-delimiter\">/</span>\n                    <span class=\"pagination-number-of-pages\">{{pageCount}}</span>\n                </div>\n                <button class=\"pagination-btn pagination-btn-next btn btn-icon btn-next\" ng-click=\"next()\" ng-disabled=\"currentPage == pageCount\"></button>\n                <button class=\"pagination-btn pagination-btn-last btn btn-icon btn-last-page\" ng-click=\"last()\" ng-disabled=\"currentPage == pageCount\"></button>\n            </div>\n        "
  };
});
/**
 * Supporting directive to SelectArrayGently plugin
 *
 */

zaa.directive('selectArrayGently', function () {
  return {
    restrict: 'E',
    scope: {
      'model': '=',
      'options': '=',
      'optionsvalue': '@optionsvalue',
      'optionslabel': '@optionslabel'
    },
    controller: ['$rootScope', '$scope', function ($rootScope, $scope) {
      if ($scope.optionsvalue === undefined) {
        $scope.optionsvalue = 'value';
      }

      if ($scope.optionslabel === undefined) {
        $scope.optionslabel = 'label';
      }

      $scope.getSelectedLabel = function () {
        // Keep raw value by default
        var selectedLabel = $scope.model;
        angular.forEach($scope.options, function (item) {
          if ($scope.model === item[$scope.optionsvalue]) {
            selectedLabel = item[$scope.optionslabel];
          }
        });
        return selectedLabel;
      };
    }],
    template: function template() {
      return '<span>{{getSelectedLabel()}}</span>';
    }
  };
});zaa.controller("DefaultDashboardObjectController", ['$scope', '$http', '$sce', function ($scope, $http, $sce) {
  $scope.data;

  $scope.loadData = function (dataApiUrl) {
    $http.get(dataApiUrl).then(function (success) {
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

zaa.controller("CrudController", ['cfpLoadingBar', '$scope', '$rootScope', '$filter', '$http', '$sce', '$state', '$timeout', '$injector', '$q', 'AdminLangService', 'AdminToastService', 'CrudTabService', 'ServiceImagesData', '$parse', function (cfpLoadingBar, $scope, $rootScope, $filter, $http, $sce, $state, $timeout, $injector, $q, AdminLangService, AdminToastService, CrudTabService, ServiceImagesData, $parse) {
  $scope.toast = AdminToastService;
  $scope.AdminLangService = AdminLangService;
  $scope.tabService = CrudTabService;

  $scope.clearData = function () {
    AdminToastService.confirm(i18n['ngrest_delete_all_button_confirm_message'], i18n['ngrest_delete_all_button_label'], function () {
      var toast = this;
      $http.get($scope.config.apiEndpoint + '/truncate').then(function () {
        toast.close();
        $scope.loadList();
      });
    });
  };
  /***** TABS AND SWITCHES *****/

  /**
   * 0 = list
   * 1 = add
   * 2 = edit
   */


  $scope.crudSwitchType = 0;

  $scope.switchToTab = function (tab) {
    angular.forEach($scope.tabService.tabs, function (item) {
      item.active = false;
    });
    tab.active = true;
    $scope.switchTo(4);
  };

  $scope.addAndswitchToTab = function (pk, route, index, label, model) {
    $scope.tabService.addTab(pk, route, index, label, model);
    $scope.switchTo(4);
  };

  $scope.closeTab = function (tab, index) {
    $scope.tabService.remove(index, $scope);
  };

  $scope.switchTo = function (type, reset) {
    if ($scope.config.relationCall) {
      $scope.crudSwitchType = type;
      return;
    }

    if (reset) {
      $scope.resetData();
    }

    if (type == 0) {
      $http.get($scope.config.apiEndpoint + '/unlock', {
        ignoreLoadingBar: true
      });
    }

    if (type == 0 || type == 1) {
      if (!$scope.config.inline) {
        $state.go('default.route');
      }
    }

    $scope.crudSwitchType = type;

    if (type !== 4 && !$scope.config.inline) {
      angular.forEach($scope.tabService.tabs, function (item) {
        item.active = false;
      });
    }
  };

  $scope.closeUpdate = function () {
    $scope.switchTo(0, true);
  };

  $scope.closeCreate = function () {
    $scope.switchTo(0, true);
  };

  $scope.activeWindowModal = true;

  $scope.openActiveWindow = function () {
    $scope.activeWindowModal = false;
  };

  $scope.closeActiveWindow = function () {
    $scope.activeWindowModal = true;
  };

  $scope.changeGroupByField = function () {
    if ($scope.config.groupByField == 0) {
      $scope.config.groupBy = 0;
    } else {
      $scope.config.groupBy = 1;
    }
  };
  /********* SETTINGS DROPDOWN MENU ******/


  $scope.isSettingsVisible = false;

  $scope.toggleSettingsMenu = function () {
    $scope.isSettingsVisible = !$scope.isSettingsVisible;
  };

  $scope.hiddeSettingsMenu = function () {
    $scope.isSettingsVisible = false;
  };
  /********* NEW EXPORT MODAL ******/


  $scope.isExportModalHidden = true;
  $scope.exportdata = {
    header: 1,
    type: "xlsx"
  };

  $scope.toggleExportModal = function () {
    $scope.exportdata.filter = $scope.config.filter;
    $scope.isExportModalHidden = !$scope.isExportModalHidden;
  };

  $scope.exportResponse = false;

  $scope.generateExport = function () {
    $http.post($scope.config.apiEndpoint + '/export?' + $scope.config.apiExportQueryString, $scope.exportdata).then(function (response) {
      $scope.exportResponse = response.data;
    });
  };

  $scope.downloadExport = function () {
    var url = $scope.exportResponse.url;
    $scope.exportResponse = false;
    window.open(url);
    return false;
  };
  /********** CRUD LIST *******/


  $scope.applySaveCallback = function () {
    if ($scope.config.saveCallback) {
      $injector.invoke($scope.config.saveCallback, this);
    }
  };
  /*********** ORDER **********/


  $scope.isOrderBy = function (field) {
    if (field == $scope.config.orderBy) {
      return true;
    }

    return false;
  };

  $scope.changeOrder = function (field, sort) {
    $scope.config.orderBy = sort + field;
    $http.post('admin/api-admin-common/ngrest-order', {
      'apiEndpoint': $scope.config.apiEndpoint,
      sort: sort,
      field: field
    }, {
      ignoreLoadingBar: true
    });
    $scope.loadList();
  };
  /****************** ACTIVE BUTTON ***********/


  $scope.callActiveButton = function (hash, id, event) {
    var elmn = angular.element(event.currentTarget);
    elmn.addClass('crud-buttons-button-loading');
    $http.get($scope.config.apiEndpoint + '/active-button?hash=' + hash + '&id=' + id.join()).then(function (success) {
      elmn.removeClass('crud-buttons-button-loading');
      elmn.addClass('crud-buttons-button-success');
      $timeout(function () {
        elmn.removeClass('crud-buttons-button-success');
      }, 5000);
      angular.forEach(success.data.events, function (value) {
        // event names
        if (value == 'loadList') {
          $scope.loadList();
        }
      });
      AdminToastService.success(success.data.message);
    }, function (error) {
      elmn.removeClass('crud-buttons-button-loading');
      elmn.addClass('crud-buttons-button-danger');
      $timeout(function () {
        elmn.removeClass('crud-buttons-button-danger');
      }, 5000);
      AdminToastService.error(error.data.message);
    });
  };
  /***************** ACTIVE WINDOW *********/


  $scope.reloadActiveWindow = function () {
    $scope.getActiveWindow($scope.data.aw.hash, $scope.data.aw.itemId);
  };

  $scope.getActiveWindow = function (activeWindowId, id, $event) {
    $http.post($scope.config.activeWindowRenderUrl, {
      itemId: id,
      activeWindowHash: activeWindowId,
      ngrestConfigHash: $scope.config.ngrestConfigHash
    }).then(function (response) {
      $scope.openActiveWindow();
      $scope.data.aw.itemId = id;
      $scope.data.aw.configCallbackUrl = $scope.config.activeWindowCallbackUrl;
      $scope.data.aw.configHash = $scope.config.ngrestConfigHash;
      $scope.data.aw.hash = activeWindowId;
      $scope.data.aw.content = $sce.trustAsHtml(response.data.content);
      $scope.data.aw.title = response.data.title;
      $scope.$broadcast('awloaded', {
        id: activeWindowId
      });
    });
  };

  $scope.getActiveWindowCallbackUrl = function (callback) {
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


  $scope.sendActiveWindowCallback = function (callback, data) {
    var data = data || {};
    return $http.post($scope.getActiveWindowCallbackUrl(callback), $.param(data), {
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
      }
    });
  };
  /*************** SEARCH ******************/


  $scope.searchPromise = null;
  $scope.$watch('config.searchQuery', function (n, o) {
    if (n == o || n == undefined || n == null) {
      return;
    }

    $scope.applySearchQuery(n);
  });

  $scope.applySearchQuery = function (n) {
    if (n == undefined || n == null) {
      return;
    }

    $timeout.cancel($scope.searchPromise);

    if (n.length == 0) {
      $scope.loadList(1);
    } else {
      cfpLoadingBar.start();
      $scope.searchPromise = $timeout(function () {
        $scope.reloadCrudList(1);
      }, 700);
    }
  };

  $scope.generateSearchPromise = function (value, page) {
    return $http.post($scope.generateUrlWithParams('search', page), {
      query: value
    }).then(function (response) {
      $scope.parseResponseQueryToListArray(response);
    });
  };
  /******* ACTIVE SELECTIONS *******/


  $scope.selectedItems = [];

  $scope.isInSelection = function (item) {
    var pk = $scope.getRowPrimaryValue(item);
    return this.selectedItems.indexOf(pk) != -1;
  };

  $scope.toggleSelection = function (item) {
    var pk = $scope.getRowPrimaryValue(item);
    var index = this.selectedItems.indexOf(pk);

    if (index == -1) {
      this.selectedItems.push(pk);
    } else {
      this.selectedItems.splice(index, 1);
    }
  };

  $scope.sendActiveSelection = function (buttonIndex) {
    $http.post($scope.config.apiEndpoint + '/active-selection?index=' + buttonIndex, {
      ids: this.selectedItems
    }).then(function (success) {
      angular.forEach(success.data.events, function (value) {
        // event names
        if (value == 'loadList') {
          $scope.loadList();
        }
      });
      AdminToastService.success(success.data.message);
    }, function (error) {
      AdminToastService.error(error.data.message);
    });
  };
  /******* RELATION CALLLS *********/

  /**
   * Modal view select a value from a modal into its parent plugin.
   */


  $scope.parentSelectInline = function (item) {
    $scope.$parent.$parent.$parent.setModelValue($scope.getRowPrimaryValue(item), item);
  };
  /**
   * Check if a field exists in the parents relation list or in a pool config, if yes hide the field
   * for the given form and return the relation call or pool config value instead in order to auto store those.
   */


  $scope.checkIfFieldExistsInPopulateCondition = function (field) {
    // check if value existing in pool config
    var pools = $scope.config.pools;

    if (pools.hasOwnProperty(field)) {
      return pools[field];
    } // this call is relation call, okay check for the parent relation definition


    if ($scope.config.relationCall) {
      var relations = $scope.$parent.$parent.config.relations;
      var definition = relations[parseInt($scope.config.relationCall.arrayIndex)];
      var linkdefinition = definition.relationLink;

      if (linkdefinition !== null && linkdefinition.hasOwnProperty(field)) {
        return parseInt($scope.config.relationCall.id);
      }
    }

    return false;
  };

  $scope.relationItems = [];
  /****** DELETE, UPDATE, CREATE */

  $scope.deleteItem = function (id, $event) {
    AdminToastService.confirm(i18n['js_ngrest_rm_page'], i18n['ngrest_button_delete'], ['$toast', function ($toast) {
      $http.delete($scope.config.apiEndpoint + '/' + id).then(function (response) {
        $scope.loadList();
        $toast.close();
        AdminToastService.success(i18n['js_ngrest_rm_confirm']);
      }, function (data) {
        $scope.printErrors(data);
      });
    }]);
  };

  $scope.toggleUpdate = function (id) {
    $scope.resetData();
    $http.get($scope.config.apiEndpoint + '/' + id + '?' + $scope.config.apiUpdateQueryString).then(function (response) {
      var data = response.data;
      $scope.data.update = data;

      if ($scope.config.relationCall) {
        $scope.crudSwitchType = 2;
      } else {
        $scope.switchTo(2);
      }

      if (!$scope.config.inline) {
        $state.go('default.route.detail', {
          id: id
        });
      }

      $scope.data.updateId = id;
    }, function (data) {
      AdminToastService.error(i18n['js_ngrest_error']);
    });
  };

  $scope.highlightPkValue = null;
  $scope.highlightTimeout = 5000;
  /**
   * Check whether this item (row) is currently highlihted or not.
   */

  $scope.isRowHighlighted = function (item) {
    var pkValue = $scope.getRowPrimaryValue(item);

    if (pkValue == $scope.highlightPkValue) {
      return true;
    }

    return false;
  };
  /**
   * Checks is string a valid CSS color
   *
   * @param possibleColor A string to test
   * @returns {boolean}
   */


  var isStringAColor = function isStringAColor(possibleColor) {
    var div = document.createElement('div');
    div.style.color = 'rgb(0, 0, 0)';
    div.style.color = possibleColor;

    if (div.style.color !== 'rgb(0, 0, 0)') {
      return true;
    }

    div.style.color = 'rgb(255, 255, 255)';
    div.style.color = possibleColor;
    return div.style.color !== 'rgb(255, 255, 255)';
  };
  /**
   * Parses string to a color or an AngularJs expression
   *
   * @param item ngRest data object
   * @param expression A string to parse
   * @returns {boolean|*} parsed string or false
   */


  $scope.getParsedCellColor = function (item, expression) {
    if (isStringAColor(expression)) {
      return expression;
    }

    try {
      var parsed = $parse(expression)(item);
    } catch (err) {
      //console.log (err.name + ': "' + err.message +  '" occurred when $parse');
      return false;
    }

    return parsed;
  };

  $scope.submitUpdate = function (close) {
    $http.put($scope.config.apiEndpoint + '/' + $scope.data.updateId, angular.toJson($scope.data.update, true)).then(function (response) {
      AdminToastService.success(i18n['js_ngrest_rm_update']);
      $scope.loadList($scope.pager.currentPage).then(function () {
        $scope.applySaveCallback();

        if (close) {
          $scope.switchTo(0, true);
        }

        $scope.highlightPkValue = $scope.getRowPrimaryValue(response.data);
        $timeout(function () {
          $scope.highlightPkValue = null;
        }, $scope.highlightTimeout);
      });
    }, function (response) {
      $scope.printErrors(response.data);
    });
  };

  $scope.submitCreate = function (close, redirect) {
    $http.post($scope.config.apiEndpoint, angular.toJson($scope.data.create, true)).then(function (response) {
      AdminToastService.success(i18n['js_ngrest_rm_success']);
      $scope.loadList().then(function () {
        $scope.applySaveCallback();

        if (close) {
          $scope.switchTo(0, true);
        }

        $scope.resetData();
        $scope.highlightPkValue = $scope.getRowPrimaryValue(response.data);
        $timeout(function () {
          $scope.highlightPkValue = null;
        }, $scope.highlightTimeout);

        if (redirect) {
          $scope.toggleUpdate(response.data.id);
        }
      });
    }, function (data) {
      $scope.printErrors(data.data);
    });
  };

  $scope.printErrors = function (data) {
    angular.forEach(data, function (value, key) {
      AdminToastService.error(value.message);
    });
  };

  $scope.resetData = function () {
    $scope.data.create = angular.copy({});
    $scope.data.update = angular.copy({});
  };

  $scope.changeNgRestFilter = function () {
    $http.post('admin/api-admin-common/ngrest-filter', {
      'apiEndpoint': $scope.config.apiEndpoint,
      'filterName': $scope.config.filter
    }, {
      ignoreLoadingBar: true
    });
    $scope.loadList(1);
  };
  /*** PAGINIATION ***/


  $scope.pager = {
    'currentPage': 1,
    'pageCount': 1,
    'perPage': 0,
    'totalItems': 0
  };
  $scope.$watch('pager.currentPage', function (newVal, oldVal) {
    if (newVal === oldVal || newVal == undefined || newVal == null) {// do nothing
    } else {
      $scope.loadList(newVal);
    }
  }, true);

  $scope.setPagination = function (currentPage, pageCount, perPage, totalItems) {
    $scope.totalRows = totalItems;
    $scope.pager = {
      'currentPage': parseInt(currentPage),
      'pageCount': pageCount,
      'perPage': perPage,
      'totalItems': totalItems
    };
  };
  /***** TOGGLER PLUGIN *****/


  $scope.toggleStatus = function (row, fieldName, fieldLabel, bindValue) {
    var invertValue = !bindValue;
    var invert = invertValue ? 1 : 0;
    var rowId = row[$scope.config.pk];
    var json = {};
    json[fieldName] = invert;
    $http.put($scope.config.apiEndpoint + '/' + rowId + '?ngrestCallType=update&fields=' + fieldName, angular.toJson(json, true)).then(function (response) {
      row[fieldName] = invert;
      AdminToastService.success(i18nParam('js_ngrest_toggler_success', {
        field: fieldLabel
      }));
    }, function (data) {
      $scope.printErrors(data);
    });
  };
  /**** SORTABLE PLUGIN ****/


  $scope.sortableUp = function (index, row, fieldName) {
    var switchWith = $scope.data.listArray[index - 1];
    $scope.data.listArray[index - 1] = row;
    $scope.data.listArray[index] = switchWith;
    $scope.updateSortableIndexPositions(fieldName);
  };

  $scope.sortableDown = function (index, row, fieldName) {
    var switchWith = $scope.data.listArray[index + 1];
    $scope.data.listArray[index + 1] = row;
    $scope.data.listArray[index] = switchWith;
    $scope.updateSortableIndexPositions(fieldName);
  };

  $scope.updateSortableIndexPositions = function (fieldName) {
    angular.forEach($scope.data.listArray, function (value, key) {
      var json = {};
      json[fieldName] = key;
      var pk = $scope.getRowPrimaryValue(value);
      $http.put($scope.config.apiEndpoint + '/' + pk + '?ngrestCallType=update&fields=' + fieldName, angular.toJson(json, true), {
        ignoreLoadingBar: true
      });
    });
  };
  /***** LIST LOADERS ********/

  /**
   * This method is triggerd by the crudLoader directive to reload service data.
   */


  $scope.loadService = function () {
    $scope.initServiceAndConfig();
  };

  $scope.evalSettings = function (settings) {
    if (settings.hasOwnProperty('order')) {
      $scope.config.orderBy = settings['order'];
    }

    if (settings.hasOwnProperty('filterName')) {
      $scope.config.filter = settings['filterName'];
    }
  };

  $scope.getRowPrimaryValue = function (row) {
    var pk = $scope.config.pk;

    if (angular.isArray(pk)) {
      var values = [];
      angular.forEach(pk, function (name) {
        values.push(row[name]);
      });
      return values.join();
    }

    return row[$scope.config.pk];
  };

  $scope.tagsFilterIds = [];

  $scope.isTagFilterActive = function (tagId) {
    if ($scope.tagsFilterIds.indexOf(tagId) == -1) {
      return false;
    }

    return true;
  };

  $scope.toggleTagFilter = function (tagId) {
    var index = $scope.tagsFilterIds.indexOf(tagId);

    if (index == -1) {
      $scope.tagsFilterIds.push(tagId);
    } else {
      $scope.tagsFilterIds.splice(index, 1);
    }

    $scope.loadList();
  };

  $scope.initServiceAndConfig = function () {
    var deferred = $q.defer();
    $http.get($scope.config.apiEndpoint + '/services?' + $scope.config.apiServicesQueryString).then(function (serviceResponse) {
      $scope.service = serviceResponse.data.service;
      $scope.serviceResponse = serviceResponse.data;
      $scope.evalSettings(serviceResponse.data._settings);

      if ($scope.$parent.notifications && $scope.$parent.notifications.hasOwnProperty($scope.serviceResponse._authId)) {
        delete $scope.$parent.notifications[$scope.serviceResponse._authId];
      }

      deferred.resolve();
    });
    return deferred.promise;
  };

  $scope.toggleNotificationMute = function () {
    $http.post($scope.config.apiEndpoint + '/toggle-notification', {
      'mute': !$scope.serviceResponse._notifcation_mute_state
    }).then(function (response) {
      $scope.initServiceAndConfig();
    });
  };

  $scope.getFieldHelp = function (fieldName) {
    if ($scope.serviceResponse && $scope.serviceResponse['_hints'] && $scope.serviceResponse._hints.hasOwnProperty(fieldName)) {
      return $scope.serviceResponse._hints[fieldName];
    }

    return false;
  };

  $scope.loadList = function (pageId) {
    if (pageId == undefined && $scope.pager) {
      return $scope.reloadCrudList($scope.pager.currentPage);
    } else {
      return $scope.reloadCrudList(pageId);
    }
  };

  $scope.totalRows = 0;
  $scope.requestedImages = [];
  /**
   * Parse an Pagination (or not pagination) object into a response.
   */

  $scope.parseResponseQueryToListArray = function (response) {
    $scope.setPagination(response.headers('X-Pagination-Current-Page'), response.headers('X-Pagination-Page-Count'), response.headers('X-Pagination-Per-Page'), response.headers('X-Pagination-Total-Count'));
    $scope.data.listArray = response.data;
    $scope.requestedImages = [];
    angular.forEach($scope.service, function (value, key) {
      // fix check for lazyload images property for service
      if (value.hasOwnProperty('lazyload_images')) {
        // yes
        angular.forEach(response.data, function (row) {
          $scope.requestedImages.push(row[key]);
        });
      }
    });
    $timeout(function () {
      ServiceImagesData.loadImages($scope.requestedImages).then(function () {
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


  $scope.generateUrlWithParams = function (endpoint, pageId) {
    var url = $scope.config.apiEndpoint + '/' + endpoint + '?' + $scope.config.apiListQueryString;

    if ($scope.config.orderBy) {
      url = url + '&sort=' + $scope.config.orderBy.replace("+", "");
    }

    if (pageId !== undefined) {
      url = url + '&page=' + pageId;
    }

    var query = $scope.config.searchQuery;

    if (query) {
      url = url + '&query=' + query;
    }

    var ids = $scope.tagsFilterIds.join(',');

    if (ids) {
      url = url + '&tags=' + ids;
    }

    return url;
  }; // this method is also used withing after save/update events in order to retrieve current selecter filter data.


  $scope.reloadCrudList = function (pageId) {
    var deferred = $q.defer();

    if (parseInt($scope.config.filter) == 0 || $scope.config.filter === null) {
      if ($scope.config.relationCall) {
        var url = $scope.generateUrlWithParams('relation-call', pageId);
        url = url + '&arrayIndex=' + $scope.config.relationCall.arrayIndex + '&id=' + $scope.config.relationCall.id + '&modelClass=' + $scope.config.relationCall.modelClass;
      } else if ($scope.config.searchQuery) {
        return $scope.generateSearchPromise($scope.config.searchQuery, pageId);
      } else {
        var url = $scope.generateUrlWithParams('list', pageId);
      }

      $http.get(url).then(function (response) {
        deferred.resolve(response);
        $scope.parseResponseQueryToListArray(response);
      });
    } else {
      var url = $scope.generateUrlWithParams('filter', pageId);
      url = url + '&filterName=' + $scope.config.filter;
      $http.get(url).then(function (response) {
        $scope.parseResponseQueryToListArray(response);
        deferred.resolve(response);
      });
    }

    return deferred.promise;
  };

  $scope.service = false;
  /**
   * Someone clicks on the same menu entry.
   */

  $scope.$on('secondMenuClick', function () {
    if ($scope.isInitalized) {
      $scope.loadList();
      $scope.switchTo(0, true);
    }
  });
  /***** CONFIG AND INIT *****/

  $scope.data = {
    create: {},
    update: {},
    aw: {},
    list: {},
    updateId: 0
  };
  $scope.isInitalized = false;
  $scope.$watch('config', function (n, o) {
    $timeout(function () {
      $scope.initServiceAndConfig().then(function () {
        $scope.isInitalized = true;
        $scope.loadList();
      });
    });
  });
}]); // activeWindowController.js

zaa.controller("ActiveWindowTagController", ['$scope', '$http', 'AdminToastService', function ($scope, $http, AdminToastService) {
  $scope.crud = $scope.$parent; // {{ data.aw.itemId }}

  $scope.tags = [];
  $scope.relation = {};
  $scope.newTagName = null;

  $scope.loadTags = function () {
    $http.get($scope.crud.getActiveWindowCallbackUrl('LoadTags')).then(function (transport) {
      $scope.tags = transport.data;
    });
  };

  $scope.loadRelations = function () {
    $http.get($scope.crud.getActiveWindowCallbackUrl('LoadRelations')).then(function (transport) {
      $scope.relation = {};
      transport.data.forEach(function (value, key) {
        $scope.relation[value.tag_id] = 1;
      });
    });
  };

  $scope.saveTag = function () {
    var tagName = $scope.newTagName;

    if (tagName !== "") {
      $scope.crud.sendActiveWindowCallback('SaveTag', {
        'tagName': tagName
      }).then(function (response) {
        if (response.data) {
          $scope.tags.push({
            id: response.data,
            name: tagName
          });
          AdminToastService.success(tagName + ' wurde gespeichert.');
        } else {
          AdminToastService.error(tagName + ' ' + i18n['js_tag_exists']);
        }

        $scope.newTagName = null;
      });
    }
  };

  $scope.saveRelation = function (tag, value) {
    $scope.crud.sendActiveWindowCallback('SaveRelation', {
      'tagId': tag.id,
      'value': value
    }).then(function (response) {
      $scope.relation[tag.id] = response.data;
      AdminToastService.success(i18n['js_tag_success']);
    });
  };

  $scope.$watch(function () {
    return $scope.data.aw.itemId;
  }, function (n, o) {
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

zaa.controller("ActiveWindowGalleryController", ['$scope', '$http', '$filter', function ($scope, $http, $filter) {
  $scope.crud = $scope.$parent;
  $scope.files = [];

  $scope.select = function (id) {
    var exists = $filter('filter')($scope.files, {
      'fileId': id
    }, true);

    if (exists.length == 0) {
      $scope.crud.sendActiveWindowCallback('AddImageToIndex', {
        'fileId': id
      }).then(function (response) {
        var data = response.data;
        $scope.files.push(data);
      });
    }
  };

  $scope.loadImages = function () {
    $http.get($scope.crud.getActiveWindowCallbackUrl('loadAllImages')).then(function (response) {
      $scope.files = response.data;
    });
  };

  $scope.changePosition = function (file, index, direction) {
    var index = parseInt(index);
    var oldRow = $scope.files[index];

    if (direction == 'up') {
      $scope.files[index] = $scope.files[index - 1];
      $scope.files[index - 1] = oldRow;
    } else if (direction == 'down') {
      $scope.files[index] = $scope.files[index + 1];
      $scope.files[index + 1] = oldRow;
    }

    var newRow = $scope.files[index];
    $scope.crud.sendActiveWindowCallback('ChangeSortIndex', {
      'new': newRow,
      'old': oldRow
    });
  };

  $scope.moveUp = function (file, index) {
    $scope.changePosition(file, index, 'up');
  };

  $scope.moveDown = function (file, index) {
    $scope.changePosition(file, index, 'down');
  };

  $scope.remove = function (file, index) {
    $scope.crud.sendActiveWindowCallback('RemoveFromIndex', {
      'imageId': file.originalImageId
    }).then(function (response) {
      $scope.files.splice(index, 1);
    });
  };

  $scope.$watch(function () {
    return $scope.data.aw.itemId;
  }, function (n, o) {
    $scope.loadImages();
  });
}]);
zaa.controller("ActiveWindowGroupAuth", ['$scope', '$http', 'CacheReloadService', function ($scope, $http, CacheReloadService) {
  $scope.crud = $scope.$parent; // {{ data.aw.itemId }}

  $scope.reload = function () {
    CacheReloadService.reload();
  };

  $scope.rights = [];
  $scope.auths = [];

  $scope.save = function (data) {
    $scope.crud.sendActiveWindowCallback('saveRights', {
      'data': data
    }).then(function (response) {
      $scope.getRights();
      $scope.reload();
    });
  };

  $scope.clearModule = function (items) {
    angular.forEach(items, function (value) {
      $scope.rights[value.id] = {
        base: 0,
        create: 0,
        update: 0,
        delete: 0
      };
    });
  };

  $scope.toggleModule = function (items) {
    angular.forEach(items, function (value) {
      $scope.rights[value.id] = {
        base: 1,
        create: 1,
        update: 1,
        delete: 1
      };
    });
  };

  $scope.toggleGroup = function (id) {
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

  $scope.toggleAll = function () {
    angular.forEach($scope.auths, function (items) {
      angular.forEach(items, function (value) {
        $scope.rights[value.id] = {
          base: 1,
          create: 1,
          update: 1,
          'delete': 1
        };
      });
    });
  };

  $scope.untoggleAll = function () {
    angular.forEach($scope.auths, function (items) {
      angular.forEach(items, function (value) {
        $scope.rights[value.id] = {
          base: 0,
          create: 0,
          update: 0,
          'delete': 0
        };
      });
    });
  };

  $scope.getRights = function () {
    $http.get($scope.crud.getActiveWindowCallbackUrl('getRights')).then(function (response) {
      $scope.rights = response.data.rights;
      $scope.auths = response.data.auths;
    });
  };

  $scope.$on('awloaded', function (e, d) {
    $scope.getRights();
  });
  $scope.$watch(function () {
    return $scope.data.aw.itemId;
  }, function (n, o) {
    $scope.getRights();
  });
}]); // DefaultController.js.

zaa.controller("DefaultController", ['$scope', '$http', '$state', '$stateParams', 'CrudTabService', function ($scope, $http, $state, $stateParams, CrudTabService) {
  $scope.moduleId = $state.params.moduleId;

  $scope.loadDashboard = function () {
    $scope.currentItem = null;
    $scope.getDashboard($scope.moduleId);
    return $state.go('default', {
      'moduleId': $scope.moduleId
    });
  };

  $scope.isOpenModulenav = false;
  $scope.items = [];
  $scope.itemRoutes = [];
  $scope.currentItem = null;
  $scope.dashboard = [];

  $scope.itemAdd = function (name, items) {
    $scope.items.push({
      name: name,
      items: items
    });

    for (var i in items) {
      var data = items[i];
      $scope.itemRoutes[data.route] = {
        alias: data.alias,
        icon: data.icon
      };
    }
  };

  $scope.getDashboard = function (nodeId) {
    $http.get('admin/api-admin-menu/dashboard', {
      params: {
        'nodeId': nodeId
      }
    }).then(function (data) {
      $scope.dashboard = data.data;
    });
  };

  $scope.init = function () {
    $scope.get();
    $scope.getDashboard($scope.moduleId);
  };

  $scope.resolveCurrentItem = function () {
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

  $scope.click = function (item) {
    $scope.isOpenModulenav = false;
    $scope.currentItem = item;
    var id = item.route;
    var res = id.split("/");
    CrudTabService.clear();
    $scope.$broadcast('secondMenuClick', {
      item: item
    });
    $state.go('default.route', {
      moduleRouteId: res[0],
      controllerId: res[1],
      actionId: res[2]
    });
  };

  $scope.get = function () {
    $http.get('admin/api-admin-menu/items', {
      params: {
        'nodeId': $scope.moduleId
      }
    }).then(function (response) {
      var data = response.data;

      for (var itm in data.groups) {
        var grp = data.groups[itm];
        $scope.itemAdd(grp.name, grp.items);
      }

      $scope.resolveCurrentItem();
    });
  };

  $scope.hasSubUnreadNotificaton = function (item) {
    if ($scope.$parent.notifications && $scope.$parent.notifications.hasOwnProperty(item.authId)) {
      return $scope.$parent.notifications[item.authId];
    }

    return 0;
  };

  $scope.$on('topMenuClick', function (e) {
    $scope.currentItem = null;
  });
  $scope.init();
}]);
zaa.controller("DashboardController", ['$scope', function ($scope) {
  $scope.logItemOpen = false;
}]);
zaa.filter('lockFilter', function () {
  return function (data, table, pk) {
    var has = false;
    angular.forEach(data, function (value) {
      if (value.lock_table == table && value.lock_pk == pk) {
        has = value;
      }
    });
    return has;
  };
});
zaa.controller("LayoutMenuController", ['$scope', '$document', '$http', '$state', '$timeout', '$window', '$filter', 'HtmlStorage', 'CacheReloadService', 'AdminDebugBar', 'LuyaLoading', 'AdminToastService', 'AdminClassService', function ($scope, $document, $http, $state, $timeout, $window, $filter, HtmlStorage, CacheReloadService, AdminDebugBar, LuyaLoading, AdminToastService, AdminClassService) {
  $scope.AdminClassService = AdminClassService;
  $scope.AdminDebugBar = AdminDebugBar;
  $scope.LuyaLoading = LuyaLoading;
  $scope.toastQueue = AdminToastService.queue;

  $scope.reload = function () {
    CacheReloadService.reload();
  };

  $scope.reload = function (cache) {
    if (cache == false) {
      $window.location.reload();
    } else {
      CacheReloadService.reload();
    }
  };

  $scope.reloadButtonCall = function (key) {
    $http.get('admin/api-admin-common/reload-button-call?key=' + key).then(function (response) {
      AdminToastService.success(response.data.message);
    });
  };
  /* Main nav sidebar toggler */


  $scope.isHover = HtmlStorage.getValue('sidebarToggleState', false);

  $scope.toggleMainNavSize = function () {
    $scope.isHover = !$scope.isHover;
    HtmlStorage.setValue('sidebarToggleState', $scope.isHover);
  };
  /* PROFIL SETTINS */


  $scope.profile = {};
  $scope.settings = {};
  $scope.packages = [];

  $scope.getProfileAndSettings = function () {
    $http.get('admin/api-admin-user/session').then(function (success) {
      $scope.profile = success.data.user;
      $scope.settings = success.data.settings;
      $scope.packages = success.data.packages;
    });
  };
  /* Browser infos */


  $scope.browser = null;

  $scope.detectBrowser = function () {
    $scope.browser = [bowser.name.replace(' ', '-').toLowerCase() + '-' + bowser.version, bowser.mac ? 'mac-os-' + (bowser.osversion ? bowser.osversion : '') : 'windows-' + (bowser.osversion ? bowser.osversion : '')].join(' ');
  };

  $scope.detectBrowser();
  $scope.getProfileAndSettings();
  $scope.debugDetail = null;
  $scope.debugDetailKey = null;

  $scope.loadDebugDetail = function (debugDetail, key) {
    $scope.debugDetail = debugDetail;
    $scope.debugDetailKey = key;
  };

  $scope.closeDebugDetail = function () {
    $scope.debugDetail = null;
    $scope.debugDetailKey = null;
  };

  $scope.notify = null;
  $scope.forceReload = 0;
  $scope.showOnlineContainer = false;

  $scope.searchDetailClick = function (itemConfig, itemData) {
    if (itemConfig.type == 'custom') {
      $scope.click(itemConfig.menuItem).then(function () {
        if (itemConfig.stateProvider) {
          var params = {};
          angular.forEach(itemConfig.stateProvider.params, function (value, key) {
            params[key] = itemData[value];
          });
          $state.go(itemConfig.stateProvider.state, params).then(function () {
            $scope.closeSearchInput();
          });
        } else {
          $scope.closeSearchInput();
        }
      });
    } else {
      $scope.click(itemConfig.menuItem.module).then(function () {
        var res = itemConfig.menuItem.route.split("/");
        $state.go('default.route', {
          moduleRouteId: res[0],
          controllerId: res[1],
          actionId: res[2]
        }).then(function () {
          if (itemConfig.stateProvider) {
            var params = {};
            angular.forEach(itemConfig.stateProvider.params, function (value, key) {
              params[key] = itemData[value];
            });
            $state.go(itemConfig.stateProvider.state, params).then(function () {
              $scope.closeSearchInput();
            });
          } else {
            $scope.closeSearchInput();
          }
        });
      });
    }
  };

  $scope.visibleAdminReloadDialog = false;
  $scope.lastKeyStroke = Date.now();
  $document.bind('keyup', function (e) {
    $scope.lastKeyStroke = Date.now();
  });
  $scope.notifications = [];

  (function tick() {
    $http.post('admin/api-admin-timestamp', {
      lastKeyStroke: $scope.lastKeyStroke
    }, {
      ignoreLoadingBar: true
    }).then(function (response) {
      $scope.forceReload = response.data.forceReload;
      $scope.notifications = response.data.notifications;

      if ($scope.forceReload && !$scope.visibleAdminReloadDialog) {
        $scope.visibleAdminReloadDialog = true;
        AdminToastService.confirm(i18n['js_admin_reload'], i18n['layout_btn_reload'], function () {
          $scope.reload();
          $scope.visibleAdminReloadDialog = false;
        });
      }

      $scope.locked = response.data.locked;
      $scope.notify = response.data.useronline;
      $scope.idleStrokeDashoffset = response.data.idleStrokeDashoffset;
      $scope.idleTimeRelative = response.data.idleTimeRelative;
      $timeout(tick, 20000);
    });
  })();

  $scope.isLocked = function (table, pk) {
    return $filter('lockFilter')($scope.locked, table, pk);
  };

  $scope.getLockedName = function (table, pk) {
    var response = $scope.isLocked(table, pk);
    return response.firstname + ' ' + response.lastname;
  };

  $scope.searchQuery = null;
  $scope.searchInputOpen = false;

  $scope.escapeSearchInput = function () {
    if ($scope.searchInputOpen) {
      $scope.closeSearchInput();
    }
  };

  $scope.toggleSearchInput = function () {
    $scope.searchInputOpen = !$scope.searchInputOpen;
  };

  $scope.openSearchInput = function () {
    $scope.searchInputOpen = true;
  };

  $scope.closeSearchInput = function () {
    $scope.searchInputOpen = false;
  };

  $scope.searchResponse = null;

  $scope.hasUnreadNotificaton = function (item) {
    var authIds = item.authIds;
    var count = 0;
    angular.forEach(authIds, function (value) {
      if (value && $scope.notifications.hasOwnProperty(value)) {
        count = count + parseInt($scope.notifications[value]);
      }
    });
    return count;
  };

  $scope.$watch(function () {
    return $scope.searchQuery;
  }, function (n, o) {
    if (n !== o) {
      if (n.length > 2) {
        $http.get('admin/api-admin-search', {
          params: {
            query: n
          }
        }).then(function (response) {
          $scope.searchResponse = response.data;
        });
      } else {
        $scope.searchResponse = null;
      }
    }
  });
  $scope.items = [];
  $scope.currentItem = {};
  $scope.isOpen = false;

  $scope.click = function (menuItem) {
    $scope.isOpen = false;
    $scope.$broadcast('topMenuClick', {
      menuItem: menuItem
    });

    if (menuItem.template) {
      return $state.go('custom', {
        'templateId': menuItem.template
      });
    } else {
      return $state.go('default', {
        'moduleId': menuItem.id
      });
    }
  };

  $scope.isActive = function (item) {
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
    $http.get('admin/api-admin-menu').then(function (response) {
      $scope.items = response.data;
    });
  };

  $scope.get();
}]);
zaa.controller("AccountController", ['$scope', '$http', '$window', 'AdminToastService', function ($scope, $http, $window, AdminToastService) {
  $scope.pass = {};

  $scope.changePassword = function () {
    $http.post('admin/api-admin-user/change-password', $scope.pass).then(function (response) {
      AdminToastService.success(i18n['aws_changepassword_succes']);
      $scope.pass = {};
    }, function (error) {
      AdminToastService.errorArray(error.data);
      $scope.pass = {};
    });
  };

  $scope.changeSettings = function (settings) {
    $http.post('admin/api-admin-user/change-settings', settings).then(function (response) {
      $window.location.reload();
    });
  };

  $scope.removeDevice = function (device) {
    $http.post('admin/api-admin-user/remove-device', {
      'deviceId': device.id
    }).then(function () {
      AdminToastService.success(i18n['js_account_update_profile_success']);
      $scope.getProfile();
    });
  };

  $scope.profile = {};
  $scope.settings = {};
  $scope.activities = {};
  $scope.email = {};
  $scope.devices = [];
  $scope.twoFa = {};
  $scope.twoFaBackupCode = false;

  $scope.getProfile = function () {
    $http.get('admin/api-admin-user/session').then(function (success) {
      $scope.profile = success.data.user;
      $scope.settings = success.data.settings;
      $scope.activities = success.data.activities;
      $scope.devices = success.data.devices;
      $scope.twoFa = success.data.twoFa;
    });
  };

  $scope.changePersonData = function (data) {
    $http.put('admin/api-admin-user/session-update', data).then(function (success) {
      AdminToastService.success(i18n['js_account_update_profile_success']);
      $scope.getProfile();
    }, function (error) {
      AdminToastService.errorArray(error.data);
    });
  };

  $scope.changeEmail = function () {
    $http.put('admin/api-admin-user/change-email', {
      token: $scope.email.token
    }).then(function (success) {
      AdminToastService.success(i18n['js_account_update_profile_success']);
      $scope.getProfile();
    }, function (error) {
      AdminToastService.errorArray(error.data);
    });
  };

  $scope.registerTwoFa = function () {
    $http.post('admin/api-admin-user/register-twofa', {
      secret: $scope.twoFa.secret,
      verification: $scope.twoFa.verification
    }).then(function (response) {
      $scope.twoFaBackupCode = response.data.backupCode;
      AdminToastService.success(i18n['js_account_update_profile_success']);
      $scope.getProfile();
    }, function (error) {
      AdminToastService.errorArray(error.data);
    });
  };

  $scope.disableTwoFa = function () {
    $http.post('admin/api-admin-user/disable-twofa').then(function () {
      AdminToastService.success(i18n['js_account_update_profile_success']);
      $scope.getProfile();
    });
  };

  $scope.getProfile();
}]);/**
 * LUYA admin scheduler directive.
 * 
 * The scheduler directive will turn any field into an interactive scheduling system.
 * 
 * ```
 * <luya-schedule
 *     value="{{currentValueOfTheEntity}}"
 *     primary-key-value="{{primaryKeyModelValue}}"
 *     model-class="luya\admin\models\User"
 *     attribute-name="is_deleted"
 *     title="Deleted Title"
 *     attribute-values="[{"label":"Draft","value":0},{"label":"Archived","value":2},{"label":"Published","value":1}]"
 * />
 * ```
 * 
 * + value: Its a two-way binding name of field which contains the value
 * + attribute-values: Its a two-way binding name of field which contains the current values to schedule and display based on an array with label and value key.
 * + primary-key-value: Its a two-way binding mame of field which contains the current primary key. Composite keys must be seperated by commans like `1,3`.
 * + model-class: The path to the model class which must impelement NgRestModelInterface
 * + attribute-name: The name of the attribute which should be scheduled.
 * + title: The title of the attribute, like the label.
 * 
 * > Keep in mind to enable the queue fake cronjob or enable a cronjob which runs the queue command.
 * 
 * @since 2.0
 */
zaa.directive("luyaSchedule", function () {
  return {
    restrict: 'E',
    relace: true,
    scope: {
      value: "=",
      attributeValues: "=",
      primaryKeyValue: "=",
      modelClass: "@",
      attributeName: "@",
      onlyIcon: "@",
      title: "@"
    },
    controller: ['$scope', '$http', '$timeout', 'AdminToastService', function ($scope, $http, $timeout, AdminToastService) {
      // toggle window
      $scope.getFirstAttributeKeyAsDefaultValue = function () {
        return $scope.attributeValues[0]['value'];
      };

      $scope.newvalue = $scope.getFirstAttributeKeyAsDefaultValue();
      $scope.isVisible = false;
      $scope.upcomingAccordionOpen = true;
      $scope.archiveAccordionOpen = false;
      $scope.showDatepicker = false;
      $scope.modalPositionClass = "";
      $scope.$watch('showDatepicker', function (newValue) {
        if (newValue === 0) {
          // disable the schedule checkbox, ensure to reset the timestamp.
          var date = new Date();
          $scope.timestamp = date.getTime() / 1000;
        }
      });

      $scope.toggleWindow = function () {
        $scope.isVisible = !$scope.isVisible;

        if ($scope.isVisible) {
          $scope.getLogTable();
        } else {
          $scope.hideInlineModal();
        }
      };

      $scope.escModal = function () {
        if ($scope.isVisible) {
          $scope.isVisible = false;
          $scope.hideInlineModal();
        }
      };

      $scope.getUniqueFormId = function (prefix) {
        return prefix + $scope.primaryKeyValue + '_' + $scope.attributeName;
      }; // get existing job data


      $scope.logs = {
        'upcoming': [],
        'archived': []
      };

      $scope.getLogTable = function (callback) {
        $http.get('admin/api-admin-common/scheduler-log?model=' + $scope.modelClass + '&pk=' + $scope.primaryKeyValue + '&target=' + $scope.attributeName).then(function (response) {
          $scope.logs.archived = [];
          $scope.logs.upcoming = [];
          response.data.forEach(function (entry) {
            if (entry.is_done) {
              $scope.logs.archived.push(entry);
            } else {
              $scope.logs.upcoming.push(entry);
            }
          }); // check if latestId is done, if yes, maybe directly change the value for a given field.

          angular.forEach($scope.logs.archived, function (value, key) {
            if (value.id == $scope.latestId) {
              $scope.value = value.new_attribute_value;
            }
          });
          $timeout(function () {
            $scope.showInlineModal();
          });
        });
      };

      $scope.valueToLabel = function (inputValue) {
        var label;
        angular.forEach($scope.attributeValues, function (value) {
          if (value.value == inputValue) {
            label = value.label;
          }
        });
        return label;
      }; // submit new job


      var now = new Date().getTime() / 1000;
      $scope.latestId;
      $scope.timestamp = parseInt(now);

      $scope.saveNewJob = function () {
        $http.post('admin/api-admin-common/scheduler-add', {
          model_class: $scope.modelClass,
          primary_key: $scope.primaryKeyValue,
          target_attribute_name: $scope.attributeName,
          new_attribute_value: $scope.newvalue,
          schedule_timestamp: $scope.timestamp
        }).then(function (response) {
          $scope.latestId = response.data.id;
          $scope.getLogTable();
        }, function (error) {
          AdminToastService.errorArray(error.data);
        });
      };

      $scope.deleteJob = function (job) {
        $http.delete('admin/api-admin-common/scheduler-delete?id=' + job.id).then(function (response) {
          $scope.getLogTable();
        });
      };
    }],
    link: function link(scope, element, attr) {
      var inlineModal = element.find('.inlinemodal');
      var inlineModalArrow = element.find('.inlinemodal-arrow');
      var button = element.find('.scheduler-btn'); // The spacing the modal has to the window border
      // and button

      var modalMargin = 15; // If the space right to the button is smaller than minSpaceRight
      // and the space to the left is bigger than to the right
      // the modal will be aligned to the left of the button

      var minSpaceRight = 500; // If the space left or right of the button is smaller than minSpace
      // the modal will be display in full width

      var minSpace = 300; // The max width of the modal, defined in the scss component inlinemodal

      var maxWidth = 1000; // Get the button position and align the modal to the right if
      // it hast at least "minSpaceRight" spacing to the right
      // If not, align left

      scope.alignModal = function () {
        var documentSize = {
          width: $(document).width(),
          height: element.parents('.luya-content')[0].scrollHeight || $(document).height()
        };
        var buttonBcr = button[0].getBoundingClientRect();
        var buttonSpaceRight = documentSize.width - (buttonBcr.left + buttonBcr.width + modalMargin * 2);
        var buttonSpaceLeft = buttonBcr.left - modalMargin * 2;
        var alignRight = buttonSpaceRight >= minSpaceRight || buttonSpaceRight >= buttonSpaceLeft;
        var notEnoughSpace = buttonSpaceLeft < minSpace && buttonSpaceRight < minSpace;
        inlineModal.removeClass('inlinemodal--left inlinemodal--right inlinemodal--full');

        if (notEnoughSpace) {
          inlineModal.addClass('inlinemodal--full');
          inlineModal.css({
            display: 'block',
            left: modalMargin,
            right: modalMargin,
            top: modalMargin,
            bottom: modalMargin
          });
        } else if (alignRight) {
          inlineModal.addClass('inlinemodal--right');
          inlineModal.css({
            display: 'block',
            left: buttonBcr.left + buttonBcr.width + modalMargin,
            right: modalMargin,
            width: 'auto'
          });
        } else {
          inlineModal.addClass('inlinemodal--left');
          inlineModal.css({
            display: 'block',
            left: buttonBcr.left > maxWidth ? 'auto' : modalMargin,
            right: documentSize.width + modalMargin - buttonBcr.left,
            width: buttonBcr.left > maxWidth ? '100%' : 'auto'
          });
        }

        scope.alignModalArrow();
      }; // Calculate the new top value for the arrow inside the inline modal
      // We also check if the arrow is "inside" the modal and if not
      // set a min or max top value


      scope.alignModalArrow = function () {
        var modalBcr = inlineModal[0].getBoundingClientRect();
        var buttonBcr = button[0].getBoundingClientRect();
        var arrowHeight = inlineModalArrow.outerHeight();
        var newTop = buttonBcr.top - modalMargin - arrowHeight / 2; // 7.5 equals the height of the arrow / 2

        var topMin = 10;
        var topMax = modalBcr.height - modalMargin - arrowHeight / 2 - 10;

        if (newTop <= topMin) {
          newTop = topMin;
        } else if (newTop >= (topMax > 0 ? topMax : 5000)) {
          // Top max might be below 0 if the modal bcr is 0
          newTop = topMax;
        }

        inlineModalArrow.css({
          top: newTop
        });
      };

      scope.showInlineModal = function () {
        scope.alignModal();
      };

      scope.hideInlineModal = function () {
        inlineModal.css({
          display: 'none'
        });
      };

      var w = angular.element(window);
      w.bind('resize', function () {
        if (scope.isVisible) {
          scope.alignModal();
        }
      });
      $(window).on('scroll', function () {
        if (scope.isVisible) {
          scope.alignModalArrow();
        }
      });
      element.parents().on('scroll', function () {
        if (scope.isVisible) {
          scope.alignModalArrow();
        }
      });
    },
    template: function template() {
      return '<div class="scheduler" ng-class="{\'inlinemodal--open\' : isVisible}">' + '<button ng-click="toggleWindow()" type="button" class="scheduler-btn btn btn-link">' + '<i class="material-icons">schedule</i><span ng-hide="onlyIcon">{{valueToLabel(value)}}</span>' + '</button>' + '<div class="inlinemodal" style="display: none;" ng-class="modalPositionClass" zaa-esc="escModal()">' + '<div class="inlinemodal-inner">' + '<div class="inlinemodal-head clearfix">' + '<div class="modal-header">' + '<h5 class="modal-title">{{title}}</h5>' + '<div class="modal-close">' + '<button type="button" class="close" aria-label="Close" ng-click="toggleWindow()">' + '<span aria-hidden="true"><span class="modal-esc">ESC</span> &times;</span>' + '</button>' + '</div>' + '</div>' + '</div>' + '<div class="inlinemodal-content">' + '<div class="clearfix">' + '<zaa-select model="newvalue" options="attributeValues" label="' + i18n['js_scheduler_new_value'] + '"></zaa-select>' + '<zaa-checkbox model="showDatepicker" fieldid="{{getUniqueFormId(\'datepicker\')}}" label="' + i18n['js_scheduler_show_datepicker'] + '"></zaa-checkbox>' + '<zaa-datetime ng-show="showDatepicker" model="timestamp" label="' + i18n['js_scheduler_time'] + '"></zaa-datetime>' + '<button type="button" class="btn btn-save btn-icon float-right" ng-click="saveNewJob()">' + i18n['js_scheduler_save'] + '</button>' + '</div>' + '<div class="card mt-4" ng-class="{\'card-closed\': !upcomingAccordionOpen}" ng-hide="logs.upcoming.length <= 0">' + '<div class="card-header" ng-click="upcomingAccordionOpen=!upcomingAccordionOpen">' + '<span class="material-icons card-toggle-indicator">keyboard_arrow_down</span>' + '<i class="material-icons">alarm</i>&nbsp;<span> ' + i18n['js_scheduler_title_upcoming'] + '</span><span class="badge badge-secondary float-right">{{logs.upcoming.length}}</span>' + '</div>' + '<div class="card-body p-2">' + '<div class="table-responsive">' + '<table class="table table-hover table-align-middle">' + '<thead>' + '<tr>' + '<th>' + i18n['js_scheduler_table_newvalue'] + '</th>' + '<th>' + i18n['js_scheduler_table_timestamp'] + '</th>' + '<th></th>' + '</tr>' + '</thead>' + '<tbody>' + '<tr ng-repeat="log in logs.upcoming">' + '<td>{{valueToLabel(log.new_attribute_value)}}</td>' + '<td>{{log.schedule_timestamp*1000 | date:\'short\'}}</td>' + '<td style="width: 60px;"><button type="button" class="btn btn-delete btn-icon" ng-click="deleteJob(log)"></button></td>' + '</tr>' + '</tbody>' + '</table>' + '</div>' + '</div>' + '</div>' + '<div class="card mt-3" ng-class="{\'card-closed\': !archiveAccordionOpen}" ng-hide="logs.archived.length <= 0">' + '<div class="card-header" ng-click="archiveAccordionOpen=!archiveAccordionOpen">' + '<span class="material-icons card-toggle-indicator">keyboard_arrow_down</span>' + '<i class="material-icons">alarm_on</i>&nbsp;<span> ' + i18n['js_scheduler_title_completed'] + '</span><span class="badge badge-secondary float-right">{{logs.archived.length}}</span>' + '</div>' + '<div class="card-body p-2">' + '<div class="table-responsive">' + '<table class="table table-hover table-align-middle">' + '<thead>' + '<tr>' + '<th>' + i18n['js_scheduler_table_newvalue'] + '</th>' + '<th>' + i18n['js_scheduler_table_timestamp'] + '</th>' + '</tr>' + '</thead>' + '<tbody>' + '<tr ng-repeat="log in logs.archived">' + '<td>{{valueToLabel(log.new_attribute_value)}}</td>' + '<td>{{log.schedule_timestamp*1000 | date:\'short\'}}</td>' + '</tr>' + '</tbody>' + '</table>' + '</div>' + '</div>' + '</div>' + '</div>' + '</div>' + '<div class="inlinemodal-arrow"></div>' + '</div>' + '</div>';
    }
  };
});