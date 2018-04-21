<?php

namespace luya\admin\ngrest\render;

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
    public function getRelationCall();
    
    public function setRelationCall(array $options);
    
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
     * Optional defintions for settings button. Those definitions are made in the ngrest crud controller.
     * 
     * @param array $buttons
     */
    public function setSettingButtonDefinitions(array $buttons);
    
    /**
     * Get an array with additionals button defintions.
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
}