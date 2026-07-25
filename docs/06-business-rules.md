# Business Rules — YFlow

## Workspace Rules
- Each user can create multiple workspaces
- Workspace owner has full admin access
- Members require invitation to join

## Project Rules
- Projects belong to exactly one workspace
- Project code must be unique within workspace
- Archived projects are read-only

## Task Rules
- Tasks belong to exactly one project
- Tasks must have assignee before moving to "In Progress"
- Completed tasks cannot be modified except for notes

## Workflow Rules
- Each project has one active workflow
- Stages have defined order
- Tasks move forward only (no backward movement)

## Permission Rules
- RBAC: Owner > Admin > Member > Viewer
- Permissions cascade from workspace to projects
- Explicit deny overrides allow

---

*Document Version: 1.0*
*Last Updated: 2026-07-25*