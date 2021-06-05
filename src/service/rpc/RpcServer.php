<?php
declare(strict_types=1);


namespace service\rabbit\pubSub\service\rpc;


use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use service\rabbit\pubSub\service\Connection;
use service\rabbit\pubSub\traites\LoggerTrait;

class RpcServer
{
    use LoggerTrait;

    private $channel;
    private $clientCallback;

    public function __construct(
        private Connection $connection,
        private string $queueName,
        protected ?LoggerInterface $logger
    )
    {
        $this->channel = $this->connection->getConnection()->channel();
    }

    public function listen(callable $callback)
    {
        $this->clientCallback = $callback;

        $this->channel->queue_declare($this->queueName, false, false, false, false);

        $this->channel->basic_qos(null, 1, null);
        $this->channel->basic_consume($this->queueName, '', false, false, false, false, fn($res) => $this->callback($res));

        while ($this->channel->is_open()) {
            $this->channel->wait();
        }
    }

    private function callback(AMQPMessage $request)
    {
        try {
            $this->infoLog('new request RPC', ['request' => $request, 'request_id' => $request->get('correlation_id')]);

            $result = call_user_func($this->clientCallback, $request->getBody());

            $this->infoLog('response RPC', ['response' => $result, 'request_id' => $request->get('correlation_id')]);

            $response = new AMQPMessage(json_encode($result), ['correlation_id' => $request->get('correlation_id')]);

            $request->getChannel()->basic_publish($response, '', $request->get('reply_to'));

            $request->ack();

        } catch (\Exception | \Error $exception) {
            $this->errorLog('RPC server', [
                'error_message' => $exception->getMessage(),
                'error_file' => $exception->getFile(),
                'error_line' => $exception->getLine()
            ]);
            $request->nack();
        }
    }

    public function __destruct()
    {
        if ($this->channel) {
            $this->channel->close();
        }
    }
}