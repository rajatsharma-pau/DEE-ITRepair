@extends('layouts.app')
@section('content')
<h4>Sections Master</h4>
<div class="alert alert-info">
    Sections are now linked with College / Directorate and Department / Office / KVK. The old separate Directorates table is no longer used.
</div>
<div class="card mb-3">
    <div class="card-body">
        <form method="POST" action="{{ route('masters.sections.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-3 form-group">
                    <label>College / Directorate</label>
                    <select id="section_college_id" name="college_id" class="form-control">
                        <option value="">Select</option>
                        @foreach($colleges as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>Department / Office / KVK</label>
                    <select id="section_department_id" name="department_id" class="form-control">
                        <option value="">Select</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}" data-college="{{ $d->college_id }}">{{ $d->name }}{{ $d->place ? ' - '.$d->place : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 form-group"><label>Section</label><input name="name" class="form-control" placeholder="Section" required></div>
                <div class="col-md-2 form-group"><label>Short</label><input name="short_name" class="form-control" placeholder="Short"></div>
                <div class="col-md-1 form-group pt-4"><label><input type="checkbox" name="is_active" checked> Active</label></div>
                <div class="col-md-1 form-group pt-4"><button class="btn btn-success">Add</button></div>
            </div>
        </form>
    </div>
</div>
<table class="table table-bordered table-sm">
    <thead><tr><th>College / Directorate</th><th>Department / Office / KVK</th><th>Section</th><th>Short</th><th>Active</th></tr></thead>
    <tbody>
        @foreach($items as $i)
            <tr>
                <td>{{ optional($i->college)->name }}</td>
                <td>{{ optional($i->department)->name }}{{ optional($i->department)->place ? ' - '.optional($i->department)->place : '' }}</td>
                <td>{{ $i->name }}</td>
                <td>{{ $i->short_name }}</td>
                <td>{{ $i->is_active ? 'Yes' : 'No' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection

@push('scripts')
<script>
function filterSectionDepartments(){
    var collegeId = $('#section_college_id').val();
    $('#section_department_id option').each(function(){
        var optCollege = $(this).data('college');
        if(!$(this).val() || !collegeId || optCollege == collegeId){ $(this).show(); } else { $(this).hide(); }
    });
    var selected = $('#section_department_id option:selected');
    if(collegeId && selected.val() && selected.data('college') != collegeId){
        $('#section_department_id').val('');
    }
}
$('#section_college_id').on('change', filterSectionDepartments);
filterSectionDepartments();
</script>
@endpush
