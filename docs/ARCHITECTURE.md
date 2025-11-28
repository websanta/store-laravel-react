# System Architecture

## Overview

This document describes the architecture of the Multi-Vendor E-Commerce Marketplace application built with Laravel and React.

## Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                         Windows 11 Host                          │
│  ┌────────────────────────────────────────────────────────┐    │
│  │              VS Code with Remote-SSH                    │    │
│  └────────────────────────────────────────────────────────┘    │
└──────────────────────────┬──────────────────────────────────────┘
                           │ SSH Connection
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│                    Linux Mint 22 VM (VMware)                     │
│                                                                   │
│  ┌────────────────────────────────────────────────────────┐    │
│  │              Docker Container Network                   │    │
│  │                                                          │    │
│  │  ┌──────────┐  ┌──────────┐  ┌──────────┐            │    │
│  │  │  Nginx   │  │   Node   │  │  Store   │            │    │
│  │  │  :80/443 │  │  :5173   │  │ (Laravel)│            │    │
│  │  │          │  │  (Vite)  │  │   :9000  │            │    │
│  │  └────┬─────┘  └────┬─────┘  └────┬─────┘            │    │
│  │       │             │              │                   │    │
│  │       └─────────────┴──────────────┘                   │    │
│  │                     │                                   │    │
│  │       ┌─────────────┴──────────────┐                   │    │
│  │       │                            │                   │    │
│  │  ┌────▼─────┐  ┌─────────┐  ┌────▼─────┐             │    │
│  │  │PostgreSQL│  │ pgAdmin │  │  Redis   │             │    │
│  │  │  :5432   │  │  :5050  │  │  :6379   │             │    │
│  │  └──────────┘  └─────────┘  └──────────┘             │    │
│  │                                                          │    │
│  └────────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────────┘
```

## Components

### 1. Frontend Layer

#### React Application
- **Framework**: React 18
- **Build Tool**: Vite
- **Location**: `resources/js/`
- **Features**:
  - Component-based architecture
  - React Router for navigation
  - State management (Context API / Redux)
  - Hot Module Replacement (HMR) via Vite
  - TypeScript support (optional)

#### UI Components
- **Authentication**: Laravel Breeze React components
- **User Interface**: Custom React components
- **Admin Panel**: Filament (separate blade-based UI)

### 2. Backend Layer

#### Laravel Application
- **Version**: 12.39.0
- **PHP**: 8.3
- **Architecture**: MVC (Model-View-Controller)
- **Key Features**:
  - RESTful API endpoints
  - Authentication via Laravel Breeze
  - Admin panel via Filament
  - Queue management
  - Event-driven architecture

#### API Structure
```
/api/v1/
├── /auth           # Authentication endpoints
├── /products       # Product CRUD operations
├── /vendors        # Vendor management
├── /orders         # Order processing
├── /users          # User management
└── /admin          # Admin-specific endpoints
```

### 3. Data Layer

#### PostgreSQL Database
- **Version**: 16
- **Purpose**: Primary relational database
- **Schema Design**:
  - Users and authentication
  - Products and categories
  - Vendors and stores
  - Orders and transactions
  - Reviews and ratings

#### Redis Cache
- **Version**: 7
- **Purpose**:
  - Session storage
  - Cache layer
  - Queue backend
  - Rate limiting

### 4. Infrastructure Layer

#### Docker Containers

##### Nginx Container
- **Base Image**: `nginx:alpine`
- **Purpose**: Web server and reverse proxy
- **Configuration**:
  - SSL/TLS termination
  - Static file serving
  - PHP-FPM proxying
  - Vite HMR proxying

##### PHP-FPM Container (store)
- **Base Image**: `php:8.3-fpm-alpine`
- **Purpose**: PHP application runtime
- **Extensions**:
  - PDO, PostgreSQL
  - Redis
  - GD, Zip, Mbstring
  - OPcache

##### Node Container
- **Base Image**: `node:20-alpine`
- **Purpose**: Frontend build and development
- **Responsibilities**:
  - Vite development server
  - Asset compilation
  - NPM package management

##### PostgreSQL Container
- **Base Image**: `postgres:16-alpine`
- **Purpose**: Primary database
- **Configuration**:
  - Persistent volume storage
  - Health checks
  - Automatic backups

##### Redis Container
- **Base Image**: `redis:7-alpine`
- **Purpose**: Caching and session storage
- **Configuration**:
  - AOF persistence
  - Password protection
  - Health checks

##### pgAdmin Container
- **Base Image**: `dpage/pgadmin4`
- **Purpose**: Database administration GUI
- **Access**: http://localhost:5050

## Data Flow

### Request Flow (Client → Server)

```
Browser (https://vmmint22.local)
    ↓
Nginx Container (:443)
    ↓
┌───────────────────────┐
│  Is it a PHP request? │
└───────────┬───────────┘
            │
    ┌───────┴────────┐
    │ YES            │ NO
    ↓                ↓
PHP-FPM         Static Files
(Laravel)       (Nginx serves directly)
    ↓
┌───────────────────────┐
│ Database/Cache needed?│
└───────────┬───────────┘
            │
    ┌───────┴────────┐
    │ YES            │ NO
    ↓                ↓
PostgreSQL/Redis   Response
    ↓                ↓
Response ←──────────┘
    ↓
Nginx
    ↓
Browser
```

### Development Flow (Vite HMR)

```
Browser
    ↓
Vite Client (injected in HTML)
    ↓
WebSocket Connection
    ↓
Nginx (:443/vite-hmr)
    ↓
Proxy to Node Container (:5173)
    ↓
Vite Dev Server
    ↓
File Watcher
    ↓
Hot Module Replacement
    ↓
Browser (updates without refresh)
```

## Security Architecture

### SSL/TLS Configuration
- Self-signed certificates for local development
- TLS 1.2+ protocols
- Strong cipher suites
- HTTPS redirect from HTTP

### Application Security
- CSRF protection (Laravel)
- XSS prevention
- SQL injection prevention (PDO prepared statements)
- Rate limiting (Redis-backed)
- Session security (secure, httponly, samesite cookies)

### Docker Security
- Non-root user for PHP-FPM
- Read-only containers where possible
- Secret management via environment variables
- Network isolation via Docker networks

## Scalability Considerations

### Horizontal Scaling
- Stateless application design
- Session storage in Redis (shareable across instances)
- Database connection pooling
- Load balancer ready (Nginx configuration)

### Caching Strategy
- Application cache (Redis)
- OPcache for PHP bytecode
- Browser caching for static assets
- Database query caching

### Performance Optimization
- Lazy loading for React components
- Code splitting with Vite
- Asset minification and compression
- Database indexing strategy
- N+1 query prevention (Eloquent eager loading)

## Development Workflow

### Local Development
```
1. Code changes in VS Code (Remote-SSH)
2. Vite watches file changes
3. HMR updates browser instantly
4. Laravel detects changes
5. Automatic reloading for PHP changes
```

### Testing Strategy
- **Unit Tests**: PHPUnit for Laravel
- **Feature Tests**: Laravel HTTP tests
- **Frontend Tests**: Vitest/Jest for React
- **Integration Tests**: API endpoint tests
- **E2E Tests**: Playwright/Cypress (future)

### Deployment Pipeline
```
Local Development
    ↓
Git Commit
    ↓
GitHub Repository
    ↓
CI/CD Pipeline (GitHub Actions)
    ↓
Automated Tests
    ↓
Build Docker Images
    ↓
Push to Registry
    ↓
Deploy to Production
```

## Database Schema (High-Level)

```sql
-- Core tables
users
├── id
├── name
├── email
├── password
└── role (admin, vendor, customer)

vendors
├── id
├── user_id (FK)
├── store_name
├── description
└── status

products
├── id
├── vendor_id (FK)
├── category_id (FK)
├── name
├── description
├── price
└── stock

orders
├── id
├── user_id (FK)
├── status
├── total
└── created_at

order_items
├── id
├── order_id (FK)
├── product_id (FK)
├── quantity
└── price

categories
├── id
├── name
└── parent_id (self-referencing)

reviews
├── id
├── product_id (FK)
├── user_id (FK)
├── rating
└── comment
```

## File Organization

### Laravel Structure
```
/store-laravel-react/
├── app/
│   ├── Http/Controllers/     # API and web controllers
│   ├── Models/               # Eloquent models
│   ├── Services/             # Business logic
│   ├── Repositories/         # Data access layer
│   └── Filament/             # Admin panel resources
├── resources/
│   ├── js/                   # React application
│   │   ├── Components/       # Reusable components
│   │   ├── Pages/            # Page components
│   │   ├── Layouts/          # Layout components
│   │   └── App.jsx           # Main React app
│   └── views/                # Blade templates
├── routes/
│   ├── api.php               # API routes
│   ├── web.php               # Web routes
│   └── channels.php          # Broadcasting routes
└── database/
    ├── migrations/           # Database migrations
    ├── seeders/              # Database seeders
    └── factories/            # Model factories
```

## Technology Stack Summary

| Layer        | Technology        | Version | Purpose                    |
|--------------|-------------------|---------|----------------------------|
| Frontend     | React             | 18      | UI framework               |
| Build Tool   | Vite              | 5       | Fast dev server & bundler  |
| Backend      | Laravel           | 12.39   | API and business logic     |
| Language     | PHP               | 8.3     | Server-side language       |
| Database     | PostgreSQL        | 16      | Primary data store         |
| Cache        | Redis             | 7       | Caching & sessions         |
| Web Server   | Nginx             | Alpine  | Reverse proxy              |
| Runtime      | Node.js           | 20      | Frontend build             |
| Container    | Docker            | Latest  | Application containerization|
| Orchestration| Docker Compose    | 3.8     | Multi-container management |
| Auth         | Laravel Breeze    | Latest  | Authentication scaffolding |
| Admin        | Filament          | 3       | Admin panel framework      |

## Monitoring & Logging

### Application Logs
- Laravel logs: `/storage/logs/laravel.log`
- Nginx access logs: `/var/log/nginx/access.log`
- Nginx error logs: `/var/log/nginx/error.log`
- PHP-FPM logs: Container stdout/stderr

### Health Checks
- Database: `pg_isready` check every 10s
- Redis: `redis-cli ping` every 10s
- PHP-FPM: `php-fpm -t` every 30s
- Nginx: `nginx -t` every 30s

## Future Enhancements

1. **Message Queue**: Laravel Horizon for queue management
2. **Search**: Elasticsearch integration for product search
3. **CDN**: CloudFlare or similar for asset delivery
4. **Storage**: S3-compatible object storage for uploads
5. **Monitoring**: Prometheus + Grafana for metrics
6. **CI/CD**: Automated testing and deployment pipeline
7. **Microservices**: Potential split of payment/notification services

---

**Document Version**: 1.0
**Last Updated**: 2025
**Author**: WebSanta