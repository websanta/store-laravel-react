# Deployment Guide

Complete step-by-step guide for deploying the Multi-Vendor E-Commerce Marketplace application.

## Prerequisites Checklist

Before starting, ensure you have:

- ✅ VMware Workstation installed on Windows 11
- ✅ Linux Mint 22 VM created and running
- ✅ Docker and Docker Compose installed on Linux Mint VM
- ✅ Git installed on Linux Mint VM
- ✅ VS Code with Remote-SSH extension on Windows 11
- ✅ Network connectivity between Windows host and Linux VM
- ✅ Sufficient disk space (minimum 20GB free on VM)

## Part 1: VM Preparation

### 1.1 Install Docker on Linux Mint 22

```bash
# Update package index
sudo apt update

# Install prerequisites
sudo apt install -y apt-transport-https ca-certificates curl software-properties-common

# Add Docker's official GPG key
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg

# Add Docker repository (Linux Mint is based on Ubuntu)
echo "deb [arch=amd64 signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu jammy stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# Install Docker
sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin

# Start and enable Docker
sudo systemctl start docker
sudo systemctl enable docker

# Add your user to docker group (to run without sudo)
sudo usermod -aG docker $USER

# Log out and log back in for group changes to take effect
# OR run: newgrp docker
```

### 1.2 Verify Docker Installation

```bash
# Check Docker version
docker --version

# Check Docker Compose version
docker compose version

# Test Docker installation
docker run hello-world
```

### 1.3 Configure VM Network

```bash
# Find VM IP address
ip addr show

# Note down the IP address (e.g., 192.168.1.100)
# You'll need this for Remote-SSH connection
```

## Part 2: Project Setup

### 2.1 Create Project Directory

```bash
# Create project directory
mkdir -p /home/websanta/docker_projects
cd /home/websanta/docker_projects

# Initialize Git repository (if cloning)
# git clone <your-repository-url> store-laravel-react

# OR create new project
mkdir store-laravel-react
cd store-laravel-react
git init
```

### 2.2 Create Project Structure

```bash
# Create directory structure
mkdir -p docs
mkdir -p infrastructure/docker/{nginx/{certs,conf.d},node,php-fpm}
mkdir -p infrastructure/deploy/github-actions
mkdir -p scripts

# Create placeholder files
touch infrastructure/docker/nginx/certs/.gitkeep
```

### 2.3 Copy Configuration Files

Copy all the configuration files created in previous steps:
- `docker-compose.yml` → `infrastructure/docker-compose.yml`
- `Dockerfile` files → respective directories
- `.env.example` → project root
- `Makefile` → project root
- `README.md` → project root
- `ARCHITECTURE.md` → `docs/`
- `nginx.conf` → `infrastructure/docker/nginx/conf.d/`
- `php.ini` → `infrastructure/docker/php-fpm/`
- `.gitignore` → project root

### 2.4 Generate SSL Certificates

```bash
# Navigate to project root
cd /home/websanta/docker_projects/store-laravel-react

# Generate self-signed certificates
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout infrastructure/docker/nginx/certs/temp-key.pem \
  -out infrastructure/docker/nginx/certs/temp.pem \
  -subj "/C=US/ST=State/L=City/O=Development/CN=vmmint22.local"

# Verify certificates created
ls -la infrastructure/docker/nginx/certs/
```

### 2.5 Configure Hosts File

#### On Linux Mint VM:

```bash
# Edit hosts file
sudo nano /etc/hosts

# Add this line:
127.0.0.1 vmmint22.local

# Save and exit (Ctrl+X, Y, Enter)
```

#### On Windows 11 Host:

```powershell
# Run PowerShell as Administrator
# Edit hosts file
notepad C:\Windows\System32\drivers\etc\hosts

# Add this line (replace with your VM's actual IP):
192.168.1.100 vmmint22.local

# Save and close
```

## Part 3: Laravel Installation

### 3.1 Install Laravel

```bash
cd /home/websanta/docker_projects/store-laravel-react

# Option 1: Using Composer on host (if available)
composer create-project laravel/laravel .

# Option 2: Using Docker temporary container
docker run --rm -v $(pwd):/app composer:latest create-project laravel/laravel /app

# Set proper permissions
sudo chown -R $USER:$USER .
chmod -R 755 storage bootstrap/cache
```

### 3.2 Configure Laravel

