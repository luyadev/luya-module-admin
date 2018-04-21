<?php

namespace luya\admin\base;

use Yii;
use luya\admin\components\Auth;
use luya\admin\models\UserOnline;
use luya\rest\UserBehaviorInterface;
use yii\web\ForbiddenHttpException;
use luya\rest\ActiveController;

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
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    
        $this->enableCors = Yii::$app->auth->cors;
    }
    
    /**
     * Get the current user auth object.
     * 
     * @return \luya\admin\components\AdminUser
     */
    public function userAuthClass()
    {
        return Yii::$app->adminuser;
    }

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
            case 'search-provider':
            case 'search-hidden-fields':
            case 'full-response':
            case 'relation-call':
            case 'filter':
            case 'export':
                $type = false;
                break;
            case 'create':
                $type = Auth::CAN_CREATE;
                break;
            case 'active-window-render';
            case 'active-window-callback':
            case 'update':
                $type = Auth::CAN_UPDATE;
                break;
            case 'delete':
                $type = Auth::CAN_DELETE;
                break;
            default:
                throw new ForbiddenHttpException("Invalid REST API action call.");
                break;
        }

        UserOnline::refreshUser($this->userAuthClass()->identity->id, $this->id);
        
        if (!Yii::$app->auth->matchApi($this->userAuthClass()->identity->id, $this->id, $type)) {
            throw new ForbiddenHttpException('Unable to access this action due to insufficient permissions.');
        }
    }

    /**
     * Checks if the current api endpoint exists in the list of accessables APIs.
     * 
     * @throws ForbiddenHttpException
     * @since 1.1.0
     */
    public function checkEndpointAccess()
    {
        UserOnline::refreshUser($this->userAuthClass()->identity->id, $this->id);
        
        if (!Yii::$app->auth->matchApi($this->userAuthClass()->identity->id, $this->id, false)) {
            throw new ForbiddenHttpException('Unable to access this action due to insufficient permissions.');
        }
    }
}
