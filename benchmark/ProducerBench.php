<?php

use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Connection\AMQPSocketConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;
use PhpBench\Benchmark\Metadata\Annotations\ParamProviders;
use PhpBench\Benchmark\Metadata\Annotations\Revs;

/**
 * @BeforeMethods({"cleanup"})
 */
class ProducerBench
{
    private $exchangeName = 'bench_exchange';

    private $queueName = 'bench_queue';

    public function connectionClass()
    {
        return [
            [
                'connection' => AMQPStreamConnection::class,
            ],
            [
                'connection' => AMQPSocketConnection::class,
            ]
        ];
    }

    public function messagesCount()
    {
        return [
            [
                'messages' => 1,
            ],
            [
                'messages' => 10,
            ],
            [
                'messages' => 100,
            ],
        ];
    }

    public function messageLength()
    {
        return [
            [
                'length' => 10,
            ],
            [
                'length' => 1000,
            ],
            [
                'length' => 10000,
            ]
        ];
    }

    public function cleanup()
    {
        /** @var AbstractConnection $conn */
        $conn = new AMQPStreamConnection(HOST, PORT, USER, PASS, VHOST);
        $ch = $conn->channel();

        $ch->queue_delete($this->queueName);
        $ch->exchange_delete($this->exchangeName);

        $ch->close();
        $conn->close();
    }

    /**
     * @ParamProviders({"connectionClass", "messagesCount", "messageLength"})
     *
     * @param array $params
     */
    public function benchPublish($params)
    {
        /** @var AbstractConnection $conn */
        $conn = new $params['connection'](HOST, PORT, USER, PASS, VHOST, false, 'AMQPLAIN', null, 'en_US', 3, 3, null, true);
        $ch = $conn->channel();

        $ch->queue_declare($this->queueName, false, true, false, false);
        $ch->exchange_declare($this->exchangeName, 'direct', false, true, false);

        $ch->queue_bind($this->queueName, $this->exchangeName);

        $body = 'a';
        for ($i = 0; $i < $params['length']; $i++) {
            $body .= ord($i % 255);
        }

        for ($i = 0; $i < $params['messages']; $i++) {
            $ch->basic_publish(new AMQPMessage($body), $this->exchangeName);
        }

        $ch->close();
        $conn->close();
    }
}
