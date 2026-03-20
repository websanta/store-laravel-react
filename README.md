# Multi-Vendor E-Commerce Marketplace

A modern, production-ready e-commerce marketplace built with **Laravel 12**, **React 18**, and **TypeScript**. Features multi-vendor support, comprehensive admin panel, email testing, debugging tools, and complete Docker development environment.

## 🚀 Tech Stack

### Backend
- **Framework:** Laravel 12.39.0
- **Language:** PHP 8.3
- **Database:** PostgreSQL 16 (LTS)
- **Cache/Sessions:** Redis 7 (LTS)
- **Authentication:** Laravel Breeze (React + TypeScript)
- **Admin Panel:** Filament 3
- **Testing:** Pest (PHPUnit wrapper)

### Frontend
- **Framework:** React 18
- **Language:** TypeScript (strict mode)
- **Build Tool:** Vite 5
- **Styling:** Tailwind CSS 3
- **State Management:** Context API
- **Code Quality:** ESLint + Prettier

### Infrastructure
- **Containerization:** Docker & Docker Compose
- **Web Server:** Nginx (Alpine)
- **Email Testing:** Mailpit
- **Database GUI:** pgAdmin 4
- **Debugging:** Xdebug 3

## 📋 Prerequisites

- ✅ Docker & Docker Compose
- ✅ Git
- ✅ Linux Mint 22 (or similar Linux distribution)
- ✅ VMware Workstation (for development VM)
- ✅ VS Code with Remote-SSH extension
- ✅ At least 4GB RAM allocated to VM
- ✅ 20GB free disk space

## ⚡ Quick Start

For detailed setup, see [QUICKSTART.md](QUICKSTART.md).

```bash
# 1. Clone repository
cd /home/websanta/docker_projects/
git clone <your-repo> store-laravel-react
cd store-laravel-react

# 2. Generate SSL certificates
mkdir -p infrastructure/docker/nginx/certs
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout infrastructure/docker/nginx/certs/temp-key.pem \
  -out infrastructure/docker/nginx/certs/temp.pem \
  -subj "/C=US/ST=State/L=City/O=Dev/CN=vmmint22.local"

# 3. Configure hosts
echo "127.0.0.1 vmmint22.local" | sudo tee -a /etc/hosts

# 4. Initialize and install
chmod +x scripts/*.sh
./scripts/init-project.sh
make install

# 5. Install Breeze & Filament
make breeze-install
make filament-install
make filament-user
```

**Done!** Access at https://vmmint22.local

## 🌐 Access Points

| Service | URL | Credentials |
|---------|-----|-------------|
| **Application** | https://vmmint22.local | - |
| **Admin Panel** | https://vmmint22.local/admin | Created via `make filament-user` |
| **Mailpit UI** | http://localhost:8025 | - |
| **pgAdmin** | http://localhost:5050 | admin@store.com / admin |
| **Vite Dev Server** | http://localhost:5173 | - |

## 🎯 Key Features

### Development Environment
- ✅ Full Docker containerization (7 services)
- ✅ Hot Module Replacement (HMR) via Vite
- ✅ Xdebug 3 for PHP debugging
- ✅ TypeScript strict mode
- ✅ ESLint + Prettier code formatting
- ✅ Automatic SSL certificates
- ✅ Health checks for all containers

### Backend Features
- ✅ Laravel 12 with latest features
- ✅ RESTful API structure
- ✅ PostgreSQL with migrations
- ✅ Redis caching and sessions
- ✅ Queue system ready
- ✅ Event-driven architecture
- ✅ Pest testing framework

### Frontend Features
- ✅ React 18 with TypeScript
- ✅ Component-based architecture
- ✅ Context API for state management
- ✅ Path aliases (@components, @pages)
- ✅ Tailwind CSS utility-first styling
- ✅ Laravel Breeze authentication UI

### DevOps
- ✅ 40+ Makefile commands
- ✅ Automated initialization scripts
- ✅ GitHub Actions CI/CD ready
- ✅ Database backup/restore
- ✅ Permission management
- ✅ Container health monitoring

## 📚 Documentation

