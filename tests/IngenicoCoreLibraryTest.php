<?php

use PHPUnit\Framework\TestCase;
use IngenicoClient\IngenicoCoreLibrary;

class IngenicoCoreLibraryTest  extends TestCase
{
    public function testTranslations()
    {
        $extension = new TestConnector();
        $coreLibrary = new IngenicoCoreLibrary($extension);

        $result = $coreLibrary->__('exceptions.access_denied', [], 'messages', 'en_US');
        $this->assertEquals('Access denied.', $result);
    }
}
