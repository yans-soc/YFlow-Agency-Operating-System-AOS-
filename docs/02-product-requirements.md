# 02 — Product Requirements Document (PRD)

# YFlow PRD Version 1.0 (MVP)

---

## Target Audience
- Solo developer
- Small agency teams

## Development Timeline
- Target: < 2 minggu untuk MVP

## Approach
- Manual-first, AI-assisted
- Core functionality first, AI features optional

---

## Scope

### 1. Authentication
- Email & Password login
- Session management
- Basic role-based access (Owner, Admin, Member, Viewer)

### 2. Dashboard
- Today Overview
- My Schedule
- Notifications
- Active Projects
- AI Suggestions (optional)
- Recent Activity
- Quick Actions

### 3. Projects
- Create / Edit / Delete
- Project Overview
- Project Members
- Project Notes
- Project Files
- Project Timeline
- Project Calendar

### 4. Workflow
- Blank Workflow creation
- AI Generate Workflow (optional)
- Workflow Builder
- Stage management
- Task within stages
- Checklist items
- Task dependencies
- AI Review Workflow (optional)

### 5. Tasks
- Full CRUD operations
- Assign members
- Priority levels
- Due dates
- Status tracking
- Checklists
- Comments
- Attachments

### 6. Calendar
- Month view
- Week view
- Day view
- Agenda view
- Mini dashboard per date
- Click date → detail task view

### 7. Focus View
- Today's Tasks
- Meetings
- Deadlines
- Reminders
- Progress indicators

### 8. People
- Team members list
- Workload visualization
- Personal calendar

### 9. Knowledge
- Notes
- SOP documentation
- Templates

### 10. AI (Optional)
- Chat interface
- Generate Workflow
- Generate Task
- Generate Checklist
- Review Workflow

### 11. Notifications
- In-app notifications
- @mentions
- Reminders
- Deadline alerts
- Approval requests

### 12. Settings
- Workspace configuration
- Role management
- User preferences

---

## Database Schema (MVP)

### Core Tables
```
users
workspaces
projects
project_members
workflows
workflow_stages
tasks
task_checklists
task_comments
calendar_events
notes
files
notifications
activity_logs
ai_sessions
ai_histories
labels
settings
```

---

## Core User Flow

```
Login 
  → Create Project 
  → Choose: Blank / Template / AI Generate
  → Build Workflow 
  → AI Review (optional) 
  → Generate Tasks 
  → Assign Members 
  → View in Calendar 
  → Focus View for execution 
  → Mark Complete 
  → Dashboard updates
```

---

## Success Criteria

| Criterion | Measurement |
|-----------|-------------|
| Usability | Digunakan oleh agency kecil |
| Completeness | Semua aktivitas inti berjalan dalam satu aplikasi |
| Flexibility | AI bersifat opsional, manual workflow fully functional |

---

## Out of Scope (Post-MVP)
- Advanced AI features
- Marketplace integration
- Third-party integrations
- Advanced analytics
- Mobile applications
- Offline mode

---

## Technical Requirements

### Backend
- Laravel 11+
- PostgreSQL
- RESTful API
- JWT Authentication

### Frontend
- React 18+
- TypeScript
- Vite
- Tailwind CSS
- TanStack Query

### Infrastructure
- Docker support
- CI/CD pipeline
- Automated testing

---

*Document Version: 1.0*
*Last Updated: 2026-07-25*
*Status: Active*