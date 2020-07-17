<?php

namespace luya\admin\controllers;

use Yii;
use luya\rest\Controller;

/**
 * Uptime Tests
 *
 * This controler is used for uptime status robots.
 *
 * In order to call this controller use `https://mydomain.com/admin/uptime`.
 *
 * @since 3.1.0
 * @author Basil Suter <git@nadar.io>
 */
class UptimeController extends Controller
{
    /**
     * Return an array with status indicators.
     *
     * @return array
     */
    public function actionIndex()
    {
        Yii::$app->db->open();

        return [
            'date' => time(),
            'db' => Yii::$app->db->isActive,
        ];
    }
}
