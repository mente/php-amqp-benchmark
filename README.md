# php-amqp-benchmark
Benchmark of available amqp libraries in PHP using phpbench (but only php-amqplib for now)

## How to run

Benchmark requires docker and some php version + make. To run benchmarks:

* install dependencies via `composer install`
* `make`
 
That's it!
 
## Results

Running with 20 revs and 5 iterations (which is not recommended, try at least 1000 revs x 5 iterations)

`ProducerBench` on php 7.0.12
```
+---------------+--------------+--------+---------------------------------------------------------------------------------------------+------+-----+------------+--------------+--------------+--------------+--------------+--------------+--------+---------+
| benchmark     | subject      | groups | params                                                                                      | revs | its | mem_peak   | best         | mean         | mode         | worst        | stdev        | rstdev | diff    |
+---------------+--------------+--------+---------------------------------------------------------------------------------------------+------+-----+------------+--------------+--------------+--------------+--------------+--------------+--------+---------+
| ProducerBench | benchPublish |        | {"connection":"PhpAmqpLib\\Connection\\AMQPStreamConnection","messages":1,"length":10}      | 20   | 5   | 1,773,088b | 14,634.750μs | 17,628.320μs | 15,686.371μs | 20,956.850μs | 2,725.482μs  | 15.46% | +4.38%  |
| ProducerBench | benchPublish |        | {"connection":"PhpAmqpLib\\Connection\\AMQPSocketConnection","messages":1,"length":10}      | 20   | 5   | 1,795,416b | 19,869.250μs | 23,756.170μs | 24,027.169μs | 28,009.850μs | 2,963.051μs  | 12.47% | +29.05% |
| ProducerBench | benchPublish |        | {"connection":"PhpAmqpLib\\Connection\\AMQPStreamConnection","messages":10,"length":10}     | 20   | 5   | 1,773,088b | 14,044.250μs | 16,938.310μs | 17,611.021μs | 18,600.800μs | 1,650.313μs  | 9.74%  | +0.49%  |
| ProducerBench | benchPublish |        | {"connection":"PhpAmqpLib\\Connection\\AMQPSocketConnection","messages":10,"length":10}     | 20   | 5   | 1,795,416b | 14,415.500μs | 16,855.420μs | 15,656.538μs | 20,287.450μs | 2,130.257μs  | 12.64% | 0.00%   |
| ProducerBench | benchPublish |        | {"connection":"PhpAmqpLib\\Connection\\AMQPStreamConnection","messages":100,"length":10}    | 20   | 5   | 1,773,088b | 31,325.050μs | 38,125.850μs | 35,904.542μs | 46,422.600μs | 5,234.321μs  | 13.73% | +55.79% |
| ProducerBench | benchPublish |        | {"connection":"PhpAmqpLib\\Connection\\AMQPSocketConnection","messages":100,"length":10}    | 20   | 5   | 1,795,416b | 32,542.700μs | 37,168.550μs | 33,968.574μs | 43,417.650μs | 4,550.193μs  | 12.24% | +54.65% |
| ProducerBench | benchPublish |        | {"connection":"PhpAmqpLib\\Connection\\AMQPStreamConnection","messages":1,"length":1000}    | 20   | 5   | 1,777,688b | 21,990.500μs | 24,904.970μs | 22,584.437μs | 30,421.100μs | 3,458.971μs  | 13.89% | +32.32% |
| ProducerBench | benchPublish |        | {"connection":"PhpAmqpLib\\Connection\\AMQPSocketConnection","messages":1,"length":1000}    | 20   | 5   | 1,800,488b | 20,579.800μs | 22,733.230μs | 23,817.416μs | 24,518.900μs | 1,639.221μs  | 7.21%  | +25.86% |
| ProducerBench | benchPublish |        | {"connection":"PhpAmqpLib\\Connection\\AMQPStreamConnection","messages":10,"length":1000}   | 20   | 5   | 1,777,824b | 18,382.900μs | 27,140.980μs | 20,484.388μs | 54,178.250μs | 13,562.856μs | 49.97% | +37.90% |
| ProducerBench | benchPublish |        | {"connection":"PhpAmqpLib\\Connection\\AMQPSocketConnection","messages":10,"length":1000}   | 20   | 5   | 1,800,624b | 18,606.650μs | 22,408.970μs | 20,621.635μs | 29,560.450μs | 3,820.460μs  | 17.05% | +24.78% |
| ProducerBench | benchPublish |        | {"connection":"PhpAmqpLib\\Connection\\AMQPStreamConnection","messages":100,"length":1000}  | 20   | 5   | 1,777,824b | 35,056.500μs | 42,784.970μs | 38,967.539μs | 58,295.350μs | 8,180.492μs  | 19.12% | +60.60% |
| ProducerBench | benchPublish |        | {"connection":"PhpAmqpLib\\Connection\\AMQPSocketConnection","messages":100,"length":1000}  | 20   | 5   | 1,800,624b | 37,473.300μs | 41,237.690μs | 39,125.999μs | 49,892.850μs | 4,447.236μs  | 10.78% | +59.13% |
| ProducerBench | benchPublish |        | {"connection":"PhpAmqpLib\\Connection\\AMQPStreamConnection","messages":1,"length":10000}   | 20   | 5   | 1,836,352b | 18,526.450μs | 22,053.070μs | 20,539.405μs | 28,513.050μs | 3,472.480μs  | 15.75% | +23.57% |
| ProducerBench | benchPublish |        | {"connection":"PhpAmqpLib\\Connection\\AMQPSocketConnection","messages":1,"length":10000}   | 20   | 5   | 1,855,320b | 14,394.650μs | 22,732.540μs | 25,320.672μs | 28,283.200μs | 4,994.675μs  | 21.97% | +25.85% |
| ProducerBench | benchPublish |        | {"connection":"PhpAmqpLib\\Connection\\AMQPStreamConnection","messages":10,"length":10000}  | 20   | 5   | 1,836,352b | 29,251.800μs | 32,112.170μs | 31,248.183μs | 35,667.850μs | 2,272.708μs  | 7.08%  | +47.51% |
| ProducerBench | benchPublish |        | {"connection":"PhpAmqpLib\\Connection\\AMQPSocketConnection","messages":10,"length":10000}  | 20   | 5   | 1,855,456b | 28,559.100μs | 34,882.340μs | 32,402.350μs | 45,786.300μs | 5,867.634μs  | 16.82% | +51.68% |
| ProducerBench | benchPublish |        | {"connection":"PhpAmqpLib\\Connection\\AMQPStreamConnection","messages":100,"length":10000} | 20   | 5   | 1,953,088b | 57,581.100μs | 61,032.570μs | 60,852.286μs | 64,546.000μs | 2,300.954μs  | 3.77%  | +72.38% |
| ProducerBench | benchPublish |        | {"connection":"PhpAmqpLib\\Connection\\AMQPSocketConnection","messages":100,"length":10000} | 20   | 5   | 1,855,456b | 46,311.400μs | 48,716.890μs | 49,567.161μs | 51,011.100μs | 1,722.810μs  | 3.54%  | +65.40% |
+---------------+--------------+--------+---------------------------------------------------------------------------------------------+------+-----+------------+--------------+--------------+--------------+--------------+--------------+--------+---------+
```

