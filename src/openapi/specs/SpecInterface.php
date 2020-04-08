<?php

namespace luya\admin\openapi\specs;

interface SpecInterface
{
    public function getSummary(): string;

    public function getDescription(): string;

    public function getParameters(): array;

    public function getResponses(): array;
}