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
 * @blog  http://oom-cc.com
 * @since 1.0.0
 */
class ChartDashboardObject extends BaseDashboardObject {


    public $wrapperOptions = ['class' => 'card', 'tag' => 'div'];

    /**
     * @var string The Chart's option
     *
     */
    public $options = '';

    public function getOuterTemplate(){
        $uniqid = md5(uniqid(microtime(true), true));
        return \yii\helpers\Html::decode(Yii::$app->controller->renderFile('@admin/views/template/dashboard/chart.php',
            ['options' => $this->options,
             'uniqid' => $uniqid]));
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
