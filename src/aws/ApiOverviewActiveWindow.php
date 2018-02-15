<?php

namespace luya\admin\aws;

use Yii;
use luya\admin\ngrest\base\ActiveWindow;

/**
 * Api Overview Active Window.
 *
 * File has been created with `aw/create` command. 
 */
class ApiOverviewActiveWindow extends ActiveWindow
{
    /**
     * @var string The name of the module where the ActiveWindow is located in order to finde the view path.
     */
    public $module = '@admin';

    /**
     * Default label if not set in the ngrest model.
     *
     * @return string The name of of the ActiveWindow. This is displayed in the CRUD list.
     */
    public function defaultLabel()
    {
        return 'API Overview';
    }

    /**
     * Default icon if not set in the ngrest model.
     *
     * @var string The icon name from goolges material icon set (https://material.io/icons/)
     */
    public function defaultIcon()
    {
        return 'call_merge';    
    }

    /**
     * The default action which is going to be requested when clicking the ActiveWindow.
     * 
     * @return string The response string, render and displayed trough the angular ajax request.
     */
    public function index()
    {
        return $this->render('index', [
            'model' => $this->model,
        ]);
    }
    
    public function callbackReplaceToken()
    {
        $randomToken = Yii::$app->security->hashData(Yii::$app->security->generateRandomString(), $this->model->password_salt);
        
        $this->model->updateAttributes([
            'auth_token' => $randomToken,
        ]);
    }
}