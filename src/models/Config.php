<?php

namespace luya\admin\models;

use luya\admin\Module;
use luya\admin\ngrest\base\NgRestModel;
use luya\traits\RegistryTrait;

/**
 * Admin Config Model.
 *
 * To work with Admin Config values you have to create the configuration value in the admin UI and can afterwards recieve those values. Its also
 * possible to create new registry values. The {{luya\admin\traits\RegistryTrait}} is responsible for get, set, has and remove.
 *
 * + {{luya\admin\traits\RegistryTrait::get()}}
 * + {{luya\admin\traits\RegistryTrait::set()}}
 * + {{luya\admin\traits\RegistryTrait::has()}}
 * + {{luya\admin\traits\RegistryTrait::remove()}}
 *
 * Create a new Config with value if does not exists.
 *
 * ```php
 * if (!Config::has('myVariable')) {
 *     Config::set('myVariable', 'myValue');
 * }
 *
 * $value = Config::get('myVariable');
 * ```
 *
 * > This system is widely used to store CMS informations like navigations ids en retrieve those late.
 *
 * @property string $name
 * @property string $value
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
final class Config extends NgRestModel
{
    use RegistryTrait;

    public const CONFIG_LAST_IMPORT_TIMESTAMP = 'last_import_timestamp';

    public const CONFIG_SETUP_COMMAND_TIMESTAMP = 'setup_command_timestamp';

    public const CONFIG_INSTALLER_VENDOR_TIMESTAMP = 'installer_vendor_timestamp';

    /**
     * @since 2.0.0
     */
    public const CONFIG_QUEUE_TIMESTAMP = 'queue_timestamp';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admin_config}}';
    }

    /**
     * @inheritdoc
     */
    public static function ngRestApiEndpoint()
    {
        return 'api-admin-config';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'value'], 'required'],
            [['name'], 'unique'],
            [['is_system'], 'integer'],
            ['is_system', 'default', 'value' => false]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => Module::t('model_config_atr_name'),
            'value' => Module::t('model_config_atr_value'),
            'is_system' => Module::t('model_config_atr_is_system'),
        ];
    }

    public function attributeHints()
    {
        return [
            'name' => Module::t('model_config_atr_name_hint'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function ngRestAttributeTypes()
    {
        return [
            'value' => 'textarea',
            'name' => 'text',
            'is_system' => ['hidden', 'value' => 0],
        ];
    }

    /**
     * @inheritdoc
     */
    public function ngRestScopes()
    {
        return [
            [['list'], ['name', 'value']],
            [['create', 'update'], ['name', 'value', 'is_system']],
            [['delete'], true],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function ngRestFind()
    {
        return parent::ngRestFind()->where(['is_system' => false]);
    }
}
