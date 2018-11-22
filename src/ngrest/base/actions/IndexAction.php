<?php

namespace luya\admin\ngrest\base\actions;

use Yii;
use yii\data\ActiveDataProvider;
use luya\traits\CacheableTrait;

/**
 * IndexAction for REST implementation.
 *
 * In order to enable or disable the pagination for index actions regulatet by the ActiveController
 * the main yii\rest\IndexAction is overriten by adding the pagination propertie to the action
 * provided from the luya\rest\ActiveController.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class IndexAction extends \yii\rest\IndexAction
{
    use CacheableTrait;

    /**
     * @var callable A callable which is executed.
     * @since 1.2.1
     */
    public $prepareActiveDataQuery;
    
    /**
     * Prepare the data models based on the ngrest find query.
     *
     * {@inheritDoc}
     *
     * @see \yii\rest\IndexAction::prepareDataProvider()
     */
    protected function prepareDataProvider()
    {
        $requestParams = Yii::$app->getRequest()->getBodyParams();
        if (empty($requestParams)) {
            $requestParams = Yii::$app->getRequest()->getQueryParams();
        }
        
        $filter = null;
        if ($this->dataFilter !== null) {
            $this->dataFilter = Yii::createObject($this->dataFilter);
            if ($this->dataFilter->load($requestParams)) {
                $filter = $this->dataFilter->build();
                if ($filter === false) {
                    return $this->dataFilter;
                }
            }
        }
        
        /* @var $modelClass \yii\db\BaseActiveRecord */
        $modelClass = $this->modelClass;
        
        $query = call_user_func($this->prepareActiveDataQuery);
        if (!empty($filter)) {
            $query->andWhere($filter);
        }
        
        $dataProvider = Yii::createObject([
            'class' => ActiveDataProvider::class,
            'query' => $query,
            'pagination' => $this->controller->pagination,
            'sort' => [
                'params' => $requestParams,
            ],
        ]);

        if ($this->isCachable() && $this->controller->cacheDependency) {
            Yii::$app->db->cache(function() use ($dataProvider) {
                $dataProvider->prepare();
            }, 0, Yii::createObject($this->controller->cacheDependency));
        }

        return $dataProvider;
    }
}
