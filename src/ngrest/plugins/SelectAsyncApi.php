<?php

namespace luya\admin\ngrest\plugins;

use luya\admin\ngrest\base\Plugin;

/**
 * Select Dropdown from an API
 * 
 * @since 4.0.0
 */
class SelectAsyncApi extends Plugin
{
    /**
     * @var string Example api would be `admin/api-admin-user`.
     */
    public $api;

    public $labelField = 'title';

    public $valueField = 'id';

    public function renderList($id, $ngModel)
    {
        return $this->createTag('async-value', null, ['api' => $this->api, 'model' => $ngModel, 'fields' => $this->labelField]);
    }

    public function renderCreate($id, $ngModel)
    {
        return $this->createFormTag('zaa-async-api-select', $id, $ngModel, [
            'api' => $this->api,
            'optionslabel' => $this->labelField,
            'optionsvalue' => $this->valueField,
        ]);
    }

    public function renderUpdate($id, $ngModel)
    {
        return $this->renderCreate($id, $ngModel);
    }
}