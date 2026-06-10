# Script Manager — Backend API

Laravel 13 **API-only** application for the Widget Marketplace Platform. See [ARCHITECTURE.md](../ARCHITECTURE.md) for the full system design and [docs/WIDGET_MARKETPLACE_ARCHITECTURE.md](../docs/WIDGET_MARKETPLACE_ARCHITECTURE.md) for the widget marketplace bounded context.

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
| `POST /api/v1/auth/register` | Register customer account; returns JWT |
| `POST /api/v1/auth/login` | Authenticate with email/password; returns JWT |
| `POST /api/v1/auth/refresh` | Issue new JWT within refresh window (requires Bearer token) |
| `POST /api/v1/auth/logout` | Invalidate current JWT (requires Bearer token) |
| `GET /api/v1/me` | Current authenticated user profile (requires Bearer token) |
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

**Public entities:** users, websites, widgets, widget_categories, widget_templates, widget_versions, website_widgets, plans, subscriptions, payments, audit_logs.

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
- `AuthTokenDTO` is the token response shape for `LoginService` and `TokenRefreshService`
- Protect routes with `auth:api` or `jwt.auth` middleware

**Tests:** `tests/Feature/Auth/JwtAuthenticationTest.php`, `tests/Unit/Config/JwtConfigTest.php`, `tests/Unit/Auth/JwtClaimBuilderTest.php`, `tests/Unit/Models/UserJwtSubjectTest.php`.

## Registration endpoint

`POST /api/v1/auth/register` creates a customer account and returns `AuthTokenDTO`.

```
RegisterRequest → RegisterDTO → RegisterService → UserRepository + RoleRepository → AuthTokenDTO
```

| Component | Location |
|-----------|----------|
| Controller | `app/Http/Controllers/Api/V1/Auth/RegisterController.php` |
| Form Request | `app/Http/Requests/Auth/RegisterRequest.php` |
| DTO | `app/DTOs/Auth/RegisterDTO.php` |
| Service | `app/Services/Auth/RegisterService.php` |

**Validation:** `name`, unique `email`, `password` (min 8, confirmed). New users receive the `customer` role and `active` status.

**Tests:** `tests/Feature/Api/V1/Auth/RegisterEndpointTest.php`, `tests/Unit/Services/Auth/RegisterServiceTest.php`.

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

## Refresh token endpoint

`POST /api/v1/auth/refresh` issues a new `AuthTokenDTO` when the caller presents a valid JWT within `JWT_REFRESH_TTL`.

```
RefreshTokenRequest → TokenRefreshService → JWT refresh + UserRepository status check → AuthTokenDTO
```

| Component | Location |
|-----------|----------|
| Controller | `app/Http/Controllers/Api/V1/Auth/RefreshTokenController.php` |
| Form Request | `app/Http/Requests/Auth/RefreshTokenRequest.php` |
| Service | `app/Services/Auth/TokenRefreshService.php` |

**Rules:** previous token is blacklisted; only `active` users receive a new token; suspended/pending accounts return 403.

**Tests:** `tests/Feature/Api/V1/Auth/RefreshTokenEndpointTest.php`, `tests/Unit/Services/Auth/TokenRefreshServiceTest.php`.

## Current user endpoint

`GET /api/v1/me` returns the authenticated user's `UserDTO` (UUID, profile, role).

```
CurrentUserRequest → CurrentUserService → UserRepository → UserDTO
```

| Component | Location |
|-----------|----------|
| Controller | `app/Http/Controllers/Api/V1/Auth/MeController.php` |
| Form Request | `app/Http/Requests/Auth/CurrentUserRequest.php` |
| Service | `app/Services/Auth/CurrentUserService.php` |

**Auth:** `auth:api` middleware required.

**Tests:** `tests/Feature/Api/V1/Auth/MeEndpointTest.php`, `tests/Unit/Services/Auth/CurrentUserServiceTest.php`.

## Websites domain

Customer websites are persisted as public UUID entities owned by a user.

```
users (uuid) ← websites.user_id (internal FK, hidden from API)
```

