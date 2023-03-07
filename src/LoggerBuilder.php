<?php
/**
 * Created by PhpStorm.
 * User: alexw
 * Date: 22/01/19
 * Time: 19:12.
 */

namespace IngenicoClient;

use Gelf\Publisher;
use Gelf\Transport\TcpTransport;
use Monolog\Formatter\GelfMessageFormatter;
use Monolog\Handler\GelfHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\WebProcessor;
use Psr\Log\LoggerInterface;

/**
 * Class LoggerBuilder.
 */
class LoggerBuilder
{
    /** @var LoggerInterface */
    protected LoggerInterface $logger;

    /**
     * Gets Logger.
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * build logger.
     *
     *
     *
     * @throws \Exception
     */
    public function createLogger(string $channel, string $path = '/tmp/ingenico_sdk.log', int $level = Logger::DEBUG): static
    {
        $this->logger = new Logger($channel);
        $this->logger->pushHandler(new StreamHandler($path, $level));
        $this->logger->pushProcessor(new WebProcessor());

        return $this;
    }

    /**
     * build Gelf logger
     *
     *
     * @return $this
     */
    public function createGelfLogger(string $channel, string $host, int $port = 12201, int $level = Logger::DEBUG): static
    {
        $transport = new TcpTransport($host, $port);
        $publisher = new Publisher($transport);

        $handler = new GelfHandler($publisher, $level);
        $handler->setFormatter(new GelfMessageFormatter());

        $this->logger = new Logger($channel);
        $this->logger->pushHandler($handler);

        return $this;
    }
}
