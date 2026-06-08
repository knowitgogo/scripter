# Script Manager — Backend API

Laravel 13 API for the Widget Marketplace Platform. See [ARCHITECTURE.md](../ARCHITECTURE.md) for the full system design.

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
- Public identifiers are UUIDs (`app/Models/Concerns/HasUuid.php`).
- API responses use `App\Support\ApiResponse` envelope.
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
    ├── Controllers/Api/V1/
    ├── Requests/{Auth,Website,Widget,Analytics,Billing,Admin}/
    └── Resources/
openapi/openapi.yaml
tests/{Unit,Feature,Contract}/
```

## API versioning

All endpoints are under `/api/v1`. Routes are defined in `routes/api.php`.

| Endpoint | Description |
|----------|-------------|
| `GET /api/v1/health` | Infrastructure health check |
| `GET /up` | Laravel health probe |

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
