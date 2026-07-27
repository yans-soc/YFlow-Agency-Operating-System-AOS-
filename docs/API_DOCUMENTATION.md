# YFlow API Documentation

## Base URL
```
http://localhost:8000/api/v1
```

## Authentication
All authenticated endpoints require Bearer token in Authorization header:
```
Authorization: Bearer {token}
```

---

## Auth Endpoints

### POST /auth/register
Register new user and create workspace

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "workspace_name": "My Agency"
}
```

**Response:**
```json
{
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": {
      "id": "uuid-here",
      "name": "John Doe",
      "email": "john@example.com",
      "role": "admin"
    }
  }
}
```

---

### POST /auth/login
Authenticate user

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": {
      "id": "uuid-here",
      "name": "John Doe",
      "email": "john@example.com",
      "role": "admin"
    }
  }
}
```

---

### POST /auth/logout
Logout current user

**Response:** `204 No Content`

---

## Project Endpoints

### GET /projects
List all projects (paginated)

**Query Parameters:**
- `page` (integer): Page number
- `per_page` (integer): Items per page (default: 15)
- `status` (string): Filter by status (planning, in_progress, completed, on_hold)

**Response:**
```json
{
  "data": {
    "data": [
      {
        "id": "uuid",
        "name": "E-Commerce Redesign",
        "description": "Project description",
        "status": "in_progress",
        "start_date": "2024-01-01",
        "due_date": "2024-06-01",
        "created_at": "2024-01-01T00:00:00Z"
      }
    ],
    "meta": {
      "current_page": 1,
      "total": 10,
      "per_page": 15
    }
  }
}
```

---

### GET /projects/{id}
Get project details

**Response:**
```json
{
  "data": {
    "data": {
      "id": "uuid",
      "name": "E-Commerce Redesign",
      "description": "Project description",
      "status": "in_progress",
      "owner": {
        "id": "uuid",
        "name": "John Doe"
      },
      "members": [],
      "tasks_count": 25,
      "created_at": "2024-01-01T00:00:00Z"
    }
  }
}
```

---

### POST /projects
Create new project

**Request Body:**
```json
{
  "name": "New Project",
  "description": "Project description",
  "status": "planning",
  "start_date": "2024-01-01",
  "due_date": "2024-06-01"
}
```

**Response:**
```json
{
  "data": {
    "data": {
      "id": "uuid",
      "name": "New Project",
      "status": "planning",
      "created_at": "2024-01-01T00:00:00Z"
    }
  }
}
```

---

### PUT /projects/{id}
Update project

**Request Body:**
```json
{
  "name": "Updated Project Name",
  "description": "Updated description",
  "status": "in_progress"
}
```

---

### DELETE /projects/{id}
Delete project (soft delete)

**Response:** `204 No Content`

---

## Task Endpoints

### GET /tasks
List all tasks

**Query Parameters:**
- `project_id` (uuid): Filter by project
- `status` (string): Filter by status
- `priority` (string): Filter by priority
- `assigned_to` (uuid): Filter by assignee

**Response:**
```json
{
  "data": {
    "data": [
      {
        "id": "uuid",
        "title": "Task Title",
        "description": "Task description",
        "status": "pending",
        "priority": "high",
        "stage": {
          "id": "uuid",
          "name": "To Do"
        },
        "assignees": [],
        "due_date": "2024-02-01"
      }
    ]
  }
}
```

---

### POST /tasks
Create new task

**Request Body:**
```json
{
  "project_id": "uuid",
  "stage_id": "uuid",
  "title": "New Task",
  "description": "Task description",
  "priority": "medium",
  "estimated_hours": 8,
  "due_date": "2024-02-01",
  "assignees": ["uuid-1", "uuid-2"]
}
```

---

### PUT /tasks/{id}
Update task

**Request Body:**
```json
{
  "title": "Updated Task",
  "status": "in_progress",
  "stage_id": "uuid"
}
```

---

### DELETE /tasks/{id}
Delete task

**Response:** `204 No Content`

---

## Department Endpoints

### GET /departments
List all departments

**Response:**
```json
{
  "data": {
    "data": [
      {
        "id": "uuid",
        "name": "Design",
        "description": "Creative team",
        "people_count": 5
      }
    ]
  }
}
```

---

### POST /departments
Create department

**Request Body:**
```json
{
  "name": "Marketing",
  "description": "Marketing team"
}
```

---

## People Endpoints

### GET /people
List all people

**Query Parameters:**
- `department_id` (uuid): Filter by department
- `status` (string): Filter by status

