<?php

namespace luya\admin\openapi;

use cebe\openapi\spec\PathItem;

interface RouteParserInterface
{
    public function getPathItem(): PathItem;

    public function getPath(): string;
}