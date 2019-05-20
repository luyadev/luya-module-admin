<?php

namespace admintests\data\fixtures;

use luya\admin\helpers\I18n;

class I18nUserFixture extends UserFixture
{
    public $modelClass = 'admintests\data\models\I18nUser';
    public function getData()
    {
        $data = parent::getData();
        $data['user1']['firstname'] = I18n::encode([
            'lang1' => 'John',
            'lang2' => 'Jojo',
        ]);
        $data['user1']['lastname'] = I18n::encode([
            'lang1' => 'Doe',
            'lang2' => 'Dodo',
        ]);
        return $data;
    }
}
