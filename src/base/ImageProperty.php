<?php

namespace luya\admin\base;

use Yii;

/**
 * Base Image Property.
 *
 * This property overrides the default implementation of a property in order to simplify the integration of image property. The
 * response of the method `getValue()` is the **sourceAbsolute** to the image. If no image is provided or it can not be loaded the
 * response is false.
 *
 * Usage Example
 *
 * ```php
 * class MyImage extends \luya\admin\base\ImageProperty
 * {
 *     public function varName()
 *     {
 *         return 'myImage';
 *     }
 *
 *     public function label()
 *     {
 *         return 'My Image';
 *     }
 * }
 * ```
 *
 * In order to get use the above MyImage property just run: `<img src="<?= $item->getProperty('myImage')->getValue(); ?>" />`.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
abstract class ImageProperty extends Property
{
    /**
     * Type Image
     *
     * @see \luya\admin\base\Property::type()
     */
    public function type()
    {
        return self::TYPE_IMAGEUPLOAD;
    }

    /**
     * In order to auto apply a filter to each image override this mehotd returning the identifier of your Filter.
     *
     * ```php
     * public function filterName()
     * {
     *     return \app\filters\MyFilter::identifier();
     * }
     * ```
     *
     * @return boolean|string
     */
    public function filterName()
    {
        return false;
    }

    /**
     * Get the absolute image source of the image, if not available the method returns false.
     *
     * @return string|boolean Returns the path to the file, otherwise false.
     * @see \luya\admin\base\Property::getValue()
     */
    public function getValue()
    {
        $value = parent::getValue();

        if ($value) {
            $image = Yii::$app->storage->getImage($value);
            /* @var $image \luya\admin\image\Item */
            if ($image) {
                if ($this->filterName()) {
                    return $image->applyFilter($this->filterName())->sourceAbsolute;
                }
                return $image->source;
            }
        }

        return false;
    }

    /**
     * Get the image property from the property value.
     *
     * @return \luya\admin\image\Item|boolean
     * @since 1.0.2
     */
    public function getImage()
    {
        $value = parent::getValue();

        if ($value) {
            return Yii::$app->storage->getImage($value);
        }

        return false;
    }
}
