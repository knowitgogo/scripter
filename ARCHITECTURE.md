# Script Manager — Architecture

> **Version:** 1.0  
> **Status:** Approved for implementation  
> **Last updated:** 2026-06-08

This document defines the monorepo architecture for the Widget Marketplace Platform across four repositories: **backend**, **customer-portal**, **admin-portal**, and **widget-sdk**. It is the authoritative reference for layering, data flow, cross-cutting concerns, and testing.

---

## Files in This Change

| Action   | Path              |
|----------|-------------------|
| Create   | `ARCHITECTURE.md` |
| Create   | `backend/`        |

See `backend/README.md` for Laravel project conventions and setup.

---

## 1. Executive Summary

The platform is an enterprise widget marketplace supporting **embedded** and **hosted** widget runtimes, analytics, usage-based billing, and marketplace distribution. A single monorepo contains all deployable artifacts and shared documentation.

```
scriptmanager/
├── backend/              # Laravel 13 API (source of truth)
├── customer-portal/      # React 19 — customer-facing SPA
├── admin-portal/         # React 19 — internal operations SPA
├── widget-sdk/           # TypeScript + Vite — embeddable runtime
├── infrastructure/       # Deployment, Supervisor, cron, Apache configs
└── docs/                 # OpenAPI exports, runbooks, ADRs
```

**Core principles**

| Principle | Rule |
|-----------|------|
| Thin controllers | Controllers delegate immediately; no business logic |
| Services own logic | All domain rules, orchestration, and side effects live in Services |
| Repositories own persistence | All database and external-store access lives in Repositories |
| DTOs at boundaries | No Eloquent models or raw arrays cross layer boundaries |
| UUIDs externally | Integer `id` is internal only; `uuid` is the public identifier |
| Validation at the edge | HTTP input validated via Form Requests before Services |
| Contract-first API | OpenAPI spec generated from backend and consumed by portals/SDK |
| Testability | PHPUnit for backend; Vitest/RTL for frontends; contract tests for API |

---

## 2. Repository Definitions

### 2.1 `backend/` — Laravel 13 API

**Role:** Single source of truth for authentication, authorization, domain data, analytics ingestion, billing, and widget configuration.

**Tech stack:** PHP 8.3+, Laravel 13, PHPUnit, Laravel Sanctum/JWT, Laravel Queue, OpenAPI (e.g. `darkaonline/l5-swagger` or equivalent).

**Deployment:** Linux, Apache, PHP-FPM, Supervisor (queue workers), cron (aggregations).

#### Directory layout

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/          # Thin; route → Form Request → DTO → Service
│   │   ├── Requests/             # Form Requests (validation + authorization hooks)
│   │   ├── Resources/            # API response transformers (optional; prefer DTO → array)
│   │   └── Middleware/
│   ├── DTOs/
│   │   ├── Auth/
│   │   ├── Website/
│   │   ├── Widget/
│   │   ├── Analytics/
│   │   └── Billing/
│   ├── Services/                 # Business logic; depends on Repositories + Events
│   ├── Repositories/
│   │   ├── Contracts/            # Interfaces (SOLID: depend on abstractions)
│   │   └── Eloquent/             # Concrete implementations
│   ├── Models/                   # Eloquent; never returned past Repository boundary
│   ├── Events/                   # Domain events (past tense: WebsiteCreated)
│   ├── Listeners/                # Sync side effects
│   ├── Jobs/                     # Async queue work
│   ├── Policies/                 # Authorization rules referenced by Form Requests
│   ├── Enums/
│   └── Support/                  # UUID helpers, response envelope, exceptions
├── database/
│   ├── migrations/
│   ├── factories/
│   └── seeders/
├── routes/
│   ├── api.php                   # Versioned: /api/v1/...
│   └── channels.php
├── tests/
│   ├── Unit/                     # Services, DTOs, pure helpers
│   ├── Feature/                # HTTP + DB integration
│   └── Contract/               # OpenAPI response shape assertions
└── openapi/
    └── openapi.yaml              # Generated artifact (committed or CI artifact)
