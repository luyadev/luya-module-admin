<?php

namespace luya\admin\models;

use Yii;
use luya\admin\ngrest\base\NgRestModel;
use luya\admin\aws\ApiOverviewActiveWindow;

/**
 * User Model represents all Administration Users.
 *
 * @property integer $id
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
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
final class ApiUser extends NgRestModel
{
	public static function tableName()
	{
		return 'admin_user';
	}
	
	public function init()
	{
		parent::init();
		
		$this->on(self::EVENT_AFTER_VALIDATE, function($e) {
			$this->is_api_user = true;
		});
	}
	
	public static function ngRestApiEndpoint()
	{
		return 'api-admin-apiuser';
	}

	public static function ngRestFind()
	{
		return self::find()->where(['is_api_user' => true]);
	}
	
	public function rules()
	{
		return [
			[['firstname', 'lastname', 'email'], 'required'],
			[['email'], 'unique'],
		];
	}
	
	public function ngRestAttributeTypes()
	{
		return [
			'firstname' => 'text',
			'lastname' => 'text',
			'email' => 'text',
		];
	}
	
	public function ngRestScopes()
	{
		return [
			[['list', 'create', 'update'], ['firstname', 'lastname', 'email']]	
		];
	}
	
	public function ngRestActiveWindows()
	{
		return [
			['class' => ApiOverviewActiveWindow::class],
		];
	}
}