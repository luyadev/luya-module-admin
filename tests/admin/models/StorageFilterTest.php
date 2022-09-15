<?php

namespace admintests\models;

use admintests\AdminModelTestCase;
use Imagine\Image\BoxInterface;
use Imagine\Image\Fill\FillInterface;
use Imagine\Image\ImageInterface;
use Imagine\Image\Palette\Color\ColorInterface;
use Imagine\Image\Palette\PaletteInterface;
use Imagine\Image\PointInterface;
use Imagine\Image\ProfileInterface;
use luya\admin\models\StorageEffect;
use luya\admin\models\StorageFilter;
use luya\admin\models\StorageFilterChain;
use luya\testsuite\fixtures\NgRestModelFixture;
use luya\testsuite\traits\AdminDatabaseTableTrait;
use Yii;
use yii\base\InvalidConfigException;

class StorageFilterTest extends AdminModelTestCase
{
    use AdminDatabaseTableTrait;

    public function testApplyFilterChain()
    {
        $this->createAdminNgRestLogFixture();

        // storage filter
        $storage = new NgRestModelFixture([
            'modelClass' => StorageFilter::class,
        ]);

        $model = new $storage->newModel();
        $this->assertNotNull($model->ngRestActiveWindows());
        $this->assertNotNull($model->ngRestScopes());

        $model->identifier = 'foo';
        $model->name = 'Foo';
        $this->assertTrue($model->save());

        // effect
        $effect = new NgRestModelFixture([
            'modelClass' => StorageEffect::class,
            'fixtureData' => [
                2 => [
                    'id' => 2,
                    'name' => 'effect2',
                    'identifier' => 'effect2',
                    'imagine_name' => 'crop',
                ],
                3 => [
                    'id' => 3,
                    'name' => 'effect3',
                    'identifier' => 'effect3',
                    'imagine_name' => 'watermark',
                ],
                4 => [
                    'id' => 4,
                    'name' => 'effect4',
                    'identifier' => 'effect4',
                    'imagine_name' => 'text',
                ]
            ]
        ]);
        $effectModel = $effect->newModel;
        $this->assertNotNull($model->ngRestActiveWindows());
        $this->assertNotNull($model->ngRestScopes());

        $effectModel->id = 1;
        $effectModel->identifier = 'foobar';
        $effectModel->name = 'foobar';
        $effectModel->imagine_name = 'thumbnail';
        $effectModel->imagine_json_params = json_encode(['vars' => [
            ['var' => 'width', 'label' => 'Breit in Pixel'],
            ['var' => 'height', 'label' => 'Hoehe in Pixel'],
            ['var' => 'mode', 'label' => 'outbound or inset'], // THUMBNAIL_OUTBOUND & THUMBNAIL_INSET
            ['var' => 'saveOptions', 'label' => 'save options'],
        ]]);

        $this->assertTrue($effectModel->save());

        $this->assertSame('thumbnail', $effectModel->getImagineEffectName());

        // add chains
        $chain = new NgRestModelFixture([
            'modelClass' => StorageFilterChain::class,
        ]);

        $chainModel = $chain->newModel;
        $chainModel->setAttributes([
            'name' => 'Thumbnail',
            'imagine_name' => 'thumbnail',
            'effect_id' => 1,
            'filter_id' => 1,
            'sort_index' => 1,
            'effect_json_values' => ['width' => 100, 'height' => 100],
        ]);
        $this->assertTrue($chainModel->save());
        $this->assertNotNull($chainModel->effect);

        $chainModel = $chain->newModel;
        $chainModel->setAttributes([
            'name' => 'Crop',
            'imagine_name' => 'crop',
            'effect_id' => 2,
            'filter_id' => 1,
            'sort_index' => 1,
            'effect_json_values' => ['width' => 100, 'height' => 100],
        ]);
        $this->assertTrue($chainModel->save());
        $this->assertNotNull($chainModel->effect);

        $chainModel = $chain->newModel;
        $chainModel->setAttributes([
            'name' => 'Watermark',
            'imagine_name' => 'watermark',
            'effect_id' => 3,
            'filter_id' => 1,
            'sort_index' => 1,
            'effect_json_values' => ['image' => Yii::getAlias('@app/tests/data/image.jpg')],
        ]);
        $this->assertTrue($chainModel->save());
        $this->assertNotNull($chainModel->effect);

        /*
        $chainModel->setAttributes([
            '$name' => 'text',
            'imagine_name' => 'text',
            'effect_id' => 4,
            'filter_id' => 1,
            'sort_index' => 1,
            'effect_json_values' => ['text' => 'text', 'fontFile' => 'fontfile.ttf'],
        ]);

        $this->assertTrue($chainModel->save());
        */



        /// APPLY THE CHAIN!

        $model->applyFilterChain(Yii::getAlias('@app/tests/data/image.jpg'), Yii::getAlias('@app/tests/data/runtime/image_result_'.time().'.jpg'));
    }

