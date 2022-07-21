<?php

namespace luya\admin\traits;

use luya\admin\behaviors\UserRequestBehavior;
use luya\admin\Module as AdminModule;
use Yii;
use yii\web\ForbiddenHttpException;

/**
 * A trait for LUYA admin rest behaviors.
 *
 * Implemented by
 *
 * + {{luya\admin\base\RestActiveController}}
 * + {{luya\admin\base\RestController}}
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 2.1.0
 */
trait AdminRestBehaviorTrait
{
    /**
     * @var AdminModule
     */
    private $_module;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->_module = AdminModule::getInstance();

        $this->enableCors = $this->_module->cors;
        $this->jsonCruft = $this->_module->jsonCruft;

        // pass all the luya admin languages to the content negoiator, the default language must be the first
        // element in the array.
        $this->languages = [Yii::$app->composition->langShortCode];
        foreach (Yii::$app->adminLanguage->languages as $lang) {
            array_push($this->languages, $lang['short_code']);
        }

        // disable session for rest usage
        Yii::$app->adminuser->enableSession = false;
    }

    /**
     * {@inheritDoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors[] = [
            'class' => UserRequestBehavior::class,
        ];

        return $behaviors;
    }

    /**
     * {@inheritDoc}
     */
    public function getCompositeAuthMethods()
    {
        $methods = parent::getCompositeAuthMethods();

        // if the jwt component is registered, authentication will be enabled.
        if (Yii::$app->get('jwt', false)) {
            array_unshift($methods, [
                'class' => 'bizley\jwt\JwtHttpBearerAuth',
                'auth' => [Yii::$app->jwt, 'authenticateUser'],
                'throwException' => false,
            ]);
        }

        return $methods;
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
     * Wether the given action id does not required authentication or not.
     *
     * > {@since 3.6.0} this will also return true when cors is enabled and the request method is OPTIONS. As the `optional` actions list
     * > is passed to the authenticator behavior, this is the place where authentication happens and is done anyhow before `isActionAuthOptional()
     * > is used in `beforeAction()` checks.
     *
     * @param string $actionId
     * @return boolean
     * @since 2.2.0
     */
    public function isActionAuthOptional($actionId)
    {
        if ($this->enableCors && Yii::$app->request->isOptions) {
            return true;
        }

        return in_array($actionId, $this->authOptional);
    }

    /**
     * If the current user is an API user this action might be not permitted. As Api Users
     * also acts as proxy for JWT authenticated users sensitive informations could be exposed.
     *
     * For example a JWT authenticated user proxied trough Api User could access `admin/api-admin-user/session`
     * as the {{luya\admin\apis\UserController::actionSession()}} is only secured through authentification and **not**
     * trough a given permission (let's say "view my session data").
     *
     * As Api Users are not allowed to login the don't need access to those *generic admin ui API endpoints*, this method
     * checks if the current user is an api user and therefore restricsts the access to such calls, unless the property
     * {{luya\admin\Module::$apiUserAllowActionsWithoutPermissions}} is enabled.
     *
     * @since 2.2.0
     */
    public function canApiUserAccess()
    {
        if ($this->userAuthClass()->identity->is_api_user && !$this->_module->apiUserAllowActionsWithoutPermissions) {
            throw new ForbiddenHttpException("This controller ({$this->id}) action is forbidden for API users unless apiUserAllowActionsWithoutPermissions is enabled in admin module config.");
        }
    }
}
