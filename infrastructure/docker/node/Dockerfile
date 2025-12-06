FROM node:20-alpine

# Set working directory
WORKDIR /var/www

# Install system dependencies
RUN apk add --no-cache \
    git \
    bash \
    curl \
    shadow

# Get host user UID/GID (default to 1000 if not provided)
ARG USER_ID=1000
ARG GROUP_ID=1000

# Modify the node user to match host UID/GID
RUN deluser --remove-home node && \
    addgroup -g ${GROUP_ID} node && \
    adduser -D -u ${USER_ID} -G node node

# Copy package files first (for better caching)
COPY --chown=node:node package*.json ./

# Copy application files
COPY --chown=node:node . .

# Use the built-in 'node' user that exists in Node.js image
USER node

# Expose Vite dev server port
EXPOSE 5174

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=60s --retries=3 \
    CMD curl -k -f https://localhost:5174 || exit 1

# Don't start dev server automatically - we'll install deps first via Makefile
CMD ["tail", "-f", "/dev/null"]
