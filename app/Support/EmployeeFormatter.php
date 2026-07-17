<?php

namespace App\Support;

class EmployeeFormatter
{
    public static function displayName($employee)
    {
        if (!$employee) {
            return '';
        }

        $salutation = trim((string)($employee->salutation ?? ''));
        $name = trim((string)($employee->full_name ?? ''));

        if ($name === '') {
            $parts = array_filter([
                $employee->first_name ?? '',
                $employee->middle_name ?? '',
                $employee->last_name ?? '',
            ]);
            $name = trim(implode(' ', $parts));
        }

        if ($salutation !== '') {
            // Remove duplicate salutation from the beginning of full_name.
            $pattern = '/^' . preg_quote($salutation, '/') . '\s+/i';
            $name = preg_replace($pattern, '', $name);
            return trim($salutation . ' ' . $name);
        }

        // Also clean accidental repeated titles like "Dr. Dr. Amit".
        $name = preg_replace('/^(Dr\.?|Mr\.?|Ms\.?|Mrs\.?)\s+\1\s+/i', '$1 ', $name);

        return trim($name);
    }
}
