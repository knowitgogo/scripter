# Widget Marketplace Architecture

> **Version:** 1.0  
> **Status:** Approved for implementation  
> **Last updated:** 2026-06-09  
> **Scope:** Widget catalog, installation, configuration, keys, and runtime initialization

This document is the authoritative design for the **Widget** bounded context in the Script Manager platform. It extends [ARCHITECTURE.md](../ARCHITECTURE.md) with marketplace-specific data models, layer contracts, API surface, caching, and testing requirements.

Parent platform principles apply unchanged: thin controllers, business logic in Services, persistence in Repositories, DTOs at layer boundaries, UUID public identifiers, Form Request validation, PHPUnit tests, and OpenAPI as the contract source of truth.

---

## Files in This Change

| Action | Path |
|--------|------|
| Create | `docs/WIDGET_MARKETPLACE_ARCHITECTURE.md` |
| Update | `backend/openapi/openapi.yaml` (Widget reference schemas + `Widgets` tag) |
| Create | `backend/tests/Unit/Architecture/WidgetMarketplaceArchitectureDocTest.php` |
| Create | `backend/tests/Contract/OpenApi/WidgetMarketplaceOpenApiSpecTest.php` |
| Update | `ARCHITECTURE.md` (cross-reference) |
| Update | `backend/README.md` (cross-reference) |

No runtime widget endpoints are introduced by this document alone. Existing Auth, Website, and Tag modules remain unchanged.

---

## 1. Executive Summary

The widget marketplace lets customers discover published widgets, install them on owned websites, configure themes and options, generate public widget keys, and serve embedded or hosted runtimes via `widget-sdk`.

```
┌─────────────────┐     browse/install      ┌──────────────────┐
│ Customer Portal │ ───────────────────────►│ Widget Catalog   │
└────────┬────────┘                         │ (widgets +       │
         │                                  │  widget_versions) │
         │ POST /website-widgets            └────────┬─────────┘
         ▼                                           │
┌─────────────────┐         pivot                    │
│ Website         │◄─────────────────────────────────┘
│ (owned by user) │
└────────┬────────┘
         │ 1:N
         ▼
┌─────────────────┐     POST /widget-keys    ┌─────────────────┐
│ website_widgets │ ────────────────────────►│ widget_keys     │
│ configuration   │                          │ (credential)    │
└────────┬────────┘                          └────────┬────────┘
         │                                            │
         │ runtime                                    │ initialize
         ▼                                            ▼
┌─────────────────┐                          ┌─────────────────┐
│ widget-sdk      │ ◄── POST /widget/initialize│ WidgetRuntime   │
│ (embed/hosted)  │ ──► POST /widget/event   │ Service         │
└─────────────────┘                          └─────────────────┘
```

**Implemented today:** Auth, Websites, Tags (catalog labels + website filtering).  
**Planned next:** Widget catalog entities, website installation, widget keys, runtime endpoints.

---

## 2. Domain Model

### 2.1 Entities

| Entity | Table | Public UUID | Purpose |
|--------|-------|-------------|---------|
| **Widget** | `widgets` | yes | Marketplace catalog entry (name, slug, description, status) |
| **WidgetVersion** | `widget_versions` | yes | Semver release with asset manifest and compatibility metadata |
| **WebsiteWidget** | `website_widgets` | yes | Installation of a widget version on a customer website |
| **WidgetKey** | `widget_keys` | no (key string is credential) | Domain-scoped runtime credential for a website widget |

### 2.2 Relationships

```
users
  └── websites
        └── website_widgets ──► widget_versions ──► widgets
              └── widget_keys

tags ←── website_tags ──► websites   (existing; used to filter websites before install)
```

- A **Widget** has many **WidgetVersion** rows; exactly one version may be `published` as the catalog default.
- A **WebsiteWidget** belongs to one **Website** and references one **WidgetVersion** at install time.
- A **WidgetKey** belongs to one **WebsiteWidget**; keys are rotatable and revocable.
- **Tags** remain a separate reusable catalog; they do not replace widget taxonomy but can label websites for discovery/filtering (already implemented on `GET /websites?tag_uuids[]=`).

