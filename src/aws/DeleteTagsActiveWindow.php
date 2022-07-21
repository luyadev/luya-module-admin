<?php

namespace luya\admin\aws;

use luya\admin\models\Tag;
use luya\admin\models\TagRelation;
use luya\admin\Module;
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
    /**
     * @var string The name of the module where the ActiveWindow is located in order to find the view path.
     */
    public $module = '@admin';

    /**
     * @inheritdoc
     */
    public function defaultIcon()
    {
        return 'delete';
    }

    public function getTitle()
    {
        return $this->model->name;
    }

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
            'tagName' => $this->model->name,
        ]);
    }

    /**
     * Remove the given tag if the entered tag name is correct.
     *
     * @param string $name The current tag alias
     * @return array
     */
    public function callbackRemove($name)
    {
        if (strtolower($name) !== strtolower($this->model->name)) {
            return $this->sendError(Module::t('aws_delete_remove_wrong_name'));
        }

        $transaction = $this->model::getDb()->beginTransaction();

        try {
            TagRelation::deleteAll(['tag_id' => $this->model->id]);
            $this->model->delete();
            $transaction->commit();

            return $this->sendSuccess(Module::t('aws_delete_remove_success'));
        } catch (\Exception $e) {
            $transaction->rollBack();
            return $this->sendError($e->getMessage());
        }
    }
}
