<p align="center">
  <img src="https://raw.githubusercontent.com/luyadev/luya/master/docs/logo/luya-logo-0.2x.png" alt="LUYA Logo"/>
</p>

# LUYA Administration Interface module

[![LUYA](https://img.shields.io/badge/Powered%20by-LUYA-brightgreen.svg)](https://luya.io)
[![Build Status](https://travis-ci.org/luyadev/luya-module-admin.svg?branch=master)](https://travis-ci.org/luyadev/luya-module-admin)
[![Test Coverage](https://api.codeclimate.com/v1/badges/26ce6892fcb4899cbd49/test_coverage)](https://codeclimate.com/github/luyadev/luya-module-admin/test_coverage)
[![Maintainability](https://api.codeclimate.com/v1/badges/26ce6892fcb4899cbd49/maintainability)](https://codeclimate.com/github/luyadev/luya-module-admin/maintainability)
[![Latest Stable Version](https://poser.pugx.org/luyadev/luya-module-admin/v/stable)](https://packagist.org/packages/luyadev/luya-module-admin)
[![Total Downloads](https://poser.pugx.org/luyadev/luya-module-admin/downloads)](https://packagist.org/packages/luyadev/luya-module-admin)
[![Slack Support](https://img.shields.io/badge/Slack-luyadev-yellowgreen.svg)](https://slack.luya.io/)

Administration Interface based on [AngularJs](https://angularjs.org/), [Bootstrap 4](https://getbootstrap.com) and [Yii 2 Framework](http://www.yiiframework.com/) (which is wrapped in the LUYA CORE).

![LUYA Admin Interface](https://raw.githubusercontent.com/luyadev/luya-module-admin/master/1.0.0-crud.png)

+ CRUD (based on RESTful and Angular)
+ Scaffolding CRUDs
+ Syncing Project between Environments
+ Storage System for Files and Images, also known as File Manager.
+ Permission System with Users and Groups.
+ Searching trough all Modules and Models.

![LUYA Admin Globalsearch](https://raw.githubusercontent.com/luyadev/luya-module-admin/master/1.0.0-globalsearch.png)

## Installation

For the installation of modules Composer is required.

```sh
composer require luyadev/luya-module-admin
```

### Configuration 

After installation via Composer include the module to your configuration file within the modules section.

```php
'modules' => [
    // ... 
    'admin' => [
        'class' => 'luya\admin\Module',
    ]
]
```

### Initialization 

After successfully installation and configuration run the migrate, import and setup command to initialize the module in your project.

1.) Migrate your database.

```sh
./vendor/bin/luya migrate
```

2.) Import the module and migrations into your LUYA project.

```sh
./vendor/bin/luya import
```

3.) Create admin user and and user groups.

```sh
./vendor/bin/luya admin/setup
```

You can now login to your Administration Interface by adding the admin module in the Url: `http://example.com/admin`


## Developers

If you want to contribute, make sure to read the following guidelines: https://luya.io/guide/luya-guideline.
