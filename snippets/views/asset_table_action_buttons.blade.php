{{-- Use this pattern inside asset table action column --}}
<a href="{{ route('assets.show', $asset->id) }}" class="btn btn-sm btn-info">View</a>

@if(\App\Support\StoreAccessScope::canManageRecord($asset))
    <a href="{{ route('assets.edit', $asset->id) }}" class="btn btn-sm btn-warning">Edit</a>
    <a href="{{ route('assets.allocate', $asset->id) }}" class="btn btn-sm btn-secondary">Allocate</a>
@endif
