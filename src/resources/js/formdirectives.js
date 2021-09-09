/** ZAA ANGULAR FORM INPUT DIRECTIVES */

/**
 * Generates form input types based on ZAA Directives.
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
        link: function ($scope, $element) {
            var elmn = $compile(angular.element('<' + $scope.dir + ' options="options" initvalue="{{initvalue}}" fieldid="{{fieldid}}" placeholder="{{placeholder}}" autocomplete="{{autocomplete}}" model="model" label="{{label}}" i18n="{{grid}}" />'))($scope);
            $element.replaceWith(elmn);
        },
    }
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

            $scope.$watch(function () { return $scope.model }, function (n, o) {
                if (n === undefined) {
                    $scope.model = [];
                }
            });

            $scope.$watch(function () { return $scope.options }, function (n, o) {
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
                    if (value.value === option.value) {
                        match = true;
                    }
                });

                if (!match) {
                    $scope.model.push({ 'value': option.value, 'label': option.label });
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
                    if (value.value === item.value) {
                        match = true;
                    }
                });

                return !match;
            }
        }],
        template: function () {
            return '' +
                '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' +
                    '<div class="form-side form-side-label">' +
                        '<label for="{{id}}">{{label}}</label>' +
                    '</div>' +
                    '<div class="form-side">' +
                        '<div class="list">' +
                            '<div class="list-item" ng-repeat="(key, item) in getModelItems() track by key">' +
                                '<div class="list-buttons">' +
                                    '<i ng-show="!$first" ng-click="moveUp(key)" class="material-icons" style="transform: rotate(270deg);">play_arrow</i>' +
                                    '<i ng-show="!$last" ng-click="moveDown(key)" class="material-icons" style="transform: rotate(90deg);">play_arrow</i>' +
                                '</div>' +
                                '<span>{{item.label}}</span>' +
                                '<div class="float-right">' +
                                    '<i ng-click="removeFromModel(key)" class="material-icons">delete</i>' +
                                '</div>' +
                            '</div>' +
                            '<div class="list-item" ng-show="sourceData.length != model.length">' +
                                '<input class="form-control" type="search" ng-model="searchString" ng-focus="dropdownOpen = true" />' +
                                '<ul class="list-group">' +
                                    '<li class="list-group-item list-group-item-action" ng-repeat="option in getSourceOptions() | filter:searchString" ng-show="dropdownOpen && elementInModel(option)" ng-click="addToModel(option)">' +
                                        '<i class="material-icons">add_circle</i><span>{{ option.label }}</span>' +
                                    '</li>' +
                                '</ul>' +
                                '<div class="list-chevron">' +
                                    '<i ng-click="dropdownOpen=!dropdownOpen" class="material-icons" ng-show="dropdownOpen">arrow_drop_up</i>' +
                                    '<i ng-click="dropdownOpen=!dropdownOpen" class="material-icons" ng-show="!dropdownOpen">arrow_drop_down</i>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>';
        }
    }
});



/**
 * Generate an array of tag ids which are selected from the list of tags.
 * 
 * An example content of model could be `var model = [1,3,4]` where values are the TAG IDs.
 * 
 * @since 2.2.1
 */
