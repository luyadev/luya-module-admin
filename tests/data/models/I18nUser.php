<?php

namespace admintests\data\models;

use luya\admin\models\User;

class I18nUser extends User
{
    public $i18n = ['firstname', 'lastname'];
}
