<?php

use Bunny\Client;
use Insomnia\Benchmark\BenchTrait;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Connection\AMQPSocketConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;
use PhpBench\Benchmark\Metadata\Annotations\ParamProviders;

/**
 * @BeforeMethods({"cleanup", "publish"})
 */
class ConsumerBench
{
    use BenchTrait;

    /**
     * @var string
     */
    private $exchangeName = 'bench_exchange';
    /**
     * @var string
     */
    private $queueName = 'bench_queue';

    public function cleanup()
    {
        $this->deleteQueue($this->queueName, $this->exchangeName);
    }

    public function publish($params)
    {
        $conn = new AMQPStreamConnection(HOST, PORT, USER, PASS, VHOST);
        $this->produceWithLibConnection($conn, $params['length'], $params['messages']);
    }

    public function keepAlive()
    {
        return [
            [
                'alive' => true,
            ],
            [
                'alive' => false,
            ]
        ];
    }

    public function messagesCount()
    {
        return [
            [
                'messages' => 1,
            ],
        ];
    }

    public function messageLength()
    {
        return [
            [
                'length' => 10000,
            ]
        ];
    }

    public function tcpNoDelay()
    {
        return [
            [
                'tcp_nodelay' => 0,
            ],
            [
                'tcp_nodelay' => 1,
            ],
        ];
    }

    /**
     * @ParamProviders({"keepAlive", "messagesCount", "messageLength"})
     *
     * @param array $params
     */
    public function benchStreamConnection($params)
    {
        $conn = new AMQPStreamConnection(HOST, PORT, USER, PASS, VHOST, false, 'AMQPLAIN', null, 'en_US', 3, 3, null, $params['alive']);
        $this->consumeWithLibraryConnection($conn, $params);
    }

    /**
     * @ParamProviders({"keepAlive", "messagesCount", "messageLength"})
     *
     * @param array $params
     */
    public function benchSocketConnection($params)
    {
        $conn = new AMQPSocketConnection(HOST, PORT, USER, PASS, VHOST, false, 'AMQPLAIN', null, 'en_US', 3, 3, null, $params['alive']);
        $this->consumeWithLibraryConnection($conn, $params);
    }

    /**
     * @ParamProviders({"messageLength", "messagesCount", "tcpNoDelay"})
     *
     * @param array $params
     */
    public function benchBunnyConnection($params)
    {
        $connection = [
            'host'      => HOST,
            'vhost'     => VHOST,
            'user'      => USER,
            'password'  => PASS,
            'tcp_nodelay' => $params['tcp_nodelay'],
        ];

        $bunny = new Client($connection);
        $bunny->connect();

        $channel = $bunny->channel();
        $channel->queueDeclare($this->queueName, false, true);
        $noop = function(){};

        for ($i = 0; $i < $params['messages']; $i++) {
            $channel->consume($noop);
        }

        $bunny->disconnect();
    }

    /**
     * @param AbstractConnection $conn
     * @param array              $params
     */
    private function consumeWithLibraryConnection(AbstractConnection $conn, $params)
    {
        $ch = $conn->channel();

        $ch->queue_declare($this->queueName, false, true, false, false);
        $ch->exchange_declare($this->exchangeName, 'direct', false, true, false);
        $ch->queue_bind($this->queueName, $this->exchangeName);

        $noop = function(){};
        for ($i = 0; $i < $params['messages']; $i++) {
            $ch->basic_consume($this->queueName, '', false, true, false, false, $noop);
        }

        $ch->close();
        $conn->close();
    }
}
