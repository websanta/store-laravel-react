# Quick Start Guide

Get the application running locally in under 10 minutes.

## Prerequisites

- Docker 29+ and Docker Compose
- Git
- `make`
- OpenSSL (for self-signed dev certificates)
- At least **4 GB RAM** and **20 GB free disk space** for the VM/host

---

## Step 1 — Clone the Repository

```bash
git clone https://github.com/websanta/store-laravel-react store-laravel-react
cd store-laravel-react
```

---

## Step 2 — Generate SSL Certificates (dev only)

```bash
mkdir -p infrastructure/docker/nginx/certs

openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout infrastructure/docker/nginx/certs/temp-key.pem \
  -out   infrastructure/docker/nginx/certs/temp.pem \
  -subj "/C=US/ST=State/L=City/O=Dev/CN={your-local-domain}"
```

Replace `{your-local-domain}` with the domain you intend to use locally (e.g. `store.local`).

---

## Step 3 — Configure Environment

```bash
cp .env.example .env
```

Open `.env` and fill in at minimum:

```env
APP_NAME="My Store"
APP_URL=https://{your-local-domain}

DB_DATABASE={dbname}
DB_USERNAME={username}
DB_PASSWORD={password}

REDIS_PASSWORD={redis-password}

# Optional for Stripe — use PAYMENT_DRIVER=mock to skip
PAYMENT_DRIVER=mock
STRIPE_KEY=
STRIPE_SECRET=
STRIPE_WEBHOOK_SECRET=
```

---

## Step 4 — Add Local Hostname (optional)

If you want to use a custom local domain instead of `localhost`:

```bash
echo "127.0.0.1 {your-local-domain}" | sudo tee -a /etc/hosts
```

---

## Step 5 — Make Scripts Executable

```bash
chmod +x scripts/*.sh
```

---

## Step 6 — Full Install

```bash
make install
```

This single command:

1. Creates `.env` if missing (`make setup`)
2. Builds all Docker images (`make dbuild`)
3. Starts containers (`make up`)
4. Waits for services to be healthy
5. Fixes storage permissions (`make permissions`)
6. Installs Composer dependencies (`make composer-install`)
7. Installs Livewire (`make livewire-install`)
8. Installs Laravel Breeze with React + TypeScript (`make breeze-install`)
9. Installs Filament admin panel (`make filament-install`)
10. Installs NPM packages (`make npm-install`)
11. Generates application key (`make key-generate`)
12. Installs Pest testing framework (`make pest-install`)
13. Runs database migrations (`make migrate`)
14. Creates the storage symlink (`make storage-link`)
15. Starts the Vite dev server in the background (`make start-vite`)
16. Shows Stripe setup instructions (`make stripe-setup`)

---

## Step 7 — Seed Demo Data (optional)

```bash
make seed
```

This creates demo departments, categories, users, vendors, and products.

Demo accounts created by the seeder:

| Role | Email | Password |
|---|---|---|
| Admin | `admin@example.com` | value of `APP_ADMIN_PASSWORD` in `.env` |
| Vendor | `vendor@example.com` | value of `APP_VENDOR_PASSWORD` in `.env` |
| Vendor 2 | `avendor@example.com` | value of `APP_VENDOR_PASSWORD` in `.env` |
| User | `user@example.com` | `password` |

---

## Step 8 — Create Filament Admin User

```bash
make filament-user
```

Follow the prompts to create your admin panel account.

---

## Access Points

| Service | URL |
|---|---|
| Application | `https://{your-local-domain}` |
| Admin Panel | `https://{your-local-domain}/admin` |
| Mailpit (email preview) | `http://localhost:8025` |
| pgAdmin (DB GUI) | `http://localhost:5050` |
| Vite dev server | `https://{your-local-domain}:5174` |

> **SSL warning:** Your browser will warn about the self-signed certificate. Click "Advanced → Proceed" to continue.

---

## Day-to-Day Development

### Start dev environment (HMR enabled)

```bash
make dev
```

This starts all dev containers and the Vite HMR server. Frontend changes reflect instantly in the browser.

### Run tests

```bash
make test
```

### View logs

```bash
make logs          # all containers
make logs-store    # Laravel only
make logs-node     # Vite only
```

### Stop everything

```bash
make ddown
```

---

## Troubleshooting

| Problem | Solution |
|---|---|
| Containers won't start | `make ddown && make dbuild && make up` |
| Permission errors | `make permissions` |
| Database connection fails | `docker compose -f infrastructure/docker-compose.yml restart postgres` then `make migrate` |
| Vite HMR not connecting | `make restart-vite` |
| SSL certificate error | Accept self-signed cert in browser, or regenerate certificates (Step 2) |
| Port already in use | Stop conflicting services or edit port mappings in `infrastructure/docker-compose.yml` |

---

## Next Steps

- Read [ARCHITECTURE.md](ARCHITECTURE.md) to understand the system design.
- Read [DEPLOYMENT.md](DEPLOYMENT.md) for production deployment instructions.
- Run `make help` to see all available Makefile commands.
