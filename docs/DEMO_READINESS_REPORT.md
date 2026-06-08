# Demo Readiness Report — Prompts 001–008

> **Date:** 2026-06-08  
> **Scope:** Infrastructure and platform foundation (no domain/business modules)  
> **Test suite:** 130/130 passing (`php artisan test`)

---

## Demo Ready

| Audience | Ready? | Notes |
|----------|--------|-------|
| **Platform / architecture review** | **Yes** | Layering, envelope, probes, OpenAPI, UUID strategy demonstrable |
| **Product / end-user demo** | **No** | Auth, websites, widgets, analytics, billing not implemented |
| **Overall** | **Partial — Yes for technical foundation demo only** | |

---

## Prompts 001–008 Coverage

| # | Deliverable | Status | Demoable today? |
|---|-------------|--------|-----------------|
| 001 | `ARCHITECTURE.md` — monorepo design | Complete | Yes (walkthrough doc) |
| 002 | `backend/` Laravel 13 scaffold + folder conventions | Complete | Yes (code tour + tests) |
| 003 | API-only app, JSON envelope, exception handling | Complete | Yes (404/JSON errors) |
| 004 | `ApiResponse` + `BaseController` | Complete | Yes (via health/ready responses) |
| 005 | UUID strategy (traits, config, migration on `users`) | Complete | Partial (DB/tinker only; no public API) |
| 006 | Health + readiness endpoints | Complete | Yes |
| 007 | Redis, queues, cache abstraction | Complete | Partial (config/code; Redis optional) |
| 008 | OpenAPI foundation + Swagger UI route | Complete | Yes |

---

## Demonstrable Features

### HTTP endpoints (live)

| Endpoint | Purpose |
|----------|---------|
| `GET /up` | Laravel process health |
| `GET /api/v1/health` | Liveness probe (API envelope) |
| `GET /api/v1/ready` | Readiness probe (DB + cache checks) |
| `GET /api/openapi.yaml` | OpenAPI 3.1 contract (YAML) |
| `GET /api/docs` | Swagger UI browser |

### Platform behaviors

- Standard JSON envelope: `success`, `data`, `message`, `errors`
- API-only routing (no web welcome page); unknown routes return JSON 404 envelope
- Exception mapping (401/403/404/422/500) via `ApiExceptionRenderer`
- UUID auto-assignment on `User` model (`HasUuid`, `HidesInternalId`)
- Infrastructure abstractions: `CacheService`, `QueueService`, repository bindings
- OpenAPI contract at `backend/openapi/openapi.yaml` synced with system routes
- 130 automated tests (unit, feature, contract)

### Not demonstrable (planned, not built)

- Customer portal, admin portal, widget SDK (repos not scaffolded)
- Auth (register/login/JWT)
- Websites, widgets, widget keys, marketplace
- Analytics dashboard, billing/Stripe
- Queue workers processing domain jobs (no domain jobs)
- Redis readiness check (only when `REDIS_ENABLED=true`)

---

## Demo Steps

### 1. Environment setup (5 min)

```bash
cd backend
composer install
cp .env.example .env   # if missing
php artisan key:generate
php artisan migrate
php artisan serve      # http://127.0.0.1:8000
```

### 2. Infrastructure walkthrough (10 min)

1. Open `ARCHITECTURE.md` — explain monorepo targets and layer rules.
2. Show `backend/app/` structure: Controllers → Services → Repositories → DTOs.
3. Run `php artisan test` — show 130 passing tests.
4. Run `php artisan route:list --path=api` — four API routes.

### 3. Live API demo (10 min)

1. **Liveness** — `GET /api/v1/health` → `{ status: ok, version: v1 }`.
2. **Readiness** — `GET /api/v1/ready` → `checks.database` and `checks.cache` both `ok`.
3. **Error envelope** — `GET /api/v1/does-not-exist` → 404 JSON envelope.
4. **OpenAPI** — open `http://127.0.0.1:8000/api/docs` in browser (Swagger UI).
5. **Raw spec** — `GET /api/openapi.yaml` — show YAML artifact.

### 4. Optional deep dives (5 min each)

