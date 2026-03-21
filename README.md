# 🛒 Multi-Vendor E-Commerce Marketplace

> A production-ready, full-stack multi-vendor e-commerce platform built with **Laravel 12**, **React 18 + TypeScript**, **InertiaJS**, and deployed via **Docker**.

[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?logo=php)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-12.42-FF2D20?logo=laravel)](https://laravel.com)
[![React](https://img.shields.io/badge/React-18.3-61DAFB?logo=react)](https://react.dev)
[![TypeScript](https://img.shields.io/badge/TypeScript-5.9-3178C6?logo=typescript)](https://typescriptlang.org)
[![InertiaJS](https://img.shields.io/badge/InertiaJS-2.2-9553E9)](https://inertiajs.com)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-4169E1?logo=postgresql)](https://postgresql.org)
[![Docker](https://img.shields.io/badge/Docker-29.3-2496ED?logo=docker)](https://docker.com)
[![License](https://img.shields.io/badge/License-MIT-green)](LICENSE)

---

## 📌 Table of Contents

- [Overview](#-overview)
- [Features](#-features)
- [Tech Stack](#-tech-stack)
- [Architecture](#-architecture)
- [Getting Started](#-getting-started)
- [Docker Services](#-docker-services)
- [Environment Configuration](#-environment-configuration)
- [Running Tests](#-running-tests)
- [Project Structure](#-project-structure)
- [Key Makefile Commands](#-key-makefile-commands)
- [Documentation](#-documentation)

---

## 🧭 Overview

This project is a **full-featured multi-vendor marketplace** where:

- **Customers** can browse products, manage a cart (guest or authenticated), and checkout via Stripe.
- **Vendors** can register, manage their store profile, create products with variations and images, and receive order notifications via email.
- **Admins** manage the platform through a Filament-powered admin panel: approve/reject vendors, manage departments, categories, products.

The application follows the **SPA architecture** via InertiaJS — no separate API layer needed. Laravel handles routing and data, React renders the UI, all seamlessly bridged by Inertia.

---

## ✨ Features

### 🛍️ Customer-Facing
- Product catalog with search and department filtering
- Product detail page with image carousel and variation selector (color, size, storage, etc.)
- Guest cart (cookie-based) with seamless migration to DB on login
- Stripe Checkout integration with webhook handling
- Order confirmation emails (queued via Redis)
- Email verification and full authentication flow (Breeze)
- Become a Vendor functionality in profile

### 🔧 Admin Panel (Filament)
- Vendor approval/rejection with reason and email notification
- Department and category management
- Product management with status (Draft / Published)
- Dashboard widget: pending vendor requests

### 🏪 Vendor Admin Panel (Filament)
- Vendor registration and status flow (Pending → Approved/Rejected)
- Product CRUD with rich text description, department/category linking
- Product image management (Spatie Media Library with conversions)
- Product variation types (Select / Radio / Image) with cartesian variation matrix
- Quantity and price per variation
- Order notifications by email

### ⚙️ Technical Highlights
- **InertiaJS** — monolithic, no REST API overhead
- **Spatie Media Library** — image uploads with `thumb`, `small`, `large` conversions
- **Spatie Laravel Permission** — RBAC (roles: admin, vendor, user; permissions: ApproveVendors, SellProducts, BuyProducts)
- **Stripe CLI** in Docker — webhook forwarding for local development
- **Mailpit** — zero-config email capture in dev
- **Redis queues** — async email delivery via dedicated `queue` container
- **PHPUnit** — Feature tests for auth flows and CartService

---

## 🛠 Tech Stack

| Layer | Technology | Version |
|---|---|---|
| Backend | Laravel Framework | 12.42 |
| Language | PHP | 8.4 |
| Frontend | React | 18.3 |
| Language | TypeScript | 5.9 |
| Bridge | InertiaJS | 2.2 |
| Styling | Tailwind CSS + DaisyUI | 3.4 / 5.5 |
| Build | Vite | 7.2 |
| Database | PostgreSQL | 16 |
| Cache / Queue | Redis | 7.4 |
| Auth | Laravel Breeze | 2.3 |
| Admin Panel | Filament | 3.3 |
| Media | Spatie Media Library | 11 |
| Permissions | Spatie Laravel Permission | 6 |
| Payments | Stripe PHP SDK | 19 |
| Testing | PHPUnit | 11.5 |
| Containerization | Docker | 29.3 |

---

## 🏗 Architecture

```
Browser
  │
  ▼
Nginx (SSL termination, reverse proxy)
  ├── PHP requests ──► store (PHP 8.4 / Laravel / PHP-FPM)
  │                        ├── PostgreSQL  (primary data store)
  │                        ├── Redis       (cache, sessions, queues)
  │                        └── queue       (background email jobs)
  │
  └── Vite HMR (dev) ──► node (Vite dev server)

Dev extras:
  ├── pgAdmin    (DB GUI)
  ├── Mailpit    (SMTP trap)
  └── stripe     (Stripe CLI — webhook forwarding)
```

See [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) for a detailed breakdown.

---

## 🚀 Getting Started

### Prerequisites

- Docker 29+ and Docker Compose
- Git
- `make`
- OpenSSL (for local SSL certificates)

### 1. Clone the Repository

```bash
git clone https://github.com/websanta/store-laravel-react store-laravel-react
cd store-laravel-react
```

### 2. Generate SSL Certificates (dev only)

```bash
mkdir -p infrastructure/docker/nginx/certs
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout infrastructure/docker/nginx/certs/temp-key.pem \
  -out  infrastructure/docker/nginx/certs/temp.pem \
  -subj "/C=US/ST=State/L=City/O=Dev/CN=localhost"
```

### 3. Configure Environment

```bash
cp .env.example .env
# Edit .env — fill in DB credentials, Redis password, Stripe keys, etc.
```

### 4. Add Local Hostname (optional, dev)

```bash
echo "127.0.0.1 {your-local-domain}" | sudo tee -a /etc/hosts
```

### 5. Start the Application (dev mode)

```bash
make install   # builds images, installs deps, runs migrations, starts Vite
make seed      # optional: seed demo data
```

The app is now available at `https://{your-local-domain}` (accept the self-signed certificate in your browser).

> For detailed step-by-step instructions, see [docs/QUICKSTART.md](docs/QUICKSTART.md).

---

## 🐳 Docker Services

### Development (`--profile dev`)

| Container | Role | Port |
|---|---|---|
| `store` | Laravel app (PHP-FPM 8.4) |
| `nginx` | Web server / reverse proxy | 80, 443 |
| `node` | Vite dev server (HMR) | 5174 |
| `postgres` | PostgreSQL 16 | 5432 |
| `pgadmin` | Database GUI | 5050 |
| `redis` | Cache & queue backend | 6379 |
| `queue` | Laravel queue worker | — |
| `mailpit` | SMTP trap / email preview | 8025 (UI), 1025 (SMTP) |
| `stripe` | Stripe CLI webhook forwarder | — |

### Production (`--profile prod`)

| Container | Role |
|---|---|
| `store` | Laravel app |
| `nginx` | Web server |
| `node` | Asset build |
| `postgres` | Database |
| `redis` | Cache & queues |
| `queue` | Queue worker |

---

## ⚙️ Environment Configuration

The project ships with two environment templates:

| File | Purpose |
|---|---|
| `.env.example` | Development / production environment template |
| `.env.testing.example` | Test environment template (uses separate DB) |

Key variables to configure:

```env
APP_URL=https://{your-domain}

DB_HOST=postgres
DB_DATABASE={dbname}
DB_USERNAME={username}
DB_PASSWORD={password}

REDIS_HOST=redis
REDIS_PASSWORD={redis-password}

MAIL_HOST=mailpit    # dev: captured by Mailpit
MAIL_PORT=1025

PAYMENT_DRIVER=stripe   # or: mock
STRIPE_KEY={pk_test_...}
STRIPE_SECRET={sk_test_...}
STRIPE_WEBHOOK_SECRET={whsec_...}
```

All sensitive values must be set in `.env` — **never commit**.

---

## 🧪 Running Tests

The project includes **Feature tests** for authentication flows and the `CartService`.

```bash
# Run all tests
make test

# Run a specific test by name
make test-filter FILTER="CartServiceTest"

# Run feature tests only
make test-feature
```

Test configuration lives in `phpunit.xml`. Tests use a dedicated PostgreSQL database (`store_test`).

**Test areas covered:**

| Suite | Tests |
|---|---|
| `Auth` | Registration, Login, Email Verification, Password Reset, Password Update, Password Confirmation |
| `Services` | `CartService` — add, update, remove, totals, guest→auth migration |
| `Profile` | View, update, delete account |

---

## 📁 Project Structure

```
store-laravel-react/
├── app/
│   ├── Enums/              # OrderStatus, ProductStatus, VendorStatus, Roles, Permissions
│   ├── Events/             # OrderPaid
│   ├── Filament/           # Admin panel resources (Departments, Products, Vendors)
│   ├── Http/
│   │   ├── Controllers/    # Web + Auth controllers
│   │   ├── Middleware/     # HandleInertiaRequests
│   │   └── Resources/      # API resources (Product, Order, User, Vendor, Department)
│   ├── Listeners/          # SendCustomerOrderConfirmation, SendVendorNewOrderNotification
│   ├── Mail/               # CheckoutCompleted, NewOrderMail, VendorStatusChanged
│   ├── Models/             # User, Product, Vendor, Order, OrderItem, CartItem, …
│   ├── Providers/          # AppServiceProvider, AdminPanelProvider
│   └── Services/           # CartService, CheckoutService, StripeService, CustomPathGenerator
├── database/
│   ├── factories/          # Model factories for testing
│   ├── migrations/         # 13 migrations (users → orders)
│   └── seeders/            # Roles, Users, Departments, Categories, Products
├── docs/                   # Architecture, Quickstart, Deployment docs
├── infrastructure/
│   ├── deploy/             # GitHub Actions CI workflow
│   └── docker/             # Dockerfiles + configs (nginx, node, php-fpm, stripe)
│       └── docker-compose.yml
├── resources/
│   ├── js/                 # React + TypeScript
│   │   ├── Components/     # App (Navbar, Cart, ProductItem) + Core (UI primitives)
│   │   ├── Layouts/        # AuthenticatedLayout, GuestLayout
│   │   ├── Pages/          # Auth, Cart, Department, Product, Profile, Stripe, Vendor
│   │   └── types/          # TypeScript type definitions
│   └── views/              # Blade entry point + mail templates
├── routes/
│   ├── web.php             # All application routes
│   └── auth.php            # Authentication routes
├── scripts/                # Shell scripts (init, permissions, seed)
├── tests/
│   └── Feature/            # Auth + CartService feature tests
├── .env.example
├── .env.testing.example
├── Makefile                # 50+ dev, test and deploy commands
├── phpunit.xml
└── vite.config.js
```

---

## 🔑 Key Makefile Commands

```bash
# Setup & installation
make install          # Full setup: build → deps → migrate → Vite
make setup            # Create .env from .env.example

# Docker lifecycle
make up               # Start containers (default profile)
make up-dev           # Start dev profile + Vite HMR
make up-prod          # Start prod profile
make ddown            # Stop dev containers
make ps               # Show running containers
make logs             # Tail all logs
make shell            # Shell into store (app) container

# Development
make dev              # Start dev environment with HMR
make start-vite       # Start Vite dev server (background)
make fbuild           # Build frontend assets for production

# Laravel
make migrate          # Run migrations
make migrate-fresh    # Drop all tables and re-migrate
make seed             # Seed the database
make artisan CMD="…"  # Run any artisan command
make cache-clear      # Clear config, route, view, app cache
make optimize         # Cache config + routes + views

# Testing
make test             # Run all tests
make test-coverage    # Tests with code coverage
make test-feature     # Feature tests only
make test-filter FILTER="Name"  # Run specific test

# Database
make db-backup        # Export DB to .sql file
make db-restore FILE=backup.sql

# Stripe
make stripe-logs      # Stripe CLI container logs
make stripe-trigger EVENT=checkout.session.completed

# Help
make help             # List all available commands
```

---

## 📖 Documentation

| Document | Description |
|---|---|
| [docs/QUICKSTART.md](docs/QUICKSTART.md) | Quick-start guide (up and run in minutes) |
| [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md) | Step-by-step deployment walkthrough |
| [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) | System architecture, data flow, DB schema |
| [docs/DOCKERFILES_CHECKLIST.md](docs/DOCKERFILES_CHECKLIST.md) | Docker configuration reference |
| [docs/CHECKLIST.md](docs/CHECKLIST.md) | Full environment setup checklist |

---

## 📝 License

This project is open-source and available under the [MIT License](LICENSE).
