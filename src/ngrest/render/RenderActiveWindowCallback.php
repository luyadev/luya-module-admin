<?php

namespace luya\admin\ngrest\render;

use luya\admin\Module;
use luya\admin\ngrest\base\ActiveWindow;
use luya\admin\ngrest\base\Render;
use luya\Exception;
use luya\helpers\ObjectHelper;
use Yii;
use yii\helpers\Inflector;

/**
 * Render an Active Window Callback call.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class RenderActiveWindowCallback extends Render
{
    public function render()
    {
        $activeWindowHash = Yii::$app->request->get('activeWindowHash');
        $activeWindowCallback = Yii::$app->request->get('activeWindowCallback');

        $activeWindows = $this->config->getPointer('aw');

        if (!isset($activeWindows[$activeWindowHash])) {
            throw new Exception("Unable to find ActiveWindow " . $activeWindowHash);
        }

        /** @var ActiveWindow $obj */
        $obj = Yii::createObject($activeWindows[$activeWindowHash]['objectConfig']);
        $obj->setItemId(Yii::$app->session->get($activeWindowHash));
        $obj->setConfigHash($this->config->getHash());
        $obj->setActiveWindowHash($activeWindowHash);

        $function = 'callback'.Inflector::id2camel($activeWindowCallback);

        try {
            return ObjectHelper::callMethodSanitizeArguments($obj, $function, Yii::$app->request->post());
        } catch (Exception $error) {
            return $obj->sendError(Module::t('aws_missing_callback_param_generic_errror'), [
                'message' => $error->getMessage(),
            ]);
        }
    }
}
