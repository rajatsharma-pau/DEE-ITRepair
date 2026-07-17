<div class="form-group">
    <label>Designation Name <span class="text-danger">*</span></label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $designation->name ?? '') }}" required>
</div>
<div class="form-group">
    <label>Cadre</label>
    <select name="cadre" class="form-control">
        @foreach(['Scientific','Administrative','Technical','Supporting','Other'] as $cadre)
            <option value="{{ $cadre }}" {{ old('cadre', $designation->cadre ?? 'Administrative') == $cadre ? 'selected' : '' }}>{{ $cadre }}</option>
        @endforeach
    </select>
</div>
<div class="form-group">
    <label>Sort Order</label>
    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $designation->sort_order ?? 999) }}">
</div>
<div class="form-group form-check">
    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" {{ old('is_active', $designation->is_active ?? 1) ? 'checked' : '' }}>
    <label for="is_active" class="form-check-label">Active</label>
</div>
