<?php

namespace luya\admin\ngrest\base;

use Yii;
use yii\base\BaseObject;

abstract class BaseActiveResponse extends BaseObject
{
    /**
     * @var string The loadList event name
     */
    const EVENT_RELOAD_LIST = 'loadList';

    private $_events = [];
    
    /**
     * Send a crud reload event.
     *
     * @return void
     */
    protected function sendReloadEvent()
    {
        $this->_events[] = self::EVENT_RELOAD_LIST;
    }

    /**
     * Send an error message as response.
     *
     * Events are only triggered on success messages {{sendSuccess()}}.
     *
     * @param string $message The error message.
     * @return array
     */
    public function sendError($message, array $additionalResponseData = [])
    {
        Yii::$app->response->setStatusCode(422, 'Data Validation Failed.');

        return [
            'success' => false,
            'error' => true,
            'message' => $message,
            'responseData' => $additionalResponseData,
            'events' => $this->_events,
        ];
    }

    /**
     * Send a success message.
     *
     * @param string $message The sucess message.
     * @return array
     */
    public function sendSuccess($message, array $additionalResponseData = [])
    {
        return [
            'success' => true,
            'error' => false,
            'message' => $message,
            'responseData' => $additionalResponseData,
            'events' => $this->_events,
        ];
    }
}