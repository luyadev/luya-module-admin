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
     * @var integer Contains the id of the current running auth id
     * @since 2.0.0
     */
    protected $authId;

    /**
     * Provide a list of actions with the given permission
     *
     * ```php
     * return [
     *     'my-action' => Auth::CAN_UPDATE,
     * ];
     * ```
     * 
     * the action `actionMyAction()` would now require at least CAN UPDATE permission on this API to work.
     * 
     * @return array An array where key is the action id and value the Auth type
     */
    public function actionPermissions()
    {
        return [];
    }

    private $_actionPermissions = [];

    /**
     * Add a permission with a function.
     * 
     * This allows you to inject permission on init() which won't allow them to override.
     * 
     * ```php
     * public function init()
     * {
     *     parent::init();
     * 
     *     $this->addActionPermission(Auth::CAN_UPDATE, [
     *         'my-action', 'another-action',
     *     ]); 
     * }
     * ```
     *
     * @param integer $type The type of permission
     * @param string $actions The name of the action
     */
    protected function addActionPermission($type, $actions)
    {
        foreach ((array) $actions as $actionName) {
            $this->_actionPermissions[$actionName] = $type;
        }
    }

    /**
     * Get all actions as array from {{actionPermissions()}} method and those wo where inject by {{åddActionPermission}}.
     * 
     * @return array
     */
    protected function getActionPermissions()
    {
        foreach ($this->actionPermissions() as $type => $actionName) {
            $this->addActionPermission($type, $actionName);
        }

        return $this->_actionPermissions;
    }

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
     * @deprected can is deprecated and replaced with isActionAllowed
     */
    public function can($type)
    {
        trigger_error("can is deprecated, us isActionAllowed instead", E_USER_DEPRECATED);
    }

    public function isActionAllowed($action)
    {
        if ($this->isActionAuthOptional($action)) {
            return true;
        }

        // a permission action exists, ensure if user has permission for this action or not:
        if (array_key_exists($action, $this->getActionPermissions())) {
            $type = $this->getActionPermissions()[$action];

            if (!in_array($type, [false, Auth::CAN_CREATE, Auth::CAN_DELETE, Auth::CAN_UPDATE, Auth::CAN_VIEW])) {
                throw new InvalidConfigException("Invalid type \"$type\" of action permission.");
            }
            
            $this->authId = Yii::$app->auth->matchApi($this->userAuthClass()->identity, $this->id, $type);
            
            if (!$this->authId) {
                throw new ForbiddenHttpException("User is unable to access the API \"{$this->id}\" due to insufficient permissions.");
            }

            return true;
        }

        // there is no permission for the given api and action id, ensure api user access.
        $this->canApiUserAccess();

        UserOnline::refreshUser($this->userAuthClass()->identity, $action);

        return true;
    }

    /**
     * Ensure the action of rest controllers can not viewed by api users by default.
     *
     * @param \yii\base\Action $action
     * @return boolean
     * @since 2.2.0
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            return $this->isActionAllowed($action->id);
        }

        return false;
    }
}
