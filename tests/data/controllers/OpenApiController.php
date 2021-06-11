<?php

namespace luya\admin\tests\data\controllers;

use luya\admin\models\Lang;
use yii\rest\Controller;
use yii\rest\IndexAction;

/**
 * Controller Summary
 *
 * Controller Description
 * @method Lang[] actionIndex() Return the data models ..
 */
class OpenApiController extends Controller
{
    public function actions()
    {
        return [
            'index' => [
                'class' => IndexAction::class,
                'modelClass' => Lang::class,
            ],
        ];
    }

    /**
     * Post information
     *
     * Description of Save Action
     * @uses string $username
     * @uses int status
     */
    public function actionSaveUser()
    {
        //
    }
}