```

#### Request flow (mandatory)

```
HTTP Request
    → Middleware (auth, rate limit, CORS)
    → Controller::__invoke() or action method
    → Form Request (validate + authorize)
    → Input DTO::fromRequest($request)
    → Service::execute(InputDTO): OutputDTO
        → Repository (read/write Models)
        → Event::dispatch() / Job::dispatch()
    → Response envelope { success, data, message, errors }
```

**Controllers MUST NOT:**

- Query Eloquent directly
- Contain `if/else` business branching beyond HTTP status mapping
- Transform domain data (use DTOs or dedicated mappers)

**Example controller shape:**

```php
public function store(CreateWebsiteRequest $request, WebsiteService $service): JsonResponse
{
    $dto = CreateWebsiteDTO::fromRequest($request);
    $result = $service->create($dto);

    return ApiResponse::created($result->toArray());
}
```

#### Layer responsibilities

| Layer | Responsibility | May depend on |
|-------|----------------|---------------|
| **Controller** | HTTP I/O, status codes | Form Request, Service, ApiResponse |
| **Form Request** | Input validation, auth gate | Policies, Rules |
| **DTO** | Immutable data carriers; `fromRequest`, `fromModel`, `toArray` | Nothing domain-heavy |
| **Service** | Business rules, transactions, event dispatch | Repository contracts, Events, Jobs, other Services |
| **Repository** | CRUD, queries, aggregates | Models, Query Builder |
| **Model** | Table mapping, casts, relationships | — |

#### Domain modules (bounded contexts)

| Module | Services (examples) | Repositories | Key DTOs |
|--------|---------------------|--------------|----------|
| **Auth** | `RegisterService`, `LoginService`, `TokenRefreshService` | `UserRepository` | `RegisterDTO`, `LoginDTO`, `AuthTokenDTO` |
| **Website** | `WebsiteService`, `WebsiteTagService` | `WebsiteRepository`, `TagRepository` | `CreateWebsiteDTO`, `WebsiteDTO` |
| **Widget** | `WidgetCatalogService`, `WebsiteWidgetService`, `WidgetKeyService` | `WidgetRepository`, `WebsiteWidgetRepository`, `WidgetKeyRepository` | `InstallWidgetDTO`, `WidgetKeyDTO` |
| **Analytics** | `EventIngestionService`, `AggregationService`, `DashboardService` | `AnalyticsEventRepository`, `AggregationRepository` | `TrackEventDTO`, `DashboardQueryDTO` |
| **Billing** | `UsageService`, `SubscriptionService`, `CheckoutService` | `PlanRepository`, `SubscriptionRepository`, `PaymentRepository` | `CheckoutDTO`, `UsageSummaryDTO` |
| **Admin** | `UserManagementService`, `AuditLogService` | `UserRepository`, `AuditLogRepository` | `AdminUserDTO`, `AuditLogQueryDTO` |

#### UUID policy

- Every externally addressable entity exposes `uuid` (CHAR(36), indexed unique).
- Routes use `{uuid}` route model binding via custom resolution in Repositories.
- API responses never include internal integer `id`.
- Foreign keys remain integer internally for join performance.

#### API response envelope

All JSON responses follow the contract defined in `API_CONTRACTS_v3`:

```json
{
  "success": true,
  "data": {},
  "message": "Optional human-readable message",
  "errors": []
}
```

HTTP status codes map failures; `errors` carries field-level validation detail.

#### OpenAPI

- Annotate Controllers or use attribute-based schema definitions.
- Generate `openapi/openapi.yaml` on every release and in CI.
- Portals and SDK run contract-diff checks against this artifact.
- Group tags: `Auth`, `Websites`, `Widgets`, `Analytics`, `Billing`, `Admin`.

---

### 2.2 `customer-portal/` — React 19 SPA

**Role:** Customer self-service — register, manage websites, install widgets, configure themes, generate keys, view analytics, manage billing.

**Tech stack:** React 19, TypeScript, Vite, React Query (TanStack Query), Zustand, ShadCN UI, TanStack Table, Recharts.

#### Directory layout

```
customer-portal/
├── src/
│   ├── api/                      # Typed API client (generated from OpenAPI)
│   ├── types/                    # Frontend DTOs mirroring API shapes
│   ├── hooks/                    # useWebsites, useAnalytics, etc.
│   ├── stores/                   # Zustand — UI state only (not server cache)
│   ├── components/
│   │   ├── ui/                   # ShadCN primitives
│   │   └── features/             # Feature-scoped components
│   ├── pages/
│   ├── lib/                      # Auth token storage, env, formatters
│   └── routes/
├── tests/
│   ├── unit/
│   └── integration/              # MSW + React Testing Library
└── openapi/                      # Symlink or copy of backend openapi.yaml
```

#### Architectural rules

| Concern | Approach |
|---------|----------|
| Server state | React Query — cache, invalidate, optimistic updates |
| UI state | Zustand — modals, filters, wizard steps |
| API types | Generated from OpenAPI (`openapi-typescript` or `orval`) |
| Auth | JWT in memory + httpOnly refresh pattern; attach `Authorization` header |
| Errors | Normalize API envelope to typed `ApiError` |
| Routing | React Router; protected routes check auth store |

**Customer portal does not contain business logic duplicated from backend.** Validation is UX-only (immediate feedback); authoritative validation remains in Form Requests.

#### Feature map

| Feature | Pages | API dependencies |
|---------|-------|------------------|
| Auth | Login, Register | `POST /auth/login`, `POST /auth/register` |
| Websites | List, Create, Detail | `GET/POST /websites` |
| Widgets | Marketplace, Install, Config | `POST /website-widgets`, widget catalog |
| Keys | Generate, Revoke | `POST /widget-keys` |
| Embed | Copy script snippet | Static template + `uuid` + key |
| Analytics | Dashboard, Events | `GET /analytics/dashboard` |
| Billing | Plans, Checkout | `POST /billing/checkout` |

---

### 2.3 `admin-portal/` — React 19 SPA

**Role:** Internal operations — user management, website oversight, widget marketplace publishing, analytics oversight, billing administration, audit and runtime logs.

**Tech stack:** Same as customer-portal (shared component patterns; separate deployable artifact).

#### Directory layout

```
admin-portal/
├── src/
│   ├── api/
│   ├── types/
│   ├── hooks/
│   ├── stores/
│   ├── components/
│   │   ├── ui/
│   │   └── features/
│   ├── pages/
│   └── routes/
└── tests/
```

#### Authorization

- Same JWT flow; token carries `role` claims (`admin`, `super_admin`).
- Backend Policies enforce role checks; admin portal hides UI for unauthorized actions but never relies on UI-only security.

#### Feature map

| Feature | Capability |
|---------|------------|
| Users | List, suspend, impersonate (audit-logged) |
| Websites | Cross-tenant view, status overrides |
| Widgets | CRUD, version publish, deprecate |
| Analytics | Platform-wide dashboards |
| Billing | Subscription overrides, usage inspection |
| Audit | Searchable audit log viewer |
| Runtime | Widget error / load logs |

---

### 2.4 `widget-sdk/` — TypeScript Runtime Library

**Role:** Shared runtime engine for embedded and hosted widgets — initialization, theme application, event tracking, version resolution.

**Tech stack:** TypeScript, Vite Library Mode, Vitest.

#### Directory layout

```
widget-sdk/
├── src/
│   ├── core/                     # Bootstrap, lifecycle, config resolver
│   ├── modes/
│   │   ├── embedded/             # Script-tag injection
│   │   └── hosted/               # Standalone page runtime
│   ├── theme/                    # Branding / CSS variable engine
│   ├── analytics/                # Event batching → POST /widget/event
│   ├── api/                      # Typed client for widget endpoints
│   └── types/                    # Public SDK interfaces
├── dist/                         # CDN-deployable bundles (versioned)
└── tests/
    ├── unit/
    └── integration/              # Mock API server
