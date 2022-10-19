<?php

namespace luya\admin\traits;

use luya\admin\ngrest\base\NgRestActiveQuery;
use luya\admin\ngrest\base\NgRestModel;
use Yii;
use yii\base\Event;
use yii\db\AfterSaveEvent;
use yii\web\Application;

/**
 * Sortable Trait provides orderBy clause and re-index when update, delete or create rows.
 *
 * By default the field `sortindex` is taken, change this by override the `sortableField` method.
 *
 * ```php
 * public static function sortableField()
 * {
 *     return 'sortfield';
 * }
 * ```
 *
 * The SortableTrait is commonly used with the {{luya\admin\ngrest\plugins\Sortable}} Plugin. By default
 * the {{luya\admin\ngrest\base\NgRestModel::ngRestListOrder}} is set to false which disables the ability
 * to sort the GRID by the end-user.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
trait SortableTrait
{
    /**
     * {@inheritDoc}
     * @since 4.4.0
     */
    public function init()
    {
        parent::init();
        $this->on(NgRestModel::EVENT_AFTER_INSERT, [$this, 'newItemIndex']);
        $this->on(NgRestModel::EVENT_AFTER_UPDATE, [$this, 'updateItemIndex']);
        $this->on(NgRestModel::EVENT_AFTER_DELETE, [$this, 'deleteItemIndex']);
    }

    /**
     * Update the index when deleting an item
     *
     * @param Event $event
     * @since 4.4.0
     */
    protected function deleteItemIndex(Event $event)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $pkName = current($event->sender->primaryKey());
            $this->reIndex($event, self::sortableField(), $pkName);
            $transaction->commit();
        } catch (\Exception|\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Update the index for a new item
     *
     * @param AfterSaveEvent $event
     * @since 4.4.0
     */
    protected function newItemIndex(AfterSaveEvent $event)
    {
        $this->updateItemIndex($event, true);
    }

    /**
     * Update the index for a given event.
     *
     * Either
     * - set the highest index available (if a row is created but no value has been given)
     * - swap index for high to low position
     * - swap index for low to hight position
     *
     * @param AfterSaveEvent $event
     * @param boolean $isNewRecord
     * @since 4.4.0
     */
    protected function updateItemIndex(AfterSaveEvent $event, $isNewRecord = false)
    {
        $attributeName = self::sortableField();
        $oldPosition = array_key_exists($attributeName, $event->changedAttributes) ? $event->changedAttributes[$attributeName] : false;
        $newPosition = $event->sender[$attributeName];

        // nothing has changed, skip further updates
        if ($oldPosition == $newPosition && !$isNewRecord) {
            return;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $findQuery = $event->sender->find();

            if ($findQuery instanceof NgRestActiveQuery && Yii::$app instanceof Application) {
                $findQuery->inPool(Yii::$app->request->get('pool'));
            }

            $pkName = current($event->sender->primaryKey());
            // no index has been set, set max value (last position)
            if ($isNewRecord && empty($newPosition)) {
                $event->sender->updateAttributes([$attributeName => $findQuery->max($attributeName) + 1]);
            } elseif ($oldPosition && $newPosition && $oldPosition != $newPosition) {
                $i = 1;
                if ($newPosition > $oldPosition) {
                    // when the new position is highter then the old one: (old position - 1) + *1
                    foreach ($findQuery->andWhere(['and', ['!=', $pkName, $event->sender->primaryKey], ['>', $attributeName, $oldPosition], ['<=', $attributeName, $newPosition]])->all() as $item) {
                        $item->updateAttributes([$attributeName => ($oldPosition - 1) + $i]);
                        $i++;
                    }
                } else {
                    // when the new position is higher then the old one: (new position + *1)
                    foreach ($findQuery->andWhere(['and', ['!=', $pkName, $event->sender->primaryKey], ['>=', $attributeName, $newPosition], ['<', $attributeName, $oldPosition]])->all() as $item) {
                        $item->updateAttributes([$attributeName => $newPosition + $i]);
                        $i++;
                    }
                }
            } elseif (!empty($newPosition) && empty($oldPosition)) {
                // its a new record where the user entered a position, lets move all the other higher indexes
                $i = 1;
                foreach ($findQuery->andWhere(['and', ['!=', $pkName, $event->sender->primaryKey], ['>=', $attributeName, $newPosition]])->all() as $item) {
                    $item->updateAttributes([$attributeName => $newPosition + $i]);
                    $i++;
                }
            }

            $this->reIndex($event, $attributeName, $pkName);
            $transaction->commit();
        } catch (\Exception|\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * ReIndex the all items to ensure consistent numbers
     *
     * @param Event $event
     * @param string $attributeName
     * @param string $pkName
     * @since 4.4.0
     */
    private function reIndex(Event $event, $attributeName, $pkName)
    {
        $findQuery = $event->sender->find();

        if ($findQuery instanceof NgRestActiveQuery && Yii::$app instanceof Application) {
            $findQuery->inPool(Yii::$app->request->get('pool'));
        }
        $q = $findQuery->asArray()->all();
        $i = 1;
        foreach ($q as $item) {
            $event->sender->updateAll([$attributeName => $i], [$pkName => $item[$pkName]]);
            $i++;
        }
    }

    /**
     * The field which should by used to sort.
     *
     * @return string
     */
    public static function sortableField()
    {
        return 'sortindex';
    }

    /**
     * Overrides the ngRestFind() method of the ActiveRecord
     * @return \yii\db\ActiveQuery
     */
    public static function ngRestFind()
    {
        return parent::ngRestFind()->orderBy([self::tableName().'.'.self::sortableField() => SORT_ASC]);
    }

    /**
     * Overrides the find() method of the ActiveRecord
     * @return \yii\db\ActiveQuery
     */
    public static function find()
    {
        return parent::find()->orderBy([self::tableName().'.'.self::sortableField() => SORT_ASC]);
    }

    /**
     * Disable the list ordering.
     *
     * @return boolean
     */
    public function ngRestListOrder()
    {
        return false;
    }
}
