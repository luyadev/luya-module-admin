<?php

namespace luya\admin\aws;

use luya\admin\models\Tag;
use luya\admin\models\TagRelation;
use luya\admin\ngrest\base\ActiveWindow;

/**
 * Delete Tags Active Window.
 * 
 * An active Window which is only allowed in Tag context in order to delete a given
 * tag. It displays all the relation which will be deleted as well.
 *
 * @property Tag $model The model tag context.
 *  
 * @author Basil Suter <basil@nadar.io>
 * @since 2.3.0
 */
class DeleteTagsActiveWindow extends ActiveWindow
{
    public $module = '@admin';

    public function index()
    {
        $relations = TagRelation::find()
            ->where(['tag_id' => $this->model->id])
            ->select(['table_name', 'count(*) as count'])
            ->groupBy(['table_name'])
            ->asArray()
            ->all();

        return $this->render('index', [
            'relations' => $relations,
        ]);
    }
}