- **UUID:** `php artisan tinker` → `User::factory()->create()` → show `uuid`, hidden `id`.
- **Cache/queue config:** walk through `config/infrastructure.php`.
- **Exception handling:** point to `ApiExceptionRenderer` + `bootstrap/app.php`.

---

## Test Commands

```bash
# Full suite
cd backend && composer test

# By area
php artisan test --filter=Health
php artisan test --filter=Readiness
php artisan test --filter=OpenApi
php artisan test --filter=ApiResponse
php artisan test --filter=Uuid

# Static analysis / routes
php artisan route:list --path=api
php artisan config:show infrastructure
php artisan config:show openapi
```

---

## Curl Examples

Base URL: `http://127.0.0.1:8000`

```bash
# Laravel health probe
curl -i http://127.0.0.1:8000/up

# Liveness
curl -s http://127.0.0.1:8000/api/v1/health | jq

# Readiness
curl -s http://127.0.0.1:8000/api/v1/ready | jq

# 404 envelope (unknown route)
curl -s http://127.0.0.1:8000/api/v1/unknown | jq

# OpenAPI spec (YAML)
curl -s -H "Accept: application/yaml" http://127.0.0.1:8000/api/openapi.yaml | head -30

# Swagger UI (HTML)
curl -s -o /dev/null -w "%{http_code} %{content_type}\n" http://127.0.0.1:8000/api/docs

# Force non-JSON Accept on API route (still returns JSON envelope)
curl -s -H "Accept: text/html" http://127.0.0.1:8000/api/v1/health | jq
```

### Expected samples

**Health (200):**
```json
{
  "success": true,
  "data": { "status": "ok", "version": "v1" },
  "message": null,
  "errors": []
}
```

**Ready (200):**
```json
{
  "success": true,
  "data": {
    "status": "ready",
    "checks": { "database": "ok", "cache": "ok" },
    "version": "v1"
  },
  "message": null,
  "errors": []
}
```

**Not found (404):**
```json
{
  "success": false,
  "data": {},
  "message": "Resource not found.",
  "errors": []
}
```

---

## Postman Examples

### Collection variables

| Variable | Value |
|----------|-------|
| `baseUrl` | `http://127.0.0.1:8000` |

### Requests

| Name | Method | URL | Tests (Postman) |
|------|--------|-----|-----------------|
| Laravel Up | GET | `{{baseUrl}}/up` | `pm.response.to.have.status(200)` |
| Health | GET | `{{baseUrl}}/api/v1/health` | `pm.expect(json.success).to.eql(true)` |
| Readiness | GET | `{{baseUrl}}/api/v1/ready` | `pm.expect(json.data.status).to.eql("ready")` |
| OpenAPI Spec | GET | `{{baseUrl}}/api/openapi.yaml` | `pm.response.to.have.header("Content-Type")` |
| Unknown Route | GET | `{{baseUrl}}/api/v1/nope` | `pm.response.to.have.status(404)` |

### Minimal collection JSON (import into Postman)

```json
{
  "info": {
    "name": "Script Manager — Foundation Demo",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "variable": [{ "key": "baseUrl", "value": "http://127.0.0.1:8000" }],
  "item": [
    {
      "name": "Health",
      "request": { "method": "GET", "url": "{{baseUrl}}/api/v1/health" }
    },
    {
      "name": "Readiness",
      "request": { "method": "GET", "url": "{{baseUrl}}/api/v1/ready" }
    },
    {
      "name": "OpenAPI YAML",
      "request": { "method": "GET", "url": "{{baseUrl}}/api/openapi.yaml" }
    },
    {
      "name": "404 Envelope",
      "request": { "method": "GET", "url": "{{baseUrl}}/api/v1/unknown" }
    }
  ]
}
```

---

## API Examples (OpenAPI-aligned)

Documented in `backend/openapi/openapi.yaml` and browsable at `/api/docs`.

| Operation ID | Path | Auth |
|--------------|------|------|
| `healthCheck` | `/health` | None |
| `readinessCheck` | `/ready` | None |
| `openApiSpec` | `/openapi.yaml` | None |
| `swaggerUi` | `/docs` | None |

