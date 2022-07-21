<?php

namespace luya\admin\traits;

/**
 * Trait to enable lazy load for NgRest plugins.
 *
 * For a example for CheckboxList:
 *
 * ```php
 * public function ngRestAttributeTypes()
 * {
 *     return [
 *         'genres' => ['checkboxList', 'data' => function () {
 *               return new Query()->all();
 *          }],
 *     ];
 * }
 * ```
 *
 * @author Bennet Klarhölter <boehsermoe@me.com>
 * @since 4.0.0
 */
trait LazyDataLoadTrait
{
    /**
     * If the given data is a closure it will call it and return the result of the function.
     * Function will only execute if the data are really need to load.
     *
     * If the given data is no closure it will return the directly data.
     *
     * ```
     * $this->lazyLoadData(function () {
     *      return Query::find()->all();
     * });
     * ```
     *
     * @param mixed|\Closure $data
     * @return mixed|\Traversable
     */
    protected function lazyLoadData($data)
    {
        if (is_callable($data)) {
            return call_user_func($data);
        }

        return $data;
    }
}
