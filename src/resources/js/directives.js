/**
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
        link: function ($scope) {
            // generate echars document
            var echartElement = echarts.init(document.getElementById($scope.id), $scope.theme);
            $scope.$watch('data', function (n) {
                if (n && n != undefined) {
                    echartElement.setOption(angular.fromJson(n));
                }
            });
            // ensure resize happens
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
        link: function (scope, element, attr) {
            var parsed = $parse(attr.ngBindHtml);
            scope.$watch(function () {
                return (parsed(scope) || "").toString();
            }, function () {
                $compile(element, null, -9999)(scope);  //The -9999 makes it skip directives so that we do not recompile ourselves
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
        template: function () {
            return '<span>' +
                '<span ng-if="link.type==1"><show-internal-redirection nav-id="link.value" /></span>' +
                '<span ng-if="link.type==2">{{link.value}}</span>' +
                '<span ng-if="link.type==3"><storage-file-display file-id="{{link.value}}"></storage-file-display></span>' +
                '<span ng-if="link.type==4">{{link.value}}</span>' +
                '<span ng-if="link.type==5">{{link.value}}</span>' +
                '</span>';
        }
    }
});

/**
 * Generate a Tooltip – usage:
 *
 * The default tooltip is positioned on the right side of the element:
 *
 * ```html
 * <span tooltip tooltip-text="Tooltip">...</span>
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
 * Display a tooltip with delay in milliseconds:
 *
 * ```html
 * <span tooltip tooltip-text="Tooltip" tooltip-popup-delay="500">...</span>
 * ```
 * 
 * 
 * You can provide an Image URL beside or instead of text.
 *
 * ```html
 * <span tooltip tooltip-image-url="http://image.url">...</span>
 * ```
 * 
 * 
 * Disable tooltip based on variable (two way binding):
 *
 * ```html
 * <span tooltip tooltip-text="Tooltip" tooltip-disabled="variableMightBeTrueMightBeFalseMightChange">Span Text</span>
 * ```
 */
