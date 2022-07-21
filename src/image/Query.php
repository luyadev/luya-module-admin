<?php

namespace luya\admin\image;

use luya\admin\storage\QueryTrait;
use Yii;
use yii\base\BaseObject;

/**
 * Storage Images Querying.
 *
 * See the {{\luya\admin\storage\QueryTrait}} for more informations.
 *
 * @property \luya\admin\storage\BaseFileSystemStorage $storage The storage component
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class Query extends BaseObject
{
    use QueryTrait;

    private $_storage;

    /**
     * Singleton behavior for storage component getter.
     *
     * @return \luya\admin\storage\BaseFileSystemStorage
     */
    public function getStorage()
    {
        if ($this->_storage === null) {
            $this->_storage = Yii::$app->storage;
        }

        return $this->_storage;
    }

    /**
     * Return all images from the storage system.
     *
     * @return array
     */
    public function getDataProvider()
    {
        return $this->storage->imagesArray;
    }

    /**
     * Get a specific images from the storage system.
     *
     * @param integer $id
     * @return boolean|array
     */
    public function getItemDataProvider($id)
    {
        return $this->storage->getImagesArrayItem($id);
    }

    /**
     * Create the image object based on the array.
     *
     * @param array $itemArray
     * @return \luya\admin\image\Item
     */
    public function createItem(array $itemArray)
    {
        return Item::create($itemArray);
    }

    /**
     * Create iterator object from a given data array.
     *
     * @param array $data
     * @return \luya\admin\image\Iterator
     */
    public function createIteratorObject(array $data)
    {
        return Yii::createObject(['class' => Iterator::class, 'data' => $data]);
    }
}
