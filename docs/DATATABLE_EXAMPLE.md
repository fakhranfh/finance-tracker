# DataTables Integration - Quick Start Example

A practical example of implementing DataTables with the CRUD Generator.

## Step-by-Step Implementation

### 1. Generate CRUD with make:rsc

```bash
php artisan make:rsc Product --label="Product"
```

When prompted for column configuration, enter:

```
Column name: title
Column type: string
String length: 255
Nullable: No
Default value: No
Index: Yes
Unique: No
Input type: text
Confirm: Yes

Column name: price
Column type: decimal
Precision: 10
Scale: 2
Nullable: No
Default value: No
Input type: number
Confirm: Yes

Column name: description
Column type: text
Nullable: Yes
Input type: textarea
Confirm: Yes

Done
```

### 2. Generator Result

The command will create the following structure:

```
app/
├── Models/
│   └── Product.php
├── Repositories/
│   └── Product/
│       ├── ProductRepository.php
│       └── ProductRepositoryInterface.php
├── Services/
│   └── ProductService.php
├── Http/
│   ├── Controllers/
│   │   └── ProductController.php
│   └── Requests/
│       └── Product/
│           ├── StoreProductRequest.php
│           └── UpdateProductRequest.php

resources/views/app/product/
├── index.blade.php
├── create.blade.php
├── edit.blade.php
└── show.blade.php
```

### 3. Routes Created

Generated routes:

```
GET       /product              product.index      (show list page)
GET       /product/data/list    product.list       (API endpoint for DataTables)
GET       /product/create       product.create     (show create form)
POST      /product              product.store      (save new item)
GET       /product/{id}         product.show       (show detail)
GET       /product/{id}/edit    product.edit       (show edit form)
PUT/PATCH /product/{id}         product.update     (update item)
DELETE    /product/{id}         product.destroy    (delete item)
```

### 4. DataTables on the Index Page

The file `resources/views/app/product/index.blade.php` already automatically uses DataTables with:

**Features:**
- ✅ AJAX loading data from `/product/data/list`
- ✅ Sorting by clicking column headers
- ✅ Search/filter across all columns
- ✅ Pagination (default 10 per page)
- ✅ Action buttons (View, Edit, Delete)

### 5. API Response Format

The `/product/data/list` endpoint returns:

```json
{
  "data": [
    {
      "id": 1,
      "name": "Product Name",
      "created_at": "2026-06-26 10:30:45",
      "actions": {
        "show": "/product/1",
        "edit": "/product/1/edit",
        "delete": "/product/1"
      }
    },
    {
      "id": 2,
      "name": "Another Product",
      "created_at": "2026-06-26 11:15:30",
      "actions": {
        "show": "/product/2",
        "edit": "/product/2/edit",
        "delete": "/product/2"
      }
    }
  ]
}
```

## Custom Implementation for an Existing Model

If you want to add DataTables to an existing model (not via make:rsc), follow these steps:

### 1. Add a `list()` Method to the Controller

File: `app/Http/Controllers/ProductController.php`

```php
public function list(Request $request)
{
    $items = $this->productService->getAll();

    return response()->json([
        'data' => $items->map(fn($item) => [
            'id' => $item->id,
            'name' => $item->name ?? '',
            'title' => $item->title ?? '',
            'price' => $item->price ?? '',
            'created_at' => $item->created_at?->format('Y-m-d H:i:s') ?? '',
            'actions' => [
                'show' => route('product.show', $item->id),
                'edit' => route('product.edit', $item->id),
                'delete' => route('product.destroy', $item->id),
            ]
        ])->toArray()
    ]);
}
```

### 2. Add the API Route

File: `routes/web.php`

```php
Route::middleware(['auth'])->group(function () {
    Route::resource('product', ProductController::class);
    Route::get('product/data/list', [ProductController::class, 'list'])->name('product.list');
});
```

**Important**: Make sure the API endpoint route is defined **before** the resource route.

### 3. Update the Index View

File: `resources/views/app/product/index.blade.php`

Replace the table and pagination section with the DataTables template from the documentation.

## Customization Examples

### Example 1: Add a Status Column

**Step 1**: Update the API response in the controller

```php
public function list(Request $request)
{
    $items = $this->productService->getAll();

    return response()->json([
        'data' => $items->map(fn($item) => [
            'id' => $item->id,
            'name' => $item->name ?? '',
            'status' => $item->status ?? 'active',  // Add this
            'created_at' => $item->created_at?->format('Y-m-d H:i:s') ?? '',
            'actions' => [
                'show' => route('product.show', $item->id),
                'edit' => route('product.edit', $item->id),
                'delete' => route('product.destroy', $item->id),
            ]
        ])->toArray()
    ]);
}
```

**Step 2**: Add the column to the columns config

```javascript
columns: [
    { data: 'id', title: '{{ __("ID") }}' },
    { data: 'name', title: '{{ __("Name") }}' },
    { 
        data: 'status', 
        title: '{{ __("Status") }}',
        render: function(data) {
            const statusClass = data === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
            return `<span class="px-2 py-1 rounded text-sm font-medium ${statusClass}">${data}</span>`;
        }
    },
    { data: 'created_at', title: '{{ __("Created") }}' },
    // Actions column...
]
```

### Example 2: Format Price/Currency

```javascript
{ 
    data: 'price', 
    title: '{{ __("Price") }}',
    render: function(data) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR'
        }).format(data);
    }
}
```

### Example 3: Change the Number of Rows per Page

```javascript
new DataTable('#product-table', {
    // ... other config
    pageLength: 25,  // Default 25 rows per page
    lengthMenu: [10, 25, 50, 100],  // Dropdown options
    // ...
});
```

## Testing

### Test DataTables via Browser

1. Start the development server:
   ```bash
   php artisan serve
   ```

2. Navigate to `/product` (or the index route you created)

3. Verify:
   - ✅ The table displays with data
   - ✅ Sorting works (click header)
   - ✅ Search works (type in the search box)
   - ✅ Pagination works
   - ✅ Action buttons work

### Test the API Endpoint

```bash
curl -X GET "http://localhost:8000/product/data/list" \
  -H "Accept: application/json" \
  -H "X-Requested-With: XMLHttpRequest"
```

Should return JSON with the structure `{ "data": [...] }`

## Common Issues & Solutions

### Issue: "DataTable is not defined"

**Cause**: The DataTables library hasn't been loaded

**Fix**: Make sure `<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>` exists in the blade template

### Issue: No data showing in the table

**Cause**: API endpoint error or the response structure differs

**Fix**: 
1. Check the browser console for errors
2. Check the network tab for the API response
3. Verify the response structure matches the columns config

### Issue: Delete button not working

**Cause**: Missing CSRF token or no DELETE route

**Fix**:
1. Add the meta tag in the master layout: `<meta name="csrf-token" content="{{ csrf_token() }}">`
2. Verify the DELETE route in routes/web.php
3. Check the browser console for fetch errors

## Performance Tips

1. **Large Datasets**: Use server-side pagination (see advanced documentation)
2. **Caching**: Cache the API response with Redis
3. **Indexing**: Add database indexes to columns that are frequently searched/sorted
4. **Lazy Loading**: Only load DataTables JavaScript on the index pages that need it

## Next Steps

- Read the full documentation: [DATATABLE_INTEGRATION.md](./DATATABLE_INTEGRATION.md)
- Check the [DataTables Official Docs](https://datatables.net/)
- Implement server-side pagination for large datasets
