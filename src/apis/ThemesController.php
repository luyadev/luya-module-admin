<?php

namespace luya\admin\apis;

use Yii;
use luya\Exception;
use luya\admin\helpers\Storage;
use luya\admin\models\StorageFile;
use luya\admin\models\StorageFolder;
use luya\admin\Module;
use luya\traits\CacheableTrait;
use luya\admin\helpers\I18n;
use luya\admin\base\RestController;
use yii\caching\DbDependency;
use luya\admin\filters\TinyCrop;
use luya\admin\filters\MediumThumbnail;
use luya\helpers\FileHelper;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use luya\admin\models\StorageImage;
use luya\admin\file\Item;
use luya\helpers\ArrayHelper;
use yii\data\ActiveDataProvider;
use luya\admin\models\TagRelation;


/**
 * Theme for LUYA CMS.
 *
 * This module / component allow user to manage actual display themes.
 *
 * @author Mateusz Szymański Teamwant <zzixxus@gmail.com>
 * @author Mateusz Szymański Teamwant <kontakt@teamwant.pl>
 * @since 1.0.0
 */
class ThemesController extends RestController
{
    use CacheableTrait;

    /**
     * @var string The route which is used in the permission system
     */
    const PERMISSION_ROUTE = 'admin/themes/index';

    /**
     * The index action returns all available themes.
     *
     * @return array
     */
    public function actionIndex()
    {
        return Yii::$app->themes->getThemes();
    }
}
