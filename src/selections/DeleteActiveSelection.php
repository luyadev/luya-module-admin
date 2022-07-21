<?php

namespace luya\admin\selections;

use luya\admin\ngrest\base\ActiveSelection;

/**
 * Delete Active Selection
 *
 * This run the `delete()` method on selected items.
 *
 * @author Basil Suter <git@nadar.io>
 * @since 4.0.0
 */
class DeleteActiveSelection extends ActiveSelection
{
    /**
     * {@inheritDoc}
     */
    public $label = 'Delete';

    /**
     * {@inheritDoc}
     */
    public $icon = 'delete';

    /**
     * @var string The success message to display.
     */
    public $message = '%s items has been deleted';

    /**
     * {@inheritDoc}
     */
    public function handle(array $items)
    {
        $count = 0;
        foreach ($items as $item) {
            if ($item->delete()) {
                $count++;
            }
        }

        $this->sendReloadEvent();

        return $this->sendSuccess(sprintf($this->message, $count));
    }
}