| Component | Location |
|-----------|----------|
| Migration | `database/migrations/2026_06_08_160000_create_websites_table.php` |
| Model | `app/Models/Website.php` extends `PublicEntity` |
| Status enum | `app/Enums/WebsiteStatus.php` (`active`, `inactive`, `suspended`) |
| Factory | `database/factories/WebsiteFactory.php` |
| Repository | `WebsiteRepositoryInterface` → `EloquentWebsiteRepository` |
| DTOs | `app/DTOs/Website/WebsiteDTO.php`, `CreateWebsiteDTO.php`, `UpdateWebsiteDTO.php` |
| Service | `app/Services/Website/WebsiteService.php` |
| Controllers | `app/Http/Controllers/Api/V1/Website/` |
| Form Requests | `app/Http/Requests/Website/` |

**Table:** `websites` — `uuid`, `user_id`, `name`, `url` (unique), `status`, timestamps.

**Relationships:** `Website` `belongsTo` `User`; `User` `hasMany` `Website`.

**Repository methods:** `findByUuid`, `findByUrl`, `listForUser`, `findByUuidForUser`, plus standard CRUD from `EloquentRepositoryInterface`.

Bind `WebsiteRepositoryInterface` in `RepositoryServiceProvider`. Services map `Website` models to `WebsiteDTO` before returning to controllers. `CreateWebsiteDTO` maps validated Form Request input.

```
CreateWebsiteDTO + User
  → WebsiteService::create()
  → WebsiteRepository (persist + url uniqueness)
  → AuditDispatcher (website.created)
  → WebsiteDTO

User → WebsiteService::listForUser(user, ListWebsitesQueryDTO?) → list<WebsiteDTO>
User + website uuid → WebsiteService::getForUser() → WebsiteDTO
User + website uuid → WebsiteService::update() → WebsiteDTO
User + website uuid → WebsiteService::delete()
```

**HTTP routes** (`routes/api/v1.php`):

| Method | Path | Permission |
|--------|------|------------|
| GET | `/websites` | `websites.view` | Optional `tag_uuids[]` query filter (AND semantics) |
| POST | `/websites` | `websites.manage` |
| GET | `/websites/{uuid}` | `websites.view` |
| PUT | `/websites/{uuid}` | `websites.manage` |
| DELETE | `/websites/{uuid}` | `websites.manage` |

**Tests:** Run the Website suite with `composer test:website`.

| Layer | Path |
|-------|------|
| CRUD flow | `tests/Feature/Website/WebsiteCrudFlowTest.php` |
| Endpoints | `tests/Feature/Api/V1/Website/*EndpointTest.php` |
| Authorization | `tests/Feature/Api/V1/Website/WebsiteAuthorizationEndpointTest.php` |
| OpenAPI contract | `tests/Contract/OpenApi/WebsiteOpenApiSpecTest.php` |
| Service | `tests/Unit/Services/Website/WebsiteServiceTest.php` |
| DTOs | `tests/Unit/DTOs/Website/` |
| Repository | `tests/Unit/Repositories/Eloquent/EloquentWebsiteRepositoryTest.php` |
| Model / migration | `tests/Feature/Models/WebsiteModelTest.php`, `tests/Unit/Database/WebsitesMigrationTest.php` |

OpenAPI paths and schemas: `openapi/openapi.yaml` (`/websites`, `/websites/{website}`).

## Tags domain

Reusable labels shared across websites via a many-to-many pivot.

```
websites ← website_tags → tags (uuid)
```

| Component | Location |
|-----------|----------|
| Migrations | `database/migrations/2026_06_08_170000_create_tags_table.php`, `2026_06_08_170001_create_website_tags_table.php` |
| Models | `app/Models/Tag.php`, `app/Models/WebsiteTag.php` (pivot) |
| Factory | `database/factories/TagFactory.php` |
| Repositories | `TagRepositoryInterface` → `EloquentTagRepository`; `WebsiteTagRepositoryInterface` → `EloquentWebsiteTagRepository` |
| DTOs | `app/DTOs/Tag/TagDTO.php`, `CreateTagDTO.php`, `UpdateTagDTO.php`, `app/DTOs/Website/WebsiteTagsDTO.php`, `SyncWebsiteTagsDTO.php` |
| Service | `app/Services/Tag/TagService.php`, `app/Services/Website/WebsiteTagService.php` |
| Controllers | `app/Http/Controllers/Api/V1/Tag/` |
| Form Requests | `app/Http/Requests/Tag/` |

