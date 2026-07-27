# Task 4 & 10: GitHub Organization Audit Report

This document presents the findings from the audit of the `YANS-DIGITAL-KREASI` GitHub organization, conducted as part of the platform standardization and security audit process.

---

## 1. Audit Method

-   **Script Used**: `scripts/audit-github-org.sh`
-   **Authentication**: The script requires a GitHub Personal Access Token (PAT) with `admin:org` scope to be exported as the `GITHUB_TOKEN` environment variable.
-   **Execution Date**: `pending`

---

## 2. Audit Findings

This section will be populated with the output from the audit script once it has been executed.

```bash
# The output of the 'bash scripts/audit-github-org.sh' command will be pasted here.
```

---

## 3. Analysis of Findings

| Setting                                | Current Value | Recommended Value | Status  | Analysis & Action Required                                                                                                                                                                             |
| -------------------------------------- | ------------- | ----------------- | ------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **Members can create repositories**    | `pending`     | `false` or `private only` | `pending` | If `true`, this allows any member to create public repositories, which could lead to accidental code exposure. **Action**: Restrict this to `private` repositories only, or disable it entirely. |
| **Default repository permission**      | `pending`     | `none` or `read`  | `pending` | If set to `write` or `admin`, new members get excessive permissions by default. This violates the principle of least privilege. **Action**: Set this to `read` or `none`.                             |
| **Two-factor authentication (2FA)**    | `pending`     | `true`            | `pending` | If `false`, the organization is at high risk of account takeover. **Action**: Enforce 2FA for all members immediately. This is a non-negotiable security baseline.                                   |
| **Third-party application access**     | `pending`     | `RESTRICTED`      | `pending` | If `UNRESTRICTED`, any member can install any third-party app, creating a potential security vulnerability. **Action**: Set this to `RESTRICTED` so that all apps require organization owner approval. |
| **IP allow list**                      | `pending`     | `ENABLED` (optional) | `pending` | If `DISABLED`, this is acceptable for most organizations but can be enabled for an extra layer of security if all developers have static IPs. **Action**: Review if this is needed.                  |

---

## 4. Summary of Required Actions

Based on the pending audit, the following actions are anticipated:

1.  [ ] **Enforce 2FA**: Navigate to organization settings and require two-factor authentication for everyone.
2.  [ ] **Restrict App Policy**: Change the third-party application policy to "Restricted."
3.  [ ] **Set Default Permissions**: Change the base member permissions to "Read" or "None."
4.  [ ] **Restrict Repository Creation**: Limit repository creation to organization owners or allow private repositories only.

Executing these actions will significantly improve the security posture of the GitHub organization, bringing it in line with production-grade standards.

---
