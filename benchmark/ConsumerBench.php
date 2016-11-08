<?php

use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Connection\AMQPSocketConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpBench\Benchmark\Metadata\Annotations\BeforeClassMethods;
use PhpBench\Benchmark\Metadata\Annotations\Iterations;
use PhpBench\Benchmark\Metadata\Annotations\ParamProviders;
use PhpBench\Benchmark\Metadata\Annotations\Revs;

/**
 * @BeforeClassMethods({"publishMessages"})
 */
class ConsumerBench
{
    public static function publishMessages()
    {
        $exchange = 'bench_exchange';
        $queue = 'bench_queue';
        $conn = new AMQPStreamConnection(HOST, PORT, USER, PASS, VHOST);
        $ch = $conn->channel();

        $ch->queue_delete($queue);
        $ch->queue_declare($queue, false, false, false, false);

        $ch->exchange_delete($exchange);
        $ch->exchange_declare($exchange, 'direct', false, false, false);

        $ch->queue_bind($queue, $exchange);

        $msg_body = <<<EOT
abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz
abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz
abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz
abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz
abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz
abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz
abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz
abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz
abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz
abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyza
EOT;

        $msg = new AMQPMessage($msg_body);

        $time = microtime(true);

        $max = 10000;

        // Publishes $max messages using $msg_body as the content.
        for ($i = 0; $i < $max; $i++) {
            $ch->basic_publish($msg, $exchange);
        }

        $ch->basic_publish(new AMQPMessage('quit'), $exchange);

        $ch->close();
        $conn->close();
    }

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

    /**
     * @ParamProviders({"connectionClass"})
     *
     * @param array $params
     */
    public function benchConsume($params)
    {
        $exchange = 'bench_exchange';
        $queue = 'bench_queue';

        /** @var AbstractConnection $conn */
        $conn = new $params['connection'](HOST, PORT, USER, PASS, VHOST);
        $ch = $conn->channel();

        $ch->queue_declare($queue, false, false, false, false);
        $ch->exchange_declare($exchange, 'direct', false, false, false);
        $ch->queue_bind($queue, $exchange);

        $noop = function(){};
        $ch->basic_consume($queue, '', false, true, false, false, $noop);

        $ch->close();
        $conn->close();
    }
}
