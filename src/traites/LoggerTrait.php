<?php
declare(strict_types=1);


namespace service\rabbit\pubSub\traites;


use Psr\Log\LoggerInterface;

trait LoggerTrait
{
    protected ?LoggerInterface $logger;

    private function errorLog(string $message, array $context)
    {
        $this->sendLog('error', $message, $context);
    }

    private function infoLog(string $message, array $context)
    {
        $this->sendLog('info', $message, $context);
    }

    private function warningLog(string $message, array $context)
    {
        $this->sendLog('warning', $message, $context);
    }


    private function sendLog(string $level, string $message, array $context)
    {
        if ($this->logger) {
            $this->logger->log(strtoupper($level), $message, $context);
        }
    }

}