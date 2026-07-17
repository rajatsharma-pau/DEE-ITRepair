<?php
// Put this logic inside EmployeeController@index before paginate().
// It adds superadmin/admin employee search by name, phone, and existing filters.

$search = trim($request->get('q'));

if ($search !== '') {
    $employees->where(function ($query) use ($search) {
        $query->where('full_name', 'like', '%' . $search . '%')
            ->orWhere('first_name', 'like', '%' . $search . '%')
            ->orWhere('middle_name', 'like', '%' . $search . '%')
            ->orWhere('last_name', 'like', '%' . $search . '%')
            ->orWhere('phone', 'like', '%' . $search . '%')
            ->orWhere('employee_code', 'like', '%' . $search . '%')
            ->orWhere('gpf_no', 'like', '%' . $search . '%')
            ->orWhere('nps_no', 'like', '%' . $search . '%')
            ->orWhereHas('user', function ($userQuery) use ($search) {
                $userQuery->where('name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');
            });
    });
}
