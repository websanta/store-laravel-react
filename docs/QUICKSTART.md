# Quick Start Guide

Get your Laravel + React + TypeScript development environment up and running in **10 minutes**.

## Prerequisites

✅ Linux Mint 22 VM with Docker installed
✅ Git installed
✅ VS Code with Remote-SSH (on Windows host)
✅ At least 4GB RAM allocated to VM
✅ 20GB free disk space

## Step 1: Clone Project (1 min)

```bash
# On your Linux Mint VM
cd /home/websanta/docker_projects/
git clone <your-repo-url> store-laravel-react
cd store-laravel-react

# OR create from scratch
mkdir store-laravel-react
cd store-laravel-react
# Copy all configuration files here
```

## Step 2: Generate SSL Certificates (1 min)

```bash
mkdir -p infrastructure/docker/nginx/certs

openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout infrastructure/docker/nginx/certs/temp-key.pem \
  -out infrastructure/docker/nginx/certs/temp.pem \
  -subj "/C=US/ST=State/L=City/O=Dev/CN=vmmint22.local"
```

## Step 3: Configure Hosts (1 min)

### On Linux VM:
```bash
echo "127.0.0.1 vmmint22.local" | sudo tee -a /etc/hosts
```

### On Windows 11 Host:
Add to `C:\Windows\System32\drivers\etc\hosts` (run Notepad as Administrator):
```
<VM_IP_ADDRESS> vmmint22.local
```

Replace `<VM_IP_ADDRESS>` with your actual VM IP (find with `ip addr show`).

## Step 4: Initialize Project (2 min)

```bash
# Make scripts executable
chmod +x scripts/*.sh

# Initialize Laravel
./scripts/init-project.sh
```

This will:
- Install Laravel 12
- Create `.env` file
- Configure for Docker
- Set up directories

## Step 5: Start Everything (5 min)

```bash
# One command to rule them all!
make install
```

This command does:
1. ✅ Build Docker containers
2. ✅ Start all services
3. ✅ Install Composer dependencies
4. ✅ Install NPM dependencies
5. ✅ Generate app key
6. ✅ Install Pest testing framework
7. ✅ Run migrations
8. ✅ Create storage link
9. ✅ Fix permissions

Wait 3-5 minutes for first build (downloads images).

## Step 6: Verify Installation

```bash
# Check containers
make ps

# All should show "Up"
```

### Access Points:

| Service | URL | Credentials |
|---------|-----|-------------|
| **Application** | https://vmmint22.local | - |
| **Mailpit UI** | http://localhost:8025 | - |
| **pgAdmin** | http://localhost:5050 | admin@store.com / admin |

## Step 7: Install Breeze & Filament (Optional)

```bash
# Install Laravel Breeze with React and TypeScript
make breeze-install

# Install Filament admin panel
make filament-install

# Create admin user
make filament-user
```

## Connect with VS Code (Windows)

1. Open VS Code
2. Press `F1` → "Remote-SSH: Connect to Host"
3. Enter: `websanta@<VM_IP>`
4. Open folder: `/home/websanta/docker_projects/store-laravel-react`
5. Install recommended extensions when prompted

## Essential Commands

```bash
# Start development (with Vite HMR)
make dev

# View logs
make logs

# Run tests
make test

# Clear cache
make cache-clear

# Database migrations
make migrate

# Access container shell
make shell

# See all commands
make help
```

## Development Workflow

### Frontend (TypeScript + React)
```bash
# Watch mode (HMR)
make dev

# Check TypeScript types
make typescript-check

# Lint code
make lint

# Format code
make format
```

### Backend (Laravel)
```bash
# Run migrations
make migrate

# Create model
make artisan CMD="make:model Product -m"

# Run tests
make test

# Tinker console
make artisan CMD="tinker"
```

### Debugging with Xdebug

1. Xdebug is enabled by default
2. In VS Code, press `F5` → select "Listen for Xdebug (Docker)"
3. Set breakpoints in PHP files
4. Refresh browser

To disable Xdebug (better performance):
```bash
make xdebug-disable
```

## Email Testing

All emails go to Mailpit (no external SMTP needed):
- Web UI: http://localhost:8025
- SMTP: localhost:1025

## Troubleshooting

### Containers won't start:
```bash
make down
make build
make up
```

### Permission errors:
```bash
make permissions
```

### Database issues:
```bash
docker compose restart postgres
make migrate
```

### Vite not connecting:
```bash
docker compose restart node
# Check logs: make logs-node
```

### Clear everything:
```bash
make clean  # ⚠️ Deletes all data!
make install
```

## Project Structure

```
/store-laravel-react/
├── app/                     # Laravel backend
├── resources/
│   ├── js/                  # React + TypeScript
│   │   ├── Components/      # Reusable components
│   │   ├── Pages/           # Page components
│   │   ├── Layouts/         # Layout components
│   │   └── types/           # TypeScript types
│   ├── css/                 # Stylesheets (Tailwind)
│   └── views/               # Blade templates
├── tests/                   # Pest tests
├── infrastructure/docker/   # Docker configs
└── scripts/                 # Automation scripts
```

## Key Features Configured

✅ Laravel 12 with PHP 8.3
✅ React 18 with TypeScript
✅ Vite with HMR
✅ PostgreSQL 16
✅ Redis 7 (cache + sessions)
✅ Mailpit (email testing)
✅ Xdebug 3 (debugging)
✅ Pest (testing framework)
✅ Filament (admin panel)
✅ Laravel Breeze (auth)
✅ Tailwind CSS
✅ ESLint + Prettier

## Next Steps

1. 📖 Read [ARCHITECTURE.md](docs/ARCHITECTURE.md)
2. 📚 Read [DEPLOYMENT.md](DEPLOYMENT.md) for details
3. 🎥 Follow [tutorial video](https://www.youtube.com/watch?v=1Vj73iP_7vk)
4. 💻 Start coding!

## Common Issues

### SSL Certificate Warning in Browser
Accept the self-signed certificate:
- Chrome/Brave: Click "Advanced" → "Proceed to vmmint22.local"

### Can't access from Windows
Check VM firewall:
```bash
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 5173/tcp
```

### TypeScript errors in VS Code
```bash
# Restart TypeScript server
# VS Code: Ctrl+Shift+P → "TypeScript: Restart TS Server"
```

---

**🚀 You're ready to code!**

For detailed explanations, see [README.md](README.md) and [DEPLOYMENT.md](DEPLOYMENT.md).

**Need help?** Check container logs: `make logs`