```

#### Public API surface

```typescript
interface WidgetRuntime {
  initialize(config: WidgetInitConfig): Promise<void>;
  track(event: AnalyticsEvent): void;
  destroy(): void;
}

interface WidgetInitConfig {
  websiteWidgetUuid: string;
  widgetKey: string;
  mode: 'embedded' | 'hosted';
  container?: HTMLElement;
  theme?: ThemeOverrides;
}
```

#### Runtime flow

```
Script load / Hosted page
    → POST /widget/initialize (key + uuid validation)
    → Receive widget config + version manifest
    → Load versioned asset from CDN (Azure Blob)
    → Apply theme engine
    → Mount widget UI
    → Analytics batcher → POST /widget/event (queued server-side)
```

#### SDK rules

- No secrets in bundle; only public widget keys.
- Semantic versioning; CDN paths include major version.
- Backward-compatible init contract within major version.
- Analytics events buffered and flushed with retry + exponential backoff.

---

## 3. Cross-Cutting Concerns

### 3.1 Events (backend)

Domain events decouple side effects from core Service flows.

| Event | Dispatched by | Listeners / Jobs |
|-------|---------------|------------------|
| `UserRegistered` | `RegisterService` | Send welcome email (Job) |
| `WebsiteCreated` | `WebsiteService` | Audit log (Listener) |
| `WidgetInstalled` | `WebsiteWidgetService` | Audit log, cache invalidation |
| `WidgetKeyGenerated` | `WidgetKeyService` | Audit log |
| `AnalyticsEventReceived` | `EventIngestionService` | `PersistAnalyticsEventJob` |
| `SubscriptionActivated` | `SubscriptionService` | Update usage limits cache |
| `PaymentCompleted` | `CheckoutService` | Activate subscription |

**Convention:** Events are immutable DTOs in `app/Events/`. Listeners stay thin and delegate to Services or Jobs.

### 3.2 Queues

**Initial driver:** Database queue (`jobs` table).  
**Future:** Redis queue when throughput requires it (no Service signature changes).

| Job | Queue | Purpose |
|-----|-------|---------|
| `PersistAnalyticsEventJob` | `analytics` | Write high-volume events |
| `AggregateDailyAnalyticsJob` | `analytics` | Roll up events by widget/date |
| `AggregateMonthlyUsageJob` | `billing` | Usage totals for plan enforcement |
| `ProcessStripeWebhookJob` | `billing` | Idempotent payment handling |
| `SendTransactionalEmailJob` | `default` | Async email |
| `InvalidateWidgetConfigCacheJob` | `default` | Cache bust after config change |

**Worker config:** Supervisor runs `php artisan queue:work --queue=default,analytics,billing`.

**Retry policy:** 3 attempts, exponential backoff; failed jobs to `failed_jobs` with alerting.

### 3.3 Caching

**Initial driver:** File cache (Laravel default) with database cache table for shared state.  
**Future:** Redis for widget config hot path.

| Key pattern | TTL | Invalidation |
|-------------|-----|--------------|
| `widget:config:{website_widget_uuid}` | 15 min | `WidgetInstalled`, config update |
| `widget:catalog:published` | 1 hour | Admin publish/deprecate |
| `user:permissions:{user_uuid}` | 30 min | Role change |
| `analytics:dashboard:{website_uuid}:{period}` | 5 min | After daily aggregation |
| `plan:limits:{user_uuid}` | 1 hour | Subscription change |

**Rules:**

- Repositories do not cache directly; `CacheableRepository` decorator or Service-level cache-aside.
- Cache keys always use public UUIDs, never integer IDs.
- Stampede protection via `Cache::remember` with lock where needed.

### 3.4 Analytics pipeline

```
Widget SDK
    → POST /widget/event (API, rate-limited, key validated)
    → EventIngestionService (sync validate + enqueue)
    → PersistAnalyticsEventJob (async)
    → analytics_events table
    → Cron: AggregateDailyAnalyticsJob
    → aggregation tables (widget_id, date, event_type, count)
    → GET /analytics/dashboard (DashboardService reads aggregates)
