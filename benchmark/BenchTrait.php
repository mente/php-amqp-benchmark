<?php

namespace Insomnia\Benchmark;

use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Trait with helper methods
 */
trait BenchTrait
{
    /**
     * @param string $queueName
     * @param string $exchangeName
     */
    protected function deleteQueue($queueName, $exchangeName)
    {
        /** @var AbstractConnection $conn */
        $conn = new AMQPStreamConnection(HOST, PORT, USER, PASS, VHOST);
        $ch = $conn->channel();

        $ch->queue_delete($queueName);
        $ch->exchange_delete($exchangeName);

        //redeclare to not benchmark rabbitmq creation of queue
        $ch->queue_declare($queueName, false, true, false, false);
        $ch->exchange_declare($exchangeName, 'direct', false, true, false);

        $ch->queue_bind($queueName, $exchangeName);

        $ch->close();
        $conn->close();
    }

    /**
     * @param AbstractConnection $conn
     * @param int                $messageLength
     * @param int                $messagesCount
     */
    protected function produceWithLibConnection(AbstractConnection $conn, $messageLength, $messagesCount)
    {
        $ch = $conn->channel();

        $ch->queue_declare($this->queueName, false, true, false, false);
        $ch->exchange_declare($this->exchangeName, 'direct', false, true, false);

        $ch->queue_bind($this->queueName, $this->exchangeName);

        $body = 'a';
        for ($i = 0; $i < $messageLength; $i++) {
            $body .= ord($i % 255);
        }

        for ($i = 0; $i < $messagesCount; $i++) {
            $ch->basic_publish(new AMQPMessage($body), $this->exchangeName);
        }

        $ch->close();
        $conn->close();
    }
}
