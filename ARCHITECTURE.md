# FocusFlow Architecture Decision Log

## Context
FocusFlow is a multi-tenant team productivity SaaS. The goal is to create a robust, production-ready system to showcase Laravel and system architecture skills.

## Architecture Decisions

### 1. Multi-Tenancy Strategy
**Decision:** We will use a single-database multi-tenancy approach, scoping models using a `workspace_id` column. We will utilize a custom global `WorkspaceScope` middleware and Eloquent scopes.
**Rationale:** Database-per-tenant adds significant operational overhead (migrations, connections). For a team productivity tool, single-database with foreign keys (`workspace_id`) is standard, performant, and scales well until very large data volumes are reached.

### 2. API Versioning
**Decision:** All API routes will be versioned at the URL level (e.g., `/api/v1/workspaces/...`).
**Rationale:** URL versioning is explicit, easily testable in tools like Postman, and standard in Laravel. It allows us to iterate on V2 endpoints later without breaking backward compatibility for external integrations.

### 3. Real-Time Communication
**Decision:** We will use Laravel Reverb for WebSockets instead of external services like Pusher.
**Rationale:** Reverb provides a native, high-performance WebSocket server written in PHP that integrates seamlessly with Laravel's broadcasting events, reducing third-party dependencies and costs.

### 4. Background Processing
**Decision:** Redis + Laravel Horizon will manage background queues.
**Rationale:** Redis provides fast, reliable queue storage. Horizon offers a dashboard and code-driven configuration for queue workers, ensuring observability into background jobs (e.g., sending emails, processing Stripe webhooks).

### 5. Authentication
**Decision:** We will use Laravel Sanctum for API token authentication.
**Rationale:** Sanctum is lightweight and built specifically for SPA and simple API authentication, which perfectly matches our architecture of an API serving a frontend SPA.
