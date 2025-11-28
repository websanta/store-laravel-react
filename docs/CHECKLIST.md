# Project Setup Checklist

Use this checklist to ensure you've completed all necessary setup steps.

## Pre-Installation

- [ ] VMware Workstation installed on Windows 11
- [ ] Linux Mint 22 VM created and running
- [ ] Docker installed on Linux Mint VM (`docker --version`)
- [ ] Docker Compose installed (`docker compose version`)
- [ ] Git installed (`git --version`)
- [ ] User added to docker group (`groups $USER` shows docker)
- [ ] VS Code installed on Windows 11
- [ ] Remote-SSH extension installed in VS Code
- [ ] Network connectivity verified between host and VM

## Project Structure Setup

- [ ] Project directory created: `/home/websanta/docker_projects/store-laravel-react`
- [ ] Directory structure created:
  ```
  - infrastructure/docker/nginx/{certs,conf.d}
  - infrastructure/docker/node
  - infrastructure/docker/php-fpm
  - infrastructure/deploy/github-actions
  - docs
  - scripts
  ```

## Configuration Files

- [ ] `docker-compose.yml` placed in `infrastructure/`
- [ ] `Dockerfile` for PHP-FPM in `infrastructure/docker/php-fpm/`
- [ ] `Dockerfile` for Nginx in `infrastructure/docker/nginx/`
- [ ] `Dockerfile` for Node in `infrastructure/docker/node/`
- [ ] `nginx.conf` in `infrastructure/docker/nginx/conf.d/`
- [ ] `php.ini` in `infrastructure/docker/php-fpm/`
- [ ] `.env.example` in project root
- [ ] `.gitignore` in project root
- [ ] `Makefile` in project root
- [ ] `README.md` in project root
- [ ] `ARCHITECTURE.md` in `docs/`
- [ ] `DEPLOYMENT.md` in project root
- [ ] `QUICKSTART.md` in project root

## SSL Certificates

- [ ] SSL certificates generated using OpenSSL
- [ ] `temp.pem` exists in `infrastructure/docker/nginx/certs/`
- [ ] `temp-key.pem` exists in `infrastructure/docker/nginx/certs/`
- [ ] Certificate permissions set correctly (`chmod 644`)

## Hosts Configuration

- [ ] `vmmint22.local` added to `/etc/hosts` on Linux VM
- [ ] `vmmint22.local` added to `C:\Windows\System32\drivers\etc\hosts` on Windows
- [ ] VM IP address determined and noted
- [ ] DNS resolution tested: `ping vmmint22.local`

## Laravel Installation

- [ ] Laravel installed in project root
- [ ] `.env` file created from `.env.example`
- [ ] `.env` configured for Docker environment:
  - [ ] `APP_URL=https://vmmint22.local`
  - [ ] `DB_CONNECTION=pgsql`
  - [ ] `DB_HOST=postgres`
  - [ ] `DB_DATABASE=store_db`
  - [ ] `DB_USERNAME=store_user`
  - [ ] `DB_PASSWORD=secret`
  - [ ] `REDIS_HOST=redis`
  - [ ] `REDIS_PASSWORD=redis_secret`
- [ ] Storage directories created
- [ ] Permissions set: `chmod -R 775 storage bootstrap/cache`

## Docker Containers

- [ ] All containers built: `make build`
- [ ] All containers started: `make up`
- [ ] Container status verified: `make ps`
- [ ] All containers showing "Up" status:
  - [ ] store_app
  - [ ] store_nginx
  - [ ] store_node
  - [ ] store_postgres
  - [ ] store_redis
  - [ ] store_pgadmin
- [ ] Container logs checked: `make logs`
- [ ] No critical errors in logs

## Dependencies Installation

- [ ] Composer dependencies installed: `make composer-install`
- [ ] NPM dependencies installed: `make npm-install`
- [ ] `vendor/` directory exists
- [ ] `node_modules/` directory exists

## Application Initialization

- [ ] Application key generated: `make key-generate`
- [ ] Database migrations run: `make migrate`
- [ ] Storage link created: `make storage-link`
- [ ] Permissions fixed: `make permissions`

## Authentication & Admin

- [ ] Laravel Breeze installed: `make breeze-install`
- [ ] Breeze React scaffolding confirmed
- [ ] Filament installed: `make filament-install`
- [ ] Admin user created: `make artisan CMD="make:filament-user"`
- [ ] Admin credentials saved securely

## Access Verification

