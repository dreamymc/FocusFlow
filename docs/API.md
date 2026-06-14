# FocusFlow API Documentation

## Authentication

- **POST** `/api/v1/login`
  - Request: `{ "email": "user@example.com", "password": "secret" }`
  - Response: `{ "token": "<sanctum-token>", "expires_at": "2026-07-14T00:00:00Z" }`
  - Uses **Laravel Sanctum** token guard.

- **POST** `/api/v1/logout`
  - Header: `Authorization: Bearer <token>`
  - Revokes the token.

## Workspaces

- **GET** `/api/v1/workspaces`
  - Returns a paginated list of workspaces the authenticated user belongs to.

- **POST** `/api/v1/workspaces`
  - Request: `{ "name": "Acme Corp" }`
  - Creates a new workspace; the creator becomes the **admin**.

- **GET** `/api/v1/workspaces/{workspace}`
  - Returns workspace details, members, and roles.

- **PUT** `/api/v1/workspaces/{workspace}`
  - Update workspace name or other mutable attributes.

- **DELETE** `/api/v1/workspaces/{workspace}`
  - Only admin can delete; removes all related projects/tasks.

- **POST** `/api/v1/workspaces/{workspace}/invite`
  - Request: `{ "email": "new@member.com", "role": "member" }`
  - Sends an invitation email; recipient must accept via `/api/v1/invitations/{token}/accept`.

## Projects (Nested under Workspace)

- **GET** `/api/v1/workspaces/{workspace}/projects`
  - List projects for a workspace.

- **POST** `/api/v1/workspaces/{workspace}/projects`
  - Request: `{ "name": "New Project" }`

- **GET** `/api/v1/workspaces/{workspace}/projects/{project}`
  - Project details.

- **PUT** `/api/v1/workspaces/{workspace}/projects/{project}`
  - Update project name, description, etc.

- **DELETE** `/api/v1/workspaces/{workspace}/projects/{project}`
  - Deletes project and all its tasks.

## Tasks (Nested under Project)

- **GET** `/api/v1/workspaces/{workspace}/projects/{project}/tasks`
  - Supports filtering & sorting:
    - `?status=done`
    - `?assignee=me`
    - `?sort=priority`

- **POST** `/api/v1/workspaces/{workspace}/projects/{project}/tasks`
  - Request example:
    ```json
    {
      "title": "Design landing page",
      "description": "Create high‑fidelity mockups",
      "priority": "high",
      "status": "todo",
      "assignee_id": 5
    }
    ```

- **GET** `/api/v1/workspaces/{workspace}/projects/{project}/tasks/{task}`
  - Returns the task with related `project` and `workspace` data.

- **PUT** `/api/v1/workspaces/{workspace}/projects/{project}/tasks/{task}`
  - Update any mutable field (title, description, status, priority, assignee).

- **DELETE** `/api/v1/workspaces/{workspace}/projects/{project}/tasks/{task}`
  - Removes the task.

- **POST** `/api/v1/workspaces/{workspace}/projects/{project}/tasks/{task}/move`
  - Request: `{ "status": "in_progress" }`
  - Triggers the `TaskMoved` event, broadcasting to the `workspace.{id}` private channel.

## Real‑Time Channels (WebSockets)

- **Private** `workspace.{workspaceId}` – members receive notifications for:
  - `TaskMoved`
  - `TaskAssigned`
  - `TaskCommented`

- **Presence** `task.{taskId}` – shows a list of users currently viewing a task (payload includes `id`, `name`, `email`).

All endpoints are protected by **role‑based policies** (`admin`, `member`, `viewer`).

---

## Error Responses

All API errors follow a consistent JSON envelope:
```json
{
  "error": {
    "code": 403,
    "message": "Forbidden",
    "details": []
  }
}
```
Validation errors return HTTP 422 with an `errors` object.

---

## Rate Limiting

- **60 requests/minute** per token on all `/api/v1/*` routes.
- Auth endpoints have a stricter **5 attempts/60 seconds** limit on login to protect against brute‑force.