```

### 3.5 Billing pipeline

```
Analytics aggregates
    → AggregateMonthlyUsageJob
    → UsageService compares against plan.monthly_load_limit
    → SubscriptionService status checks
    → POST /billing/checkout → Stripe Checkout Session
    → Stripe webhook → ProcessStripeWebhookJob
    → payments + subscriptions tables updated
```

### 3.6 Security

| Control | Implementation |
|---------|----------------|
| Authentication | JWT (access + refresh tokens) |
| Authorization | Laravel Policies + role_id |
| Rate limiting | Per-route throttle middleware |
| Widget keys | Hashed at rest; domain validation on initialize |
| Audit | All admin mutations and auth events logged |
| Input | Form Requests + parameterized queries only |

### 3.7 Storage

**Azure Blob Storage** for widget version assets, exports, and CDN origin. Backend issues SAS URLs; SDK loads versioned JS/CSS bundles from CDN.

---

## 4. Database Overview

Managed **Azure Database for MySQL**. Schema authority: `DATABASE_DESIGN_v3`.

| Table | Public UUID | Notes |
|-------|-------------|-------|
| `users` | yes | `role_id` FK |
| `websites` | yes | `user_id` FK |
| `widgets` | yes | Marketplace catalog |
| `widget_versions` | yes | Versioned assets |
| `website_widgets` | yes | Installation + `configuration_json` |
| `widget_keys` | no (key is credential) | `website_widget_id` FK |
| `analytics_events` | no (high volume) | Composite index: widget, date, event_type |
| `plans` | yes | `monthly_load_limit`, price |
| `subscriptions` | yes | `user_id`, `plan_id`, status |
| `payments` | yes | Stripe `transaction_id` |
| `audit_logs` | yes | Polymorphic or typed reference |

**Indexing:** UUID unique indexes; all FKs indexed; analytics composite indexes per design doc.

---

## 5. API Surface (v1)

Base path: `/api/v1`

| Method | Path | Auth | Service |
|--------|------|------|---------|
| POST | `/auth/register` | Public | `RegisterService` |
| POST | `/auth/login` | Public | `LoginService` |
| GET | `/websites` | Customer | `WebsiteService` |
| POST | `/websites` | Customer | `WebsiteService` |
| POST | `/website-widgets` | Customer | `WebsiteWidgetService` |
| POST | `/widget-keys` | Customer | `WidgetKeyService` |
| POST | `/widget/initialize` | Widget key | `WidgetRuntimeService` |
| POST | `/widget/event` | Widget key | `EventIngestionService` |
| GET | `/analytics/dashboard` | Customer | `DashboardService` |
| POST | `/billing/checkout` | Customer | `CheckoutService` |
| *Admin* | `/admin/...` | Admin | Admin Services |

Full request/response schemas: generated OpenAPI.

---

## 6. Testing Strategy

### 6.1 Backend (PHPUnit)

```
tests/
├── Unit/
│   └── Services/           # Mock repository contracts; assert business rules
├── Feature/
│   └── Http/               # Full stack: request → DB → response envelope
└── Contract/
    └── OpenApi/            # Assert responses match openapi.yaml components
