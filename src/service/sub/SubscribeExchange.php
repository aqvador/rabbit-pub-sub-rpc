<?php
declare(strict_types=1);


namespace service\rabbit\pubSub\service\sub;


use Psr\Log\LoggerInterface;
use service\rabbit\pubSub\service\Connection;

final class SubscribeExchange
{
    private $channel = null;

    public function __construct(
        private Connection $connection,
        private LoggerInterface $logger
    )
    {
    }

    public function subscribe(SubscribeConfig $config)
    {
        try {
            $this->channel = $this->connection->getConnection()->channel();

            $this->channel->exchange_declare($config->getExchange(), $config->getTypeExchange(), false, false, false);

            list($queue_name, ,) = $this->channel->queue_declare($config->getQueue(), false, false, true, false);

            if (is_array($config->getRoutingKey())) {

                array_map(fn($item) => $this->channel->queue_bind($queue_name, $config->getExchange(), $item), $config->getRoutingKey());

            } else if (is_string($config->getRoutingKey())) {

                $this->channel->queue_bind($queue_name, $config->getExchange(), $config->getRoutingKey());

            } else {

                $this->channel->queue_bind($queue_name, $config->getExchange());
            }

            $this->channel->basic_consume($queue_name, '', false, true, false, false, $config->getCallback());

            $this->logger->info('Subscribe rabbit', compact('config'));

            while ($this->channel->is_open()) {
                $this->channel->wait();
            }

        } catch (\Error | \Exception $error) {
            $this->logger->error('Subscribe rabbit', [
                'error_message' => $error->getMessage(),
                'error_file' => $error->getFile(),
                'error_line' => $error->getLine(),
                'subscribe_args' => $config
            ]);
        }
    }

    public function __destruct()
    {
        if ($this->channel) {
            $this->channel->close();
        }
    }
}