**Tables:** `tags` — `uuid`, `name`, `slug` (unique), timestamps. `website_tags` — `website_id`, `tag_id` (unique pair), timestamps.

**Relationships:** `Tag` `belongsToMany` `Website`; `Website` `belongsToMany` `Tag` via `website_tags` using `WebsiteTag` pivot. The same tag row can be attached to multiple websites.

**Repository methods:** `TagRepositoryInterface` — `findBySlug`, `listOrderedByName`, UUID lookups. `WebsiteTagRepositoryInterface` — `attach`, `detach`, `sync`, `listTagsForWebsite`, `isAttached`.

Bind `TagRepositoryInterface` and `WebsiteTagRepositoryInterface` in `RepositoryServiceProvider`. `TagService` maps models to DTOs and owns attach/detach/sync. `WebsiteTagService` delegates to `TagService` for backward compatibility.

```
TagService::list() → list<TagDTO>
TagService::getByUuid(uuid) → TagDTO
TagService::getBySlug(slug) → TagDTO
TagService::listForWebsite(websiteUuid, user) → list<TagDTO>
TagService::attach(websiteUuid, tagUuid, user) → WebsiteTagsDTO
TagService::detach(websiteUuid, tagUuid, user) → WebsiteTagsDTO
TagService::sync(websiteUuid, SyncWebsiteTagsDTO, user) → WebsiteTagsDTO
```

**HTTP routes** (`routes/api/v1.php`):

| Method | Path | Permission |
|--------|------|------------|
| GET | `/tags` | `tags.view` |
| POST | `/tags` | `tags.manage` |
| GET | `/tags/{uuid}` | `tags.view` |
| PUT | `/tags/{uuid}` | `tags.manage` |
| DELETE | `/tags/{uuid}` | `tags.manage` |

**Tests:** Run the Tags suite with `composer test:tags`.

| Layer | Path |
|-------|------|
| CRUD flow | `tests/Feature/Tag/TagCrudFlowTest.php` |
| Endpoints | `tests/Feature/Api/V1/Tag/*EndpointTest.php` |
| Authorization | `tests/Feature/Api/V1/Tag/TagAuthorizationEndpointTest.php` |
| OpenAPI contract | `tests/Contract/OpenApi/TagOpenApiSpecTest.php` |
| Service | `tests/Unit/Services/Tag/TagServiceTest.php` |
| DTO | `tests/Unit/DTOs/Tag/` |
| Repository | `tests/Unit/Repositories/Eloquent/EloquentTagRepositoryTest.php` |
| Model / migration | `tests/Feature/Models/TagModelTest.php`, `tests/Unit/Database/TagsMigrationTest.php` |
| Website tags | `tests/Feature/Models/WebsiteTagRelationshipTest.php`, `tests/Unit/Database/WebsiteTagsMigrationTest.php` |
| Website tag service | `tests/Unit/Services/Website/WebsiteTagServiceTest.php` |

OpenAPI paths and schemas: `openapi/openapi.yaml` (`/tags`, `/tags/{tag}`, `Tag`, `CreateTagRequest`, `UpdateTagRequest`).

## Widgets domain

Marketplace catalog widgets are persisted as public UUID entities with lifecycle status.

| Component | Location |
|-----------|----------|
| Migration | `database/migrations/2026_06_09_180000_create_widgets_table.php` |
| Model | `app/Models/Widget.php` extends `PublicEntity` |
| Status enum | `app/Enums/WidgetStatus.php` (`draft`, `published`, `deprecated`) |
| Factory | `database/factories/WidgetFactory.php` |
| Repository | `WidgetRepositoryInterface` → `EloquentWidgetRepository` |
| DTO | `app/DTOs/Widget/WidgetDTO.php`, `app/DTOs/Widget/ListWidgetCatalogQueryDTO.php` |
| Service | `app/Services/Widget/WidgetService.php` (catalog); `WidgetCatalogService` and `WidgetVersionService` remain for focused use |

**Table:** `widgets` — `uuid`, `name`, `slug` (unique), `description`, `status`, timestamps.

**Repository methods:** `findBySlug`, `listPublishedOrderedByName`, `listByStatus`, plus UUID lookups from `UuidRepositoryInterface`.

Bind `WidgetRepositoryInterface` in `RepositoryServiceProvider`. `WidgetService` maps `Widget` and `WidgetVersion` models to DTOs.