```

| Test type | Scope | Tools |
|-----------|-------|-------|
| **Unit** | Services, DTOs, Enums | PHPUnit mocks for Repository interfaces |
| **Feature** | Controllers, Form Requests, Policies | `RefreshDatabase`, JWT test helpers |
| **Contract** | Response shapes vs OpenAPI | Custom assertion or `league/openapi-psr7-validator` |
| **Integration** | Repositories | SQLite/MySQL test DB |

**Requirements for every PR:**

- New Service methods → Unit tests covering success + failure paths
- New endpoints → Feature test asserting envelope, status, UUID exposure
- Schema changes → Contract test or OpenAPI snapshot update
- `composer test` (or `php artisan test`) passes with zero failures

**Example test matrix (WebsiteService):**

| Case | Type | Assert |
|------|------|--------|
| Create website with valid DTO | Unit | Returns `WebsiteDTO` with uuid |
| Duplicate domain for user | Unit | Throws `DomainConflictException` |
| POST /websites 201 | Feature | `success: true`, uuid in data |
| POST /websites 422 | Feature | `errors` array populated |
| Unauthenticated GET /websites | Feature | 401 |

### 6.2 Customer & Admin Portals

| Test type | Tools | Focus |
|-----------|-------|-------|
| Unit | Vitest | Hooks, stores, formatters |
| Component | RTL + MSW | Forms, tables, error states |
| E2E (optional CI) | Playwright | Critical flows: login → create website |

**MSW handlers** generated from same OpenAPI spec as production client.

### 6.3 Widget SDK

| Test type | Tools | Focus |
|-----------|-------|-------|
| Unit | Vitest | Theme engine, event batcher, config resolver |
| Integration | Vitest + mock server | Initialize flow, analytics flush |
| Bundle | Size limit CI check | dist bundle < budget |

### 6.4 CI pipeline (GitHub Actions)

```yaml
# Per-PR gates (conceptual)
backend:     composer install → php artisan test → openapi:generate → contract diff
customer:    npm ci → npm run test → npm run build
admin:       npm ci → npm run test → npm run build
widget-sdk:  npm ci → npm run test → npm run build
```

Deployment: SSH to Linux server per `DECISIONS.md`; Supervisor reload for workers.

---

## 7. Dependency Rules (SOLID)

```
Controllers  →  Services, Form Requests, DTOs
Services     →  Repository Contracts, Events, Jobs, other Services
Repositories →  Models
Models       →  (nothing upstream)
DTOs         →  (nothing upstream)
```

- **Single Responsibility:** One Service per use case (e.g. `CreateWebsiteService` or clear `WebsiteService::create` method group).
- **Open/Closed:** Extend via new Services/Events, not by modifying Controllers.
- **Liskov:** Repository implementations honor interface contracts.
- **Interface Segregation:** Small repository interfaces per aggregate (`WebsiteRepository`, not `EverythingRepository`).
- **Dependency Inversion:** Services type-hint `Contracts\WebsiteRepositoryInterface`, bound in `AppServiceProvider`.

---

## 8. Error Handling

| Exception | HTTP | Envelope |
|-----------|------|----------|
| `ValidationException` | 422 | `success: false`, `errors` populated |
| `AuthenticationException` | 401 | `success: false`, message |
| `AuthorizationException` | 403 | `success: false`, message |
| `ModelNotFoundException` | 404 | `success: false` (uuid not found) |
| `DomainException` | 409/422 | Business rule violation message |
| Unhandled | 500 | Generic message; logged with trace ID |

---

## 9. Deployment Topology

```
                    ┌─────────────────┐
                    │  GitHub Actions │
                    └────────┬────────┘
                             │ SSH deploy
         ┌───────────────────┼───────────────────┐
         ▼                   ▼                   ▼
   ┌───────────┐      ┌────────────┐     ┌──────────────┐
   │  Apache   │      │  PHP-FPM   │     │  Supervisor  │
   │  vhosts   │      │  Laravel   │     │  queue workers│
   └─────┬─────┘      └──────┬─────┘     └──────────────┘
         │                   │
         │            ┌──────┴──────┐
         │            ▼             ▼
         │     ┌────────────┐  ┌──────────┐
         │     │ Azure MySQL│  │ Azure Blob│
         │     └────────────┘  └────┬─────┘
         │                          │ CDN
   ┌─────┴─────┐              ┌─────┴─────┐
   │ React SPAs│              │ widget-sdk│
   │ (static)  │              │ bundles   │
   └───────────┘              └───────────┘
