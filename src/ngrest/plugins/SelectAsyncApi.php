<?php

namespace luya\admin\ngrest\plugins;

use luya\admin\helpers\Angular;
use luya\admin\ngrest\base\Plugin;
use luya\helpers\Json;

/**
 * Select Dropdown from an API
 *
 * Whenever the API changes (trough angularjs two way binding of variables) a call will be made to the API which returns the select values.
 *
 * Therefore this can also be used als "dependent select" feature known as "depdrop"
 *
 * ```php
 * [
 *     'class' => SelectAsyncApi::class,
 *     'api' => 'admin/api-admin-user/search?query={name}',
 *     'labelField' => 'title',
 *     'valueField' => 'id'
 * ]
 * ```
 *
 * The above example illustrates that whenever the value of the field `name` changes, the given value will be popuplated to the api request.
 *
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
     * 'api' => 'admin/api-admin-user?title={name}'
     * ```
     */
    public $api;

    /**
     * @var string The api which should be take to determine the list entry, if not defined {{$api}} will be taken. The main purpose of this
     * is that the list api usually do not need to query data, it should just display the selected value in the list view. Therefore this could
     * look like `admin/api-admin-user` without params as the list async request will add the `/{id}?fields={id}` to the api url.
     */
    public $listApi;

    /**
     * @var string The field which will be taken from the xhr request as label
     */
    public $labelField = 'title';

    /**
     * @var string The attribute which will be taken for the select value
     */
    public $valueField = 'id';

    /**
     * @var boolean If enabled all variables with curly brackets will be convereted into the relative context of the attribute. For example
     * `{firstname}` would be converted to `data.create.firstname` or `data.update.firstname` based on the correct context. If disabled you
     * can use double curly brackets to access the angular scope `{{data.update.firstname}}` but won't have the option to auto change its context.
     */
    public $variablizeApi = true;

    /**
     * @var boolean See {{$variablizeApi}}.
     */
    public $variablizeListApi = true;

    /**
     * Whether the api should be variablized
     *
     * @param string $ngModel
     * @return string
     */
    public function getResolvedApi($ngModel)
    {
        return $this->variablizeApi ? Angular::variablizeContext($ngModel, $this->api, true) : $this->api;
    }

    /**
     * Whether the list api should be variablized
     *
     * @param string $ngModel
     * @return string
     */
    public function getResolvedListApi($ngModel)
    {
        return $this->variablizeListApi ? Angular::variablizeContext($ngModel, $this->listApi, true) : $this->listApi;
    }

    /**
     * {@inheritDoc}
     */
    public function renderList($id, $ngModel)
    {
        $api = $this->listApi ? $this->getResolvedListApi($ngModel) : $this->getResolvedApi($ngModel);

        return $this->createTag('async-value', null, ['api' => $api, 'model' => $ngModel, 'fields' => Json::encode((array) $this->labelField)]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderCreate($id, $ngModel)
    {
        return $this->createFormTag('zaa-async-api-select', $id, $ngModel, [
            'api' => $this->getResolvedApi($ngModel),
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