### 2.3 Widget lifecycle states

| Entity | States | Notes |
|--------|--------|-------|
| Widget (catalog) | `draft`, `published`, `deprecated` | Admin publish flow |
| WidgetVersion | `draft`, `published`, `deprecated` | Immutable once published |
| WebsiteWidget | `active`, `inactive`, `suspended` | Customer toggles install |
| WidgetKey | `active`, `revoked` | Hashed at rest |

Enums live in `app/Enums/` and are cast on Models; DTOs expose backed enum values as strings in API responses.

---

## 3. Layer Architecture

All widget marketplace code follows the same mandatory flow defined in [ARCHITECTURE.md](../ARCHITECTURE.md):

```
HTTP Request
  → Middleware (auth:api, permission:{slug}, throttle)
  → Controller::__invoke()
  → Form Request (validate + authorize)
  → Input DTO::fromRequest($request)
  → Service method (business rules, transactions, events)
      → Repository contract(s)
      → AuditDispatcher / CacheService / Queue
  → Output DTO
  → ApiResponse envelope
```

### 3.1 Controllers (thin)

Location: `app/Http/Controllers/Api/V1/Widget/`

| Controller | Responsibility |
|------------|----------------|
| `IndexWidgetsController` | List published catalog |
| `ShowWidgetController` | Catalog detail |
| `IndexWebsiteWidgetsController` | List installs for a website |
| `StoreWebsiteWidgetController` | Install widget on website |
| `UpdateWebsiteWidgetController` | Update configuration |
| `DestroyWebsiteWidgetController` | Uninstall |
| `StoreWidgetKeyController` | Generate key |
| `DestroyWidgetKeyController` | Revoke key |
| `InitializeWidgetController` | Runtime bootstrap (widget key auth) |

Controllers MUST NOT query Eloquent or embed business rules.

### 3.2 Form Requests

Location: `app/Http/Requests/Widget/`

Validation examples:

- `InstallWidgetRequest` — `website_uuid`, `widget_version_uuid`, optional `configuration` JSON schema
- `CreateWidgetKeyRequest` — `website_widget_uuid`, allowed `domains[]`
- `InitializeWidgetRequest` — `website_widget_uuid`, `widget_key`, `origin` URL

### 3.3 DTOs

Location: `app/DTOs/Widget/`

| DTO | Direction | Purpose |
|-----|-----------|---------|
| `WidgetDTO` | Output | Public catalog row |
| `WidgetVersionDTO` | Output | Version metadata + asset URLs |
| `WebsiteWidgetDTO` | Output | Installation with nested widget summary |
| `InstallWidgetDTO` | Input | Install payload |
| `UpdateWebsiteWidgetDTO` | Input | Configuration patch |
| `WidgetKeyDTO` | Output | Key metadata (never re-expose full secret after create) |
| `CreateWidgetKeyDTO` | Input | Domain allow-list |
| `WidgetInitConfigDTO` | Output | Runtime manifest returned by initialize |
| `ListWidgetCatalogQueryDTO` | Input | Catalog filters (search, category) |

DTOs use `MapsFromRequest` for input and `fromModel()` for output. Internal integer IDs never appear in `toArray()`.

### 3.4 Services

Location: `app/Services/Widget/`

| Service | Responsibility |
|---------|----------------|
| `WidgetCatalogService` | List/show published widgets and versions; admin publish is delegated to Admin module |
| `WebsiteWidgetService` | Install, update config, uninstall; scoped to website owner |
| `WidgetKeyService` | Generate, rotate, revoke keys; domain validation rules |
| `WidgetRuntimeService` | Validate key + origin; return cached config manifest |
| `WidgetConfigService` | Merge defaults with `configuration_json`; cache-aside |

Services depend on **repository contracts only**, never concrete Eloquent classes.