```
WidgetService::listPublished(?ListWidgetCatalogQueryDTO) → list<WidgetDTO>
WidgetService::getByUuid(uuid) → WidgetDTO
WidgetService::getBySlug(slug) → WidgetDTO
WidgetService::listVersionsForWidget(widgetUuid) → list<WidgetVersionDTO>
WidgetService::listPublishedVersionsForWidget(widgetUuid) → list<WidgetVersionDTO>
WidgetService::getVersionByUuid(uuid) → WidgetVersionDTO
WidgetService::register(RegisterWidgetDTO, User) → WidgetDTO
```

**Catalog listing:** `GET /api/v1/widgets` requires `widgets.view`. Query filters via `ListWidgetCatalogQueryDTO`:

| Parameter | Behavior |
|-----------|----------|
| `search` | Substring match on name, slug, or description |
| `category` | Slug equals or starts with `{category}-` |
| `slugs[]` | Restrict to explicit widget slugs |

**Admin registration:** `POST /api/v1/widgets` requires `admin.widgets.publish`. Returns `WidgetDTO` with default status `draft`.

**Admin activation/deactivation:**

```
POST /api/v1/widgets/{uuid}/activate   → published (requires published version)
POST /api/v1/widgets/{uuid}/deactivate → deprecated (published only)
```

Both require `admin.widgets.publish` and emit audit events (`published`, `deprecated`).

**Tests:** Run the Widget suite with `composer test:widget`.

| Layer | Path |
|-------|------|
| OpenAPI contract | `tests/Contract/OpenApi/WidgetMarketplaceOpenApiSpecTest.php` |
| Service | `tests/Unit/Services/Widget/WidgetServiceTest.php` |
| DTO | `tests/Unit/DTOs/Widget/WidgetDTOTest.php`, `tests/Unit/DTOs/Widget/ListWidgetCatalogQueryDTOTest.php`, `tests/Unit/DTOs/Widget/RegisterWidgetDTOTest.php` |
| HTTP | `tests/Feature/Widget/WidgetRegistrationFlowTest.php`, `tests/Feature/Widget/WidgetActivationFlowTest.php`, `tests/Feature/Api/V1/Widget/ListWidgetsEndpointTest.php`, `tests/Feature/Api/V1/Widget/WidgetAuthorizationEndpointTest.php` |
| Repository | `tests/Unit/Repositories/Eloquent/EloquentWidgetRepositoryTest.php` |
| Model / migration | `tests/Feature/Models/WidgetModelTest.php`, `tests/Unit/Database/WidgetsMigrationTest.php` |

OpenAPI schema and paths: `openapi/openapi.yaml` (`Widget`, `WidgetStatus`, `RegisterWidgetRequest`, `ListWidgetCatalogQuery`, `GET /widgets`, `POST /widgets`, `GET /widgets/{widget}`, `POST /widgets/{widget}/activate`, `POST /widgets/{widget}/deactivate`).

### Widget versions

Semver releases belong to a parent widget and expose asset manifest metadata.

| Component | Location |
|-----------|----------|
| Migration | `database/migrations/2026_06_09_180001_create_widget_versions_table.php` |
| Model | `app/Models/WidgetVersion.php` extends `PublicEntity` |
| Status enum | `app/Enums/WidgetVersionStatus.php` (`draft`, `published`, `deprecated`) |
| Factory | `database/factories/WidgetVersionFactory.php` |
| Repository | `WidgetVersionRepositoryInterface` → `EloquentWidgetVersionRepository` |
| DTO | `app/DTOs/Widget/WidgetVersionDTO.php` |
| Service | `app/Services/Widget/WidgetVersionService.php` |

**Table:** `widget_versions` — `uuid`, `widget_id` (FK → `widgets`), `version` (unique per widget), `status`, `asset_manifest_url`, timestamps.

**Repository methods:** `findByWidgetAndVersion`, `findPublishedForWidget`, `listForWidget`, `listPublishedForWidget`, `listByStatus`, plus UUID lookups from `UuidRepositoryInterface`.

Bind `WidgetVersionRepositoryInterface` in `RepositoryServiceProvider`. `WidgetVersionService` maps `WidgetVersion` models to `WidgetVersionDTO`.

