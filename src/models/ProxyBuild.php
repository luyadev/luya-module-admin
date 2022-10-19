<?php

namespace luya\admin\models;

use luya\admin\aws\DetailViewActiveWindow;
use luya\admin\Module;
use luya\admin\ngrest\base\NgRestModel;
use yii\helpers\Json;

/**
 * This is the model class for table "admin_proxy_build".
 *
 * @property integer $id
 * @property integer $machine_id
 * @property integer $timestamp
 * @property string $build_token
 * @property string $config
 * @property integer $is_complet
 * @property integer $expiration_time
 *
 * @property ProxyMachine $proxyMachine
 * @property array $arrayConfig
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class ProxyBuild extends NgRestModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admin_proxy_build}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['machine_id', 'timestamp', 'build_token', 'config', 'expiration_time'], 'required'],
            [['machine_id', 'timestamp', 'expiration_time'], 'integer'],
            [['is_complet'], 'boolean'],
            [['config'], 'string'],
            [['build_token'], 'string', 'max' => 190],
            [['build_token'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'machine_id' => Module::t('model_proxy_build_machine_id_label'),
            'timestamp' => Module::t('model_proxy_build_timestamp_label'),
            'build_token' => Module::t('model_proxy_build_build_token_label'),
            'config' => Module::t('model_proxy_build_config_label'),
            'is_complet' => Module::t('model_proxy_build_is_complete_label'),
            'expiration_time' => Module::t('model_proxy_build_expiration_time_label'),
        ];
    }

    /**
     * ProxyMachine relation
     *
     * @return ProxyMachine
     */
    public function getProxyMachine()
    {
        return $this->hasOne(ProxyMachine::class, ['id' => 'machine_id']);
    }

    /**
     * @return string Defines the api endpoint for the angular calls
     */
    public static function ngRestApiEndpoint()
    {
        return 'api-admin-proxybuild';
    }

    private $_arrayConfig;

    /**
     * Get an array from the config json
     *
     * @return array
     */
    public function getArrayConfig()
    {
        if ($this->_arrayConfig === null) {
            $this->_arrayConfig = Json::decode($this->config);
        }

        return $this->_arrayConfig;
    }

    /**
     * Get rowsPerRequest from json config
     *
     * @return integer
     */
    public function getRowsPerRequest()
    {
        return $this->arrayConfig['rowsPerRequest'];
    }

    /**
     * Get the full configuration for a given table
     *
     * @param string $table
     * @return array|false False if the table is not found
     */
    public function getTableConfig($table)
    {
        return $this->arrayConfig['tables'][$table] ?? false;
    }

    /**
     * {@inheritDoc}
     */
    public function ngRestAttributeTypes()
    {
        return [
            'machine_id' => ['selectModel', 'modelClass' => ProxyMachine::class, 'valueField' => 'id', 'labelField' => 'name'],
            'timestamp' => 'datetime',
            'build_token' => 'text',
            'is_complet' => 'toggleStatus',
            'expiration_time' => 'datetime',
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function ngRestScopes()
    {
        return [
            [['list'], ['machine_id', 'build_token', 'expiration_time', 'is_complet']]
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function ngRestActiveWindows()
    {
        return [
            [
                'class' => DetailViewActiveWindow::class,
            ]
        ];
    }
}
