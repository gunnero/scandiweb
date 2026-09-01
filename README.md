# Scandiweb full-stack test

A standards-focused implementation of the Scandiweb Junior Full-Stack Developer assignment. The application is a Vite React SPA backed by an object-oriented PHP GraphQL API and a normalized MySQL database populated from Scandiweb's supplied `data.json`.

## Stack

- Vite, React and TypeScript
- Functional React components and plain CSS
- `webonyx/graphql-php` on PHP 8.1+
- FastRoute, extending the supplied GraphQL starter carcass
- MySQL 5.6-compatible SQL with PDO repositories
- PHPUnit, PHP_CodeSniffer, Vitest and Testing Library

No React framework, backend framework, component library, bundled catalog fallback or browser-local order substitute is used.

## GraphQL starter foundation

The backend extends Scandiweb's [provided GraphQL carcass](https://github.com/Mr0Bread/fullstack-test-starter/tree/8ed02d39620e4ba3dba186d52d2031c8930b1fff). It retains the starter's PSR-4 `App\` namespace, FastRoute `POST /graphql` entry point and Webonyx request/execute/serialize lifecycle. The placeholder `echo` query and `sum` mutation were replaced with the application-owned catalog schema, resolver classes and persistent `createOrder` mutation.

## Local setup

Prerequisites: Node.js 20.19+, npm 10+, PHP 8.1+, Composer 2 and MySQL 5.6 or newer.

Install dependencies:

```bash
npm ci
npm --prefix frontend ci
composer --working-dir=backend install
```

Create a local database and account. Replace `YOUR_LOCAL_PASSWORD` with a password used only on your machine, then put the same value in the ignored `backend/.env` file:

```bash
mysql -uroot -p -e "CREATE DATABASE scandiweb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -uroot -p -e "CREATE USER 'scandiweb'@'127.0.0.1' IDENTIFIED BY 'YOUR_LOCAL_PASSWORD'; GRANT ALL PRIVILEGES ON scandiweb.* TO 'scandiweb'@'127.0.0.1'; FLUSH PRIVILEGES;"
cp backend/.env.example backend/.env
composer --working-dir=backend run seed
```

Start the PHP API and Vite frontend together:

```bash
npm run dev
```

- Storefront: `http://localhost:3000`
- GraphQL endpoint: `http://localhost:8010/graphql`
- Health check: `http://localhost:8010/health`

Vite proxies `/graphql` to the local PHP service. If the frontend and backend are hosted on different origins, set `VITE_GRAPHQL_ENDPOINT` in `frontend/.env` and add the frontend origin to `APP_ALLOWED_ORIGIN`.

## Docker setup

The production container serves the built SPA and PHP endpoint from one origin. A MySQL 5.7 service is included because it remains compatible with the assignment's MySQL `^5.6` requirement.

```bash
SCANDIWEB_DB_PASSWORD='YOUR_LOCAL_PASSWORD' docker compose up --build
```

Open `http://localhost:8080`. The first container start creates the schema and imports `backend/data/data.json`; subsequent starts preserve catalog and order data in the database volume.

## Validation

Run the complete local quality gate:

```bash
npm run check
```

Or run the layers independently:

```bash
npm --prefix frontend run check
composer --working-dir=backend validate --strict
composer --working-dir=backend run check
npm run build
```

The backend tests include real MySQL catalog and transactional order-persistence checks. Seed the database before running them.

## Architecture

- `backend/src/Model/Category`, `backend/src/Model/Product` and `backend/src/Model/Attribute` contain abstract models and concrete type subclasses.
- Aggregate and named categories delegate to separate catalog repository operations through their own subtype behavior.
- Type dispatch uses explicit class registries rather than product/attribute `if` or `switch` chains.
- Attribute subclasses enforce type-specific invariants: text options require non-empty values, while swatches require hexadecimal colors.
- GraphQL types and resolvers are separate from PDO repositories.
- Product attributes are resolved by `AttributeResolver` and `AttributeType`, rather than directly inside product query logic.
- Checkout validates stock, quantities and every arbitrary attribute selection, calculates prices on the server and writes one order header, its lines and selection snapshots in one transaction.
- `backend/database/schema.sql` avoids native JSON columns/functions and other post-MySQL-5.6 features.

## Deployment and submission

Use the root `Dockerfile` on an always-on host with a persistent MySQL-compatible database, or deploy `backend/` to PHP hosting and `build/` as the public document root. Required environment variables are listed in `backend/.env.example`.

Before submission:

1. Deploy over HTTPS without visitor authentication or sleeping.
2. Verify direct PLP and PDP URLs, `POST /graphql`, and order rows.
3. Run Scandiweb Auto QA against the external URL until every check passes.
4. Save the Passed screenshot.
5. Keep the repository public or share it with `tests@scandiweb.com`.

The OpenAI Sites configuration can provide a static frontend preview, but PHP/MySQL must be hosted by a PHP-capable service for a compliant submission.
