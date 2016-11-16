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
| ProducerBench | benchStreamConnection             |        | {"length":10000,"alive":true,"messages":1}  | 1000 | 5   | 15,595,040b | 83,399.591μs | 83,524.389μs | 83,538.585μs | 83,631.702μs | 78.470μs  | 0.09%  | +96.21% |
| ProducerBench | benchStreamConnection             |        | {"length":10000,"alive":false,"messages":1} | 1000 | 5   | 15,603,232b | 83,263.452μs | 83,552.294μs | 83,605.351μs | 83,751.470μs | 159.134μs | 0.19%  | +96.21% |
| ProducerBench | benchSocketConnection             |        | {"length":10000,"alive":true,"messages":1}  | 1000 | 5   | 14,966,592b | 3,019.856μs  | 3,429.933μs  | 3,216.970μs  | 3,970.095μs  | 359.444μs | 10.48% | +7.72%  |
| ProducerBench | benchSocketConnection             |        | {"length":10000,"alive":false,"messages":1} | 1000 | 5   | 14,966,592b | 2,976.760μs  | 3,744.477μs  | 3,263.736μs  | 5,692.399μs  | 992.943μs | 26.52% | +15.47% |
| ProducerBench | benchExtension                    |        | {"length":10000,"messages":1}               | 1000 | 5   | 1,649,720b  | 2,806.814μs  | 3,165.280μs  | 3,241.993μs  | 3,406.212μs  | 208.662μs | 6.59%  | 0.00%   |
| ProducerBench | benchSocketConnectionInBundle     |        | {"length":10000,"alive":true,"messages":1}  | 1000 | 5   | 15,020,472b | 3,508.562μs  | 4,030.839μs  | 3,777.561μs  | 4,683.422μs  | 434.206μs | 10.77% | +21.47% |
| ProducerBench | benchSocketConnectionInBundle     |        | {"length":10000,"alive":false,"messages":1} | 1000 | 5   | 15,020,472b | 3,167.289μs  | 3,663.262μs  | 3,900.860μs  | 4,045.169μs  | 353.297μs | 9.64%  | +13.59% |
| ProducerBench | benchStreamConnectionInBundle     |        | {"length":10000,"alive":true,"messages":1}  | 1000 | 5   | 15,648,904b | 83,971.592μs | 84,014.088μs | 84,018.148μs | 84,043.903μs | 27.277μs  | 0.03%  | +96.23% |
| ProducerBench | benchStreamConnectionInBundle     |        | {"length":10000,"alive":false,"messages":1} | 1000 | 5   | 15,657,096b | 84,043.417μs | 84,069.036μs | 84,082.660μs | 84,091.391μs | 19.953μs  | 0.02%  | +96.23% |
| ProducerBench | benchLazyStreamConnectionInBundle |        | {"length":10000,"alive":true,"messages":1}  | 1000 | 5   | 15,662,896b | 83,939.590μs | 84,025.255μs | 84,031.416μs | 84,099.737μs | 55.458μs  | 0.07%  | +96.23% |
| ProducerBench | benchLazyStreamConnectionInBundle |        | {"length":10000,"alive":false,"messages":1} | 1000 | 5   | 15,671,088b | 83,983.488μs | 84,044.554μs | 84,058.345μs | 84,104.922μs | 44.677μs  | 0.05%  | +96.23% |
| ProducerBench | benchLazySocketConnectionInBundle |        | {"length":10000,"alive":true,"messages":1}  | 1000 | 5   | 15,034,640b | 3,390.307μs  | 3,657.892μs  | 3,507.522μs  | 4,271.147μs  | 319.875μs | 8.74%  | +13.47% |
| ProducerBench | benchLazySocketConnectionInBundle |        | {"length":10000,"alive":false,"messages":1} | 1000 | 5   | 15,034,640b | 3,448.534μs  | 4,018.611μs  | 4,152.250μs  | 4,420.423μs  | 331.625μs | 8.25%  | +21.23% |
+---------------+-----------------------------------+--------+---------------------------------------------+------+-----+-------------+--------------+--------------+--------------+--------------+-----------+--------+---------+
```

`ConsumerBench` on php 7.0.12
```
+---------------+--------------+--------+---------------------------------------------------------------+------+-----+-------------+--------------+--------------+--------------+--------------+-----------+--------+---------+
| benchmark     | subject      | groups | params                                                        | revs | its | mem_peak    | best         | mean         | mode         | worst        | stdev     | rstdev | diff    |
+---------------+--------------+--------+---------------------------------------------------------------+------+-----+-------------+--------------+--------------+--------------+--------------+-----------+--------+---------+
| ConsumerBench | benchConsume |        | {"connection":"PhpAmqpLib\\Connection\\AMQPStreamConnection"} | 1000 | 5   | 14,491,608b | 44,012.166μs | 44,327.434μs | 44,072.631μs | 45,355.548μs | 516.536μs | 1.17%  | +95.16% |
| ConsumerBench | benchConsume |        | {"connection":"PhpAmqpLib\\Connection\\AMQPSocketConnection"} | 1000 | 5   | 14,117,280b | 2,032.960μs  | 2,147.196μs  | 2,066.253μs  | 2,326.285μs  | 117.498μs | 5.47%  | 0.00%   |
+---------------+--------------+--------+---------------------------------------------------------------+------+-----+-------------+--------------+--------------+--------------+--------------+-----------+--------+---------+
```


## What's next

Add [bunny](https://github.com/jakubkulhan/bunny) (?)
