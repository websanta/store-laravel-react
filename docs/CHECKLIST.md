# Setup Checklist

Use this checklist to track your environment setup progress.

---

## Prerequisites

- [ ] Docker 29+ installed — `docker --version`
- [ ] Docker Compose available — `docker compose version`
- [ ] Git installed — `git --version`
- [ ] `make` installed — `make --version`
- [ ] Current user added to `docker` group — `groups $USER`
- [ ] At least 4 GB RAM available
- [ ] At least 20 GB free disk space

---

## Repository

- [ ] Repository cloned
- [ ] Working directory: project root

---

## SSL Certificates

- [ ] `infrastructure/docker/nginx/certs/` directory exists
- [ ] `temp.pem` generated (certificate)
- [ ] `temp-key.pem` generated (private key)
- [ ] Certificate permissions set (`644`)

---

## Environment

- [ ] `.env` created from `.env.example`
- [ ] `APP_NAME` set
- [ ] `APP_URL` set (e.g. `https://{your-local-domain}`)
- [ ] `APP_KEY` generated (`make key-generate`)
- [ ] `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` configured
- [ ] `REDIS_PASSWORD` configured
- [ ] `APP_ADMIN_PASSWORD` set (for seeder)
- [ ] `APP_VENDOR_PASSWORD` set (for seeder)
- [ ] `PAYMENT_DRIVER` set (`mock` for dev, `stripe` for real payments)
- [ ] Stripe keys set if `PAYMENT_DRIVER=stripe`
- [ ] `ENABLE_XDEBUG` set (`true` for dev)

---

## Local Hostname (optional)

- [ ] `/etc/hosts` updated with `127.0.0.1 {your-local-domain}` (Linux host)
- [ ] DNS resolves correctly — `ping {your-local-domain}`

---

## Scripts

- [ ] `chmod +x scripts/*.sh` — scripts are executable

---

## Docker Containers

- [ ] Images built — `make dbuild`
- [ ] Dev containers started — `make up-dev`
- [ ] All containers healthy — `make ps`

Expected healthy containers in dev mode:

| Container | Status |
|---|---|
| `store_app` | Up (healthy) |
| `store_nginx` | Up (healthy) |
| `store_node` | Up (healthy) |
| `store_postgres` | Up (healthy) |
| `store_redis` | Up (healthy) |
| `store_pgadmin` | Up |
| `store_queue` | Up |
| `store_mailpit` | Up (healthy) |
| `store_stripe` | Up (healthy) — if Stripe keys set |

---

## Dependencies

- [ ] Composer dependencies installed — `make composer-install`
- [ ] NPM dependencies installed — `make npm-install`
- [ ] `vendor/` directory exists
- [ ] `node_modules/` directory exists (in container volume)

---

## Application Initialization

- [ ] Application key generated — `make key-generate`
- [ ] Migrations run — `make migrate`
- [ ] Storage symlink created — `make storage-link`
- [ ] Permissions fixed — `make permissions`

---

## Admin Panel

- [ ] Filament installed — `make filament-install`
- [ ] Admin user created — `make filament-user`
- [ ] Admin panel accessible — `https://{your-local-domain}/admin`

---

## Access Verification

- [ ] Application loads — `https://{your-local-domain}`
- [ ] SSL certificate accepted in browser
- [ ] Home page shows (product listing or empty state)
- [ ] Login / Registration works
- [ ] Admin panel accessible (after `make filament-user`)
- [ ] Mailpit accessible — `http://localhost:8025`
- [ ] pgAdmin accessible — `http://localhost:5050`
- [ ] Vite dev server accessible (HMR active) — `https://{your-local-domain}:5174`

---

## Demo Data (optional)

- [ ] Database seeded — `make seed`
- [ ] Demo departments / categories exist (Electronics, Household, Tools)
- [ ] Demo products visible on home page
- [ ] Demo vendor accounts usable

---

## Development Workflow

- [ ] HMR working — frontend change reflected in browser instantly
- [ ] PHP changes applied after container restart (or file watchers)
- [ ] Tests pass — `make test`

---

## Testing Environment

- [ ] `.env.testing` created from `.env.testing.example`
- [ ] Test database created — `make test-db-create`
- [ ] Test migrations run — `make test-migrate`
- [ ] All tests pass — `make test`

---
