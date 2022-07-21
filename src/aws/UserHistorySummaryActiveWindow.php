<?php

namespace luya\admin\aws;

use luya\admin\Module;
use luya\admin\ngrest\base\ActiveWindow;

/**
 * User History Summary Active Window.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.1.0
 */
class UserHistorySummaryActiveWindow extends ActiveWindow
{
    /**
     * @var string The name of the module where the ActiveWindow is located in order to find the view path.
     */
    public $module = '@admin';

    /**
     * Default icon if not set in the ngrest model.
     *
     * @var string The icon name from goolges material icon set (https://material.io/icons/)
     */
    public function defaultIcon()
    {
        return 'pie_chart';
    }

    public function defaultLabel()
    {
        return Module::t('aw_userhistorysummary_defaultlabel');
    }

    /**
     * The default action which is going to be requested when clicking the ActiveWindow.
     *
     * @return string The response string, render and displayed trough the angular ajax request.
     */
    public function index()
    {
        $groups = [];
        foreach ($this->model->groups as $group) {
            $groups[] = $group->name;
        }
        return $this->render('index', [
            'model' => $this->model,
            'userLogins' => $this->model->getUserLogins()->limit(25)->all(),
            'ngrestLogs' => $this->model->getNgrestLogs()->orderBy(['timestamp_create' => SORT_DESC])->limit(25)->all(),
            'groups' => $groups,
        ]);
    }

    public function callbackPie()
    {
        return [
        'tooltip' => ['trigger' => 'item', 'formatter' => '{c} {b}'],
        'legend' => ['orient' => 'vertical', 'x' => 'left', 'data' => [Module::t('aw_userhistorysummary_contribcount_inserts'), Module::t('aw_userhistorysummary_contribcount_updates')]],
        'series' => [
            [
                'name' => "Hits",
                'type' => 'pie',
                'radius' => ['50%', '70%'],
                'avoidLabelOverlap' => false,
                'labelLine' => ['normal' => ['show' => false]],
                'label' => [
                    'normal' => ['show' => false, 'position' => 'center'],
                    'emphasis' => [
                        'show' => true,
                        'textStyle' => [
                            'fontSize' => '30',
                            'fontWeight' => 'bold'
                        ]
                    ]
                ],
                'data' => [
                    ['value' => $this->model->getNgrestLogs()->where(['is_insert' => true])->count(), 'name' => Module::t('aw_userhistorysummary_contribcount_inserts')],
                    ['value' => $this->model->getNgrestLogs()->where(['is_update' => true])->count(), 'name' => Module::t('aw_userhistorysummary_contribcount_updates')],
                ]
            ]
        ]
    ];
    }
}
