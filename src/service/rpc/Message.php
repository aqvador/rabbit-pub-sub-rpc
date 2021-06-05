<?php
declare(strict_types=1);


namespace service\rabbit\pubSub\service\rpc;


use Psr\Http\Message\StreamInterface;

class Message implements StreamInterface
{
    public function __construct(private array $response)
    {
    }

    public function __toString()
    {
        return $this->getContents();
    }

    public function close()
    {
        // TODO: Implement close() method.
    }

    public function detach()
    {
        // TODO: Implement detach() method.
    }

    public function getSize()
    {
        $m = memory_get_usage();
        $a = $this->response;
        return memory_get_usage() - $m;
    }

    public function tell()
    {
        // TODO: Implement tell() method.
    }

    public function eof()
    {
        // TODO: Implement eof() method.
    }

    public function isSeekable()
    {
        // TODO: Implement isSeekable() method.
    }

    public function seek($offset, $whence = SEEK_SET)
    {
        // TODO: Implement seek() method.
    }

    public function rewind()
    {
        // TODO: Implement rewind() method.
    }

    public function isWritable()
    {
        // TODO: Implement isWritable() method.
    }

    public function write($string)
    {
        // TODO: Implement write() method.
    }

    public function isReadable()
    {
        // TODO: Implement isReadable() method.
    }

    public function read($length)
    {
        // TODO: Implement read() method.
    }

    public function getContents()
    {
        return json_encode($this->response);
    }

    public function getMetadata($key = null)
    {
        // TODO: Implement getMetadata() method.
    }
}