<?php

namespace luya\admin\apis;

use luya\admin\base\RestController;
use luya\admin\helpers\Angular;
use luya\admin\models\UserAuthNotification;
use luya\admin\models\UserOnline;
use luya\traits\CacheableTrait;
use Yii;

/**
 * Timestamp API, refreshes the UserOnline system of the administration area.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class TimestampController extends RestController
{
    use CacheableTrait;

    /**
     * The timestamp action provider informations about currenct only users and if the ui needs to be refreshed.
     *
     * @uses integer $lastKeyStroke
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

        // get the stroke-dashoffset for the given user, this indicates the time he is idling
        // stroke-dashoffset="88px" = 0 // which means 0 percent of time has elapsed
        // stroke-dashoffset="0px" => 100 // which means 100 percent of time has elpased, auto logout will redirect the user
        $seconds = (time() - $userOnlineModel->last_timestamp);
        $percentage = round(($seconds / $this->module->userIdleTimeout) * 100);
        $offsetPercent = round((81 / 100) * $percentage);
        $strokeOffset = 81 - $offsetPercent;

        // return users, verify force reload.
        $data = [
            'notifications' => $this->getAuthNotifications(),
            'lastKeyStroke' => $lastKeyStroke,
            'idleSeconds' => $seconds,
            'idleTimeRelative' => round(($this->module->userIdleTimeout - $seconds) / 60),
            'idlePercentage' => $percentage,
            'idleStrokeDashoffset' => $strokeOffset,
            'useronline' => UserOnline::getList(),
            'forceReload' => Angular::typeCast(Yii::$app->adminuser->identity->force_reload),
            'locked' => UserOnline::find()
                ->select(['user_id', 'lock_pk', 'lock_table', 'last_timestamp', 'firstname', 'lastname', 'admin_user.id'])
                ->where(['!=', 'admin_user.id', $userId])
                ->joinWith('user')
                ->asArray()
                ->all(),
        ];

        return $data;
    }

    /**
     * Returns an array with auth_id as key and value is the diff notification count.
     *
     * @return array An array with key auth_id and value diff count
     * @since 2.0.0
     */
    private function getAuthNotifications()
    {
        $diff = [];
        foreach (UserAuthNotification::find()->where(['user_id' => Yii::$app->adminuser->id, 'is_muted' => false])->all() as $uan) {
            $diff[$uan->auth_id] = $uan->getDiffCount();
        }

        return $diff;
    }
}