```
WidgetVersionService::listForWidget(widgetUuid) → list<WidgetVersionDTO>
WidgetVersionService::listPublishedForWidget(widgetUuid) → list<WidgetVersionDTO>
WidgetVersionService::getByUuid(uuid) → WidgetVersionDTO
WidgetVersionService::publish(versionUuid, User) → WidgetVersionDTO
WidgetVersionService::deprecate(versionUuid, User) → WidgetVersionDTO
WidgetVersionService::rollback(versionUuid, User) → WidgetVersionDTO
```

**Admin version publishing:**

```
POST /api/v1/widget-versions/{uuid}/publish   → published (requires asset_manifest_url; deprecates prior published version)
POST /api/v1/widget-versions/{uuid}/deprecate → deprecated (published only)
POST /api/v1/widget-versions/{uuid}/rollback  → published (deprecated only; restores prior release)
```

All require `admin.widgets.publish` and emit audit events (`published`, `deprecated`, `restored`) with `subject_type: widget_version`.

| Layer | Path |
|-------|------|
| Service | `tests/Unit/Services/Widget/WidgetVersionServiceTest.php` |
| DTO | `tests/Unit/DTOs/Widget/WidgetVersionDTOTest.php` |
| HTTP | `tests/Feature/Widget/WidgetVersionPublishingFlowTest.php`, `tests/Feature/Widget/WidgetVersionRollbackFlowTest.php`, `tests/Feature/Api/V1/Widget/WidgetVersionAuthorizationEndpointTest.php` |
| Repository | `tests/Unit/Repositories/Eloquent/EloquentWidgetVersionRepositoryTest.php` |
| Model / migration | `tests/Feature/Models/WidgetVersionModelTest.php`, `tests/Unit/Database/WidgetVersionsMigrationTest.php` |

OpenAPI schema and paths: `openapi/openapi.yaml` (`WidgetVersion`, `WidgetVersionStatus`, `POST /widget-versions/{widget_version}/publish`, `POST /widget-versions/{widget_version}/deprecate`, `POST /widget-versions/{widget_version}/rollback`).

### Widget categories

Marketplace taxonomy categories group widgets for discovery. Widgets attach via `widget_category_widget` pivot.

| Component | Location |
|-----------|----------|
| Migrations | `2026_06_09_180002_create_widget_categories_table.php`, `2026_06_09_180003_create_widget_category_widget_table.php` |
| Model | `app/Models/WidgetCategory.php` extends `PublicEntity` |
| Pivot | `app/Models/WidgetCategoryWidget.php` |
| Factory | `database/factories/WidgetCategoryFactory.php` |
| Repository | `WidgetCategoryRepositoryInterface` → `EloquentWidgetCategoryRepository` |
| DTO | `app/DTOs/Widget/WidgetCategoryDTO.php` |
| Service | `app/Services/Widget/WidgetCategoryService.php` |

```
WidgetCategoryService::list() → list<WidgetCategoryDTO>
WidgetCategoryService::getByUuid(uuid) → WidgetCategoryDTO
WidgetCategoryService::getBySlug(slug) → WidgetCategoryDTO
WidgetCategoryService::listForWidget(widgetUuid) → list<WidgetCategoryDTO>
WidgetCategoryService::attach(widgetUuid, categoryUuid) → WidgetCategoriesDTO
WidgetCategoryService::detach(widgetUuid, categoryUuid) → WidgetCategoriesDTO
WidgetCategoryService::sync(widgetUuid, SyncWidgetCategoriesDTO) → WidgetCategoriesDTO
```

Bind `WidgetCategoryWidgetRepositoryInterface` in `RepositoryServiceProvider`.

| Layer | Path |
|-------|------|
| Service | `tests/Unit/Services/Widget/WidgetCategoryServiceTest.php` |
| DTO | `tests/Unit/DTOs/Widget/WidgetCategoryDTOTest.php`, `WidgetCategoriesDTOTest.php`, `SyncWidgetCategoriesDTOTest.php` |
| Repository | `tests/Unit/Repositories/Eloquent/EloquentWidgetCategoryRepositoryTest.php`, `EloquentWidgetCategoryWidgetRepositoryTest.php` |
| Model / migration | `tests/Feature/Models/WidgetCategoryModelTest.php`, `tests/Unit/Database/WidgetCategoriesMigrationTest.php`, `tests/Unit/Database/WidgetCategoryWidgetMigrationTest.php` |

