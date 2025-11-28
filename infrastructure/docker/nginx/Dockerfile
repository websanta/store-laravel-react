# Base Nginx image
FROM nginx:alpine

# Copy custom configuration
COPY conf.d/nginx.conf /etc/nginx/conf.d/default.conf

# Copy SSL certificates
# NOTE: The user is responsible for placing valid certificates here.
# These paths must match the paths referenced in nginx.conf.
COPY certs/temp.pem /etc/nginx/certs/vmmint22.local.pem
COPY certs/temp-key.pem /etc/nginx/certs/vmmint22.local.key

WORKDIR /var/www/html
