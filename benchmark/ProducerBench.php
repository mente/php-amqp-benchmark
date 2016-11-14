<?php

use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Connection\AMQPSocketConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;
use PhpBench\Benchmark\Metadata\Annotations\ParamProviders;

/**
 * @BeforeMethods({"cleanup"})
 */
class ProducerBench
{
    private $exchangeName = 'bench_exchange';

    private $queueName = 'bench_queue';

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
//            [
//                'length' => 10,
//            ],
//            [
//                'length' => 1000,
//            ],
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

        //redeclare to not benchmark rabbitmq creation of queue
        $ch->queue_declare($this->queueName, false, true, false, false);
        $ch->exchange_declare($this->exchangeName, 'direct', false, true, false);

        $ch->queue_bind($this->queueName, $this->exchangeName);

        $ch->close();
        $conn->close();
    }

    /**
     * @ParamProviders({"messageLength", "keepAlive", "messagesCount"})
     *
     * @param array $params
     */
    public function benchStreamConnection($params)
    {
        /** @var AbstractConnection $conn */
        $conn = new AMQPStreamConnection(HOST, PORT, USER, PASS, VHOST, false, 'AMQPLAIN', null, 'en_US', 3, 3, null, $params['alive']);
        $this->produceWithLibConnection($conn, $params);
    }

    /**
     * @ParamProviders({"messageLength", "keepAlive", "messagesCount"})
     *
     * @param array $params
     */
    public function benchSocketConnection($params)
    {
        /** @var AbstractConnection $conn */
        $conn = new AMQPSocketConnection(HOST, PORT, USER, PASS, VHOST, false, 'AMQPLAIN', null, 'en_US', 3, $params['alive']);
        $this->produceWithLibConnection($conn, $params);
    }

    /**
     * @ParamProviders({"messageLength", "messagesCount"})
     *
     * @param array $params
     */
    public function benchExtension($params)
    {
        // Create a connection
        $cnn = new \AMQPConnection();
        $cnn->setHost(HOST);
        $cnn->setPort(PORT);
        $cnn->setLogin(USER);
        $cnn->setPassword(PASS);
        $cnn->setVhost(VHOST);
        $cnn->connect();

        // Create a channel
        $ch = new \AMQPChannel($cnn);

        // Declare a new exchange
        $ex = new \AMQPExchange($ch);
        $ex->setName($this->exchangeName);
        $ex->setType("direct");
        $ex->setFlags(AMQP_DURABLE);
        $ex->declareExchange();

        // Create a new queue
        $q = new \AMQPQueue($ch);
        $q->setName($this->queueName);
        $q->setFlags(AMQP_DURABLE);
        $q->declareQueue();

        // Bind it on the exchange to routing.key
        $q->bind($this->exchangeName);

        $body = 'a';
        for ($i = 0; $i < $params['length']; $i++) {
            $body .= ord($i % 255);
        }

        for ($i = 0; $i < $params['messages']; $i++) {
            $ex->publish($body, null, AMQP_DURABLE);
        }

        //Disconnect
        $cnn->disconnect();
    }

    /**
     * @param AbstractConnection $conn
     * @param array              $params
     */
    private function produceWithLibConnection(AbstractConnection $conn, $params)
    {
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