OpenAPI schemas: `openapi/openapi.yaml` (`WidgetCategory`, `WidgetCategories`, `SyncWidgetCategoriesRequest`).

### Widget templates

| Layer | Path |
|-------|------|
| Migration | `database/migrations/2026_06_09_180004_create_widget_templates_table.php` |
| Model | `app/Models/WidgetTemplate.php` extends `PublicEntity` |
| Factory | `database/factories/WidgetTemplateFactory.php` |
| Repository | `WidgetTemplateRepositoryInterface` → `EloquentWidgetTemplateRepository` |
| DTO | `app/DTOs/Widget/WidgetTemplateDTO.php` |
| Service | `app/Services/Widget/WidgetTemplateService.php` |

```
WidgetTemplateService::listForWidget(widgetUuid) → list<WidgetTemplateDTO>
WidgetTemplateService::getByUuid(uuid) → WidgetTemplateDTO
WidgetTemplateService::getByWidgetAndSlug(widgetUuid, slug) → WidgetTemplateDTO
WidgetTemplateService::getDefaultForWidget(widgetUuid) → WidgetTemplateDTO
```

Bind `WidgetTemplateRepositoryInterface` in `RepositoryServiceProvider`.

| Layer | Path |
|-------|------|
| Service | `tests/Unit/Services/Widget/WidgetTemplateServiceTest.php` |
| DTO | `tests/Unit/DTOs/Widget/WidgetTemplateDTOTest.php` |
| Repository | `tests/Unit/Repositories/Eloquent/EloquentWidgetTemplateRepositoryTest.php` |
| Model / migration | `tests/Feature/Models/WidgetTemplateModelTest.php`, `tests/Unit/Database/WidgetTemplatesMigrationTest.php` |

OpenAPI schema: `openapi/openapi.yaml` (`WidgetTemplate`).

### Widget template assignment

| Layer | Path |
|-------|------|
| DTO | `app/DTOs/Widget/WidgetTemplatesDTO.php`, `AssignWidgetTemplateDTO.php` |
| Service | `app/Services/Widget/WidgetTemplateAssignmentService.php` |

```
WidgetTemplateAssignmentService::assign(widgetUuid, AssignWidgetTemplateDTO, user) → WidgetTemplatesDTO
WidgetTemplateAssignmentService::assignDefault(widgetUuid, templateUuid, user) → WidgetTemplateDTO
WidgetTemplateAssignmentService::unassign(widgetUuid, templateUuid, user) → WidgetTemplatesDTO
```

| Layer | Path |
|-------|------|
| Service | `tests/Unit/Services/Widget/WidgetTemplateAssignmentServiceTest.php` |
| DTO | `tests/Unit/DTOs/Widget/WidgetTemplatesDTOTest.php`, `AssignWidgetTemplateDTOTest.php` |

OpenAPI schemas: `openapi/openapi.yaml` (`WidgetTemplates`, `AssignWidgetTemplateRequest`).

### Website widgets

| Layer | Path |
|-------|------|
| Migration | `database/migrations/2026_06_09_180005_create_website_widgets_table.php` |
| Enum | `app/Enums/WebsiteWidgetStatus.php` |
| Model | `app/Models/WebsiteWidget.php` extends `PublicEntity` |
| Factory | `database/factories/WebsiteWidgetFactory.php` |
| Repository | `WebsiteWidgetRepositoryInterface` → `EloquentWebsiteWidgetRepository` |
| DTO | `app/DTOs/Widget/WebsiteWidgetDTO.php`, `InstallWidgetDTO.php`, `UpdateWebsiteWidgetDTO.php` |
| Service | `app/Services/Widget/WebsiteWidgetService.php` |

```
WebsiteWidgetRepositoryInterface::listForWebsite(websiteId)
WebsiteWidgetRepositoryInterface::listForUser(userId)
WebsiteWidgetRepositoryInterface::findByUuidForWebsite(websiteId, uuid)
WebsiteWidgetRepositoryInterface::findByUuidForUser(uuid, userId)
WebsiteWidgetRepositoryInterface::findByWebsiteAndWidgetVersion(websiteId, widgetVersionId)
WebsiteWidgetRepositoryInterface::create / update / delete (inherited CRUD)
```

