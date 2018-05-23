<?php

namespace luya\admin\apis;

use Yii;
use luya\admin\models\UserOnline;
use luya\admin\base\RestController;

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
        $userId = Yii::$app->adminuser->id;
        
        // clear user online list
        UserOnline::clearList($this->module->userIdleTimeout);
        $userOnlineModel = UserOnline::findOne(['user_id' => Yii::$app->adminuser->id]);
        
        if (!$userOnlineModel) {
            Yii::$app->response->statusCode = 401;
            return Yii::$app->response->send();
        }
        
        // update keystrokes
        $lastKeyStroke = Yii::$app->request->getBodyParam('lastKeyStroke');
        if (Yii::$app->session->get('__lastKeyStroke') != $lastKeyStroke) {
            // refresh the user timestamp
            $userOnlineModel->last_timestamp = time();
            $userOnlineModel->update(true, ['last_timestamp']);
        }
        
        Yii::$app->session->set('__lastKeyStroke', $lastKeyStroke);
        
        // if developer is enabled, check if vendor has changed and run the required commands and force reload
        // @TODO: its a concept
        /*
        if (!YII_ENV_PROD) {
            $config = (int) Config::get(Config::CONFIG_INSTALLER_VENDOR_TIMESTAMP, null);
            $ts = Yii::$app->packageInstaller->timestamp;
            if ($config !== $ts) {
                // run migration and import process for developer usage.
            }
        }
        */
        
        // get the stroke-dashoffset for the given user, this indicates the time he is idling
        // stroke-dashoffset="88px" = 0 // which means 0 percent of time has elapsed
        // stroke-dashoffset="0px" => 100 // which means 100 percent of time has elpased, auto logout will redirect the user
        $seconds = (time() - $userOnlineModel->last_timestamp);
        $percentage = round(($seconds / $this->module->userIdleTimeout) * 100);
        $offsetPercent = round((81/100) * $percentage);
        $strokeOffset = 81 - $offsetPercent;
        
        
        
        // return users, verify force reload.
        $data = [
            'lastKeyStroke' => $lastKeyStroke,
            'idleSeconds' => $seconds,
            'idleTimeRelative' => round(($this->module->userIdleTimeout-$seconds) / 60),
            'idlePercentage' => $percentage,
            'idleStrokeDashoffset' => $strokeOffset,
            'useronline' => UserOnline::getList(),
            'forceReload' => Yii::$app->adminuser->identity->force_reload,
            'locked' => UserOnline::find()->select(['lock_pk', 'lock_table', 'last_timestamp', 'u.firstname', 'u.lastname', 'u.id'])->where(['!=', 'u.id', $userId])->joinWith('user as u')->createCommand()->queryAll(),
        ];
        
        return $data;
    }
}
