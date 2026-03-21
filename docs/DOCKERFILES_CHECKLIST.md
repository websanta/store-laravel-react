# Docker Configuration Reference

Overview of all Dockerfiles, configuration files, and Docker Compose services in the project.

---

## File Locations

```
infrastructure/
├── docker-compose.yml
└── docker/
    ├── nginx/
    │   ├── Dockerfile
    │   ├── certs/                # add temp.pem + temp-key.pem here
    │   └── conf.d/
    │       ├── default.conf      # main virtual host (HTTP→HTTPS redirect, PHP-FPM proxy, Vite HMR)
    │       └── ssl.conf          # TLS settings (protocols, ciphers, session)
    ├── node/
    │   └── Dockerfile
    ├── php-fpm/
    │   ├── Dockerfile
    │   ├── php.ini               # PHP runtime config
    │   └── xdebug.ini            # Xdebug 3 config
    └── stripe/
        └── Dockerfile
```

---

## Dockerfiles

### PHP-FPM (`infrastructure/docker/php-fpm/Dockerfile`)

Base image: `php:8.4-fpm-alpine`

Installed PHP extensions: `pdo`, `pdo_pgsql`, `pgsql`, `mbstring`, `exif`, `pcntl`, `bcmath`, `gd`, `zip`, `intl`, `opcache`, `redis` (PECL), `xdebug` (PECL, conditional)

Build argument:
- `ENABLE_XDEBUG` (default: `true`) — set to `false` in prod for better performance

Application user: `www:www` (UID/GID 1000) — non-root

Exposed port: `9000` (PHP-FPM)

### Nginx (`infrastructure/docker/nginx/Dockerfile`)

Base image: `nginx:alpine`

Copies `conf.d/default.conf` and `conf.d/ssl.conf` into the container at build time. SSL certificates are mounted at runtime via a Docker volume.

Exposed ports: `80`, `443`

Health check: `curl -f http://localhost/health`

### Node (`infrastructure/docker/node/Dockerfile`)

Base image: `node:20-alpine`

Runs as non-root user (`node`, UID 1000). Installs `package.json` dependencies at build time for layer caching.

Exposed port: `5174` (Vite dev server)

Health check: `curl -k -f https://localhost:5174`

### Stripe CLI (`infrastructure/docker/stripe/Dockerfile`)

Base image: `stripe/stripe-cli:latest`

Adds `curl` and `jq`. Entrypoint remains the `stripe` binary.

Used only in the `dev` Docker Compose profile.

---

## docker-compose.yml

Located at `infrastructure/docker-compose.yml`.

### Profiles

Services are grouped into two profiles:

| Service | `dev` | `prod` |
|---|---|---|
| `store` | ✅ | ✅ |
| `nginx` | ✅ | ✅ |
| `node` | ✅ | — |
| `postgres` | ✅ | ✅ |
| `redis` | ✅ | ✅ |
| `queue` | ✅ | ✅ |
| `pgadmin` | ✅ | — |
| `mailpit` | ✅ | — |
| `stripe` | ✅ | — |

Start dev: `docker compose --profile dev up -d`
Start prod: `docker compose --profile prod up -d`

Or use the Makefile shortcuts: `make up-dev` / `make up-prod`.

### Named Volumes

| Volume | Purpose |
|---|---|
| `postgres_data` | PostgreSQL data persistence |
| `pgadmin_data` | pgAdmin configuration persistence |
| `redis_data` | Redis AOF persistence |
| `node_modules` | NPM packages (avoids host ↔ container permission issues) |
| `nginx_logs` | Nginx access and error logs |
| `mailpit_data` | Mailpit email storage |
| `stripe_config` | Stripe CLI authentication cache |

### Health Checks

| Service | Check |
|---|---|
| `store` | `php-fpm -t` |
| `nginx` | `nginx -t` |
| `node` | `curl -k -f https://localhost:5174` |
| `postgres` | `pg_isready -U {DB_USERNAME} -d {DB_DATABASE}` |
| `redis` | `redis-cli --raw incr ping` |
| `mailpit` | `wget --spider http://localhost:8025` |

---

## PHP Configuration (`infrastructure/docker/php-fpm/php.ini`)

Key settings:

| Setting | Value |
|---|---|
| `memory_limit` | 512M |
| `max_execution_time` | 300 |
| `upload_max_filesize` | 50M |
| `post_max_size` | 50M |
| `session.save_handler` | redis |
| `opcache.enable` | 1 |
| `opcache.memory_consumption` | 256 |
| `expose_php` | Off |
| `error_log` | `/var/log/php_errors.log` |

---

## Xdebug Configuration (`infrastructure/docker/php-fpm/xdebug.ini`)

| Setting | Value |
|---|---|
| `xdebug.mode` | `debug,develop,coverage` |
| `xdebug.start_with_request` | `yes` |
| `xdebug.client_host` | `host.docker.internal` |
| `xdebug.client_port` | `9003` |
| `xdebug.idekey` | `VSCODE` |
| `xdebug.max_nesting_level` | `512` |

To disable Xdebug (production or performance testing): set `ENABLE_XDEBUG=false` in `.env` and rebuild the `store` container.

---

## Nginx Configuration

### `default.conf` — what it handles

- HTTP (port 80) → HTTPS (301 redirect)
- HTTPS (port 443) — main virtual host
- PHP requests → `store:9000` via FastCGI
- Vite asset proxying (`/@vite/`, `/resources/`, `/node_modules/`)
- Vite HMR WebSocket proxy at `/vite-hmr`
- Static file caching (30 days, `Cache-Control: public, immutable`)
- Security headers (`X-Frame-Options`, `X-Content-Type-Options`, `X-XSS-Protection`, etc.)
- Gzip compression
- `/health` endpoint for Docker health checks

### `ssl.conf` — TLS settings

- Protocols: `TLSv1.2`, `TLSv1.3`
- Strong cipher suites (ECDHE + AES-GCM + ChaCha20)
- Session cache: `shared:SSL:10m`
- HSTS header: commented out — enable after verifying SSL works in your environment
