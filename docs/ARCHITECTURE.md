Store Laravel/React Marketplace Architecture

The application follows a modern, containerized, multi-tier architecture designed for development efficiency and production readiness.

1. Overview (C4 Model - System Context)

The Store Marketplace is composed of six primary Docker containers, orchestrated by Docker Compose, running on a Linux Mint Virtual Machine.

Component

Technology

Role

Client

Nginx

Web Server

Terminates SSL, routes requests to the appropriate backend service, and serves static frontend assets.

Store (PHP-FPM)

Laravel 12.x

Handles API requests, server-side logic, Authentication (Breeze), and Admin Panel (Filament).

Node

Node.js 20

Environment for compiling and serving the React frontend assets (Vite/Mix).

PostgreSQL

Database

Primary persistent data store for the application.

Redis

Key-Value Store

Used for caching and queue management.

pgAdmin

Database GUI

Web-based tool for PostgreSQL database administration.

2. Request Flow and Tiers

A. Frontend/React Flow (SPA - Single Page Application)

Client Request: A user requests https://vmmint22.local/.

Nginx: The Nginx container (nginx) intercepts the request.

Static Files: If the path is for a static asset (CSS, JS, images, or the root /), Nginx serves the file directly from the mounted project volume (/var/www/html/public).

React Routing: For all non-API routes, Nginx is configured to serve the index.html file, allowing the React client-side router (e.g., React Router) to handle the routing and rendering.

B. Backend/API Flow (Laravel)

Client Request: A user makes an API request (e.g., /api/products or /login).

Nginx: Nginx determines the request is for a dynamic resource (e.g., a route that matches a Laravel route).

PHP-FPM Proxy: Nginx proxies the request to the store container on port 9000 via FastCGI.

Laravel Execution: The PHP-FPM process executes the Laravel application logic, interacts with the PostgreSQL/Redis services, and returns the response (JSON or HTML for the admin panel).

Response: The response is sent back through Nginx to the client.

3. Data Persistence

The application utilizes Docker volumes to ensure data persistence across container restarts:

postgres_data volume: Persists the entire PostgreSQL database state.

pgadmin_data volume: Stores pgAdmin user and server configuration.

Host Volume Mount (../../:/var/www/html): The application code is mounted from the host machine (Linux Mint VM) directly into the store and node containers, enabling real-time code changes in VS Code to be reflected without rebuilding containers.