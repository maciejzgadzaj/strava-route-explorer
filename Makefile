VERSION := "0.0.5"

SELF_DIR := $(dir $(lastword $(MAKEFILE_LIST)))
COMPOSE = docker-compose --file docker-compose-dev.yml
PHP_SERVICE := $(COMPOSE) exec php sh

export APP_ENV := $(shell grep APP_ENV $(SELF_DIR).env | awk -F '=' '{print $$NF}')

UID := $(shell id -u)
GID := $(shell id -g)


## environment

build: ## Build the environment
	$(COMPOSE) build --build-arg uid=$(UID) --build-arg gid=$(GID)
.PHONY: build

.check:
	@echo "\033[31mWARNING!!!\033[0m Executing this script will reinitialize the project and all of its data to factory"
	@( read -p "Are you sure you wish to continue? [y/N]: " sure && case "$$sure" in [yY]) true;; *) false;; esac )
.PHONY: .check

config: ## Generate the ".env" and "phpunit.xml" files if they don't already exist
	-@test -f .env \
		|| (cp .env.dist .env \
		&& export $$(cat .env | grep -v ^\# | xargs)
	-@test -f phpunit.xml \
		|| (cp phpunit.xml.dist phpunit.xml
.PHONY: config

destroy: ## Destroy the environment
	@rm -f .env phpunit.xml
	$(COMPOSE) rm -v --force --stop || true
.PHONY: destroy

down: ## Shutdown the environment
	@$(COMPOSE) down
.PHONY: down

install: ## Install the environment
	@make config build up composer-install yarn-install encore-dev
.PHONY: install

logs: ## Follow new log entries generated by all containers
	@grc -c /Users/maciej.zgadzaj/.oh-my-zsh/custom/plugins/ankorstore/grc.conf tail -f var/log/dev.log
.PHONY: logs

logs-docker: ## Follow new log entries generated by all containers
	@$(COMPOSE) logs -f --tail=0
.PHONY: logs

ps: ## List all containers managed by the environment
	$(COMPOSE) ps
.PHONY: ps

reinstall: ## Reinstall the environment
	@make .check destroy install
.PHONY: reinstall

restart: down up ## Restart the environment
.PHONY: restart

start: ## alias for "make up"
	@make up
.PHONY: start

stats: ## Print real-time statistics about containers resource usage
	@docker stats $(docker ps --format={{.Names}})
.PHONY: stats

status: ## List all containers managed by the environment
	@make ps
.PHONY: status

stop: ## Stop the environment
	$(COMPOSE) stop
.PHONY: stop

uninstall: ## Uninstall the environment
	@make config
	$(COMPOSE) kill
	$(COMPOSE) down --volumes --remove-orphans
.PHONY: uninstall

run: ## Project startup
# Delete existing log files only if php container is not running.
	@$(COMPOSE) ps | grep php > /dev/null || (echo "\033[33mDeleting old log files ...\033[0m" && rm -fv var/log/*.log)
	@echo "\033[33mFiring up containers ...\033[0m"
	$(COMPOSE) up -d
	@echo "\033[32mContainers running!\033[0m"
	@echo "Site: \033[33mhttp://localhost/\033[0m"
	@echo "phpMyAdmin: \033[33mhttp://localhost:8080/\033[0m"
up: run


## project

ifeq (composer,$(firstword $(MAKECMDGOALS)))
  	RUN_ARGS := $(wordlist 2,$(words $(MAKECMDGOALS)),$(MAKECMDGOALS))
  	$(eval $(subst :,\:,$(RUN_ARGS)):.ignore;@:)
endif

composer: ## Execute composer in the "php" container
	@$(PHP_SERVICE) -c "composer $(RUN_ARGS)"
.PHONY: composer

ifeq (console,$(firstword $(MAKECMDGOALS)))
  	RUN_ARGS := $(wordlist 2,$(words $(MAKECMDGOALS)),$(MAKECMDGOALS))
  	$(eval $(subst :,\:,$(RUN_ARGS)):.ignore;@:)
endif

console: ## Execute Symfony console in the "php" container
	@$(PHP_SERVICE) -c "bin/console $(RUN_ARGS)"
.PHONY: console

ifeq (sh,$(firstword $(MAKECMDGOALS)))
  	RUN_ARGS := $(wordlist 2,$(words $(MAKECMDGOALS)),$(MAKECMDGOALS))
  	$(eval $(subst :,\:,$(RUN_ARGS)):.ignore;@:)
  	SHELL = $(if $(filter $(RUN_ARGS),nginx),sh,bash)
endif

sh: ## Open a terminal in the specified container
	$(COMPOSE) exec $(RUN_ARGS) sh
.PHONY: sh


## code

composer-install: ## Install Composer dependencies from the "php" container
	@$(PHP_SERVICE) -c "composer install --optimize-autoloader --dev --no-interaction -o"
.PHONY: composer-install

encore: ## Compile dev assets once with Encore/Webpack
	@make encore-dev
.PHONY: encore

encore-dev: ## Compile dev assets once with Encore/Webpack
	@$(PHP_SERVICE) -c "yarn run encore dev"
.PHONY: encore-dev

encore-prod: ## Compile production assets once with Encore/Webpack and minify & optimize them
	@$(PHP_SERVICE) -c "yarn run encore production"
.PHONY: encore-prod

encore-watch: ## Compile assets automatically with Encore/Webpack when files change
	@$(PHP_SERVICE) -c "yarn run encore dev --watch"
.PHONY: encore-watch

update: ## Update project code
	@make .update-code composer-install
.PHONY: update

.update-code:
	@( git stash && git checkout develop && git fetch --all && git pull --rebase && git checkout @{-1} && git stash pop )
.PHONY: .update-code

yarn-install: ## Install Yarn dependencies from the "php" container
	@$(PHP_SERVICE) -c "yarn install"
.PHONY: yarn-install


## data

reset: ## Load doctrine fixtures
	$(PHP_SERVICE) -c "bin/console --env=test doctrine:fixtures:load --no-interaction|true"
.PHONY: reset


## changeblog

mongo-check: ## Check content files
	@make console changeblog:links:check
	@make console changeblog:images:check
	@make console changeblog:redirects:generate
.PHONY: mongo-check

mongo-check-import: check import ## Check content files and import content into database
.PHONY: mongo-check-import

ifeq (mongo-import,$(firstword $(MAKECMDGOALS)))
  	RUN_ARGS := $(wordlist 2,$(words $(MAKECMDGOALS)),$(MAKECMDGOALS))
  	$(eval $(subst :,\:,$(RUN_ARGS)):.ignore;@:)
endif

mongo-import: ## Import content <type> into database
	@$(PHP_SERVICE) -c "bin/console mongo:import -p $(RUN_ARGS)"
	@test -f /usr/bin/osascript \
		&& osascript -e 'display notification "Content import completed" with title "Change(b)log Make"'
.PHONY: mongo-import


## quality

lint: ## Lint YAML configuration, Twig templates and JavaScript files
	$(PHP_SERVICE) -c "php bin/console lint:yaml config"
	$(PHP_SERVICE) -c "php bin/console lint:twig templates"
.PHONY: lint

cs: ## Run the PHP coding standards fixer on dry-run mode
	@test -f .php_cs || cp .php_cs.dist .php_cs
	$(PHP_SERVICE) -c "php vendor/bin/php-cs-fixer fix --config=.php_cs \
		--cache-file=var/cache/.php_cs --verbose --dry-run"
.PHONY: cs

cs-fix: ## Run the PHP coding standards fixer on apply mode
	@test -f .php_cs || cp .php_cs.dist .php_cs
	$(PHP_SERVICE) -c "php vendor/bin/php-cs-fixer fix --config=.php_cs \
		--cache-file=var/cache/.php_cs --verbose"
.PHONY: cs-fix

phpunit: ## Run the test suite (unit & functional)
	@make reset APP_ENV=test
	$(PHP_SERVICE) -c "./bin/phpunit"
.PHONY: phpunit

phpunit-coverage: ## Run the test suite (unit & functional) with code coverage report
	@make reset APP_ENV=test
    @$(PHP_SERVICE) -c "bin/phpunit -v --coverage-text --coverage-html=tmp/report --colors=always"
.PHONY: phpunit-coverage

security: ## Run a security analysis on dependencies
	$(PHP_SERVICE) -c "php bin/console security:check"
.PHONY: security

test: ## Execute all quality assurance tools
	make lint phpcsfixer phpunit security
.PHONY: test


## docker

RUNNING_CONTAINERS := $(shell docker container ls -a -q)

stop-all: ## Stop all running Docker containers
	@docker container stop $(RUNNING_CONTAINERS)
.PHONY: stop-all

system-prune: ## Remove all unused containers, networks, images (both dangling and unreferenced)
	@docker system prune -a
.PHONY: system-prune

system-prune-all: ## Remove all unused containers, networks, images (both dangling and unreferenced) and volumes
	@docker system prune -a --volumes
.PHONY: system-prune-all

#

help:
	@awk 'BEGIN {FS = ":.*##"; \
	printf "\033[32mMakefile\033[0m version \033[33m%s\033[0m\n \
	\n\033[33mUsage:\033[0m\n  make <target>\n\n\033[33mAvailable targets:\033[0m\n", $(VERSION) } \
	/^[a-zA-Z_-]+:.*?##/ { printf "  \033[32m%-20s\033[0m %s\n", $$1, $$2 } \
	/^##/ { printf "\033[33m%s\033[0m\n", substr($$0, 3) } ' $(MAKEFILE_LIST)
.PHONY: help
.DEFAULT_GOAL := help

.ignore:
	@:
