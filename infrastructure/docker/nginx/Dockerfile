FROM nginx:alpine

# Install curl for healthcheck
RUN apk add --no-cache curl

# Remove default configuration
RUN rm -rf /etc/nginx/conf.d/*

# Copy custom nginx configurations
COPY infrastructure/docker/nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf
COPY infrastructure/docker/nginx/conf.d/ssl.conf /etc/nginx/conf.d/ssl.conf

# Copy SSL certificates
COPY infrastructure/docker/nginx/certs /etc/nginx/certs

# Set proper permissions
RUN chmod 644 /etc/nginx/certs/*.pem 2>/dev/null || true

# Create log directory
RUN mkdir -p /var/log/nginx

# Expose ports
EXPOSE 80 443

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

# Start Nginx
CMD ["nginx", "-g", "daemon off;"]