zaa.directive("zaaTagArray", function() {
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

            $http.get('admin/api-admin-common/tags').then(function(response) {
                angular.forEach(response.data, function(value) {
                    value.id = parseInt(value.id);
                    $scope.tags.push(value);
                });
            });

            if ($scope.model === undefined) {
                $scope.model = [];
            } else {
                angular.forEach($scope.model, function(value, key) {
                    $scope.model[key] = parseInt(value);
                });
            }

            $scope.isInSelection = function(id) {
                id = parseInt(id);
                if ($scope.model.indexOf(id) === -1) {
                    return false;
                }

                return true;
            };

            $scope.toggleSelection = function(id) {
                var i = $scope.model.indexOf(id);
                if (i > -1) {
                    $scope.model.splice(i, 1);
                } else {
                    $scope.model.push(id);
                }
            };
        }],
        template: function () {
            return '' +
                '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' +
                    '<div class="form-side form-side-label">' +
                        '<label for="{{id}}">{{label}}</label>' +
                    '</div>' +
                    '<div class="form-side">' +
                        '<span ng-click="toggleSelection(tag.id)" ng-repeat="tag in tags" ng-class="{\'badge-primary\' : isInSelection(tag.id), \'badge-secondary\' : !isInSelection(tag.id)}" class="badge badge-pill mx-1 mb-2">{{tag.name}}</span>' +
                    '</div>' +
                '</div>';
        }
    }
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
        template: function () {
            return '' +
                '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' +
                    '<div class="form-side form-side-label">' +
                        '<label for="{{id}}">{{label}}</label>' +
                    '</div>' +
                    '<div class="form-side">' +
                        '<div ng-if="!isEmpty(data.model)">' +
                            '<div class="link-selector">' +
                                '<div class="link-selector-actions">' +
                                    '<div class="link-selector-btn btn btn-secondary" ng-click="data.modalState=0">' +
                                        '<i class="material-icons left">insert_link</i>' +
                                        '<span>' + i18n['js_link_change_value'] + '</span>' +
                                    '</div>' +
                                    '<span ng-hide="model | isEmpty" class="link-selector-reset" ng-click="unset()"><i class="material-icons">remove_circle</i></span>' +
                                '</div>' +
                                '<link-object-to-string class="ml-2" link="model"></link-object-to-string>' +
                            '</div>' +
                        '</div>' +
                        '<div ng-if="isEmpty(data.model)">' +
                            '<div class="link-selector">' +
                                '<div class="link-selector-actions">' +
                                    '<div class="link-selector__btn btn btn-secondary" ng-click="data.modalState=0">' +
                                        '<i class="material-icons left">insert_link</i>' +
                                        '<span>' + i18n['js_link_set_value'] + '</span>' +
                                    '</div>' +
                                    '<span style="margin-left:10px;">' + i18n['js_link_not_set'] + '</span>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                        '<modal is-modal-hidden="data.modalState" modal-title="{{label}}">' +
                            '<form ng-submit="data.modalState=1">' +
                                '<zaa-link-options data="data.model" uid="id" ng-if="!data.modalState"></zaa-link-options>' +
                                '<button ng-click="data.modalState=1" class="btn btn-icon btn-save" type="submit">' + i18n['js_link_set_value'] + '</button>' +
                            '</form>' +
                        '</modal>' +
                    '</div>' +
                '</div>';
        }
    }
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
zaa.directive("zaaLinkOptions", function() {
    return {
        restrict : 'EA',
        scope : {
            data : '=',
            uid : '='
        },
        templateUrl : 'linkoptions.html',
        controller : ['$scope', function($scope) {
            $scope.$watch(function() { return $scope.data }, function(n, o) {
                if (angular.isArray(n)) {
                    $scope.data = {};
                }
            });
        }]
    }
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
                if (n !== o) {
                    $scope.model = $filter('slugify')(n);
                }
            });

        }],
        template: function () {
            return '' +
                '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' +
                    '<div class="form-side form-side-label">' +
                        '<label for="{{id}}">{{label}}</label>' +
                    '</div>' +
                    '<div class="form-side">' +
                        '<input id="{{id}}" insert-paste-listener ng-model="model" type="text" class="form-control" placeholder="{{placeholder}}" />' +
                    '</div>' +
                '</div>';
        }
    }
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
                    var yiq = ((r * 299) + (g * 587) + (b * 114)) / 1000;
                    return (yiq >= 128) ? '#000' : '#fff';
                }

                return '#000';
            }

            $scope.textColor = getTextColor();

            $scope.$watch(function () { return $scope.model; }, function (n, o) {
                $scope.textColor = getTextColor();
            });
        }],
        template: function () {
            return '' +
                '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' +
                    '<div class="form-side form-side-label">' +
                        '<label for="{{id}}">{{label}}</label>' +
                    '</div>' +
                    '<div class="form-side">' +
                        '<div class="colorwheel">' +
                            '<div class="colorwheel-background" style="background-color: {{model}};">' +
                                '<input class="colorwheel-input" type="text" ng-model="model" style="color: {{textColor}}; border-color: {{textColor}};" maxlength="7" />' +
                            '</div>' +
                            '<div class="colorwheel-wheel">' +
                                '<div ng-colorwheel="{ size: 150, segments: 120 }" ng-model="model">' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>';
        }
    }
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
        template: function () {
            return '' +
                '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' +
                    '<div class="form-side form-side-label">' +
                        '<label for="{{id}}">{{label}}</label>' +
                    '</div>' +
                    '<div class="form-side">' +
                        '<ng-wig ng-disabled="false" ng-model="model" buttons="bold, italic, link, list1, list2" source-mode-allowed></ng-wig>' +
                    '</div>' +
                '</div>';
        }
    }
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
        link: function ($scope) {
            $scope.$watch(function () { return $scope.model }, function (n, o) {
                if (n === undefined) {
                    $scope.model = parseInt($scope.initvalue);
                }
                if (angular.isNumber($scope.model)) {
                    $scope.isValid = true;
                } else {
                    $scope.isValid = false;
                }
            })
        }, template: function () {
            return '' +
                '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' +
                    '<div class="form-side form-side-label">' +
                        '<label for="{{id}}">{{label}}</label>' +
                    '</div>' +
                    '<div class="form-side">' +
                        '<input id="{{id}}" ng-model="model" type="number" min="0" class="form-control" ng-class="{\'invalid\' : !isValid }" placeholder="{{placeholder}}" />' +
                    '</div>' +
                '</div>';
        }
    }
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
        link: function ($scope) {
            $scope.$watch(function () { return $scope.model }, function (n, o) {
                if (angular.isNumber($scope.model)) {
                    $scope.isValid = true;
                } else {
                    $scope.isValid = false;
                }
            })
        },
        template: function () {
            return '' +
                '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' +
                    '<div class="form-side form-side-label">' +
                        '<label for="{{id}}">{{label}}</label>' +
                    '</div>' +
                    '<div class="form-side">' +
                        '<input id="{{id}}" ng-model="model" type="number" min="0" step="{{steps}}" class="form-control" ng-class="{\'invalid\' : !isValid }" placeholder="{{placeholder}}" />' +
                    '</div>' +
                '</div>';
        }
    }
});

/**
 * Generates a form group with simple text input. Mostly used in LUYA admin when create or update CRUD record.
 *
 * Usage:
 *
 * ```
 * <zaa-text model="itemCopy.title" label="<?= Module::t('view_index_page_title'); ?>"></zaa-text>
 * ```
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

        template: function () {
            return '' +
                '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' +
                    '<div class="form-side form-side-label">' +
                        '<label for="{{id}}">{{label}}</label>' +
                    '</div>' +
                    '<div class="form-side">'+
                        '<luya-text ng-model="model" fieldid="{{id}}" autocomplete="{{autocomplete}}" placeholder="{{placeholder}}"></luya-text>' +
                    '</div>' +
                '</div>';
        },
    }
});

/**
 * Generates a simple text input which is styled like the rest LUYA admin UI elements.
 *
 * Usage:
 * ```
 * <luya-text ng-model="someAngularJsModel"></luya-text>
 * ```
 */
zaa.directive("luyaText", function () {
    return {
        restrict: "E",
        scope: {
            "model": "=ngModel",
            "id": "@fieldid",
            "autocomplete": "@autocomplete",
            "placeholder": "@placeholder"
        },
        template: function () {
            return '<input id="{{id}}" insert-paste-listener ng-model="model" type="text" class="form-control" autocomplete="{{autocomplete}}" placeholder="{{placeholder}}" />';
        }
    }
});




/**
 * Generates a form group with read-only text. Mostly used in LUYA admin when create or update CRUD record.
 *
 * Usage:
 *
 * ```
 * <zaa-readonly model="itemCopy.title" label="<?= Module::t('view_index_page_title'); ?>"></zaa-readonly>
 * ```
 * @since 1.2.1
 */
zaa.directive("zaaReadonly", function () {
    return {
        restrict: "E",
        scope: {
            "model": "=",
            "label": "@label",
            "i18n": "@i18n",
            "id": "@fieldid",
        },

        template: function () {
            return '' +
                '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' +
                    '<div class="form-side form-side-label">' +
                        '<label for="{{id}}">{{label}}</label>' +
                    '</div>' +
                    '<div class="form-side">'+
                        '<luya-readonly ng-model="model" fieldid="{{id}}"></luya-readonly>' +
                    '</div>' +
                '</div>';
        },
    }
});

/**
 * Renders a value from model like a read-only attribute.
 *
 * Usage:
 * ```
 * <luya-readonly ng-model="someAngularJsModel"></luya-readonly>
 * ```
 */
