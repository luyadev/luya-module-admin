<?php

namespace luya\admin\openapi\specs;

/**
 * Each Spec Requires.
 *
 * @author Basil Suter <git@nadar.io>
 * @since 3.2.0
 */
interface SpecInterface
{
    public function getSummary(): string;

    public function getDescription(): string;

    public function getParameters(): array;

    public function getResponses(): array;
}
