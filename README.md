# php-amqp-benchmark
Benchmark of available amqp libraries in PHP using phpbench. Right now supports [php-amqplib](https://github.com/php-amqplib/php-amqplib), [amqp-ext](https://github.com/pdezwart/php-amqp) + symfony bundles for it.

Idea of this benchmark came from real production usage. We have noticed that php-amqplib took ~50ms to push a message. 
Consumer benchmarks are also included but not that fine grained.

## How to run

Benchmark requires docker + make. To run benchmarks:

* `make`
 
That's it! 

## Under hood

`make` does following:
* build php image based on 7.0.12
* run `composer install` in docker
* start rabbitmq instance in docker
* run benchmarks
* stop docker

If there was some error and rabbitmq docker instance didn't stop, run `make stop-docker`. Check `Makefile` for detailed information.

Current benchmark cases reflect production use in our company: 1 big message pushed per request. To adopt benchmarks for own cases update `ProducerBench::messagesCount()` and `ProducerBench::messageLength()`
 
## Results

Running with 1000 revs and 5 iterations on idle server in docker

`ProducerBench` on php 7.0.12
```
+---------------+-----------------------------------+--------+---------------------------------------------+------+-----+-------------+--------------+--------------+--------------+--------------+-----------+--------+---------+
| benchmark     | subject                           | groups | params                                      | revs | its | mem_peak    | best         | mean         | mode         | worst        | stdev     | rstdev | diff    |
+---------------+-----------------------------------+--------+---------------------------------------------+------+-----+-------------+--------------+--------------+--------------+--------------+-----------+--------+---------+
| ProducerBench | benchStreamConnection             |        | {"length":10000,"alive":true,"messages":1}  | 1000 | 5   | 15,679,864b | 82,769.578μs | 83,099.127μs | 83,214.860μs | 83,403.392μs | 229.773μs | 0.28%  | +96.42% |
| ProducerBench | benchStreamConnection             |        | {"length":10000,"alive":false,"messages":1} | 1000 | 5   | 15,688,056b | 83,055.956μs | 83,343.620μs | 83,325.739μs | 83,679.752μs | 222.186μs | 0.27%  | +96.43% |
| ProducerBench | benchSocketConnection             |        | {"length":10000,"alive":true,"messages":1}  | 1000 | 5   | 15,051,416b | 3,196.397μs  | 3,923.715μs  | 4,324.155μs  | 4,512.115μs  | 567.925μs | 14.47% | +24.27% |
| ProducerBench | benchSocketConnection             |        | {"length":10000,"alive":false,"messages":1} | 1000 | 5   | 15,051,416b | 3,029.927μs  | 3,912.132μs  | 4,134.947μs  | 4,349.238μs  | 470.997μs | 12.04% | +24.05% |
| ProducerBench | benchExtension                    |        | {"length":10000,"messages":1}               | 1000 | 5   | 1,734,584b  | 2,829.156μs  | 3,148.285μs  | 3,035.734μs  | 3,647.460μs  | 275.690μs | 8.76%  | +5.62%  |
| ProducerBench | benchBunny                        |        | {"length":10000,"messages":1}               | 1000 | 5   | 29,316,888b | 43,991.661μs | 44,041.097μs | 44,037.783μs | 44,095.487μs | 33.548μs  | 0.08%  | +93.25% |
| ProducerBench | benchSocketConnectionInBundle     |        | {"length":10000,"alive":true,"messages":1}  | 1000 | 5   | 15,105,176b | 3,278.849μs  | 3,861.149μs  | 4,153.481μs  | 4,267.648μs  | 409.718μs | 10.61% | +23.05% |
| ProducerBench | benchSocketConnectionInBundle     |        | {"length":10000,"alive":false,"messages":1} | 1000 | 5   | 15,105,176b | 3,220.278μs  | 3,636.562μs  | 3,742.603μs  | 3,866.545μs  | 228.185μs | 6.27%  | +18.29% |
| ProducerBench | benchStreamConnectionInBundle     |        | {"length":10000,"alive":true,"messages":1}  | 1000 | 5   | 15,733,608b | 84,039.469μs | 84,053.974μs | 84,043.857μs | 84,075.631μs | 14.881μs  | 0.02%  | +96.47% |
| ProducerBench | benchStreamConnectionInBundle     |        | {"length":10000,"alive":false,"messages":1} | 1000 | 5   | 15,741,800b | 83,959.722μs | 84,035.645μs | 84,040.637μs | 84,107.391μs | 49.145μs  | 0.06%  | +96.46% |
| ProducerBench | benchLazyStreamConnectionInBundle |        | {"length":10000,"alive":true,"messages":1}  | 1000 | 5   | 15,747,600b | 83,983.349μs | 84,023.564μs | 84,033.401μs | 84,043.671μs | 21.126μs  | 0.03%  | +96.46% |
| ProducerBench | benchLazyStreamConnectionInBundle |        | {"length":10000,"alive":false,"messages":1} | 1000 | 5   | 15,755,792b | 84,007.580μs | 84,066.051μs | 84,054.118μs | 84,139.697μs | 43.532μs  | 0.05%  | +96.47% |
| ProducerBench | benchLazySocketConnectionInBundle |        | {"length":10000,"alive":true,"messages":1}  | 1000 | 5   | 15,119,344b | 3,555.436μs  | 4,014.462μs  | 4,002.224μs  | 4,574.670μs  | 386.679μs | 9.63%  | +25.99% |
| ProducerBench | benchLazySocketConnectionInBundle |        | {"length":10000,"alive":false,"messages":1} | 1000 | 5   | 15,119,344b | 3,188.946μs  | 3,785.022μs  | 3,685.951μs  | 4,504.850μs  | 429.972μs | 11.36% | +21.50% |
| ProducerBench | benchExtensionInBundle            |        | {"length":10000,"messages":1}               | 1000 | 5   | 1,734,592b  | 2,870.306μs  | 2,971.301μs  | 2,958.325μs  | 3,086.545μs  | 75.799μs  | 2.55%  | 0.00%   |
+---------------+-----------------------------------+--------+---------------------------------------------+------+-----+-------------+--------------+--------------+--------------+--------------+-----------+--------+---------+
```

`ConsumerBench` on php 7.0.12
```
+---------------+--------------+--------+---------------------------------------------------------------+------+-----+-------------+--------------+--------------+--------------+--------------+-----------+--------+---------+
| benchmark     | subject      | groups | params                                                        | revs | its | mem_peak    | best         | mean         | mode         | worst        | stdev     | rstdev | diff    |
+---------------+--------------+--------+---------------------------------------------------------------+------+-----+-------------+--------------+--------------+--------------+--------------+-----------+--------+---------+
| ConsumerBench | benchConsume |        | {"connection":"PhpAmqpLib\\Connection\\AMQPStreamConnection"} | 1000 | 5   | 14,569,304b | 44,017.694μs | 44,277.477μs | 44,050.083μs | 45,199.881μs | 461.698μs | 1.04%  | +95.07% |
| ConsumerBench | benchConsume |        | {"connection":"PhpAmqpLib\\Connection\\AMQPSocketConnection"} | 1000 | 5   | 14,194,944b | 2,089.361μs  | 2,181.877μs  | 2,149.016μs  | 2,278.700μs  | 68.619μs  | 3.14%  | 0.00%   |
+---------------+--------------+--------+---------------------------------------------------------------+------+-----+-------------+--------------+--------------+--------------+--------------+-----------+--------+---------+
```


## What's next

Add [bunny](https://github.com/jakubkulhan/bunny) (?) consumer benchmarks
