<?php

namespace luya\admin\apis;

use Yii;
use luya\admin\models\UserOnline;
use luya\admin\base\RestController;
use luya\admin\models\Config;

/**
 * Timestamp API, refreshes the UserOnline system of the administration area.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class TimestampController extends RestController
{
    /**
     * The timestamp action provider informations about currenct only users and if the ui needs to be refreshed.
     *
     * @return array
     */
    public function actionIndex()
    {
        // clear user online list
        UserOnline::clearList();
        
        if (!UserOnline::findOne(['user_id' => Yii::$app->adminuser->id])) {
            Yii::$app->response->statusCode = 401;
            return Yii::$app->response->send();
        }
        
        $forceReload = Yii::$app->adminuser->identity->force_reload;
                
        // if developer is enabled, check if vendor has changed and run the required commands and force reload
        if (!YII_ENV_PROD) {
            $config = (int) Config::get(Config::CONFIG_INSTALLER_VENDOR_TIMESTAMP, null);
            $ts = Yii::$app->packageInstaller->timestamp;
            if ($config !== $ts) {
                // run migration and import process for developer usage.
            }
        }
        
        // return users, verify force reload.
        $data = [
            'useronline' => UserOnline::getList(),
            'forceReload' => $forceReload,
            'locked' => UserOnline::find()->select(['lock_pk', 'lock_table', 'last_timestamp', 'u.firstname', 'u.lastname', 'u.id'])->where(['!=', 'u.id', Yii::$app->adminuser->id])->joinWith('user as u')->createCommand()->queryAll(),
        ];
        
        return $data;
    }
}