- **[QUICKSTART.md](QUICKSTART.md)** - Get started in 10 minutes
- **[DEPLOYMENT.md](DEPLOYMENT.md)** - Complete deployment guide (12 parts)
- **[ARCHITECTURE.md](docs/ARCHITECTURE.md)** - System architecture details
- **[CHECKLIST.md](CHECKLIST.md)** - 142-point setup checklist
- **[DOCKERFILES_CHECKLIST.md](DOCKERFILES_CHECKLIST.md)** - All Dockerfiles reference

## 🛠️ Common Commands

### Container Management
```bash
make up              # Start all containers
make down            # Stop all containers
make restart         # Restart containers
make ps              # Show container status
make logs            # View all logs
make logs-store      # View Laravel logs
make shell           # Access container shell
```

### Development
```bash
make dev             # Start dev environment (Vite HMR)
make test            # Run Pest tests
make test-coverage   # Run tests with coverage
make lint            # Run ESLint
make format          # Format code with Prettier
make typescript-check # Check TypeScript types
```

### Laravel
```bash
make migrate         # Run migrations
make seed            # Seed database
make cache-clear     # Clear all caches
make optimize        # Optimize application
make artisan CMD="..." # Run artisan command
```

### Database
```bash
make db-backup       # Backup database
make db-restore FILE=backup.sql # Restore database
```

### Debugging
```bash
make xdebug-enable   # Enable Xdebug
make xdebug-disable  # Disable Xdebug (performance)
make xdebug-status   # Check Xdebug status
```

### Maintenance
```bash
make permissions     # Fix permissions
make clean           # Clean everything (⚠️ deletes data!)
make info            # Show system info
```

**See all commands:** `make help`

## 📁 Project Structure

```
/store-laravel-react/
├── app/                      # Laravel application
│   ├── Http/Controllers/     # API and web controllers
│   ├── Models/               # Eloquent models
│   ├── Services/             # Business logic
│   └── Filament/             # Admin panel resources
├── resources/
│   ├── js/                   # React + TypeScript
│   │   ├── Components/       # Reusable components
│   │   ├── Pages/            # Page components
│   │   ├── Layouts/          # Layout components
│   │   └── types/            # TypeScript definitions
│   ├── css/                  # Tailwind CSS
│   └── views/                # Blade templates
├── tests/                    # Pest tests
│   ├── Feature/              # Feature tests
│   └── Unit/                 # Unit tests
├── infrastructure/
│   ├── docker/               # Docker configurations
│   │   ├── nginx/            # Web server
│   │   ├── node/             # Vite server
│   │   └── php-fpm/          # PHP runtime
│   └── docker-compose.yml    # Orchestration
├── scripts/                  # Automation scripts
│   ├── init-project.sh       # Project initialization
│   ├── setup-permissions.sh  # Permission management
│   └── seed-database.sh      # Database seeding
├── .vscode/                  # VS Code configuration
└── docs/                     # Documentation
```

## 🔧 Configuration Files

### Environment
- `.env.example` - Environment template
- `.env` - Your local configuration (auto-generated)

### TypeScript
- `tsconfig.json` - TypeScript configuration
- `tsconfig.node.json` - Node/Vite config
- `vite.config.ts` - Vite build configuration

### Code Quality
- `.eslintrc.json` - ESLint rules
- `.prettierrc` - Code formatting rules
- `package.json` - NPM scripts and dependencies

### Docker
- `infrastructure/docker-compose.yml` - Services orchestration
- `infrastructure/docker/*/Dockerfile` - Container definitions
- `infrastructure/docker/*/php.ini` - PHP configuration
- `infrastructure/docker/*/xdebug.ini` - Debugging config

## 🧪 Testing

### Backend Testing (Pest)
```bash
# Run all tests
make test

# Run with coverage
make test-coverage

# Run specific test
make test-filter FILTER="ProductTest"

# Parallel execution
make test-parallel
```

### Frontend Testing
```bash
# Type checking
make typescript-check

# Linting
make lint
make lint-fix

# Code formatting
make format
```

## 🐛 Debugging

### PHP (Xdebug)
1. Xdebug is enabled by default
2. In VS Code: Press `F5` → "Listen for Xdebug (Docker)"
3. Set breakpoints in PHP files
4. Trigger request in browser

