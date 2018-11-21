<?php

namespace luya\admin\base;

use Yii;
use luya\admin\components\Auth;
use luya\admin\models\UserOnline;
use luya\rest\UserBehaviorInterface;
use yii\web\ForbiddenHttpException;
use luya\rest\ActiveController;
use luya\admin\Module as AdminModule;

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
    
        $this->enableCors = AdminModule::getInstance()->cors;
        $this->jsonCruft = AdminModule::getInstance()->jsonCruft;
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
            case 'relation-call':
            case 'filter':
            case 'export':
            case 'list':
                $type = false;
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
                throw new ForbiddenHttpException("Invalid REST API action call.");
                break;
        }

        UserOnline::refreshUser($this->userAuthClass()->identity, $this->id);
        
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
        UserOnline::refreshUser($this->userAuthClass()->identity, $this->id);
        
        if (!Yii::$app->auth->matchApi($this->userAuthClass()->identity->id, $this->id, false)) {
            throw new ForbiddenHttpException('Unable to access this action due to insufficient permissions.');
        }
    }
}
