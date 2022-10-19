<?php

namespace luya\admin\ngrest\render;

use luya\admin\traits\TaggableTrait;
use luya\helpers\ObjectHelper;
use luya\web\View;
use Yii;
use yii\helpers\Json;
use yii\web\JsExpression;

/**
 * The View renderer for RenderCrud class.
 *
 * @property \luya\admin\ngrest\render\RenderCrud $context
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class RenderCrudView extends View
{
    public function registerAngularControllerScript()
    {
        $config = $this->getAngularControllerConfig();

        $client = 'zaa.bootstrap.register(\''.$this->context->config->getHash().'\', [\'$scope\', \'$controller\', function($scope, $controller) {
			$.extend(this, $controller(\'CrudController\', { $scope : $scope }));
			$scope.config = '.Json::htmlEncode($config).'
	    }]);';

        $this->registerJs($client, self::POS_BEGIN);
    }

    /**
     * Returns the config array for Angular controller
     *
     * @return array
     * @since 2.0.0
     */
    protected function getAngularControllerConfig()
    {
        return [
            'apiListQueryString' => $this->context->apiQueryString('list'),
            'apiUpdateQueryString' => $this->context->apiQueryString('update'),
            'apiServicesQueryString' => $this->context->apiQueryString('services'),
            'apiExportQueryString' => $this->context->apiQueryString('list'), // list for export will also expand i18n values which is requried when exporting ngrest filters
            'apiEndpoint' => $this->context->getApiEndpoint(),
            'list' => $this->context->getFields('list'),
            'create' => $this->context->getFields('create'),
            'update' => $this->context->getFields('update'),
            'ngrestConfigHash' => $this->context->getConfig()->getHash(),
            'activeWindowCallbackUrl' => $this->context->getApiEndpoint('active-window-callback'),
            'activeWindowRenderUrl' =>  $this->context->getApiEndpoint('active-window-render'),
            'pk' => $this->context->getConfig()->getPrimaryKey(),
            'inline' => $this->context->getIsInline(),
            'modelSelection' => $this->context->getModelSelection(),
            'orderBy' => $this->context->getOrderBy(),
            'tableName' => $this->context->getConfig()->getTableName(),
            'groupBy' => $this->context->getConfig()->getGroupByField() ? 1 : 0,
            'groupByField' => $this->context->getConfig()->getGroupByField() ?: '0',
            'groupByExpanded' => $this->context->getConfig()->getGroupByExpanded(),
            'filter' => '0',
            'filters' => $this->context->getConfig()->getFilters(),
            'fullSearchContainer' => false,
            'minLengthWarning' => false,
            'saveCallback' => $this->context->getConfig()->getOption('saveCallback') ? new JsExpression($this->context->getConfig()->getOption('saveCallback')) : false,
            'relationCall' => $this->context->getRelationCall(),
            'relations' => $this->context->getConfig()->getRelations(),
            'pools' => $this->context->getActivePoolConfig(),
            'activePool' => Yii::$app->request->get('pool'),
            'tagFilter' => ObjectHelper::isTraitInstanceOf($this->context->getModel(), TaggableTrait::class),
        ];
    }
}