    public function testUnknownEffectName()
    {
        new NgRestModelFixture([
            'modelClass' => StorageEffect::class,
            'fixtureData' => [
                1 => [
                    'id' => 1,
                    'name' => 'effect2',
                    'identifier' => 'effect2',
                    'imagine_name' => 'foooo',
                ],
            ],
        ]);
        // add chains
        $chain = new NgRestModelFixture([
            'modelClass' => StorageFilterChain::class,
        ]);

        $chainModel = $chain->newModel;
        $chainModel->setAttributes([
            'name' => 'Thumbnail',
            'effect_id' => 1,
            'filter_id' => 1,
            'sort_index' => 1,
            'effect_json_values' => ['width' => 100, 'height' => 100],
        ]);
        $this->assertTrue($chainModel->save());
        $chainModel->eventAfterFind();
        $this->expectException(InvalidConfigException::class);
        $chainModel->applyFilter(new ImageInterfaceMock(), []);
    }

    public function testInvalidParamConfig()
    {
        new NgRestModelFixture([
            'modelClass' => StorageEffect::class,
            'fixtureData' => [
                1 => [
                    'id' => 1,
                    'name' => 'effect2',
                    'identifier' => 'effect2',
                    'imagine_name' => 'thumbnail',
                ],
            ],
        ]);
        // add chains
        $chain = new NgRestModelFixture([
            'modelClass' => StorageFilterChain::class,
        ]);

        $chainModel = $chain->newModel;
        $chainModel->setAttributes([
            'name' => 'Thumbnail',
            'effect_id' => 1,
            'filter_id' => 1,
            'sort_index' => 1,
            'effect_json_values' => ['x' => 100, 'y' => 100],
        ]);
        $this->assertTrue($chainModel->save());
        $chainModel->eventAfterFind();
        $this->expectException(InvalidConfigException::class);
        $chainModel->applyFilter(new ImageInterfaceMock(), []);
    }

    public function testTextEffectWhichFaileDueToMissingExtensions()
    {
        $this->createAdminNgRestLogFixture();

        // storage filter
        $storage = new NgRestModelFixture([
            'modelClass' => StorageFilter::class,
        ]);

        $model = new $storage->newModel();
        $this->assertNotNull($model->ngRestActiveWindows());
        $this->assertNotNull($model->ngRestScopes());

        $model->identifier = 'foo';
        $model->name = 'Foo';
        $this->assertTrue($model->save());

        // effect
        new NgRestModelFixture([
            'modelClass' => StorageEffect::class,
            'fixtureData' => [
                1 => [
                    'id' => 1,
                    'name' => 'effect4',
                    'identifier' => 'effect4',
                    'imagine_name' => 'text',
                ]
            ]
        ]);

        // add chains
        $chain = new NgRestModelFixture([
            'modelClass' => StorageFilterChain::class,
        ]);
        $chainModel = $chain->newModel;
        $chainModel->setAttributes([
            '$name' => 'text',
            'imagine_name' => 'text',
            'effect_id' => 1,
            'filter_id' => 1,
            'sort_index' => 1,
            'effect_json_values' => ['text' => 'text', 'fontFile' => 'fontfile.ttf'],
        ]);

        $this->assertTrue($chainModel->save());



        /// APPLY THE CHAIN!

        $this->expectException(\Exception::class);
        $model->applyFilterChain(Yii::getAlias('@app/tests/data/image.jpg'), Yii::getAlias('@app/tests/data/runtime/image_result_'.time().'.jpg'));
    }
}

