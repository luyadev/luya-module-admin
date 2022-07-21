<?php

namespace luya\admin\tags;

use luya\admin\Module;
use luya\tag\BaseTag;
use Yii;
use yii\helpers\Html;

/**
 * File Tag.
 *
 * Generates a link to a target file in a new window.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class FileTag extends BaseTag
{
    /**
     * @inheritdoc
     */
    public function example()
    {
        return 'file[123](File XYZ.pdf)';
    }

    /**
     * @inheritdoc
     */
    public function readme()
    {
        return Module::t('tag_file_readme');
    }

    /**
     * @inheritdoc
     */
    public function parse($value, $sub)
    {
        $file = Yii::$app->storage->getFile($value);

        if (!$file) {
            return false;
        }

        return Html::a(!empty($sub) ? $sub : $file->name, $file->href, ['target' => '_blank']);
    }
}
