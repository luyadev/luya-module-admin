<?php

namespace luya\admin\dashboard;

use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
* ECharts library dashboard object.
*
* The see all possible configuration options visit: https://echarts.apache.org/examples/en/index.html
*
* Example usage:
*
* ```php
* public $dashboardObjects = [
*     [
*        'class' => 'luya\admin\dashboard\ChartDashboardObject',
*        'dataApiUrl' => 'admin/api-addressbook-contact/sale',
*        'title' => 'Echart'
*     ]
* ];
* ```
*
* Doughnut echarts (https://echarts.apache.org/examples/en/editor.html?c=pie-doughnut) example response:
*
* ```php
* public function actionSale()
* {
*     return [
*    	'tooltip' => ['trigger' => 'item', 'formatter' => '{b}: {c} million ({d}%)'],
*    	'legend' => ['orient' => 'vertical', 'x' => 'left', 'data' => ['Asia', 'Africa', 'America', 'Europe', 'Oceania']],
*    	'series' => [
*    		[
*    			'name' => "Continent",
*    			'type' => 'pie',
*    			'radius' => ['50%', '70%'],
*    			'avoidLabelOverlap' => false,
*    			'labelLine' => ['normal' => ['show' => false]],
*    			'label' => [
*    				'normal' => ['show' => false, 'position' => 'center'],
*    				'emphasis' => [
*    					'show' => true,
*    					'textStyle' => [
*    						'fontSize' => '30',
*    						'fontWeight' => 'bold'
*    					]
*    				]
*    			],
*    			'data' => [
*    				['value' => 4437, 'name' => 'Asia'],
*    				['value' => 1203, 'name' => 'Africa'],
*    				['value' => 997, 'name' => 'America'],
*   				['value' => 740, 'name' => 'Europe'],
*    				['value' => 40, 'name' => 'Oceania'],
*    			]
*    		]
*    	]
*    ];
* }
* ```
*
* An API response example for a line diagram:
*
* ```php
* return [
*    'xAxis' => ['type' => 'category', 'boundaryGap' => false, 'data' => ['Jan', 'Feb', 'March']],
*    'yAxis' => ['type' => 'value'],
*    'series' => [
*        'data' => [100,200,125],
*    	 'type' => 'line',
*     ]
* ];
* ```
*
* Read more about echarts: https://github.com/apache/echarts
*
* @author Oom <baqianxin@163.com>
* @since 1.0.2
*/
class ChartDashboardObject extends BaseDashboardObject
{
    /**
     * @var array Options to generate the wrapper element. Generates a tag like:
     *
     * ```php
     * <div class="card-panel" ng-controller="DefaultDashboardObjectController" ng-init="loadData(\'{{dataApiUrl}}\');">
     *     <!-- content from: $outerTemplate -->
     * </div>
     * ```
     *
     * The tag element `<div>` can be changed by overriding the key `tag`.
     */
    public $wrapperOptions = [
        'class' => 'card',
        'tag' => 'div'
    ];

    /**
     * Get outer Template for echarts
     *
     * ```php
     * <div class="card-header"><h4>{{title}}<h4></div>
     * <echarts id="charts_'.$uniqid.'" legend="legend" data="data"></echarts>
     * ```
     *
     * The variables
     *
     * + {{title}}
     * + {{dataApiUrl}}
     *
     * Will be automatically parsed to its original input while rendering.
     *
     * @return string
     */
    public function getOuterTemplate()
    {
        return '<div class="card-header">{{title}}</div><div class="card-body"><echarts id="charts_'.$this->generateUniqueId().'" data="data"></echarts></div>';
    }

    /**
     * @inheritdoc
     */
    public function getOuterTemplateContent()
    {
        $options = ArrayHelper::merge([
            'ng-controller' => 'DefaultDashboardObjectController',
            'ng-init' => 'loadData(\'{{dataApiUrl}}\')'], $this->wrapperOptions);

        return Html::tag(ArrayHelper::remove($this->wrapperOptions, 'tag', 'div'), $this->getOuterTemplate(), $options);
    }

    /**
     * @return string An unique string.
     */
    protected function generateUniqueId()
    {
        return md5($this::class . $this->getTitle() . uniqid());
    }
}
