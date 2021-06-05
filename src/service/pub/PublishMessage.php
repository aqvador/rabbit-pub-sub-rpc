<?php
declare(strict_types=1);


namespace service\rabbit\pubSub\service\pub;


final class PublishMessage
{
    public function __construct(
        private string $message,
        private ?string $route
    )
    {
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return string|null
     */
    public function getRoute(): ?string
    {
        return $this->route;
    }
}