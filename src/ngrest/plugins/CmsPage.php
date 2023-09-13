<?php

namespace luya\admin\ngrest\plugins;

use luya\admin\ngrest\base\Plugin;
use Yii;

/**
 * Create ability to select a CMS page.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class CmsPage extends Plugin
{
    /**
     * @inheritdoc
     */
    public function renderList($id, $ngModel)
    {
        return $this->createTag('show-internal-redirection', null, ['nav-id' => $ngModel, 'ng-show' => $ngModel]);
    }

    /**
     * @inheritdoc
     */
    public function renderCreate($id, $ngModel)
    {
        return $this->createFormTag('zaa-cms-page', $id, $ngModel, ['clearable' => 1]);
    }

    /**
     * @inheritdoc
     */
    public function renderUpdate($id, $ngModel)
    {
        return $this->renderCreate($id, $ngModel);
    }

    /**
     * @inheritdoc
     */
    public function onAfterFind($event)
    {
        // get value
        $fieldValue = $event->sender->getAttribute($this->name);

        // get menu item
        $menuItem = !empty($fieldValue) ? Yii::$app->menu->find()->where(['nav_id' => $fieldValue])->with(['hidden'])->one() : false;

        // assign value
        $this->writeAttribute($event, $menuItem);

        return true;
    }
}
