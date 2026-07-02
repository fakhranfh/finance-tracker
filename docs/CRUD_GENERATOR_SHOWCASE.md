# CRUD Generator — Generated Output Showcase

This document shows what `make:rsc` generates out of the box, using `Product` as the example model.

---

## Command Used

```bash
php artisan make:rsc Product --label="Products"
```

---

## What Gets Generated

Running the command produces a complete, working module in seconds:

| File | Purpose |
|---|---|
| `app/Models/Product.php` | Eloquent model |
| `app/Repositories/Product/ProductRepositoryInterface.php` | Repository contract |
| `app/Repositories/Product/ProductRepository.php` | Repository implementation |
| `app/Services/ProductService.php` | Business logic layer |
| `app/Http/Controllers/ProductController.php` | HTTP controller with DataTables list endpoint |
| `app/Http/Requests/Product/StoreProductRequest.php` | Create validation |
| `app/Http/Requests/Product/UpdateProductRequest.php` | Update validation |
| `resources/views/app/product/index.blade.php` | List view with DataTables |
| `resources/views/app/product/create.blade.php` | Create form |
| `resources/views/app/product/edit.blade.php` | Edit form with info sidebar |
| `resources/views/app/product/show.blade.php` | Detail view with action sidebar |
| `routes/web.php` (modified) | RESTful resource routes + DataTables list route |
| `app/Providers/AppServiceProvider.php` (modified) | Repository binding |
| `config/sidebar.php` (modified) | Sidebar menu entry |

---

## Generated Views — Screenshots

All screenshots below are captured from an actual generated module running in the app.

### 1. Index — `/products`

The list page uses server-side DataTables with search, sort, and pagination. The sidebar automatically shows the active "Products" item with its icon.

![Product Index](dusk/images/crud/product-index.png)

**Key features:**
- Header with title and "**+ NEW PRODUCTS**" button
- Column-aware filter UI (text, date range, enum dropdown) with Apply / Reset buttons
- Server-side DataTable via AJAX (`/products/data/list`) — filters are passed as query params
- Columns and filter inputs derived automatically from column definitions at generation time
- Edit / View / Delete action buttons per row
- Empty-state "No data available" when table has no records

---

### 2. Create — `/products/create`

The create form is auto-generated from the column configuration provided during `make:rsc`. Columns with `text`, `email`, `number`, `textarea`, `select`, `radio`, and `date` types all generate the appropriate HTML input.

![Product Create](dusk/images/crud/product-create.png)

**Key features:**
- Breadcrumb: `Products / Create`
- "Fill in the form below to create a new record" subtitle
- Form fields generated per column type
- `CREATE` (primary) and `CANCEL` (secondary) buttons
- Inline validation error display on submit failure

---

### 3. Show — `/products/{id}`

The detail view displays a record's fields in a two-column layout: field values on the left, metadata and actions on the right.

![Product Show](dusk/images/crud/product-show.png)

**Key features:**
- Breadcrumb: `Products / {id}`
- "Details" card with all field values (read-only)
- Information sidebar showing Created and Updated timestamps
- `Edit`, `Delete` (with confirm modal), and `Back to List` action buttons

---

### 4. Edit — `/products/{id}/edit`

The edit form mirrors the create form but pre-fills fields with existing record data and shows a "Save Changes" button instead.

![Product Edit](dusk/images/crud/product-edit.png)

**Key features:**
- Breadcrumb: `Products / {id} / Edit`
- "Update the record details" subtitle
- Pre-filled form fields (when columns have data)
- `SAVE CHANGES` (primary) and `CANCEL` (secondary) buttons
- Information sidebar with timestamps and Delete button

---

## Architecture of Generated Code

```
HTTP Request
    │
    ▼
ProductController          ← Thin controller, delegates to service
    │
    ▼
ProductService             ← Business logic (create, update, find, delete)
    │
    ▼
ProductRepositoryInterface ← Contract (bound in AppServiceProvider)
    │
    ▼
ProductRepository          ← Eloquent queries
    │
    ▼
products table             ← Database
```

**Validation** is handled by `StoreProductRequest` and `UpdateProductRequest` before reaching the controller.

---

## RESTful Routes Generated

```
GET     /products              → index   (DataTables page)
GET     /products/data/list    → list    (DataTables AJAX endpoint)
GET     /products/create       → create
POST    /products              → store
GET     /products/{id}         → show
GET     /products/{id}/edit    → edit
PUT     /products/{id}         → update
DELETE  /products/{id}         → destroy
```

---

## Sidebar Integration

The generator appends an entry to `config/sidebar.php`. The sidebar blade component renders all entries from this config, so no HTML is injected directly:

```php
// config/sidebar.php
[
    'label'          => 'Products',
    'route'          => 'products.index',
    'icon'           => 'shopping_cart',
    'active_pattern' => 'products.*',
],
```

Active state is applied automatically when any `products.*` route is active.

---

## Regenerating Screenshots

To recapture these screenshots after making changes, run:

```bash
php artisan dusk tests/Browser/CaptureProductCrudScreenshotsTest.php
```

Screenshots are saved to `docs/dusk/images/crud/`.

---

## Deleting a Generated Module

```bash
php artisan delete:rsc Product
```

This removes all generated files and undoes the route, service provider, and sidebar modifications. Pass `--migrations` to also delete the migration file.
