<div class="form-group">
    <label>Name <span class="text-danger">*</span></label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $college->name ?? '') }}" required>
</div>
<div class="form-group">
    <label>Short Name</label>
    <input type="text" name="short_name" class="form-control" value="{{ old('short_name', $college->short_name ?? '') }}">
</div>
<div class="form-group form-check">
    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" {{ old('is_active', $college->is_active ?? 1) ? 'checked' : '' }}>
    <label for="is_active" class="form-check-label">Active</label>
</div>
