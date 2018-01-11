<?php

namespace luya\admin\dashboard;

use Yii;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;

/**
 * Fast generated Dashboard Objects.
 *
 * The default object is the default class for all {{luya\admin\base\Module::$dashboardObjects}} items without a class defintion.
 *
 * @author Oom <baqianxin@163.com>
 * @since 1.0.2
 * Example usage:
 * ```php
 *     //The demo of echarts :http://echarts.baidu.com/demo.html#pie-doughnut
 *
 *     public $dashboardObjects = [
 *         [
 *           'class' => 'luya\admin\dashboard\ChartDashboardObject',
 *           'dataApiUrl' => 'admin/api-addressbook-contact/sale',
 *           'title' => 'Echart'
 *         ]
 *     ];
 *
 * //the admin/api-addressbook-contact/sale ;
 * //return array or json string;
 * public function actionPolar()
 * {
 *     $option = [];
 *
 *     $option['tooltip'] = [
 *         'trigger' => 'item',
 *         'formatter' => "{a} <br/>{b}: {c} ({d}%)"
 *     ];
 *     $option['legend'] = [
 *         'orient' => "vertical",
 *         'x' => 'left',
 *         'data' => ['直接访问', '邮件营销', '联盟广告', '视频广告', '搜索引擎']
 *     ];
 *
 *     $option['series'] = [
 *         [
 *             'name' => "Pie Chart",
 *             'type' => 'pie',
 *             'radius' => ['50%', '70%'],
 *             'avoidLabelOverlap' => false,
 *             'labelLine' => [
 *                 'normal' => [
 *                     'show' => false
 *                 ]
 *             ],
 *             'label' => [
 *                 'normal' => [
 *                     'show' => false,
 *                     'position' => 'center'
 *                 ],
 *                 'emphasis' => [
 *                     'show' => true,
 *                     'textStyle' => [
 *                         'fontSize' => '30',
 *                         'fontWeight' => 'bold'
 *                     ]
 *                 ]
 *             ],
 *             'data' => [
 *                 ['value' => 335, 'name' => '直接访问'],
 *                 ['value' => 310, 'name' => '邮件营销'],
 *                 ['value' => 234, 'name' => '联盟广告'],
 *                 ['value' => 135, 'name' => '视频广告'],
 *                 ['value' => 1234, 'name' => '搜索引擎'],
 *             ]
 *
 *         ]
 *     ];
 *
 *     return $option;
 *
 *}
 *
 *```
 */
class ChartDashboardObject extends BaseDashboardObject
{


  /**
   * @var array Options to generate the wrapper element. Generates a tag like:
   *
   * ```
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
     *
     * ```
     * <div class="card-header"><h4>{{title}}<h4></div>
     * <echarts id="charts_'.$uniqid.'" legend="legend" item="item" data="data"></echarts>
     * ```
     *
     * The variables
     *
     * + {{title}}
     * + {{dataApiUrl}}
     *
     * Will be automatically parsed to its original input while rendering.
     */
    public function getOuterTemplate()
    {
        $uniqid = md5(uniqid(microtime(true),true));
        return '<div class="card-header"><h4>{{title}}<h4></div><echarts id="charts_'.$uniqid.'" legend="legend" item="item" data="data"></echarts>';
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
}