zaa.directive("luyaReadonly", function () {
    return {
        restrict: "E",
        scope: {
            "model": "=ngModel",
            "id": "@fieldid",
        },
        template: function () {
            return '<span id="{{id}}" class="text-muted form-control-plaintext">{{model}}</span>';
        }
    }
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
        template: function () {
            return '' +
                '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' +
                    '<div class="form-side form-side-label">' +
                        '<label for="{{id}}">{{label}}</label>' +
                    '</div>' +
                    '<div class="form-side">' +
                        '<async-value model="model" api="{{api}}" fields="fields"  ng-show="model"></async-value>' +
                        '<button type="button" class="btn btn-icon btn-cancel" ng-click="resetValue()" ng-show="model"></button>' +
                    '</div>' +
                '</div>';
        }
    }
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
        template: function () {
            return '<span ng-bind="value"></span>';
        }
    }
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
        template: function () {
            return '' +
                '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' +
                    '<div class="form-side form-side-label">' +
                        '<label for="{{id}}">{{label}}</label>' +
                    '</div>' +
                    '<div class="form-side">' +
                        '<textarea id="{{id}}" insert-paste-listener ng-model="model" type="text" class="form-control" auto-grow placeholder="{{placeholder}}"></textarea>' +
                    '</div>' +
                '</div>';
        }
    }
});

/**
 * Generates a form group with password input. Mostly used in LUYA admin when create or update CRUD record.
 *
 * Usage:
 *
 * ```
 * <zaa-password model="expression" label="someLabel" fieldid="someId"></zaa-password>
 * ```
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
            "autocomplete": "@autocomplete",
            "inputmode": "@inputmode",
        },

        template: function () {
            return '' +
                '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' +
                    '<div class="form-side form-side-label">' +
                        '<label for="{{id}}">{{label}}</label>' +
                    '</div>' +
                    '<div class="form-side">'+
                        '<luya-password ng-model="model" fieldid="{{id}}" autocomplete="{{autocomplete}}" inputmode="{{inputmode}}"></luya-password>' +
                    '</div>' +
                '</div>';
        },
    }
});


/**
 * Generates a simple password input which is styled like the rest LUYA admin UI elements.
 *
 * Usage:
 * ```
 * <luya-password ng-model="expression"></luya-password>
 * ```
 */
zaa.directive("luyaPassword", function () {
    return {
        restrict: "E",
        scope: {
            "model": "=ngModel",
            "id": "@fieldid",
            "autocomplete": "@autocomplete",
            "inputmode": "@inputmode",
        },

        controller: ['$scope', '$timeout', function ($scope, $timeout) {
            $scope.init = function () {
                if ($scope.autocomplete === undefined || $scope.autocomplete === '') {
                    $scope.autocomplete = 'on';
                }

                if ($scope.inputmode === undefined || $scope.inputmode === '') {
                    $scope.inputmode = 'verbatim';
                }
            };

            $timeout(function () {
                $scope.init();
            });
        }],

        template: function () {
            return '<input id="{{id}}" ng-model="model" type="password" class="form-control" autocomplete="{{autocomplete}}" inputmode="{{inputmode}}" />';
        }
    }
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
                if ($scope.model === undefined || $scope.model === null) {
                    $scope.model = typeCastValue($scope.initvalue);
                }
            };
            $timeout(function () {
                $scope.init();
            });
        }],
        template: function () {
            return '' +
                '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' +
                    '<div class="form-side form-side-label">' +
                        '<label for="{{id}}">{{label}}</label>' +
                    '</div>' +
                    '<div class="form-side">' +
                        '<div ng-repeat="(key, item) in options" class="form-check">' +
                            '<input value="{{item.value}}" type="radio" ng-click="setModelValue(item.value)" ng-checked="item.value == model" name="{{id}}_{{key}}" class="form-check-input" id="{{id}}_{{key}}">' +
                            '<label class="form-check-label" for="{{id}}_{{key}}">' +
                                '{{item.label}}' +
                            '</label>' +
                        '</div>' +
                    '</div>' +
                '</div>';
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
            "clearable" : "<",
        },
        controller: ['$scope', '$timeout', '$rootScope', function ($scope, $timeout, $rootScope) {
            if ($scope.optionsvalue === undefined) {
                $scope.optionsvalue = 'value';
            }
            if ($scope.optionslabel === undefined) {
                $scope.optionslabel = 'label';
            }

            if ($scope.clearable === undefined) {
                $scope.clearable = true;
            }
        }],
        template: function () {
            return  '' +
                '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' +
                    '<div class="form-side form-side-label">' +
                        '<label for="{{id}}">{{label}}</label>' +
                    '</div>' +
                    '<div class="form-side">'+
                        '<luya-select ng-model="model" options="options" fieldid="{{id}}" clearable="clearable" optionsvalue="{{optionsvalue}}" optionslabel="{{optionslabel}}" initvalue="{{initvalue}}"></luya-select>' +
                    '</div>' +
                '</div>';
        }
    }
});

zaa.directive("luyaSelect", function() {
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
            ngChange : "&"
        },
        controller: ['$scope', '$timeout', '$rootScope', function ($scope, $timeout, $rootScope) {

            $scope.isOpen = 0;

            if ($scope.optionsvalue === undefined || $scope.optionsvalue === "") {
                $scope.optionsvalue = 'value';
            }

            if ($scope.optionslabel === undefined || $scope.optionslabel === "") {
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
                $scope.$watch(function () { return $scope.model }, function (n, o) {
                    if (n === undefined || n === null || n === '') {
                        if (angular.isNumber($scope.initvalue)) {
                            $scope.initvalue = typeCastValue($scope.initvalue);
                        }
                        var exists = $scope.valueExistsInOptions(n);

                        if (!exists) {
                            $scope.model = $scope.initvalue;
                        }
                    }
                });

                if ($scope.clearable === undefined) {
                    $scope.clearable = true;
                }
            });

            /* methods */

            $scope.valueExistsInOptions = function (value) {
                var exists = false;
                angular.forEach($scope.options, function (item) {
                    if (value === item[$scope.optionsvalue]) {
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
                    if ($scope.model === item[$scope.optionsvalue]) {
                        defaultLabel = item[$scope.optionslabel];
                    }
                });

                return defaultLabel;
            };

            $scope.hasSelectedValue = function () {
                var modelValue = $scope.model;

                if ($scope.valueExistsInOptions(modelValue) && modelValue !== $scope.initvalue) {
                    return true;
                }

                return false;
            };
        }],
        template: function () {
            return '' +
                '<div class="zaaselect" ng-class="{\'open\':isOpen, \'selected\':hasSelectedValue()}">' +
                    '<select class="zaaselect-select" ng-model="model" id="{{id}}">' +
                        '<option ng-repeat="opt in options" ng-value="opt[optionsvalue]">{{opt[optionslabel]}}</option>' +
                    '</select>' +
                    '<div class="zaaselect-selected">' +
                        '<span class="zaaselect-selected-text" ng-click="toggleIsOpen()">{{getSelectedLabel()}}</span>' +
                        '<i class="material-icons zaaselect-clear-icon" ng-show="clearable" ng-click="setModelValue(initvalue)">clear</i>' +
                        '<i class="material-icons zaaselect-dropdown-icon" ng-click="toggleIsOpen()">keyboard_arrow_down</i>' +
                    '</div>' +
                    '<div class="zaaselect-dropdown">' +
                        '<div class="zaaselect-search">' +
                            '<input class="zaaselect-search-input" type="search" focus-me="isOpen" ng-model="searchQuery" />' +
                        '</div>' +
                        '<div class="zaaselect-overflow" ng-if="isOpen">' +
                            '<div class="zaaselect-item" ng-repeat="opt in options | filter:searchQuery">' +
                                '<span class="zaaselect-label" ng-class="{\'zaaselect-label-active\': opt[optionsvalue] == model}" ng-click="opt[optionsvalue] == model ? false : setModelValue(opt)">{{opt[optionslabel]}}</span>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>';
        }
    }
});


