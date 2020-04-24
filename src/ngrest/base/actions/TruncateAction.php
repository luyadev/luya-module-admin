<?php

namespace luya\admin\ngrest\base\actions;

use yii\rest\Action;

class TruncateAction extends Action
{
    public function run()
    {
        $class = $this->modelClass;
        return [
            'truncate' => $class::getDb()->createCommand()->truncateTable($class::tableName())->execute(),
        ];
    }
}