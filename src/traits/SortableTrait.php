<?php

namespace luya\admin\traits;

use luya\admin\ngrest\base\NgRestModel;
use Yii;
use yii\db\AfterSaveEvent;

/**
 * Sortable Trait provides orderBy clause.
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
    public function init()
    {
        parent::init();
        $this->on(NgRestModel::EVENT_AFTER_INSERT, [$this, 'swapIndex']);
        $this->on(NgRestModel::EVENT_AFTER_UPDATE, [$this, 'swapIndex']);
    }

    public function swapIndex(AfterSaveEvent $event)
    {
        $attributeName = self::sortableField();
        $oldPosition = array_key_exists($attributeName, $event->changedAttributes) ? $event->changedAttributes[$attributeName] : false;
        $newPosition = $event->sender[$attributeName];

        // nothing has changed, skip further updates
        if ($oldPosition == $newPosition) {
            return;
        }
        
        $pkName = current($event->sender->primaryKey());

        if (!$oldPosition && empty($newPosition)) {
            Yii::debug('set max value for new record', __METHOD__);
            // no index has been set, set max value (last position)
            $event->sender->updateAttributes([$attributeName => $event->sender::find()->max($attributeName) + 1]);
        } else if ($oldPosition && $newPosition && $oldPosition != $newPosition) {
            if ($newPosition > $oldPosition) {
                // wenn neue position grösser als alte position: = (alte position – 1)+ *1
                // find alle einträge 
                $q = $event->sender->find()->andWhere([
                    'and',
                    ['!=', $pkName, $event->sender->primaryKey],
                    ['>', $attributeName, $oldPosition],
                    ['<=', $attributeName, $newPosition]
                ])->all();

                $i = 1;
                foreach ($q as $item) {
                    $item->updateAttributes([$attributeName => ($oldPosition - 1) + $i]);
                    $i++;
                }
            } else {
                // wenn neue position kleiner als alte position = (neue position + *1)
                $q = $event->sender->find()->andWhere([
                    'and',
                    ['!=', $pkName, $event->sender->primaryKey],
                    ['>=', $attributeName, $newPosition],
                    ['<', $attributeName, $oldPosition]
                ])->all();

                $i = 1;
                foreach ($q as $item) {
                    $item->updateAttributes([$attributeName => $newPosition + $i]);
                    $i++;
                }
            }
        } else if (!empty($newPosition) && empty($oldPosition)) {
            Yii::debug('new record with user input, move all other indexes', __METHOD__);
            // its a new record where the user entered a position, lets move all the other higher indexes
            $q = $event->sender->find()->andWhere([
                'and',
                ['!=', $pkName, $event->sender->primaryKey],
                ['>=', $attributeName, $newPosition],
            ])->all();

            $i = 1;
            foreach ($q as $item) {
                $item->updateAttributes([$attributeName => $newPosition + $i]);
                $i++;
            }
        }

        $q = $event->sender->find()->asArray()->all();

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
        return parent::ngRestFind()->orderBy([self::sortableField() => SORT_ASC]);
    }
    
    /**
     * Overrides the find() method of the ActiveRecord
     * @return \yii\db\ActiveQuery
     */
    public static function find()
    {
        return parent::find()->orderBy([self::sortableField() => SORT_ASC]);
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
