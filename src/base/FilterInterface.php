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
     * @var string Thumbnail mode inset
     */
    public const THUMBNAIL_MODE_INSET = 'inset';

    /**
     * @var string Thumbnail mode outbound
     */
    public const THUMBNAIL_MODE_OUTBOUND = 'outbound';

    /**
     * @var string Thumbnail effect
     */
    public const EFFECT_THUMBNAIL = 'thumbnail';

    /**
     * @var string Crop Effect
     */
    public const EFFECT_CROP = 'crop';

    /**
     * @var string Watermark Effect
     * @since 2.0.0
     */
    public const EFFECT_WATERMARK = 'watermark';

    /**
     * @var string Text Effect
     * @since 2.0.0
     */
    public const EFFECT_TEXT = 'text';

    /**
     * Unique identifier name for the effect, no special chars allowed.
     *
     * @return string The identifier must match [a-zA-Z0-9\-]
     */
    public static function identifier();
}