```

**Cron (scheduler):** Daily analytics aggregation (02:00 UTC), monthly usage rollup (1st of month), subscription renewal checks.

---

## 10. Future Roadmap (Out of Scope v1)

Documented for architectural continuity; do not implement until ADR approved:

- Organizations (multi-tenant hierarchy)
- Third-party widget developer portal
- Public REST API with OAuth2
- White-label hosted widgets
- Redis cache + Redis queue migration
- Marketplace revenue sharing

---

## 11. Related Documents

| Document | Purpose |
|----------|---------|
| `SYSTEM_ARCHITECTURE_v1.docx` | Original system overview |
| `DATABASE_DESIGN_v3.docx` | Schema authority |
| `API_CONTRACTS_v3.docx` | Endpoint and envelope contract |
| `PRODUCT_REQUIREMENTS_v3.docx` | User stories and roadmap |
| `DECISIONS.docx` | ADR log (monorepo, JWT, queues, etc.) |

---

## 12. PR Checklist

Before merge, verify:

- [ ] Controllers contain no business logic
- [ ] New logic in Services; new queries in Repositories
- [ ] DTOs used between Controller ↔ Service ↔ Repository
- [ ] Form Requests for all mutating endpoints
- [ ] Public identifiers are UUIDs only
- [ ] PHPUnit tests added and passing
- [ ] OpenAPI spec regenerated
- [ ] No unrelated modules modified
- [ ] No breaking API changes without version bump
