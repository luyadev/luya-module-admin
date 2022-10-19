<?php

namespace luya\admin\ngrest\base;

use yii\db\QueryInterface;

/**
 * NgRest Relation Interface.
 *
 * Each relation definition must be an instance of this class.
 *
 * @author Basil Suter <basil@nadar.io>
 */
interface NgRestRelationInterface
{
    // setter

    /**
     * Set the target model class.
     *
     * @param string $targetModel
     * @since 2.0.0
     */
    public function setTargetModel($targetModel);

    /**
     * Set the model class of the current ngRestModel.
     *
     * @param string $modelClass
     */
    public function setModelClass($modelClass);

    /**
     * Set the index of the relation in the relations array.
     *
     * @param integer $arrayIndex
     */
    public function setArrayIndex($arrayIndex);

    // getters

    /**
     * Get the target model class path.
     *
     * @return string
     * @since 2.0.0
     */
    public function getTargetModel();

    /**
     * Get the encoded model class name.
     */
    public function getModelClass();

    /**
     * Get the array index of the relation in the relations array.
     */
    public function getArrayIndex();

    /**
     * Get the label of the relation.
     */
    public function getLabel();

    /**
     * Returns the tab label attribute name.
     *
     * In order to change the tab label, any of the available and exposed attributes can be taken. The attribute must be defined in
     * {{luya\admin\ngrest\base\NgRestModel::ngRestScopes()}} list scope. The tab can only display labels which are returned by the API.
     */
    public function getTabLabelAttribute();

    /**
     * Get relation link informations.
     */
    public function getRelationLink();

    /**
     * Get the api endpoint for the relation in order to make the relation data call.
     */
    public function getApiEndpoint();

    /**
     * @return QueryInterface
     */
    public function getDataProvider();
}