/**
 * We just created this mock in order to support php 7.4 unit tests without mock builder
 */
class ImageInterfaceMock implements ImageInterface
{
    /**
         * Returns the image content as a binary string.
         *
         * @param string $format
         * @param array $options
         *
         * @throws \Imagine\Exception\RuntimeException
         *
         * @return string binary
         */
    public function get($format, array $options = [])
    {
    }

    /**
     * Returns the image content as a PNG binary string.
     *
     * @throws \Imagine\Exception\RuntimeException
     *
     * @return string binary
     */
    public function __toString()
    {
    }

    /**
     * Instantiates and returns a DrawerInterface instance for image drawing.
     *
     * @return \Imagine\Draw\DrawerInterface
     */
    public function draw()
    {
    }

    /**
     * @return \Imagine\Effects\EffectsInterface
     */
    public function effects()
    {
    }

    /**
     * Returns current image size.
     *
     * @return \Imagine\Image\BoxInterface
     */
    public function getSize()
    {
    }

    /**
     * Transforms creates a grayscale mask from current image, returns a new
     * image, while keeping the existing image unmodified.
     *
     * @return static
     */
    public function mask()
    {
    }

    /**
     * Returns array of image colors as Imagine\Image\Palette\Color\ColorInterface instances.
     *
     * @return \Imagine\Image\Palette\Color\ColorInterface[]
     */
    public function histogram()
    {
    }

    /**
     * Returns color at specified positions of current image.
     *
     * @param \Imagine\Image\PointInterface $point
     *
     * @throws \Imagine\Exception\RuntimeException
     *
     * @return \Imagine\Image\Palette\Color\ColorInterface
     */
    public function getColorAt(PointInterface $point)
    {
    }

    /**
     * Returns the image layers when applicable.
     *
     * @throws \Imagine\Exception\RuntimeException In case the layer can not be returned
     * @throws \Imagine\Exception\OutOfBoundsException In case the index is not a valid value
     *
     * @return \Imagine\Image\LayersInterface
     */
    public function layers()
    {
    }

    /**
     * Enables or disables interlacing.
     *
     * @param string $scheme
     *
     * @throws \Imagine\Exception\InvalidArgumentException When an unsupported Interface type is supplied
     *
     * @return $this
     */
    public function interlace($scheme)
    {
    }

    /**
     * Return the current color palette.
     *
     * @return \Imagine\Image\Palette\PaletteInterface
     */
    public function palette()
    {
    }

    /**
     * Set a palette for the image. Useful to change colorspace.
     *
     * @param \Imagine\Image\Palette\PaletteInterface $palette
     *
     * @throws \Imagine\Exception\RuntimeException
     *
     * @return $this
     */
    public function usePalette(PaletteInterface $palette)
    {
    }

    /**
     * Applies a color profile on the Image.
     *
     * @param \Imagine\Image\ProfileInterface $profile
     *
     * @throws \Imagine\Exception\RuntimeException
     *
     * @return $this
     */
    public function profile(ProfileInterface $profile)
    {
    }

    /**
     * Returns the Image's meta data.
     *
     * @return \Imagine\Image\Metadata\MetadataBag
     */
    public function metadata()
    {
    }

    /**
     * Copies current source image into a new ImageInterface instance.
     *
     * @throws \Imagine\Exception\RuntimeException
     *
     * @return static
     */
    public function copy()
    {
    }

    /**
     * Crops a specified box out of the source image (modifies the source image)
     * Returns cropped self.
     *
     * @param \Imagine\Image\PointInterface $start
     * @param \Imagine\Image\BoxInterface $size
     *
     * @throws \Imagine\Exception\OutOfBoundsException
     * @throws \Imagine\Exception\RuntimeException
     *
     * @return $this
     */
    public function crop(PointInterface $start, BoxInterface $size)
    {
    }

