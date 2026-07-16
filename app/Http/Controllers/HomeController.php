<?php

namespace App\Http\Controllers;

use App\Employee;
use App\RepairRequest;
use App\Asset;
use App\StoreItem;
use App\StoreIndent;
use Illuminate\Support\Facades\Auth;
use App\Support\AccessScope;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        $employee = $user->employee;
        $isEmployeeOnly = AccessScope::isEmployeeOnly($user);

        if ($isEmployeeOnly) {
            return $this->employeeDashboard($user, $employee);
        }

        return $this->roleDashboard($user, $employee);
    }

    protected function employeeDashboard($user, $employee)
    {
        $requestQuery = RepairRequest::query();
        $assetQuery = Asset::query();
        $indentQuery = StoreIndent::query();

        if ($employee) {
            $requestQuery->where('employee_id', $employee->id);
            $assetQuery->where('assigned_to_employee_id', $employee->id)->where('asset_state', 'With Employee');
            $indentQuery->where('employee_id', $employee->id);
        } else {
            $requestQuery->whereRaw('1 = 0');
            $assetQuery->whereRaw('1 = 0');
            $indentQuery->whereRaw('1 = 0');
        }

        $cards = [
            [
                'title' => 'New Repair Request',
                'count' => '+',
                'url' => route('repair-requests.create'),
                'text' => 'Submit repair / material request',
                'class' => 'success',
            ],
            [
                'title' => 'My Pending Requests',
                'count' => (clone $requestQuery)->whereNotIn('status', ['Closed','Rejected'])->count(),
                'url' => route('repair-requests.index'),
                'text' => 'Requests under process',
                'class' => 'warning',
            ],
            [
                'title' => 'My Closed Requests',
                'count' => (clone $requestQuery)->where('status', 'Closed')->count(),
                'url' => route('repair-requests.index', ['status' => 'Closed']),
                'text' => 'Completed requests',
                'class' => 'info',
            ],
            [
                'title' => 'My Assets',
                'count' => (clone $assetQuery)->count(),
                'url' => route('assets.index'),
                'text' => 'Allocated assets',
                'class' => 'primary',
            ],
            [
                'title' => 'New Store Indent',
                'count' => '+',
                'url' => route('store-indents.create'),
                'text' => 'Ask for stationery / store items',
                'class' => 'success',
            ],
            [
                'title' => 'My Indents',
                'count' => (clone $indentQuery)->count(),
                'url' => route('store-indents.index'),
                'text' => 'Store indent status',
                'class' => 'secondary',
            ],
            [
                'title' => 'My Profile',
                'count' => $employee && $employee->photo ? '✓' : '!',
                'url' => route('profile.show'),
                'text' => $employee && $employee->photo ? 'View profile' : 'Upload photo',
                'class' => 'dark',
            ],
        ];

        $requests = RepairRequest::with(['employee','category','assignedTo','selectedEstimate.vendor'])
            ->when($employee, function($q) use ($employee) { $q->where('employee_id', $employee->id); })
            ->latest()->take(8)->get();

        return view('home', [
            'dashboardTitle' => 'Employee Dashboard',
            'dashboardSubtitle' => 'Your requests, assets, indents and profile.',
            'cards' => $cards,
            'requests' => $requests,
            'isEmployeeDashboard' => true,
        ]);
    }

    protected function roleDashboard($user, $employee)
    {
        $employeesCount = AccessScope::apply(Employee::query())->count();
        $storekeeperPending = AccessScope::apply(RepairRequest::where('current_handler_role', 'storekeeper'))->count();
        $programmerVerification = AccessScope::apply(RepairRequest::whereIn('current_handler_role', ['programmer','store_incharge']))->count();
        $d4ManualFiles = AccessScope::apply(RepairRequest::where('current_handler_role', 'd4_seat'))->count();
        $closed = AccessScope::apply(RepairRequest::where('status', 'Closed'))->count();
        $assetsInStore = AccessScope::apply(Asset::where('asset_state', 'In Store'))->count();
        $assetsWithEmployee = AccessScope::apply(Asset::where('asset_state', 'With Employee'))->count();
        $lowStockItems = AccessScope::apply(StoreItem::whereRaw('current_stock <= reorder_level'))->count();
        $pendingIndents = AccessScope::apply(StoreIndent::where('status', 'Submitted'))->count();

        $cards = [
            ['title' => 'Employees', 'count' => $employeesCount, 'url' => route('employees.index'), 'text' => 'Employee master', 'class' => 'primary'],
            ['title' => 'Storekeeper Pending', 'count' => $storekeeperPending, 'url' => route('repair-requests.index', ['handler' => 'storekeeper']), 'text' => 'Action required', 'class' => 'warning'],
            ['title' => 'Verification Pending', 'count' => $programmerVerification, 'url' => route('repair-requests.index', ['handler' => 'programmer']), 'text' => 'Programmer / Store Incharge', 'class' => 'info'],
            ['title' => 'D-4 Manual Files', 'count' => $d4ManualFiles, 'url' => route('repair-requests.index', ['handler' => 'd4_seat']), 'text' => 'Manual process', 'class' => 'secondary'],
            ['title' => 'Closed Requests', 'count' => $closed, 'url' => route('repair-requests.index', ['status' => 'Closed']), 'text' => 'Completed', 'class' => 'success'],
            ['title' => 'Assets In Store', 'count' => $assetsInStore, 'url' => route('assets.index', ['asset_state' => 'In Store']), 'text' => 'Available assets', 'class' => 'dark'],
            ['title' => 'Assets With Employee', 'count' => $assetsWithEmployee, 'url' => route('assets.index', ['asset_state' => 'With Employee']), 'text' => 'Allocated assets', 'class' => 'primary'],
            ['title' => 'Low Stock Items', 'count' => $lowStockItems, 'url' => route('store-items.index', ['low_stock' => 1]), 'text' => 'Reorder required', 'class' => 'danger'],
            ['title' => 'Pending Indents', 'count' => $pendingIndents, 'url' => route('store-indents.index', ['status' => 'Submitted']), 'text' => 'Issue store items', 'class' => 'warning'],
        ];

        $baseQuery = RepairRequest::with(['employee','category','assignedTo','selectedEstimate.vendor']);
        AccessScope::apply($baseQuery);

        $handlerRoles = [];
        foreach (['programmer','storekeeper','d4_seat'] as $r) {
            if ($user->hasRole($r)) $handlerRoles[] = $r;
        }
        if ($employee && $employee->hasActiveCharge('Store Incharge')) {
            $handlerRoles[] = 'store_incharge';
        }

        $requests = $baseQuery
            ->when(!empty($handlerRoles) && $employee, function($q) use ($employee, $handlerRoles) {
                $q->where(function($x) use ($employee, $handlerRoles) {
                    $x->where('assigned_to_employee_id', $employee->id)
                      ->orWhereIn('current_handler_role', $handlerRoles);
                });
            })
            ->latest()->take(8)->get();

        return view('home', [
            'dashboardTitle' => 'Dashboard',
            'dashboardSubtitle' => AccessScope::scopeLabel($user),
            'cards' => $cards,
            'requests' => $requests,
            'isEmployeeDashboard' => false,
        ]);
    }
}
