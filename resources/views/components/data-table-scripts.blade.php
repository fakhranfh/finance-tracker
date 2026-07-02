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

// Build URL with filter query params collected from data-filter-table inputs
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

    const qs = params.toString();
    return baseUrl + (qs ? '?' + qs : '');
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
