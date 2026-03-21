# System Architecture

## Overview

This document describes the architecture of the **Multi-Vendor E-Commerce Marketplace** — a Laravel 12 + React 18 + InertiaJS application deployed in Docker.

The application follows a **monolithic app** pattern via InertiaJS: Laravel owns routing, data fetching, and business logic; React renders the UI. There is no standalone REST API — Inertia acts as a protocol bridge, eliminating the need for a separate frontend/backend split.

---

## High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                        Docker Network                       │
│                                                             │
│  ┌──────────────┐    ┌─────────────────────────────────┐    │
│  │    nginx     │    │         store (PHP-FPM)         │    │
│  │  :80 / :443  │───►│  Laravel 12 / InertiaJS / React │    │
│  │  (SSL, proxy)│    │           PHP 8.4               │    │
│  └──────┬───────┘    └────────────┬────────────────────┘    │
│         │                         │                         │
│         │ (Vite HMR, dev only)    ├─── PostgreSQL 16        │
│         │                         │       (primary DB)      │
│  ┌──────▼───────┐                 ├─── Redis 7.4            │
│  │     node     │                 │       (cache, sessions, │
│  │  Vite :5174  │                 │        queue backend)   │
│  └──────────────┘                 │                         │
│                                   └─── queue (worker)       │
│                                           (email jobs)      │
│                                                             │
│  Dev-only:  pgAdmin · Mailpit · Stripe CLI                  │
└─────────────────────────────────────────────────────────────┘
```

---

## Docker Services

### Development profile

| Service | Image | Role |
|---|---|---|
| `store` | `php:8.4-fpm-alpine` (custom) | Laravel app — PHP-FPM runtime |
| `nginx` | `nginx:alpine` (custom) | Web server, SSL termination, proxy |
| `node` | `node:20-alpine` (custom) | Vite dev server + HMR |
| `postgres` | `postgres:16-alpine` | Primary relational database |
| `pgadmin` | `dpage/pgadmin4` | Web-based PostgreSQL GUI |
| `redis` | `redis:7-alpine` | Cache, sessions, queue backend |
| `queue` | same as `store` | `php artisan queue:work redis` |
| `mailpit` | `axllent/mailpit` | SMTP trap for local email testing |
| `stripe` | `stripe/stripe-cli` (custom) | Webhook forwarding to local app |

### Production profile

`store`, `nginx`, `node`, `postgres`, `redis`, `queue` — development tooling excluded.

---

## Request Lifecycle

### Browser → Response

```
Browser request
    ▼
Nginx (:443)  ──────────────────────── static file? serve directly
    │
    ▼ PHP request
store (PHP-FPM / Laravel)
    │
    ├── Middleware stack (auth, Inertia, CSRF …)
    │
    ├── Router → Controller → Service → Model
    │                                     │
    │                              PostgreSQL / Redis
    │
    └── InertiaJS response (JSON or full HTML on first visit)
            ▼
        React renders component
            ▼
        Browser (SPA navigation from here on)
```

### Vite HMR in Development

```
Browser ◄──── WebSocket ────► Nginx (:443 /vite-hmr proxy)
                                        ▼
                               node (Vite dev server :5174)
                                        ▼
                               File watcher → HMR update
