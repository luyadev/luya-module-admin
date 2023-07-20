<?php

namespace luya\admin\storage;

use luya\Exception;
use Yii;
use yii\base\Arrayable;
use yii\base\ArrayableTrait;
use yii\base\BaseObject;

/**
 * Base class for file, image and folder Items.
 *
 * @property $itemArray array An array with all elements assigned for this element.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
abstract class ItemAbstract extends BaseObject implements Arrayable
{
    use ArrayableTrait;

    private array $_itemArray = [];

    /**
     * Setter method for itemArray property.
     * @param array $item
     */
    public function setItemArray(array $item)
    {
        $this->_itemArray = $item;
    }

    /**
     * Returns the whole item array.
     *
     * @return array An array with all keys for the given item.
     */
    public function getItemArray()
    {
        return $this->_itemArray;
    }

    /**
     * Returns a value for a given key inside the itemArray.
     *
     * @param string $key The requested key.
     * @param boolean $exception Whether getKey should throw an exception if the key is not found or just return false instead.
     * @throws Exception If the key is not found inside the array an exception is thrown. If $exception is disabled false is returned instead.
     */
    public function getKey($key, $exception = true)
    {
        if (!array_key_exists($key, $this->_itemArray)) {
            if ($exception) {
                throw new Exception("Unable to find the requested item key '$key' in item " . var_export($this->_itemArray, true));
            } else {
                return false;
            }
        }

        return $this->_itemArray[$key];
    }

    /**
     * Method to construct/build the item from Iterator or Query class.
     *
     * @param array $itemArray The array data
     * @return object Returns the specific item object (file, folder, image).
     */
    public static function create(array $itemArray)
    {
        return Yii::createObject(['class' => self::className(), 'itemArray' => $itemArray]);
    }
}
