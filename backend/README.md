# Script Manager — Backend API

Laravel 13 **API-only** application for the Widget Marketplace Platform. See [ARCHITECTURE.md](../ARCHITECTURE.md) for the full system design.

## API-only configuration

- Web routing is disabled; all HTTP endpoints live under `/api/v1`.
- `ForceJsonResponse` middleware forces JSON negotiation on API routes.
- `prefersJsonResponses()` ensures JSON error pages for `/up` and broad Accept headers.
- All exceptions on API routes render the standard envelope via `ApiExceptionRenderer`.

## Layer conventions

```
HTTP Request
  → Middleware
  → Controller (thin)
  → Form Request (validation)
  → DTO
  → Service (business logic)
  → Repository
  → Model
  → ApiResponse envelope
```

| Layer | Location | Responsibility |
|-------|----------|----------------|
| Controllers | `app/Http/Controllers/Api/V1/` | HTTP I/O only |
| Form Requests | `app/Http/Requests/{Domain}/` | Validation and authorization |
| DTOs | `app/DTOs/{Domain}/` | Immutable data between layers |
| Services | `app/Services/{Domain}/` | Business rules and orchestration |
| Repositories | `app/Repositories/` | Database access |
| Models | `app/Models/` | Eloquent mapping (internal) |

**Rules**

