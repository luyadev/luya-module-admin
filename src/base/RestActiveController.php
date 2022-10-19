<?php

namespace luya\admin\base;

use luya\admin\components\Auth;
use luya\admin\models\UserOnline;
use luya\admin\traits\AdminRestBehaviorTrait;
use luya\rest\ActiveController;
use luya\rest\UserBehaviorInterface;
use Yii;
use yii\base\InvalidConfigException;
use yii\web\ForbiddenHttpException;

/**
 * Base class for Rest Active Controllers.
 *
 * > Read more about permissions: [[app-admin-module-permission.md]]
 * > Rest Active Controllers use `api` permissions.
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
     * @since 2.2.0
     */
    public function actionPermissions()
    {
        return [];
    }

    private array $_actionPermissions = [];

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
     * @since 2.2.0
     */
    public function addActionPermission($type, $actions)
    {
        foreach ((array) $actions as $actionName) {
            $this->_actionPermissions[$actionName] = $type;
        }
    }

    /**
     * Get all actions as array from {{actionPermissions()}} method and those wo where inject by {{åddActionPermission}}.
     *
     * @return array
     * @since 2.2.0
     */
    public function getActionPermissions()
    {
        foreach ($this->actionPermissions() as $type => $actionName) {
            $this->addActionPermission($actionName, $type);
        }

        return $this->_actionPermissions;
    }

    /**
     * {@inheritDoc}
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        // use the check access method to ensure whether a certain item is valid to a certain user or not.
        // the main purpose of validation whether the action can be access or not is done in {{isActionAllowed()}}
        // which runs always in {{beforeAction()}}.
    }

    /**
     * Checks if the given action id is allowed or not
     *
     * @param string $action The action id
     * @return boolean
     * @since 2.2.0
     */
    public function isActionAllowed($action)
    {
        if ($this->isActionAuthOptional($action)) {
            return true;
        }

        // a permission action exists, ensure if user has permission for this action or not:
        if (array_key_exists($action, $this->getActionPermissions())) {
            $type = $this->getActionPermissions()[$action];
            if (!in_array($type, [false, Auth::CAN_CREATE, Auth::CAN_DELETE, Auth::CAN_UPDATE, Auth::CAN_VIEW], true)) {
                throw new InvalidConfigException("Invalid type \"$type\" of action permission.");
            }

            $this->authId = Yii::$app->auth->matchApi($this->userAuthClass()->identity, $this->id, $type);

            if (!$this->authId) {
                throw new ForbiddenHttpException("User is unable to access the API \"{$this->id}\" due to insufficient permissions.");
            }
        } else {
            // there is no permission for the given api and action id, ensure api user access.
            $this->canApiUserAccess();
        }

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
