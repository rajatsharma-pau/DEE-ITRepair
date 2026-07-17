<?php

namespace App\Support;

class RepairRequestWorkflowAccess
{
    public static function employee($user = null)
    {
        $user = $user ?: auth()->user();
        return $user && isset($user->employee) ? $user->employee : null;
    }

    public static function hasRole($user, $role)
    {
        if (!$user) {
            return false;
        }

        if (method_exists($user, 'hasRole')) {
            return $user->hasRole($role);
        }

        return isset($user->role) && $user->role === $role;
    }

    public static function hasAnyRole($user, array $roles)
    {
        if (!$user) {
            return false;
        }

        if (method_exists($user, 'hasAnyRole')) {
            return $user->hasAnyRole($roles);
        }

        foreach ($roles as $role) {
            if (self::hasRole($user, $role)) {
                return true;
            }
        }

        return false;
    }

    public static function employeeHasCharge($employee, $chargeName)
    {
        if (!$employee) {
            return false;
        }

        if (method_exists($employee, 'hasActiveCharge')) {
            return $employee->hasActiveCharge($chargeName);
        }

        return false;
    }

    public static function isAssignedToCurrentEmployee($repairRequest, $user = null)
    {
        $employee = self::employee($user);

        if (!$employee || !isset($repairRequest->assigned_to_employee_id)) {
            return false;
        }

        return (int) $repairRequest->assigned_to_employee_id === (int) $employee->id;
    }

    public static function canShowStorekeeperEstimatePanel($repairRequest, $user = null)
    {
        $user = $user ?: auth()->user();

        if (!self::hasAnyRole($user, array('superuser', 'storekeeper'))) {
            return false;
        }

        $handler = isset($repairRequest->current_handler_role) ? $repairRequest->current_handler_role : null;
        $status = isset($repairRequest->status) ? $repairRequest->status : null;

        return in_array($handler, array(null, '', 'storekeeper'))
            || in_array($status, array('Submitted to Storekeeper', 'Estimate Taken by Storekeeper'));
    }

    public static function canShowVerificationPanel($repairRequest, $user = null)
    {
        $user = $user ?: auth()->user();
        $employee = self::employee($user);
        $handler = isset($repairRequest->current_handler_role) ? $repairRequest->current_handler_role : null;

        if (!in_array($handler, array('programmer', 'store_incharge'))) {
            return false;
        }

        if (self::isAssignedToCurrentEmployee($repairRequest, $user)) {
            return true;
        }

        if ($handler === 'programmer' && self::hasAnyRole($user, array('superuser', 'programmer'))) {
            return true;
        }

        if ($handler === 'store_incharge' && (self::hasRole($user, 'superuser') || self::employeeHasCharge($employee, 'Store Incharge') || self::hasRole($user, 'store_incharge'))) {
            return true;
        }

        return false;
    }

    public static function canShowD4SubmitPanel($repairRequest, $user = null)
    {
        $user = $user ?: auth()->user();

        if (!self::hasAnyRole($user, array('superuser', 'storekeeper'))) {
            return false;
        }

        $handler = isset($repairRequest->current_handler_role) ? $repairRequest->current_handler_role : null;
        $status = isset($repairRequest->status) ? $repairRequest->status : null;

        return $handler === 'storekeeper'
            && in_array($status, array(
                'Programmer Verified Estimate OK',
                'Store Incharge Verified Estimate OK',
                'Estimate Verified OK',
                'Returned to Storekeeper After Verification'
            ));
    }

    public static function canShowD4SeatPanel($repairRequest, $user = null)
    {
        $user = $user ?: auth()->user();
        return self::hasAnyRole($user, array('superuser', 'd4_seat'));
    }

    public static function verifierLabel($repairRequest)
    {
        $handler = isset($repairRequest->current_handler_role) ? $repairRequest->current_handler_role : null;

        if ($handler === 'store_incharge') {
            return 'Store Incharge';
        }

        return 'Programmer';
    }
}
