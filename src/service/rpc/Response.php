<?php
declare(strict_types=1);


namespace service\rabbit\pubSub\service\rpc;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class Response implements ResponseInterface
{
    /**
     * Response constructor.
     * @param StreamInterface $body
     * @param int $statusCode
     * @param array $headers
     * @param string $protocolVersion
     */
    public function __construct(
        private StreamInterface $body,
        private int $statusCode = 200,
        private array $headers = [],
        private string $protocolVersion = '0.1'
    )
    {
    }

    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    public function withProtocolVersion($version)
    {
        return $this;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function hasHeader($name)
    {
        return isset($this->headers[$name]);
    }

    public function getHeader($name)
    {
        return $this->hasHeader($name) ? $this->headers[$name] : null;
    }

    public function getHeaderLine($name)
    {
        return $this->hasHeader($name) ? (string)$this->headers[$name] : '';
    }

    public function withHeader($name, $value)
    {
        return $this->hasHeader($name) ? $this->getHeader($name) === $value : false;
    }

    public function withAddedHeader($name, $value)
    {
        return false;
    }

    public function withoutHeader($name)
    {
        return false;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function withBody(StreamInterface $body)
    {
        if ($body === $this->body) {
            return $this;
        }

        $new = clone $this;
        $new->body = $body;
        return $new;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function withStatus($code, $reasonPhrase = '')
    {
        if ($code === $this->statusCode) {
            return $this;
        }

        $new = clone $this;
        $new->statusCode = $code;
        return $new;
    }

    public function getReasonPhrase()
    {
        // TODO: Implement getReasonPhrase() method.
    }
}