/** ZAA ANGULAR FORM INPUT DIRECTIVES */


/**
 * @ngdoc directive
 * @name zaaInjector
 * @restrict E
 *
 * @description
 * Generates form input types based on ZAA Directives.
 *
 * @param {expression} dir Name of the injected directive
 * @param {expression} model assignable {@link https://docs.angularjs.org/guide/expression Expression} to bind to.
 * @param {object} options Options object. May contains data and some settings for injected directive.
 * @param {string} label Form group label.
 * @param {string} fieldid The id attribute of the input field.
 * @param {string} grid Is form group represent an i18n attribute.
 * @param {string} placeholder A short hint that describes the expected value of an input field. Used in text-like inputs.
 * @param {string} autocomplete Specifies whether or not an input field should have autocomplete enabled. Used in text-like inputs.
 * @param {string} initvalue The value that will be passed to the model when input. Used in select-like inputs.
 * @param {string} optionslabel The name of the property in data object which corresponds to the data 'label'. Used in select-like inputs.
 * @param {string} optionsvalue The name of the property in data object which corresponds to the data 'value'. Used in select-like inputs.
 *
 *
 * @example
 * <zaa-injector dir="zaa-text" options="{}" fieldid="myFieldId" initvalue="0" label="My Label" model="some.model"></zaa-injector>
 *
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
            "label": "@",
            "grid": "@",
            "fieldid": "@",
            "placeholder": "@",
            "initvalue": "@",
            "autocomplete": "@",
            "optionsvalue": "@",
            "optionslabel": "@"
        },
        link: function ($scope, $element) {
            var elmn = $compile(angular.element('<' + $scope.dir + ' options="options" initvalue="{{initvalue}}" fieldid="{{fieldid}}" placeholder="{{placeholder}}" autocomplete="{{autocomplete}}" model="model" label="{{label}}" i18n="{{grid}}" optionsvalue="{{optionsvalue}}" optionslabel="{{optionslabel}}"/>'))($scope);
            $element.replaceWith(elmn);
        },
    }
}]);


/**
 * @ngdoc directive
 * @name zaaSortRelationArray
 * @restrict E
 *
 * @description
 * Generates a form group with a multiselectable and sortable list based on array data.
 *
 * @param {expression} model assignable {@link https://docs.angularjs.org/guide/expression Expression} to bind to. Must be an array.
 * @param {object} options Options object. `sourceData` property is holding the data array: `options.sourceData = [{"value" : 1, "label" => 'Label for Value 1' }, {"value" : 2, "label" => 'Label for Value 2' }]`.
 * @param {string} fieldid The dummy id attribute. The label will point to this id, but such an id will not be associated with any field.
 * @param {expression} i18n Is form group  represent an i18n attribute.
 * @param {string} label Form group label.
 *
 * @example
 * <zaa-sort-relation-array model="some.model" label="SomeLabel" options="{sourceData: [{'label' : 'FirstLabel', 'value' : 'FirstValue'}, {'label' : 'SecondLabel', 'value' : 2}]}"></zaa-sort-relation-array>
 *
 */
zaa.directive("zaaSortRelationArray", function () {
    return {
        restrict: "E",
        scope: {
            "model": "=",
            "options": "=",
            "label": "@",
            "i18n": "@",
            "id": "@fieldid"
        },
        controller: ['$scope', '$filter', function ($scope, $filter) {
            if ($scope.model === undefined) {
                $scope.model = [];
            }
        }],
        template: function () {
            return '' +
                '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' +
                '<div class="form-side form-side-label">' +
                '<label for="{{id}}">{{label}}</label>' +
                '</div>' +
                '<div class="form-side">' +
                '<luya-sort-relation-array ng-model="model" options="options.sourceData"></luya-sort-relation-array>' +
                '</div>' +
                '</div>';
        }
    }
});


/**
 * @ngdoc directive
 * @name luyaSortRelationArray
 * @restrict E
 *
 * @description
 * Generates a multiselectable and sortable list which is styled like the rest LUYA admin UI elements.
 *
 * The output data will be presented as an array of objects in the form [0 => {'label': 'First label', 'value': 'firstValue'}, 1 => {'label': 'First label', 'value': 'firstValue'}].
 * Only selected objects will be included in array. The order of output array is defined by order of list.
 *
 * @param {expression} ngModel assignable {@link https://docs.angularjs.org/guide/expression Expression} to bind to. Must be an array.
 * @param {array} options Data array. options = [{'label' : 'checkboxLabel', 'value' : 'someValue'}, {'label' : 'anotherLabel', 'value' : 123},...];`
 *
 * @example
 * <luya-sort-relation-array  ng-model="some.model" options="[{'label' : 'checkboxLabel', 'value' : 'someValue'}, {'label' : 'anotherLabel', 'value' : 123}]"></luya-sort-relation-array>
 *
 * @todo
 * optionsvalue and optionslabel attributes
 *
 * @since 4.2.0
 */
