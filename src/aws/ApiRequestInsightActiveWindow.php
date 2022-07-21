<?php

namespace luya\admin\aws;

use luya\admin\models\UserRequest;
use luya\admin\Module;
use luya\admin\ngrest\base\ActiveWindow;
use yii\data\ActiveDataProvider;

class ApiRequestInsightActiveWindow extends ActiveWindow
{
    /**
     * @var string The name of the module where the ActiveWindow is located in order to find the view path.
     */
    public $module = '@admin';

    /**
     * Default label if not set in the ngrest model.
     *
     * @return string The name of of the ActiveWindow. This is displayed in the CRUD list.
     */
    public function defaultLabel()
    {
        return Module::t('aw_requestinsight_default_label');
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

    public function getTitle()
    {
        return $this->model->firstname . ' ' . $this->model->lastname;
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

    public function callbackData($page = 1, $query = null)
    {
        $find = UserRequest::find()->where(['user_id' => $this->model->id]);

        if ($query) {
            $find->andFilterWhere(['like', 'request_url', $query]);
        }
        return new ActiveDataProvider([
            'query' => $find,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
            'pagination' => [
                'page' => ($page - 1), // The default value is 0, meaning the first page. Angular Pagination takes 1 as first page
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
            if ($status) {
                return $this->sendSuccess(Module::t('aw_requestinsight_toggle_logger_enabled'));
            }
            return $this->sendSuccess(Module::t('aw_requestinsight_toggle_logger_disabled'));
        }

        return $this->sendError(Module::t('aw_requestinsight_toggle_error'));
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

        return $this->sendSuccess(Module::t('aw_requestinsight_cleared'));
    }
}
