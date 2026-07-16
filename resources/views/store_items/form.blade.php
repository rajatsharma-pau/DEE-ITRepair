@csrf
<div class="row">
<div class="col-md-4 form-group"><label>College / Directorate</label><select id="stock_college_id" name="college_id" class="form-control"><option value="">Select</option>@foreach($colleges as $c)<option value="{{ $c->id }}" {{ old('college_id',$item->college_id)==$c->id?'selected':'' }}>{{ $c->name }}</option>@endforeach</select></div>
<div class="col-md-4 form-group"><label>Department / Office / KVK</label><select id="stock_department_id" name="department_id" class="form-control"><option value="">Select</option>@foreach($departments as $d)<option value="{{ $d->id }}" data-college="{{ $d->college_id }}" {{ old('department_id',$item->department_id)==$d->id?'selected':'' }}>{{ $d->name }}{{ $d->place ? ' - '.$d->place : '' }}</option>@endforeach</select></div>
</div>
<div class="row">
<div class="col-md-3 form-group"><label>Item Code</label><input name="item_code" class="form-control" value="{{ old('item_code',$item->item_code) }}"></div>
<div class="col-md-4 form-group"><label class="required">Item Name</label><input name="name" class="form-control" required value="{{ old('name',$item->name) }}" placeholder="A4 Paper, Pen, File Cover"></div>
<div class="col-md-3 form-group"><label>Category</label><input name="category" class="form-control" value="{{ old('category',$item->category) }}" placeholder="Stationery"></div>
<div class="col-md-2 form-group"><label class="required">Unit</label><input name="unit" class="form-control" required value="{{ old('unit',$item->unit) }}" placeholder="Nos/Ream/Packet"></div>
</div>
<div class="row">
<div class="col-md-3 form-group"><label>Brand</label><input name="brand" class="form-control" value="{{ old('brand',$item->brand) }}"></div>
@if(!$item->exists)<div class="col-md-3 form-group"><label>Opening Stock</label><input type="number" step="0.01" name="opening_stock" class="form-control" value="{{ old('opening_stock',$item->opening_stock) }}"></div>@endif
<div class="col-md-3 form-group"><label>Reorder Level</label><input type="number" step="0.01" name="reorder_level" class="form-control" value="{{ old('reorder_level',$item->reorder_level) }}"></div>
<div class="col-md-3 form-group"><label>Location</label><input name="location" class="form-control" value="{{ old('location',$item->location) }}"></div>
</div>
<div class="form-group"><label>Description</label><textarea name="description" class="form-control">{{ old('description',$item->description) }}</textarea></div>
<div class="form-group"><label><input type="checkbox" name="is_active" value="1" {{ old('is_active',$item->is_active) ? 'checked' : '' }}> Active</label></div>
<button class="btn btn-success">Save</button><a href="{{ route('store-items.index') }}" class="btn btn-secondary">Back</a>

@push('scripts')
<script>
function filterStockDepartments(){
 var collegeId = $('#stock_college_id').val();
 $('#stock_department_id option').each(function(){ var optCollege=$(this).data('college'); if(!$(this).val() || !collegeId || optCollege==collegeId){$(this).show();} else {$(this).hide();} });
 var selected=$('#stock_department_id option:selected'); if(collegeId && selected.val() && selected.data('college') != collegeId){ $('#stock_department_id').val(''); }
}
$('#stock_college_id').on('change', filterStockDepartments); filterStockDepartments();
</script>
@endpush
