<?php

use Bunny\Client;
use Insomnia\Benchmark\BenchTrait;
use M6Web\Bundle\AmqpBundle\Factory\ProducerFactory;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Connection\AMQPLazyConnection;
use PhpAmqpLib\Connection\AMQPLazySocketConnection;
use PhpAmqpLib\Connection\AMQPSocketConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpBench\Benchmark\Metadata\Annotations\BeforeMethods;
use PhpBench\Benchmark\Metadata\Annotations\ParamProviders;
use M6Web\Bundle\AmqpBundle\Amqp\Producer as ExtProducer;

/**
 * @BeforeMethods({"cleanup"})
 */
class ProducerBench
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
//            [
//                'messages' => 10,
//            ],
//            [
//                'messages' => 100,
//            ],
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

    /**
     * @ParamProviders({"messageLength", "keepAlive", "messagesCount"})
     *
     * @param array $params
     */
    public function benchStreamConnection($params)
    {
        $conn = new AMQPStreamConnection(HOST, PORT, USER, PASS, VHOST, false, 'AMQPLAIN', null, 'en_US', 3, 3, null, $params['alive']);
        $this->produceWithLibConnection($conn, $params['length'], $params['messages']);
    }

    /**
     * @ParamProviders({"messageLength", "keepAlive", "messagesCount"})
     *
     * @param array $params
     */
    public function benchSocketConnection($params)
    {
        $conn = new AMQPSocketConnection(HOST, PORT, USER, PASS, VHOST, false, 'AMQPLAIN', null, 'en_US', 3, $params['alive']);
        $this->produceWithLibConnection($conn, $params['length'], $params['messages']);
    }

    /**
     * @ParamProviders({"messageLength", "messagesCount"})
     *
     * @param array $params
     */
    public function benchExtension($params)
    {
        // Create a connection
        $cnn = $this->getExtensionConnection();

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

        $body = '';
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
     * @ParamProviders({"messageLength", "messagesCount"})
     *
     * @param array $params
     */
    public function benchBunny($params)
    {
        $connection = [
            'host'      => HOST,
            'vhost'     => VHOST,
            'user'      => USER,
            'password'  => PASS,
        ];

        $bunny = new Client($connection);
        $bunny->connect();

        $channel = $bunny->channel();
        $channel->queueDeclare($this->queueName, false, true);

        $body = '';
        for ($i = 0; $i < $params['length']; $i++) {
            $body .= ord($i % 255);
        }

        for ($i = 0; $i < $params['messages']; $i++) {
            $channel->publish($body, [], $this->exchangeName);
        }

        $bunny->disconnect();
    }

    /**
     * @ParamProviders({"messageLength", "keepAlive", "messagesCount"})
     *
     * @param array $params
     */
    public function benchSocketConnectionInBundle($params)
    {
        $conn = new AMQPSocketConnection(HOST, PORT, USER, PASS, VHOST, false, 'AMQPLAIN', null, 'en_US', 3, $params['alive']);
        $this->produceWithLibConnectionViaBundle($conn, $params);
    }

    /**
     * @ParamProviders({"messageLength", "keepAlive", "messagesCount"})
     *
     * @param array $params
     */
    public function benchStreamConnectionInBundle($params)
    {
        $conn = new AMQPStreamConnection(HOST, PORT, USER, PASS, VHOST, false, 'AMQPLAIN', null, 'en_US', 3, 3, null, $params['alive']);
        $this->produceWithLibConnectionViaBundle($conn, $params);
    }

    /**
     * @ParamProviders({"messageLength", "keepAlive", "messagesCount"})
     *
     * @param array $params
     */
    public function benchLazyStreamConnectionInBundle($params)
    {
        $conn = new AMQPLazyConnection(HOST, PORT, USER, PASS, VHOST, false, 'AMQPLAIN', null, 'en_US', 3, 3, null, $params['alive']);
        $this->produceWithLibConnectionViaBundle($conn, $params);
    }

    /**
     * @ParamProviders({"messageLength", "keepAlive", "messagesCount"})
     *
     * @param array $params
     */
    public function benchLazySocketConnectionInBundle($params)
    {
        $conn = new AMQPLazySocketConnection(HOST, PORT, USER, PASS, VHOST, false, 'AMQPLAIN', null, 'en_US', 3, 3, null, $params['alive']);
        $this->produceWithLibConnectionViaBundle($conn, $params);
    }

    /**
     * @ParamProviders({"messageLength", "messagesCount"})
     *
     * @param array $params
     */
    public function benchExtensionInBundle($params)
    {
        // Create a connection
        $conn = $this->getExtensionConnection();

        $factory = new ProducerFactory(AMQPChannel::class, AMQPExchange::class, AMQPQueue::class);
        $exchangeOptions = [
            'type' => 'direct',
            'durable' => true,
            'name' => $this->exchangeName,
            'arguments' => [],
        ];
        $queueOptions = [
            'durable' => true,
            'name' => $this->queueName,
            'arguments' => [],
            'routing_keys' => [],
        ];

        $producer = $factory->get(ExtProducer::class, $conn, $exchangeOptions, $queueOptions);
        $body = '';
        for ($i = 0; $i < $params['length']; $i++) {
            $body .= ord($i % 255);
        }

        for ($i = 0; $i < $params['messages']; $i++) {
            $producer->publishMessage($body);
        }

        $conn->disconnect();
    }

    private function produceWithLibConnectionViaBundle(AbstractConnection $conn, $params)
    {
        $producer = new Producer($conn);
        $producer->setExchangeOptions([
            'name' => $this->exchangeName,
            'type' => 'direct',
        ]);
        $producer->setQueueOptions([
            'name' => $this->queueName,
        ]);

        $body = '';
        for ($i = 0; $i < $params['length']; $i++) {
            $body .= ord($i % 255);
        }

        for ($i = 0; $i < $params['messages']; $i++) {
            $producer->publish($body);
        }

        unset($producer);
    }

    /**
     * @return AMQPConnection
     */
    private function getExtensionConnection()
    {
        $cnn = new \AMQPConnection();
        $cnn->setHost(HOST);
        $cnn->setPort(PORT);
        $cnn->setLogin(USER);
        $cnn->setPassword(PASS);
        $cnn->setVhost(VHOST);
        $cnn->connect();

        return $cnn;
    }
}
