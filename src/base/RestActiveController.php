<?php

namespace luya\admin\base;

use Yii;
use luya\admin\components\Auth;
use luya\admin\models\UserOnline;
use luya\rest\UserBehaviorInterface;
use yii\web\ForbiddenHttpException;
use luya\rest\ActiveController;
use yii\base\InvalidConfigException;
use luya\admin\traits\AdminRestBehaviorTrait;

/**
 * Base class for Rest Active Controllers.
 *
 * Wrapper for yii2 basic rest controller used with a model class. The wrapper is made to
 * change behaviours and overwrite the indexAction.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class RestActiveController extends ActiveController implements UserBehaviorInterface
{
    use AdminRestBehaviorTrait;

    /**
     * @inheritdoc
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        switch ($action) {
            case 'index':
            case 'view':
            case 'services':
            case 'search':
            case 'relation-call':
            case 'filter':
            case 'export':
            case 'list':
            case 'toggle-notification':
                $type = Auth::CAN_VIEW;
                break;
            case 'create':
                $type = Auth::CAN_CREATE;
                break;
            case 'active-window-render':
            case 'active-window-callback':
            case 'active-button':
            case 'update':
                $type = Auth::CAN_UPDATE;
                break;
            case 'delete':
                $type = Auth::CAN_DELETE;
                break;
            default:
                throw new ForbiddenHttpException("Invalid REST API call for action '{$action}'.");
                break;
        }
        
        return $this->can($type);
    }

    /**
     * @var integer Contains the id of the current running auth id
     * @since 2.0.0
     */
    protected $authId;

    /**
     * Check if the current user have given permissions type.
     *
     * ```php
     * $this->can(Auth::CAN_UPDATE);
     * ```
     *
     * If the user has no permission to update a forbidden http exception is thrown.
     *
     * @param integer $type
     * @return boolean Returns true otherwise throws an exception
     * @throws ForbiddenHttpException
     * @since 2.0.0
     */
    public function can($type)
    {
        if (!in_array($type, [Auth::CAN_CREATE, Auth::CAN_DELETE, Auth::CAN_UPDATE, Auth::CAN_VIEW])) {
            throw new InvalidConfigException("Invalid type of permission call.");
        }

        $this->authId = Yii::$app->auth->matchApi($this->userAuthClass()->identity, $this->id, $type);

        if (!$this->authId) {
            throw new ForbiddenHttpException("User is unable to access the API due to insufficient permissions.");
        }

        UserOnline::refreshUser($this->userAuthClass()->identity, $this->id);

        return true;
    }

    /**
     * Checks if the current api endpoint exists in the list of accessables APIs.
     *
     * @throws ForbiddenHttpException
     * @since 1.1.0
     */
    public function checkEndpointAccess()
    {
        if (!Yii::$app->auth->matchApi($this->userAuthClass()->identity, $this->id, false)) {
            throw new ForbiddenHttpException('Unable to access this action due to insufficient permissions.');
        }

        UserOnline::refreshUser($this->userAuthClass()->identity, $this->id);
    }
}
