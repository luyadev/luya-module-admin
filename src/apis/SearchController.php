<?php

namespace luya\admin\apis;

use luya\admin\base\GenericSearchInterface;
use luya\admin\base\RestController;
use luya\admin\models\SearchData;
use luya\Exception;
use Yii;
use yii\db\ActiveQueryInterface;
use yii\db\QueryInterface;

/**
 * Search API, allows you to perform search querys for the entire administration including all items provided in the auth section.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class SearchController extends RestController
{
    /**
     * Transform the different generic search response into an array.
     *
     * @param array|\yii\db\QueryInterface|\yii\db\ActiveQueryInterface $response
     * @return array
     * @since 1.2.2
     */
    private function transformGenericSearchToData($response)
    {
        if ($response instanceof ActiveQueryInterface) {
            return $response->asArray(true)->all();
        } elseif ($response instanceof QueryInterface) {
            return $response->all();
        }

        return $response;
    }
    /**
     * Administration Global search provider.
     *
     * This method returns all node items with an search model class or a generic search interface instance and returns its data.
     *
     * @param string $query The query to search for.
     * @return array
     * @throws Exception
     */
    public function actionIndex($query)
    {
        $search = [];
        $module = Yii::$app->getModule('admin');

        foreach (Yii::$app->adminmenu->getModules() as $node) {
            if (isset($node['searchModelClass']) && !empty($node['searchModelClass'])) {
                $model = Yii::createObject($node['searchModelClass']);
                if (!$model instanceof GenericSearchInterface) {
                    throw new Exception('The model must be an instance of GenericSearchInterface');
                }
                $data = $this->transformGenericSearchToData($model->genericSearch($query));
                if (count($data) > 0) {
                    $stateProvider = $model->genericSearchStateProvider();
                    $search[] = [
                        'hideFields' => $model->genericSearchHiddenFields(),
                        'type' => 'custom',
                        'menuItem' => $node,
                        'data' => $data,
                        'stateProvider' => $stateProvider,
                    ];
                }

                unset($model, $data);
            }
        }

        foreach (Yii::$app->adminmenu->getItems() as $api) {
            if ($api['permissionIsApi']) {
                $ctrl = $module->createController($api['permissionApiEndpoint']);
                if (!$ctrl) {
                    continue;
                }
                $controller = $ctrl[0];

                if ($controller && $controller->model && $controller->model instanceof GenericSearchInterface) {
                    $data = $this->transformGenericSearchToData($controller->model->genericSearch($query));
                    if (count($data) > 0) {
                        $search[] = [
                            'hideFields' => $controller->model->genericSearchHiddenFields(),
                            'type' => 'api',
                            'menuItem' => $api,
                            'data' => $data,
                            'stateProvider' => $controller->model->genericSearchStateProvider(),
                        ];
                    }

                    unset($data);
                }

                unset($controller, $ctrl);
            }
        }

        $searchData = new SearchData();
        $searchData->query = $query;
        $searchData->num_rows = count($search);
        $searchData->save();

        return $search;
    }
}
