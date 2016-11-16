.PHONY: benchmark benchmark-in-docker start-docker stop-docker benchmark-in-host build
DOCKER_NAME := rabbitmq_amqplib_ci
ITERATIONS=5
REVS=1000
REPORT=aggregate


benchmark-in-docker: build stop-docker
	@make start-docker
	docker run -it --rm -v `pwd`:/bench --link=$(DOCKER_NAME) rabbitmq-benchmark /bench/vendor/bin/phpbench run benchmark/ProducerBench.php --revs=$(REVS) --iterations=$(ITERATIONS) --report=$(REPORT)
	docker run -it --rm -v `pwd`:/bench --link=$(DOCKER_NAME) rabbitmq-benchmark /bench/vendor/bin/phpbench run benchmark/ConsumerBench.php --revs=$(REVS) --iterations=$(ITERATIONS) --report=$(REPORT)
	@make stop-docker

benchmark-in-host: start-docker benchmark stop-docker

benchmark:
	./vendor/bin/phpbench run benchmark/ProducerBench.php --revs=$(REVS) --iterations=$(ITERATIONS) --report=$(REPORT)
	./vendor/bin/phpbench run benchmark/ConsumerBench.php --revs=$(REVS) --iterations=$(ITERATIONS) --report=$(REPORT)

build:
	docker build -t rabbitmq-benchmark .
	docker run --rm -v `pwd`:/app composer/composer --ignore-platform-reqs install

start-docker:
	docker run -d --name $(DOCKER_NAME) -p 5672:5672 rabbitmq:latest && sleep 10

stop-docker:
	docker stop $(DOCKER_NAME) 2>&1 > /dev/null
	docker rm $(DOCKER_NAME) 2>&1 > /dev/null
