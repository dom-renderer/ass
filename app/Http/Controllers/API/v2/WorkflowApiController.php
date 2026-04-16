<?php

namespace App\Http\Controllers\API\v2;

use Illuminate\Support\Facades\Validator;
use App\Models\NewWorkflowAssignmentItem;
use App\Models\TaskDeviceInformation;
use App\Models\NewWorkflowAssignment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\ChecklistTask;
use \Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\RedoAction;
use App\Helpers\Helper;
use App\Models\User;
use Carbon\Carbon;

class WorkflowApiController extends \App\Http\Controllers\Controller
{
    public function progressDashboard(Request $request)
    {
        $user = auth()->user();
        $fromDate = $request->from_date ? Carbon::parse($request->from_date)->format('Y-m-d') : null;
        $toDate = $request->to_date ? Carbon::parse($request->to_date)->format('Y-m-d') : null;
        $now = Carbon::now();

        if ($request->has('for_checker') && $request->for_checker == 1) {
            $baseQuery = ChecklistTask::workflow()
                ->whereHas('wf.parent', fn($builder) => $builder->where('status', 1))
                ->when(
                    $request->filled('workflow_id'),
                    fn($builder) =>
                    $builder->whereHas('wf', fn($q) => $q->where('new_workflow_assignment_id', $request->workflow_id))
                )
                ->when($fromDate, fn($builder) => $builder->whereDate('date', '>=', $fromDate))
                ->when($toDate, fn($builder) => $builder->whereDate('date', '<=', $toDate))
                ->when(
                    $request->filled('department_id'),
                    fn($builder) =>
                    $builder->whereHas('wf', fn($q) => $q->where('department_id', $request->department_id))
                )
                ->when(
                    $request->filled('section_id'),
                    fn($builder) =>
                    $builder->whereHas('wf', fn($q) => $q->where('section_id', $request->section_id))
                )
                ->whereHas('wf', function ($q) use ($user) {
                    $q->where('checker_id', $user->id);
                });

            $counts = (clone $baseQuery)
                ->selectRaw('
                    COUNT(CASE WHEN status IN (3) THEN 1 END) as completed_count,
                    COUNT(CASE WHEN status = 2 AND verified_at IS NULL THEN 1 END) as pending_count
                ', )
                ->first();

            $overdueCount = ChecklistTask::workflow()
                ->join('new_workflow_assignment_items as wf', 'wf.id', '=', 'checklist_tasks.workflow_checklist_id')
                ->join('new_workflow_assignments as parent', 'parent.id', '=', 'wf.new_workflow_assignment_id')
                ->where('parent.status', 1)
                ->when(
                    $request->filled('workflow_id'),
                    fn($q) =>
                    $q->where('wf.new_workflow_assignment_id', $request->workflow_id)
                )
                ->where('wf.checker_id', auth()->id())
                ->whereIn('checklist_tasks.status', [1])
                ->whereRaw(
                    "
                    DATE_ADD(
                        checklist_tasks.created_at,
                        INTERVAL (
                            COALESCE(wf.checker_turn_around_time_day, 0) * 1440 +
                            COALESCE(wf.checker_turn_around_time_hour, 0) * 60 +
                            COALESCE(wf.checker_turn_around_time_minute, 0)
                        ) MINUTE
                    ) < ?
                    ",
                    [now()]
                )
                ->count();

            $completedCount = $counts->completed_count ?? 0;
            $pendingCount = $counts->pending_count ?? 0;

            $completionPercentage = $pendingCount + $completedCount;
            $completionPercentage = $completionPercentage > 0
                ? ($completedCount / $completionPercentage) * 100
                : 0;

            return response()->json([
                'success' => [
                    'completed_count' => $completedCount,
                    'pending_count' => $pendingCount,
                    'overdue_count' => $overdueCount,
                    'in_progress_count' => 0,
                    'completion_percentage' => round($completionPercentage, 2)
                ]
            ]);
        } else {
            $baseQuery = ChecklistTask::workflow()
                ->whereHas('wf.parent', fn($builder) => $builder->where('status', 1))
                ->when(
                    $request->filled('workflow_id'),
                    fn($builder) =>
                    $builder->whereHas('wf', fn($q) => $q->where('new_workflow_assignment_id', $request->workflow_id))
                )
                ->when($fromDate, fn($builder) => $builder->whereDate('date', '>=', $fromDate))
                ->when($toDate, fn($builder) => $builder->whereDate('date', '<=', $toDate))
                ->when(
                    $request->filled('department_id'),
                    fn($builder) =>
                    $builder->whereHas('wf', fn($q) => $q->where('department_id', $request->department_id))
                )
                ->when(
                    $request->filled('section_id'),
                    fn($builder) =>
                    $builder->whereHas('wf', fn($q) => $q->where('section_id', $request->section_id))
                )
                ->whereHas('wf', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });

            $counts = (clone $baseQuery)
                ->selectRaw('
                    COUNT(CASE WHEN status IN (2, 3) THEN 1 END) as completed_count,
                    COUNT(CASE WHEN status = 0 AND completed_by > ? THEN 1 END) as pending_count,
                    COUNT(CASE WHEN status = 1 THEN 1 END) as in_progress_count
                ', [$now])
                ->first();

            $overdueCount = ChecklistTask::workflow()
                ->whereHas('wf.parent', fn($builder) => $builder->where('status', 1))
                ->when(
                    $request->filled('workflow_id'),
                    fn($builder) =>
                    $builder->whereHas('wf', fn($q) => $q->where('new_workflow_assignment_id', $request->workflow_id))
                )
                ->whereHas('wf', fn($q) => $q->where('user_id', auth()->id()))
                ->whereIn('status', [0, 1])
                ->where('completed_by', '<', $now)
                ->count();

            $completionData = ChecklistTask::workflow()
                ->whereHas('wf.parent', fn($builder) => $builder->where('status', 1))
                ->when($fromDate, fn($builder) => $builder->whereDate('date', '>=', $fromDate))
                ->when($toDate, fn($builder) => $builder->whereDate('date', '<=', $toDate))
                ->whereHas('wf', fn($q) => $q->where('user_id', auth()->id()))
                ->where('status', '!=', 0)
                ->selectRaw('COUNT(*) as count, SUM(percentage) as total_percentage')
                ->first();

            $completionPercentage = $completionData->count > 0
                ? $completionData->total_percentage / $completionData->count
                : 0;

            return response()->json([
                'success' => [
                    'completed_count' => $counts->completed_count ?? 0,
                    'pending_count' => $counts->pending_count ?? 0,
                    'overdue_count' => $overdueCount,
                    'in_progress_count' => $counts->in_progress_count ?? 0,
                    'completion_percentage' => round($completionPercentage, 2)
                ]
            ]);
        }
    }

    public function sectionProgress(Request $request)
    {
        $user = auth()->user();
        $fromDate = $request->from_date ? Carbon::parse($request->from_date) : Carbon::now();
        $toDate = $request->to_date ? Carbon::parse($request->to_date) : Carbon::now();

        if ($request->has('for_checker') && $request->for_checker == 1) {

            $tasks = ChecklistTask::with(['wf.parent'])
                ->where('type', 1)
                ->whereHas('wf.parent', function ($builder) use ($user) {
                    $builder->where('status', 1);
                })
                ->when($request->has('workflow_id'), function ($builder) {
                    return $builder->whereHas('wf', function ($innerBuilder) {
                        $innerBuilder->where('new_workflow_assignment_id', request('workflow_id'));
                    });
                })
                ->when($fromDate, function ($builder) use ($fromDate) {
                    return $builder->whereDate('date', '>=', $fromDate->format('Y-m-d'));
                })
                ->when($toDate, function ($builder) use ($toDate) {
                    return $builder->whereDate('date', '<=', $toDate->format('Y-m-d'));
                })
                ->where(function ($q) use ($user) {
                    $q->whereHas('wf', function ($q2) use ($user) {
                        $q2->where('checker_id', $user->id);
                    });
                })
                ->when($request->has('department_id'), function ($builder) {
                    return $builder->whereHas('wf', function ($innerBuilder) {
                        $innerBuilder->where('department_id', request('department_id'));
                    });
                })
                ->get();

            $grouped = $tasks->groupBy(function ($task) {
                return $task->wf->section_name ?? 'Unknown Section';
            });

            $result = [];
            foreach ($grouped as $sectionName => $sectionTasks) {
                $sectionData = [
                    'section_name' => $sectionName,
                    'total_tasks' => $sectionTasks->whereIn('status', [2, 3])->count(),
                    'completed_tasks' => $sectionTasks->whereIn('status', [3])->count(),
                    'pending_tasks' => $sectionTasks->where('status', 2)->count(),
                    'tasks' => $sectionTasks->map(function ($task) {

                        if ($task->status == 2) {
                            if (isset($task->wf->checker_id)) {
                                if ($task->redos()->where('status', 1)->count() == 0 && $task->redos()->where('status', 0)->count() > 0) {
                                    $statusLabel = 'REASSIGNED';
                                } else if ($task->redos()->where('status', 0)->count() == 0 && $task->redos()->where('status', 1)->count() == 0) {
                                    $statusLabel = 'PENDING-VERIFICATION';
                                } else {
                                    $statusLabel = 'VERIFYING';
                                }
                            } else {
                                $statusLabel = 'COMPLETED';
                            }
                        } else if ($task->status == 3) {
                            if (isset($task->wf->checker_id)) {
                                $statusLabel = 'VERIFIED';
                            } else {
                                $statusLabel = 'COMPLETED';
                            }
                        } else {
                            $statusLabel = 'PENDING';
                        }

                        return [
                            'id' => $task->id,
                            'code' => $task->code,
                            'status' => $task->status,
                            'status_label' => $statusLabel,
                            'date' => $task->date,
                            'step_name' => $task->wf->step_name ?? '',
                            'percentage' => $task->percentage,
                            'extra_info' => $task->extra_info,
                            'assignment_title' => $task->wf->parent->title ?? ''
                        ];
                    })
                ];
                $result[] = $sectionData;
            }

            return response()->json([
                'status' => true,
                'data' => $result
            ]);

        } else {
            $tasks = ChecklistTask::with(['wf.parent'])
                ->where('type', 1)
                ->whereHas('wf.parent', function ($builder) use ($user) {
                    $builder->where('status', 1);
                })
                ->when($request->has('workflow_id'), function ($builder) {
                    return $builder->whereHas('wf', function ($innerBuilder) {
                        $innerBuilder->where('new_workflow_assignment_id', request('workflow_id'));
                    });
                })
                ->when($fromDate, function ($builder) use ($fromDate) {
                    return $builder->whereDate('date', '>=', $fromDate->format('Y-m-d'));
                })
                ->when($toDate, function ($builder) use ($toDate) {
                    return $builder->whereDate('date', '<=', $toDate->format('Y-m-d'));
                })
                ->where(function ($q) use ($user) {
                    $q->whereHas('wf', function ($q2) use ($user) {
                        $q2->where('user_id', $user->id);
                    });
                })
                ->when($request->has('department_id'), function ($builder) {
                    return $builder->whereHas('wf', function ($innerBuilder) {
                        $innerBuilder->where('department_id', request('department_id'));
                    });
                })
                ->get();

            $grouped = $tasks->groupBy(function ($task) {
                return $task->wf->section_name ?? 'Unknown Section';
            });

            $result = [];
            foreach ($grouped as $sectionName => $sectionTasks) {
                $sectionData = [
                    'section_name' => $sectionName,
                    'total_tasks' => $sectionTasks->count(),
                    'completed_tasks' => $sectionTasks->whereIn('status', [2, 3])->count(),
                    'pending_tasks' => $sectionTasks->where('status', 0)->count(),
                    'tasks' => $sectionTasks->map(function ($task) {

                        if ($task->status == 0) {
                            $statusLabel = 'PENDING';
                        } else if ($task->status == 1) {
                            $statusLabel = 'IN-PROGRESS';
                        } else if ($task->status == 2) {

                            if (isset($task->wf->checker_id)) {
                                if ($task->redos()->where('status', 1)->count() == 0 && $task->redos()->where('status', 0)->count() > 0) {
                                    $statusLabel = 'REASSIGNED';
                                } else if ($task->redos()->where('status', 0)->count() == 0 && $task->redos()->where('status', 1)->count() == 0) {
                                    $statusLabel = 'PENDING-VERIFICATION';
                                } else {
                                    $statusLabel = 'VERIFYING';
                                }
                            } else {
                                $statusLabel = 'COMPLETED';
                            }
                        } else {
                            if (isset($task->wf->checker_id)) {
                                $statusLabel = 'VERIFIED';
                            } else {
                                $statusLabel = 'COMPLETED';
                            }
                        }

                        return [
                            'id' => $task->id,
                            'code' => $task->code,
                            'status' => $task->status,
                            'status_label' => $statusLabel,
                            'date' => $task->date,
                            'step_name' => $task->wf->step_name ?? '',
                            'percentage' => $task->percentage,
                            'extra_info' => $task->extra_info,
                            'assignment_title' => $task->wf->parent->title ?? ''
                        ];
                    })
                ];
                $result[] = $sectionData;
            }

            return response()->json([
                'status' => true,
                'data' => $result
            ]);
        }
    }

    public function sectionProgress2(Request $request)
    {
        $user = auth()->user();
        $fromDate = $request->from_date ? Carbon::parse($request->from_date) : Carbon::now();
        $toDate = $request->to_date ? Carbon::parse($request->to_date) : Carbon::now();

        if ($request->has('for_checker') && $request->for_checker == 1) {

            $tasks = ChecklistTask::with(['wf.parent'])
                ->where('type', 1)
                ->whereHas('wf.parent', function ($builder) use ($user) {
                    $builder->where('status', 1);
                })
                ->when($request->has('workflow_id'), function ($builder) {
                    return $builder->whereHas('wf', function ($innerBuilder) {
                        $innerBuilder->where('new_workflow_assignment_id', request('workflow_id'));
                    });
                })
                ->when($fromDate, function ($builder) use ($fromDate) {
                    return $builder->whereDate('date', '>=', $fromDate->format('Y-m-d'));
                })
                ->when($toDate, function ($builder) use ($toDate) {
                    return $builder->whereDate('date', '<=', $toDate->format('Y-m-d'));
                })
                ->where(function ($q) use ($user) {
                    $q->whereHas('wf', function ($q2) use ($user) {
                        $q2->where('checker_id', $user->id);
                    });
                })
                ->when($request->has('department_id'), function ($builder) {
                    return $builder->whereHas('wf', function ($innerBuilder) {
                        $innerBuilder->where('department_id', request('department_id'));
                    });
                })
                ->get();

            $grouped = $tasks->groupBy(function ($task) {
                return $task->wf->section_name ?? 'Unknown Section';
            });

            $result = [];
            foreach ($grouped as $sectionName => $sectionTasks) {
                $sectionData = [
                    'section_name' => $sectionName,
                    'total_tasks' => $sectionTasks->whereIn('status', [2, 3])->count(),
                    'completed_tasks' => $sectionTasks->whereIn('status', [3])->count(),
                    'pending_tasks' => $sectionTasks->where('status', 2)->count(),
                    'tasks' => $sectionTasks->map(function ($task) {

                        if ($task->status == 2) {
                            if (isset($task->wf->checker_id)) {
                                if ($task->redos()->where('status', 1)->count() == 0 && $task->redos()->where('status', 0)->count() > 0) {
                                    $statusLabel = 'REASSIGNED';
                                } else if ($task->redos()->where('status', 0)->count() == 0 && $task->redos()->where('status', 1)->count() == 0) {
                                    $statusLabel = 'PENDING-VERIFICATION';
                                } else {
                                    $statusLabel = 'VERIFYING';
                                }
                            } else {
                                $statusLabel = 'COMPLETED';
                            }
                        } else if ($task->status == 3) {
                            if (isset($task->wf->checker_id)) {
                                $statusLabel = 'VERIFIED';
                            } else {
                                $statusLabel = 'COMPLETED';
                            }
                        } else {
                            $statusLabel = 'PENDING';
                        }

                        $versionedForm = Helper::getVersionForm($task->version_id);
                        $theFulfilledJson = [];
                        if (isset($versionedForm)) {
                            $theFulfilledJson = $versionedForm;
                            if (!empty($task->data)) {
                                foreach ($task->data as $row) {
                                    if (isset($theFulfilledJson[$row->page - 1]) && is_array($theFulfilledJson[$row->page - 1])) {
                                        foreach ($theFulfilledJson[$row->page - 1] as $thisRowKey => $thisRow) {
                                            if (property_exists($thisRow, 'name') && $thisRow->name == $row->name) {
                                                $theFulfilledJson[$row->page - 1][$thisRowKey]->value = $row->value;
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        $startAt = date('d-m-Y H:i', strtotime($task->date));
                        $copmletedBy = date('d-m-Y H:i', strtotime($task->completed_by));

                        $startability = $this->canStartTask($task);

                        return [
                            'id' => $task->id,
                            'code' => $task->code,
                            'status' => $task->status,
                            'status_label' => $statusLabel,
                            'date' => $task->date,
                            'step_name' => $task->wf->step_name ?? '',
                            'assignment_title' => $task->wf->parent->title ?? '',
                            'step_id' => $task->id,
                            'workflow_task_data' => $task->wf,
                            'checklist_id' => $task->wf->checklist_id,
                            'department_name' => isset($task->wf->department->name) ? $task->wf->department->name : '',
                            'department_id' => isset($task->wf->department->id) ? $task->wf->department->id : '',
                            'user' => $task->wf->user,
                            'start_date' => $startAt,
                            'trigger_type' => ($task->wf->trigger ?? 1) == 0 ? 'auto' : 'manual',
                            'completed_by' => $copmletedBy,
                            'checklist_title' => $task->wf->checklist->name ?? '',
                            'extra_info' => $task->extra_info,
                            'percentage' => $task->percentage,
                            'schema_encoded' => $theFulfilledJson,
                            'data' => isset($task->data) ? $task->data : null,
                            'is_point_checklist' => Helper::isPointChecklist($versionedForm),
                            'check_inout' => $task->submissionentries()->latest()->get()->toArray(),
                            'excel_export' => route('workflow-task-export-excel', $task->id),
                            'is_checker' => $task->wf->checker_id == auth()->user()->id,
                            'pdf_export' => route('workflow-task-export-compressed-pdf', $task->id),
                            'pdf_report_link' => asset("storage/task-pdf/task-compressed-{$task->id}.pdf"),
                            'is_geofencing_enabled' => isset($task->wf->checklist->is_geofencing_enabled) && $task->wf->checklist->is_geofencing_enabled == 1 ? true : false,
                            'geofencing_range' => env('GEOFENCE_RANGE', 300),
                            'can_start' => $startability['can_start'],
                            'cannot_start_reason' => $startability['can_start'] ? null : $startability['reason'],
                            'redo_action' => \App\Models\RedoAction::where('task_id', $task->id)->where('status', 0)->get()->toArray(),
                            'new_tickets' => \App\Models\NewTicket::with(['department', 'particular', 'issue', 'creator', 'store', 'owners.user', 'histories.author'])->where('task_id', $task->id)->latest()->get()->map(function ($ticketEl) {
                                return \App\Http\Controllers\API\v2\ApiController::formatTicketResponse($ticketEl);
                            }),
                        ];
                    })
                ];
                $result[] = $sectionData;
            }

            return response()->json([
                'status' => true,
                'data' => $result
            ]);

        } else {
            $tasks = ChecklistTask::with(['wf.parent'])
                ->where('type', 1)
                ->whereHas('wf.parent', function ($builder) use ($user) {
                    $builder->where('status', 1);
                })
                ->when($request->has('workflow_id'), function ($builder) {
                    return $builder->whereHas('wf', function ($innerBuilder) {
                        $innerBuilder->where('new_workflow_assignment_id', request('workflow_id'));
                    });
                })
                ->when($fromDate, function ($builder) use ($fromDate) {
                    return $builder->whereDate('date', '>=', $fromDate->format('Y-m-d'));
                })
                ->when($toDate, function ($builder) use ($toDate) {
                    return $builder->whereDate('date', '<=', $toDate->format('Y-m-d'));
                })
                ->where(function ($q) use ($user) {
                    $q->whereHas('wf', function ($q2) use ($user) {
                        $q2->where('user_id', $user->id);
                    });
                })
                ->when($request->has('department_id'), function ($builder) {
                    return $builder->whereHas('wf', function ($innerBuilder) {
                        $innerBuilder->where('department_id', request('department_id'));
                    });
                })
                ->get();

            $grouped = $tasks->groupBy(function ($task) {
                return $task->wf->section_name ?? 'Unknown Section';
            });

            $result = [];
            foreach ($grouped as $sectionName => $sectionTasks) {
                $sectionData = [
                    'section_name' => $sectionName,
                    'total_tasks' => $sectionTasks->count(),
                    'completed_tasks' => $sectionTasks->whereIn('status', [2, 3])->count(),
                    'pending_tasks' => $sectionTasks->where('status', 0)->count(),
                    'tasks' => $sectionTasks->map(function ($task) {

                        if ($task->status == 2) {
                            if (isset($task->wf->checker_id)) {
                                if ($task->redos()->where('status', 1)->count() == 0 && $task->redos()->where('status', 0)->count() > 0) {
                                    $statusLabel = 'REASSIGNED';
                                } else if ($task->redos()->where('status', 0)->count() == 0 && $task->redos()->where('status', 1)->count() == 0) {
                                    $statusLabel = 'PENDING-VERIFICATION';
                                } else {
                                    $statusLabel = 'VERIFYING';
                                }
                            } else {
                                $statusLabel = 'COMPLETED';
                            }
                        } else if ($task->status == 3) {
                            if (isset($task->wf->checker_id)) {
                                $statusLabel = 'VERIFIED';
                            } else {
                                $statusLabel = 'COMPLETED';
                            }
                        } else {
                            $statusLabel = 'PENDING';
                        }

                        $versionedForm = Helper::getVersionForm($task->version_id);
                        $theFulfilledJson = [];
                        if (isset($versionedForm)) {
                            $theFulfilledJson = $versionedForm;
                            if (!empty($task->data)) {
                                foreach ($task->data as $row) {
                                    if (isset($theFulfilledJson[$row->page - 1]) && is_array($theFulfilledJson[$row->page - 1])) {
                                        foreach ($theFulfilledJson[$row->page - 1] as $thisRowKey => $thisRow) {
                                            if (property_exists($thisRow, 'name') && $thisRow->name == $row->name) {
                                                $theFulfilledJson[$row->page - 1][$thisRowKey]->value = $row->value;
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        $startAt = date('d-m-Y H:i', strtotime($task->date));
                        $copmletedBy = date('d-m-Y H:i', strtotime($task->completed_by));

                        $startability = $this->canStartTask($task);

                        return [
                            'id' => $task->id,
                            'code' => $task->code,
                            'status' => $task->status,
                            'status_label' => $statusLabel,
                            'date' => $task->date,
                            'step_name' => $task->wf->step_name ?? '',
                            'assignment_title' => $task->wf->parent->title ?? '',
                            'step_id' => $task->id,
                            'checklist_id' => $task->wf->checklist_id,
                            'department_name' => isset($task->wf->department->name) ? $task->wf->department->name : '',
                            'department_id' => isset($task->wf->department->id) ? $task->wf->department->id : '',
                            'user' => $task->wf->user,
                            'start_date' => $startAt,
                            'workflow_task_data' => $task->wf,
                            'trigger_type' => ($task->wf->trigger ?? 1) == 0 ? 'auto' : 'manual',
                            'completed_by' => $copmletedBy,
                            'checklist_title' => $task->wf->checklist->name ?? '',
                            'extra_info' => $task->extra_info,
                            'percentage' => $task->percentage,
                            'schema_encoded' => $theFulfilledJson,
                            'data' => isset($task->data) ? $task->data : null,
                            'is_point_checklist' => Helper::isPointChecklist($versionedForm),
                            'check_inout' => $task->submissionentries()->latest()->get()->toArray(),
                            'excel_export' => route('workflow-task-export-excel', $task->id),
                            'is_checker' => $task->wf->checker_id == auth()->user()->id,
                            'pdf_export' => route('workflow-task-export-compressed-pdf', $task->id),
                            'pdf_report_link' => asset("storage/task-pdf/task-compressed-{$task->id}.pdf"),
                            'is_geofencing_enabled' => isset($task->wf->checklist->is_geofencing_enabled) && $task->wf->checklist->is_geofencing_enabled == 1 ? true : false,
                            'geofencing_range' => env('GEOFENCE_RANGE', 300),
                            'can_start' => $startability['can_start'],
                            'cannot_start_reason' => $startability['can_start'] ? null : $startability['reason'],
                            'redo_action' => RedoAction::where('task_id', $task->id)->where('status', 0)->get()->toArray(),
                            'new_tickets' => \App\Models\NewTicket::with(['department', 'particular', 'issue', 'creator', 'store', 'owners.user', 'histories.author'])->where('task_id', $task->id)->latest()->get()->map(function ($ticketEl) {
                                return \App\Http\Controllers\API\v2\ApiController::formatTicketResponse($ticketEl);
                            }),
                        ];
                    })
                ];
                $result[] = $sectionData;
            }

            return response()->json([
                'status' => true,
                'data' => $result
            ]);
        }
    }

    private function canStartTask(ChecklistTask $task)
    {
        if ($task->status == 3) {
            return [
                'can_start' => false,
                'reason' => 'Task has been completed and verified!'
            ];
        }

        $workflowItem = $task->wf;
        if (!$workflowItem) {
            return [
                'can_start' => false,
                'reason' => 'Workflow assignment item not found'
            ];
        }

        if ($workflowItem->is_entry_point) {
            return [
                'can_start' => true,
                'reason' => null
            ];
        }

        $dependency = $workflowItem->dependency ?? 'NO_DEPENDENCY';
        $dependencySteps = $workflowItem->dependency_steps ?? [];

        if ($dependency == 'NO_DEPENDENCY') {
            return [
                'can_start' => true,
                'reason' => null
            ];
        } else if ($dependency == 'ALL_COMPLETED') {
            $parentTasks = ChecklistTask::where('type', 1)
                ->where('workflow_checklist_id', $workflowItem->workflow_checklist_id)
                ->where('id', '<', $task->id)
                ->whereIn('status', [0, 1, 2])
                ->count();

            if ($parentTasks > 0) {
                return [
                    'can_start' => false,
                    'reason' => 'Previous task of this task is not completed yet!'
                ];
            } else {
                return [
                    'can_start' => true,
                    'reason' => null
                ];
            }
        } else if ($dependency == 'SELECTED_COMPLETED') {
            $parentWorkflowItems = NewWorkflowAssignmentItem::select('id')
                ->where('new_workflow_assignment_id', $workflowItem->new_workflow_assignment_id)
                ->whereIn('id', $dependencySteps)
                ->pluck('id')
                ->toArray();

            if (empty($parentWorkflowItems)) {
                return [
                    'can_start' => true,
                    'reason' => null
                ];
            } else {
                $parentWorkflowItems = ChecklistTask::with('wf')->where('type', 1)
                    ->whereIn('workflow_checklist_id', $parentWorkflowItems)
                    ->whereIn('status', [0, 1])
                    ->get();

                if (!$parentWorkflowItems->isEmpty()) {
                    $taskName = '';

                    foreach ($parentWorkflowItems as $parentWorkflowItemsRow) {
                        if (isset($parentWorkflowItemsRow->wf->id)) {
                            $taskName .= ($parentWorkflowItemsRow->wf->step_name . ', ');
                        }
                    }

                    if (!empty($taskName)) {
                        $taskName = rtrim($taskName, ', ');

                        return [
                            'can_start' => false,
                            'reason' => "{$taskName} tasks needs to completed first!"
                        ];
                    } else {
                        return [
                            'can_start' => true,
                            'reason' => null
                        ];
                    }
                } else {
                    return [
                        'can_start' => true,
                        'reason' => null
                    ];
                }
            }
        } else {
            return [
                'can_start' => true,
                'reason' => null
            ];
        }
    }

    public function tasks(Request $request)
    {
        $page = $request->page > -1 ? $request->page : 0;
        $perPage = $request->record_per_page > 0 ? $request->record_per_page : 5;
        $skip = $page * $perPage;

        $filterCompending = $request->status;
        $filterFrom = $request->from_date ? date('Y-m-d', strtotime($request->from_date)) : date('Y-m-d');
        $filterTo = $request->to_date ? date('Y-m-d', strtotime($request->to_date)) : date('Y-m-d');

        $tType = isset($request->for_checker) && $request->for_checker ? 2 : 1;

        $tasks = ChecklistTask::with(['wf.user', 'wf.checker', 'redos'])
            ->whereHas('wf.parent', function ($builder) {
                $builder->where('status', 1);
            })
            ->where('type', 1)
            ->when($request->has('workflow_id'), function ($builder) {
                return $builder->whereHas('wf', function ($innerBuilder) {
                    $innerBuilder->where('new_workflow_assignment_id', request('workflow_id'));
                });
            })
            ->when($request->has('task_id') && !empty($request->task_id), function ($innerBuilder) {
                $innerBuilder->where('id', request('task_id'));
            })
            ->when(auth()->check(), function ($inBldr) use ($tType) {
                $inBldr->where(function ($innerBuilder) use ($tType) {
                    $innerBuilder->whereHas('wf', function ($query) use ($tType) {
                        $query->where($tType == 2 ? 'checker_id' : 'user_id', auth()->user()->id);
                    });
                });
            })
            ->when($filterCompending == 1, function ($builder) {
                return $builder->where('status', 0);
            })
            ->when($filterCompending == 2, function ($builder) {
                return $builder->where('status', 1);
            })
            ->when($filterCompending == 3, function ($builder) {
                return $builder->where('status', 2);
            })
            ->when($filterCompending == 4, function ($builder) {
                return $builder->where('status', 3);
            })
            ->when($tType == 1 && in_array(request('filter_status'), ['PENDING', 'IN_PROGRESS', 'PENDING_VERIFICATION', 'VERIFIED', 'COMPLETED']), function ($builder) {
                if (request('filter_status') == 'PENDING') {
                    $builder->where('status', 0);
                } else if (request('filter_status') == 'IN_PROGRESS') {
                    $builder->where('status', 1);
                } else if (request('filter_status') == 'PENDING_VERIFICATION') {
                    $builder->where('status', 2);
                } else if (request('filter_status') == 'VERIFIED') {
                    $builder->where('status', 3)
                        ->whereHas('wf', function ($query) {
                            $query->where('checker_id', '>', 0);
                        });
                } else if (request('filter_status') == 'COMPLETED') {
                    $builder->where('status', 3)
                        ->whereHas('wf', function ($query) {
                            $query->whereNull('checker_id');
                        });
                }
            })
            ->when($tType == 2 && in_array(request('filter_status'), ['PENDING_VERIFICATION', 'REASSIGNED', 'VERIFYING', 'VERIFIED']), function ($builder) {
                if (request('filter_status') == 'PENDING_VERIFICATION') {
                    $builder->where('status', 2)
                        ->whereDoesntHave('redos', function ($query) {
                            $query->where('status', [0, 1]);
                        });
                } else if (request('filter_status') == 'REASSIGNED') {
                    $builder->where('status', 2)
                        ->whereDoesntHave('redos', function ($query) {
                            $query->where('status', 1);
                        })
                        ->whereHas('redos', function ($query) {
                            $query->where('status', 0);
                        });
                } else if (request('filter_status') == 'VERIFYING') {
                    $builder->where('status', 2)
                        ->whereHas('redos', function ($query) {
                            $query->where('status', 1);
                        });
                } else if (request('filter_status') == 'VERIFIED') {
                    $builder->where('status', 3);
                }
            })
            ->when($tType == 2 && !in_array(request('filter_status'), ['PENDING_VERIFICATION', 'REASSIGNED', 'VERIFYING', 'VERIFIED']), function ($builder) {
                $builder->where('status', 2);
            })
            ->when(is_numeric($request->checklist_template_id) && $request->checklist_template_id > 0, function ($builder) {
                $builder->whereHas('wf', function ($query) {
                    $query->where('checklist_id', request('checklist_template_id'));
                });
            })
            ->when($filterFrom, function ($builder) use ($filterFrom) {
                return $builder->whereDate('date', '>=', $filterFrom);
            })
            ->when($filterTo, function ($builder) use ($filterTo) {
                return $builder->whereDate('date', '<=', $filterTo);
            })
            ->when(is_numeric($request->checklist_template_id) && $request->checklist_template_id > 0, function ($builder) {
                $builder->whereHas('wf', function ($query) {
                    $query->where('checklist_id', request('checklist_template_id'));
                });
            })
            ->orderBy('id', 'DESC');

        $total = $tasks->count();
        $tasks = $tasks->skip($skip)->take($perPage)->get()->map(function ($el) use ($tType) {
            if ($el->status == 0) {
                $statusLabel = 'PENDING';
            } else if ($el->status == 1) {
                $statusLabel = 'IN-PROGRESS';
            } else if ($el->status == 2) {

                if ($tType == 1 && request('filter_status') == 'PENDING_VERIFICATION') {
                    $statusLabel = 'PENDING-VERIFICATION';
                } else {
                    if (isset($el->wf->checker_id)) {
                        if ($el->redos()->where('status', 1)->count() == 0 && $el->redos()->where('status', 0)->count() > 0) {
                            $statusLabel = 'REASSIGNED';
                        } else if ($el->redos()->where('status', 0)->count() == 0 && $el->redos()->where('status', 1)->count() == 0) {
                            $statusLabel = 'PENDING-VERIFICATION';
                        } else {
                            $statusLabel = 'VERIFYING';
                        }
                    } else {
                        $statusLabel = 'COMPLETED';
                    }
                }
            } else {
                if (isset($el->wf->checker_id)) {
                    $statusLabel = 'VERIFIED';
                } else {
                    $statusLabel = 'COMPLETED';
                }
            }

            $versionedForm = Helper::getVersionForm($el->version_id);
            $theFulfilledJson = [];
            if (isset($versionedForm)) {
                $theFulfilledJson = $versionedForm;
                if (!empty($el->data)) {
                    foreach ($el->data as $row) {
                        if (isset($theFulfilledJson[$row->page - 1]) && is_array($theFulfilledJson[$row->page - 1])) {
                            foreach ($theFulfilledJson[$row->page - 1] as $thisRowKey => $thisRow) {
                                if (property_exists($thisRow, 'name') && $thisRow->name == $row->name) {
                                    $theFulfilledJson[$row->page - 1][$thisRowKey]->value = $row->value;
                                }
                            }
                        }
                    }
                }
            }

            $startAt = date('d-m-Y H:i', strtotime($el->date));
            $copmletedBy = date('d-m-Y H:i', strtotime($el->completed_by));

            $startability = $this->canStartTask($el);

            return [
                'step_id' => $el->id,
                'checklist_id' => $el->wf->checklist_id,
                'department_name' => isset($el->wf->department->name) ? $el->wf->department->name : '',
                'department_id' => isset($el->wf->department->id) ? $el->wf->department->id : '',
                'user' => $el->wf->user,
                'start_date' => $startAt,
                'workflow_task_data' => $el->wf,
                'trigger_type' => ($el->wf->trigger ?? 1) == 0 ? 'auto' : 'manual',
                'completed_by' => $copmletedBy,
                'checklist_title' => $el->wf->checklist->name ?? '',
                'code' => $el->code,
                'extra_info' => $el->extra_info,
                'percentage' => $el->percentage,
                'schema_encoded' => $theFulfilledJson,
                'data' => isset($el->data) ? $el->data : null,
                'status' => $el->status,
                'is_point_checklist' => Helper::isPointChecklist($versionedForm),
                'status_label' => $statusLabel,
                'check_inout' => $el->submissionentries()->latest()->get()->toArray(),
                'excel_export' => route('workflow-task-export-excel', $el->id),
                'is_checker' => $el->wf->checker_id == auth()->user()->id,
                'pdf_export' => route('workflow-task-export-compressed-pdf', $el->id),
                'pdf_report_link' => asset("storage/task-pdf/task-compressed-{$el->id}.pdf"),
                'is_geofencing_enabled' => isset($el->wf->checklist->is_geofencing_enabled) && $el->wf->checklist->is_geofencing_enabled == 1 ? true : false,
                'geofencing_range' => env('GEOFENCE_RANGE', 300),
                'can_start' => $startability['can_start'],
                'cannot_start_reason' => $startability['can_start'] ? null : $startability['reason'],
                'redo_action' => RedoAction::where('task_id', $el->id)->where('status', 0)->get()->toArray(),
                'new_tickets' => \App\Models\NewTicket::with(['department', 'particular', 'issue', 'creator', 'store', 'owners.user', 'histories.author'])->where('task_id', $el->id)->latest()->get()->map(function ($ticketEl) {
                    return \App\Http\Controllers\API\v2\ApiController::formatTicketResponse($ticketEl);
                }),
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $tasks,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage
        ]);
    }

    public function submitTask(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'task_id' => 'required|exists:checklist_tasks,id',
            'status' => 'required|in:1,2',
            'type' => 'required|in:1,2', //1 = Full JSON | 2 = Partial JSON
            'data' => 'required'
        ]);

        if ($validator->fails()) {
            $errorString = implode(",", $validator->messages()->all());
            return response()->json(['error' => $errorString], 401);
        }

        TaskDeviceInformation::create([
            'eloquent' => ChecklistTask::class,
            'eloquent_id' => $request->task_id,
            'user_id' => auth()->check() ? auth()->user()->id : null,
            'device_model' => $request->device_model,
            'network_speed' => $request->network_speed,
            'device_version' => $request->device_version
        ]);

        $task = ChecklistTask::findOrFail($request->task_id);
        $forImageChecklistId = $task->wf->checklist_id ?? 'NA';
        $forImageTaskId = $task->id ?? 'NA';

        if ($task->status == Helper::$status['in-verification']) {
            return response()->json(['error' => 'This Checklist already submitted.']);
        }

        if ($request->status == 1 && $task->status == 0) {
            $startability = $this->canStartTask($task);
            if (!$startability['can_start']) {
                return response()->json([
                    'error' => 'Cannot start this task. ' . $startability['reason']
                ], 403);
            }
        }

        if (!file_exists(storage_path('app/public/workflow-task-uploads'))) {
            mkdir(storage_path('app/public/workflow-task-uploads'), 0777, true);
        }

        if (is_string($request->data) && $request->data != "NONE") {
            $data = json_decode($request->data, true);
        } else {
            $data = $request->data;
        }

        $filesToBeRemoved = [];
        $currentJson = $task->data;

        if ($request->data != "NONE") {
            if ($request->type == 2) {
                if (empty($currentJson)) {
                    $currentJson = [];
                }

                foreach ($data as $row) {
                    if (self::hasValueByName($currentJson, $row['name'])) {
                        foreach ($currentJson as &$item) {
                            if (isset($item->name) && $item->name === $row['name']) {

                                if (property_exists($item, 'isFile') &&  $item->isFile) {
                                    if (is_array($item->value)) {
                                        foreach ($item->value as $fileVal) {
                                            if (!empty($fileVal) && !Str::contains($fileVal, '|to_be_generated.png') && is_file(storage_path("app/public/workflow-task-uploads/{$fileVal}"))) {
                                                $fileDoesExists = false;
                                                if (is_array($row['value'])) {
                                                    foreach ($row['value'] as $rw) {
                                                        if ($rw == $fileVal) {
                                                            $fileDoesExists = true;
                                                            continue;
                                                        }
                                                    }
                                                } else if (is_string($row['value'])) {
                                                    if ($row['value'] == $item->value) {
                                                        $fileDoesExists = true;
                                                    }
                                                }

                                                if ($fileDoesExists === false) {
                                                    $filesToBeRemoved[] = storage_path("app/public/workflow-task-uploads/{$fileVal}");
                                                }
                                            }
                                        }
                                    } else if (is_string($item->value)) {
                                        if (!empty($item->value) && !Str::contains($item->value, '|to_be_generated.png') && is_file(storage_path("app/public/workflow-task-uploads/{$item->value}"))) {
                                            $fileDoesExists = false;

                                            if (is_array($row['value'])) {
                                                foreach ($row['value'] as $rw) {
                                                    if ($rw == $item->value) {
                                                        $fileDoesExists = true;
                                                        continue;
                                                    }
                                                }
                                            } else if (is_string($row['value'])) {
                                                if ($row['value'] == $item->value) {
                                                    $fileDoesExists = true;
                                                }
                                            }

                                            if ($fileDoesExists === false) {
                                                $filesToBeRemoved[] = storage_path("app/public/workflow-task-uploads/{$item->value}");
                                            }
                                        }
                                    }
                                }

                                if ($row['isFile'] && is_array($row['value'])) {
                                    $finalImgArrObj = [];
                                    foreach ($row['value'] as $tfov) {
                                        if (!Str::contains($tfov, '|to_be_generated.png')) {
                                            $finalImgArrObj[] = $tfov;
                                        }
                                    }

                                    $row['value'] = $finalImgArrObj;
                                } else if ($row['isFile'] && is_string($row['value'])) {
                                    if (!Str::contains($row['value'], '|to_be_generated.png')) {
                                        $finalImgArrObj = [$row['value']];
                                    }

                                    $row['value'] = $finalImgArrObj;
                                }

                                $item->value = $row['value'];

                                if (property_exists($item, 'value_label') && isset($row['value_label'])) {
                                    $item->value_label = $row['value_label'];
                                }

                                continue;
                            }
                        }
                    } else {
                        if (!is_array($currentJson) && ($currentJson == '{}' || empty($currentJson))) {
                            $currentJson = [];
                        } else if (is_object($currentJson)) {
                            $currentJson = (array) $currentJson;
                        }

                        if (isset($row) && array_key_exists('isFile', $row)) {
                            if (is_array($row['value'])) {
                                $finalImgArr = [];
                                foreach ($row['value'] as $thisFileRow) {
                                    if (!Str::contains($thisFileRow, '|to_be_generated.png')) {
                                        $finalImgArr[] = $thisFileRow;
                                    }
                                }

                                $row['value'] = $finalImgArr;
                            } else if (is_string($row['value'])) {
                                if (Str::contains($row['value'], '|to_be_generated.png')) {
                                    $row['value'] = [];
                                }
                            }
                        }

                        array_push($currentJson, $row);
                    }
                }

                foreach ($currentJson as &$item) {
                    if (is_array($item)) {
                        $item = (object) $item;
                    }
                }
                unset($item);

                usort($currentJson, function ($a, $b) {
                    $pageComparison = (int)$a->page <=> (int)$b->page;

                    if ($pageComparison === 0) {
                        $aIndex = isset($a->index) ? (int)$a->index : PHP_INT_MAX;
                        $bIndex = isset($b->index) ? (int)$b->index : PHP_INT_MAX;

                        return $aIndex <=> $bIndex;
                    }

                    return $pageComparison;
                });

                $task->data = $currentJson;
            } else {
                foreach ($data as &$dt) {
                    if (array_key_exists('isFile', $dt) && $dt['isFile'] == true) {
                        if (is_array($dt['value'])) {
                            foreach ($dt['value'] as &$tempRow) {
                                if (strpos($tempRow, 'SIGN-20') !== false) {
                                    continue;
                                }
                                $tempRow = Helper::downloadBase64FileWebp($tempRow, ('SIGN-' . date('YmdHis') . uniqid() . '-' . $forImageChecklistId . '-' . $forImageTaskId), storage_path('app/public/workflow-task-uploads'));
                            }
                        } else {
                            if (strpos($dt['value'], 'SIGN-20') !== false) {
                                continue;
                            }
                            $dt['value'] = Helper::downloadBase64FileWebp($dt['value'], ('SIGN-' . date('YmdHis') . uniqid() . '-' . $forImageChecklistId . '-' . $forImageTaskId), storage_path('app/public/workflow-task-uploads'));
                        }
                    }
                }

                $task->data = $data;
            }
        }

        if (isset($task->wf->checker_id)) {
            $task->status = $request->status;
        } else {
            if ($request->status == Helper::$status['in-verification']) {
                $task->status = Helper::$status['completed'];
                $task->completion_date = now();
            } else {
                $task->status = $request->status;
            }
        }

        if (empty($task->started_at)) {
            if (!empty($request->starting_date)) {
                $task->started_at = date('Y-m-d H:i:s', strtotime($request->starting_date));
            } else {
                $task->started_at = now();
            }
        }

        $task->save();

        if ($request->status == Helper::$status['in-verification']) {
            $task = ChecklistTask::find($request->task_id);
            $task->completion_date = now();

            $task->save();
        }

        if ($request->status == 2) {
            //generating from notification center
            // \App\Jobs\GenerateOptimizedTaskPdf::dispatch($task->id);

            // Send completion notifications (email + push) to maker, checker, and dependent task makers
            \App\Jobs\SendWorkflowCompletionNotification::dispatch($task->id);
        }

        if (!empty($filesToBeRemoved)) {
            foreach ($filesToBeRemoved as $filesToBeRemovedFile) {
                if (is_file($filesToBeRemovedFile)) {
                    //keep for logs
                    // unlink($filesToBeRemovedFile);
                }
            }
        }

        return response()->json(['success' => 'Task submitted successfully.', 'data' => $data]);
    }

    public static function hasValueByName($items, $targetName)
    {
        foreach ($items as $item) {
            if (isset($item->name) && $item->name === $targetName) {
                return !empty($item->name);
            }
        }
        return false;
    }

    public function submitOnlyImagesForTask(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'task_id' => 'required|exists:checklist_tasks,id',
            'data' => 'required'
        ]);

        if ($validator->fails()) {
            $errorString = implode(",", $validator->messages()->all());
            return response()->json(['error' => $errorString], 401);
        }

        $task = ChecklistTask::find($request->task_id);
        $existingJson = $task->data;
        $originals = $thumbnails = [];
        $mergerArray = [];

        $forImageChecklistId = $task->wf->checklist_id ?? 'NA';
        $forImageTaskId = $task->id ?? 'NA';

        try {

            if (is_string($request->data)) {
                $data = json_decode($request->data, true);
            } else {
                $data = $request->data;
            }

            if (!empty($data)) {

                $allOfTheFields = [];
                foreach ($data as $dataKey => $dataRow) {
                    $allOfTheFields[$dataRow['field_id']] = $dataRow;
                }

                foreach ($data as $dataKey => $dataRow) {
                    if (!empty($existingJson)) {
                        foreach ($existingJson as &$row) {
                            if (property_exists($row, 'name') && $row->name == $dataRow['field_id']) {
                                if (isset($allOfTheFields[$dataRow['field_id']])) {
                                    unset($allOfTheFields[$dataRow['field_id']]);
                                }

                                $tmpArr = [];
                                $iterationForLatLong = -1;
                                foreach ($dataRow['values'] as $dt) {
                                    $iterationForLatLong++;
                                    $tempName = ('SIGN-' . date('YmdHis') . uniqid() . '-' . $forImageChecklistId . '-' . $forImageTaskId);
                                    $image = Helper::downloadBase64FileWebp($dt, $tempName, storage_path('app/public/workflow-task-uploads'));

                                    if (is_file(storage_path("app/public/workflow-task-uploads/{$image}"))) {

                                        $loc = isset($dataRow['location'][$iterationForLatLong]) ? $dataRow['location'][$iterationForLatLong] : '';
                                        $loc = explode(',', is_string($loc) ? $loc : '');

                                        $latitude = isset($loc[0]) ? $loc[0] : '';
                                        $longitude = isset($loc[1]) ? $loc[1] : '';

                                        \App\Jobs\AddMetaDataToImage::dispatch([
                                            'task_id' => $request->task_id,
                                            'timestamp' => isset($dataRow['timestamp'][$iterationForLatLong]) ? $dataRow['timestamp'][$iterationForLatLong] : null,
                                            'latitude' => $latitude,
                                            'longitude' => $longitude,
                                            'field_name' => $dataRow['field_id'] ?? '',
                                            'path' => storage_path("app/public/workflow-task-uploads/{$image}")
                                        ]);

                                        $imagePngWebpThumb = str_replace(".webp", ".png", $image);
                                        $img2 = Helper::createImageThumbnail(storage_path("app/public/workflow-task-uploads/{$image}"), storage_path("app/public/workflow-task-uploads-thumbnails/{$imagePngWebpThumb}"), 200, 200);
                                        if ($img2 && is_file(storage_path("app/public/workflow-task-uploads-thumbnails/{$imagePngWebpThumb}"))) {

                                            if (isset($thumbnails[$row->name])) {
                                                $thumbnails[$row->name]['values'][] = $image;
                                            } else {
                                                $thumbnails[$row->name]['field_name'] = $row->name;
                                                $thumbnails[$row->name]['values'][] = $image;
                                            }
                                        } else {
                                            return response()->json(['error' => "Error occured while generating thumbnail"]);
                                        }
                                    } else {
                                        return response()->json(['error' => "Error occured while generating image"]);
                                    }

                                    $tmpArr[] = $image;
                                }

                                if (is_string($row->value) && !empty($row->value)) {
                                    array_push($tmpArr, $row->value);
                                } else if (is_array($row->value) && !empty($row->value)) {
                                    $tmpArr = array_merge($tmpArr, $row->value);
                                }

                                $row->value = $tmpArr;

                                if (isset($dataRow['timestamp'])) {
                                    $row->timestamp = $dataRow['timestamp'];
                                }

                                if (isset($dataRow['location'])) {
                                    $row->location = $dataRow['location'];
                                }

                                $row->value = $tmpArr;
                            }
                        }
                    }
                }

                $allOfTheFields = array_filter($allOfTheFields);
                if (!empty($allOfTheFields)) {

                    foreach ($allOfTheFields as $line) {

                        $tmpArr = [];
                        $iterationForLatLong = -1;
                        foreach ($line['values'] as $dt) {
                            $iterationForLatLong++;
                            $tempName = ('SIGN-' . date('YmdHis') . uniqid() . '-' . $forImageChecklistId . '-' . $forImageTaskId);
                            $image = Helper::downloadBase64FileWebp($dt, $tempName, storage_path('app/public/workflow-task-uploads'));

                            if (is_file(storage_path("app/public/workflow-task-uploads/{$image}"))) {

                                $loc = isset($line['location'][$iterationForLatLong]) ? $line['location'][$iterationForLatLong] : '';
                                $loc = explode(',', is_string($loc) ? $loc : '');

                                $latitude = isset($loc[0]) ? $loc[0] : '';
                                $longitude = isset($loc[1]) ? $loc[1] : '';

                                \App\Jobs\AddMetaDataToImage::dispatch([
                                    'task_id' => $request->task_id,
                                    'timestamp' => isset($line['timestamp'][$iterationForLatLong]) ? $line['timestamp'][$iterationForLatLong] : null,
                                    'latitude' => $latitude,
                                    'longitude' => $longitude,
                                    'field_name' => $line['field_id'] ?? '',
                                    'path' => storage_path("app/public/workflow-task-uploads/{$image}")
                                ]);

                                $imagePngWebpThumb = str_replace(".webp", ".png", $image);
                                $img2 = Helper::createImageThumbnail(storage_path("app/public/workflow-task-uploads/{$image}"), storage_path("app/public/workflow-task-uploads-thumbnails/{$imagePngWebpThumb}"), 200, 200);
                                if ($img2 && is_file(storage_path("app/public/workflow-task-uploads-thumbnails/{$imagePngWebpThumb}"))) {
                                    if (isset($thumbnails[$line['field_id']])) {
                                        $thumbnails[$line['field_id']]['values'][] = $image;
                                    } else {
                                        $thumbnails[$line['field_id']]['field_name'] = $line['field_id'];
                                        $thumbnails[$line['field_id']]['values'][] = $image;
                                    }
                                } else {
                                    return response()->json(['error' => "Error occured while generating thumbnail"]);
                                }
                            } else {
                                return response()->json(['error' => "Error occured while generating image"]);
                            }

                            $tmpArr[] = $image;
                        }

                        $mergerArray[] = (object)[
                            "className" => $line['className'],
                            "page" => $line['page'],
                            "index" => $line['index'],
                            "label" => $line['label'],
                            "timestamp" => isset($line['timestamp']) ? $line['timestamp'] : null,
                            "location" => isset($line['location']) ? $line['location'] : null,
                            "name" => $line['field_id'],
                            "value" => $tmpArr,
                            "isFile" => true
                        ];
                    }
                }

                if (empty($existingJson) || $existingJson == '{}') {
                    $task->data = $mergerArray;
                } else {
                    if (!is_array($existingJson)) {
                        $existingJson = (array) $existingJson;
                    }

                    $tmpAr = array_merge($mergerArray, $existingJson);
                    $task->data = array_filter($tmpAr);
                }

                $task->status = $task->status == 0 ? 1 : $task->status;
                $task->save();

                $tempJsonForReindexing = $task->data;

                usort($tempJsonForReindexing, function ($a, $b) {
                    $pageComparison = (int)$a->page <=> (int)$b->page;

                    if ($pageComparison === 0) {
                        $aIndex = isset($a->index) ? (int)$a->index : PHP_INT_MAX;
                        $bIndex = isset($b->index) ? (int)$b->index : PHP_INT_MAX;

                        return $aIndex <=> $bIndex;
                    }

                    return $pageComparison;
                });

                $task->data = $tempJsonForReindexing;
                $task->save();

                return response()->json(['success' => "Image uploaded successfully", "thumbnails" => array_values($thumbnails)]);
            } else {
                return response()->json(['error' => "No JSON Found"]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'line' => $e->getLine()]);
        }
    }

    public function workflows(Request $request)
    {
        $user = auth()->user();
        $fromDate = $request->from_date ? Carbon::parse($request->from_date) : Carbon::now();
        $toDate = $request->to_date ? Carbon::parse($request->to_date) : Carbon::now();
        $tType = isset($request->for_checker) && $request->for_checker ? 1 : 0;

        $workflows = NewWorkflowAssignment::with(['children.task'])
            ->where('status', 1)
            ->where(function ($q) use ($user, $tType) {
                $q->whereHas('children', function ($q2) use ($user, $tType) {
                    if ($tType) {
                        $q2->where('checker_id', $user->id);
                    } else {
                        $q2->where('user_id', $user->id);
                    }
                });
            })
            ->get()
            ->map(function ($el) use ($user, $tType) {
                $percentage = $divider = 0;

                if (!empty($el->children) && is_iterable($el->children)) {
                    foreach ($el->children as $step) {
                        if (isset($step->task->id)) {
                            $percentage += $step->task->percentage;
                            $divider++;
                        }
                    }
                }

                if ($divider > 0) {
                    $percentage = $percentage / $divider;
                }

                return [
                    'workflow_id' => $el->id,
                    'title' => $el->title,
                    'description' => $el->description,
                    'start_from' => $el->start_from,
                    'departments' => $el->sections,
                    'checklist_count' => $el->children()->where($tType ? 'checker_id' : 'user_id', $user->id)->whereHas('task', function ($innerBuilder) use ($tType) {
                        $innerBuilder->whereIn('status', $tType ? [2, 3] : [0, 1, 2, 3]);
                    })->count(),
                    'completed_count' => $tType ? ChecklistTask::workflow()->whereHas('wf', function ($builder) use ($el, $user) {
                        $builder->where('new_workflow_assignment_id', $el->id)
                            ->where('checker_id', $user->id);
                    })->whereIn('status', [3])->count() : ChecklistTask::workflow()->whereHas('wf', function ($builder) use ($el, $user) {
                        $builder->where('new_workflow_assignment_id', $el->id)
                            ->where('user_id', $user->id);
                    })->whereIn('status', [2, 3])->count(),
                    'pending_count' => $tType ? ChecklistTask::workflow()->whereHas('wf', function ($builder) use ($el, $user) {
                        $builder->where('new_workflow_assignment_id', $el->id)
                            ->where('user_id', $user->id);
                    })->whereIn('status', [2])->count() : ChecklistTask::workflow()->whereHas('wf', function ($builder) use ($el, $user) {
                        $builder->where('new_workflow_assignment_id', $el->id)
                            ->where('user_id', $user->id);
                    })->whereIn('status', [0])->count(),
                    'inprogress_count' => $tType ? 0 : ChecklistTask::workflow()->whereHas('wf', function ($builder) use ($el, $user) {
                        $builder->where('new_workflow_assignment_id', $el->id)
                            ->where('user_id', $user->id);
                    })->whereIn('status', [1])->count()
                ];
            });

        return response()->json([
            'status' => true,
            'data' => $workflows
        ]);
    }

    public function verifyTask(Request $request) {
        $validator = Validator::make($request->all(), [
            'task_id' => 'required|exists:checklist_tasks,id'
        ]);

        if ($validator->fails()) {
            $errorString = implode(",", $validator->messages()->all());
            return response()->json(['error' => $errorString], 401);
        }

        TaskDeviceInformation::create([
            'eloquent' => ChecklistTask::class,
            'eloquent_id' => $request->task_id,
            'user_id' => auth()->check() ? auth()->user()->id : null,
            'device_model' => $request->device_model,
            'network_speed' => $request->network_speed,
            'device_version' => $request->device_version
        ]);

        $task = ChecklistTask::find($request->task_id);
        if (isset($task->id)) {
            $task->status = 3;
            $task->verified_at = now();
            $task->save();

            return response()->json(['success' => 'Task verified successfully.']);
        }

        return response()->json(['error' => 'Task not found.']);
    }

    public function approveDecline(Request $request) {
        $validator = Validator::make($request->all(), [
            'task_id' => 'required|exists:checklist_tasks,id',
            'class' => 'required',
            'page' => 'required',
            'action' => 'required'
        ]);

        if ($validator->fails()) { 
            $errorString = implode(",",$validator->messages()->all());
            return response()->json(['error'=>$errorString], 401);
        }

        $task = ChecklistTask::find($request->task_id);

        DB::beginTransaction();

        try {
            $json = $task->data;

            foreach ($json as &$item) {
                if (isset($item->className) && $item->className === $request->class) {
                    if ($request->action == 'approve') {
                        $item->approved = 'yes';
                    } else if ($request->action == 'decline') {
                        $item->approved = 'no';
                    }
                }
            }

            if ($request->action == 'decline') {
                $redoActionExists = RedoAction::where('task_id', $request->task_id)
                ->where('field_id', $request->class);
    
                if ($redoActionExists->exists()) {
                    $redoActionExists->update([
                        'title' => $request->title,
                        'remarks' => $request->remarks,
                        'page' => $request->page,
                        'status' => 0,
                        'start_at' => date('Y-m-d H:i:s', strtotime($request->start_at)),
                        'completed_by' => date('Y-m-d H:i:s', strtotime($request->completed_by)),
                        'do_not_allow_late_submission' => $request->do_not_allow_late_submission
                    ]);
                } else {
                    RedoAction::create([
                        'task_id' => $request->task_id,
                        'field_id' => $request->class,
                        'page' => $request->page,                        
                        'title' => $request->title,
                        'remarks' => $request->remarks,
                        'start_at' => date('Y-m-d H:i:s', strtotime($request->start_at)),
                        'completed_by' => date('Y-m-d H:i:s', strtotime($request->completed_by)),
                        'do_not_allow_late_submission' => $request->do_not_allow_late_submission
                    ]);
                }
            } else if ($request->action == 'approve') {
                $redoActionExists = RedoAction::where('task_id', $request->task_id)
                ->where('field_id', $request->class);
    
                if ($redoActionExists->exists()) {
                    $redoActionExists->update([
                        'title' => $request->title,
                        'remarks' => $request->remarks,
                        'page' => $request->page,
                        'status' => 1,
                        'start_at' => date('Y-m-d H:i:s', strtotime($request->start_at)),
                        'completed_by' => date('Y-m-d H:i:s', strtotime($request->completed_by)),
                        'do_not_allow_late_submission' => $request->do_not_allow_late_submission
                    ]);
                } else {
                    RedoAction::create([
                        'task_id' => $request->task_id,
                        'field_id' => $request->class,
                        'page' => $request->page,                        
                        'title' => $request->title,
                        'remarks' => $request->remarks,
                        'status' => 1,
                        'start_at' => date('Y-m-d H:i:s', strtotime($request->start_at)),
                        'completed_by' => date('Y-m-d H:i:s', strtotime($request->completed_by)),
                        'do_not_allow_late_submission' => $request->do_not_allow_late_submission
                    ]);
                }
            }

            $task->data = $json;
            $task->save();

            //generating from notification center
            // \App\Jobs\GenerateOptimizedTaskPdf::dispatch($task->id);

            DB::commit();
            return response()->json(['success' => 'Updated successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('API CHECKER VERIFICATION: ' . $e->getMessage() . ' ON LINE ' . $e->getLine());
            return response()->json(['error' => 'Something went wrong']);
        }
    }

    public function redoActionTasks(Request $request) {
        $allTasksId = [];
        $filterFrom = date('Y-m-d H:i:s', strtotime($request->from));
        $filterTo = date('Y-m-d H:i:s', strtotime($request->to));

        $redoActions = RedoAction::with(['task.wf'])
        ->whereHas('task', function ($builder) {
            $builder->workflow();
        })
        ->when(is_numeric($request->workflow_id) && $request->workflow_id > 0, function ($builder) {
            $builder->whereHas('task.wf.parent', function ($query) {
                $query->where('id', request('workflow_id'));
            });
        })
        ->when(is_numeric($request->section_id) && $request->section_id > 0, function ($builder) {
            $builder->whereHas('task.wf.parent', function ($query) {
                $query->where('section_id', request('section_id'));
            });
        })
        ->where('status', 0);

        $allTasksId = $redoActions->pluck('task_id')->toArray();

        $tasks = ChecklistTask::with(['wf.parent', 'wf.user' => function ($builder) {
            return $builder->withTrashed();
        }])
        ->whereHas('redos')
        ->when(!empty($allTasksId), function ($builder) use ($allTasksId) {
            $builder->whereIn('id', $allTasksId);
        })
        ->when(empty($allTasksId), function ($builder) {
            $builder->where('id', 0);
        })
        ->workflow();

        if (!empty($request->from) && !empty($request->to)) {
            $tasks = $tasks->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), '>=', date('Y-m-d', strtotime($filterFrom)))
            ->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), '<=', date('Y-m-d', strtotime($filterTo)));
        } else if (!empty($request->from) && empty($request->to)) {
            $tasks = $tasks->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), '>=', date('Y-m-d', strtotime($filterFrom)));
        } else if (empty($request->from) && !empty($request->to)) {
            $tasks = $tasks->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), '<=', date('Y-m-d', strtotime($filterTo)));
        }

        $tasks = $tasks
        ->get()
        ->map(function ($el) {

            return [
                'checklist_task_id' => $el->id,
                'checklist_id' => $el->wf->checklist_id,
                'user' => $el->wf->user,
                'checklist_title' => $el->wf->checklist->name ?? '',
                'code' => $el->code,
                'do_not_allow_late_submission' => $el->do_not_allow_late_submission,
                'date' => date('d-m-Y H:i', strtotime($el->date))
            ];
        });
        
        $tasks = $tasks->toArray();

        return response()->json(['success' => $tasks], 200); 
    }

    public function getRedoActions(Request $request) {
        $validator = Validator::make($request->all(), [
            'task_id' => 'required|exists:checklist_tasks,id'
        ]);

        if ($validator->fails()) { 
            $errorString = implode(",",$validator->messages()->all());
            return response()->json(['error'=>$errorString], 401);
        }

        $page = $request->page > -1 ? $request->page : 0;
        $perPage = $request->record_per_page > 0 ? $request->record_per_page : 5;
        $skip = $page * $perPage;

        $tasks = RedoAction::with(['task'])
        ->when(is_numeric($request->workflow_id) && $request->workflow_id > 0, function ($builder) {
            $builder->whereHas('task.wf.parent', function ($query) {
                $query->where('id', request('workflow_id'));
            });
        })
        ->when(is_numeric($request->section_id) && $request->section_id > 0, function ($builder) {
            $builder->whereHas('task.wf.parent', function ($query) {
                $query->where('section_id', request('section_id'));
            });
        })
        ->whereHas('task', function ($builder) {
            $builder->where('id', request('task_id'))->workflow();
        })
        ->when($request->filter_status == 'PENDING' || $request->filter_status == 'COMPLETED', function ($builder) {
            if (request('filter_status') == 'PENDING') {
                $builder->where('status', 0);
            } else {
                $builder->where('status', 1);
            }
        });

        $taskCount = $tasks->clone()->count();

        $tasks = $tasks
        ->orderBy('status', 'ASC')
        ->skip($skip)
        ->take($perPage)
        ->get()
        ->map(function ($el) {
            $matchedItems = [];

            $versionedForm = Helper::getVersionForm($el->task->version_id);

            foreach (is_array($versionedForm) ? $versionedForm : [] as $section) {
                foreach ($section as $item) {
                    if (isset($item->className) && $item->className === $el->field_id) {
                        $item->last_submission = collect($el->task->data)
                        ->where('className', $item->className)
                        ->where('name', $item->name)
                        ->first();

                        $matchedItems[] = $item;
                    }
                }
            }

            return [
                'id' => $el->id,
                'task_id' => $el->task->id,
                'task_code' => $el->task->code,
                'class' => $el->field_id,
                'page' => $el->page,
                'title' => $el->title,
                'remarks' => $el->remarks,
                'start_at' => $el->start_at,
                'completed_by' => $el->completed_by,
                'do_not_allow_late_submission' => $el->do_not_allow_late_submission,
                'status' => $el->status,
                'status_label' => $el->status == 0 ? 'PENDING' : 'COMPLETED',
                'fields_todo' => $matchedItems
            ];
        });
        
        $tasks = $tasks->toArray();

        return response()->json(['success' => $tasks, 'total_records' => $taskCount, 'page' => intval($page), 'record_per_page' => $perPage], 200); 
    }

    public function submitRedo(Request $request) {
        $validator = Validator::make($request->all(), [
            'task_id' => 'required|exists:checklist_tasks,id'
        ]);

        if ($validator->fails()) { 
            $errorString = implode(",",$validator->messages()->all());
            return response()->json(['error'=>$errorString], 401);
        }

        TaskDeviceInformation::create([
            'eloquent' => ChecklistTask::class,
            'eloquent_id' => $request->task_id,
            'user_id' => auth()->check() ? auth()->user()->id : null,
            'device_model' => $request->device_model,
            'network_speed' => $request->network_speed,
            'device_version' => $request->device_version,
            'resubmission' => 1
        ]);

        if (is_string($request->data)) {
            $decodedJson = json_decode($request->data, true);
        } else {
            $decodedJson = $request->data;
        }

        if (empty($decodedJson)) {
            return response()->json(['error' => 'You must have to submit atleast a field']);
        }

        if (!file_exists(storage_path('app/public/workflow-task-uploads'))) {
            mkdir(storage_path('app/public/workflow-task-uploads'), 0777, true);
        }

        DB::beginTransaction();

        try {

            $totalItemsSubmitted = 0;
            $totaItemsInRequest = 0;
            $task = ChecklistTask::find($request->task_id);
            $json = $task->data;
            $forImageChecklistId = $task->wf->checklist_id ?? 'NA';
            $forImageTaskId = $task->id ?? 'NA';

            foreach ($decodedJson as $row) {
                RedoAction::where('id', $row['id'])->update(['status' => 1]);

                $tempArr = $row['data'];
                $totaItemsInRequest = count($tempArr);

                foreach ($tempArr as &$dt) {
                    if (array_key_exists('isFile', $dt) && $dt['isFile'] == true) {
                        if (is_array($dt['value'])) {
                            foreach ($dt['value'] as &$tempRow) {
                                if (strpos($tempRow, 'SIGN-20') !== false) {
                                    continue;
                                }
                                $tempRow = Helper::downloadBase64FileWebp($tempRow, ('SIGN-' . date('YmdHis') . uniqid() . '-' . $forImageChecklistId . '-' . $forImageTaskId), storage_path('app/public/workflow-task-uploads'));
                            }
                        } else {
                            if (strpos($dt['value'], 'SIGN-20') !== false) {
                                continue;
                            }
                            $dt['value'] = Helper::downloadBase64FileWebp($dt['value'], ('SIGN-' . date('YmdHis') . uniqid() . '-' . $forImageChecklistId . '-' . $forImageTaskId), storage_path('app/public/workflow-task-uploads'));
                        }
                    }
                }

                foreach ($json as &$item) {
                    if (isset($item->className) && $item->className === $row['class']) {
                        if (is_iterable($tempArr)) {
                            foreach ($tempArr as $k => $v) {
                                if ((is_array($item) ? $item['name'] : $item->name) == (is_array($v) ? $v['name'] : $v->name)) {
                                    $totalItemsSubmitted++;
                                    $item = $v;
                                }
                            }
                        }
                    }
                }
            }

            if ($totalItemsSubmitted < $totaItemsInRequest) {
                $tempArr = array_map(function ($item) {
                    return (object) $item;
                }, $tempArr);
                $json = array_merge($json, $tempArr);
            }

            $task->data = $json;
            $task->save();

            //generating from notification center
            // \App\Jobs\GenerateOptimizedTaskPdf::dispatch($task->id);            

            DB::commit();
            return response()->json(['success' => 'Assignment submitted successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('REASSIGNMENT FAILURE: ' . $e->getMessage() . ' ON LINE : ' . $e->getLine());
            return response()->json(['error' => 'You must have to submit atleast a field']);
        }
    }

    public function reassignmentTasks(Request $request) {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id'
        ]);

        if ($validator->fails()) { 
            $errorString = implode(",",$validator->messages()->all());
            return response()->json(['error'=>$errorString], 401);
        }

        $page = $request->page > -1 ? $request->page : 0;
        $perPage = $request->record_per_page > 0 ? $request->record_per_page : 5;
        $skip = $page * $perPage;

        $tasks = ChecklistTask::with(['wf.parent'])->workflow()
        ->when(is_numeric($request->workflow_id) && $request->workflow_id > 0, function ($builder) {
            $builder->whereHas('wf.parent', function ($query) {
                $query->where('id', request('workflow_id'));
            });
        })
        ->when(is_numeric($request->section_id) && $request->section_id > 0, function ($builder) {
            $builder->whereHas('wf.parent', function ($query) {
                $query->where('section_id', request('section_id'));
            });
        })
        ->whereHas('redos', function ($builder) {
            $builder->where('status', 0);
        })
        ->when($request->filter_status == 'PENDING' || $request->filter_status == 'COMPLETED', function ($builder) {
            if (request('filter_status') == 'PENDING') {
                $builder->whereHas('redos', function ($innerBuilder) {
                    $innerBuilder->where('status', 0);
                });
            } else {
                $builder->whereDoesntHave('redos', function ($innerBuilder) {
                    $innerBuilder->where('status', 0);
                });
            }
        })
        ->where(function ($innerBuilder) {
            $innerBuilder->whereHas('wf', function ($query) {
                $query->where('user_id', auth()->user()->id);
            });
        })
        ->scheduling();

        $taskCount = $tasks->clone()->count();

        $tasks = $tasks
        ->skip($skip)
        ->take($perPage)
        ->get()
        ->map(function ($el) {
            $reDoArray = [];
            $tempPage = null;
            
            foreach ($el->redos as $x) {
                $matchedItems = [];

                $versionedForm = Helper::getVersionForm($el->version_id);

                foreach (is_array($versionedForm) ? $versionedForm : [] as $section) {
                    foreach ($section as $item) {
                        if (isset($item->className) && $item->className === $x->field_id) {
                            $item->last_submission = collect($el->data)
                            ->where('className', $item->className)
                            ->where('name', $item->name)
                            ->first();
                            
                            $matchedItems[] = $item;

                            if ($tempPage == null) {
                                $tempPage = isset($item->last_submission->page) ? $item->last_submission->page : 0;
                            }
                        }
                    }
                }

                $reDoArray[$tempPage] = [
                    'class' => $x->field_id,
                    'title' => $x->title,
                    'remarks' => $x->remarks,
                    'start_at' => $x->start_at,
                    'completed_by' => $x->completed_by,
                    'do_not_allow_late_submission' => $x->do_not_allow_late_submission,
                    'status' => $x->status,
                    'status_label' => $x->status == 0 ? 'PENDING' : 'COMPLETED',
                    'fields_todo' => array_values($matchedItems),
                    'page' => intval($tempPage)
                ];

                $tempPage = null;
            }


            if ($el->status == 0) {
                $statusLabel = 'PENDING';
            } else if ($el->status == 1) {
                $statusLabel = 'IN-PROGRESS';
            } else if ($el->status == 2) {

                if (request('task_type') == 1 && request('filter_status') == 'PENDING_VERIFICATION') {
                    $statusLabel = 'PENDING-VERIFICATION';
                } else {
                    if (isset($el->wf->checker_id)) {
                        if ($el->redos()->where('status', 1)->count() == 0 && $el->redos()->where('status', 0)->count() > 0) {
                            $statusLabel = 'REASSIGNED';
                        } else if ($el->redos()->where('status', 0)->count() == 0 && $el->redos()->where('status', 1)->count() == 0) {
                            $statusLabel = 'PENDING-VERIFICATION';
                        } else {
                            $statusLabel = 'VERIFYING';
                        }
                    } else {
                        $statusLabel = 'COMPLETED';
                    }
                }

            } else {
                if (isset($el->wf->checker_id)) {
                    $statusLabel = 'VERIFIED';
                } else {
                    $statusLabel = 'COMPLETED';
                }
            }

            $thisVersionedForm = Helper::getVersionForm($el->version_id);

            return [
                'checklist_id' => $el->wf->checklist_id,
                'task_id' => $el->id,
                'reassignment_data' => array_values($reDoArray),
                'user' => $el->wf->user,
                'checklist_title' => $el->wf->checklist->name ?? '',
                'code' => $el->code,
                'is_point_checklist' => Helper::isPointChecklist($thisVersionedForm),
                'status_label' => $statusLabel,
            ];
        });
        
        $tasks = $tasks->toArray();

        return response()->json(['success' => $tasks, 'total_records' => $taskCount, 'page' => intval($page), 'record_per_page' => $perPage], 200); 
    }
}