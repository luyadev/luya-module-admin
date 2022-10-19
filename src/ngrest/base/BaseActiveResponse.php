<?php

namespace luya\admin\ngrest\base;

use Yii;
use yii\base\BaseObject;

/**
 * Base Response for Active Window, Button and Selection.
 *
 * @author Basil Suter <git@nadar.io>
 * @since 4.0.0
 */
abstract class BaseActiveResponse extends BaseObject
{
    /**
     * @var string The loadList event name
     */
    public const EVENT_RELOAD_LIST = 'loadList';

    private array $_events = [];

    /**
     * Send a CRUD reload event.
     */
    public function sendReloadEvent()
    {
        $this->_events[] = self::EVENT_RELOAD_LIST;
    }

    /**
     * Send an error message as response.
     *
     * Events are only triggered on success messages {{sendSuccess()}}.
     *
     * @param string $message The error message.
     * @param array $additionalResponseData Data which should be added to the xhr response.
     * @return array An array with `success`, `error`, `message`, `responseData` and `events`.
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
     * @param array $additionalResponseData Data which should be added to the xhr response.
     * @return array An array with `success`, `error`, `message`, `responseData` and `events`.
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
