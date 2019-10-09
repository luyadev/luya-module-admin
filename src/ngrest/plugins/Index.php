<?php

namespace luya\admin\ngrest\plugins;

class Index extends Angular
{
    /**
     * config.groupBy
     * config.groupByExpanded
     * 
     * contains group data.
     */
    public $template = '{{1+((pager.currentPage-1) * pager.perPage)+$index}}';
}