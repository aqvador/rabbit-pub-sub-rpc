<?php
declare(strict_types=1);


namespace service\rabbit\pubSub\service\sub;


final class SubscribeConfig implements \JsonSerializable
{
    private $callback;

    public function __construct(
        private string $exchange,
        private string $typeExchange,
        /** null|array|string */
        private string $queue,
        private $routingKey,
        callable $callback
    )
    {
        $this->callback = $callback;
    }

    /**
     * @return callable
     */
    public function getCallback(): callable
    {
        return $this->callback;
    }

    /**
     * @return string
     */
    public function getExchange(): string
    {
        return $this->exchange;
    }

    /**
     * @return string
     */
    public function getTypeExchange(): string
    {
        return $this->typeExchange;
    }

    /**
     * @return string
     */
    public function getQueue(): string
    {
        return $this->queue;
    }

    /**
     * @return null|array|string
     */
    public function getRoutingKey()
    {
        return $this->routingKey;
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}