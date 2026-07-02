# CA-1 — Evidência: stack local ponta a ponta (STORY-000)

Data: 2026-07-02
Comando de subida (um comando), stack Sail isolado (portas livres p/ não colidir com outro agente ativo):
```
cd app && WWWUSER=$(id -u) WWWGROUP=$(id -g) APP_PORT=8000 VITE_PORT=5173 \
  COMPOSE_PROJECT_NAME=quantah-s000 ./vendor/bin/sail up -d pgsql laravel.test
```

## Containers
- quantah-laravel.test-1 | sail-8.5/app | 0.0.0.0:5173->5173/tcp, [::]:5173->5173/tcp, 0.0.0.0:8000->80/tcp, [::]:8000->80/tcp | Up 11 minutes
- quantah-pgsql-1 | postgres:18-alpine | 0.0.0.0:5442->5432/tcp, [::]:5442->5432/tcp | Up 11 minutes (healthy)

## Postgres + migrations
```
/var/run/postgresql:5432 - accepting connections
migrate:status (amostra):

 Migration name .. Batch / Status 
 0001_01_01_000000_create_users_table .. [1] Ran 
 0001_01_01_000001_create_cache_table .. [1] Ran 
 0001_01_01_000002_create_jobs_table .. [1] Ran 

```

## Rota Inertia servida (GET /)
```
GET / -> HTTP 200  bytes=24156
componente Inertia no HTML: component&quot;:&quot;Welcome
versoes reportadas pelo backend: laravelVersion&quot;:&quot;13.18.0
phpVersion&quot;:&quot;8.5.7
asset buildado /build/assets/app-Yx30LMzV.js -> HTTP 200
```
