<?php

namespace luya\admin\aws;

use luya\admin\ngrest\base\ActiveWindow;
use Yii;

/**
 * Storage Effect Active Window.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
final class StorageFilterImagesActiveWindow extends ActiveWindow
{
    /**
     * @var string The name of the module where the ActiveWindow is located in order to find the view path.
     */
    public $module = '@admin';

    /**
     * The default action which is going to be requested when clicking the ActiveWindow.
     *
     * @return string The response string, render and displayed trough the angular ajax request.
     */
    public function index()
    {
        return $this->render('index', [
            'model' => $this->model,
            'images' => Yii::$app->storage->findImages(['filter_id' => $this->model->id]),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function defaultLabel()
    {
        return 'Image Filters';
    }

    public function getTitle()
    {
        return $this->model->name;
    }

    /**
     * @inheritdoc
     */
    public function defaultIcon()
    {
        return 'filter_vintage';
    }

    /**
     *
     * @return array
     */
    public function callbackRemove()
    {
        $log = $this->model->removeImageSources();

        Yii::$app->storage->flushArrays();

        return $this->sendSuccess("Removed ".(is_countable($log) ? count($log) : 0)." images for filter {$this->model->name}");
    }
}
