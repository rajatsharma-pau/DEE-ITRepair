@extends('layouts.app')
@section('content')
<div class="card"><div class="card-header">New Store Indent</div><div class="card-body">
<form method="POST" action="{{ route('store-indents.store') }}">@csrf
<div class="row">
    <div class="col-md-3 form-group">
        <label>Required Date <span class="text-danger">*</span></label>
        <input type="date" name="required_date" class="form-control" value="{{ old('required_date', isset($requiredDate) ? $requiredDate : date('Y-m-d')) }}" required>
        <small class="form-text text-muted">Auto-filled with today's date. You may change it if items are required on another date.</small>
    </div>
    <div class="col-md-9 form-group">
        <label>Remarks</label>
        <input name="employee_remarks" class="form-control" value="{{ old('employee_remarks') }}">
    </div>
</div>
<table class="table table-bordered" id="itemsTable"><thead><tr><th>Store Item</th><th width="180">Available Stock</th><th width="180">Requested Qty</th><th width="80"></th></tr></thead><tbody>
<tr>
<td><select name="store_item_id[]" class="form-control item-select" required><option value="">-- Select --</option>@foreach($items as $item)<option value="{{ $item->id }}" data-stock="{{ $item->current_stock }} {{ $item->unit }}">{{ $item->name }} (Stock: {{ $item->current_stock }} {{ $item->unit }})</option>@endforeach</select></td>
<td class="stock-text">-</td><td><input type="number" step="0.01" name="requested_qty[]" class="form-control" required></td><td><button type="button" class="btn btn-danger btn-sm remove-row">X</button></td>
</tr>
</tbody></table>
<button type="button" id="addRow" class="btn btn-info">Add More</button>
<button class="btn btn-success">Submit Indent</button>
<a href="{{ route('store-indents.index') }}" class="btn btn-secondary">Back</a>
</form>
</div></div>
@endsection
@push('scripts')
<script>
$(document).on('change','.item-select',function(){ $(this).closest('tr').find('.stock-text').text($(this).find(':selected').data('stock') || '-'); });
$('#addRow').click(function(){ var row=$('#itemsTable tbody tr:first').clone(); row.find('input').val(''); row.find('select').val(''); row.find('.stock-text').text('-'); $('#itemsTable tbody').append(row); });
$(document).on('click','.remove-row',function(){ if($('#itemsTable tbody tr').length>1) $(this).closest('tr').remove(); });
</script>
@endpush
