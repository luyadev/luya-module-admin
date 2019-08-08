<?php

namespace luya\admin\base;

interface JwtIdentityInterface
{
    public function loginByJwtToken($token);
}