#!/bin/bash
#
# Infrastructure Inspection Script
#
# This script runs on the Application Server (VPS1) to inspect the
# production infrastructure components. It is designed to be
# non-destructive and report on the current state.
#
# REQUIREMENTS:
# - Run as a user with Docker permissions.
# - `curl`, `docker`, `openssl`, `dig` commands must be available.
#
# USAGE:
# bash inspect-infra.sh

# --- Configuration ---
SERVER_PUBLIC_IP="43.133.152.124" # VPS1 Public IP
HOST_DOMAIN="host.yansdigital.com"
COOLIFY_DOMAIN="coolify.yansdigital.com"
APPS_WILDCARD_DOMAIN="*.apps.yansdigital.com"
EXAMPLE_APP_DOMAIN="yflow.apps.yansdigital.com"
DB_SERVER_HOSTNAME="db" # As per Tailscale

# --- Colors for output ---
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# --- Helper Functions ---
print_header() {
    echo -e "\n${GREEN}=======================================================${NC}"
    echo -e "${GREEN}  $1 ${NC}"
    echo -e "${GREEN}=======================================================${NC}"
}

print_subheader() {
    echo -e "\n${YELLOW}--- $1 ---${NC}"
}

check_command() {
    if ! command -v $1 &> /dev/null; then
        echo -e "${RED}Error: Command '$1' not found. Please install it.${NC}"
        exit 1
    fi
}

# --- Pre-flight Checks ---
check_command docker
check_command curl
check_command openssl
check_command dig

# --- TASK 1: Coolify Inspection ---
print_header "TASK 1: Coolify Inspection"
COOLIFY_VERSION=$(docker ps --filter "name=coolify-core" --format "{{.Image}}" | cut -d':' -f2)
if [ -n "$COOLIFY_VERSION" ]; then
    echo "Coolify Core Version: $COOLIFY_VERSION"
    curl -s https://github.com/coollabsio/coolify/releases | grep -q "Latest" | grep -q "$COOLIFY_VERSION"
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}Compatibility: Your version is the latest stable release.${NC}"
    else
        echo -e "${YELLOW}Compatibility: A newer stable version might be available. Current version is not marked 'Latest'.${NC}"
    fi
else
    echo -e "${RED}Coolify Core container not found.${NC}"
fi

# --- TASK 2: Docker, Network, Traefik, SSL, DNS Inspection ---
print_header "TASK 2: Infrastructure Stack Inspection"

print_subheader "Docker Inspection"
docker --version
echo "Running Containers:"
docker ps --format "table {{.ID}}\t{{.Image}}\t{{.Names}}\t{{.Status}}"

print_subheader "Docker Networks"
docker network ls --filter "name=coolify" --format "table {{.ID}}\t{{.Name}}\t{{.Driver}}\t{{.Subnet}}"
docker network inspect coolify --format '{{json .IPAM.Config}}' | jq .

print_subheader "Docker Volumes"
docker volume ls --filter "name=coolify" --format "table {{.Name}}\t{{.Driver}}"

print_subheader "Traefik Reverse Proxy"
TRAEFIK_CONTAINER=$(docker ps -q --filter "name=coolify-proxy")
if [ -n "$TRAEFIK_CONTAINER" ]; then
    echo "Traefik container (coolify-proxy) is running."
    docker inspect $TRAEFIK_CONTAINER --format '{{json .Config.Image}}'
else
    echo -e "${RED}Traefik container (coolify-proxy) not found.${NC}"
fi

print_subheader "SSL & DNS Inspection"
DOMAINS=($HOST_DOMAIN $COOLIFY_DOMAIN $EXAMPLE_APP_DOMAIN)
for DOMAIN in "${DOMAINS[@]}"; do
    echo ""
    echo "Inspecting Domain: $DOMAIN"
    
    # DNS Resolution
    RESOLVED_IP=$(dig +short $DOMAIN | tail -n1)
    echo "  DNS resolves to: $RESOLVED_IP"
    if [[ "$RESOLVED_IP" == "$SERVER_PUBLIC_IP" ]]; then
        echo -e "  ${GREEN}DNS Check: OK. Points to this server's public IP.${NC}"
    elif [[ "$RESOLVED_IP" =~ ^(172|104|108)\. ]]; then
         echo -e "  ${YELLOW}DNS Check: Points to a Cloudflare IP. Proxy is active.${NC}"
    else
        echo -e "  ${RED}DNS Check: FAILED. Does not point to server IP or Cloudflare.${NC}"
    fi

    # SSL Certificate
    echo "  Testing SSL/TLS connection..."
    SSL_INFO=$(echo | openssl s_client -servername $DOMAIN -connect $DOMAIN:443 2>/dev/null)
    if [ $? -eq 0 ]; then
        ISSUER=$(echo "$SSL_INFO" | openssl x509 -noout -issuer -nameopt multiline | grep "organizationName" | sed 's/.* = //')
        SUBJECT=$(echo "$SSL_INFO" | openssl x509 -noout -subject -nameopt multiline | grep "commonName" | sed 's/.* = //')
        EXPIRY_DATE=$(echo "$SSL_INFO" | openssl x509 -noout -enddate | sed 's/notAfter=//')
        
        echo "    - Subject: $SUBJECT"
        echo "    - Issuer: $ISSUER"
        echo "    - Expires on: $EXPIRY_DATE"

        if [[ "$ISSUER" == *"Let's Encrypt"* ]]; then
             echo -e "    - ${GREEN}SSL Check: OK. Valid Let's Encrypt certificate found.${NC}"
        else
             echo -e "    - ${YELLOW}SSL Check: Certificate is not from Let's Encrypt (Issuer: $ISSUER).${NC}"
        fi
    else
        echo -e "    - ${RED}SSL Check: FAILED. Could not establish TLS connection to $DOMAIN:443.${NC}"
    fi