```

---

## Application Layers

### Backend (Laravel)

```
app/
├── Enums/          Business-rule enumerations
│   ├── OrderStatusEnum       draft | processing | paid | shipped | delivered | cancelled
│   ├── ProductStatusEnum     draft | published
│   ├── VendorStatusEnum      pending | approved | rejected
│   ├── RolesEnum             admin | vendor | user
│   ├── PermissionsEnum       ApproveVendors | SellProducts | BuyProducts
│   └── ProductVariationTypeEnum  Select | Radio | Image
│
├── Models/         Eloquent models (PostgreSQL)
│   User, Vendor, Product, ProductVariation, VariationTypes,
│   VariationTypeOption, Category, Department,
│   Order, OrderItem, CartItem
│
├── Services/       Business logic
│   ├── CartService         guest (cookie) and auth (DB) cart management
│   ├── CheckoutService     order + Stripe line-item creation
│   ├── StripeService       webhook handlers (session.completed, charge.updated)
│   └── CustomPathGenerator hashed media storage paths
│
├── Http/
│   ├── Controllers/        Web controllers + full Auth suite (Breeze)
│   ├── Middleware/         HandleInertiaRequests (shared props)
│   └── Resources/          JSON API resources for Inertia props
│
├── Filament/       Admin panel resources
│   ├── Resources/  Department, Product (+ sub-pages), Vendor
│   └── Widgets/    PendingVendors dashboard widget
│
├── Events/         OrderPaid
├── Listeners/      SendCustomerOrderConfirmation, SendVendorNewOrderNotification
└── Mail/           CheckoutCompleted, NewOrderMail, VendorStatusChanged
```

### Frontend (React + TypeScript)

```
resources/js/
├── Components/
│   ├── App/    Navbar, CartItem, MiniCartDropdown, ProductItem, ApplicationLogo
│   └── Core/   Carousel, TextInput, Modal, Dropdown, buttons, InputLabel …
│
├── Layouts/
│   ├── AuthenticatedLayout   toast notifications, error/success banners
│   └── GuestLayout
│
├── Pages/
│   ├── Auth/       Login, Register, ForgotPassword, ResetPassword, VerifyEmail, ConfirmPassword
│   ├── Cart/       Index (grouped by vendor, per-vendor checkout)
│   ├── Department/ Index (filtered product listing)
│   ├── Product/    Show (carousel, variation picker, add-to-cart)
│   ├── Profile/    Edit (profile info, password, delete account, vendor details)
│   ├── Stripe/     Success, Failure
│   ├── Vendor/     Profile (store page with product grid)
│   ├── Dashboard   (authenticated home)
│   └── Home        (public product listing + search)
│
└── types/          TypeScript interfaces: User, Product, CartItem, Order, Vendor …
```

---

## Database Schema

### Core tables

| Table | Description |
|---|---|
| `users` | Authentication, roles via `model_has_roles` |
| `vendors` | Store profile, status, cover image, PK = `user_id` |
| `departments` | Top-level product categories |
| `categories` | Subcategories (belong to department, self-referencing parent) |
| `products` | Product listings with soft-delete |
| `variation_types` | Product attribute types (Color, Size …) |
| `variation_type_options` | Concrete options per type |
| `product_variations` | Price/qty per option combination (JSON array of option IDs) |
| `media` | Spatie Media Library — images for products and variation options |
| `cart_items` | Shopping cart rows (user-scoped, JSON option IDs) |
| `orders` | One order per vendor per checkout session |
| `order_items` | Line items with variation snapshot |
| `sessions` / `cache` / `jobs` | Laravel defaults |
| `roles` / `permissions` / pivots | Spatie Permission tables |

### Key relationships

```
User ──< Vendor (1:1, user_id PK)
User ──< CartItem
User ──< Order (as buyer)
User ──< Order (as vendor_user, seller)

Product >── Vendor (via created_by → users.id)
Product >── Department
Product >── Category
Product ──< VariationTypes ──< VariationTypeOptions
Product ──< ProductVariations
Product ──< Media (collection: images)
VariationTypeOption ──< Media (collection: images)

Order >── User (buyer)
Order >── User (vendor_user_id)
Order ──< OrderItem >── Product
```

---

## Authentication & Authorization

Authentication is provided by **Laravel Breeze** (React variant). After login:

- Admins and Vendors are redirected to the Filament admin panel.
- Regular users are redirected to the dashboard.

Cart items stored in cookies are **automatically migrated to the database** on login.

Authorization uses **Spatie Laravel Permission** (RBAC):

| Role | Permissions |
|---|---|
| `user` | BuyProducts |
| `vendor` | SellProducts, BuyProducts |
| `admin` | ApproveVendors, SellProducts, BuyProducts |

The Filament admin panel access is guarded by `role:admin|vendor` middleware.

---

## Payment Flow (Stripe)

```
Cart → POST /cart/checkout
    ▼
CheckoutService::checkout()
    Creates Order records (status: draft)
    Builds Stripe line items
    ▼
Stripe\Checkout\Session::create()
    ▼
User redirected to Stripe Hosted Checkout
    ▼
Stripe webhook: checkout.session.completed
    ▼
StripeService::handleCheckoutSessionCompleted()
    Orders marked as paid
    Product quantities decremented
    ▼
Stripe webhook: charge.updated
    ▼
StripeService::handleChargeUpdated()
    Commission splits calculated
    ▼
OrderPaid event dispatched
    ▼
Listeners (queued via Redis):
    SendCustomerOrderConfirmation  → CheckoutCompleted mail
    SendVendorNewOrderNotification → NewOrderMail per vendor
```

A `mock` payment driver is also supported (`PAYMENT_DRIVER=mock`) for development without Stripe credentials.

---

## Queue Architecture

Email notifications are dispatched asynchronously via **Redis queues**:

- Queue connection: `redis`
- Queue name: `emails`
- Worker: dedicated `queue` Docker container running `php artisan queue:work redis --queue=default,emails`

This ensures that the HTTP response is returned immediately to the user, and emails are delivered in the background.

---

## Media Library

Product images and variation option images are managed by **Spatie Laravel Media Library**:

- Storage disk: `public`
- Path generator: `CustomPathGenerator` (MD5 hash of media ID + app key)
- Conversions registered on `Product` and `VariationTypeOption`:
  - `thumb` → 100px wide
  - `small` → 480px wide
  - `large` → 1200px wide

---
