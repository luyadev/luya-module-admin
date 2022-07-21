<?php

namespace luya\admin\behaviors;

use luya\admin\models\UserRequest;
use Yii;
use yii\base\ActionFilter;

/**
 * Measure the API request time for a given user if enabled.
 */
class UserRequestBehavior extends ActionFilter
{
    private $_startTime;

    /**
     * Whether request log is enabled or not.
     *
     * @return boolean
     */
    private function hasUserEnabled()
    {
        return Yii::$app->adminuser->isGuest ? false : Yii::$app->adminuser->identity->is_request_logger_enabled;
    }

    /**
     * {@inheritDoc}
     */
    public function beforeAction($action)
    {
        $this->_startTime = microtime(true);
        return parent::beforeAction($action);
    }

    /**
     * {@inheritDoc}
     */
    public function afterAction($action, $result)
    {
        // check if the owner controller has enabled userAuthntification && adminuser is not guest.
        if ($this->owner->userAuthClass() && $this->hasUserEnabled()) {
            $time = microtime(true) - $this->_startTime;
            $request = new UserRequest();
            $request->detachBehavior('LogBehavior');
            $request->timestamp = time();
            $request->user_id = Yii::$app->adminuser->id;
            $request->response_time = round($time * 1000);
            $request->request_url = Yii::$app->request->url;
            $request->request_method = Yii::$app->request->method;
            $request->save();
        }

        return parent::afterAction($action, $result);
    }
}
