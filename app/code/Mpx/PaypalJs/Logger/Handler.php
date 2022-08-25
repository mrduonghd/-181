<?php
namespace Mpx\PaypalJs\Logger;

use Mpx\PaypalJs\Model\Config;

/**
 * Class Handler
 * display error PayPal checkout
 */
class Handler
{

    /** @var Config */
    protected $_paypalConfig;

    /** @var \Psr\Log\LoggerInterface */
    protected $_logger;

    public function __construct(
        Config $config,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_paypalConfig = $config;
        $this->_logger       = $logger;
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function debug(string $message, array $context = []): void
    {
        if ($this->_paypalConfig->isSetFlag(Config::CONFIG_XML_DEBUG_MODE)) {
            $this->_logger->debug($message, $context);
        }
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function error(string $message, array $context = []): void
    {
        $this->_logger->error($message, $context);
    }
}
