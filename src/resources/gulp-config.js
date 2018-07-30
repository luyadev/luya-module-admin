const imagemin = require("gulp-imagemin");
const browserlist = ["> 0.5%"];

module.exports = {
    css: {
        scss: {
            config: {
                outputStyle: "compressed" // nested, compact, expanded and compressed are available options
            }
        },

        sourcemaps: {
            enabled: "local"
        },

        autoprefixer: {
            enabled: true,
            config: {
                browsers: browserlist
            }
        },

        cleanCss: {
            enabled: true,
            config: {
                compatibility: "ie8"
            }
        }
    },

    js: {
        sourcemaps: {
            enabled: "local"
        },
        browserify: {
            enabled: false
        },
        uglify: {
            enabled: false
        },
        babeljs: {
            enabled: false,
            config: {
                minified: false,
                comments: false
            }
        }
    },

    clean: {
        enabled: "prod",
        paths: ["dist/**/*.map"]
    },

    images: {
        imagemin: {
            enabled: true,
            config: [
                imagemin.gifsicle({ interlaced: true }),
                imagemin.jpegtran({ progressive: true }),
                imagemin.optipng({ optimizationLevel: 5 }),
                imagemin.svgo({ plugins: [{ removeViewBox: true }] })
            ]
        }
    },

    svg: {
        svgmin: {
            enabled: true,
            config: {}
        }
    },

    extraTasks: {
        jsUglify: {
            runAsTask: 'js',
            sourcemaps: {
                enabled: "local"
            },
            browserify: {
                enabled: false
            },
            uglify: {
                enabled: "prod"
            },
            babeljs: {
                enabled: false,
                config: {
                    minified: false,
                    comments: false
                }
            }
        },
        purgeDist: {
            runAsTask: 'clean',
            enabled: true,
            paths: ["dist/*"]
        }
    },


    paths: {
        // "DESTINATION" : ['SOURCE']
        css: {
            "dist/css/": ["scss/**/*.scss"]
        },
        js: {
            "dist/js/main.js": [
                "../../vendor/bower-asset/angular/angular.min.js",
                "vendorlibs/ng-colorwheel/ng-colorwheel.js",
                "../../vendor/bower-asset/angular-loading-bar/build/loading-bar.min.js",
                "../../vendor/bower-asset/angularjs-datepicker/dist/angular-datepicker.min.js",
                "../../vendor/bower-asset/ui-router/release/angular-ui-router.min.js",
                "../../vendor/bower-asset/ng-file-upload/ng-file-upload-shim.min.js",
                "../../vendor/bower-asset/ng-file-upload/ng-file-upload.min.js",
                "../../vendor/bower-asset/ng-flow/dist/ng-flow-standalone.min.js",
                "../../vendor/bower-asset/ng-wig/dist/ng-wig.min.js",
                "../../vendor/bower-asset/twigjs-bower/twig/twig.js",
                "../../vendor/bower-asset/angular-filter/dist/angular-filter.min.js",
                // Colorwheel?
                "../../vendor/bower-asset/bowser/src/useragent.js",
                "../../vendor/bower-asset/bowser/src/bowser.js",
                "../../vendor/bower-asset/echarts/dist/echarts.min.js",
                "../../vendor/bower-asset/angularjs-slider/dist/rzslider.js",
            ]
        },
        jsUglify: {
            "dist/js/main.uglified.js": [
            	"js/dnd.js",
                "js/zaa.js",
                "js/services.js",
                "js/filters.js",
                "js/directives.js",
                "js/controllers.js",
            ],
            "dist/js/login.js": [
                "js/login.js"
            ]
        },
        images: {
            "images/": [
                "images/**/*.jpeg",
                "images/**/*.jpg",
                "images/**/*.png",
                "images/**/*.gif"
            ]
        },
        svg: {
            "svg/": ["svg/**/*.svg"]
        }
    },

    // All tasks above are available (css, js, images and svg)
    combinedTasks: {
        default: [["dist", "watch"]],
        dist: ["purgeDist", "css", "js", "jsUglify", "images", "svg", "clean"]
    },

    watchTask: {
        css: ["css"],
        js: ["js"],
        jsUglify: ["jsUglify"]
    }
};
