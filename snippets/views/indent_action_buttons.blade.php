{{-- Use this pattern in indent show/list action area --}}
<a href="{{ route('indents.show', $indent->id) }}" class="btn btn-sm btn-info">View</a>

@if(\App\Support\StoreAccessScope::canManageRecord($indent))
    @if($indent->status == 'Pending')
        <form action="{{ route('indents.issue', $indent->id) }}" method="POST" style="display:inline-block">
            @csrf
            <button type="submit" class="btn btn-sm btn-success">Issue</button>
        </form>

        <form action="{{ route('indents.reject', $indent->id) }}" method="POST" style="display:inline-block">
            @csrf
            <button type="submit" class="btn btn-sm btn-danger">Reject</button>
        </form>
    @endif
@endif