zaa.directive("luyaSortRelationArray", function () {
    return {
        restrict: "E",
        scope: {
            "model": "=ngModel",
            "options": "=",
        },
        controller: ['$scope', '$filter', function ($scope, $filter) {

            $scope.searchString;

            $scope.sourceData = [];

            $scope.dropdownOpen = false;

            $scope.$watch(function () {
                return $scope.model
            }, function (n, o) {
                if (n === undefined) {
                    $scope.model = [];
                }
            });

            $scope.$watch(function () {
                return $scope.options
            }, function (n, o) {
                if (n !== undefined && n !== null) {
                    $scope.sourceData = n;
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
                    if (value.value == item.value) {
                        match = true;
                    }
                });

                return !match;
            }
        }],
        template: function () {
            return '' +

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
zaa.directive("zaaTagArray", function () {
    return {
        restrict: "E",
        scope: {
            "model": "=",
            "label": "@",
            "i18n": "@",
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

            if ($scope.model === undefined) {
                $scope.model = [];
            } else {
                angular.forEach($scope.model, function (value, key) {
                    $scope.model[key] = parseInt(value);
                });
            }

            $scope.isInSelection = function (id) {
                id = parseInt(id);
                return $scope.model.indexOf(id) !== -1;
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
 * <zaa-link model="some.model"></zaa-link>
 */
zaa.directive("zaaLink", ['$filter', function ($filter) {
    return {
        restrict: "E",
        scope: {
            "model": "=",
            "options": "=",
            "label": "@",
            "i18n": "@",
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
                return $scope.data
            }, function (n, o) {
                if (angular.isArray(n)) {
                    $scope.data = {};
                }
            });
        }]
    }
});


/**
 * @ngdoc directive
 * @name luyaSlug
 * @restrict E
 *
 * @description
 * Generates a form group with text input. The values of the input are automatically 'slugified'. Mostly used in LUYA admin when create or update CRUD record.
 *
 * If a `listener` attribute is provided, the value will be taken from it.
 *
 * @param {expression} model assignable {@link https://docs.angularjs.org/guide/expression Expression} to bind to.
 * @param {expression} listener assignable to get value from.
 * @param {string} fieldid The id attribute of the checkbox.
 * @param {expression} i18n 'Is field represent an i18n attribute' condition
 * @param {string} label Form group label.
 * @param {string} placeholder A short hint that describes the expected value of an input field.
 *
 * @example
 * <zaa-slug model="some.model" listener="another.model" label="Some label"></zaa-slug>
 *
 */
zaa.directive("zaaSlug", function () {
    return {
        restrict: "E",
        scope: {
            "model": "=",
            "options": "=",
            "listener": "=",
            "label": "@",
            "i18n": "@",
            "id": "@fieldid",
            "placeholder": "@"
        },
        template: function () {
            return '' +
                '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' +
                '<div class="form-side form-side-label">' +
                '<label for="{{id}}">{{label}}</label>' +
                '</div>' +
                '<div class="form-side">' +
                '<luya-slug fieldid="{{id}}" provider="listener" ng-model="model" placeholder="{{placeholder}}"></luya-slug>' +
                '</div>' +
                '</div>';
        }
    }
});

/**
 * @ngdoc directive
 * @name luyaSlug
 * @restrict E
 *
 * @description
 * Generates an input text field whose value are automatically converted to slug.
 *
 * If a `provider` attribute is given, the value will be taken from it.
 *
 * @param {expression} ngModel assignable {@link https://docs.angularjs.org/guide/expression Expression} to bind to.
 * @param {expression} provider assignable to get value from.
 * @param {string} fieldid The id attribute of the field.
 * @param {string} placeholder A short hint that describes the expected value of an input field.
 *
 * @example
 * <luya-slug ng-model="some.model" provider="another.model" placeholder="Placeholder"></luya-slug>
 *
 * @since 4.2.0
 */
zaa.directive("luyaSlug", function () {
    return {
        restrict: "E",
        scope: {
            "model": "=ngModel",
            "provider": "=",
            "id": "@fieldid",
            "placeholder": "@"
        },
        controller: ['$scope', '$filter', function ($scope, $filter) {

            $scope.$watch('provider', function (n, o) {
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
                '<input id="{{id}}" insert-paste-listener ng-model="model" type="text" class="form-control" placeholder="{{placeholder}}" />';
        }
    }
});


/**
 * @ngdoc directive
 * @name zaaColor
 * @restrict E
 *
 * @description
 * Generates a form group with color wheel input.
 *
 * @param {expression} model assignable {@link https://docs.angularjs.org/guide/expression Expression} to bind to.
 * @param {object} options Options object. Not used.
 * @param {string} fieldid The dummy id attribute. The label will point to this id, but such an id will not be associated with any field.
 * @param {expression} i18n Is color wheel represent an i18n attribute.
 * @param {string} label Form group label.
 *
 * @example
 * <zaa-color model="some.model"></zaa-color>
 *
 * @see https://github.com/patlux/ng-colorwheel
 *
 */
zaa.directive("zaaColor", function () {
    return {
        restrict: "E",
        scope: {
            "model": "=",
            "options": "=",
            "label": "@",
            "i18n": "@",
            "id": "@fieldid",
            "initvalue": "@"
        },
        controller: ['$scope', function ($scope) {

            if ($scope.model === undefined || !$scope.model) {
                if ($scope.initvalue) {
                    $scope.model = $scope.initvalue
                } else {
                    $scope.model = '#000000';
                }
            }

            function getTextColor() {
                if (typeof $scope.model === 'undefined' || !$scope.model) {
                    if ($scope.initvalue) {
                        return $scope.initvalue
                    }
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

            $scope.$watch(function () {
                return $scope.model;
            }, function (n, o) {
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


/**
 * @ngdoc directive
 * @name zaaWysiwyg
 * @restrict E
 *
 * @description
 * Generates a form group with VERY SIMPLE WYSIWYG text input.
 *
 * @param {expression} model assignable {@link https://docs.angularjs.org/guide/expression Expression} to bind to.
 * @param {object} options Options object. Not used.
 * @param {string} fieldid The dummy id attribute. The label will point to this id, but such an id will not be associated with any field.
 * @param {expression} i18n Is input represent an i18n attribute.
 * @param {string} label Form group label.
 * @param {string} placeholder A short hint that describes the expected value of an input field. Used in text-like inputs.
 *
 * @example
 * <zaa-wysiwyg model="some.model" label="Some label" placeholder="Some hints"></zaa-wysiwyg>
 *
 * @see https://github.com/stevermeister/ngWig
 *
 */
zaa.directive("zaaWysiwyg", function () {
    return {
        restrict: "E",
        scope: {
            "model": "=",
            "options": "=",
            "label": "@",
            "i18n": "@",
            "id": "@fieldid",
            "placeholder": "@"
        },
        template: function () {
            return '' +
                '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' +
                '<div class="form-side form-side-label">' +
                '<label for="{{id}}">{{label}}</label>' +
                '</div>' +
                '<div class="form-side">' +
                '<ng-wig ng-disabled="false" ng-model="model" buttons="bold, italic, link, list1, list2" source-mode-allowed placeholder="{{placeholder}}"></ng-wig>' +
                '</div>' +
                '</div>';
        }
    }
});


/**
 * @ngdoc directive
 * @name stringToInteger
 *
 * @description
 * Converts string to integer number to prevent errors in <input type="number"> field.
 *
 * @see https://code.angularjs.org/1.8.2/docs/error/ngModel/numfmt
 */
zaa.directive('stringToInteger', function () {
    return {
        require: 'ngModel',
        link: function (scope, element, attrs, ngModel) {
            ngModel.$formatters.push(function (value) {
                return parseInt(value);
            });
        }
    };
});

/**
 * @ngdoc directive
 * @name stringToFloat
 *
 * @description
 * Converts string to float number to prevent errors in <input type="number"> field.
 *
 * @see https://code.angularjs.org/1.8.2/docs/error/ngModel/numfmt
 */
zaa.directive('stringToFloat', function () {
    return {
        require: 'ngModel',
        link: function (scope, element, attrs, ngModel) {
            ngModel.$formatters.push(function (value) {
                return parseFloat(value);
            });
        }
    };
});


/**
 * @ngdoc directive
 * @name zaaNumber
 * @restrict E
 *
 * @description
 * Generates a form group with non negative integer number input field. Mostly used in LUYA admin when create or update CRUD record.
 *
 *
 * @param {expression} model assignable {@link https://docs.angularjs.org/guide/expression Expression} to bind to.
 * @param {object} options Options object. Not used.
 * @param {string} fieldid The id attribute of the input.
 * @param {expression} i18n 'Is field represent an i18n attribute' condition.
 * @param {string} label Form group label.
 * @param {string} placeholder A short hint that describes the expected value of an input field.
 * @param {string} initvalue The initial value of the input.
 *
 * @example
 * <zaa-number model="some.model" label="Some label"></zaa-number>
 *
 */
zaa.directive("zaaNumber", function () {
    return {
        restrict: "E",
        scope: {
            "model": "=",
            "options": "=",
            "label": "@",
            "i18n": "@",
            "id": "@fieldid",
            "placeholder": "@",
            "initvalue": "@",
            "min": "@"
        },
        template: function () {
            return '' +
                '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' +
                '<div class="form-side form-side-label">' +
                '<label for="{{id}}">{{label}}</label>' +
                '</div>' +
                '<div class="form-side">' +
                '<luya-number ng-model="model" fieldid="{{id}}" min="{{min}}" placeholder="{{placeholder}}" initvalue="{{initvalue}}"></luya-number>' +
                '</div>' +
                '</div>';
        }
    }
});


/**
 * @ngdoc directive
 * @name luyaNumber
 * @restrict E
 *
 * @description
 * Generates an integer number input field which is styled like the rest LUYA admin UI elements.
 *
 *
 * @param {expression} ngModel assignable {@link https://docs.angularjs.org/guide/expression Expression} to bind to.
 * @param {string} fieldid The id attribute of the field.
 * @param {string} placeholder A short hint that describes the expected value of an input field.
 * @param {string} initvalue The initial value of the input.
 * @param {string} min The minimal allowed value of the input.
 * @param {string} max The maximal allowed value of the input.
 *
 * @example
 * <luya-number ng-model="some.model" min="-3" max="10" placeholder="Placeholder" initvalue="0"></luya-number>
 *
 * @since 4.2.0
 */
zaa.directive("luyaNumber", function () {
    return {
        restrict: "E",
        scope: {
            "model": "=ngModel",
            "id": "@fieldid",
            "placeholder": "@",
            "initvalue": "@",
            "min": "@",
            "max": "@"
        },
        link: function ($scope) {
            $scope.$watch(function () {
                return $scope.model
            }, function (n, o) {
                if (n === undefined) {
                    $scope.model = parseInt($scope.initvalue);
                }

                if ($scope.model == parseInt(n)) {
                    $scope.model = parseInt(n);
                }

                $scope.isValid = !!angular.isNumber($scope.model);
            })
        }, template: function () {
            return '' +
                '<input string-to-integer id="{{id}}" ng-model="model" type="number" min="{{min}}" max="{{max}}" class="form-control" ng-class="{\'invalid\' : !isValid }" placeholder="{{placeholder}}" />';
        }
    }
});

/**
 * @ngdoc directive
 * @name zaaDecimal
 * @restrict E
 *
 * @description
 * Generates a form group with non negative float number input field. Mostly used in LUYA admin when create or update CRUD record.
 *
 *
 * @param {expression} model assignable {@link https://docs.angularjs.org/guide/expression Expression} to bind to.
 * @param {object} options Options object. `steps` property is standing for step attribute of the input field.
 * @param {string} fieldid The id attribute of the input.
 * @param {expression} i18n 'Is field represent an i18n attribute' condition.
 * @param {string} label Form group label.
 * @param {string} placeholder A short hint that describes the expected value of an input field.
 *
 * @example
 * <zaa-decimal model="some.model" label="Some label"></zaa-number>
 *
 */
zaa.directive("zaaDecimal", function () {
    return {
        restrict: "E",
        scope: {
            "model": "=",
            "options": "=",
            "label": "@",
            "i18n": "@",
            "id": "@fieldid",
            "placeholder": "@"
        },
        controller: ['$scope', function ($scope) {
            if ($scope.options === undefined || $scope.options === null) {
                $scope.steps = 0.01;
            } else {
                $scope.steps = $scope.options['steps'];
            }
        }],
        template: function () {
            return '' +
                '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' +
                '<div class="form-side form-side-label">' +
                '<label for="{{id}}">{{label}}</label>' +
                '</div>' +
                '<div class="form-side">' +
                '<luya-decimal ng-model="model" fieldid="{{id}}" min="0" placeholder="{{placeholder}}" step="{{steps}}"></luya-decimal>' +
                '</div>' +
                '</div>';
        }
    }
});


/**
 * @ngdoc directive
 * @name luyaDecimal
 * @restrict E
 *
 * @description
 * Generates an float number input field which is styled like the rest LUYA admin UI elements.
 *
 *
 * @param {expression} ngModel assignable {@link https://docs.angularjs.org/guide/expression Expression} to bind to.
 * @param {string} fieldid The id attribute of the field.
 * @param {string} placeholder A short hint that describes the expected value of an input field.
 * @param {string} min The minimal allowed value of the input.
 * @param {string} max The maximal allowed value of the input.
 * @param {string} step A stepping interval to use when using up and down arrows to adjust the value.
 *
 * @example
 * <luya-number ng-model="some.model" min="-3" max="10" placeholder="Placeholder" initvalue="0"></luya-number>
 *
 * @since 4.2.0
 */
zaa.directive("luyaDecimal", function () {
    return {
        restrict: "E",
        scope: {
            "model": "=ngModel",
            "id": "@fieldid",
            "placeholder": "@",
            "step": "@",
            "min": "@",
            "max": "@"
        },
        controller: ['$scope', function ($scope) {
            if ($scope.step === undefined || $scope.step === null) {
                $scope.step = 0.01;
            }
        }],
        link: function ($scope) {
            $scope.$watch(function () {
                return $scope.model
            }, function (n, o) {
                if ($scope.model == parseFloat(n)) {
                    $scope.model = parseFloat(n);
                }

                $scope.isValid = !!angular.isNumber($scope.model);
            })
        },
        template: function () {
            return '' +
                '<input string-to-float id="{{id}}" ng-model="model" type="number" min="{{min}}" max="{{max}}" step="{{step}}" class="form-control" ng-class="{\'invalid\' : !isValid }" placeholder="{{placeholder}}" />';
        }
    }
});


/**
 * @ngdoc directive
 * @name zaaText
 * @restrict E
 *
 * @description
 * Generates a form group with simple text input. Mostly used in LUYA admin when create or update CRUD record.
 *
 * @param {expression} model assignable {@link https://docs.angularjs.org/guide/expression Expression} to bind to.
 * @param {object} options Options object. Not used.
 * @param {string} fieldid The id attribute of the input.
 * @param {expression} i18n 'Is field represent an i18n attribute' condition.
 * @param {string} label Form group label.
 * @param {string} autocomplete Specifies whether or not an input field should have autocomplete enabled.
 * @param {string} placeholder A short hint that describes the expected value of an input field.
 *
 * @example
 * <zaa-text model="some.model" label="Some label" placeholder="placeholder"></zaa-text>
 *
 */
zaa.directive("zaaText", function () {
    return {
        restrict: "E",
        scope: {
            "model": "=",
            "options": "=",
            "label": "@",
            "i18n": "@",
            "id": "@fieldid",
            "placeholder": "@",
            "autocomplete": "@"
        },
        template: function () {
            return '' +
                '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' +
                '<div class="form-side form-side-label">' +
                '<label for="{{id}}">{{label}}</label>' +
                '</div>' +
                '<div class="form-side">' +
                '<luya-text ng-model="model" fieldid="{{id}}" autocomplete="{{autocomplete}}" placeholder="{{placeholder}}"></luya-text>' +
                '</div>' +
                '</div>';
        },
    }
});


/**
 * @ngdoc directive
 * @name luyaText
 * @restrict E
 *
 * @description
 * Generates a simple text input which is styled like the rest LUYA admin UI elements.
 *
 * @param {expression} ngModel assignable {@link https://docs.angularjs.org/guide/expression Expression} to bind to.
 * @param {string} fieldid The id attribute of the input field.
 * @param {string} autocomplete Specifies whether or not an input field should have autocomplete enabled.
 * @param {string} placeholder A short hint that describes the expected value of an input field.
 *
 * @example
 * <luya-text ng-model="some.model" placeholder="Some placeholder"></luya-text>
 *
 * @since 4.2.0
 */
zaa.directive("luyaText", function () {
    return {
        restrict: "E",
        scope: {
            "model": "=ngModel",
            "id": "@fieldid",
            "autocomplete": "@",
            "placeholder": "@"
        },
        template: function () {
            return '<input id="{{id}}" insert-paste-listener ng-model="model" type="text" class="form-control" autocomplete="{{autocomplete}}" placeholder="{{placeholder}}" />';
        }
    }
});


/**
 * @ngdoc directive
 * @name zaaReadonly
 * @restrict E
 *
 * @description
 * Generates a form group with read-only text. Mostly used in LUYA admin when create or update CRUD record.
 *
 * @param {expression} model assignable {@link https://docs.angularjs.org/guide/expression Expression} to bind to.
 * @param {object} options Options object. Not used.
 * @param {string} fieldid The id attribute of the output text.
 * @param {expression} i18n 'Is field represent an i18n attribute' condition.
 * @param {string} label Form group label.
 *
 * @example
 * <zaa-readonly model="some.model" label="Some label"></zaa-readonly>
 *
 * @since 1.2.1
 */
zaa.directive("zaaReadonly", function () {
    return {
        restrict: "E",
        scope: {
            "model": "=",
            "label": "@",
            "i18n": "@",
            "id": "@fieldid",
        },

        template: function () {
            return '' +
                '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' +
                '<div class="form-side form-side-label">' +
                '<label for="{{id}}">{{label}}</label>' +
                '</div>' +
                '<div class="form-side">' +
                '<luya-readonly ng-model="model" fieldid="{{id}}"></luya-readonly>' +
                '</div>' +
                '</div>';
        },
    }
});

/**
 * @ngdoc directive
 * @name luyaReadonly
 * @restrict E
 *
 * @description
 * Renders a value from model like a read-only attribute.
 *
 * @param {expression} ngModel assignable {@link https://docs.angularjs.org/guide/expression Expression} to bind to.
 * @param {string} fieldid The id attribute of the span element.
 *
 * @example
 * <luya-readonly ng-model="some.model"></luya-readonly>
 *
 * @since 4.2.0
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
 * @ngdoc directive
 * @name zaaAsyncValue
 * @restrict E
 *
 * @description
 * Generates a form group with result of the special api request.
 *
 * @param {expression} model assignable {@link https://docs.angularjs.org/guide/expression Expression} to bind to.
 * @param {string} fieldid The id attribute of the output text.
 * @param {expression} i18n 'Is field represent an i18n attribute' condition.
 * @param {string} label Form group label.
 * @param {string} api Part of request api URL.
 * @param {string} fields JSON string with a list of attributes required to get.
 *
 * @example
 * <zaa-async-value model="some.model" label="Some label" api="admin/admin-users" fields="[foo,bar]" ></zaa-async-value>
 * The above example will send the following request: `/admin/admin-users/{model}?fields=foo,bar`
 *
 */
zaa.directive("zaaAsyncValue", function () {
    return {
        restrict: "E",
        scope: {
            "model": "=",
            "api": "@",
            "fields": "=",
            "label": "@",
            "i18n": "@",
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
                '<luya-async-value ng-model="model" api="{{api}}" fields="fields" fieldid="{{id}}" ng-show="model"></luya-async-value>' +
                '<button type="button" class="btn btn-icon btn-cancel" ng-click="resetValue()" ng-show="model"></button>' +
                '</div>' +
                '</div>';
        }
    }
});

/**
 * @ngdoc directive
 * @name luyaAsyncValue
 * @restrict E
 *
 * @description
 * Renders a value from async api request.
 *
 * @param {expression} ngModel assignable {@link https://docs.angularjs.org/guide/expression Expression} to bind to.
 * @param {string} fieldid The id attribute of the span element.
 * @param {string} api Part of request api URL.
 * @param {string} fields JSON string with a list of attributes required to get.
 *
 * @example
 * <luya-async-value ng-model="some.model" api="admin/admin-users" fields="['foo','bar']"></luya-async-value>
 *
 * @since 4.2.0
 */
zaa.directive("luyaAsyncValue", function () {
    return {
        restrict: "E",
        scope: {
            "model": "=ngModel",
            "api": "@",
            "fields": "=",
            "id": "@fieldid"
        },
        controller: ['$scope', '$timeout', '$http', function ($scope, $timeout, $http) {
            $timeout(function () {
                $scope.$watch('model', function (n, o) {
                    if (n) {
                        $scope.value = '';
                        $http.get($scope.api + "/" + n + "?fields=" + $scope.fields.join())
                            .then(function (response) {
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
            return '<span id="{{id}}" ng-bind="value"></span>';
        }
    }
});


/**
 * Can be used to just fetch a value from an api async.
 *
 * ```
 * <async-value model="some.model" api="admin/admin-users" fields="[foo,bar]"></async-value>
 * ```
 *
 * @since 1.2.2
 *
 * @deprecated
 * sinceVersion="4.2.0"
 *
 * This directive is deprecated. Use `luyaAsyncValue` instead.
 */
zaa.directive("asyncValue", function () {
    return {
        restrict: "E",
        scope: {
            "model": "=",
            "fields": "=",
            "api": "@"
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
 * @ngdoc directive
 * @name zaaTextarea
 * @restrict E
 *
 * @description
 * Generates a form group with textarea input. Mostly used in LUYA admin when create or update CRUD record.
 *
 * @param {expression} model assignable {@link https://docs.angularjs.org/guide/expression Expression} to bind to.
 * @param {object} options Options object. Not used.
 * @param {string} fieldid The id attribute of the input.
 * @param {expression} i18n 'Is field represent an i18n attribute' condition.
 * @param {string} label Form group label.
 * @param {string} placeholder A short hint that describes the expected value of an input field.
 *
 * @example
 * <zaa-textarea model="some.model" label="Some label" placeholder="placeholder"></zaa-textarea>
 *
 */
zaa.directive("zaaTextarea", function () {
    return {
        restrict: "E",
        scope: {
            "model": "=",
            "options": "=",
            "label": "@",
            "i18n": "@",
            "id": "@fieldid",
            "placeholder": "@",
        },

        template: function () {
            return '' +
                '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' +
                '<div class="form-side form-side-label">' +
                '<label for="{{id}}">{{label}}</label>' +
                '</div>' +
                '<div class="form-side">' +
                '<luya-textarea ng-model="model" fieldid="{{id}}" placeholder="{{placeholder}}"></luya-textarea>' +
                '</div>' +
                '</div>';
        },
    }
});


/**
 * @ngdoc directive
 * @name luyaTextarea
 * @restrict E
 *
 * @description
 * Generates a textarea input which is styled like the rest LUYA admin UI elements.
 *
 * @param {expression} ngModel assignable {@link https://docs.angularjs.org/guide/expression Expression} to bind to.
 * @param {string} fieldid The id attribute of the input field.
 * @param {string} placeholder A short hint that describes the expected value of an input field.
 *
 * @example
 * <luya-textarea ng-model="some.model" placeholder="Some placeholder"></luya-textarea>
 *
 * @since 4.2.0
 */
zaa.directive("luyaTextarea", function () {
    return {
        restrict: "E",
        scope: {
            "model": "=ngModel",
            "id": "@fieldid",
            "placeholder": "@"
        },
        template: function () {
            return '<textarea id="{{id}}" insert-paste-listener ng-model="model" type="text" class="form-control" auto-grow placeholder="{{placeholder}}"></textarea>';
        }
    }
});


/**
 * Generates a form group with password input. Mostly used in LUYA admin when create or update CRUD record.
 *
 * Usage:
 * ```
 * <zaa-password model="some.model" label="someLabel" fieldid="someId"></zaa-password>
 * ```
 */

/**
 * @ngdoc directive
 * @name zaaPassword
 * @restrict E
 *
 * @description
 * Generates a form group with textarea input. Mostly used in LUYA admin when create or update CRUD record.
 *
 * @param {expression} model assignable {@link https://docs.angularjs.org/guide/expression Expression} to bind to.
 * @param {object} options Options object. Not used.
 * @param {string} fieldid The id attribute of the input.
 * @param {expression} i18n 'Is field represent an i18n attribute' condition.
 * @param {string} label Form group label.
 * @param {string} placeholder A short hint that describes the expected value of an input field.
 *
 * @example
 * <zaa-password model="some.model" label="someLabel" fieldid="someId"></zaa-password>
 *
 */
zaa.directive("zaaPassword", function () {
    return {
        restrict: "E",
        scope: {
            "model": "=",
            "options": "=",
            "label": "@",
            "i18n": "@",
            "id": "@fieldid",
            "autocomplete": "@",
            "inputmode": "@",
        },

        template: function () {
            return '' +
                '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' +
                '<div class="form-side form-side-label">' +
                '<label for="{{id}}">{{label}}</label>' +
                '</div>' +
                '<div class="form-side">' +
                '<luya-password ng-model="model" fieldid="{{id}}" autocomplete="{{autocomplete}}" inputmode="{{inputmode}}"></luya-password>' +
                '</div>' +
                '</div>';
        },
    }
});


/**
 * @ngdoc directive
 * @name luyaPassword
 * @restrict E
 *
 * @description
 * Generates a simple password input which is styled like the rest LUYA admin UI elements.
 *
 * @param {expression} ngModel assignable {@link https://docs.angularjs.org/guide/expression Expression} to bind to.
 * @param {string} fieldid The id attribute of the input field.
 * @param {string} inputmode A hint to the browser for which keyboard to display. Default is 'verbatim'.
 * @param {string} autocomplete Specifies whether or not an input field should have autocomplete enabled.
 *
 * @example
 * <luya-password ng-model="some.model"></luya-password>
 *
 * @since 4.2.0
 */
zaa.directive("luyaPassword", function () {
    return {
        restrict: "E",
        scope: {
            "model": "=ngModel",
            "id": "@fieldid",
            "autocomplete": "@",
            "inputmode": "@",
        },

        controller: ['$scope', '$timeout', function ($scope, $timeout) {
            if ($scope.autocomplete === undefined || $scope.autocomplete === '') {
                $scope.autocomplete = 'on';
            }

            if ($scope.inputmode === undefined || $scope.inputmode === '') {
                $scope.inputmode = 'verbatim';
            }
        }],

        template: function () {
            return '<input id="{{id}}" ng-model="model" type="password" class="form-control" autocomplete="{{autocomplete}}" inputmode="{{inputmode}}" />';
        }
    }
});


/**
 * @ngdoc directive
 * @name zaaRadio
 * @restrict E
 *
 * @description
 * Generates a form group with radio list input. Mostly used in LUYA admin when create or update CRUD record.
 *
 *
 * @param {expression} ngModel assignable {@link https://docs.angularjs.org/guide/expression Expression} to bind to.
 * @param {array} options Data array. options = [{'label' : 'FirstLabel', 'value' : 'someValue'}, {'label' : 'anotherLabel', 'value' : 123},...];`
 * @param {string} optionslabel The name of the property in options object which corresponds to 'label'.
 * @param {string} optionsvalue The name of the property in options object which corresponds to 'value'.
 * @param {expression} i18n 'Is field represent an i18n attribute' condition.
 * @param {string} label Form group label.
 * @param {string} fieldid The dummy id attribute. The label will point to this id, but such an id will not be associated with any field.
 * @param {string} inline Inline flag. If enabled, buttons will be rendered in line. To set it to false leave the inline attribute empty or omit it.
 * @param {string} initvalue The value that will be passed to the model when the radios is initiated. If this value is equal to value of some radio button, that button will be checked.
 *
 * @example
 * <zaa-radio model="some.model" label="label" options="[{name:'foo', value: 'bar'}, {name:'John', value: 'Doe'} {...}]" optionslabel="name"></zaa-radio>
 *
 * @since 4.2.0
 */
zaa.directive("zaaRadio", function () {
    return {
        restrict: "E",
        scope: {
            "model": "=",
            "options": "=",
            "optionsvalue": "@",
            "optionslabel": "@",
            "label": "@",
            "i18n": "@",
            "id": "@fieldid",
            "initvalue": "@",
            "inline": "@"
        },
        controller: ['$scope', '$timeout', function ($scope, $timeout) {
            $timeout(function () {
                if ($scope.optionsvalue === undefined || $scope.optionsvalue === "") {
                    $scope.optionsvalue = 'value';
                }

                if ($scope.optionslabel === undefined || $scope.optionslabel === "") {
                    $scope.optionslabel = 'label';
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
                '<luya-radio ng-model="model" options="options" fieldid="{{id}}" initvalue="{{initvalue}}" optionsvalue="{{optionsvalue}}" optionslabel="{{optionslabel}}" ng-attr-inline="{{inline}}"></luya-radio>' +
                '</div>' +
                '</div>';
        },
    }
});

/**
 * @ngdoc directive
 * @name luyaRadio
 * @restrict E
 *
 * @description
 * Generates a radio list which is styled like the rest LUYA admin UI elements.
 *
 * @param {expression} ngModel assignable {@link https://docs.angularjs.org/guide/expression Expression} to bind to.
 * @param {json} options Options object which holding the data array: `options = [{"value" : 1, "label" => 'Label for Value 1' }, {"value" : 2, "label" => 'Label for Value 2' }]`.
 * @param {string} optionslabel The name of the property in options object which corresponds to 'label'.
 * @param {string} optionsvalue The name of the property in options object which corresponds to 'value'.
 * @param {string} initvalue The value that will be passed to the model when the radios is initiated. If this value is equal to value of some radio button, that button will be checked.
 * @param {string} inline Inline flag. If enabled, radio buttons will be rendered in line. To set it to false leave the inline attribute empty or omit it.
 * @param {string} fieldid The initial part of the id of single radio button.
 *
 * @example
 * <luya-radio ng-model="some.model" options="[{label:'foo', value: 'bar'}, {...}]" inline="inline" initvalue="bar"></luya-radio>
 *
 * @since 4.2.0
 */
zaa.directive("luyaRadio", function () {
    return {
        restrict: "E",
        scope: {
            "model": "=ngModel",
            "options": "=",
            "optionsvalue": "@",
            "optionslabel": "@",
            "id": "@fieldid",
            "initvalue": "@",
            "inline": "@"
        },
        controller: ['$scope', '$timeout', function ($scope, $timeout) {
            $scope.setModelValue = function (value) {
                $scope.model = value;
            };

            $scope.init = function () {
                if ($scope.model === undefined || $scope.model === null) {
                    $scope.model = typeCastValue($scope.initvalue);
                }

                if ($scope.id === undefined || $scope.id === null || $scope.id === '') {
                    $scope.id = Math.random().toString(36).substring(7);
                }
            };
            $timeout(function () {
                if ($scope.optionsvalue === undefined || $scope.optionsvalue === "") {
                    $scope.optionsvalue = 'value';
                }

                if ($scope.optionslabel === undefined || $scope.optionslabel === "") {
                    $scope.optionslabel = 'label';
                }

                $scope.init();
            });
        }],
        template: function () {
            return '' +
                '<div ng-repeat="(key, item) in options" class="form-check" ng-class="{\'form-check-inline\': inline}">' +
                '<input value="{{item[optionsvalue]}}" type="radio" ng-click="setModelValue(item[optionsvalue])" ng-checked="item[optionsvalue] == model" name="{{id}}" class="form-check-input" id="{{id}}_{{key}}">' +
                '<label class="form-check-label" for="{{id}}_{{key}}">' +
                '{{item[optionslabel]}}' +
                '</label>' +
                '</div>'
        }
    };
});

/**
 * @ngdoc directive
 * @name zaaSelect
 * @restrict E
 *
 * @description
 * Generates a form group with dropdown list input. Mostly used in LUYA admin when create or update CRUD record.
 *
 *
 * @param {expression} ngModel assignable {@link https://docs.angularjs.org/guide/expression Expression} to bind to.
 * @param {array} options Data array. options = [{'label' : 'FirstLabel', 'value' : 'someValue'}, {'label' : 'anotherLabel', 'value' : 123},...];`
 * @param {string} optionslabel The name of the property in options object which corresponds to 'label'.
 * @param {string} optionsvalue The name of the property in options object which corresponds to 'value'.
 * @param {expression} i18n 'Is field represent an i18n attribute' condition.
 * @param {string} label Form group label.
 * @param {string} fieldid The id of 'real' select element which is lied under the hood.
 * @param {string} clearable Clearable flag. If enabled, the button for resetting the dropdown to initial state will be displayed. To set it to false set clearable = 'false' or clearable = '0'.
 * @param {string} initvalue The value that will be passed to the model when the dropdown is initiated. If this value is equal to value of some option, that option will be shown.
 *
 * @example
 * <zaa-select model="some.model" label="someLabel" options="[{label:'foo', value: 'bar'}, {label:'John', value: 'Doe'}]" initvalue="Doe"></zaa-select>
 *
 */
zaa.directive("zaaSelect", function () {
    return {
        restrict: "E",
        scope: {
            "model": "=",
            "options": "=",
            "optionsvalue": "@",
            "optionslabel": "@",
            "label": "@",
            "i18n": "@",
            "id": "@fieldid",
            "initvalue": "@",
            "clearable": "@",
            "placeholder": "@"
        },
        controller: ['$scope', '$timeout', '$rootScope', function ($scope, $timeout, $rootScope) {
            $timeout(function () {
                if ($scope.optionsvalue === undefined) {
                    $scope.optionsvalue = 'value';
                }
                if ($scope.optionslabel === undefined) {
                    $scope.optionslabel = 'label';
                }

                $scope.clearable = !($scope.clearable === 'false' || $scope.clearable === '0');
            });
        }],
        template: function () {
            return '' +
                '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' +
                '<div class="form-side form-side-label">' +
                '<label for="{{id}}">{{label}}</label>' +
                '</div>' +
                '<div class="form-side">' +
                '<luya-select ng-model="model" options="options" fieldid="{{id}}" clearable="{{clearable}}" placeholder="{{placeholder}}" optionsvalue="{{optionsvalue}}" optionslabel="{{optionslabel}}" initvalue="{{initvalue}}"></luya-select>' +
                '</div>' +
                '</div>';
        }
    }
});


/**
 * @ngdoc directive
 * @name luyaSelect
 * @restrict E
 *
 * @description
 * Generates a dropdown list which is styled like the rest LUYA admin UI elements.
 *
 * @param {expression} ngModel assignable {@link https://docs.angularjs.org/guide/expression Expression} to bind to.
 * @param {json} options Options object which holding the data array: `options = [{"value" : 1, "label" => 'Label for Value 1' }, {"value" : 2, "label" => 'Label for Value 2' }]`.
 * @param {string} optionslabel The name of the property in options object which corresponds to 'label'.
 * @param {string} optionsvalue The name of the property in options object which corresponds to 'value'.
 * @param {string} initvalue The value that will be passed to the model when the dropdown is initiated. If this value is equal to value of some option, that option will be shown.
 * @param {string} fieldid The id of 'real' select element which is lied under the hood.
 * @param {string} clearable Clearable flag. If enabled, the button for resetting the dropdown to initial state will be displayed. To set it to false set clearable = 'false' or clearable = '0'.
 * @param {string} placeholder A short hint that describes the expected value of an input field. Shown when no value is selected. If placeholder is absent or empty, a default message 'Nothing selected' will appear.
 * @param {Function} ngChange The function that will be called on select change.
 *
 * @example
 * <luya-select ng-model="some.model" options="[{label:'foo', value: 'bar'}, {label:'John', value: 'Doe'}]" initvalue="Doe"></luya-select>
 *
 * @since 4.2.0
 */
zaa.directive("luyaSelect", function () {
    return {
        restrict: "E",
        scope: {
            "model": "=ngModel",
            "options": "=",
            "optionsvalue": "@",
            "optionslabel": "@",
            "id": "@fieldid",
            "initvalue": "@",
            "placeholder": "@",
            "clearable": "@",
            ngChange: "&"
        },
        controller: ['$scope', '$timeout', '$rootScope', function ($scope, $timeout, $rootScope) {

            $scope.isOpen = 0;
            $scope.isDefault = 1;


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
                    return $scope.model
                }, function (n, o) {
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

                if ($scope.optionsvalue === undefined || $scope.optionsvalue === "") {
                    $scope.optionsvalue = 'value';
                }

                if ($scope.optionslabel === undefined || $scope.optionslabel === "") {
                    $scope.optionslabel = 'label';
                }

                $scope.clearable = !($scope.clearable === 'false' || $scope.clearable === '0');
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
                $scope.isDefault = 1;
                var value = i18n['ngrest_select_no_selection'];
                if ($scope.placeholder) value = $scope.placeholder;
                angular.forEach($scope.options, function (item) {
                    if ($scope.model == item[$scope.optionsvalue]) { // == is intentionally here
                        value = item[$scope.optionslabel];
                        $scope.isDefault = 0;
                    }
                });

                return value;
            };

            $scope.hasSelectedValue = function () {
                var modelValue = $scope.model;
                return $scope.valueExistsInOptions(modelValue) && modelValue !== $scope.initvalue;
            };
        }],
        template: function () {
            return '' +
                '<div class="zaaselect" ng-class="{\'open\':isOpen, \'selected\':hasSelectedValue()}">' +
                '<select class="zaaselect-select" ng-model="model" id="{{id}}">' +
                '<option ng-repeat="opt in options" ng-value="opt[optionsvalue]">{{opt[optionslabel]}}</option>' +
                '</select>' +
                '<div class="zaaselect-selected">' +
                '<span class="zaaselect-selected-text" ng-class="{\'text-muted\':(placeholder && isDefault)}" ng-click="toggleIsOpen()">{{getSelectedLabel()}}</span>' +
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
 * @ngdoc directive
 * @name zaaSelect
 * @restrict E
 *
 * @description
 * Generates a dropdown list input. Data for options is taken from API request.
 * Used in SelectAsyncApi plugin.
 *
 *
 * @param {expression} ngModel assignable {@link https://docs.angularjs.org/guide/expression Expression} to bind to.
 * @param {string} api Request api URL.
 * @param {string} optionslabel The name of the property in options object which corresponds to 'label'.
 * @param {string} optionsvalue The name of the property in options object which corresponds to 'value'.
 * @param {expression} i18n 'Is field represent an i18n attribute' condition.
 * @param {string} label Form group label.
 * @param {string} fieldid The id of 'real' select element which is lied under the hood.
 * @param {string} initvalue The value that will be passed to the model when the dropdown is initiated. If this value is equal to value of some option, that option will be shown.
 *
 * @example
 * <zaa-select model="some.model" label="someLabel" options="[{label:'foo', value: 'bar'}, {label:'John', value: 'Doe'}]" initvalue="Doe"></zaa-select>
 *
 */
zaa.directive("zaaAsyncApiSelect", function () {
    return {
        restrict: "E",
        scope: {
            "model": "=",
            "api": "@",
            "optionsvalue": "@",
            "optionslabel": "@",
            "label": "@",
            "i18n": "@",
            "id": "@fieldid",
            "initvalue": "@",
            "placeholder": "@"
        },
        controller: ['$scope', '$http', '$timeout', function ($scope, $http, $timeout) {
            $scope.options = [];

            $timeout(function () {
                if ($scope.optionsvalue === undefined) {
                    $scope.optionsvalue = 'id';
                }
                if ($scope.optionslabel === undefined) {
                    $scope.optionslabel = 'title';
                }
            });

            $scope.$watch('api', function (apiUrl) {
                $http.get(apiUrl).then(function (value) {
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
                '<div class="form-side">' +
                '<luya-select ng-model="model" options="options" id="{{id}}" placeholder="{{placeholder}}"  initvalue="{{initvalue}}"></luya-select>' +
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
zaa.directive("zaaSelectCrud", function () {
    return {
        restrict: "E",
        scope: {
            "model": "=",
            "options": "=",
            "api": "@api",
            "label": "@",
            "i18n": "@",
            "id": "@fieldid",
            "initvalue": "@"
        },
        template: function () {
            return '' +
                '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' +
                '<div class="form-side form-side-label">' +
                '<label for="{{id}}">{{label}}</label>' +
                '</div>' +
                '<div class="form-side">' +
                '<luya-async-value ng-model="model" api="{{options.api}}" fields="options.fields"></luya-async-value>' +
                '<crud-loader api="{{options.route}}" model-setter="model" model-selection="1" alias="{{label}}"></crud-loader>' +
                '</div>' +
                '</div>';
        }
    }
});


/**
 * @ngdoc directive
 * @name zaaCheckbox
 * @restrict E
 *
 * @description
 * Generates a form group with checkbox input. Mostly used in LUYA admin when create or update CRUD record.
 *
 * @param {expression} model assignable {@link https://docs.angularjs.org/guide/expression Expression} to bind to.
 * @param {object} options Options object. `true-value` & `false-value` options available: `options = {'true-value' : 1, 'false-value' : 0};`
 * @param {string} fieldid The id attribute of the checkbox.
 * @param {expression} i18n Is checkbox represent an i18n attribute.
 * @param {string} label Field label to be drawn to the left of checkbox.
 * @param {string} checkboxlabel Checkbox label to be drawn to the right of checkbox.
 * @param {string} initvalue The value that will be passed to the model when the checkbox is initiated. If this value is equal to options.true-value the checkbox will be checked.
 * @param {Function} ngChange The function that will be called on checkbox state change.
 *
 * @example
 * <zaa-checkbox model="some.model" initvalue="1" label="Some label" checkboxlabel="Sublabel"></luya-checkbox>
 *
 */
zaa.directive("zaaCheckbox", function () {
    return {
        restrict: "E",
        scope: {
            "model": "=",
            "options": "=",
            "i18n": "@",
            "id": "@fieldid",
            "initvalue": "@",
            "label": "@",
            "checkboxlabel": "@",
            ngChange: "&"
        },
        controller: ['$scope', function ($scope) {
            if ($scope.options === null || $scope.options === undefined) {
                $scope.valueTrue = 1;
                $scope.valueFalse = 0;
            } else {
                $scope.valueTrue = $scope.options['true-value'];
                $scope.valueFalse = $scope.options['false-value'];
            }
        }],
        template: function () {
            return '' +
                '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' +
                '<div class="form-side form-side-label">' +
                '<label for="{{id}}">{{label}}</label>' +
                '</div>' +
                '<div class="form-side">' +
                '<luya-checkbox ng-model="model" fieldid="{{id}}" truevalue="{{valueTrue}}" falsevalue="{{valueFalse}}" initvalue="{{initvalue}}" label="{{checkboxlabel}}" ng-change="ng-change"></luya-checkbox>' +
                '</div>' +
                '</div>';
        }
    }
});


/**
 * @ngdoc directive
 * @name luyaCheckbox
 * @restrict E
 *
 * @description
 * Generates a checkbox with label (optionally) which is styled like the rest LUYA admin UI elements.
 *
 * @param {expression} ngModel assignable {@link https://docs.angularjs.org/guide/expression Expression} to bind to.
 * @param {string} fieldid The id attribute of the checkbox.
 * @param {string} label Checkbox label to be drawn to the right of checkbox.
 * @param {string} truevalue The value that will be passed to the model when the checkbox is checked. Default is 1.
 * @param {string} falsevalue The value that will be passed to the model when the checkbox is unchecked. Default is 0.
 * @param {string} initvalue The value that will be passed to the model when the checkbox is initiated. If this value is equal to truevalue the checkbox will be checked.
 * @param {Function} ngChange The function that will be called on checkbox state change.
 *
 * @example
 * <luya-checkbox truevalue="on" falsevalue="off" initvalue="on" ng-model="some.model"></luya-checkbox>
 *
 * @since 4.2.0
 */
zaa.directive("luyaCheckbox", function () {
    return {
        restrict: "E",
        scope: {
            "model": "=ngModel",
            "label": "@",
            "truevalue": "@",
            "falsevalue": "@",
            "id": "@fieldid",
            "initvalue": "@",
            ngChange: "&"
        },
        controller: ['$scope', '$timeout', function ($scope, $timeout) {

            $scope.init = function () {
                if ($scope.truevalue === undefined || $scope.truevalue === "") {
                    $scope.truevalue = 1;
                }

                if ($scope.falsevalue === undefined || $scope.falsevalue === "") {
                    $scope.falsevalue = 0;
                }

                if ($scope.initvalue === undefined || $scope.initvalue === "") {
                    $scope.initvalue = $scope.falsevalue;
                }

                if ($scope.model === undefined || $scope.model === null) {
                    $scope.model = $scope.initvalue;
                }

                if ($scope.id === undefined || $scope.id === null || $scope.id === '') {
                    $scope.id = Math.random().toString(36).substring(7);
                }
            };
            $timeout(function () {
                $scope.init();
            });

            $scope.clicker = function () {
                if ($scope.model == $scope.truevalue) { // == is intentionally here
                    $scope.model = $scope.falsevalue;
                } else {
                    $scope.model = $scope.truevalue;
                }
                $timeout($scope.ngChange, 0);
            };
        }],
        template: function () {
            return '' +
                '<div class="form-check">' +
                '<input id="{{id}}" type="checkbox" class="form-check-input-standalone" ng-click="clicker()" ng-checked="model == truevalue"/>' +
                '<label for="{{id}}">{{label}}</label>' +
                '</div>';
        }
    }
});


/**
 * @ngdoc directive
 * @name zaaCheckboxArray
 * @restrict E
 *
 * @description
 * Generates a form group with checkboxes list. Mostly used in LUYA admin when create or update CRUD record.
 *
 * @param {expression} model assignable {@link https://docs.angularjs.org/guide/expression Expression} to bind to.
 * @param {object} options Options object. `item` property is holding the data array: `options.items = [{"value" : 1, "label" => 'Label for Value 1' }, {"value" : 2, "label" => 'Label for Value 2' }]`.
 * @param {string} fieldid The dummy id attribute. The label will point to this id, but such an id will not be associated with any checkbox.
 * @param {expression} i18n Is checkbox list represent an i18n attribute.
 * @param {string} label Form group label.
 * @param {string} preselect Select-all flag. If enabled, all checkboxes will be checked by default. To set it to false leave the preselect attribute empty or omit it.
 * @param {string} inline Inline flag. If enabled, checkboxes will be rendered in line. To set it to false leave the inline attribute empty or omit it.
 *
 * @example
 * <zaa-checkbox model="some.model" initvalue="1" label="Some label" checkboxlabel="Sublabel"></luya-checkbox>
 *
 */
zaa.directive("zaaCheckboxArray", function () {
    return {
        restrict: "E",
        scope: {
            "model": "=",
            "options": "=",
            "i18n": "@",
            "id": "@fieldid",
            "label": "@",
            "preselect": "@",
            "inline": "@"
        },
        controller: ['$scope', '$filter', function ($scope, $filter) {
            if ($scope.model === undefined) {
                $scope.model = [];
            }

            $scope.toggleAll = function () {
                if ($scope.model.length > 0) {
                    $scope.model = [];
                } else {
                    for (let item of $scope.options.items) {
                        $scope.model.push({ value: item.value });
                    }
                }
            }
        }],
        template: function () {
            return '' +
                '<div class="form-group form-side-by-side" ng-class="{\'input--hide-label\': i18n}">' +
                '<div class="form-side form-side-label">' +
                '<label for="{{id}}">{{label}}</label>' +
                '</div>' +
                '<div class="form-side">' +
                '<button ng-click="toggleAll()" type="button" class="zaa-checkbox-array-toggle-all btn btn-info btn-icon"><i class="material-icons">done_all</i></button>' +
                '<luya-checkbox-array ng-model="model" options="options.items" preselectall="{{preselect}}" ng-attr-inline="{{inline}}"></luya-checkbox-array>' +
                '</div>' +
                '</div>';
        }
    }
});


/**
 * @ngdoc directive
 * @name luyaCheckboxArray
 * @restrict E
 *
 * @description
 * Generates a list of checkboxes with labels which is styled like the rest LUYA admin UI elements.
 *
 * The output data will be presented as an array of objects in the form of {'value': 'someValue'}. Only objects corresponding to the checked checkboxes will be included in the array.
 *
 * @param {expression} ngModel assignable {@link https://docs.angularjs.org/guide/expression Expression} to bind to.
 * @param {array} options Data array. options = [{'label' : 'checkboxLabel', 'value' : 'someValue'}, {'label' : 'anotherLabel', 'value' : 123},...];`
 * @param {string} preselectall Select-all flag. If enabled, all checkboxes will be checked by default. To set it to false leave the preselectall attribute empty or omit it.
 * @param {string} inline Inline flag. If enabled, checkboxes will be rendered in line. To set it to false leave the inline attribute empty or omit it.
 *
 * @example
 * <luya-checkbox-array  ng-model="some.model" inline="inline" options="[{'label' : 'checkboxLabel', 'value' : 'someValue'}, {'label' : 'anotherLabel', 'value' : 123}]" preselectall="1"></luya-checkbox-array>
 *
 * @todo
 * optionsvalue and optionslabel attributes
 *
 * @since 4.2.0
 */
zaa.directive("luyaCheckboxArray", function () {
    return {
        restrict: "E",
        scope: {
            "model": "=ngModel",
            "options": "=",
            "preselectall": "@",
            "inline": "@"
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
                if (n !== undefined) {
                    $scope.items = $filter('orderBy')(n, 'label');
                    if ($scope.preselectall) {
                        $scope.preselectOptionValuesToModel(n);
                    }
                }
            });

            $scope.filtering = function () {
                $scope.items = $filter('filter')($scope.options, $scope.searchString);
            }

            $scope.toggleSelection = function (value) {
                if ($scope.model === undefined) {
                    $scope.model = [];
                }

                for (var i in $scope.model) {
                    if ($scope.model[i]["value"] == value.value) {
                        $scope.model.splice(i, 1);
                        return;
                    }
                }
                $scope.model.push({ 'value': value.value });
            }

            $scope.isChecked = function (item) {
                for (var i in $scope.model) {
                    if ($scope.model[i]["value"] == item.value) {
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
                '<div class="position-relative mb-3">' +
                '<div class="input-group">' +
                '<div class="input-group-prepend">' +
                '<div class="input-group-text">' +
                '<i class="material-icons">search</i>' +
                '</div>' +
                '</div>' +
                '<input class="form-control" type="text" ng-change="filtering()" ng-model="searchString" placeholder="' + i18n['ngrest_crud_search_text'] + '">' +
                '</div>' +
                '<span class="zaa-checkbox-array-counter badge badge-secondary">{{items.length}} ' + i18n['js_dir_till'] + ' {{options.length}}</span>' +
                '</div>' +
                '<div ng-repeat="(k, item) in items track by k"  class="form-check" ng-class="{\'form-check-inline\': inline}">' +
                '<input type="checkbox" class="form-check-input" ng-checked="isChecked(item)" id="{{random}}_{{k}}" ng-click="toggleSelection(item)" />' +
                '<label for="{{random}}_{{k}}">{{item.label}}</label>' +
                '</div>'
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
            "label": "@",
            "id": "@fieldid",
            "i18n": "@",
            "resetable": "@resetable",
        },
        controller: ['$scope', '$filter', function ($scope, $filter) {

            $scope.isNumeric = function (num) {
                return !isNaN(num)
            }

            $scope.$watch(function () {
                return $scope.model
            }, function (n, o) {
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

            $scope.$watch(function () {
                return $scope.date
            }, function (n, o) {
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
            "label": "@",
            "id": "@fieldid",
            "i18n": "@",
            "resetable": "@resetable"
        },
        controller: ['$scope', '$filter', function ($scope, $filter) {

            $scope.$watch(function () {
                return $scope.model
            }, function (n, o) {

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

            $scope.$watch(function () {
                return $scope.date
            }, function (n, o) {
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
            "label": "@",
            "i18n": "@",
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


/**
 * Generates a form group with file selection from storage input. Mostly used in LUYA admin when create or update CRUD record.
 *
 * Usage:
 * ```
 * <zaa-file-upload model="model" label="someLabel" fieldid="someId"></zaa-file-upload>
 * ```
 */
zaa.directive("zaaFileUpload", function () {
    return {
        restrict: "E",
        scope: {
            "model": "=",
            "options": "=",
            "label": "@",
            "i18n": "@",
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

/**
 * Generates a form group with image file selection from storage input. Mostly used in LUYA admin when create or update CRUD record.
 *
 * Usage:
 * ```
 * <zaa-image-upload model="model" label="someLabel" fieldid="someId"></zaa-image-upload>
 * ```
 */
zaa.directive("zaaImageUpload", function () {
    return {
        restrict: "E",
        scope: {
            "model": "=",
            "options": "=",
            "label": "@",
            "i18n": "@",
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
            "label": "@",
            "i18n": "@",
            "id": "@fieldid",
        },
        link: function (scope, element, attributes) {
            scope.$watch('model', function (newValue, oldValue) {
                if (newValue && newValue.length >= 1) {
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
                if ($scope.model === null || $scope.model === '' || $scope.model === undefined) {
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

            $scope.isDescriptionEnabled = function () {
                if ($scope.options && $scope.options.hasOwnProperty('description')) {
                    return $scope.options.description
                }

                return true;
            }

            $scope.noFiltersOption = function () {
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
            "label": "@",
            "i18n": "@",
            "id": "@fieldid",
        },
        controller: ['$scope', '$element', '$timeout', function ($scope, $element, $timeout) {

            if ($scope.model === undefined) {
                $scope.model = [];
            }

            $scope.add = function () {
                if ($scope.model === null || $scope.model === '' || $scope.model === undefined) {
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
            "label": "@",
            "i18n": "@",
            "id": "@fieldid",
            "controls": "@"
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
                if ($scope.model === null || $scope.model === '' || $scope.model === undefined) {
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

            $scope.hasControls = function() {
                if ($scope.controls && $scope.controls == false) {
                    return false;
                }

                return true;
            }

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
                                    '<zaa-injector dir="opt.type" options="opt.options" fieldid="id-{{msortKey}}-{{mutliOptKey}}" placeholder="{{opt.placeholder}}" initvalue="{{opt.initvalue}}" label="{{opt.label}}" model="row[opt.var]"></zaa-injector>' +
                                '</div>' +
                                '<div class="list-buttons" ng-show="hasControls()">' +
                                    '<div class="btn-group" role="group">' +
                                        '<button type="button" class="btn btn-sm btn-outline-info" ng-click="moveUp(msortKey)" ng-if="msortKey > 0"><i class="material-icons">keyboard_arrow_up</i></button>' +
                                        '<button type="button" class="btn btn-sm btn-outline-info" ng-click="moveDown(msortKey)" ng-if="showDownButton(msortKey)"><i class="material-icons">keyboard_arrow_down</i></button>' +
                                        '<button type="button" class="btn btn-sm btn-outline-danger" ng-click="remove(msortKey)"><i class="material-icons">remove</i></button>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                            '<button ng-show="hasControls()" ng-click="add()" type="button" class="btn btn-sm btn-success list-add-button"><i class="material-icons">add</i></button>' +
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
            "label": "@",
            "i18n": "@",
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
                '<input type="text" class="form-control" placeholder="' + i18n['js_jsonobject_newkey'] + '" aria-label="' + i18n['js_jsonobject_newkey'] + '" ng-model="newKey">' +
                '<div class="input-group-append">' +
                '<div class="input-group-text" ng-click="add(newKey);newKey=null;">' +
                '<i class="material-icons">add</i>' +
                '</div>' +
                '</div>' +
                '</div>' +
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
            "label": "@",
            "i18n": "@",
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
