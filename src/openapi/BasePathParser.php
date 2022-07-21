<?php

namespace luya\admin\openapi;

use cebe\openapi\spec\PathItem;
use luya\helpers\Inflector;

/**
 * Base Class to convert Data into Paths.
 *
 * Terminology:
 *
 * + Absolute Route: admin/user/index or admin/api-cms-admin/create
 * + Controller Map Route: admin/api-cms-admin or admin/api-cms-nav
 *
 * @author Basil Suter <git@nadar.io>
 * @since 3.2.0
 */
abstract class BasePathParser
{
    /**
     * Return a PathItem.
     *
     * A PathItem represents a path within the openapi definition. A path (or maybe also named as endpoint/route) can have multiple verbs.
     * Like post, get, put
     *
     * @return PathItem
     */
    abstract public function getPathItem(): PathItem;

    /**
     * Returns the path which should be associated with this endpoint.
     *
     * This is the actual route which then recieves the request.
     *
     * @return string
     */
    abstract public function getPath(): string;

    /**
     * Whether this Parser is valid or not.
     *
     * @return boolean
     */
    abstract public function isValid(): bool;

    /**
     * Returns all absolute controller map routes which are covered by this parser.
     *
     * For example the update and post route are difference and covered, it would be:
     *
     * + admin/api-admin-user/create
     * + admin/api-admin-user/update
     *
     * @return array
     */
    abstract public function routes(): array;

    /**
     * Generate a normalized tag from a given route.
     *
     * @param string $route
     * @return string
     */
    public function normalizeTag($route)
    {
        $route = str_replace(["admin/"], '', $route);

        return ltrim(trim($route, '/'));
    }

    /**
     * Generate a readable operation id for current route and verb.
     *
     * @param string $verb
     * @return string
     */
    public function generateOperationId($verb)
    {
        $path = ltrim(str_replace(["admin/api-", "admin/api", "admin/"], '', $this->getPath()), '/');

        $operation = $verb . '-'. str_replace("/", " ", $path); // replace slashes with newlines
        $camelCase = Inflector::slug($operation, '-', true, false);
        return Generator::generateUniqueOperationId(Inflector::id2camel($camelCase));
    }
}
