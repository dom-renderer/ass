<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;
use Spatie\Permission\Models\Role;
use App\Models\Designation;
use App\Models\Department;
use App\Helpers\Helper;
use App\Models\Store;
use App\Models\User;

class UsersExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        $data = [];

        try {
            $helperRoles = Helper::$rolesKeys;
            $rolesOtherThanSystem = Role::whereNotIn('id', array_keys($helperRoles))
            ->pluck('name', 'id')
            ->map(fn($name) => \Illuminate\Support\Str::slug($name))
            ->toArray();

            if (!empty($rolesOtherThanSystem)) {
                $helperRoles = $helperRoles + $rolesOtherThanSystem;
            }

            foreach (User::get() as $user) {
                $thisRole = $user->roles[0]->id;
                $stores = '';

                if (in_array($thisRole, [Helper::$roles['store-manager'], Helper::$roles['store-employee'], Helper::$roles['store-cashier']])) {
                    $tempStores = Designation::select('type_id')
                        ->where('user_id', $user->id)
                        ->where('type', 1)
                        ->pluck('type_id')
                        ->toArray();

                    if (!empty($tempStores)) {
                        $stores = implode(' , ', Store::select('code')->whereIn('id', $tempStores)->pluck('code')->toArray());
                    }
                } else if (in_array($thisRole, [Helper::$roles['divisional-operations-manager'], Helper::$roles['head-of-department'], Helper::$roles['operations-manager']])) {
                    $tempStores = Designation::select('type_id')
                        ->where('user_id', $user->id)
                        ->where('type', 3)
                        ->pluck('type_id')
                        ->toArray();

                    if (!empty($tempStores)) {
                        $stores = implode(' , ', Department::selectRaw('LOWER(name) as name')->whereIn('id', $tempStores)->pluck('name')->toArray());
                    }
                }

                $data[] = [
                    $user->name,
                    $user->middle_name,
                    $user->last_name,
                    $user->email,
                    $user->employee_id,
                    $user->username,
                    $user->phone_number,
                    $user->status == 1 ? 'enable' : 'disable',
                    '',
                    isset($helperRoles[$thisRole]) ? $helperRoles[$thisRole] : null,
                    $stores
                ];
            }

            return $data;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error($e->getMessage() . 'on line ' . $e->getLine() . ' in file ' . $e->getFile());
            return [];
        }
    }

    public function headings(): array
    {
        return [
            'first name',
            'middle name',
            'last name',
            'email',
            'employee id',
            'username',
            'phone number',
            'status',
            'password',
            'role',
            'department'
        ];
    }
}
