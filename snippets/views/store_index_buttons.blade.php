{{-- Put this at the top of stock/assets/indents index views --}}
@php
    $canManageStore = isset($canManageStore) ? $canManageStore : \App\Support\StoreAccessScope::canManageStore();
    $storeViewOnly = \App\Support\StoreAccessScope::isStoreViewOnly();
@endphp

@if($storeViewOnly)
    <div class="alert alert-info">
        You have view-only access for store records. Only Storekeeper can add, edit, issue or reject records.
    </div>
@endif

{{-- Add button example --}}
@if($canManageStore)
    <a href="{{ route('store.items.create') }}" class="btn btn-primary">Add Stock</a>
@endif
