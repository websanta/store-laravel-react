Store Laravel/React Marketplace

This repository contains the infrastructure and initial setup for a multi-vendor e-commerce marketplace built using Laravel 12.x (Backend/API/Admin) and React (Frontend/Client).

The project is designed to run locally within a Dockerized environment using custom PHP-FPM, Nginx, and Node.js containers, alongside PostgreSQL and Redis.

🚀 Setup & Installation

Prerequisites

Docker and Docker Compose (v2.x) installed.

VS Code with Remote-SSH configured for your Linux Mint VM.

A self-signed SSL certificate for vmmint22.local.

1. Generate SSL Certificates

For local HTTPS access at https://vmmint22.local/, you need to generate a self-signed certificate and key.

# Use the Makefile target to easily generate certificates (recommended)
make certs


The generated temp.pem and temp-key.pem files should be placed in infrastructure/docker/nginx/certs/.

2. Configure Environment

Copy the example environment file and update the variables as needed.

cp .env.example .env
# Edit .env and adjust PostgreSQL, Redis, and Laravel settings.


3. Build and Run Containers

Use the included Makefile to manage the services.

# Build custom images and start all services in detached mode
make up

# Check the status of all containers
docker compose ps


4. Application Initialization

Once containers are running, install dependencies and set up the database.

# Install Composer dependencies and Node modules
make install

# Generate Laravel application key
make artisan key:generate

# Run database migrations and seeders
make artisan migrate --seed

# Start the frontend watcher (for development)
make frontend-dev


The application should now be accessible at https://vmmint22.local/ and the pgAdmin interface at http://localhost:8080.

🛠️ Usage via Makefile

The Makefile provides convenient shortcuts for common development tasks:

Command

Description

make up

Builds and starts all Docker services.

make down

Stops and removes all containers and networks.

make install

Installs Composer and Node dependencies inside containers.

make artisan <cmd>

Runs php artisan <cmd> inside the store container.

make frontend-dev

Starts the Node container to run npm run dev (Vite hot reload).

make test

Runs tests (requires setup).

make certs

Generates self-signed SSL certificates for local use.