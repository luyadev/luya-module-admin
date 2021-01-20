<?php

namespace luya\admin\ngrest\plugins;

use luya\admin\ngrest\base\Plugin;

/**
 * Select Dropdown from an API
 * 
 * Whenever the API changes a call will be made to the API which returns results which will be available in the select.
 *
 * Therefore this can also be used als "dependent select" feature known as "depdrop"
 * 
 * ```php
 * [
 *     'class' => SelectAsyncApi::class,
 *     'api' => 'admin/api-admin-user/search?query={{data.create.name}}',
 *     'labelField' => 'title',
 *     'valueField' => 'id'
 * ]
 * ```
 * @author Basil Suter <git@nadar.io>
 * @since 4.0.0
 */
class SelectAsyncApi extends Plugin
{
    /**
     * @var string Example api would be `admin/api-admin-user`. Its even possible to add parameters from other fields, becuase the api
     * will be called whenever the api changes (there is a watcher registered).
     *  
     * ````
     * 'api' => 'admin/api-admin-user?title={{data.create.name}}'
     * ```
     */
    public $api;

    /**
     * @var string The field which will be taken from the xhr request as label
     */
    public $labelField = 'title';

    /**
     * @var string The attribute which will be taken for the select value
     */
    public $valueField = 'id';

    /**
     * {@inheritDoc}
     */
    public function renderList($id, $ngModel)
    {
        return $this->createTag('async-value', null, ['api' => $this->api, 'model' => $ngModel, 'fields' => $this->labelField]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderCreate($id, $ngModel)
    {
        return $this->createFormTag('zaa-async-api-select', $id, $ngModel, [
            'api' => $this->api,
            'optionslabel' => $this->labelField,
            'optionsvalue' => $this->valueField,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderUpdate($id, $ngModel)
    {
        return $this->renderCreate($id, $ngModel);
    }
}