done

print_subheader "Wildcard Domain Routing"
echo "Checking if Traefik is configured for wildcard routing..."
# This is an indirect check. A better check would be to inspect traefik's config dump.
curl -k --resolve $EXAMPLE_APP_DOMAIN:443:$SERVER_PUBLIC_IP https://$EXAMPLE_APP_DOMAIN -I 2>/dev/null | head -n 1
if [ $? -eq 0 ]; then
    echo -e "${GREEN}Wildcard routing seems to be active (received a response).${NC}"
else
    echo -e "${RED}Wildcard routing might not be configured correctly.${NC}"
fi

print_subheader "Cloudflare Integration"
echo "Checking for Cloudflare headers from $COOLIFY_DOMAIN..."
CF_HEADERS=$(curl -sI $COOLIFY_DOMAIN | grep -i "cf-")
if [ -n "$CF_HEADERS" ]; then
    echo -e "${GREEN}Cloudflare headers detected. Integration appears active.${NC}"
    echo "$CF_HEADERS"
else
    echo -e "${YELLOW}No Cloudflare headers detected. Is Cloudflare proxy enabled for $COOLIFY_DOMAIN?${NC}"
fi


# --- TASK 10: Security Audit (Partial) ---
print_header "TASK 10: Security Audit (Initial Scan)"

print_subheader "Firewall Status (UFW)"
if command -v ufw &> /dev/null; then
    ufw status
else
    echo -e "${YELLOW}UFW command not found. Cannot check firewall status.${NC}"
fi

print_subheader "Docker Socket Exposure"
DOCKER_SOCKET_CHECK=$(docker ps -q --filter "publish=2375" --filter "publish=2376")
if [ -n "$DOCKER_SOCKET_CHECK" ]; then
    echo -e "${RED}SECURITY RISK: Docker socket appears to be exposed over TCP.${NC}"
    docker ps --filter "publish=2375" --filter "publish=2376"
else
    echo -e "${GREEN}Docker socket does not appear to be exposed over TCP.${NC}"
fi
if [ -S /var/run/docker.sock ]; then
    echo "Docker socket file permissions:"
    ls -l /var/run/docker.sock
else
    echo -e "${YELLOW}Docker socket file not found at /var/run/docker.sock.${NC}"
fi

print_subheader "SSH Configuration"
echo "Checking sshd_config for root login and password authentication..."
if [ -f /etc/ssh/sshd_config ]; then
    PERMIT_ROOT=$(grep -i "^PermitRootLogin" /etc/ssh/sshd_config)
    PASSWORD_AUTH=$(grep -i "^PasswordAuthentication" /etc/ssh/sshd_config)
    echo "  $PERMIT_ROOT"
    echo "  $PASSWORD_AUTH"
    if [[ "$PERMIT_ROOT" == "PermitRootLogin no" ]]; then
        echo -e "  ${GREEN}Root login is disabled.${NC}"
    else
        echo -e "  ${RED}Root login may be enabled. Recommended: 'PermitRootLogin no'${NC}"
    fi
    if [[ "$PASSWORD_AUTH" == "PasswordAuthentication no" ]]; then
        echo -e "  ${GREEN}Password authentication is disabled.${NC}"
    else
        echo -e "  ${RED}Password authentication may be enabled. Recommended: 'PasswordAuthentication no'${NC}"
    fi
else
    echo -e "${YELLOW}Cannot find /etc/ssh/sshd_config.${NC}"
fi


# --- Database Connectivity ---
print_header "External Services Connectivity"
print_subheader "Database Server (Tailscale)"
if command -v tailscale &> /dev/null; then
    echo "Pinging DB server ($DB_SERVER_HOSTNAME) via Tailscale..."
    if tailscale ping --timeout=5s --c=1 $DB_SERVER_HOSTNAME &> /dev/null; then
        echo -e "${GREEN}Connectivity to DB server ($DB_SERVER_HOSTNAME) is OK.${NC}"
    else
        echo -e "${RED}FAILED to ping DB server ($DB_SERVER_HOSTNAME) via Tailscale.${NC}"
    fi
else
    echo -e "${YELLOW}Tailscale command not found. Skipping DB connectivity check.${NC}"
fi

echo -e "\n${GREEN}--- Inspection Complete ---${NC}\n"
