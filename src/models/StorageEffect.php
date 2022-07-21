<?php

namespace luya\admin\models;

use luya\admin\aws\DetailViewActiveWindow;
use luya\admin\Module;
use luya\admin\ngrest\base\NgRestModel;

/**
 * This is the model class for table "admin_storage_effect".
 *
 * @property integer $id
 * @property string $identifier
 * @property string $name
 * @property string $imagine_name
 * @property string $imagine_json_params
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
final class StorageEffect extends NgRestModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admin_storage_effect}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['identifier'], 'required'],
            [['imagine_json_params'], 'string'],
            [['identifier'], 'string', 'max' => 100],
            [['name', 'imagine_name'], 'string', 'max' => 255],
            [['identifier'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'identifier' => Module::t('model_storageeffect_identifier'),
            'name' => Module::t('model_storageeffect_name'),
            'imagine_name' => Module::t('model_storageeffect_imagine_name'),
            'imagine_json_params' => Module::t('model_storageeffect_imagine_json_params'),
        ];
    }

    /**
     * @inheritdoc
     */
    public static function ngRestApiEndpoint()
    {
        return 'api-admin-effect';
    }

    /**
     * @inheritdoc
     */
    public function ngRestAttributeTypes()
    {
        return [
            'name' => 'text',
            'identifier' => 'text',
            'imagine_name' => 'text',
            'imagine_json_params' => ['textarea', 'encoding' => false],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function ngRestScopes()
    {
        return [
            [['list'], ['identifier']],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function ngRestActiveWindows()
    {
        return [
            ['class' => DetailViewActiveWindow::class],
        ];
    }

    /**
     * Returns the effect name ensured with lowercase.
     *
     * @return string
     * @since 1.0.2
     */
    public function getImagineEffectName()
    {
        return strtolower($this->imagine_name);
    }
}
