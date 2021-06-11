<?php

namespace luya\admin\apis;

use luya\admin\components\Auth;
use luya\admin\models\Group;
use luya\admin\ngrest\base\Api;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * API to manage, create, udpate and delete all System Groups.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class GroupController extends Api
{
    /**
     * @var string The path to the group model.
     */
    public $modelClass = 'luya\admin\models\Group';

    /**
     * {@inheritDoc}
     */
    public function actionPermissions()
    {
        return [
            'me' => Auth::CAN_VIEW,
        ];
    }

    /**
     * Loggedin User Groups
     *
     * Returns all groups where the current loggedin (me) belongs to.
     *
     * @return Group[]
     * @since 4.0
     */
    public function actionMe()
    {
        return new ActiveDataProvider([
            'query' => Yii::$app->adminuser->identity->getGroups(),
        ]);
    }
}
