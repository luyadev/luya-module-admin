/**
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

zaa.config(['$httpProvider', '$stateProvider', '$controllerProvider', '$urlMatcherFactoryProvider', function($httpProvider, $stateProvider, $controllerProvider, $urlMatcherFactoryProvider) {
    	
        $httpProvider.interceptors.push("authInterceptor");

        // used to bootstrap the angularjs controllers in the view 
        zaa.bootstrap = $controllerProvider;

        $urlMatcherFactoryProvider.strictMode(false)

        /**
         * resolvers: https://github.com/angular-ui/ui-router/wiki#resolve
         */
        $stateProvider
            .state("default", {
                url: "/default/:moduleId",
                templateUrl: function ($stateParams) {
                    return "admin/template/default";
                }
            })
            .state("default.route", {
                url: "/:moduleRouteId/:controllerId/:actionId",
                templateUrl: function ($stateParams) {
                    return $stateParams.moduleRouteId + "/" + $stateParams.controllerId + "/" + $stateParams.actionId;
                },
                parent: 'default',
                resolve: {
                    adminServiceResolver: adminServiceResolver
                }
            })
            .state("custom", {
                url: "/template/:templateId",
                templateUrl: function ($stateParams) {
                    return $stateParams.templateId;
                },
                resolve: {
                    adminServiceResolver: adminServiceResolver,
                    resolverProvider: ['resolver', function (resolver) {
                        return resolver.then;
                    }]
                }
            })
            .state("home", {
                url: "",
                templateUrl: "admin/default/dashboard",
                controller: ['$scope', function($scope) {
                    $scope.$parent.currentItem = {'icon':'home', 'alias': i18n['menu_dashboard']};
                }]
            })
            // ngrest crud detail view
            .state("default.route.detail", {
				url: "/:id",
				parent: 'default.route',
				template: '<ui-view/>',
				controller: ['$scope', '$stateParams', function($scope, $stateParams) {
	
					$scope.crud = $scope.$parent;
	
					$scope.init = function() {
						if (!$scope.crud.config.inline) {
							if ($scope.crud.data.updateId != $stateParams.id) {
								$scope.crud.toggleUpdate($stateParams.id);
							}
						}
					}
	
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
zaa.provider("resolver", [function() {
    var list = [];

    this.addCallback = function (callback) {
        list.push(callback);
    };

    this.$get = ['$injector', '$q', '$state', function ($injector, $q, $state) {
        return $q(function(resolve, reject) {
            for (var i in list) {
                $injector.invoke(list[i]);
            }
        })
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
zaa.factory("LuyaLoading", ['$timeout', function($timeout) {

    var state = false;
    var stateMessage = null;
    var timeoutPromise = null;

    return {
        start: function (myMessage) {
            if (myMessage == undefined) {
                stateMessage = i18n['js_zaa_server_proccess'];
            } else {
                stateMessage = myMessage;
            }
            // rm previous timeouts
            $timeout.cancel(timeoutPromise);

            state = true
            timeoutPromise = $timeout(function () {
                state = true;
            }, 1000);
        },
        stop: function () {
            $timeout.cancel(timeoutPromise);
            state = false;
        },
        getStateMessage: function () {
            return stateMessage;
        },
        getState: function () {
            return state;
        }
    }
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

    service.hasClassSpace = function(spaceName) {
    	 if (service.vars.hasOwnProperty(spaceName)) {
    		 return true;
    	 }
    	 
    	 return false;
    };
    
    service.setClassSpace = function (spaceName, className) {
        service.vars[spaceName] = className;
    };
    
    service.clearSpace = function(spaceName) {
    	if (service.vars.hasOwnProperty(spaceName)) {
    		service.vars[spaceName] = null;
    	}
    };
    
    service.removeSpace = function(spaceName) {
    	if (service.hasClassSpace(spaceName)) {
    		delete service.vars[spaceName];
    	}
    };

    service.stack = 0;
    
    service.modalStackPush = function() {
    	service.stack += 1;
    };
    
    service.modalStackRemove = function() {
    	if (service.stack <= 1) {
    		service.stack = 0; 
    	} else {
    		service.stack -= 1;
    	}
    };
    
    service.modalStackRemoveAll = function() {
    	service.stack = 0;
    };
    
    service.modalStackIsEmpty = function() {
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
    }
    
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
        request: function (config) {
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
        response: function(config) {
        	if (!config.hasOwnProperty('ignoreLoadingBar')) {
        		AdminDebugBar.pushResponse(config);
        	}
        	
        	return config || $q.when(config);
        },
        responseError: function (data) {
            if (data.status == 401 || data.status == 403 || data.status == 405) {
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
}]);
