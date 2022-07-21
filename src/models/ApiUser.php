<?php

namespace luya\admin\models;

use luya\admin\aws\ApiOverviewActiveWindow;
use luya\admin\aws\ApiRequestInsightActiveWindow;
use luya\admin\aws\UserHistorySummaryActiveWindow;
use Yii;

/**
 * User Model represents all Administration Users.
 *
 * @property integer $id User Primary Key
 * @property string $firstname
 * @property string $lastname
 * @property integer $title
 * @property string $email
 * @property string $password
 * @property string $password_salt
 * @property string $auth_token
 * @property integer $is_deleted
 * @property string $secure_token
 * @property integer $secure_token_timestamp
 * @property integer $force_reload
 * @property string $settings
 * @property \luya\admin\models\UserSetting $setting Setting object to store data.
 * @property integer $is_api_user
 * @property integer $api_rate_limit
 * @property string $api_allowed_ips
 * @property integer $api_last_activity
 * @property boolean $is_request_logger_enabled
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.1.0
 */
final class ApiUser extends User
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admin_user}}';
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // allow only is_api_user flagged edit and adds
        $this->on(self::EVENT_BEFORE_VALIDATE, function () {
            $this->is_api_user = true;
            if ($this->isNewRecord) {
                $this->password = Yii::$app->security->generateRandomString();
                $this->password_salt = Yii::$app->security->generateRandomString();
            }
        });
    }

    /**
     * @inheritdoc
     */
    public static function ngRestApiEndpoint()
    {
        return 'api-admin-apiuser';
    }

    /**
     * @inheritdoc
     */
    public static function ngRestFind()
    {
        return self::find()->where(['is_api_user' => true]);
    }

    /**
     * @inheritdoc
     */
    public function ngRestFilters()
    {
        return [
            'Removed' => self::find()->where(['is_deleted' => true, 'is_api_user' => true]),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['firstname', 'lastname', 'email'], 'required'],
            [['email'], 'email'],
            [['password_salt'], 'string'],
            [['email', 'auth_token'], 'unique'],
            [['is_request_logger_enabled'], 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function ngRestAttributeTypes()
    {
        return [
            'firstname' => 'text',
            'email' => 'text',
            'lastname' => 'text',
            'is_request_logger_enabled' => 'toggleStatus',
        ];
    }

    /**
     * @inheritdoc
     */
    public function ngRestScopes()
    {
        return [
            [['list', 'update', 'create'], ['firstname', 'lastname', 'email']],
            [['delete'], true],
        ];
    }

    /**
     * @inheritdoc
     */
    public function ngRestActiveWindows()
    {
        return [
            ['class' => ApiOverviewActiveWindow::class, 'label' => false],
            ['class' => UserHistorySummaryActiveWindow::class, 'label' => false],
            ['class' => ApiRequestInsightActiveWindow::class, 'label' => false],
        ];
    }
}
