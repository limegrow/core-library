<?php

use PHPUnit\Framework\TestCase;
use IngenicoClient\IngenicoCoreLibrary;
use IngenicoClient\Logger;
use IngenicoClient\Logger\AdapterInterface;
use IngenicoClient\Logger\MonologAdapter;
use IngenicoClient\Logger\FileAdapter;

class IngenicoCoreLibraryTest  extends TestCase
{
    public function testTranslations()
    {
        $extension = new TestConnector();
        $coreLibrary = new IngenicoCoreLibrary($extension);

        $result = $coreLibrary->__('exceptions.access_denied', [], 'messages', 'en_US');
        $this->assertEquals('Access denied.', $result);
    }

    public function testLogAdapter()
    {
        $logFile = sys_get_temp_dir() . '/ingenico_core.log';

        $adapter = new FileAdapter(['file' => $logFile]);
        $this->assertInstanceOf(AdapterInterface::class, $adapter);

        $logger = new Logger($adapter);
        $this->assertInstanceOf(Logger::class, $logger);

        $logger->log($logger::INFO, 'Test');
        $this->assertEquals(true, file_exists($logFile));

        $logger->emergency('Test');
        $this->assertEquals(true, stripos(file_get_contents($logFile), $logger::EMERGENCY) !== false);

        $logger->alert('Test');
        $this->assertEquals(true, stripos(file_get_contents($logFile), $logger::ALERT) !== false);

        $logger->critical('Test');
        $this->assertEquals(true, stripos(file_get_contents($logFile), $logger::CRITICAL) !== false);

        $logger->error('Test');
        $this->assertEquals(true, stripos(file_get_contents($logFile), $logger::ERROR) !== false);

        $logger->warning('Test');
        $this->assertEquals(true, stripos(file_get_contents($logFile), $logger::WARNING) !== false);

        $logger->notice('Test');
        $this->assertEquals(true, stripos(file_get_contents($logFile), $logger::NOTICE) !== false);

        $logger->info('Test');
        $this->assertEquals(true, stripos(file_get_contents($logFile), $logger::INFO) !== false);

        $logger->debug('Test');
        $this->assertEquals(true, stripos(file_get_contents($logFile), $logger::DEBUG) !== false);

        $adapter = new MonologAdapter(['logger' => null]);
        $this->assertInstanceOf(AdapterInterface::class, $adapter);
        $this->assertNull($adapter->log($logger::INFO, 'Test'));

        return $this;
    }
}
