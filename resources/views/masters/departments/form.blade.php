<div class="form-group">
    <label>College / Directorate <span class="text-danger">*</span></label>
    <select name="college_id" class="form-control" required>
        <option value="">Select</option>
        @foreach($colleges as $college)
            <option value="{{ $college->id }}" {{ old('college_id', $department->college_id ?? '') == $college->id ? 'selected' : '' }}>{{ $college->name }}</option>
        @endforeach
    </select>
</div>
<div class="form-group">
    <label>Department / Office / KVK Name <span class="text-danger">*</span></label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $department->name ?? '') }}" required>
</div>
<div class="form-group">
    <label>Place</label>
    <input type="text" name="place" class="form-control" value="{{ old('place', $department->place ?? 'Ludhiana') }}">
</div>
<div class="form-group form-check">
    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" {{ old('is_active', $department->is_active ?? 1) ? 'checked' : '' }}>
    <label for="is_active" class="form-check-label">Active</label>
</div>