**Path Mappings:**
- Container: `/var/www`
- Local: Project root

### TypeScript
- IntelliSense enabled
- Error checking in real-time
- Type definitions included

## 📧 Email Testing

All emails sent via `Mail` facade are captured by **Mailpit**:

- **Web UI:** http://localhost:8025
- **SMTP Port:** 1025
- **No configuration needed** - works out of the box

View sent emails, inspect HTML/text versions, check attachments.

## 🔐 Security Features

- ✅ HTTPS with self-signed certificates
- ✅ CSRF protection enabled
- ✅ XSS prevention
- ✅ SQL injection prevention (PDO)
- ✅ Redis password protection
- ✅ Secure session handling
- ✅ Security headers in Nginx
- ✅ Non-root Docker containers

## 🚀 Performance Optimizations

- ✅ OPcache enabled (PHP bytecode caching)
- ✅ Redis for caching and sessions
- ✅ Gzip compression (Nginx)
- ✅ Static asset caching
- ✅ Vite code splitting
- ✅ Lazy loading (React components)
- ✅ Database query optimization ready

## 🔄 Development Workflow

### 1. Start Development
```bash
make dev
```

### 2. Make Changes
- **Backend:** Edit files in `app/`, migrations, routes
- **Frontend:** Edit files in `resources/js/`
- Changes auto-reload via Vite HMR

### 3. Test Changes
```bash
make test
make typescript-check
```

### 4. Commit
```bash
git add .
git commit -m "feat: add new feature"
git push
```

## 📦 Package Management

### Composer (PHP)
```bash
# Install package
make composer CMD="require vendor/package"

# Update packages
make composer-update

# Remove package
make composer CMD="remove vendor/package"
```

### NPM (JavaScript)
```bash
# Install package
make npm CMD="install package-name"

# Update packages
make npm-update

# Remove package
make npm CMD="uninstall package-name"
```

## 🌍 Environment Variables

Key variables in `.env`:

```env
# Application
APP_URL=https://vmmint22.local
APP_DEBUG=true

# Database
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_DATABASE=store_db
DB_USERNAME=store_user
DB_PASSWORD=secret

# Redis
REDIS_HOST=redis
REDIS_PASSWORD=redis_secret

# Mail
MAIL_HOST=mailpit
MAIL_PORT=1025

# Xdebug
ENABLE_XDEBUG=true
XDEBUG_MODE=debug,develop,coverage
```

## 🤝 Contributing

This is a portfolio/pet project, but suggestions and improvements are welcome!

1. Fork the repository
2. Create feature branch: `git checkout -b feature/amazing-feature`
3. Commit changes: `git commit -m 'Add amazing feature'`
4. Push to branch: `git push origin feature/amazing-feature`
5. Open Pull Request

## 📝 License

This project is open-source and available under the [MIT License](LICENSE).

## 👤 Author

Created as a portfolio project for resume demonstration.

## 🙏 Acknowledgments

- Based on tutorial: [Build Multi Vendor E-Commerce Marketplace](https://www.youtube.com/watch?v=1Vj73iP_7vk)
- Laravel Framework
- React Community
- Docker Community

## 📞 Support

- **Issues:** Create an issue on GitHub
- **Documentation:** See [docs/](docs/) folder
- **Tutorial:** Follow video guide above

## 🗺️ Roadmap

- [x] Docker development environment
- [x] Laravel 12 + React 18 + TypeScript
- [x] Authentication (Breeze)
- [x] Admin panel (Filament)
- [x] Testing framework (Pest)
- [x] Email testing (Mailpit)
- [x] Debugging (Xdebug)
- [ ] Multi-vendor functionality
- [ ] Product catalog
- [ ] Shopping cart
- [ ] Payment integration
- [ ] Order management
- [ ] Reviews and ratings
- [ ] Search functionality
- [ ] CI/CD pipeline
- [ ] Production deployment

---


**Documentation:**
- [Quick Start](QUICKSTART.md)
- [Deployment Guide](DEPLOYMENT.md)
- [Architecture](docs/ARCHITECTURE.md)
- [Checklist](CHECKLIST.md)