**Response:**
```json
{
  "data": {
    "data": [
      {
        "id": "uuid",
        "name": "John Doe",
        "email": "john@example.com",
        "role": "member",
        "status": "active",
        "department": {
          "id": "uuid",
          "name": "Design"
        },
        "position": {
          "id": "uuid",
          "title": "Senior Designer"
        },
        "skills": []
      }
    ]
  }
}
```

---

### GET /people/{id}
Get person details

---

### PUT /people/{id}
Update person

**Request Body:**
```json
{
  "name": "Updated Name",
  "department_id": "uuid",
  "position_id": "uuid",
  "skill_ids": ["uuid-1", "uuid-2"]
}
```

---

## Note Endpoints

### GET /notes
List all notes

**Response:**
```json
{
  "data": {
    "data": [
      {
        "id": "uuid",
        "title": "Meeting Notes",
        "content": "Note content here",
        "is_pinned": true,
        "created_at": "2024-01-01T00:00:00Z"
      }
    ]
  }
}
```

---

### POST /notes
Create note

**Request Body:**
```json
{
  "title": "New Note",
  "content": "Note content",
  "project_id": "uuid",
  "is_pinned": false
}
```

---

## File Endpoints

### GET /files
List all files

---

### POST /files
Upload file

**Content-Type:** `multipart/form-data`

**Request Body:**
- `file` (file): The file to upload
- `project_id` (uuid, optional): Associate with project

---

## Calendar Event Endpoints

### GET /calendar-events
List calendar events

**Query Parameters:**
- `start_date` (date): Filter from date
- `end_date` (date): Filter to date

**Response:**
```json
{
  "data": {
    "data": [
      {
        "id": "uuid",
        "title": "Team Meeting",
        "description": "Weekly sync",
        "type": "meeting",
        "start_time": "2024-01-15T10:00:00Z",
        "end_time": "2024-01-15T11:00:00Z",
        "location": "Conference Room A"
      }
    ]
  }
}
```

---

### POST /calendar-events
Create event

**Request Body:**
```json
{
  "title": "New Meeting",
  "description": "Meeting description",
  "type": "meeting",
  "start_time": "2024-01-15T10:00:00Z",
  "end_time": "2024-01-15T11:00:00Z",
  "location": "Conference Room A",
  "project_id": "uuid"
}
```

---

## Notification Endpoints

### GET /notifications
List current user notifications

**Response:**
```json
{
  "data": {
    "data": [
      {
        "id": "uuid",
        "type": "task_assigned",
        "message": "You have been assigned to a task",
        "is_read": false,
        "created_at": "2024-01-01T00:00:00Z"
      }
    ]
  }
}
```

---

### POST /notifications/{id}/read
Mark notification as read

**Response:** `204 No Content`

---

### POST /notifications/read-all
Mark all notifications as read

**Response:** `204 No Content`

---

## Activity Endpoints

### GET /activities
List activity log

**Query Parameters:**
- `project_id` (uuid): Filter by project
- `user_id` (uuid): Filter by user

**Response:**
```json
{
  "data": {
    "data": [
      {
        "id": "uuid",
        "user": {
          "id": "uuid",
          "name": "John Doe"
        },
        "action": "created",
        "description": "Created new task",
        "subject_type": "App\\Models\\Task",
        "created_at": "2024-01-01T00:00:00Z"
      }
    ]
  }
}
```

---

## AI Session Endpoints

### GET /ai-sessions
List AI sessions

---

### POST /ai-sessions
Create AI session

**Request Body:**
```json
{
  "project_id": "uuid",
  "title": "Project Assistant"
}
```

---

### GET /ai-sessions/{id}/messages
Get session messages

---

### POST /ai-sessions/{id}/messages
Send message to AI

**Request Body:**
```json
{
  "content": "Help me plan this project"
}
```

**Response:**
```json
{
  "data": {
    "message": {
      "id": "uuid",
      "role": "assistant",
      "content": "Here's my suggestion..."
    }
  }
}
```

---

## Error Responses

### 400 Bad Request
```json
{
  "error": {
    "message": "Validation failed",
    "errors": {
      "email": ["The email field is required."]
    }
  }
}
```

### 401 Unauthorized
```json
{
  "error": {
    "message": "Unauthenticated."
  }
}
```

### 403 Forbidden
```json
{
  "error": {
    "message": "This action is unauthorized."
  }
}
```

### 404 Not Found
```json
{
  "error": {
    "message": "Resource not found."
  }
}
```

### 500 Internal Server Error
```json
{
  "error": {
    "message": "Internal server error."
  }
}