- [ ] Main application accessible: `https://vmmint22.local`
- [ ] Application loads without errors
- [ ] SSL certificate accepted in browser
- [ ] pgAdmin accessible: `http://localhost:5050` or `http://<VM_IP>:5050`
- [ ] pgAdmin login works
- [ ] Vite dev server accessible: `http://localhost:5173`
- [ ] Admin panel accessible: `https://vmmint22.local/admin`

## Development Environment

- [ ] VS Code Remote-SSH configured
- [ ] SSH connection to VM working
- [ ] Project folder opened in VS Code Remote
- [ ] Recommended extensions installed:
  - [ ] PHP Intelephense
  - [ ] Laravel Extra Intellisense
  - [ ] Laravel Blade Snippets
  - [ ] ES7+ React/Redux snippets
  - [ ] Tailwind CSS IntelliSense
  - [ ] Docker
  - [ ] GitLens
- [ ] Terminal working in VS Code
- [ ] File editing working correctly

## Development Workflow

- [ ] Vite HMR working: `make dev`
- [ ] Frontend changes reflect immediately
- [ ] Backend changes apply after container restart
- [ ] Hot Module Replacement functioning
- [ ] No CORS errors in browser console

## Database & Cache

- [ ] PostgreSQL connection working
- [ ] Database accessible via pgAdmin
- [ ] Tables created from migrations
- [ ] Redis connection working
- [ ] Session storage in Redis confirmed
- [ ] Cache operations working

## Testing

- [ ] Tests run successfully: `make test`
- [ ] No failing tests
- [ ] PHPUnit configuration correct
- [ ] Test database configured

## Git Repository

- [ ] Git initialized: `git init`
- [ ] `.gitignore` configured correctly
- [ ] Initial commit made
- [ ] Remote repository added (optional)
- [ ] `.git` directory exists

## Backup & Recovery

- [ ] Database backup tested: `make db-backup`
- [ ] Backup file created successfully
- [ ] Database restore tested: `make db-restore`
- [ ] Restore works correctly

## Documentation

- [ ] README.md reviewed and customized
- [ ] ARCHITECTURE.md reviewed
- [ ] DEPLOYMENT.md reviewed
- [ ] QUICKSTART.md reviewed
- [ ] All documentation links verified

## Optional Enhancements

- [ ] Bash aliases configured (`.bash_aliases`)
- [ ] GitHub Actions workflow configured
- [ ] Pre-commit hooks set up
- [ ] Code quality tools configured (PHPStan, ESLint)
- [ ] Logging configured
- [ ] Monitoring set up

## Troubleshooting Verification

- [ ] `make down` and `make up` work correctly
- [ ] `make restart` works correctly
- [ ] `make logs CONTAINER=store` shows logs
- [ ] `make shell` provides container access
- [ ] `make permissions` fixes permission issues
- [ ] `make cache-clear` clears caches
- [ ] All Makefile commands tested

## Security Checklist

- [ ] `.env` not committed to Git
- [ ] Sensitive data excluded from version control
- [ ] SSL certificates not committed (or use placeholder)
- [ ] Database passwords changed from defaults (production)
- [ ] Redis password changed from defaults (production)
- [ ] Admin panel protected
- [ ] CSRF protection enabled

## Performance Verification

- [ ] Application loads in < 3 seconds
- [ ] No N+1 queries in logs
- [ ] OPcache enabled
- [ ] Asset compression enabled
- [ ] Database indexes created
- [ ] Redis caching working

## Final Checks

- [ ] All environment variables documented
- [ ] All ports documented
- [ ] All access URLs documented
- [ ] Team members can access application (if applicable)
- [ ] Development workflow documented
- [ ] Deployment process documented
- [ ] Rollback process documented
- [ ] Contact information added (if team project)

## Known Issues Log

Document any issues encountered and their solutions:

| Issue | Solution | Date |
|-------|----------|------|
| | | |

## Next Steps

After completing all checklist items:

1. [ ] Start feature development
2. [ ] Set up continuous integration
3. [ ] Configure staging environment
4. [ ] Plan production deployment
5. [ ] Set up monitoring and alerts
6. [ ] Create development branch strategy
7. [ ] Set up code review process
8. [ ] Document API endpoints

---

## Sign-off

- **Setup Completed By**: ___________________________
- **Date**: ___________________________
- **Verified By**: ___________________________
- **Date**: ___________________________

---

**Status**:
- [ ] In Progress
- [ ] Completed
- [ ] Needs Review
- [ ] Ready for Development

**Notes**:
_Add any additional notes or observations here_

---

**Completion**: ___/142 items checked