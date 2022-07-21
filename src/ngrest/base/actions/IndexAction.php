<?php

namespace luya\admin\ngrest\base\actions;

use luya\traits\CacheableTrait;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * List
 *
 * Returns all entries for the given model paginated by a number of elements.
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

        $query = call_user_func($this->prepareActiveDataQuery);
        if (!empty($filter)) {
            $query->andWhere($filter);
        }

        $dataProvider = Yii::createObject([
            'class' => ActiveDataProvider::class,
            'query' => $query,
            'pagination' => $this->controller->pagination,
            'sort' => [
                'attributes' => $this->controller->generateSortAttributes($this->controller->model->getNgRestConfig()),
                'params' => $requestParams,
            ],
        ]);

        if ($this->isCachable() && $this->controller->cacheDependency) {
            Yii::$app->db->cache(function () use ($dataProvider) {
                $dataProvider->prepare();
            }, 0, Yii::createObject($this->controller->cacheDependency));
        }

        return $dataProvider;
    }
}
