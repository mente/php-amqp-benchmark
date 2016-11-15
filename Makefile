.PHONY: benchmark benchmark-in-docker start-docker stop-docker benchmark-in-host build
DOCKER_NAME := rabbitmq_amqplib_ci
ITERATIONS=5
REVS=1000
REPORT=aggregate


benchmark-in-docker: build
	@make start-docker
	docker run -it --rm -v $(PWD):/bench --link=$(DOCKER_NAME) rabbitmq-benchmark /bench/vendor/bin/phpbench run benchmark/ProducerBench.php --revs=$(REVS) --iterations=$(ITERATIONS) --report=$(REPORT)
	docker run -it --rm -v $(PWD):/bench --link=$(DOCKER_NAME) rabbitmq-benchmark /bench/vendor/bin/phpbench run benchmark/ConsumerBench.php --revs=$(REVS) --iterations=$(ITERATIONS) --report=$(REPORT)
	@make stop-docker

benchmark-in-host:
	@make start-docker
	./vendor/bin/phpbench run benchmark/ProducerBench.php --revs=$(REVS) --iterations=$(ITERATIONS) --report=$(REPORT)
	./vendor/bin/phpbench run benchmark/ConsumerBench.php --revs=$(REVS) --iterations=$(ITERATIONS) --report=$(REPORT)
	@make stop-docker

build:
	docker build -t rabbitmq-benchmark .

start-docker:
	docker run -d --name $(DOCKER_NAME) -p 5672:5672 rabbitmq:latest && sleep 10

stop-docker:
	docker stop $(DOCKER_NAME) > /dev/null
	docker rm $(DOCKER_NAME) > /dev/null
