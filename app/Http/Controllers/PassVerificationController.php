<?php

namespace App\Http\Controllers;

use App\Models\GatePassLog;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\Helper;

class PassVerificationController extends Controller
{
    public function index(Request $request)
    {
        ini_set('memory_limit', '-1');

        if ($request->ajax()) {

            if (!empty($request->loc)) {
                $stores = Store::selectRaw("id, CONCAT(code, ' - ', name) as name")->whereIn('id', $request->loc)->pluck('name', 'id')->toArray();
                session()->put(['pass_verification_loc' => $stores]);
            } else {
                session()->forget('pass_verification_loc');
            }

            if (!empty($request->verified_by)) {
                $users = User::select('id', 'name')->whereIn('id', $request->verified_by)->pluck('name', 'id')->toArray();
                session()->put(['pass_verification_verified_by' => $users]);
            } else {
                session()->forget('pass_verification_verified_by');
            }

            if (!empty($request->validation_type)) {
                session()->put(['pass_verification_validation_type' => $request->validation_type]);
            } else {
                session()->forget('pass_verification_validation_type');
            }

            if (!empty($request->is_valid)) {
                session()->put(['pass_verification_is_valid' => $request->is_valid]);
            } else {
                session()->forget('pass_verification_is_valid');
            }

            if (!empty($request->entry_type)) {
                session()->put(['pass_verification_entry_type' => $request->entry_type]);
            } else {
                session()->forget('pass_verification_entry_type');
            }

            if (!empty($request->from)) {
                session()->put(['pass_verification_from' => $request->from]);
            } else {
                session()->forget('pass_verification_from');
            }

            if (!empty($request->to)) {
                session()->put(['pass_verification_to' => $request->to]);
            } else {
                session()->forget('pass_verification_to');
            }
            
            if ($request->has('pass_number')) {
                session()->put(['pass_verification_pass_number' => $request->pass_number]);
            } else {
                session()->forget('pass_verification_pass_number');
            }

            $allStoreName = Store::withoutGlobalScope('os')->selectRaw("id, CONCAT(COALESCE(code, ''), ' - ', COALESCE(name, '')) as name")->pluck('name', 'id')->toArray();
            $allEmployees = User::selectRaw("id, CONCAT(COALESCE(employee_id, ''), ' - ', COALESCE(name, ''), ' ', COALESCE(middle_name, ''), ' ', COALESCE(last_name, '')) as name")
            ->pluck('name', 'id')->toArray();

            $gatePassLogs = GatePassLog::query()
                ->with(['task.parent.parent'])
                ->when(!empty($request->loc), function ($builder) use ($request) {
                    $builder->whereHas('task.parent', function ($innerBuilder) use ($request) {
                        $innerBuilder->whereIn('store_id', $request->loc);
                    });
                })
                ->when(!empty($request->verified_by), function ($builder) use ($request) {
                    $builder->whereIn('verified_by', $request->verified_by);
                })
                ->when(!empty($request->validation_type), function ($builder) use ($request) {
                    $builder->whereIn('validation_type', $request->validation_type);
                })
                ->when(!empty($request->is_valid), function ($builder) use ($request) {
                    $builder->whereIn('is_valid', $request->is_valid);
                })
                ->when(!empty($request->entry_type), function ($builder) use ($request) {
                    $builder->whereIn('entry_type', $request->entry_type);
                })
                ->when(!empty($request->pass_number), function ($builder) use ($request) {
                    $builder->where('entered_pass_number', 'like', '%' . $request->pass_number . '%');
                })
                ->when(!empty($request->from), function ($builder) use ($request) {
                    $builder->where(\DB::raw("DATE_FORMAT(gate_pass_logs.created_at, '%Y-%m-%d')"), '>=', date('Y-m-d', strtotime($request->from)));
                })
                ->when(!empty($request->to), function ($builder) use ($request) {
                    $builder->where(\DB::raw("DATE_FORMAT(gate_pass_logs.created_at, '%Y-%m-%d')"), '<=', date('Y-m-d', strtotime($request->to)));
                })
                ->orderBy('id', 'DESC');

            return datatables()
                ->eloquent($gatePassLogs)
                ->addColumn('location_name', function ($row) use ($allStoreName) {
                    return isset($row->task->parent->store_id) && isset($allStoreName[$row->task->parent->store_id]) ? $allStoreName[$row->task->parent->store_id] : '-';
                })
                ->addColumn('task_title', function ($row) {
                    return $row->task->parent->parent->title ?? '-';
                })
                ->addColumn('verified_by_name', function ($row) use ($allEmployees) {
                    return isset($row->verified_by) && isset($allEmployees[$row->verified_by]) ? $allEmployees[$row->verified_by] : '-';
                })
                ->editColumn('entered_pass_number', function ($row) {
                    return $row->entered_pass_number ?: '-';
                })
                ->editColumn('validation_type', function ($row) {
                    return $row->validation_type == 0 ? '<span class="badge bg-primary">Scanned</span>' : '<span class="badge bg-secondary">Manual</span>';
                })
                ->editColumn('is_valid', function ($row) {
                    return $row->is_valid == 0 ? '<span class="badge bg-success">Valid</span>' : '<span class="badge bg-danger">Invalid</span>';
                })
                ->editColumn('entry_type', function ($row) {
                    return $row->entry_type == 1 ? '<span class="badge bg-info">Entry</span>' : '<span class="badge bg-warning">Exit</span>';
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at ? date('d-m-Y H:i', strtotime($row->created_at)) : '-';
                })
                ->rawColumns(['validation_type', 'is_valid', 'entry_type'])
                ->toJson();
        }

        $page_title = 'Pass Verifications';
        $page_description = 'Manage and export Pass Verifications reports here';
        return view('pass-verifications.index', compact('page_title', 'page_description'));
    }
}