Cross-service orchestration example:

```
InstallWidgetDTO + User
  → WebsiteWidgetService::install()
  → WebsiteRepository::findByUuidForUser()   (ownership)
  → WidgetRepository::findPublishedVersion()
  → WebsiteWidgetRepository::create()
  → AuditDispatcher (widget.installed)
  → InvalidateWidgetConfigCacheJob
  → WebsiteWidgetDTO
```

### 3.5 Repositories

Location: `app/Repositories/Contracts/` and `app/Repositories/Eloquent/`

| Contract | Key methods |
|----------|-------------|
| `WidgetRepositoryInterface` | `listPublished()`, `findBySlug()`, `findPublishedVersion()` |
| `WidgetVersionRepositoryInterface` | `listForWidget()`, `findPublishedByWidget()` |
| `WebsiteWidgetRepositoryInterface` | `listForWebsite()`, `findByUuidForWebsite()`, CRUD |
| `WidgetKeyRepositoryInterface` | `findActiveByHash()`, `createForWebsiteWidget()`, `revoke()` |

Bind all contracts in `RepositoryServiceProvider`.

Repositories return Eloquent Models; Services map to DTOs before leaving the domain layer.

---

## 4. Authorization

Permissions (already defined in `app/Enums/Permission.php`):

| Permission | Slug | Grant | Usage |
|------------|------|-------|-------|
| WidgetsView | `widgets.view` | customer, admin | Browse catalog, list installs |
| WidgetsInstall | `widgets.install` | customer, admin | Install, configure, generate keys |
| AdminWidgetsPublish | `admin.widgets.publish` | admin | Publish/deprecate catalog entries |

Route middleware pattern (consistent with Websites and Tags):

```php
Route::middleware(['auth:api', 'permission:'.Permission::WidgetsView->value])->group(function (): void {
    Route::get('widgets', IndexWidgetsController::class);
});

Route::middleware(['auth:api', 'permission:'.Permission::WidgetsInstall->value])->group(function (): void {
    Route::post('website-widgets', StoreWebsiteWidgetController::class);
});
```

Runtime routes (`/widget/initialize`, `/widget/event`) authenticate via **widget key + domain allow-list**, not JWT.

---

## 5. Caching

| Key pattern | TTL | Invalidation |
|-------------|-----|--------------|
| `widget:catalog:published` | 1 hour | Admin publish/deprecate |
| `widget:config:{website_widget_uuid}` | 15 min | Install, config update, key rotation |

Cache keys use public UUIDs. Repositories do not cache directly; Services use `CacheService` / `CacheKeyBuilder` (see `config/infrastructure.php`).

---

## 6. Events and Audit

| Action | Service | Audit subject_type |
|--------|---------|-------------------|
| Widget installed | `WebsiteWidgetService` | `website_widget` |
| Widget updated | `WebsiteWidgetService` | `website_widget` |
| Widget uninstalled | `WebsiteWidgetService` | `website_widget` |
| Key generated | `WidgetKeyService` | `widget_key` |
| Key revoked | `WidgetKeyService` | `widget_key` |
| Widget published | Admin service | `widget` |

Side effects (cache invalidation, analytics counters) are dispatched via Listeners/Jobs, not Controllers.

---

## 7. Runtime Flows

### 7.1 Embedded widget

1. Customer installs widget → `WebsiteWidget` row created.
2. Customer generates key with allowed domains.
3. Host page loads `widget-sdk` bundle from CDN.
4. SDK calls `POST /api/v1/widget/initialize` with `website_widget_uuid`, `widget_key`, page origin.
5. API validates key hash + domain → returns `WidgetInitConfigDTO` (version assets, theme, config).
6. SDK mounts UI; batches analytics to `POST /api/v1/widget/event`.

### 7.2 Hosted widget

Same backend path; SDK runs inside platform-hosted iframe with stricter CSP and origin checks.

---

## 8. OpenAPI Contract