/**
 * Select form based on API Request
 */
zaa.directive("zaaAsyncApiSelect", function () {
    return {
        restrict: "E",
        scope: {
            "model": "=",
            "api":"@api",
            "optionsvalue": "@optionsvalue",
            "optionslabel": "@optionslabel",
            "label": "@label",
            "i18n": "@i18n",
            "id": "@fieldid",
            "initvalue": "@initvalue",
        },
        controller: ['$scope', '$http', '$timeout', function ($scope, $http, $timeout) {
            $scope.options = [];
            if ($scope.optionsvalue === undefined) {
                $scope.optionsvalue = 'id';
            }
            if ($scope.optionslabel === undefined) {
                $scope.optionslabel = 'title';
            }

            $scope.$watch('api', function(apiUrl) {
                $http.get(apiUrl).then(function(value) {
                    const items = [];
                    angular.forEach(value.data, function (item) {
                        items.push({
                            label: item[$scope.optionslabel],
                            value: item[$scope.optionsvalue]
                        })
                    });
                    $scope.options = items;
                })
            })
        }],
        template: function () {
            return '' +
                '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' +
                    '<div class="form-side form-side-label">' +
                        '<label for="{{id}}">{{label}}</label>' +
                    '</div>' +
                    '<div class="form-side">'+
                        '<luya-select ng-model="model" options="options" id="{{id}}" initvalue="{{initvalue}}"></luya-select>' +
                    '</div>' +
                '</div>';
        }
    }
});

/**
 * A selection based on a CRUD view.
 * 
 * <zaa-select-crud options={'route': 'module/controller/index', 'api':'admin/api-module-controller', 'fields':['id','title']}></zaa-select-crud>
 * 
 * @since 3.7.0
 */
