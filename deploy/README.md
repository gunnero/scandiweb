# Deployment runbook

The assignment requires an external, password-free, continuously available PHP/MySQL deployment. Static hosting alone is not compliant.

## Recommended managed PHP route

On an alwaysdata PHP site or comparable PHP host:

1. Select PHP 8.1 or newer.
2. Create a MySQL/MariaDB database and set the six `DB_*` variables from `backend/.env.example`.
3. Upload `backend/`, run `composer install --no-dev --classmap-authoritative`, and point `/graphql` and `/health` to `backend/public/index.php`.
4. Run `php backend/bin/seed.php` once.
5. Build the frontend with `VITE_GRAPHQL_ENDPOINT=https://YOUR_HOST/graphql npm --prefix frontend run build`.
6. Publish the contents of `build/` as the public document root with SPA fallback to `index.html`.

## Container route

The root `Dockerfile` produces one web container containing the Vite build and PHP endpoint. Supply an external persistent MySQL database through environment variables:

```text
APP_DEBUG=0
APP_ALLOWED_ORIGIN=https://YOUR_HOST
DB_HOST=YOUR_DATABASE_HOST
DB_PORT=3306
DB_NAME=YOUR_DATABASE_NAME
DB_USER=YOUR_DATABASE_USER
DB_PASSWORD=YOUR_DATABASE_PASSWORD
```

The container's startup initializer creates missing tables and imports the official catalog only when the catalog is empty. It never replaces existing orders.

## Release smoke checks

- `GET /` returns the Vite application.
- Direct `/category/all` and `/product/apple-imac-2021` requests return the SPA.
- `GET /health` returns `{"status":"ok"}`.
- `POST /graphql` returns all three categories and eight products.
- An order mutation writes one row to `orders`, the expected lines to `order_items`, and all chosen options to `order_item_attributes`.
- The external URL is HTTPS, password-free and does not sleep.
- Scandiweb Auto QA reports Passed for every test.
