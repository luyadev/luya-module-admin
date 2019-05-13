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
        ]);
    }

    public function callbackData($page = 1)
    {
        return new ActiveDataProvider([
            'query' => UserRequest::find()->where(['user_id' => $this->model->id]),
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
            'pagination' => [
                'page' => $page,
                'pageSize' => 23,
            ]
        ]);
    }

    public function callbackToggle()
    {
        $status = !$this->model->is_request_logger_enabled;

        if ($this->model->updateAttributes([
            'is_request_logger_enabled' => $status,
        ])) {
            return $this->sendSuccess("Enabled/Disabled");
        }

        return $this->sendError("Error while enabling request logger.");
    }

    public function callbackInsight()
    {
        return [
            'avarage' => UserRequest::find()->select(['response_time'])->where(['user_id' => $this->model->id])->average('response_time'),
            'max' => UserRequest::find()->select(['response_time'])->where(['user_id' => $this->model->id])->max('response_time'),
            'min' => UserRequest::find()->select(['response_time'])->where(['user_id' => $this->model->id])->min('response_time'),
            'counted' => UserRequest::find()->select(['request_url', 'count' => 'count(*)'])->where(['user_id' => $this->model->id])->orderBy(['count' => SORT_DESC])->groupBy(['request_url'])->asArray()->limit(10)->all(),
            'slowest' => UserRequest::find()->select(['request_url', 'response_time'])->where(['user_id' => $this->model->id])->orderBy(['response_time' => SORT_DESC])->distinct()->asArray()->limit(10)->all(),
        ];
    }

    public function callbackDelete()
    {
        UserRequest::deleteAll(['user_id' => $this->model->id]);

        return $this->sendSuccess("Data has been removed.");
    }
}