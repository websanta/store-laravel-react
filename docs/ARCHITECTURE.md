# Multi-Vendor Marketplace — Architecture Documentation

**Project:** Laravel 12 + React SPA
**Database:** PostgreSQL
**Cache / Queue:** Redis
**Frontend:** React + Vite + Tailwind
**Backend:** Laravel API (PHP 8.3, Composer)
**Web server:** Nginx
**Deployment:** Docker + Docker Compose

---

## 1️⃣ Context Diagram (C4 Level 1)

Describes the users, external systems, and high-level system interactions.

```mermaid
C4Context
    title Multi-Vendor Marketplace — System Context

    Person(user, "End User", "Browses products, orders, pays")
    Person(vendor, "Vendor", "Manages own products, receives payouts")
    Person(admin, "Admin", "Manages site, users, categories, products")

    System(system, "Store Laravel + React", "Multi-vendor marketplace")

    user --> system : "Browse products, place orders"
    vendor --> system : "Add / manage products"
    admin --> system : "Manage users, categories, products"

    System_Ext(stripe, "Stripe Payment Gateway", "Processes payments")
    System_Ext(mail, "SMTP / Mail Service", "Sends emails")

    system --> stripe : "Initiates payment transactions"
    system --> mail : "Sends order and notification emails"

C4Container
    title Store Laravel + React — Container Diagram

    System_Boundary(system, "Store Laravel + React") {

        Container(backend, "Laravel API", "PHP 8.3 + PostgreSQL", "REST API for frontend and admin")
        Container(frontend, "React SPA", "React + Vite + Tailwind", "Single Page App for users and vendors")
        Container(nginx, "Nginx", "Nginx", "Serves SPA & reverse-proxies API requests")
        Container(postgres, "PostgreSQL", "Database", "Stores users, orders, products, categories")
        Container(redis, "Redis", "Cache / Queues", "Caches data and handles job queues")
    }

    frontend --> nginx : "HTTP requests"
    frontend --> backend : "REST API calls"
    backend --> postgres : "Read / write"
    backend --> redis : "Cache, queues"

C4Component
    title Laravel Backend — Components

    Container(backend, "Laravel API", "PHP 8.3 + PostgreSQL") {

        Component(auth, "Auth & Roles", "Handles login, registration, roles & permissions")
        Component(products, "Product Management", "CRUD, variations, categories")
        Component(cart, "Shopping Cart", "Handles adding/removing items")
        Component(orders, "Orders & Checkout", "Checkout process, Stripe integration")
        Component(vendorPanel, "Vendor Panel", "Vendor dashboard, payouts")
        Component(notifications, "Notifications", "Emails, system notifications")
        Component(api, "API Layer", "Exposes endpoints for frontend and admin")
    }

    frontend --> api : "API calls"
    auth --> postgres : "Users & roles"
    products --> postgres : "Product & category data"
    orders --> postgres : "Orders & payments"
    notifications --> mail : "Send emails"

flowchart TB
    subgraph docker [Docker Environment]
        direction TB
        nginx["Nginx (80/443)"] --> frontend["React SPA"]
        nginx --> backend["Laravel API (PHP-FPM)"]
        backend --> postgres["PostgreSQL DB"]
        backend --> redis["Redis Cache / Queue"]
    end

    user["User / Vendor / Admin"] --> nginx
