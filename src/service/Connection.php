<?php
declare(strict_types=1);


namespace service\rabbit\pubSub\service;


use PhpAmqpLib\Connection\AMQPStreamConnection;

final class Connection
{
    private $connection;

    public function __construct(
        private string $host,
        private int $port,
        private string $user,
        private string $password,
        private string $vhost,
    )
    {
    }

    /**
     * @return mixed
     */
    public function getConnection()
    {
        return $this->setGetConnection();
    }

    private function setGetConnection()
    {
        if (!$this->connection) {
            $this->connection = new AMQPStreamConnection($this->getHost(), $this->getPort(), $this->getUser(), $this->getPassword(), $this->getVhost(), heartbeat: 30);
        }

        return $this->connection;
    }

    public function __destruct()
    {
        if ($this->connection) {
            $this->connection->close();
        }
    }


    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getVhost(): string
    {
        return $this->vhost;
    }
}