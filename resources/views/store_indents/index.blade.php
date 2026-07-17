@extends('layouts.app')
@section('content')
@php
    $canCreateIndent = isset($canCreateIndent) ? $canCreateIndent : (\App\Support\AccessScope::isEmployeeOnly(Auth::user()) || \App\Support\StoreAccessScope::isStorekeeper(Auth::user()) || \App\Support\StoreAccessScope::isSuperuser(Auth::user()));
@endphp

<div class="d-flex justify-content-between mb-3">
    <h4>Store Indents</h4>
    @if($canCreateIndent)
        <a href="{{ route('store-indents.create') }}" class="btn btn-success">New Indent</a>
    @endif
</div>

<div class="card"><div class="card-body table-responsive"><table class="table table-bordered table-sm">
<thead><tr><th>Indent No</th><th>Employee</th><th>Items</th><th>Status</th><th>Required Date</th><th>Issued By</th><th>Action</th></tr></thead><tbody>
@forelse($indents as $indent)
<tr>
<td>{{ $indent->indent_no }}</td>
<td>{{ optional($indent->employee)->display_name }}</td>
<td>@foreach($indent->items as $line){{ optional($line->storeItem)->name }} ({{ $line->requested_qty }})<br>@endforeach</td>
<td><span class="badge badge-info">{{ $indent->status }}</span></td>
<td>{{ optional($indent->required_date)->format('d-m-Y') }}</td>
<td>{{ optional($indent->issuedBy)->display_name }}</td>
<td><a class="btn btn-sm btn-primary" href="{{ route('store-indents.show',$indent) }}">View</a></td>
</tr>
@empty <tr><td colspan="7">No indent found.</td></tr> @endforelse
</tbody></table>{{ $indents->links() }}</div></div>
@endsection