zaa.directive("zaaSelectCrud", function() {
    return {
        restrict: "E",
        scope: {
            "model": "=",
            "options": "=",
            "api":"@api",
            "label": "@label",
            "i18n": "@i18n",
            "id": "@fieldid",
            "initvalue": "@initvalue"
        },
        template: function () {
            return '' +
                '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' +
                    '<div class="form-side form-side-label">' +
                        '<label for="{{id}}">{{label}}</label>' +
                    '</div>' +
                    '<div class="form-side">'+
                        '<async-value model="model" api="{{options.api}}" fields="options.fields"></async-value>' +
                        '<crud-loader api="{{options.route}}" model-setter="model" model-selection="1" alias="{{label}}"></crud-loader>' +
                    '</div>' +
                '</div>';
        }
    }
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
                if ($scope.model === undefined || $scope.model === null) {
                    $scope.model = typeCastValue($scope.initvalue);
                }
            };
            $timeout(function () {
                $scope.init();
            });

            $scope.clicker = function() {
                if ($scope.model === $scope.valueTrue) {
                    $scope.model = $scope.valueFalse;
                } else {
                    $scope.model = $scope.valueTrue;
                }
            };
        }],
        template: function () {
            return '' +
                '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' +
                    '<div class="form-side form-side-label">' +
                        '<label for="{{id}}">{{label}}</label>' +
                    '</div>' +
                    '<div class="form-side">' +
                        '<div class="form-check">' +
                            '<input id="{{id}}" ng-true-value="{{valueTrue}}" ng-change="change()" ng-click="clicker()" ng-false-value="{{valueFalse}}" ng-model="model" type="checkbox" class="form-check-input-standalone" ng-checked="model == valueTrue" />' +
                            '<label for="{{id}}"></label>' +
                        '</div>' +
                    '</div>' +
                '</div>';
        }
    }
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

            if ($scope.model === undefined) {
                $scope.model = [];
            }

            $scope.preselectOptionValuesToModel = function (options) {
                angular.forEach(options, function (value) {
                    $scope.model.push({ 'value': value.value });
                });
            };

            $scope.searchString = '';

            $scope.$watch('options', function (n, o) {
                if (n !== undefined && n.hasOwnProperty('items')) {
                    $scope.optionitems = $filter('orderBy')(n.items, 'label');
                    if ($scope.preselect) {
                        $scope.preselectOptionValuesToModel(n.items);
                    }
                }
            });

            $scope.filtering = function () {
                $scope.optionitems = $filter('filter')($scope.options.items, $scope.searchString);
            }

            $scope.toggleSelection = function (value) {
                if ($scope.model === undefined) {
                    $scope.model = [];
                }

                for (var i in $scope.model) {
                    if ($scope.model[i]["value"] === value.value) {
                        $scope.model.splice(i, 1);
                        return;
                    }
                }
                $scope.model.push({ 'value': value.value });
            }

            $scope.isChecked = function (item) {
                for (var i in $scope.model) {
                    if ($scope.model[i]["value"] === item.value) {
                        return true;
                    }
                }
                return false;
            }
        }],
        link: function (scope) {
            scope.random = Math.random().toString(36).substring(7);
        },
        template: function () {
            return '' +
                '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' +
                    '<div class="form-side form-side-label">' +
                        '<label for="{{id}}">{{label}}</label>' +
                    '</div>' +
                    '<div class="form-side">' +
                        '<div class="position-relative mb-3">' +
                            '<div class="input-group">' +
                                '<div class="input-group-prepend">' +
                                    '<div class="input-group-text">' +
                                        '<i class="material-icons">search</i>' +
                                    '</div>' +
                                '</div>' +
                                '<input class="form-control" type="text" ng-change="filtering()" ng-model="searchString" placeholder="' + i18n['ngrest_crud_search_text'] + '">' +
                            '</div>' +
                            '<span class="zaa-checkbox-array-counter badge badge-secondary">{{optionitems.length}} ' + i18n['js_dir_till'] + ' {{options.items.length}}</span>' +
                        '</div>' +
                        '<div class="form-check" ng-repeat="(k, item) in optionitems track by k">' +
                            '<input type="checkbox" class="form-check-input" ng-checked="isChecked(item)" id="{{random}}_{{k}}" ng-click="toggleSelection(item)" />' +
                            '<label for="{{random}}_{{k}}">{{item.label}}</label>' +
                        '</div>' +
                    '</div>' +
                '</div>';
        }
    }
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
            "resetable": "@resetable",
        },
        controller: ['$scope', '$filter', function ($scope, $filter) {

            $scope.isNumeric = function (num) {
                return !isNaN(num)
            }

            $scope.$watch(function () { return $scope.model }, function (n, o) {
                if (n !== null && n !== undefined) {
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
                if (!$scope.isNumeric($scope.hour) || $scope.hour === '') {
                    $scope.hour = "0";
                }

                if (!$scope.isNumeric($scope.min) || $scope.min === '') {
                    $scope.min = "0";
                }

                if (n === 'Invalid Date' || n === "" || n === 'NaN') {
                    $scope.date = null;
                    $scope.model = null;
                } else {
                    var res = n.split(".");
                    if (res.length === 3) {
                        if (res[2].length === 4) {

                            if (parseInt($scope.hour) > 23) {
                                $scope.hour = 23;
                            }

                            if (parseInt($scope.min) > 59) {
                                $scope.min = 59;
                            }

                            var en = res[1] + "/" + res[0] + "/" + res[2] + " " + $scope.hour + ":" + $scope.min;
                            $scope.model = (Date.parse(en) / 1000);
                            $scope.datePickerToggler = false;
                        }
                    }
                }
            }

            $scope.$watch(function () { return $scope.date }, function (n, o) {
                if (n !== o && n !== undefined && n !== null) {
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
        template: function () {
            return '' +
                '<div class="form-group form-side-by-side zaa-datetime" ng-class="{\'input--hide-label\': i18n, \'input--with-time\': model!=null && date!=null}">' +
                    '<div class="form-side form-side-label">' +
                        '<label>{{label}}</label>' +
                    '</div>' +
                    '<div class="form-side form-inline datepicker-wrapper">' +
                        '<datepicker class="input-group input-group--append-clickable" date-set="{{pickerPreselect.toString()}}" date-week-start-day="1" datepicker-toggle="false" datepicker-show="{{datePickerToggler}}" date-format="dd.MM.yyyy">' +
                            '<input class="form-control datepicker-date-input" ng-model="date" type="text" ng-focus="openDatePicker()" />' +
                            '<div class="input-group-append" ng-click="toggleDatePicker()">' +
                                '<div class="input-group-text">' +
                                    '<i class="material-icons" ng-hide="datePickerToggler">date_range</i>' +
                                    '<i class="material-icons" ng-show="datePickerToggler">close</i>' +
                                '</div>' +
                            '</div>' +
                        '</datepicker>' +
                        '<div ng-show="model!=null && date!=null" class="hour-selection">' +
                            '<div class="input-group">' +
                                '<input class="form-control zaa-datetime-hour-input" type="text" ng-model="hour" ng-change="autoRefactor()" />' +
                            '</div>' +
                            '<div class="input-group">' +
                                '<div class="input-group-prepend zaa-datetime-time-colon">' +
                                    '<div class="input-group-text">:</div>' +
                                '</div>' +
                                '<input class="form-control form-control--force-border zaa-datetime-minute-input" type="text" ng-model="min" ng-change="autoRefactor()" />' +
                            '</div>' +
                        '</div>' +
                        '<div ng-show="model && getIsResetable()">' +
                            '<button type="button" ng-click="reset()" class="ml-2 btn btn-icon btn-cancel"></button>' +
                        '</div>' +
                    '</div>' +
                '</div>';
        }
    }
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

            $scope.$watch(function () { return $scope.model }, function (n, o) {

                if (n !== null && n !== undefined) {
                    var datep = new Date(n * 1000);
                    $scope.pickerPreselect = datep;
                    $scope.date = $filter('date')(datep, 'dd.MM.yyyy');
                } else {
                    $scope.date = null;
                    $scope.model = null;
                }
            });

            $scope.refactor = function (n) {
                if (n === 'Invalid Date' || n === "") {
                    $scope.date = null;
                    $scope.model = null;
                } else {
                    var res = n.split(".");
                    if (res.length === 3) {
                        if (res[2].length === 4) {
                            var en = res[1] + "/" + res[0] + "/" + res[2];
                            $scope.model = (Date.parse(en) / 1000);
                            $scope.datePickerToggler = false;
                        }
                    }
                }
            }

            $scope.$watch(function () { return $scope.date }, function (n, o) {
                if (n !== o && n !== undefined && n !== null) {
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
        template: function () {
            return '' +
                '<div class="form-group form-side-by-side zaa-date" ng-class="{\'input--hide-label\': i18n}">' +
                    '<div class="form-side form-side-label">' +
                        '<label>{{label}}</label>' +
                    '</div>' +
                    '<div class="form-side form-inline datepicker-wrapper">' +
                        '<datepicker class="input-group input-group--append-clickable" date-set="{{pickerPreselect.toString()}}" date-week-start-day="1" datepicker-toggle="false" datepicker-show="{{datePickerToggler}}" date-format="dd.MM.yyyy">' +
                            '<input class="form-control datepicker-date-input" ng-model="date" type="text" ng-focus="openDatePicker()" />' +
                            '<div class="input-group-append" ng-click="toggleDatePicker()">' +
                                '<div class="input-group-text">' +
                                    '<i class="material-icons" ng-hide="datePickerToggler">date_range</i>' +
                                    '<i class="material-icons" ng-show="datePickerToggler">close</i>' +
                                '</div>' +
                            '</div>' +
                        '</datepicker>' +
                        '<div ng-show="model && getIsResetable()">' +
                            '<button type="button" ng-click="reset()" class="ml-2 btn btn-icon btn-cancel"></button>' +
                        '</div>' +
                    '</div>' +
                '</div>';
        }
    }
});

zaa.directive("zaaTable", function () {
    return {
        restrict: "E",
        scope: {
            "model": "=",
            "options": "=",
            "label": "@label",
            "i18n": "@i18n",
            "id": "@fieldid",
        },
        controller: ['$scope', function ($scope) {

            if ($scope.model === undefined) {
                $scope.model = [{ 0: '' }];
            }

            $scope.addColumn = function () {
                var len = 0;
                for (var o in $scope.model[0]) {
                    len++;
                }

                for (var i in $scope.model) {
                    $scope.model[i][len] = '';
                }
            }

            $scope.addRow = function () {
                var elmn = $scope.model[0];
                var ins = {};
                for (var i in elmn) {
                    ins[i] = '';
                }

                $scope.model.push(ins);
            }

            $scope.removeColumn = function (key) {
                for (var i in $scope.model) {
                    var item = $scope.model[i];
                    if (item instanceof Array) {
                        item.splice(key, 1);
                    } else {
                        delete item[key];
                    }
                }
            }

            $scope.moveLeft = function (index) {
                index = parseInt(index);
                for (var i in $scope.model) {
                    var oldValue = $scope.model[i][index];
                    $scope.model[i][index] = $scope.model[i][index - 1];
                    $scope.model[i][index - 1] = oldValue;
                }
            }

            $scope.moveRight = function (index) {
                index = parseInt(index);
                for (var i in $scope.model) {
                    var oldValue = $scope.model[i][index];
                    $scope.model[i][index] = $scope.model[i][index + 1];
                    $scope.model[i][index + 1] = oldValue;
                }
            }

            $scope.moveUp = function (index) {
                index = parseInt(index);
                var oldRow = $scope.model[index];
                $scope.model[index] = $scope.model[index - 1];
                $scope.model[index - 1] = oldRow;
            }

            $scope.moveDown = function (index) {
                index = parseInt(index);
                var oldRow = $scope.model[index];
                $scope.model[index] = $scope.model[index + 1];
                $scope.model[index + 1] = oldRow;
            }

            $scope.removeRow = function (key) {
                $scope.model.splice(key, 1);
            }

            $scope.showRightButton = function (index) {
                return (parseInt(index) < Object.keys($scope.model[0]).length - 1);

            }
            $scope.showDownButton = function (index) {
                return (parseInt(index) < Object.keys($scope.model).length - 1);

            }
        }],
        template: function () {
            return '' +
                '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' +
                    '<div class="form-side form-side-label">' +
                        '<label ng-if="label">{{label}}</label>' +
                        '<label ng-if="!label">Table</label>' +
                    '</div>' +
                    '<div class="form-side">' +
                        '<div class="zaa-table-wrapper">' +
                            '<table class="zaa-table table table-bordered">' +
                                '<tbody>' +
                                    '<tr>' +
                                        '<th scope="col" width="35px"></th>' +
                                        '<th scope="col" data-ng-repeat="(hk, hr) in model[0] track by hk" class="zaa-table-buttons">' +
                                            '<div class="btn-group" role="group">' +
                                                '<button type="button" class="btn btn-sm btn-outline-info" ng-click="moveLeft(hk)" ng-if="hk > 0"><i class="material-icons">keyboard_arrow_left</i></button>' +
                                                '<button type="button" class="btn btn-sm btn-outline-info" ng-click="moveRight(hk)" ng-if="showRightButton(hk)"><i class="material-icons">keyboard_arrow_right</i></button>' +
                                                '<button type="button" class="btn btn-sm btn-outline-danger" ng-click="removeColumn(hk)"><i class="material-icons">remove</i></button>' +
                                            '</div>' +
                                        '</th>' +
                                    '</tr>' +
                                    '<tr data-ng-repeat="(key, row) in model track by key">' +
                                        '<td width="35px" scope="row" class="zaa-table-buttons">' +
                                            '<div class="btn-group-vertical" role="group">' +
                                                '<button type="button" class="btn btn-sm btn-outline-info" ng-click="moveUp(key)" ng-if="key > 0"><i class="material-icons">keyboard_arrow_up</i></button>' +
                                                '<button type="button" class="btn btn-sm btn-outline-info" ng-click="moveDown(key)" ng-if="showDownButton(key)"><i class="material-icons">keyboard_arrow_down</i></button>' +
                                                '<button type="button" class="btn btn-sm btn-outline-danger" ng-click="removeRow(key)"><i class="material-icons">remove</i></button>' +
                                            '</div>' +
                                        '</td>' +
                                        '<td data-ng-repeat="(field,value) in row track by field">' +
                                            '<textarea ng-model="model[key][field]" class="zaa-table__textarea"></textarea>' +
                                        '</td>' +
                                    '</tr>' +
                                '</tbody>' +
                            '</table>' +
                            '<button ng-click="addRow()" type="button" class="zaa-table-add-row btn btn-sm btn-success"><i class="material-icons">add</i></button>' +
                            '<button ng-click="addColumn()" type="button" class="zaa-table-add-column btn btn-sm btn-success"><i class="material-icons">add</i></button>' +
                        '</div>' +
                    '</div>' +
                '</div>';
        }
    }
});

zaa.directive("zaaFileUpload", function () {
    return {
        restrict: "E",
        scope: {
            "model": "=",
            "options": "=",
            "label": "@label",
            "i18n": "@i18n",
            "id": "@fieldid",
        },
        template: function () {
            return '' +
                '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' +
                    '<div class="form-side form-side-label">' +
                        '<label>{{label}}</label>' +
                    '</div>' +
                    '<div class="form-side">' +
                        '<storage-file-upload ng-model="model"></storage-file-upload>' +
                    '</div>' +
                '</div>';
        }
    }
});

zaa.directive("zaaImageUpload", function () {
    return {
        restrict: "E",
        scope: {
            "model": "=",
            "options": "=",
            "label": "@label",
            "i18n": "@i18n",
            "id": "@fieldid",
        },
        template: function () {
            return '' +
                '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' +
                    '<div class="form-side form-side-label">' +
                        '<label>{{label}}</label>' +
                    '</div>' +
                    '<div class="form-side">' +
                        '<storage-image-upload options="options" ng-model="model"></storage-image-upload>' +
                    '</div>' +
                '</div>';
        }
    }
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
            "id": "@fieldid",
        },
        link: function (scope, element, attributes) {
            scope.$watch('model', function (newValue, oldValue) {
                if (newValue.length >= 1) {
                    $(element).removeClass('is-empty').addClass('is-not-empty');
                } else {
                    $(element).removeClass('is-not-empty').addClass('is-empty');
                }
            }, true);
        },
        controller: ['$scope', function ($scope) {
            if ($scope.model === undefined) {
                $scope.model = [];
            }

            $scope.add = function () {
                if ($scope.model == null || $scope.model === '' || $scope.model === undefined) {
                    $scope.model = [];
                }
                $scope.model.push({ imageId: 0, caption: '' });
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
                return (parseInt(index) < Object.keys($scope.model).length - 1);

            };

            $scope.isDescriptionEnabled = function() {
                if ($scope.options && $scope.options.hasOwnProperty('description')) {
                    return $scope.options.description
                }

                return true;
            }

            $scope.noFiltersOption = function() {
                if ($scope.options && $scope.options.hasOwnProperty('filter')) {
                    return !$scope.options.filter
                }

                return false;
            }
        }],
        template: function () {
            return '' +
                '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' +
                    '<div class="form-side form-side-label">' +
                        '<label>{{label}}</label>' +
                    '</div>' +
                    '<div class="form-side">' +
                        '<div class="list zaa-file-array-upload">' +
                            '<p class="alert alert-info" ng-hide="model.length > 0">' + i18n['js_dir_no_selection'] + '</p>' +
                            '<div ng-repeat="(key,image) in model track by key" class="list-item">' +
                                '<div class="list-section">' +
                                    '<div class="list-left">' +
                                        '<storage-image-upload ng-model="image.imageId" options="{no_filter: noFiltersOption()}"></storage-image-upload>' +
                                    '</div>' +
                                    '<div class="list-right" ng-show="isDescriptionEnabled()">' +
                                        '<div class="form-group">' +
                                            '<label for="{{image.id}}">' + i18n['js_dir_image_description'] + '</label>' +
                                            '<textarea ng-model="image.caption" id="{{image.id}}" class="zaa-file-array-upload-description form-control" auto-grow></textarea>' +
                                        '</div>' +
                                    '</div>' +
                                '</div>' +
                                '<div class="list-buttons">' +
                                    '<div class="btn-group" role="group">' +
                                        '<button type="button" class="btn btn-sm btn-outline-info" ng-click="moveUp(key)" ng-if="key > 0"><i class="material-icons">keyboard_arrow_up</i></button>' +
                                        '<button type="button" class="btn btn-sm btn-outline-info" ng-click="moveDown(key)" ng-if="showDownButton(key)"><i class="material-icons">keyboard_arrow_down</i></button>' +
                                        '<button type="button" class="btn btn-sm btn-outline-danger" ng-click="remove(key)"><i class="material-icons">remove</i></button>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                            '<button ng-click="add()" type="button" class="btn btn-sm btn-success list-add-button"><i class="material-icons">add</i></button>' +
                        '</div>' +
                    '</div>' +
                '</div>';
        }
    }
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
            "id": "@fieldid",
        },
        controller: ['$scope', '$element', '$timeout', function ($scope, $element, $timeout) {

            if ($scope.model === undefined) {
                $scope.model = [];
            }

            $scope.add = function () {
                if ($scope.model == null || $scope.model === '' || $scope.model === undefined) {
                    $scope.model = [];
                }
                $scope.model.push({ fileId: 0, caption: '' });
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
                return (parseInt(index) < Object.keys($scope.model).length - 1);

            };
        }],
        template: function () {
            return '' +
                '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' +
                    '<div class="form-side form-side-label">' +
                        '<label>{{label}}</label>' +
                    '</div>' +
                    '<div class="form-side">' +
                        '<div class="list zaa-file-array-upload">' +
                            '<p class="alert alert-info" ng-hide="model.length > 0">' + i18n['js_dir_no_selection'] + '</p>' +
                            '<div ng-repeat="(key,file) in model track by key" class="list-item">' +
                                '<div class="list-section" ng-if="file.hiddenStorageUploadSource">' +
                                    '<a ng-href="{{file.hiddenStorageUploadSource}}" target="_blank" class="btn btn-primary">{{file.hiddenStorageUploadName}}</a>' +
                                '</div>' +
                                '<div class="list-section" ng-if="!file.hiddenStorageUploadSource">' +
                                    '<div class="list-left">' +
                                        '<storage-file-upload ng-model="file.fileId"></storage-file-upload>' +
                                    '</div>' +
                                    '<div class="list-right">' +
                                        '<div class="form-group">' +
                                            '<label for="{{file.id}}">' + i18n['js_dir_image_description'] + '</label>' +
                                            '<textarea ng-model="file.caption" id="{{file.id}}" class="zaa-file-array-upload-description form-control" auto-grow></textarea>' +
                                        '</div>' +
                                    '</div>' +
                                '</div>' +
                                '<div class="list-buttons"  ng-if="!file.hiddenStorageUploadSource">' +
                                    '<div class="btn-group" role="group">' +
                                        '<button type="button" class="btn btn-sm btn-outline-info" ng-click="moveUp(key)" ng-if="key > 0"><i class="material-icons">keyboard_arrow_up</i></button>' +
                                        '<button type="button" class="btn btn-sm btn-outline-info" ng-click="moveDown(key)" ng-if="showDownButton(key)"><i class="material-icons">keyboard_arrow_down</i></button>' +
                                        '<button type="button" class="btn btn-sm btn-outline-danger" ng-click="remove(key)"><i class="material-icons">remove</i></button>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                        '<button ng-click="add()" type="button" class="btn btn-sm btn-success list-add-button"><i class="material-icons">add</i></button>' +
                    '</div>' +
                '</div>';
        }
    }
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
            "id": "@fieldid",
        },
        controller: ['$scope', '$timeout', function ($scope, $timeout) {
            $scope.init = function () {
                if ($scope.model === undefined || $scope.model === null) {
                    $scope.model = [];
                } else {
                    angular.forEach($scope.model, function (value, key) {
                        var len = Object.keys(value).length;
                        /* issue #1519: if there are no keys, ensure the item is an object */
                        if (len === 0) {
                            $scope.model[key] = {};
                        }
                    });
                }
            };

            $scope.add = function () {
                if ($scope.model == null || $scope.model === '' || $scope.model === undefined) {
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
        template: function () {
            return '' +
                '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' +
                    '<div class="form-side form-side-label">' +
                        '<label>{{label}}</label>' +
                    '</div>' +
                    '<div class="form-side">' +
                        '<div class="list zaa-multiple-inputs">' +
                            '<p class="alert alert-info" ng-hide="model.length > 0">' + i18n['js_dir_no_selection'] + '</p>' +
                            '<div ng-repeat="(msortKey,row) in model track by msortKey" class="list-item" ng-init="ensureRow(row)">' +
                                '<div ng-repeat="(mutliOptKey,opt) in options track by mutliOptKey">' +
                                    '<zaa-injector dir="opt.type" options="opt.options" fieldid="id-{{msortKey}}-{{mutliOptKey}}" initvalue="{{opt.initvalue}}" label="{{opt.label}}" model="row[opt.var]"></zaa-injector>' +
                                '</div>' +
                                '<div class="list-buttons">' +
                                    '<div class="btn-group" role="group">' +
                                        '<button type="button" class="btn btn-sm btn-outline-info" ng-click="moveUp(msortKey)" ng-if="msortKey > 0"><i class="material-icons">keyboard_arrow_up</i></button>' +
                                        '<button type="button" class="btn btn-sm btn-outline-info" ng-click="moveDown(msortKey)" ng-if="showDownButton(msortKey)"><i class="material-icons">keyboard_arrow_down</i></button>' +
                                        '<button type="button" class="btn btn-sm btn-outline-danger" ng-click="remove(msortKey)"><i class="material-icons">remove</i></button>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                            '<button ng-click="add()" type="button" class="btn btn-sm btn-success list-add-button"><i class="material-icons">add</i></button>' +
                        '</div>' +
                    '</div>' +
                '</div>';
        }
    }
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
            "id": "@fieldid",
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
        template: function () {
            return '' +
                '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' +
                    '<div class="form-side form-side-label">' +
                        '<label>{{label}}</label>' +
                    '</div>' +
                    '<div class="form-side">' +
                        '<div class="list zaa-json-array">' +
                            '<div ng-repeat="(key,value) in model" class="list-item">' +
                                '<div class="input-group">' +
                                    '<div class="input-group-prepend border-right">' +
                                        '<div class="input-group-text text-muted">{{key}}</div>' +
                                    '</div>' +
                                    '<input class="form-control" type="text" ng-model="model[key]" />' +
                                '</div>' +
                                '<div class="list-buttons">' +
                                    '<div class="btn-group" role="group">' +
                                        '<button type="button" class="btn btn-sm btn-outline-danger" ng-click="remove(key)"><i class="material-icons">remove</i></button>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                            '<div class="input-group input-group--append-clickable">' +
                                '<input type="text" class="form-control" placeholder="'+i18n['js_jsonobject_newkey']+'" aria-label="'+i18n['js_jsonobject_newkey']+'" ng-model="newKey">' +
                                '<div class="input-group-append">' +
                                    '<div class="input-group-text" ng-click="add(newKey);newKey=null;">' +
                                        '<i class="material-icons">add</i>' +
                                    '</div>' +
                                '</div>' +
                            '</div>'+
                        '</div>' +
                    '</div>' +
                '</div>';
        }
    }
});

zaa.directive("zaaListArray", function () {
    return {
        restrict: "E",
        scope: {
            "model": "=",
            "options": "=",
            "label": "@label",
            "i18n": "@i18n",
            "id": "@fieldid",
        },
        controller: ['$scope', '$element', '$timeout', function ($scope, $element, $timeout) {

            $scope.init = function () {
                if ($scope.model === undefined || $scope.model === null) {
                    $scope.model = [];
                }
            };

            $scope.add = function () {
                if ($scope.model === null || $scope.model === '' || $scope.model === undefined) {
                    $scope.model = [];
                }
                $scope.model.push({ value: '' });
                $scope.setFocus();
            };

            $scope.remove = function (key) {
                $scope.model.splice(key, 1);
            };

            $scope.refactor = function (key, row) {
                if (key !== ($scope.model.length - 1)) {
                    if (row['value'] === "") {
                        $scope.remove(key);
                    }
                }
            };

            $scope.setFocus = function () {
                $timeout(function () {
                    var input = $element.children('.list').children('.list__item:last-of-type').children('.list__left').children('input');

                    if (input.length === 1) {
                        input[0].focus();
                    }
                }, 50);
            };

            $scope.moveUp = function (index) {
                index = parseInt(index);
                var oldRow = $scope.model[index];
                $scope.model[index] = $scope.model[index - 1];
                $scope.model[index - 1] = oldRow;
            }

            $scope.moveDown = function (index) {
                index = parseInt(index);
                var oldRow = $scope.model[index];
                $scope.model[index] = $scope.model[index + 1];
                $scope.model[index + 1] = oldRow;
            }

            $scope.showDownButton = function (index) {
                return (parseInt(index) < Object.keys($scope.model).length - 1);

            }

            $scope.init();

        }],
        template: function () {
            return '' +
                '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' +
                    '<div class="form-side form-side-label">' +
                        '<label>{{label}}</label>' +
                    '</div>' +
                    '<div class="form-side">' +
                        '<div class="list zaa-file-array-upload">' +
                            '<p class="alert alert-info" ng-hide="model.length > 0">' + i18n['js_dir_no_selection'] + '</p>' +
                            '<div ng-repeat="(key,row) in model track by key" class="list-item">' +
                                '<input class="form-control list-input" type="text" ng-model="row.value" />' +
                                '<div class="list-buttons">' +
                                    '<div class="btn-group" role="group">' +
                                        '<button type="button" class="btn btn-sm btn-outline-info" ng-click="moveUp(key)" ng-if="key > 0"><i class="material-icons">keyboard_arrow_up</i></button>' +
                                        '<button type="button" class="btn btn-sm btn-outline-info" ng-click="moveDown(key)" ng-if="showDownButton(key)"><i class="material-icons">keyboard_arrow_down</i></button>' +
                                        '<button type="button" class="btn btn-sm btn-outline-danger" ng-click="remove(key)"><i class="material-icons">remove</i></button>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                            '<button ng-click="add()" type="button" class="btn btn-sm btn-success list-add-button"><i class="material-icons">add</i></button>' +
                        '</div>' +
                    '</div>' +
                '</div>';
        }
    }
});