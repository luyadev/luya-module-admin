<?php

namespace luya\admin\models;

use luya\admin\aws\DetailViewActiveWindow;
use luya\admin\Module;
use luya\admin\ngrest\base\NgRestModel;
use luya\admin\traits\SoftDeleteTrait;
use luya\helpers\Url;
use Yii;

/**
 * Proxy Machine.
 *
 * File has been created with `crud/create` command on LUYA version 1.0.0.
 *
 * @property integer $id
 * @property string $name
 * @property string $identifier
 * @property string $access_token
 * @property smallint $is_deleted
 * @property smallint $is_disabled
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class ProxyMachine extends NgRestModel
{
    use SoftDeleteTrait;

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();

        $this->on(self::EVENT_BEFORE_VALIDATE, [$this, 'generateIdentifierAndToken']);
    }

    /**
     * Generate the identifier and access token.
     *
     * Only when creating a new record.
     */
    public function generateIdentifierAndToken()
    {
        if ($this->isNewRecord) {
            $this->identifier = uniqid('lcp');
            $this->access_token = str_replace(['-', '_'], random_int(1, 9), Yii::$app->security->generateRandomString(32));
        }
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admin_proxy_machine}}';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => Module::t('model_proxy_machine_name_label'),
            'access_token' => Module::t('model_proxy_machine_access_token_label'),
            'is_deleted' => Module::t('model_proxy_machine_is_deleted_label'),
            'is_disabled' => Module::t('model_proxy_machine_is_disabled_label'),
            'identifier' => Module::t('model_proxy_machine_identifier_label'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'access_token', 'identifier'], 'required'],
            [['is_deleted', 'is_disabled'], 'boolean'],
            [['name', 'access_token', 'identifier'], 'string', 'max' => 255],
        ];
    }

    /**
     * @return string Defines the api endpoint for the angular calls
     */
    public static function ngRestApiEndpoint()
    {
        return 'api-admin-proxymachine';
    }

    /**
     * @return array An array define the field types of each field
     */
    public function ngRestAttributeTypes()
    {
        return [
            'name' => 'text',
            'access_token' => 'text',
            'identifier' => 'text',
            'is_disabled' => 'toggleStatus',
        ];
    }

    /**
     * @inheritdoc
     */
    public function ngRestScopes()
    {
        return [
            [['list'], ['name', 'identifier', 'access_token', 'is_disabled']],
            [['create', 'update'], ['name']],
            [['delete'], true],
        ];
    }

    /**
     * @inheritdoc
     */
    public function ngRestActiveWindows()
    {
        return [
            [
                'class' => DetailViewActiveWindow::class,
                'intro' => 'For more details about how to use the sync command, take a look at the <a class="text-primary" href="https://api.luya.io/luya-admin-commands-proxycontroller.html" target="_blank">admin/proxy guide</a>.',
                'attributes' => [
                    'name',
                    'identifier',
                    'access_token',
                    [
                        'label' => 'cli',
                        'format' => 'html',
                        'value' => fn ($model) => '<code>admin/proxy --url='.Url::base(true).' --idf='.$model->identifier.' --token=' . $model->access_token . '</code>'
                    ]
                ]
            ]
        ];
    }
}
