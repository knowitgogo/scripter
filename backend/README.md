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

All endpoints are under `/api/v1`. Routes are defined in `routes/api.php`.

| Endpoint | Description |
|----------|-------------|
| `GET /api/v1/health` | Liveness probe — process is running |
| `GET /api/v1/ready` | Readiness probe — database and cache are reachable |
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

## OpenAPI

The contract lives at `openapi/openapi.yaml`. Extend this file as endpoints are added. Contract tests in `tests/Contract/` validate the spec artifact.

## Setup

```bash
composer install
cp .env.example .env   # if needed
php artisan key:generate
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
