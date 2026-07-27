# Dockerfile for YFlow Frontend

# --- Builder Stage ---
FROM node:22-alpine AS builder

# Set working directory
WORKDIR /app

# Copy package files
COPY package.json yarn.lock ./

# Install dependencies
RUN yarn install

# Copy source files
COPY . .

# Build the application
RUN yarn build

# --- Final Stage ---
FROM nginx:alpine

# Copy built files from builder
COPY --from=builder /app/dist /usr/share/nginx/html

# Copy nginx configuration
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Expose port
EXPOSE 80

# Start command
CMD ["nginx", "-g", "daemon off;"]
