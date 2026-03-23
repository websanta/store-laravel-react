FROM nginx:alpine

# Install curl for healthcheck
RUN apk add --no-cache curl

# Remove default configuration
RUN rm -rf /etc/nginx/conf.d/*

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