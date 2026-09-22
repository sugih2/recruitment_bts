up:
	docker compose up -d

down:
	docker compose down

build:
	docker compose build --no-cache

shell:
	docker compose exec app sh

install:
	docker compose run --rm app sh -c "composer global require laravel/installer --no-interaction && \
		export PATH=\"$$PATH:$$HOME/.composer/vendor/bin\" && \
		laravel new _tmp_laravel_install --no-interaction --no-authentication --no-node && \
		cp -a _tmp_laravel_install/. . && \
		rm -rf _tmp_laravel_install && \
		php artisan install:api --no-interaction"

artisan:
	docker compose exec app php artisan $(cmd)

migrate:
	docker compose exec app php artisan migrate

fresh:
	docker compose exec app php artisan migrate:fresh --seed

test:
	docker compose exec app php artisan test

logs:
	docker compose logs -f

frontend:
	docker compose --profile frontend up -d node
setup:
	@if [ ! -f .env ]; then cp .env.example .env; fi
	@if ! grep -q "^UID=" .env; then echo "UID=$$(id -u)" >> .env; fi
	@if ! grep -q "^GID=" .env; then echo "GID=$$(id -g)" >> .env; fi
	@echo "Setup complete. UID=$$(id -u), GID=$$(id -g) written to .env"

up: setup
	docker compose up -d
