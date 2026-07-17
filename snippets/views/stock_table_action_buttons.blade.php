{{-- Use this pattern inside stock table action column --}}
<a href="{{ route('store.items.show', $item->id) }}" class="btn btn-sm btn-info">View</a>

@if(\App\Support\StoreAccessScope::canManageRecord($item))
    <a href="{{ route('store.items.edit', $item->id) }}" class="btn btn-sm btn-warning">Edit</a>

    <form action="{{ route('store.items.destroy', $item->id) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Delete this item?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
    </form>
@endif
