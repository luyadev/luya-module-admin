<?php

namespace luya\admin\apis;

/**
 * Ngrest Log Controller.
 *
 * @author Basil Suter <git@nadar.io>
 * @since 3.2.0
 */
class NgrestLogController extends \luya\admin\ngrest\base\Api
{
    /**
     * {@inheritDoc}
     */
    public $modelClass = 'luya\admin\models\NgrestLog';

    /**
     * {@inheritDoc}
     */
    public $truncateAction = true;

    /**
     * {@inheritDoc}
     */
    public function prepareListQuery()
    {
        return parent::prepareListQuery()->with(['user']);
    }
}