`ConsumerBench` on php 7.0.12
```
+---------------+--------------+--------+---------------------------------------------------------------+------+-----+------------+--------------+--------------+--------------+--------------+--------------+--------+---------+
| benchmark     | subject      | groups | params                                                        | revs | its | mem_peak   | best         | mean         | mode         | worst        | stdev        | rstdev | diff    |
+---------------+--------------+--------+---------------------------------------------------------------+------+-----+------------+--------------+--------------+--------------+--------------+--------------+--------+---------+
| ConsumerBench | benchConsume |        | {"connection":"PhpAmqpLib\\Connection\\AMQPStreamConnection"} | 20   | 5   | 1,978,286b | 12,675.250μs | 31,458.770μs | 17,959.458μs | 55,536.050μs | 18,509.705μs | 58.84% | +43.55% |
| ConsumerBench | benchConsume |        | {"connection":"PhpAmqpLib\\Connection\\AMQPSocketConnection"} | 20   | 5   | 1,687,176b | 12,217.150μs | 17,758.800μs | 14,559.130μs | 25,085.450μs | 4,992.793μs  | 28.11% | 0.00%   |
+---------------+--------------+--------+---------------------------------------------------------------+------+-----+------------+--------------+--------------+--------------+--------------+--------------+--------+---------+
```


## What's next

Add amqp-ext, check with keepalive and not, add bunny (?)
