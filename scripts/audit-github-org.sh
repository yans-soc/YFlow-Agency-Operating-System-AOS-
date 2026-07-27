#!/bin/bash
#
# GitHub Organization Audit Script
#
# This script inspects key security and policy settings for the
# 'YANS-DIGITAL-KREASI' GitHub organization.
#
# REQUIREMENTS:
# - GitHub CLI ('gh') must be installed and authenticated.
# - A GITHUB_TOKEN environment variable must be exported with a
#   Personal Access Token (PAT) that has 'admin:org' scope.
#
# USAGE:
# export GITHUB_TOKEN="your_github_pat_with_admin_org_scope"
# bash audit-github-org.sh

# --- Configuration ---
ORG_NAME="YANS-DIGITAL-KREASI"

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

check_command() {
    if ! command -v $1 &> /dev/null; then
        echo -e "${RED}Error: Command '$1' not found. Please install it.${NC}"
        exit 1
    fi
}

# --- Pre-flight Checks ---
check_command gh

if [ -z "$GITHUB_TOKEN" ]; then
    echo -e "${RED}Error: GITHUB_TOKEN environment variable is not set.${NC}"
    echo "Please export a PAT with 'admin:org' scope."
    exit 1
fi

# --- TASK 4: GitHub Organization Inspection ---
print_header "TASK 4: GitHub Organization Audit for '$ORG_NAME'"

# Query the organization settings using the GraphQL API
ORG_INFO=$(gh api graphql -f query='
  query($org: String!) {
    organization(login: $org) {
      name
      login
      description
      membersCanCreateRepositories
      thirdPartyAppAccessPolicy
      ipAllowListEnabledSetting
      requiresTwoFactorAuthentication
      defaultRepositoryPermission
    }
  }
' -f org="$ORG_NAME" --jq '.data.organization')

# Check if the query was successful
if [ -z "$ORG_INFO" ]; then
    echo -e "${RED}Failed to fetch organization information. Check your token and permissions.${NC}"
    exit 1
fi

# Extract and display settings
echo "Organization Name: $(echo $ORG_INFO | jq -r '.name')"
echo "Login: $(echo $ORG_INFO | jq -r '.login')"

# Member repository creation
MEMBERS_CAN_CREATE=$(echo $ORG_INFO | jq -r '.membersCanCreateRepositories')
echo -n "Members can create repositories: "
if [[ "$MEMBERS_CAN_CREATE" == "true" ]]; then
    echo -e "${YELLOW}Yes. Consider restricting to 'private' or disabling.${NC}"
else
    echo -e "${GREEN}No. (Recommended Setting)${NC}"
fi

# Default repository permission
DEFAULT_PERM=$(echo $ORG_INFO | jq -r '.defaultRepositoryPermission')
echo -n "Default repository permission for members: "
if [[ "$DEFAULT_PERM" == "none" || "$DEFAULT_PERM" == "read" ]]; then
    echo -e "${GREEN}${DEFAULT_PERM} (Good Practice)${NC}"
else
    echo -e "${YELLOW}${DEFAULT_PERM} (Consider 'read' or 'none')${NC}"
fi

# Two-factor authentication
TFA_REQUIRED=$(echo $ORG_INFO | jq -r '.requiresTwoFactorAuthentication')
echo -n "Two-factor authentication required for all members: "
if [[ "$TFA_REQUIRED" == "true" ]]; then
    echo -e "${GREEN}Yes (Excellent)${NC}"
else
    echo -e "${YELLOW}No (Strongly Recommended to enforce 2FA)${NC}"
fi

# Third-party app access policy
APP_POLICY=$(echo $ORG_INFO | jq -r '.thirdPartyAppAccessPolicy')
echo -n "Third-party application access policy: "
if [[ "$APP_POLICY" == "RESTRICTED" ]]; then
    echo -e "${GREEN}Restricted (Good, requires approval)${NC}"
elif [[ "$APP_POLICY" == "UNRESTRICTED" ]]; then
     echo -e "${YELLOW}Unrestricted (Consider changing to 'Restricted')${NC}"
else
    echo "$APP_POLICY"
fi

# IP allow list
IP_ALLOW_LIST=$(echo $ORG_INFO | jq -r '.ipAllowListEnabledSetting')
echo -n "IP allow list enabled: "
if [[ "$IP_ALLOW_LIST" != "DISABLED" ]]; then
    echo -e "${GREEN}Yes (Good for security)${NC}"
else
    echo -e "${YELLOW}No${NC}"
fi


echo -e "\n${GREEN}--- Audit Complete ---${NC}\n"

echo "This script provides a high-level overview. For a complete audit, manually review all settings in:"
echo "https://github.com/organizations/$ORG_NAME/settings/profile"

