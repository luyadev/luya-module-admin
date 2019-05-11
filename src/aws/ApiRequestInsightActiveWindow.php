<?php

namespace luya\admin\aws;

use luya\admin\ngrest\base\ActiveWindow;
use luya\admin\models\UserRequest;
use yii\data\ActiveDataProvider;

class ApiRequestInsightActiveWindow extends ActiveWindow
{
    /**
     * @var string The name of the module where the ActiveWindow is located in order to finde the view path.
     */
    public $module = '@admin';

    /**
     * Default label if not set in the ngrest model.
     *
     * @return string The name of of the ActiveWindow. This is displayed in the CRUD list.
     */
    public function defaultLabel()
    {
        return 'Request Insight';
    }

    /**
     * Default icon if not set in the ngrest model.
     *
     * @var string The icon name from goolges material icon set (https://material.io/icons/)
     */
    public function defaultIcon()
    {
        return 'assessment';
    }

    /**
     * The default action which is going to be requested when clicking the ActiveWindow.
     *
     * @return string The response string, render and displayed trough the angular ajax request.
     */
    public function index()
    {
        return $this->render('index', [
            'model' => $this->model,
            'isEnabled' => $this->model->is_request_logger_enabled,
            'count' => UserRequest::find()->where(['user_id' => $this->model->id])->count(),
        ]);
    }

    public function callbackData($page = 1)
    {
        return new ActiveDataProvider([
            'query' => UserRequest::find()->where(['user_id' => $this->model->id]),
            'sort' => ['defaultOrder' => ['timestamp' => SORT_DESC]]
        ]);
    }
}