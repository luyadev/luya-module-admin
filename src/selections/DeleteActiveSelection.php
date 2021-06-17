<?php

namespace luya\admin\selections;

use luya\admin\ngrest\base\ActiveSelection;

class DeleteActiveSelection extends ActiveSelection
{
    public $label = 'Delete';

    public $icon = 'delete';
    /**
     * {@inheritDoc}
     */
    public function handle(array $items)
    {
        $count = 0;
        foreach ($items as $item) {
            $item->delete();
            $count++;
        }

        return $this->sendSuccess("{$count} items deleted.");
    }
}