```bash
# Copy environment file
cp .env.example .env

# Edit .env file with your settings
nano .env

# Update these values in .env:
# APP_URL=https://vmmint22.local
# DB_CONNECTION=pgsql
# DB_HOST=postgres
# DB_DATABASE=store_db
# DB_USERNAME=store_user
# DB_PASSWORD=secret
# REDIS_HOST=redis
# REDIS_PASSWORD=redis_secret
```

## Part 4: Build and Start Application

### 4.1 Initial Build

```bash
cd /home/websanta/docker_projects/store-laravel-react

# Build Docker containers (first time may take 10-15 minutes)
make build

# Start containers
make up

# Check if all containers are running
make ps
```

Expected output:
```
NAME              STATUS          PORTS
store_app         Up 30 seconds   9000/tcp
store_nginx       Up 30 seconds   0.0.0.0:80->80/tcp, 0.0.0.0:443->443/tcp
store_node        Up 30 seconds   0.0.0.0:5173->5173/tcp
store_postgres    Up 30 seconds   0.0.0.0:5432->5432/tcp
store_redis       Up 30 seconds   0.0.0.0:6379->6379/tcp
store_pgadmin     Up 30 seconds   0.0.0.0:5050->80/tcp
```

### 4.2 Install Dependencies

```bash
# Install PHP dependencies
make composer-install

# Install Node.js dependencies
make npm-install
```

### 4.3 Initialize Application

```bash
# Generate application key
make key-generate

# Run database migrations
make migrate

# Create storage symbolic link
make storage-link

# Fix permissions
make permissions
```

### 4.4 Install Authentication (Breeze)

```bash
# Install Laravel Breeze
make breeze-install

# This will:
# 1. Install Breeze package
# 2. Scaffold React authentication
# 3. Install NPM dependencies
# 4. Build assets
```

### 4.5 Install Admin Panel (Filament)

```bash
# Install Filament
make filament-install

# Create admin user
make artisan CMD="make:filament-user"
# Follow prompts to create admin account
```

## Part 5: VS Code Remote Development Setup

### 5.1 Configure SSH Connection

#### On Windows 11:

1. Open VS Code
2. Install "Remote - SSH" extension
3. Press `F1` and type "Remote-SSH: Add New SSH Host"
4. Enter: `ssh websanta@192.168.1.100` (use your VM IP)
5. Select SSH config file (usually `C:\Users\YourName\.ssh\config`)

#### Configure SSH Config (optional):

Create/edit `~/.ssh/config` on Windows:

```
Host vmmint22
    HostName 192.168.1.100
    User websanta
    IdentityFile ~/.ssh/id_rsa
    ForwardAgent yes
```

### 5.2 Connect to VM

1. Press `F1` in VS Code
2. Type "Remote-SSH: Connect to Host"
3. Select `vmmint22` or `websanta@192.168.1.100`
4. Open folder: `/home/websanta/docker_projects/store-laravel-react`

### 5.3 Install VS Code Extensions on Remote

Recommended extensions:
- PHP Intelephense
- Laravel Extra Intellisense
- Laravel Blade Snippets
- ES7+ React/Redux/React-Native snippets
- Tailwind CSS IntelliSense
- Docker
- GitLens

## Part 6: Development Workflow

### 6.1 Start Development Environment

```bash
# In VS Code terminal (connected to VM)
cd /home/websanta/docker_projects/store-laravel-react

# Start development servers
make dev

# Or manually:
make up
docker compose exec -d node npm run dev
```

### 6.2 Access Application

Open in Brave browser on Windows 11:
- **Main App**: https://vmmint22.local
- **Admin Panel**: https://vmmint22.local/admin
- **pgAdmin**: http://192.168.1.100:5050
- **Vite Dev**: http://192.168.1.100:5173

### 6.3 Watch Logs

```bash
# All containers
make logs

# Specific container
make logs CONTAINER=store
make logs CONTAINER=nginx
```

## Part 7: Testing and Verification

### 7.1 Test Database Connection

```bash
# Access PostgreSQL directly
docker compose exec postgres psql -U store_user -d store_db

# List tables
\dt

# Exit
\q
```

### 7.2 Test Redis Connection

```bash
# Access Redis CLI
docker compose exec redis redis-cli -a redis_secret

# Test command
ping
# Should respond: PONG

# Exit
exit
```

### 7.3 Test Laravel Application

```bash
# Run Laravel tests
make test

# Check routes
make artisan CMD="route:list"

# Check configuration
make artisan CMD="config:show database"
```

### 7.4 Test React Application

```bash
# Build production assets
make npm CMD="run build"

# Check build output
ls -la public/build/
```