    /**
     * Resizes current image and returns self.
     *
     * @param \Imagine\Image\BoxInterface $size
     * @param string $filter
     *
     * @throws \Imagine\Exception\RuntimeException
     *
     * @return $this
     */
    public function resize(BoxInterface $size, $filter = ImageInterface::FILTER_UNDEFINED)
    {
    }

    /**
     * Rotates an image at the given angle.
     * Optional $background can be used to specify the fill color of the empty
     * area of rotated image.
     *
     * @param int $angle
     * @param \Imagine\Image\Palette\Color\ColorInterface $background
     *
     * @throws \Imagine\Exception\RuntimeException
     *
     * @return $this
     */
    public function rotate($angle, ColorInterface $background = null)
    {
    }

    /**
     * Pastes an image into a parent image
     * Throws exceptions if image exceeds parent image borders or if paste
     * operation fails.
     *
     * Returns source image
     *
     * @param \Imagine\Image\ImageInterface $image
     * @param \Imagine\Image\PointInterface $start
     * @param int $alpha How to paste the image, from 0 (fully transparent) to 100 (fully opaque)
     *
     * @throws \Imagine\Exception\InvalidArgumentException
     * @throws \Imagine\Exception\RuntimeException
     *
     * @return $this
     */
    public function paste(ImageInterface $image, PointInterface $start, $alpha = 100)
    {
    }

    /**
     * Saves the image at a specified path, the target file extension is used
     * to determine file format, only jpg, jpeg, gif, png, wbmp, xbm, webp and bmp are
     * supported.
     * Please remark that bmp is supported by the GD driver only since PHP 7.2.
     *
     * @param string $path
     * @param array $options
     *
     * @throws \Imagine\Exception\RuntimeException
     *
     * @return $this
     */
    public function save($path = null, array $options = [])
    {
    }

    /**
     * Outputs the image content.
     *
     * @param string $format
     * @param array $options
     *
     * @throws \Imagine\Exception\RuntimeException
     *
     * @return $this
     */
    public function show($format, array $options = [])
    {
    }

    /**
     * Flips current image using vertical axis.
     *
     * @throws \Imagine\Exception\RuntimeException
     *
     * @return $this
     */
    public function flipHorizontally()
    {
    }

    /**
     * Flips current image using horizontal axis.
     *
     * @throws \Imagine\Exception\RuntimeException
     *
     * @return $this
     */
    public function flipVertically()
    {
    }

    /**
     * Remove all profiles and comments.
     *
     * @throws \Imagine\Exception\RuntimeException
     *
     * @return $this
     */
    public function strip()
    {
    }

    /**
     * Generates a thumbnail from a current image
     * Returns it as a new image without modifying the current image unless the THUMBNAIL_FLAG_NOCLONE flag is specified.
     *
     * @param \Imagine\Image\BoxInterface $size
     * @param int|string $settings One or more of the ManipulatorInterface::THUMBNAIL_ flags (joined with |). It may be a string for backward compatibility with old constant values that were strings.
     * @param string $filter The filter to use for resizing, one of ImageInterface::FILTER_*
     *
     * @throws \Imagine\Exception\RuntimeException
     *
     * @return static
     */
    public function thumbnail(BoxInterface $size, $settings = self::THUMBNAIL_INSET, $filter = ImageInterface::FILTER_UNDEFINED)
    {
    }

    /**
     * Applies a given mask to current image's alpha channel.
     *
     * @param \Imagine\Image\ImageInterface $mask
     *
     * @return $this
     */
    public function applyMask(ImageInterface $mask)
    {
    }

    /**
     * Fills image with provided filling, by replacing each pixel's color in
     * the current image with corresponding color from FillInterface, and
     * returns modified image.
     *
     * @param \Imagine\Image\Fill\FillInterface $fill
     *
     * @return $this
     */
    public function fill(FillInterface $fill)
    {
    }
}