- Controllers contain no business logic.
- Services never return Eloquent models; use DTOs.
- Public identifiers are UUIDs — see [UUID strategy](#uuid-strategy) below.
- API responses use `App\Support\ApiResponse` envelope via `BaseController` helpers.
- Repository bindings go in `app/Providers/RepositoryServiceProvider.php`.

## Repository pattern

Persistence repositories follow a contract-first hierarchy:

```
RepositoryInterface                    # root marker
└── EloquentRepositoryInterface        # CRUD by internal id
    └── UuidRepositoryInterface        # + findByUuid / findByUuidOrFail
```

| Class | Use for |
|-------|---------|
| `EloquentRepository` | Internal-id aggregates |
| `UuidEloquentRepository` | Public entities extending `PublicEntity` |
| `FindsByUuid` | Used internally by `UuidEloquentRepository` |

**Domain repository example:**

```php
final class WebsiteRepository extends UuidEloquentRepository implements WebsiteRepositoryInterface
{
    protected function model(): string
    {
        return Website::class;
    }
}
```

Bind the domain interface in `RepositoryServiceProvider`. Services receive the contract, map Models to DTOs, and never expose integer `id` values.

## Directory layout

```
app/
├── DTOs/{Auth,Website,Widget,Analytics,Billing}/
├── Services/{Auth,Website,Widget,Analytics,Billing,Admin}/
├── Repositories/{Contracts,Eloquent}/
├── Events/
├── Listeners/
├── Jobs/
├── Policies/
├── Enums/
├── Support/
├── Exceptions/
└── Http/
    ├── Controllers/Api/V1/   # BaseController + domain controllers
    ├── Requests/{Auth,Website,Widget,Analytics,Billing,Admin}/
    └── Resources/
openapi/openapi.yaml
tests/{Unit,Feature,Contract}/
```

## API versioning

Versioning is config-driven via `config/api.php`:

| Setting | Default | Purpose |
|---------|---------|---------|
| `api.prefix` | `api` | Global route prefix (`bootstrap/app.php`) |
| `api.default_version` | `v1` | Active API version |
| `api.supported_versions` | `v1` | Registered versions |
| `api.version_header` | `X-API-Version` | Response header on versioned routes |

**URL structure:**

```
/api/{version}/{resource}     # versioned domain endpoints
/api/openapi.yaml             # unversioned meta
/api/docs                     # unversioned Swagger UI
```

**Route registration:** `routes/api.php` loads `routes/api/{version}.php` for each supported version. Controllers live under `App\Http\Controllers\Api\{Version}`.

**Adding v2 later:**

1. Create `routes/api/v2.php`
2. Add `v2` to `API_SUPPORTED_VERSIONS` in `.env`
3. Add controllers under `App\Http\Controllers\Api\V2`
4. Extend `openapi/openapi.yaml` with v2 server entry

All versioned responses include the `X-API-Version` header via `SetApiVersionHeader` middleware.

| Endpoint | Description |
|----------|-------------|
| `POST /api/v1/auth/login` | Authenticate with email/password; returns JWT |
| `POST /api/v1/auth/logout` | Invalidate current JWT (requires Bearer token) |
| `GET /api/v1/health` | Liveness probe — process is running |
| `GET /api/v1/ready` | Readiness probe — database and cache are reachable |
| `GET /api/openapi.yaml` | Raw OpenAPI 3.1 specification (YAML) |
| `GET /api/docs` | Swagger UI documentation browser |
| `GET /up` | Laravel health probe |

## Exception handling

All API errors return the standard envelope (`success`, `data`, `message`, `errors`):

| Exception | HTTP | Message |
|-----------|------|---------|
| `ValidationException` | 422 | Validation message; `errors` populated |
| `AuthenticationException` | 401 | Unauthenticated. |
| `AuthorizationException` | 403 | Forbidden. |
| `ModelNotFoundException` | 404 | Resource not found. |
| `NotFoundHttpException` | 404 | Resource not found. |
| `MethodNotAllowedHttpException` | 405 | Method not allowed. |
| `DomainException` | configurable | Business rule message |
| Unhandled | 500 | Generic message; `X-Trace-Id` header logged |

Implementation: `app/Support/ApiExceptionRenderer.php`, registered in `bootstrap/app.php`.

## ApiResponse standard

All successful and error responses use the envelope defined in `app/Support/ApiResponse.php`:

```json
{
  "success": true,
  "data": {},
  "message": null,
  "errors": []
}
```

| Method | HTTP | Use case |
|--------|------|----------|
| `ApiResponse::success()` | 200 | Standard success |
| `ApiResponse::created()` | 201 | Resource created |
| `ApiResponse::accepted()` | 202 | Async job accepted |
| `ApiResponse::noContent()` | 204 | Delete / no body |
| `ApiResponse::error()` | 4xx/5xx | Failures |

Controllers extend `App\Http\Controllers\Api\V1\BaseController` and return via:

- `respondSuccess($data, $message)`
- `respondCreated($data, $message)`
- `respondAccepted($data, $message)`
- `respondNoContent()`
- `respondError($message, $errors, $status)`

`$data` accepts arrays or DTOs (`DataTransferObject` / `Arrayable`).

## DTO pattern

Immutable data carriers live in `app/DTOs/{Domain}/` and extend `DataTransferObject`.

```php
final class WebsiteDTO extends DataTransferObject
{
    use MapsFromRequest, MapsFromModel;

    public function __construct(
        public readonly string $uuid,
        public readonly string $name,
        public readonly string $status,
    ) {}

    protected function hiddenProperties(): array
    {
        return ['id'];
    }
}
```

| Capability | Mechanism |
|------------|-----------|
| Immutability | `public readonly` constructor properties |
| Serialization | `toArray()` / `jsonSerialize()` with nested DTO support |
| From input | `MapsFromRequest::fromRequest()` |
| From persistence | `MapsFromModel::fromModel()` (excludes integer `id`) |
| From array | `DataTransferObject::fromArray()` |

Services return DTOs to controllers; controllers pass DTOs to `BaseController` response helpers.

## UUID strategy

Public entities are identified by UUID in routes, DTOs, and API responses. Internal integer `id` values are never exposed.

| Component | Location | Purpose |
|-----------|----------|---------|
| Config | `config/uuids.php` | Registry of public entity tables |
| Generator | `app/Support/UuidGenerator.php` | Centralized UUID creation and validation |
| Model trait | `app/Models/Concerns/HasUuid.php` | Auto-assign, immutable, route binding |
| Model trait | `app/Models/Concerns/HidesInternalId.php` | Hide integer `id` from serialization |
| Base model | `app/Models/PublicEntity.php` | Extend for new domain models |
| Migration macro | `$table->publicUuid()` | Unique UUID column in migrations |
| Repository | `FindsByUuid` concern | Lookup by public identifier |
| Validation | `App\Rules\ValidUuid` | Form Request validation |

**Public entities:** users, websites, widgets, widget_versions, website_widgets, plans, subscriptions, payments, audit_logs.

**Excluded:** widget_keys (credential-based), analytics_events (high volume).

**Usage in migrations:**

```php
Schema::create('websites', function (Blueprint $table): void {
    $table->id();
    $table->publicUuid();
    // ...
});
```

**Usage in repositories:**

```php
final class WebsiteRepository extends EloquentRepository implements UuidRepositoryInterface
{
    use FindsByUuid;

    protected function model(): string
    {
        return Website::class;
    }
}
```

**Usage in Form Requests:**

```php
'website_uuid' => ['required', new ValidUuid],
```

## Audit event architecture

Audit logging is event-driven and decoupled from domain Services.

```
Domain Service
  → AuditDispatcher::dispatch(AuditEventInterface)
  → RecordAuditLog listener (thin)
  → AuditLogService
  → PersistAuditLogJob (async) or AuditLogRepository (sync)
  → audit_logs table
```

| Component | Location | Role |
|-----------|----------|------|
| Events | `app/Events/Audit/` | Immutable audit events |
| DTO | `app/DTOs/Audit/AuditLogEntryDTO.php` | Persistence payload |
| Service | `app/Services/Audit/AuditLogService.php` | Record orchestration |
| Dispatcher | `app/Services/Audit/AuditDispatcher.php` | Event bus entry point |
| Listener | `app/Listeners/RecordAuditLog.php` | Thin delegation |
| Job | `app/Jobs/PersistAuditLogJob.php` | Async persistence |
| Repository | `AuditLogRepositoryInterface` | Database access |

**Usage from a domain Service (future modules):**

```php
$this->auditDispatcher->dispatch(
    GenericAuditEvent::record(
        action: AuditAction::Created,
        subjectType: 'website',
        subjectUuid: $websiteDto->uuid,
        actorUuid: $actorUuid,
    ),
);
```

Configuration: `config/audit.php` (`AUDIT_ENABLED`, `AUDIT_ASYNC`).

## Redis, cache, and queues

Infrastructure settings live in `config/infrastructure.php`. Defaults use **database** cache and **database** queues per ADR; Redis is opt-in via `REDIS_ENABLED=true`.

| Component | Default | Redis mode |
|-----------|---------|------------|
| Cache | `CACHE_STORE=database` | `CACHE_STORE=redis` |
| Queue | `QUEUE_CONNECTION=database` | `QUEUE_CONNECTION=redis` |
| Failover | — | `CACHE_FAILOVER_STORES=redis,database,file` |

**Abstractions (use in Services, not facades directly):**

| Service | Repository | Purpose |
|---------|------------|---------|
| `CacheService` | `CacheRepositoryInterface` | Pattern-based cache-aside |
| `QueueService` | `QueueDispatcherInterface` | Dispatch to `default`, `analytics`, `billing` queues |

**Cache key patterns** (from architecture): `widget_config`, `widget_catalog`, `user_permissions`, `analytics_dashboard`, `plan_limits`.

**Supervisor worker command:**

```bash
php artisan queue:work --queue=default,analytics,billing --tries=3
```

## Roles domain model

The `roles` table defines authorization roles referenced by `users.role_id` and JWT claims.

| Column | Type | Notes |
|--------|------|-------|
| `id` | bigint | Internal primary key (hidden from API) |
| `uuid` | uuid | Public identifier |
| `name` | string | Display name (e.g. `Customer`) |
| `slug` | string | Canonical slug (`RoleSlug` enum): `customer`, `admin`, `super_admin` |
| `timestamps` | — | `created_at`, `updated_at` |

**Seeder:** `RoleSeeder` seeds all `RoleSlug` values idempotently via `firstOrCreate`.

**Factory:** `RoleFactory` with states `customer()`, `admin()`, `superAdmin()`.

**Tests:** `tests/Feature/Models/RoleModelTest.php`, `tests/Unit/Database/RoleSeederTest.php`, `tests/Unit/Database/RolesMigrationTest.php`, `tests/Unit/Enums/RoleSlugTest.php`.

## JWT authentication

JWT is provided by [`php-open-source-saver/jwt-auth`](https://github.com/PHP-Open-Source-Saver/jwt-auth) (v2.9+). Access and refresh lifetimes are config-driven.

| Setting | Env | Default | Purpose |
|---------|-----|---------|---------|
| Guard | `AUTH_GUARD` | `api` | Default auth guard |
| Secret | `JWT_SECRET` | — | HMAC signing key (`php artisan jwt:secret`) |
| Access TTL | `JWT_TTL` | `60` | Access token lifetime (minutes) |
| Refresh window | `JWT_REFRESH_TTL` | `20160` | Refresh window (minutes, 14 days) |
| Algorithm | `JWT_ALGO` | `HS256` | Signing algorithm |
| Blacklist | `JWT_BLACKLIST_ENABLED` | `true` | Invalidate tokens on logout |

**Setup:**

```bash
composer install
php artisan jwt:secret   # writes JWT_SECRET to .env
```

**Architecture:**

- `User` implements `JWTSubject`; `sub` claim is the public `uuid` (never internal `id`)
- Custom `role` claim via `App\Support\Auth\JwtClaimBuilder`
- `AuthTokenDTO` is the token response shape for `LoginService` / `TokenRefreshService`
- Protect routes with `auth:api` or `jwt.auth` middleware

**Tests:** `tests/Feature/Auth/JwtAuthenticationTest.php`, `tests/Unit/Config/JwtConfigTest.php`, `tests/Unit/Auth/JwtClaimBuilderTest.php`, `tests/Unit/Models/UserJwtSubjectTest.php`.

## Login endpoint

`POST /api/v1/auth/login` authenticates a user and returns `AuthTokenDTO`.

```
LoginRequest → LoginDTO → LoginService → UserRepository + JWT guard → AuthTokenDTO
```

| Component | Location |
|-----------|----------|
| Controller | `app/Http/Controllers/Api/V1/Auth/LoginController.php` |
| Form Request | `app/Http/Requests/Auth/LoginRequest.php` |
| DTO | `app/DTOs/Auth/LoginDTO.php` |
| Service | `app/Services/Auth/LoginService.php` |

**Rules:** only `active` users may log in; invalid credentials return 401; suspended/pending accounts return 403; successful logins update `last_login_at` and record an audit event.

**Tests:** `tests/Feature/Api/V1/Auth/LoginEndpointTest.php`, `tests/Unit/Services/Auth/LoginServiceTest.php`.

## Logout endpoint

`POST /api/v1/auth/logout` invalidates the caller's JWT (blacklist) and returns `LogoutResultDTO`.

```
LogoutRequest → LogoutService → JWT guard logout + audit → LogoutResultDTO
```

| Component | Location |
|-----------|----------|
| Controller | `app/Http/Controllers/Api/V1/Auth/LogoutController.php` |
| Form Request | `app/Http/Requests/Auth/LogoutRequest.php` |
| DTO | `app/DTOs/Auth/LogoutResultDTO.php` |
| Service | `app/Services/Auth/LogoutService.php` |

**Auth:** `auth:api` middleware required. Blacklisted tokens cannot be reused.

**Tests:** `tests/Feature/Api/V1/Auth/LogoutEndpointTest.php`, `tests/Unit/Services/Auth/LogoutServiceTest.php`.

## Role assignment service

`RoleAssignmentService` (`app/Services/Auth/RoleAssignmentService.php`) assigns a `RoleSlug` to a user identified by UUID.

```
AssignRoleDTO
  → RoleAssignmentService::assign()
  → UserRepository + RoleRepository (persistence)
  → CacheService::forget(user_permissions)
  → AuditDispatcher (role change audit)
  → UserDTO
```

| Component | Location |
|-----------|----------|
| Command DTO | `app/DTOs/Auth/AssignRoleDTO.php` |
| Response DTO | `app/DTOs/Auth/UserDTO.php`, `app/DTOs/Auth/RoleDTO.php` |
| Service | `app/Services/Auth/RoleAssignmentService.php` |
| Repositories | `UserRepositoryInterface`, `RoleRepositoryInterface` |

**Behaviour:** idempotent when the user already has the target role; clears `user_permissions` cache on change; records an `updated` audit event with `previous_role` and `new_role` metadata.

**Tests:** `tests/Unit/Services/Auth/RoleAssignmentServiceTest.php`, `tests/Unit/DTOs/Auth/UserDTOTest.php`.

## Users domain model

The `users` table stores platform accounts. Migrations are additive on the Laravel default schema.

| Column | Type | Notes |
|--------|------|-------|
| `id` | bigint | Internal primary key (hidden from API) |
| `uuid` | uuid | Public identifier |
| `role_id` | FK → `roles` | Authorization role |
| `name` | string | Display name |
| `email` | string | Unique login email |
| `password` | string | Hashed credential |
| `status` | string | `active`, `suspended`, or `pending` (`UserStatus` enum) |
| `last_login_at` | timestamp | Nullable last successful login |
| `timestamps` | — | `created_at`, `updated_at` |

**Related tables:** `roles` — see [Roles domain model](#roles-domain-model).

**Factories:** `UserFactory` (default customer role; states: `admin()`, `suspended()`, `pending()`, `unverified()`, `withLastLogin()`), `RoleFactory`.

**Tests:** `tests/Feature/Models/UserModelTest.php`, `tests/Feature/Models/RoleModelTest.php`, `tests/Unit/Database/RoleSeederTest.php`.

## OpenAPI

The contract lives at `openapi/openapi.yaml`. Extend this file as endpoints are added. Contract tests in `tests/Contract/` validate the spec artifact.

| Route | Description |
|-------|-------------|
| `GET /api/openapi.yaml` | Serves the YAML specification |
| `GET /api/docs` | Swagger UI browser (disable with `OPENAPI_UI_ENABLED=false`) |

Configuration: `config/openapi.php`. Services load the spec via `OpenApiSpecService` and `OpenApiSpecRepositoryInterface`.

**Workflow:** update `openapi/openapi.yaml` → verify with `php artisan test --filter=OpenApi` → browse `/api/docs`.

## Setup

```bash
composer install
cp .env.example .env   # if needed
php artisan key:generate
php artisan jwt:secret
php artisan migrate
```

## Testing

```bash
composer test
# or
php artisan test
```

| Suite | Path | Purpose |
|-------|------|---------|
| Unit | `tests/Unit/` | Support classes, DTOs, architecture |
| Feature | `tests/Feature/` | HTTP integration |
| Contract | `tests/Contract/` | OpenAPI spec validation |

## Adding a new domain module

1. Create DTOs in `app/DTOs/{Domain}/`.
2. Create repository contract in `app/Repositories/Contracts/`.
3. Create Eloquent repository in `app/Repositories/Eloquent/`.
4. Bind contract in `RepositoryServiceProvider`.
5. Create Service in `app/Services/{Domain}/`.
6. Create Form Request in `app/Http/Requests/{Domain}/`.
7. Create thin Controller in `app/Http/Controllers/Api/V1/`.
8. Register route in `routes/api.php`.
9. Update `openapi/openapi.yaml`.
10. Add Unit, Feature, and Contract tests.