Reference schemas and the `Widgets` tag are declared in `backend/openapi/openapi.yaml`. HTTP paths are added incrementally as endpoints ship; schemas are stable contracts for portal and SDK code generation.

| Schema | Maps to |
|--------|---------|
| `Widget` | `WidgetDTO` |
| `WidgetVersion` | `WidgetVersionDTO` |
| `WebsiteWidget` | `WebsiteWidgetDTO` |
| `InstallWidgetRequest` | `InstallWidgetDTO` |
| `UpdateWebsiteWidgetRequest` | `UpdateWebsiteWidgetDTO` |
| `WidgetKey` | `WidgetKeyDTO` |
| `CreateWidgetKeyRequest` | `CreateWidgetKeyDTO` |

Planned operations (not yet implemented):

| Method | Path | Permission |
|--------|------|------------|
| GET | `/widgets` | `widgets.view` |
| GET | `/widgets/{widget}` | `widgets.view` |
| GET | `/websites/{website}/widgets` | `widgets.view` |
| POST | `/website-widgets` | `widgets.install` |
| PUT | `/website-widgets/{website_widget}` | `widgets.install` |
| DELETE | `/website-widgets/{website_widget}` | `widgets.install` |
| POST | `/widget-keys` | `widgets.install` |
| DELETE | `/widget-keys/{widget_key}` | `widgets.install` |
| POST | `/widget/initialize` | widget key |
| POST | `/widget/event` | widget key |

---

## 9. Testing Strategy

Every widget marketplace increment MUST include PHPUnit coverage before merge.

### 9.1 Unit tests

| Target | Path | Focus |
|--------|------|-------|
| DTOs | `tests/Unit/DTOs/Widget/` | `fromRequest`, `fromModel`, required fields |
| Services | `tests/Unit/Services/Widget/` | Business rules with real repositories + `RefreshDatabase` |
| Repositories | `tests/Unit/Repositories/Eloquent/` | Queries, scopes, ownership filters |

### 9.2 Feature tests

| Target | Path | Focus |
|--------|------|-------|
| Endpoints | `tests/Feature/Api/V1/Widget/` | Envelope, status codes, UUID exposure |
| Authorization | `tests/Feature/Api/V1/Widget/*Authorization*` | 401/403 |
| Flow | `tests/Feature/Widget/WidgetInstallFlowTest.php` | Install → key → initialize |

### 9.3 Contract tests

| Target | Path | Focus |
|--------|------|-------|
| OpenAPI | `tests/Contract/OpenApi/WidgetMarketplaceOpenApiSpecTest.php` | Schemas + tag documented |
| Architecture | `tests/Unit/Architecture/WidgetMarketplaceArchitectureDocTest.php` | Doc completeness |

Run widget suite (once implemented): `composer test:widget` (planned).  
Architecture doc tests run in the default `composer test` suite today.

---

## 10. Implementation Phases

| Phase | Deliverable | Status |
|-------|-------------|--------|
| 1 | Website + Tag foundation | **Done** |
| 2 | Widget/WidgetVersion migrations, catalog read API | Planned |
| 3 | WebsiteWidget install + configuration CRUD | Planned |
| 4 | WidgetKey generation + runtime initialize | Planned |
| 5 | Admin publish/deprecate + CDN asset pipeline | Planned |
| 6 | Analytics event ingestion from SDK | Planned |

Each phase ships as an independent merge request: migration → model → repository → DTO → service → controller → OpenAPI paths → tests.

---

## 11. Related Documents

| Document | Purpose |
|----------|---------|
| [ARCHITECTURE.md](../ARCHITECTURE.md) | Monorepo-wide layering and cross-cutting concerns |
| [backend/README.md](../backend/README.md) | Laravel API conventions and module index |
| [backend/openapi/openapi.yaml](../backend/openapi/openapi.yaml) | Machine-readable API contract |
| [docs/DEMO_READINESS_REPORT.md](./DEMO_READINESS_REPORT.md) | Current implementation status |
