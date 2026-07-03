@push('scripts')
<script>
// Load data for table
function loadTableData(tableId, listUrl, renderCallback) {
    const tbody = document.querySelector(`#${tableId} tbody`);
    if (!tbody) {
        console.error(`Table with id ${tableId} not found`);
        return;
    }

    fetch(listUrl, { credentials: 'include' })
        .then(r => r.json())
        .then(data => {
            tbody.innerHTML = '';
            if (data.data && data.data.length > 0) {
                data.data.forEach(item => {
                    const row = renderCallback(item);
                    tbody.appendChild(row);
                });
            } else {
                const emptyRow = document.createElement('tr');
                emptyRow.innerHTML = `<td colspan="100%" class="px-6 py-4 text-center text-gray-500">{{ __('No data available') }}</td>`;
                tbody.appendChild(emptyRow);
            }
        })
        .catch(e => {
            console.error(`Failed to load data for ${tableId}:`, e);
            const errorRow = document.createElement('tr');
            errorRow.innerHTML = `<td colspan="100%" class="px-6 py-4 text-center text-red-500">{{ __('Failed to load data') }}</td>`;
            tbody.appendChild(errorRow);
        });
}

// Build URL with filter query params collected from data-filter-table inputs,
// plus the current sort column/direction stored on the table itself
function buildFilterUrl(tableId) {
    const table = document.getElementById(tableId);
    const baseUrl = table ? table.dataset.listUrl : '#';
    const params = new URLSearchParams();

    document.querySelectorAll(`[data-filter-table="${tableId}"]`).forEach(input => {
        const value = input.value.trim();
        if (value) {
            params.set(input.dataset.filterKey, value);
        }
    });

    if (table?.dataset.sort) {
        params.set('sort', table.dataset.sort);
        params.set('dir', table.dataset.dir || 'desc');
    }

    const qs = params.toString();
    return baseUrl + (qs ? '?' + qs : '');
}

// Update a table's header sort icons to reflect its current sort column/direction
function updateSortIcons(tableId, column, dir) {
    document.querySelectorAll(`#${tableId} [data-sort-icon]`).forEach((icon) => {
        icon.textContent = icon.dataset.sortIcon === column
            ? (dir === 'asc' ? 'arrow_upward' : 'arrow_downward')
            : '';
    });
}

// Toggle the sort column/direction for a table, update its header icons, and reload
function toggleSort(tableId, column) {
    const table = document.getElementById(tableId);
    if (!table) {
        return;
    }

    const dir = table.dataset.sort === column && table.dataset.dir === 'asc' ? 'desc' : 'asc';

    table.dataset.sort = column;
    table.dataset.dir = dir;

    updateSortIcons(tableId, column, dir);
    applyFilters(tableId);
}

function applyFilters(tableId) {
    const functionName = 'load' + tableId.charAt(0).toUpperCase() + tableId.slice(1);
    if (typeof window[functionName] === 'function') {
        window[functionName]();
    }
}

function resetFilters(tableId) {
    document.querySelectorAll(`[data-filter-table="${tableId}"]`).forEach(input => {
        input.value = '';
    });
    applyFilters(tableId);
}

// Generic delete function
window.deleteItem = function(url) {
    showConfirmModal(url);
};

// Initialize table on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    // Find all tables with data-list-url and load their data
    const tables = document.querySelectorAll('[data-list-url]');
    tables.forEach(table => {
        const tableId = table.id;
        const listUrl = table.dataset.listUrl;

        if (tableId && listUrl) {
            if (table.dataset.sort) {
                updateSortIcons(tableId, table.dataset.sort, table.dataset.dir || 'desc');
            }

            // Call page-specific function if it exists
            const functionName = 'load' + tableId.charAt(0).toUpperCase() + tableId.slice(1);
            if (typeof window[functionName] === 'function') {
                window[functionName]();
            } else {
                console.warn('[DataTable] Function not found:', functionName);
            }
        }
    });
});
</script>
@endpush
