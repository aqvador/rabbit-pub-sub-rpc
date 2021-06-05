<?php
declare(strict_types=1);


namespace service\rabbit\pubSub\service\pub;

use PhpAmqpLib\Message\AMQPMessage;
use service\rabbit\pubSub\service\Connection;

final class PublishExchange
{
    private $channel = null;

    public function __construct(
        private Connection $connection,
        private string $exchange,
        private string $exchangeType
    )
    {
    }

    public function push(PublishMessage $message): void
    {
        if (!$this->channel) {
            $this->channel = $this->connection->getConnection()->channel();
        }

        $this->channel->exchange_declare($this->exchange, $this->exchangeType, false, false, false);

        $msg = new AMQPMessage($message->getMessage());

        $this->channel->basic_publish($msg, $this->exchange, $message->getRoute() ?: '');
    }

    public function __destruct()
    {
        if ($this->channel) {
            $this->channel->close();
        }
    }
}