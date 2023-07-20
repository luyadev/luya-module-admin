<?php

namespace luya\admin\ngrest\render;

use luya\admin\ngrest\base\NgRestModelInterface;

/**
 * Interface for CRUD renderers.
 *
 * All CRUD renderers must implement this interface in order to interact with the API and Controllers.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
interface RenderCrudInterface
{
    /**
     * Get an array with all relations
     *
     * @return array
     */
    public function getRelationCall();

    /**
     * Set an array with relations.
     *
     * @param array $options
     */
    public function setRelationCall(array $options);

    /**
     * Set the current ngrest model for the crud.
     *
     * @param \luya\admin\ngrest\base\NgRestModelInterface $model
     * @since 1.2.2
     */
    public function setModel(NgRestModelInterface $model);

    /**
     * @return \luya\admin\ngrest\base\NgRestModelInterface Returns the current ngrest model object.
     * @since 1.2.2
     */
    public function getModel();

    /**
     * Get whether the inline mode is enabled or not.
     *
     * @return boolean Determine whether this ngrest config is runing as inline window mode (a modal dialog with the
     * crud inside) or not. When inline mode is enabled some features like ESC-Keys and URL chaning must be disabled.
     */
    public function getIsInline();

    /**
     * Setter method for inline mode.
     *
     * @param boolean $inline
     */
    public function setIsInline($inline);

    /**
     * Model preselection value when using ngrest relation calls.
     *
     * @param string $selection
     */
    public function setModelSelection($selection);

    /**
     * Model selection value from setter method.
     *
     * @return string|boolean
     */
    public function getModelSelection();

    /**
     * Optional definitions for settings button. Those definitions are made in the ngrest crud controller.
     *
     * @param array $buttons
     */
    public function setSettingButtonDefinitions(array $buttons);

    /**
     * Get an array with additionals button definitions.
     *
     * @return array
     */
    public function getSettingButtonDefinitions();

    /**
     * Whether create permission is granted.
     *
     * @return boolean Whether current user can create items.
     */
    public function canCreate();

    /**
     * Whether update permission is granted.
     *
     * @return boolean Whether current user can update items.
     */
    public function canUpdate();

    /**
     * Whether delete permission is granted.
     *
     * @return boolean Whether current user can update items.
     */
    public function canDelete();

    /**
     * Returns the pool config array for the current active pool if any.
     *
     * @return array
     * @since 2.0.0
     */
    public function getActivePoolConfig();
}
