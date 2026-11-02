COMPOSE   ?= docker compose
CONTAINER ?= php_bundle_validate_http_response
WORKDIR   ?= /www
COMMAND   ?= /bin/bash

.DEFAULT_GOAL := help

.PHONY: help build run shutdown attach

help: ## Show this help
	@awk 'BEGIN {FS = ":.*## "} /^[a-zA-Z_-]+:.*## / {printf "  \033[36m%-10s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

# The Dockerfile copies composer.lock, but the file is not versioned (.gitignore), so it is generated when missing.
composer.lock:
	docker run --rm -v "$(CURDIR)":/app -w /app composer:2 composer update --no-install --no-scripts --no-interaction --ignore-platform-reqs

build: composer.lock ## Build the container image
	$(COMPOSE) build

run: composer.lock ## Build (if needed) and start the container in the background
	$(COMPOSE) up -d --build

shutdown: ## Stop and remove the container
	$(COMPOSE) down

attach: ## Enter the running container (COMMAND="..." runs another command instead of a shell)
	docker exec -w $(WORKDIR) -it $(CONTAINER) $(COMMAND)
