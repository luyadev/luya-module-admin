<?php

namespace luya\admin\ngrest\plugins;

class Index extends Angular
{
    public $template = '{{1+((pager.currentPage-1) * pager.perPage)+$index}}';
}