zaa.directive("tooltip", ['$document', '$http', '$timeout', function ($document, $http, $timeout) {
    return {
        restrict: 'A',
        scope: {
            'tooltipText': '@',
            'tooltipExpression': '=',
            'tooltipPosition': '@',
            'tooltipOffsetTop': '@',
            'tooltipOffsetLeft': '@',
            'tooltipPopupDelay': '@',
            'tooltipImageUrl': '@',
            'tooltipPreviewUrl': '@',
            'tooltipDisabled': '='
        },
        link: function (scope, element, attr) {
            var defaultPosition = 'right';

            var lastValue = null;

            var popupTimeout = null;

            var positions = {
                top: function () {
                    var bcr = element[0].getBoundingClientRect();
                    return {
                        top: bcr.top - scope.pop.outerHeight(),
                        left: (bcr.left + (bcr.width / 2)) - (scope.pop.outerWidth() / 2),
                    }
                },
                bottom: function () {
                    var bcr = element[0].getBoundingClientRect();
                    return {
                        top: bcr.top + bcr.height,
                        left: (bcr.left + (bcr.width / 2)) - (scope.pop.outerWidth() / 2),
                    }
                },
                right: function () {
                    var bcr = element[0].getBoundingClientRect();
                    return {
                        top: (bcr.top + (bcr.height / 2)) - (scope.pop.outerHeight() / 2),
                        left: bcr.left + bcr.width
                    }
                },
                left: function () {
                    var bcr = element[0].getBoundingClientRect();
                    return {
                        top: (bcr.top + (bcr.height / 2)) - (scope.pop.outerHeight() / 2),
                        left: bcr.left - scope.pop.outerWidth()
                    }
                }
            };

            var onScroll = function () {
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

            var cancelPopupTimeout = function () {
                if (popupTimeout) {
                    $timeout.cancel(popupTimeout);
                    popupTimeout = null;
                }
            };

            element.on('mouseenter', function () {

                if (scope.tooltipExpression) {
                    scope.tooltipText = scope.tooltipExpression;
                }

                // Generate tooltip HTML for the first time
                if ( (!scope.pop || lastValue != scope.tooltipText)
                  && (typeof scope.tooltipDisabled === 'undefined' || scope.tooltipDisabled === false)
                  && (scope.tooltipText || scope.tooltipImageUrl || scope.tooltipPreviewUrl) ) {

                    lastValue = scope.tooltipText

                    var html = '<div class="tooltip tooltip-' + (scope.tooltipPosition || defaultPosition) + (scope.tooltipImageUrl ? ' tooltip-image' : '') + '" role="tooltip">' +
                        '<div class="tooltip-arrow"></div>' +
                        '<div class="tooltip-inner">' +
                        (scope.tooltipText ? ('<span class="tooltip-text">' + scope.tooltipText + '</span>') : '') +
                        '</div>' +
                        '</div>';

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
                }

                // Should the tooltip be displayed?
                if (scope.pop && (typeof scope.tooltipDisabled === 'undefined' || scope.tooltipDisabled === false)) {

                    // check position
                    onScroll();

                    // todo: Improve performance ...? x)
                    // register scroll listener
                    element.parents().on('scroll', onScroll);

                    // show popup...
                    if (!isNaN(scope.tooltipPopupDelay)) {
                        // ...with delay
                        popupTimeout = $timeout(function () {
                            scope.pop.show();
                        }, scope.tooltipPopupDelay);
                    }
                    else {
                        // ...instantly
                        scope.pop.show();
                    }
                }
            });

            element.on('mouseleave', function () {
                element.parents().off('scroll', onScroll);

                cancelPopupTimeout();

                if (scope.pop) {
                    scope.pop.hide();
                }
            });

            scope.$on('$destroy', function () {
                cancelPopupTimeout();

                if (scope.pop) {
                    scope.pop.remove();
                }
            });
        }
    }
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
        link: function (scope, element, attrs, ngModel) {
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

        var destroy = function () {
            if ($shadow != null) {
                $shadow.remove();
                $shadow = null;
            }
        };

        var update = function () {
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

            var times = function (string, number) {
                for (var i = 0, r = ''; i < number; i++) {
                    r += string;
                }
                return r;
            };

            var val = element.val().replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/&/g, '&amp;')
                .replace(/\n$/, '<br/>&nbsp;')
                .replace(/\n/g, '<br/>')
                .replace(/\s{2,}/g, function (space) {
                    return times('&nbsp;', space.length - 1) + ' '
                });

            $shadow.html(val);

            element.css('height', $shadow.outerHeight() + 10 + 'px');
        };

        element.bind('keyup keydown keypress change click', update);
        element.bind('blur', destroy);
        update();
    }
});

/**
 * Resize the given element
 */
zaa.directive('resizer', ['$document', function ($document) {
    return {
        scope: {
            trigger: '@'
        },
        link: function ($scope, $element, $attrs) {

            $scope.$watch('trigger', function (n, o) {
                if (n == 0) {
                    $($attrs.resizerLeft).removeAttr('style');
                    $($attrs.resizerRight).removeAttr('style');
                }
            })

            $element.on('mousedown', function (event) {
                event.preventDefault();
                $document.on('mousemove', mousemove);
                $document.on('mouseup', mouseup);
            });

            function mousemove(event) {

                $($attrs.resizerCover).show();
                // Handle vertical resizer
                var x = event.pageX;
                var i = window.innerWidth;

                if (x < 600) {
                    x = 600;
                }

                if (x > (i - 400)) {
                    x = (i - 400);
                }

                var wl = $($attrs.resizerLeft).width();
                var wr = $($attrs.resizerRight).width();

                $($attrs.resizerLeft).css({
                    width: x + 'px'
                });
                $($attrs.resizerRight).css({
                    width: (i - x) + 'px'
                });
            }

            function mouseup() {
                $($attrs.resizerCover).hide();
                $document.unbind('mousemove', mousemove);
                $document.unbind('mouseup', mouseup);
            }
        }
    }
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
        link: function (scope, element, attr) {
            var msg = attr.ngConfirmClick || "Are you sure?";
            var clickAction = attr.confirmedClick;
            element.bind("click", function (event) {
                if (window.confirm(msg)) {
                    scope.$eval(clickAction)
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
        link: function (scope, element, attrs) {
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
        link: function (scope, element, attrs) {
            element.bind('click', function () {
                $rootScope.$broadcast('insertPasteListener', attrs['clickPastePusher']);
            })
        }
    }
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
        link: function (scope, element, attrs) {
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
    }
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
                    if (n) { // is hidden
                        AdminClassService.modalStackRemove();
                    } else { // is visible
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
        link: function (scope, element) {
            scope.$on('$destroy', function () {
                element.remove();
            });
            angular.element(document.body).append(element);
        }
    }
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
zaa.directive("collapseContainer", [function() {
    return {
        restrict: "E",
        scope: {
            "title" : "@",
            "icon" : "@"
        },
        replace: true,
        transclude: true,
        controller: ['$scope', function($scope) {
            $scope.visible = false;
            $scope.toggleVisibility = function() {
                $scope.visible = !$scope.visible;
            };
        }],
        template: function() {
            return '<div class="card" ng-class="{\'card-closed\': !visible}">'+
                '<div class="card-header" ng-click="toggleVisibility()">'+
                    '<span class="material-icons card-toggle-indicator">keyboard_arrow_down</span>'+
                    '<i class="material-icons" ng-show="icon">{{icon}}</i>'+
                    '<span>{{title}}</span>'+
                '</div>'+
                '<div class="card-body" ng-transclude></div>'+
            '</div>';
        }
    }
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

            $scope.input = { showWindow: true };

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
        template: function () {
            return '<div class="crud-loader-tag"><button ng-click="toggleWindow()" type="button" class="btn btn-info btn-icon"><i class="material-icons">playlist_add</i></button><modal is-modal-hidden="input.showWindow" modal-title="{{alias}}"><div class="modal-body" compile-html ng-bind-html="content"></modal></div>';
        }
    }
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
        template: function () {
            return '<div compile-html ng-bind-html="content"></div>';
        }
    }
}]);

// storage.js
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
        template: function () {
            return '<a ng-show="fileinfo" href="{{ fileinfo.source }}" target="_blank">{{ fileinfo.name_original }}</a>';
        }
    }
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

            $scope.evaluateImages = function() {
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
        template: function () {
            return '<img ng-show="imageSrc" ng-src="{{imageSrc}}" alt="{{imageSrc}}" class="img-fluid rounded border" />';
        }
    }
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
            });

            // controller logic

            $scope.$watch(function () { return $scope.imageId }, function (n, o) {
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
        template: function () {
            return '<div ng-show="imageSrc"><img ng-src="{{imageSrc}}" alt="{{imageSrc}}" class="img-fluid" /></div>';
        }
    }
});

zaa.directive('storageFileUpload', function () {
    return {
        restrict: 'E',
        scope: {
            ngModel: '='
        },
        controller: ['$scope', '$filter', 'ServiceFilesData', function ($scope, $filter, ServiceFilesData) {

            $scope.modal = { state: 1 };

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

            $scope.$watch(function () { return $scope.ngModel }, function (n) {
                if (n === null || n === undefined || !angular.isNumber(n)) {
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
    }
});

/**
 * Storage Image Upload directive.
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
            options: '=',
        },
        controller: ['$scope', '$http', '$filter', 'ServiceFiltersData', 'ServiceImagesData', 'AdminToastService', 'ServiceFilesData', function ($scope, $http, $filter, ServiceFiltersData, ServiceImagesData, AdminToastService, ServiceFilesData) {

            // ServiceFiltesrData inheritance

            //$scope.ngModel = 0;

            $scope.filtersData = ServiceFiltersData.data;

            $scope.$on('service:FiltersData', function (event, data) {
                $scope.filtersData = data;
            });

            // controller logic

            $scope.noFilters = function () {
                if ($scope.options) {
                    return $scope.options.no_filter;
                }
            }

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
                    var images = $filter('filter')(response.images, { filter_id: $scope.filterId });
                    // unable to find the image for the given filter, create the image for the filter
                    if (images.length == 0) {
                        $http.post('admin/api-admin-storage/image-filter', { fileId: $scope.fileId, filterId: $scope.filterId }).then(function (uploadResponse) {
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

            $scope.$watch(function () { return $scope.fileId }, function (n, o) {

                if (n !== null && n !== undefined) {
                    $scope.filterApply();
                }
            });

            $scope.$watch(function () { return $scope.ngModel }, function (n, o) {
                if (n !== null && n !== undefined && n !== 0) {
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
    }
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
        controller: [
            '$scope', '$http', '$filter', '$timeout', '$q', 'HtmlStorage', 'cfpLoadingBar', 'Upload', 'ServiceFoldersData', 'ServiceFilesData', 'LuyaLoading', 'AdminToastService', 'ServiceFoldersDirectoryId', 'ServiceAdminTags', 'ServiceQueueWaiting',
            function ($scope, $http, $filter, $timeout, $q, HtmlStorage, cfpLoadingBar, Upload, ServiceFoldersData, ServiceFilesData, LuyaLoading, AdminToastService, ServiceFoldersDirectoryId, ServiceAdminTags, ServiceQueueWaiting) {

                // ServiceFoldersData inheritance

                $scope.foldersData = ServiceFoldersData.data;

                $scope.$on('service:FoldersData', function (event, data) {
                    $scope.foldersData = data;
                });

                $scope.foldersDataReload = function () {
                    return ServiceFoldersData.load(true);
                };

                // Service Tags

                $scope.tags = [];

                ServiceAdminTags.load().then(function (response) {
                    $scope.tags = response;
                });

                // ServiceFilesData inheritance

                $scope.filesData = [];
                $scope.totalFiles = 0;
                $scope.pageCount = 0;
                $scope.currentPageId = parseInt(HtmlStorage.getValue('filemanager.pageId', 1));

                $scope.$watch('currentPageId', function (pageId, oldPageId) {
                    if (pageId !== undefined && pageId != oldPageId) {
                        $scope.getFilesForCurrentPage();
                    }
                }, true);

                // load files data for a given folder id
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
                }

                $scope.hasFolderActiveChild = function (folderId) {
                    var value = false;
                    angular.forEach($scope.folderInheritance, function (item) {
                        if (item.id == folderId) {
                            value = true;
                        }
                    });

                    return value;
                }

                $scope.getFilesForPageAndFolder = function (folderId, pageId) {
                    return $q(function (resolve, reject) {
                        $http.get($scope.createUrl(folderId, pageId, $scope.sortField, $scope.searchQuery)).then(function (response) {
                            // store sortField
                            HtmlStorage.setValue('filemanager.sortField', $scope.sortField);
                            // store pageId
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
                    $scope.filesData = response.data;
                    // meta
                    $scope.pageCount = response.headers('X-Pagination-Page-Count');
                    $scope.currentPageId = parseInt(response.headers('X-Pagination-Current-Page'));
                    $scope.totalFiles = response.headers('X-Pagination-Total-Count');
                };

                $scope.filesMetaToPagination = function (meta) {
                    $scope.pageCount = meta.totalPages;
                };

                $scope.getFilesForCurrentPage = function () {
                    return $scope.getFilesForPageAndFolder($scope.currentFolderId, $scope.currentPageId);
                };

                // ServiceFolderId

                $scope.currentFolderId = ServiceFoldersDirectoryId.folderId;

                $scope.foldersDirectoryIdReload = function () {
                    return ServiceFoldersDirectoryId.load(true);
                }

                // file replace logic

                $scope.folderCountMessage = function (folder) {
                    return i18nParam('js_filemanager_count_files_overlay', { count: folder.filesCount });
                }

                $scope.errorMsg = null;

                $scope.replaceFile = function (file, errorFiles) {
                    $scope.replaceFiled = file;
                    if (!file) {
                        return;
                    }
                    LuyaLoading.start();

                    Upload.upload({
                        url: 'admin/api-admin-storage/file-replace',
                        data: { file: file, fileId: $scope.fileDetail.id, pageId: $scope.currentPageId }
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
                };

                // upload logic

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
                                fields: { 'folderId': $scope.currentFolderId },
                                file: item.getAsFile()
                            }).then(function (response) {
                                if (response.data.upload) {
                                    ServiceQueueWaiting.waitFor(response.data.queueIds).then(waitForResposne => {
                                        $scope.getFilesForCurrentPage().then(function () {
                                            AdminToastService.success(i18n['js_dir_manager_upload_image_ok']);
                                            $scope.foldersDataReload()
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
                        fields: { 'folderId': $scope.currentFolderId },
                        file: file
                    });

                    file.upload.then(function (response) {
                        $timeout(function () {
                            ServiceQueueWaiting.waitFor(response.data.queueIds).then(waitForResponse => {
                                $scope.uploadResults++;
                                file.processed = true;
                                file.result = response.data;
                                if (!file.result.upload) {
                                    AdminToastService.error(file.result.message);
                                    LuyaLoading.stop();
                                    $scope.errorMsg = true
                                }
                                $scope.foldersDataReload()
                            })
                        });
                    }, function (response) {
                        file = response.data;
                        AdminToastService.error(file.message);
                        LuyaLoading.stop();
                        $scope.errorMsg = true
                    });

                    file.upload.progress(function (evt) {
                        file.processed = false;
                        // Math.min is to fix IE which reports 200% sometimes
                        file.progress = Math.min(100, parseInt(100.0 * evt.loaded / evt.total));
                    });
                }

                // selector logic

                $scope.selectedFiles = [];

                $scope.toggleSelectionAll = function () {
                    $scope.filesData.forEach(function (value, key) {
                        $scope.toggleSelection(value);
                    });
                }

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
                };

                // folder add

                $scope.showFolderForm = false;

                $scope.createNewFolder = function (newFolderName) {
                    if (!newFolderName) {
                        return;
                    }
                    $http.post('admin/api-admin-storage/folder-create', { folderName: newFolderName, parentFolderId: $scope.currentFolderId }).then(function (response) {
                        var folderId = response.data;
                        $scope.foldersDataReload().then(function (response) {
                            $scope.folderFormToggler();
                            $scope.newFolderName = null;
                            $scope.changeCurrentFolderId(folderId);
                        })
                    });
                };

                $scope.folderFormToggler = function () {
                    $scope.showFolderForm = !$scope.showFolderForm;
                };

                // controller logic

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
                                cfpLoadingBar.complete()
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
                        ServiceFoldersDirectoryId.folderId = folderId;
                        $http.post('admin/api-admin-common/save-filemanager-folder-state', { folderId: folderId }, { ignoreLoadingBar: true });
                    }
                };

                $scope.toggleFolderItem = function (data) {
                    if (data.toggle_open == undefined) {
                        data['toggle_open'] = 1;
                    } else {
                        data['toggle_open'] = !data.toggle_open;
                    }
                    $http.post('admin/api-admin-common/filemanager-foldertree-history', { data: data }, { ignoreLoadingBar: true });
                };

                $scope.folderUpdateForm = false;

                $scope.folderDeleteForm = false;

                $scope.folderDeleteConfirmForm = false;

                $scope.updateFolder = function (folder) {
                    $http.post('admin/api-admin-storage/folder-update?folderId=' + folder.id, { name: folder.name }).then(function (transport) {
                        AdminToastService.success(i18n['js_dir_manager_rename_success']);
                    });
                };

                $scope.isFolderMoveModalHidden = true;

                $scope.currentEditFolder = null;

                $scope.cancelFolderEdit = function (folder, oldName) {
                    folder.name = oldName;
                    $scope.isFolderMoveModalHidden = true
                };

                $scope.moveFolderTo = function(targetFolder, toFolderId) {
                    $http.post('admin/api-admin-storage/folder-update?folderId=' + targetFolder.id, { parent_id: toFolderId }).then(function (transport) {
                        AdminToastService.success(i18nParam('js_ngrest_toggler_success', {field: targetFolder.name}));
                        $scope.isFolderMoveModalHidden = true
                        $scope.foldersDataReload()
                    });
                }

                $scope.deleteFolder = function (folder) {
                    $http.post('admin/api-admin-storage/is-folder-empty?folderId=' + folder.id, { name: folder.name }).then(function (transport) {
                        var isEmpty = transport.data.empty;
                        var filesCount = transport.data.count;
                        if (isEmpty) {
                            $http.post('admin/api-admin-storage/folder-delete?folderId=' + folder.id, { name: folder.name }).then(function (transport) {
                                $scope.foldersDataReload().then(function () {
                                    $scope.currentFolderId = 0;
                                });
                            });
                        } else {
                            AdminToastService.confirm(i18nParam('layout_filemanager_remove_dir_not_empty', { folderName: folder.name, count: filesCount }), i18n['js_dir_manager_rm_folder_confirm_title'], ['$timeout', '$toast', function ($timeout, $toast) {
                                $http.post('admin/api-admin-storage/folder-delete?folderId=' + folder.id, { name: folder.name }).then(function () {
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
                        $http.post('admin/api-admin-storage/filemanager-remove-files', { 'ids': $scope.selectedFiles, 'pageId': $scope.currentPageId, 'folderId': $scope.currentFolderId }).then(function (transport) {
                            $scope.getFilesForCurrentPage().then(function () {
                                $toast.close();
                                AdminToastService.success(i18n['js_dir_manager_rm_file_ok']);
                                $scope.selectedFiles = [];
                                $scope.closeFileDetail();
                                $scope.foldersDataReload();
                            });
                        });
                    }]);
                }

                $scope.moveFilesTo = function (folderId) {
                    $http.post('admin/api-admin-storage/filemanager-move-files', { 'fileIds': $scope.selectedFiles, 'toFolderId': folderId, 'currentPageId': $scope.currentPageId, 'currentFolderId': $scope.currentFolderId }).then(function (transport) {
                        $scope.getFilesForCurrentPage().then(function () {
                            $scope.selectedFiles = [];
                            $scope.showFoldersToMove = false;
                        });
                    });
                };

                $scope.getFolderData = function(parentFolderId) {
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

                $scope.detailLoading = false


                $scope.openFileDetail = function (file, force) {
                    if ($scope.fileDetail.id == file.id && force !== true) {
                        $scope.closeFileDetail();
                    } else {
                        cfpLoadingBar.start();
                        $scope.detailLoading = true;
                        ServiceFilesData.getFile(file.id, force).then(function (responseFile) {
                            $scope.fileDetailFull = responseFile;
                            $scope.fileDetailFolder = $scope.foldersData[responseFile.folder_id];
                            $scope.detailLoading = false
                            cfpLoadingBar.complete()
                            
                        }, function () {

                        });

                        $scope.fileDetail = file;
                    }
                };

                $scope.saveTagRelation = function (tag, file) {
                    $http.post('admin/api-admin-storage/toggle-file-tag', { tagId: tag.id, fileId: file.id }).then(function (response) {
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

                $scope.editFile = function(file) {
                    $scope.isFileEditHidden = !$scope.isFileEditHidden;
                };

                $scope.cropSuccess = function() {
                    $scope.isFileEditHidden = true;
                    $scope.getFilesForCurrentPage().then(function () {
                        AdminToastService.success(i18n['crop_success']);
                    });
                    $scope.openFileDetail($scope.fileDetail, true);
                }

                $scope.storeFileCaption = function (fileDetail) {
                    $http.post('admin/api-admin-storage/filemanager-update-caption', { 'id': fileDetail.id, 'captionsText': fileDetail.captionArray, 'pageId': $scope.currentPageId }).then(function (transport) {
                        AdminToastService.success(i18n['file_caption_success']);
                    });
                }

                $scope.selectedFileFromParent = null;

                $scope.init = function () {
                    if ($scope.$parent.fileinfo) {
                        $scope.selectedFileFromParent = $scope.$parent.fileinfo;
                        $scope.changeCurrentFolderId($scope.selectedFileFromParent.folder_id, true);
                    }
                }

                $scope.init();

            }],
        templateUrl: 'storageFileManager'
    }
});

zaa.directive("hasEnoughSpace", ['$window', '$timeout', function ($window, $timeout) {
    return {
        restrict: "A",
        scope: {
            "loadingCondition": "=",
            "isFlexBox": "="
        },
        link: function (scope, element, attrs) {
            scope.elementWidth = 0;

            var getElementOriginalWidth = function () {
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
    }
}]);

zaa.directive('activeClass', function () {
    return {
        restrict: 'A',
        scope: {
            activeClass: '@'
        },
        link: function (scope, element) {
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
zaa.directive('imageEdit', function() {
    return {
        restrict: 'E',
        scope: {
            fileId:'=',
            onSuccess: '&',
        },
        controller: ['$scope', '$http', '$timeout', function($scope, $http, $timeout) {
            // the loaded file to crop
            $scope.file;
            // cropper image
            $scope.cropperImage;
            // cropper config
            $scope.cropperConfig = {
                distUrl:'',
                areaType : 'rectangle',
                ratio : null,
                resultImageSize : 'max',
                resultImageFormat: 'image/jpeg',
                resultImageQuality: 1.0,
                areaInitSize : 200,
                canvasScalemode : 'full-width',
            };

            $scope.changeQuality = function(value) {
                $scope.cropperConfig.resultImageQuality = value;
            };

            $scope.isCurrentQuality = function(value) {
                return $scope.cropperConfig.resultImageQuality == value;
            };

            $http.get('/admin/api-admin-storage/file-info?id=' + $scope.fileId).then(function(response) {
                $scope.file = response.data;
                $scope.cropperConfig.resultImageFormat = $scope.file.mime_type;
                // use the LUYA file controller proxy which ensures accessability, which does not work when using s3 filesystem f.e.
                $scope.cropperImage = $scope.file.file.href;
            });

            $scope.saveAsCopy = true;

            $scope.isCurrentRatio = function(value) {
                return $scope.cropperConfig.ratio == value;
            };

            $scope.changeRatio = function(value) {
                $scope.cropperImage = false;
                $scope.cropperConfig.ratio = value;
                $timeout(function() {
                    $scope.cropperImage = $scope.file.source;
                });
            };

            $scope.save = function() {
                $http.post('/admin/api-admin-storage/file-crop', {
                    distImage: $scope.cropperConfig.distUrl,
                    fileName: $scope.file.name_new_compound,
                    extension: $scope.file.extension,
                    saveAsCopy: $scope.saveAsCopy,
                    fileId: $scope.file.id
                }).then(function(response) {
                    $scope.onSuccess();
                });
            };
        }],
        template : `
    <div class="row">
        <div class="col-md-8">
            <p class="lead">` + i18n['crop_source_image'] + `</p>
            <div class="bg-light rounded pt-3 pl-3 pr-3 pb-2">
            <ui-cropper
                ng-if="cropperImage" 
                image="cropperImage" 
                result-image="cropperConfig.distUrl"
                result-image-format="{{cropperConfig.resultImageFormat}}"
                result-image-quality="cropperConfig.resultImageQuality"
                result-image-size="cropperConfig.resultImageSize"
                area-type="{{cropperConfig.areaType}}" 
                area-init-size="cropperConfig.areaInitSize"
                chargement="'Loading'"
                canvas-scalemode="{{cropperConfig.canvasScalemode}}"
                aspect-ratio="cropperConfig.ratio"
            ></ui-cropper>
            </div>
            <ul class="list-group list-group-horizontal justify-content-center mt-3">
                <li class="list-group-item text-center" ng-class="{'active':isCurrentRatio(null)}" ng-click="changeRatio(null)"><i class="material-icons">crop_free</i><br /><small>` + i18n['crop_size_free'] + `</small></li>
                <li class="list-group-item text-center" ng-class="{'active':isCurrentRatio('1')}" ng-click="changeRatio('1')"><i class="material-icons">crop_square</i><br /><small>` + i18n['crop_size_1to1'] + `</small></li>
                <li class="list-group-item text-center" ng-class="{'active':isCurrentRatio('1.7')}" ng-click="changeRatio('1.7')"><i class="material-icons">crop_16_9</i><br /><small>` + i18n['crop_size_desktop'] + `</small></li>
                <li class="list-group-item text-center" ng-class="{'active':isCurrentRatio('0.5')}" ng-click="changeRatio('0.5')"><i class="material-icons">crop_portrait</i><br /><small>` + i18n['crop_size_mobile'] + `</small></li>
            </ul>
        </div>
        <div class="col-md-4" ng-show="cropperImage">
            <p class="lead">` + i18n['crop_preview'] + `</p>
            <img ng-src="{{cropperConfig.distUrl}}" ng-show="cropperConfig.distUrl" class="img-fluid border" />

            <ul class="list-group list-group-horizontal justify-content-center mt-3">
                <li class="list-group-item text-center" ng-class="{'active':isCurrentQuality(1.0)}" ng-click="changeQuality(1.0)"><i class="material-icons">looks_one</i><br /><small>` + i18n['crop_quality_high'] + `</small></li>
                <li class="list-group-item text-center" ng-class="{'active':isCurrentQuality(0.8)}" ng-click="changeQuality(0.8)"><i class="material-icons">looks_two</i><br /><small>` + i18n['crop_quality_medium'] + `</small></li>
                <li class="list-group-item text-center" ng-class="{'active':isCurrentQuality(0.5)}" ng-click="changeQuality(0.5)"><i class="material-icons">looks_3</i><br /><small>` + i18n['crop_quality_low'] + `</small></li>
            </ul>

            <div class="form-check mt-3 rounded border p-2" ng-click="saveAsCopy=!saveAsCopy" ng-class="{'bg-light':saveAsCopy}">
                <input class="form-check-input" type="checkbox" ng-model="saveAsCopy">
                <label class="form-check-label">
                ` + i18n['crop_btn_as_copy'] + `
                </label>
                <small class="text-muted">` + i18n['crop_btn_as_copy_hint'] + `</small>
            </div>

            <button type="button" ng-show="saveAsCopy" class="mt-3 btn btn-lg btn-icon btn-save" ng-click="save()">`+ i18n['crop_btn_save_copy'] + `</button>
            <button type="button" ng-show="!saveAsCopy" class="mt-3 btn btn-lg btn-icon btn-save" ng-click="save()">`+ i18n['crop_btn_save_replace'] + `</button>
        </div>
    </div>
        `
    }
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

            $scope.$watch('currentPage', function(newVal) {
                $scope.pageNumberInputVal = newVal;
            })
            $scope.$watch('pageNumberInputVal', function() {
                // Set the input width (ato-grow)
                $scope.inputWidth = 25 + (10 * ($scope.pageNumberInputVal.toString().length <= 0 ? 1 : $scope.pageNumberInputVal.toString().length))
            })

            var timeoutPromise = null;
            $scope.pageNumberInputChange = function() {
                if(timeoutPromise) {
                    $timeout.cancel(timeoutPromise)
                }

                // Debounce
                timeoutPromise = $timeout( function() {
                    if(isNaN($scope.pageNumberInputVal)) {
                        // Not a number, reset
                        $scope.pageNumberInputVal = $scope.currentPage
                    } else {
                        // Input is number
                        if(parseInt($scope.pageNumberInputVal) > parseInt($scope.pageCount) || parseInt($scope.pageNumberInputVal) <= 0) {
                            // Out of range, reset
                            $scope.pageNumberInputVal = $scope.currentPage
                        } else {
                            $scope.currentPage = $scope.pageNumberInputVal
                        }
                    }
                }, 500)
            }

            $scope.next = function() {
                if($scope.currentPage < $scope.pageCount) {
                    $scope.currentPage += 1;
                }
            }
            $scope.prev = function() {
                if($scope.currentPage > 1) {
                    $scope.currentPage -= 1;
                }
            }
            $scope.first = function() {
                $scope.currentPage = 1;
            }
            $scope.last = function() {
                $scope.currentPage = $scope.pageCount;
            }
        }],
        template: `
            <div class="pagination" ng-show="pageCount > 1">
                <button class="pagination-btn pagination-btn-first btn btn-icon btn-first-page" ng-click="first()" ng-disabled="currentPage == 1"></button>
                <button class="pagination-btn pagination-btn-prev btn btn-icon btn-prev" ng-click="prev()" ng-disabled="currentPage == 1"></button>
                <div class="pagination-page">
                    <input ng-style="{'max-width': inputWidth + 'px'}" class="form-control pagination-input" type="text" ng-model="pageNumberInputVal" ng-change="pageNumberInputChange()" />
                    <span class="pagination-delimiter">/</span>
                    <span class="pagination-number-of-pages">{{pageCount}}</span>
                </div>
                <button class="pagination-btn pagination-btn-next btn btn-icon btn-next" ng-click="next()" ng-disabled="currentPage == pageCount"></button>
                <button class="pagination-btn pagination-btn-last btn btn-icon btn-last-page" ng-click="last()" ng-disabled="currentPage == pageCount"></button>
            </div>
        `,
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
            'optionslabel': '@optionslabel',
        },
        controller: ['$rootScope', '$scope',  function ($rootScope, $scope) {
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


        template: function () {
            return '<span>{{getSelectedLabel()}}</span>';
        }
    };
});
