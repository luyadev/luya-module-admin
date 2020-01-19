<?php

namespace luya\admin\ngrest\base;

/**
 * Active Window Interface.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
interface ActiveWindowInterface
{
    /**
     * Get the item id of the current ActiveWindow context item id.
     *
     * @return integer|array If its a composite key an array is returned, otherwise the integer number for the PK.
     */
    public function getItemId();
    
    /**
     * @return Whether the current item is a composite key or not.
     */
    public function getIsCompositeItem();
    
    /**
     * Returns an array with all items if its a composite key.
     *
     * @return array
     */
    public function getItemIds();
    
    /**
     * Set the value of the item Id in where the active window context is initialized.
     *
     * @param integer $id The item id context
     */
    public function setItemId($id);

    /**
     * Set the current configratuion hash name to the ActiveWindow.
     *
     * Setting the hash happens in the {{luya\admin\ngrest\render\RenderActiveWindow::render}} method.
     *
     * @param string $hash The hash name of the current active config.
     */
    public function setConfigHash($hash);
    
    /**
     * Set the hash of the current active window which is calculated by the ActiveWindow.
     *
     * Setting the hash happens in the {{luya\admin\ngrest\render\RenderActiveWindow::render}} method.
     * @param string $hash
     */
    public function setActiveWindowHash($hash);
    
    /**
     * The default action which is going to be requested when clicking the active window.
     *
     * @return string The response string, render and displayed trough the angular ajax request.
     */
    public function index();
    
    /**
     * Return the current label defined for this Active Window.
     *
     * @return string Returns the label of the active window, keep in mind this value has not model context.
     */
    public function getLabel();
    
    /**
     * Return the current icon defined for this Active Window.
     *
     * @return string Returns the material icon name as string.
     */
    public function getIcon();
    
    /**
     * By default the title just returns the {{getLabel()}} value if not overriden.
     *
     * In getTitle() its allowed to access the model context, which is not allowed in {{getLabel()}}.
     *
     * Display the username as title could look like this:
     *
     * ```php
     * public function getTitle()
     * {
     *     return $this->model->username;
     * }
     * ```
     *
     * @return string Returns the title string which can have model context.
     * @since 1.2.2
     */
    public function getTitle();
}
