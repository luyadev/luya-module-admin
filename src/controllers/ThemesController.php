<?php

namespace luya\admin\controllers;

use luya\admin\base\Controller;

/**
 * Theme for LUYA CMS.
 *
 * This module / component allow user to manage actual display themes.
 *
 * @author Mateusz Szymański Teamwant <zzixxus@gmail.com>
 * @author Mateusz Szymański Teamwant <kontakt@teamwant.pl>
 * @since 1.0.0
 */
class ThemesController extends Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }
}
