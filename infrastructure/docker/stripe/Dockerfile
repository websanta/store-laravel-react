FROM stripe/stripe-cli:latest

# Install curl to send webhooks
RUN apk add --no-cache curl

# Additional utilities
RUN apk add --no-cache jq

# Override entrypoint to preserve the original behavior
ENTRYPOINT ["stripe"]
CMD ["--help"]
