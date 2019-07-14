<?php

namespace luya\admin\apis;

use Yii;
use luya\Exception;
use luya\traits\CacheableTrait;
use luya\admin\helpers\I18n;
use luya\admin\base\RestController;


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
