<?php

namespace luya\admin\base;

/**
 * Filter Interface
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
interface FilterInterface
{
    
    /**
     * Resize-Effect.
     */
    //const EFFECT_RESIZE = 'resize';
    
    
    
    const THUMBNAIL_MODE_INSET = 'inset';
    
    const THUMBNAIL_MODE_OUTBOUND = 'outbound';
    
    /**
     * Thumbnail-Effect.
     */
    const EFFECT_THUMBNAIL = 'thumbnail';
    
    /**
     * Crop-Effect.
     */
    const EFFECT_CROP = 'crop';
    
    /**
     * Unique identifier name for the effect, no special chars allowed.
     *
     * @return string The identifier must match [a-zA-Z0-9\-]
     */
    public static function identifier();
}
