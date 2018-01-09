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
 * @example:
 * ```
 *     public $dashboardObjects = [
 *         [
 *           'class' => 'luya\admin\dashboard\ChartDashboardObject',
 *           'dataApiUrl' => 'admin/api-addressbook-contact/sale',
 *           'title' => 'Echart'
 *         ]
 *     ];
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
     * @var string The wrapper template which is by default:
     * @var string $uniqid The echarts's id : id="charts_$uniqid"
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
