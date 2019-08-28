<?php

namespace luya\admin\apis;

use luya\admin\ngrest\base\Api;
use luya\admin\models\Tag;
use yii\data\ActiveDataProvider;

/**
 * Tags API, provides ability to add, manage or collect all system tags.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class TagController extends Api
{
    /**
     * @var string The path to the tag model.
     */
    public $modelClass = 'luya\admin\models\Tag';
    
    /**
     * @inheritdoc
     */
    public function withRelations()
    {
        return ['tagRelations'];
    }

    /**
     * Return all tags for a given relation table.
     *
     * @param string $tableName The table which is used to store the relation.
     * @return ActiveDataProvider
     * @since 1.2.2.1
     */
    public function actionTable($tableName)
    {
        return new ActiveDataProvider([
            'query' => Tag::find()->joinWith(['tagRelations'])->where(['table_name' => $tableName])->distinct(),
        ]);
    }
}
