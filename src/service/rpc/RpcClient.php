<?php
declare(strict_types=1);


namespace service\rabbit\pubSub\service\rpc;


use PhpAmqpLib\Message\AMQPMessage;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use service\rabbit\pubSub\service\Connection;
use service\rabbit\pubSub\traites\LoggerTrait;

class RpcClient
{
    use LoggerTrait;

    private $channel;
    private $callback_queue;
    private ?array $response = null;
    private $corr_id;

    public function __construct(
        private Connection $connection,
        private string $routingQueue,
        protected ?LoggerInterface $logger,
    )
    {
        $this->channel = $this->connection->getConnection()->channel();
        $this->queueDeclare();
    }

    public function request(\JsonSerializable $message, int $timeOut = 4): ResponseInterface
    {
        try {

            $this->sendRequest($message);

            return $this->waitResponse($timeOut);

        } catch (\Exception | \Error $exception) {
            $this->errorLog('RPC Client', [
                'error_message' => $exception->getMessage(),
                'error_file' => $exception->getFile(),
                'error_line' => $exception->getLine()
            ]);

            return new Response(new Message(['rpc_message' => $exception->getMessage()]), 400);
        }
    }

    private function sendRequest(\JsonSerializable $message)
    {
        $this->response = null;
        $this->corr_id = uniqid();

        $this->infoLog('RPC new request', ['request' => json_encode($message), 'request_id' => $this->corr_id, 'route' => $this->routingQueue]);

        $msg = new AMQPMessage(json_encode($message), ['correlation_id' => $this->corr_id, 'reply_to' => $this->callback_queue]);

        $this->channel->basic_publish($msg, '', $this->routingQueue);
    }


    private function waitResponse(int $timeout): ResponseInterface
    {
        while (!$this->response) {
            try {
                $this->channel->wait(timeout: $timeout);
            } catch (\Exception | \Error $exception) {
                $this->warningLog($exception->getMessage(), []);

                return new Response(new Message(['rpc_message' => $exception->getMessage()]), 400);
            }
        }

        $this->infoLog('RPC response', ['request_id' => $this->corr_id, 'response' => $this->response]);

        return new Response(new Message($this->response), 200);
    }

    private function queueDeclare()
    {
        if ($this->callback_queue) {
            return;
        }

        list($this->callback_queue, ,) = $this->channel->queue_declare(
            "",
            false,
            false,
            true,
            false,
        );
        $this->channel->basic_consume(
            $this->callback_queue,
            '',
            false,
            true,
            false,
            false,
            function ($resp) {
                if ($resp->get('correlation_id') === $this->corr_id) {
                    $this->response = json_decode($resp->body, true);
                }
            }
        );
    }

    public function __destruct()
    {
        if ($this->channel) {
            $this->channel->close();
        }
    }
}