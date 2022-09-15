<?php

namespace admintests\admin\helpers;

use admintests\AdminTestCase;
use luya\admin\helpers\I18n;

class I18nTest extends AdminTestCase
{
    private $json = '{"de":"Deutsch","en":"English"}';

    private $array = ['de' => 'Deutsch', 'en' => 'English'];

    public function testDecode()
    {
        $deocde = I18n::decode($this->json);

        $this->assertSame($this->array, $deocde);
    }

    public function testDecodeArray()
    {
        $decode = I18n::decodeArray([$this->json, $this->json]);
        $this->assertSame([$this->array, $this->array], $decode);
    }

    public function testEncode()
    {
        $encode = I18N::encode($this->array);

        $this->assertSame($this->json, $encode);
    }

    public function testFindActive()
    {
        $active = I18n::findActive($this->array);
        $this->assertSame('English', $active);
    }

    public function testFindActiveArray()
    {
        $active = I18n::findActiveArray([$this->array, $this->array]);

        $this->assertSame(['English', 'English'], $active);
    }

    public function testDecodeFindActive()
    {
        $this->assertSame('English', I18n::decodeFindActive($this->json));
    }

    public function testDecodeFindActiveEmptyValue()
    {
        $this->assertSame('empty', I18n::decodeFindActive($this->json, 'empty', 'es'));
    }

    public function testDecodeFindActiveArrayEmptyValue()
    {
        $this->assertSame(['empty', 'empty'], I18n::decodeFindActiveArray([$this->json, $this->json], 'empty', 'es'));
    }

    public function testDecodeFindActiveLang()
    {
        $this->assertSame('Deutsch', I18n::decodeFindActive($this->json, '', 'de'));
    }

    public function testDecodeFindActiveArrayLang()
    {
        $this->assertSame(['Deutsch', 'Deutsch'], I18n::decodeFindActiveArray([$this->json, $this->json], '', 'de'));
    }

    public function testDecodeFindActiveArray()
    {
        $this->assertSame(['English', 'English'], I18n::decodeFindActiveArray([$this->json, $this->json]));
    }
}