Server base in spec: `/api/v1` (system meta routes are under `/api`).

---

## Local Testing Steps

1. Clone repo and `cd backend`.
2. `composer install`
3. Copy `.env.example` → `.env`; run `php artisan key:generate`.
4. Ensure SQLite file exists or configure MySQL in `.env`.
5. `php artisan migrate` (creates users, cache, jobs tables + `users.uuid`).
6. `php artisan serve`
7. Verify:
   - Browser: `http://127.0.0.1:8000/api/docs`
   - CLI: `curl http://127.0.0.1:8000/api/v1/health`
8. Run `composer test` — expect **130 passed**.

### Optional: demo user seed

```bash
php artisan db:seed
# Creates: test@example.com / password (factory default)
```

Verify UUID in tinker:

```bash
php artisan tinker --execute="echo App\Models\User::first()->uuid;"
```

---

## Screenshots Checklist

| # | Capture | URL / action | Purpose |
|---|---------|--------------|---------|
| 1 | Terminal — tests green | `composer test` | Quality gate |
| 2 | Terminal — routes | `php artisan route:list --path=api` | Scope clarity |
| 3 | Swagger UI home | `/api/docs` | OpenAPI foundation |
| 4 | Swagger — Health expanded | `/api/docs` | Contract detail |
| 5 | Health JSON | `/api/v1/health` (browser or Postman) | ApiResponse envelope |
| 6 | Readiness JSON | `/api/v1/ready` | Dependency checks |
| 7 | 404 envelope | `/api/v1/unknown` | Error handling |
| 8 | OpenAPI YAML | `/api/openapi.yaml` (first 40 lines) | Contract artifact |
| 9 | IDE — layer folders | `backend/app/{Http,Services,Repositories,DTOs}` | Architecture |
| 10 | IDE — ARCHITECTURE.md | repo root | System design |

---

## Seed Data

| Required for current demo? | **No** |
|----------------------------|--------|

Health, readiness, OpenAPI, and error-envelope demos require **no seeded data**.

**Optional** (existing `DatabaseSeeder`):

```bash
php artisan db:seed
```

Creates one user (`test@example.com`) with auto-generated UUID. No API exposes users yet — demonstrate via Tinker only.

**Not generated** (per instruction: no new business functionality):

- Websites, widgets, plans, subscriptions, analytics events

---

## Missing Dependencies

### Repositories not scaffolded

| Component | Path | Impact |
|-----------|------|--------|
| Customer portal | `customer-portal/` | No UI demo |
| Admin portal | `admin-portal/` | No admin UI demo |
| Widget SDK | `widget-sdk/` | No embed demo |
| Infrastructure deploy configs | `infrastructure/` | Deploy story doc-only |

### Backend — not implemented

| Module | Planned API (ARCHITECTURE.md) |
|--------|-------------------------------|
| Auth | `POST /auth/register`, `POST /auth/login` |
| Websites | `GET/POST /websites` |
| Widgets | `POST /website-widgets`, `POST /widget-keys` |
| Runtime | `POST /widget/initialize`, `POST /widget/event` |
| Analytics | `GET /analytics/dashboard` |
| Billing | `POST /billing/checkout` |
| Admin | `/admin/*` |

### Runtime / infra (optional for foundation demo)

| Dependency | Default | When needed |
|------------|---------|-------------|
| Redis | Off (`REDIS_ENABLED=false`) | Production cache/queue; adds `redis` readiness check |
| Supervisor / queue worker | Not configured | When domain jobs exist |
| MySQL (Azure) | SQLite locally | Production parity |
| Stripe, Azure Blob | Not configured | Billing and widget CDN |
| JWT / Sanctum | Not installed | Auth module |

### Documentation vs product

- `API_CONTRACTS_v3.docx` and `PRODUCT_REQUIREMENTS_v3.docx` describe full product; only **system endpoints** are live.

---

## Recommendation

**Proceed with a technical foundation demo** covering architecture, API conventions, probes, OpenAPI, and test coverage.

**Do not schedule a product demo** until Auth + at least one domain vertical (e.g. Websites) is implemented in prompts 009+.
