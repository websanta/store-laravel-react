# Multi-Vendor E-Commerce Marketplace (Laravel + React)

![Project Logo](docs/assets/logo.png)

## 📌 Project Overview

This is a **full-stack multi-vendor e-commerce marketplace** built with:

- **Backend:** Laravel 12.39.0 (PHP 8.3)
- **Frontend:** React SPA (Vite + TailwindCSS)
- **Database:** PostgreSQL 16
- **Cache & Queue:** Redis
- **Web Server:** Nginx
- **Containerization:** Docker + Docker Compose
- **CI/CD:** GitHub Actions
- **Payment Gateway (optional):** Stripe

The project is designed as a **PET-project for portfolio**, demonstrating professional full-stack skills including:

- Separation of frontend and backend (SPA + API)
- Multi-vendor architecture
- Role-based access (Admin, Vendor, User)
- Product CRUD with variations
- Shopping cart & checkout
- Notifications (emails)
- Dockerized dev & production environments
- CI/CD pipelines with automated tests

---

## 📂 Project Structure

```text
/store-laravel-react/
├── backend/                     # Laravel API
├── frontend/                    # React SPA
├── infrastructure/              # Docker, CI/CD, deploy scripts
├── docs/                        # Documentation & architecture diagrams
├── Makefile                     # Common commands for dev, test, deploy
├── .gitignore
└── README.md
