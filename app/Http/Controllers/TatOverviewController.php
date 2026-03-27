<?php

namespace App\Http\Controllers;

use App\Models\ChecklistTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TatOverviewController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = ChecklistTask::query()
                ->leftJoin('checklist_scheduling_extras as cse', 'checklist_tasks.checklist_scheduling_id', '=', 'cse.id')
                ->leftJoin('stores as s', 'cse.store_id', '=', 's.id')
                ->leftJoin('users as u', 'cse.user_id', '=', 'u.id')
                ->leftJoin('form_versions as fv', 'checklist_tasks.version_id', '=', 'fv.id')
                ->leftJoin('dynamic_forms as df', 'fv.checklist_id', '=', 'df.id')
                ->where('checklist_tasks.type', 0)
                ->where(function ($builder) {
                    $builder->whereNull('checklist_tasks.deleted_at')->orWhere('checklist_tasks.deleted_at', '');
                })
                ->where(function ($builder) {
                    $builder->whereNull('s.deleted_at')->orWhere('s.deleted_at', '');
                })
                ->where(function ($builder) {
                    $builder->whereNull('u.deleted_at')->orWhere('u.deleted_at', '');
                })
                ->where(function ($builder) {
                    $builder->whereNull('fv.deleted_at')->orWhere('fv.deleted_at', '');
                })
                ->where(function ($builder) {
                    $builder->whereNull('df.deleted_at')->orWhere('df.deleted_at', '');
                })
                ->select([
                    'checklist_tasks.id',
                    'checklist_tasks.type',
                    'checklist_tasks.checklist_scheduling_id',
                    'checklist_tasks.workflow_checklist_id',
                    'checklist_tasks.version_id',
                    'checklist_tasks.code',
                    'checklist_tasks.date',
                    'checklist_tasks.started_at',
                    'checklist_tasks.completion_date',
                    'checklist_tasks.status',
                    's.id as store_id',
                    's.code as store_code',
                    's.name as store_name',
                    'u.id as user_id',
                    'u.name as user_name',
                    'df.id as checklist_id',
                    'df.name as checklist_name',
                    'df.is_store_checklist as is_store_checklist',
                ]);

            $query->when($request->inspection_type === '0' || $request->inspection_type === '1', function ($builder) use ($request) {
                $builder->where('df.is_store_checklist', intval($request->inspection_type));
            });

            $query->when(!empty($request->store), function ($builder) {
                $builder->whereIn('cse.store_id', (array) request('store'));
            });

            $query->when(!empty($request->user), function ($builder) {
                $builder->whereIn('cse.user_id', (array) request('user'));
            });

            $query->when(!empty($request->checklist), function ($builder) {
                $builder->whereIn('df.id', (array) request('checklist'));
            });

            $query->when(!empty($request->from), function ($builder) {
                $builder->where(DB::raw("DATE_FORMAT(checklist_tasks.date, '%Y-%m-%d')"), '>=', date('Y-m-d', strtotime(request('from'))));
            });

            $query->when(!empty($request->to), function ($builder) {
                $builder->where(DB::raw("DATE_FORMAT(checklist_tasks.date, '%Y-%m-%d')"), '<=', date('Y-m-d', strtotime(request('to'))));
            });

            $query->when(in_array($request->status, [1, 2, 3]), function ($builder) {
                $builder->where('checklist_tasks.status', request('status'));
            }, function ($builder) {
                $builder->whereIn('checklist_tasks.status', [1, 2, 3]);
            });

            $avgMinutes = (clone $query)
                ->selectRaw("AVG(GREATEST(0, IFNULL(TIMESTAMPDIFF(MINUTE, checklist_tasks.started_at, checklist_tasks.completion_date), 0))) as avg_minutes")
                ->value('avg_minutes');

            $avgMinutes = is_numeric($avgMinutes) ? floatval($avgMinutes) : 0.0;

            return datatables()
                ->eloquent($query->orderBy('checklist_tasks.id', 'DESC'))
                ->addColumn('inspection_type', function ($row) {
                    return intval($row->is_store_checklist ?? 0) === 1 ? 'Store Checklist' : 'DoM Checklist';
                })
                ->addColumn('store', function ($row) {
                    $code = trim((string) ($row->store_code ?? ''));
                    $name = trim((string) ($row->store_name ?? ''));
                    if ($code !== '' && $name !== '') {
                        return "{$code} - {$name}";
                    }
                    return $name !== '' ? $name : $code;
                })
                ->addColumn('user', function ($row) {
                    return $row->user_name ?? '';
                })
                ->addColumn('checklist', function ($row) {
                    return $row->checklist_name ?? '';
                })
                ->editColumn('date', function ($row) {
                    return $row->date ? date('d-m-Y', strtotime($row->date)) : '';
                })
                ->addColumn('status_label', function ($row) {
                    $status = intval($row->status ?? 0);
                    if ($status === 0) {
                        return '<span class="badge bg-warning">Pending</span>';
                    }
                    if ($status === 1) {
                        return '<span class="badge bg-info">In-Progress</span>';
                    }
                    if ($status === 2) {
                        return '<span class="badge bg-secondary">Pending Verification</span>';
                    }
                    if ($status === 3) {
                        return '<span class="badge bg-success">Verified</span>';
                    }
                    return '<span class="badge bg-light text-dark">Unknown</span>';
                })
                ->addColumn('tat', function ($row) {
                    if (empty($row->started_at) || empty($row->completion_date)) {
                        return '0';
                    }

                    $start = \Carbon\Carbon::parse($row->started_at);
                    $end   = \Carbon\Carbon::parse($row->completion_date);

                    if ($end->lte($start)) {
                        return '0';
                    }

                    $diffMinutes = $end->diffInMinutes($start);

                    return $diffMinutes > 0 ? self::formatMinutes($diffMinutes) : '0';
                })
                ->addColumn('action', function ($row) {
                    return '<a class="btn btn-sm btn-secondary" href="'.route('checklists-submission-view-for-maker', encrypt($row->id)).'">View</a>';
                })
                ->rawColumns(['status_label', 'action'])
                ->with([
                    'avg_tat_minutes' => $avgMinutes,
                    'avg_tat_label' => self::formatMinutes((int) round($avgMinutes)),
                ])
                ->toJson();
        }

        $page_title = 'TAT Overview';
        $page_description = 'Turn Around Time (TAT) overview for inspection tasks';
        return view('tat-overview.index', compact('page_title', 'page_description'));
    }

    private static function formatMinutes(int $minutes): string
    {
        if ($minutes <= 0) {
            return '0 minutes';
        }

        $days = intdiv($minutes, 1440);
        $remainingMinutes = $minutes % 1440;

        $hours = intdiv($remainingMinutes, 60);
        $remainingMinutes = $remainingMinutes % 60;

        $parts = [];

        if ($days > 0) {
            $parts[] = $days . ' ' . ($days === 1 ? 'day' : 'days');
        }

        if ($hours > 0) {
            $parts[] = $hours . ' ' . ($hours === 1 ? 'hour' : 'hours');
        }

        if ($remainingMinutes > 0) {
            $parts[] = $remainingMinutes . ' ' . ($remainingMinutes === 1 ? 'minute' : 'minutes');
        }

        return implode(' ', $parts);
    }
}

