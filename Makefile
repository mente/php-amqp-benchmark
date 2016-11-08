.PHONY: benchmark benchmark-in-docker
default=default
DOCKER_NAME := rabbitmq_amqplib_ci
ITERATIONS=5
REVS=1000
REPORT=aggregate

default: start-docker benchmark stop-docker

start-docker:
	docker run -d --name $(DOCKER_NAME) -p 5672:5672 rabbitmq:latest && sleep 10

benchmark:
	./vendor/bin/phpbench run benchmark/ProducerBench.php --revs=$(REVS) --iterations=$(ITERATIONS) --report=$(REPORT)
	./vendor/bin/phpbench run benchmark/ConsumerBench.php --revs=$(REVS) --iterations=$(ITERATIONS) --report=$(REPORT)

stop-docker:
	docker stop $(DOCKER_NAME) > /dev/null
	docker rm $(DOCKER_NAME) > /dev/null