```
WebsiteWidgetService::listForUser(user) → list<WebsiteWidgetDTO>
WebsiteWidgetService::listForWebsite(websiteUuid) → list<WebsiteWidgetDTO>
WebsiteWidgetService::getForUser(websiteWidgetUuid, user) → WebsiteWidgetDTO
WebsiteWidgetService::getByUuid(uuid) → WebsiteWidgetDTO
WebsiteWidgetService::getByUuidForWebsite(websiteUuid, uuid) → WebsiteWidgetDTO
WebsiteWidgetService::install(InstallWidgetDTO, user) → WebsiteWidgetDTO
WebsiteWidgetService::update(websiteWidgetUuid, UpdateWebsiteWidgetDTO, user) → WebsiteWidgetDTO
WebsiteWidgetService::uninstall(websiteWidgetUuid, user) → void
```

Bind `WebsiteWidgetRepositoryInterface` in `RepositoryServiceProvider`.

| Layer | Path |
|-------|------|
| Service | `tests/Unit/Services/Widget/WebsiteWidgetServiceTest.php` |
| DTO | `tests/Unit/DTOs/Widget/WebsiteWidgetDTOTest.php`, `InstallWidgetDTOTest.php`, `UpdateWebsiteWidgetDTOTest.php` |
| Repository | `tests/Unit/Repositories/Eloquent/EloquentWebsiteWidgetRepositoryTest.php` |
| Model / migration | `tests/Feature/Models/WebsiteWidgetModelTest.php`, `tests/Unit/Database/WebsiteWidgetsMigrationTest.php` |

OpenAPI schemas: `openapi/openapi.yaml` (`WebsiteWidget`, `WebsiteWidgetStatus`, `InstallWidgetRequest`, `UpdateWebsiteWidgetRequest`).

See [docs/WIDGET_MARKETPLACE_ARCHITECTURE.md](../docs/WIDGET_MARKETPLACE_ARCHITECTURE.md) for the full widget marketplace design.

## Permissions architecture

Role-based permissions are config-driven and resolved through `PermissionService`.

```
User.role → PermissionsRepository → PermissionService (cache) → UserPermissionsDTO / Gate
```

| Component | Location |
|-----------|----------|
| Permission enum | `app/Enums/Permission.php` |
| Role map | `config/permissions.php` |
| Repository | `PermissionsRepositoryInterface` → `ConfigPermissionsRepository` |
| Service | `app/Services/Auth/PermissionService.php` |
| Authorization | `app/Services/Auth/AuthorizationService.php` |
| DTO | `app/DTOs/Auth/UserPermissionsDTO.php` |
| Policies | `app/Policies/BasePolicy.php` + `ChecksPermissions` trait |
| Middleware | `permission:{slug}` → `EnsurePermission` → `AuthorizationService` |
| Gates | Registered in `AuthorizationServiceProvider` |

**Cache:** resolved sets use `user:permissions:{user_uuid}` (invalidated by `RoleAssignmentService`).

**Usage in policies:**

```php
final class WebsitePolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->allows($user, Permission::WebsitesView);
    }
}
```

**Usage on routes:**

```php
Route::get('admin/users', ...)->middleware(['auth:api', 'permission:admin.users.view']);
```

**Tests:** `tests/Unit/Services/Auth/AuthorizationServiceTest.php`, `tests/Unit/Services/Auth/PermissionServiceTest.php`, `tests/Unit/Http/Middleware/EnsurePermissionTest.php`, `tests/Feature/Auth/AuthorizationGateTest.php`, `tests/Feature/Auth/EnsurePermissionMiddlewareTest.php`.

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
| Auth | `tests/Feature/Auth/`, `tests/Feature/Api/V1/Auth/`, `tests/Unit/Services/Auth/`, etc. | JWT authentication domain |

**Run auth suite only:**

```bash
composer test:auth
# or
php artisan test --testsuite=Auth
```

**Auth test helpers:** `tests/Concerns/InteractsWithAuthentication.php` provides role seeding, JWT bearer helpers, and API envelope assertions. Integration flows live in `tests/Feature/Auth/AuthenticationFlowTest.php` and `tests/Feature/Auth/AuthenticationGuardTest.php`.

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
