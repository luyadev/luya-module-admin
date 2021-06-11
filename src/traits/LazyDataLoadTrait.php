<?php

namespace luya\admin\traits;

trait LazyDataLoadTrait
{
    /**
     * @param array|\Closure $data
     * @return array|\Traversable
     */
    protected function loadData($data)
    {
        if (is_callable($data)) {
            return call_user_func($data);
        } elseif (is_array($data) || $data instanceof \Traversable) {
            return $data;
        }
        
        return [];
    }
}