## Part 8: Troubleshooting

### Common Issues and Solutions

#### Issue 1: Container Won't Start

```bash
# Check logs
make logs CONTAINER=store

# Rebuild container
docker compose down
make build
make up
```

#### Issue 2: Permission Denied Errors

```bash
# Fix permissions
make permissions

# If still issues, run as root:
docker compose exec -u root store chown -R www:www /var/www
```

#### Issue 3: Database Connection Failed

```bash
# Check if PostgreSQL is running
make ps

# Restart database
docker compose restart postgres

# Wait for health check
make logs CONTAINER=postgres
```

#### Issue 4: SSL Certificate Error in Browser

Accept the self-signed certificate:
1. In Brave, click "Advanced"
2. Click "Proceed to vmmint22.local (unsafe)"

Or regenerate certificates:
```bash
cd infrastructure/docker/nginx/certs
rm temp.pem temp-key.pem
# Run openssl command from section 2.4
docker compose restart nginx
```

#### Issue 5: Vite HMR Not Working

```bash
# Restart Node container
docker compose restart node

# Check Vite config in vite.config.js:
# server: {
#   host: '0.0.0.0',
#   hmr: {
#     host: 'vmmint22.local'
#   }
# }
```

#### Issue 6: Cannot Access from Windows Browser

```bash
# On VM, check firewall
sudo ufw status

# If firewall is active, allow ports:
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 5173/tcp
sudo ufw reload

# Check VM network adapter (should be Bridged or NAT)
```

## Part 9: Database Seeding (Optional)

### 9.1 Create Seeders

```bash
# Create seeder
make artisan CMD="make:seeder ProductSeeder"
make artisan CMD="make:seeder VendorSeeder"
make artisan CMD="make:seeder CategorySeeder"
```

### 9.2 Run Seeders

```bash
# Seed database
make seed

# Or fresh migration with seed
make migrate-fresh
```

## Part 10: Git Configuration

### 10.1 Initialize Repository

```bash
# Initialize git
git init

# Add all files
git add .

# First commit
git commit -m "Initial project setup with Docker, Laravel, and React"

# Add remote repository (if you have one)
git remote add origin <your-repository-url>
git push -u origin main
```

## Part 11: Backup and Recovery

### 11.1 Backup Database

```bash
# Create backup
make db-backup

# Backup file will be created: backup_YYYY-MM-DD_HH-MM-SS.sql
```

### 11.2 Restore Database

```bash
# Restore from backup
make db-restore FILE=backup_2024-01-01_12-00-00.sql
```

### 11.3 Backup Application Files

```bash
# Create tar archive (excluding node_modules and vendor)
tar -czf store-backup-$(date +%Y%m%d).tar.gz \
  --exclude='node_modules' \
  --exclude='vendor' \
  --exclude='storage/logs/*.log' \
  /home/websanta/docker_projects/store-laravel-react
```

## Part 12: Next Steps

After successful deployment:

1. ✅ Configure email settings in `.env`
2. ✅ Set up queue worker: `make artisan CMD="queue:work"`
3. ✅ Configure scheduled tasks (cron)
4. ✅ Set up monitoring and logging
5. ✅ Create initial categories and products
6. ✅ Test all features thoroughly
7. ✅ Set up Git workflow and branches
8. ✅ Configure CI/CD pipeline (GitHub Actions)

## Useful Commands Reference

```bash
# Container management
make up           # Start all containers
make down         # Stop all containers
make restart      # Restart all containers
make ps           # Show running containers
make logs         # View all logs

# Development
make dev          # Start development environment
make shell        # Access container shell
make artisan      # Run Artisan commands
make composer     # Run Composer commands
make npm          # Run NPM commands

# Database
make migrate      # Run migrations
make seed         # Seed database
make db-backup    # Backup database
make db-restore   # Restore database

# Maintenance
make cache-clear  # Clear all caches
make optimize     # Optimize application
make permissions  # Fix permissions
make clean        # Clean everything (WARNING!)
```

## Support and Resources

- **Laravel Documentation**: https://laravel.com/docs
- **React Documentation**: https://react.dev
- **Docker Documentation**: https://docs.docker.com
- **Tutorial Video**: https://www.youtube.com/watch?v=1Vj73iP_7vk

---

**Deployment Complete! 🎉**

Your Multi-Vendor E-Commerce Marketplace is now ready for development.

If you encounter any issues not covered in this guide, check container logs using `make logs` and refer to the troubleshooting section.

**Happy Coding!**