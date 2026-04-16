<?php

namespace App\Http\Controllers;

use App\Models\NewWorkflowAssignmentItem;
use App\Models\NewWorkflowAssignment;
use App\Models\NewWorkflowTemplate;
use Illuminate\Support\Facades\DB;
use App\Models\ChecklistTask;
use Illuminate\Http\Request;
use App\Models\DynamicForm;
use App\Models\Department;
use App\Helpers\Helper;
use App\Models\User;
use Carbon\Carbon;

class WorkflowAssignmentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $checklistScheduling = NewWorkflowAssignment::latest();

            return datatables()
                ->eloquent($checklistScheduling)
                ->editColumn('status', function ($row) {
                    if ($row->status) {
                        return '<span class="badge bg-success"> Active </span>';
                    } else {
                        return '<span class="badge bg-danger"> InActive </span>';
                    }
                })
                ->addColumn('on_going', function ($row) {
                    return $row->on_going_project ? 'Yes' : 'No';
                })
                ->editColumn('start_from', function ($row) {
                    return $row->start_from ? $row->start_from->format('Y-m-d H:i') : '-';
                })
                ->addColumn('action', function ($row) {
                    $action = '';

                    if (auth()->user()->can('workflow-assignments.show')) {
                        $action .= '<a href="' . route("workflow-assignments.show", encrypt($row->id)) . '" class="btn btn-warning btn-sm me-2"> Show </a>';
                        $action .= '<a href="' . route("workflow-dashboard", encrypt($row->id)) . '" class="btn btn-primary btn-sm me-2" title="Visualize"><i class="bi bi-speedometer2"></i></a>';
                    }

                    if (auth()->user()->can('workflow-assignments.edit')) {
                        $action .= '<a href="' . route('workflow-assignments.edit', encrypt($row->id)) . '" class="btn btn-info btn-sm me-2">Edit</a>';
                    }

                    // Export buttons
                    $action .= '<a href="' . route('workflow-assignments.export-excel', encrypt($row->id)) . '" class="btn btn-success btn-sm me-2">Excel</a>';
                    $action .= '<a href="' . route('workflow-assignments.export-pdf', encrypt($row->id)) . '" class="btn btn-secondary btn-sm me-2">PDF</a>';

                    if (auth()->user()->can('workflow-assignments.destroy')) {
                        $action .= '<form method="POST" action="' . route("workflow-assignments.destroy", encrypt($row->id)) . '" style="display:inline;"><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="' . csrf_token() . '"><button type="submit" class="btn btn-danger btn-sm deleteGroup">Delete</button></form>';
                    }

                    return $action;
                })
                ->addColumn('stepscnt', function ($row) {
                    return $row->children()->count();
                })
                ->rawColumns(['action', 'status'])
                ->toJson();
        }

        $page_title = 'Project Assignment';
        $page_description = 'Manage project assignment here';
        return view('workflow-assignments.index', compact('page_title', 'page_description'));
    }

    /**
     * Export all workflow tasks of a given assignment to Excel.
     *
     * Columns:
     * - DEPARTMENT  => items.section_code . ' ' . items.section_name
     * - TASK        => items.step_name
     * - MAKER NAME  => full name of items.user_id
     * - CHECKER NAME=> full name of items.checker_id
     * - TAT BREACH  => Yes/No based on completion_date > completed_by
     * - END DATE    => on_going_project ? on_going_completion_date : completion_date
     * - STATUS      => checklist_tasks.status
     */
    public function exportAssignmentExcel($encryptedId)
    {
        $currentUser = auth()->user()->id;
        $thisUserRoles = auth()->user()->roles()->pluck('id')->toArray();

        $assignmentId = decrypt($encryptedId);

        $assignment = NewWorkflowAssignment::findOrFail($assignmentId);

        $tasks = ChecklistTask::with(['wf.user', 'wf.checker'])
            ->where('type', 1)
            ->whereHas('wf', function ($builder) use ($assignmentId) {
                $builder->where('new_workflow_assignment_id', $assignmentId);
            })
            ->orderBy('date', 'ASC')
            ->get();

        $data = [];

        // Header row
        $data[] = [
            'DEPARTMENT',
            'TASK',
            'MAKER NAME',
            'CHECKER NAME',
            'TAT BREACH',
            'END DATE',
            'STATUS'
        ];

        foreach ($tasks as $task) {
            if (!$task->wf) {
                continue;
            }

            $html = 'Pending';

            $item = $task->wf;

            $department = trim(($item->section_name ?? ''));
            // $department = trim(($item->section_code ?? '') . ' ' . ($item->section_name ?? ''));

            $maker = '';
            if ($item->user) {
                $maker = trim(($item->user->name ?? '') . ' ' . ($item->user->middle_name ?? '') . ' ' . ($item->user->last_name ?? ''));
            }

            $checker = '';
            if ($item->checker) {
                $checker = trim(($item->checker->name ?? '') . ' ' . ($item->checker->middle_name ?? '') . ' ' . ($item->checker->last_name ?? ''));
            }

            // TAT breach: completion_date > completed_by => Yes
            $breach = 'No';
            if (!empty($task->completion_date) && !empty($task->completed_by)) {
                $completion = Carbon::parse($task->completion_date);
                $due = Carbon::parse($task->completed_by);
                if ($completion->gt($due)) {
                    $breach = 'Yes';
                }
            }

            // END DATE
            if ($assignment->on_going_project == 1 && !empty($task->on_going_completion_date)) {
                $endDate = Carbon::parse($task->on_going_completion_date)->format('Y-m-d H:i');
            } elseif (!empty($task->completion_date)) {
                $endDate = Carbon::parse($task->completion_date)->format('Y-m-d H:i');
            } else {
                $endDate = '';
            }

            if ((in_array(Helper::$roles['admin'], $thisUserRoles) || $task->checker_id == $currentUser)) {
                if (in_array($task->status, [2, 3])) {

                    if ($task->status == 0) {
                        $html = 'Pending';
                    } else if ($task->status == 1) {
                        $html = 'In-Progress';
                    } else if ($task->status == 2) {
                        if (isset($task->wf->checker_id)) {
                            if ($task->redos()->count() == 0) {
                                $html = 'Pending Verification';
                            } else if ($task->redos()->where('status', 1)->count() == 0) {
                                $html = 'Reassigned';
                            } else {
                                $html = 'Verifying';
                            }
                        } else {
                            $html = 'Completed';
                        }
                    } else {
                        if (isset($task->wf->checker_id)) {
                            $html = 'Verified';
                        } else {
                            $html = 'Completed';
                        }
                    }

                } else {
                    if ($task->status == 0) {
                        $html = 'Pending';
                    } else if ($task->status == 1) {
                        $html = 'In-Progress';
                    } else if ($task->status == 2) {
                        if (isset($task->wf->checker_id)) {
                            if ($task->redos()->count() == 0) {
                                $html = 'Pending Verification';
                            } else if ($task->redos()->where('status', 1)->count() == 0) {
                                $html = 'Reassigned';
                            } else {
                                $html = 'Verifying';
                            }
                        } else {
                            $html = 'Completed';
                        }
                    } else {
                        if (isset($task->wf->checker_id)) {
                            $html = 'Verified';
                        } else {
                            $html = 'Completed';
                        }
                    }
                }
                
            } else {
                if ($task->status == 0) {
                    $html = 'Pending';
                } else if ($task->status == 1) {
                    $html = 'In-Progress';
                } else if ($task->status == 2) {
                    if (isset($task->wf->checker_id)) {
                        if ($task->redos()->count() == 0) {
                            $html = 'Pending Verification';
                        } else if ($task->redos()->where('status', 1)->count() == 0) {
                            $html = 'Reassigned';
                        } else {
                            $html = 'Verifying';
                        }
                    } else {
                        $html = 'Completed';
                    }
                } else {
                    if (isset($task->wf->checker_id)) {
                        $html = 'Verified';
                    } else {
                        $html = 'Completed';
                    }
                }
            }            

            $data[] = [
                $department,
                $item->step_name ?? '',
                $maker,
                $checker,
                $breach,
                $endDate,
                $html,
            ];
        }

        // Simple styling: header row highlighted
        $styleData = [
            [
                'type' => 'header_row',
                'row' => 1,
            ],
        ];

        $fileName = 'workflow-assignment-' . $assignmentId . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\StyledTaskExport($data, $styleData),
            $fileName
        );
    }

    /**
     * Placeholder PDF export for a workflow assignment.
     * Implemented later as per requirements.
     */
    public function exportAssignmentPdf($encryptedId)
    {
        $assignmentId = decrypt($encryptedId);

        $assignment = NewWorkflowAssignment::findOrFail($assignmentId);
        $assignment = $assignment->load([
            'children' => function ($query) {
                $query->with(['user', 'checker', 'department', 'checklist']);
            }
        ]);

        // Build rows similar to Excel export
        $currentUser = auth()->user()->id;
        $thisUserRoles = auth()->user()->roles()->pluck('id')->toArray();

        $tasks = ChecklistTask::with(['wf.user', 'wf.checker'])
            ->where('type', 1)
            ->whereHas('wf', function ($builder) use ($assignmentId) {
                $builder->where('new_workflow_assignment_id', $assignmentId);
            })
            ->orderBy('date', 'ASC')
            ->get();

        $rows = [];

        foreach ($tasks as $task) {
            if (!$task->wf) {
                continue;
            }

            $html = 'Pending';

            $item = $task->wf;

            $department = trim(($item->section_name ?? ''));

            $maker = '';
            if ($item->user) {
                $maker = trim(($item->user->name ?? '') . ' ' . ($item->user->middle_name ?? '') . ' ' . ($item->user->last_name ?? ''));
            }

            $checker = '';
            if ($item->checker) {
                $checker = trim(($item->checker->name ?? '') . ' ' . ($item->checker->middle_name ?? '') . ' ' . ($item->checker->last_name ?? ''));
            }

            // TAT breach: completion_date > completed_by => Yes
            $breach = 'No';
            if (!empty($task->completion_date) && !empty($task->completed_by)) {
                $completion = Carbon::parse($task->completion_date);
                $due = Carbon::parse($task->completed_by);
                if ($completion->gt($due)) {
                    $breach = 'Yes';
                }
            }

            // END DATE
            if ($assignment->on_going_project == 1 && !empty($task->on_going_completion_date)) {
                $endDate = Carbon::parse($task->on_going_completion_date)->format('Y-m-d H:i');
            } elseif (!empty($task->completion_date)) {
                $endDate = Carbon::parse($task->completion_date)->format('Y-m-d H:i');
            } else {
                $endDate = '';
            }

            // Human readable status (same logic as Excel)
            if ((in_array(Helper::$roles['admin'], $thisUserRoles) || $task->checker_id == $currentUser)) {
                if (in_array($task->status, [2, 3])) {

                    if ($task->status == 0) {
                        $html = 'Pending';
                    } else if ($task->status == 1) {
                        $html = 'In-Progress';
                    } else if ($task->status == 2) {
                        if (isset($task->wf->checker_id)) {
                            if ($task->redos()->count() == 0) {
                                $html = 'Pending Verification';
                            } else if ($task->redos()->where('status', 1)->count() == 0) {
                                $html = 'Reassigned';
                            } else {
                                $html = 'Verifying';
                            }
                        } else {
                            $html = 'Completed';
                        }
                    } else {
                        if (isset($task->wf->checker_id)) {
                            $html = 'Verified';
                        } else {
                            $html = 'Completed';
                        }
                    }

                } else {
                    if ($task->status == 0) {
                        $html = 'Pending';
                    } else if ($task->status == 1) {
                        $html = 'In-Progress';
                    } else if ($task->status == 2) {
                        if (isset($task->wf->checker_id)) {
                            if ($task->redos()->count() == 0) {
                                $html = 'Pending Verification';
                            } else if ($task->redos()->where('status', 1)->count() == 0) {
                                $html = 'Reassigned';
                            } else {
                                $html = 'Verifying';
                            }
                        } else {
                            $html = 'Completed';
                        }
                    } else {
                        if (isset($task->wf->checker_id)) {
                            $html = 'Verified';
                        } else {
                            $html = 'Completed';
                        }
                    }
                }

            } else {
                if ($task->status == 0) {
                    $html = 'Pending';
                } else if ($task->status == 1) {
                    $html = 'In-Progress';
                } else if ($task->status == 2) {
                    if (isset($task->wf->checker_id)) {
                        if ($task->redos()->count() == 0) {
                            $html = 'Pending Verification';
                        } else if ($task->redos()->where('status', 1)->count() == 0) {
                            $html = 'Reassigned';
                        } else {
                            $html = 'Verifying';
                        }
                    } else {
                        $html = 'Completed';
                    }
                } else {
                    if (isset($task->wf->checker_id)) {
                        $html = 'Verified';
                    } else {
                        $html = 'Completed';
                    }
                }
            }

            $rows[] = [
                'department' => $department,
                'task' => $item->step_name ?? '',
                'maker' => $maker,
                'checker' => $checker,
                'breach' => $breach,
                'end_date' => $endDate,
                'status' => $html,
            ];
        }

        // return View('workflow-assignments.assignment-pdf', [
        //     'assignment' => $assignment,
        //     'rows' => $rows,
        // ]);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('workflow-assignments.assignment-pdf', [
            'assignment' => $assignment,
            'rows' => $rows,
        ])->setPaper('A4');

        return $pdf->download('workflow-assignment-' . $assignmentId . '.pdf');
    }

    public function create()
    {
        $templates = NewWorkflowTemplate::where('status', 1)->orderBy('title')->get(['id', 'title']);
        $title = $description = '';
        $sections = [];
        $template = null;

        $template = NewWorkflowTemplate::with('children')->find(request('template_id'));

        if (request()->has('template_id') && is_numeric(request('template_id')) && request('template_id') > 0 && isset($template->id)) {
            if ($template->sections && is_array($template->sections)) {
                foreach ($template->sections as $section) {
                    $sectionId = $section['id'] ?? 'section_' . uniqid();
                    $sections[$sectionId] = [
                        'id' => $sectionId,
                        'name' => $section['name'] ?? '',
                        'code' => $section['code'] ?? '',
                        'description' => $section['description'] ?? '',
                        'steps' => []
                    ];
                }
            }

            // Load steps for each section
            foreach ($template->children as $step) {
                $sectionId = $step->section_id;
                if (!isset($sections[$sectionId])) {
                    $sections[$sectionId] = [
                        'id' => $sectionId,
                        'name' => $step->section_name ?? '',
                        'code' => $step->section_code ?? '',
                        'description' => $step->section_description ?? '',
                        'steps' => []
                    ];
                }

                $stepId = $step->id;
                $sections[$sectionId]['steps'][$stepId] = [
                    'id' => $stepId,
                    'record_id' => null, // New step, no record_id
                    'globalNumber' => $step->step,
                    'step_name' => $step->step_name ?? '',
                    'department_id' => $step->department_id ?? '',
                    'checklist_id' => $step->checklist_id ?? '',
                    'checklist_description' => $step->checklist_description ?? '',
                    'trigger' => $step->trigger ?? 0,
                    'dependency' => $step->dependency ?? 'NO_DEPENDENCY',
                    'dependency_steps' => $step->dependency_steps ?? [],
                    'is_entry_point' => $step->is_entry_point ?? false,
                    'user_id' => $step->user_id ?? '',
                    'maker_turn_around_time_day' => $step->maker_turn_around_time_day ?? '',
                    'maker_turn_around_time_hour' => $step->maker_turn_around_time_hour ?? '',
                    'maker_turn_around_time_minute' => $step->maker_turn_around_time_minute ?? '',
                    'maker_escalation_user_id' => $step->maker_escalation_user_id ?? '',
                    'maker_escalation_after_day' => $step->maker_escalation_after_day ?? '',
                    'maker_escalation_after_hour' => $step->maker_escalation_after_hour ?? '',
                    'maker_escalation_after_minute' => $step->maker_escalation_after_minute ?? '',
                    'maker_escalation_email_notification' => $step->maker_escalation_email_notification ?? '',
                    'maker_escalation_push_notification' => $step->maker_escalation_push_notification ?? '',
                    'checker_id' => $step->checker_id ?? '',
                    'checker_turn_around_time_day' => $step->checker_turn_around_time_day ?? '',
                    'checker_turn_around_time_hour' => $step->checker_turn_around_time_hour ?? '',
                    'checker_turn_around_time_minute' => $step->checker_turn_around_time_minute ?? '',
                    'checker_escalation_user_id' => $step->checker_escalation_user_id ?? '',
                    'checker_escalation_after_day' => $step->checker_escalation_after_day ?? '',
                    'checker_escalation_after_hour' => $step->checker_escalation_after_hour ?? '',
                    'checker_escalation_after_minute' => $step->checker_escalation_after_minute ?? '',
                    'checker_escalation_email_notification' => $step->checker_escalation_email_notification ?? '',
                    'checker_escalation_push_notification' => $step->checker_escalation_push_notification ?? '',
                    'department' => $step->department ? ['id' => $step->department->id, 'name' => $step->department->name] : null,
                    'checklist' => $step->checklist ? ['id' => $step->checklist->id, 'name' => $step->checklist->name] : null,
                    'user' => $step->user ? ['id' => $step->user->id, 'name' => $step->user->name, 'last_name' => $step->user->last_name ?? ''] : null,
                    'checker' => $step->checker ? ['id' => $step->checker->id, 'name' => $step->checker->name, 'last_name' => $step->checker->last_name ?? ''] : null,
                ];
            }
        }

        return view('workflow-assignments.create', compact('templates', 'title', 'description', 'sections', 'template'));
    }

    public function loadTemplate($id)
    {
        $template = NewWorkflowTemplate::with('children')->find($id);
        
        if (!$template) {
            return response()->json(['error' => 'Template not found'], 404);
        }

        $sections = [];
        if ($template->sections && is_array($template->sections)) {
            foreach ($template->sections as $section) {
                $sectionId = $section['id'] ?? 'section_' . uniqid();
                $sections[$sectionId] = [
                    'id' => $sectionId,
                    'name' => $section['name'] ?? '',
                    'code' => $section['code'] ?? '',
                    'description' => $section['description'] ?? '',
                    'steps' => []
                ];
            }
        }

        // Load steps for each section
        foreach ($template->children as $step) {
            $sectionId = $step->section_id;
            if (!isset($sections[$sectionId])) {
                $sections[$sectionId] = [
                    'id' => $sectionId,
                    'name' => $step->section_name ?? '',
                    'code' => $step->section_code ?? '',
                    'description' => $step->section_description ?? '',
                    'steps' => []
                ];
            }

            $stepId = $step->id;
            $sections[$sectionId]['steps'][$stepId] = [
                'id' => $stepId,
                'record_id' => null, // New step, no record_id
                'globalNumber' => $step->step,
                'step_name' => $step->step_name ?? '',
                'department_id' => $step->department_id ?? '',
                'checklist_id' => $step->checklist_id ?? '',
                'checklist_description' => $step->checklist_description ?? '',
                'trigger' => $step->trigger ?? 0,
                'dependency' => $step->dependency ?? 'NO_DEPENDENCY',
                'dependency_steps' => $step->dependency_steps ?? [],
                'is_entry_point' => $step->is_entry_point ?? false,
                'user_id' => $step->user_id ?? '',
                'maker_turn_around_time_day' => $step->maker_turn_around_time_day ?? '',
                'maker_turn_around_time_hour' => $step->maker_turn_around_time_hour ?? '',
                'maker_turn_around_time_minute' => $step->maker_turn_around_time_minute ?? '',
                'maker_escalation_user_id' => $step->maker_escalation_user_id ?? '',
                'maker_escalation_after_day' => $step->maker_escalation_after_day ?? '',
                'maker_escalation_after_hour' => $step->maker_escalation_after_hour ?? '',
                'maker_escalation_after_minute' => $step->maker_escalation_after_minute ?? '',
                'maker_escalation_email_notification' => $step->maker_escalation_email_notification ?? '',
                'maker_escalation_push_notification' => $step->maker_escalation_push_notification ?? '',
                'checker_id' => $step->checker_id ?? '',
                'checker_turn_around_time_day' => $step->checker_turn_around_time_day ?? '',
                'checker_turn_around_time_hour' => $step->checker_turn_around_time_hour ?? '',
                'checker_turn_around_time_minute' => $step->checker_turn_around_time_minute ?? '',
                'checker_escalation_user_id' => $step->checker_escalation_user_id ?? '',
                'checker_escalation_after_day' => $step->checker_escalation_after_day ?? '',
                'checker_escalation_after_hour' => $step->checker_escalation_after_hour ?? '',
                'checker_escalation_after_minute' => $step->checker_escalation_after_minute ?? '',
                'checker_escalation_email_notification' => $step->checker_escalation_email_notification ?? '',
                'checker_escalation_push_notification' => $step->checker_escalation_push_notification ?? '',
                'department' => $step->department ? ['id' => $step->department->id, 'name' => $step->department->name] : null,
                'checklist' => $step->checklist ? ['id' => $step->checklist->id, 'name' => $step->checklist->name] : null,
                'user' => $step->user ? ['id' => $step->user->id, 'name' => $step->user->name, 'last_name' => $step->user->last_name ?? ''] : null,
                'checker' => $step->checker ? ['id' => $step->checker->id, 'name' => $step->checker->name, 'last_name' => $step->checker->last_name ?? ''] : null,
            ];
        }

        return response()->json([
            'title' => $template->title,
            'description' => $template->description,
            'sections' => $sections,
            'template' => $template
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateStore($request);

        return DB::transaction(function () use ($validated) {
            $sectionsData = [];
            if (isset($validated['sections'])) {
                foreach ($validated['sections'] as $sectionId => $sectionData) {
                    $sectionsData[] = [
                        'id' => $sectionId,
                        'name' => $sectionData['name'],
                        'code' => $sectionData['code'],
                        'description' => $sectionData['description'] ?? null,
                        'order' => array_search($sectionId, array_keys($validated['sections'])) + 1
                    ];
                }
            }

            $assignment = NewWorkflowAssignment::create([
                'new_workflow_template_id' => $validated['new_workflow_template_id'] ?? null,
                'title' => $validated['title'],
                'status' => request('status') == 1 ? 1 : 0,
                'description' => $validated['description'] ?? null,
                'start_from' => $validated['start_from'] ?? null,
                'sections' => $sectionsData,
                'added_by' => auth()->user()->id,
                'send_to_all_notification' => isset($validated['send_to_all_notification']) && $validated['send_to_all_notification'] == 'true' ? 1 : 0,
                'on_going_project' => isset($validated['on_going_project']) && $validated['on_going_project'] == 'true' ? 1 : 0
            ]);

            $dependencyVariable = [];

            if (isset($validated['sections'])) {
                foreach ($validated['sections'] as $sectionId => $sectionData) {
                    if (isset($sectionData['steps']) && is_array($sectionData['steps'])) {
                        $sectionOrder = array_search($sectionId, array_keys($validated['sections'])) + 1;

                        foreach ($sectionData['steps'] as $stepId => $stepData) {
                            $stepOrder = array_search($stepId, array_keys($sectionData['steps'])) + 1;
                            $stepNumber = $stepData['step'] ?? $stepOrder;

                            $newStep = NewWorkflowAssignmentItem::create([
                                'new_workflow_assignment_id' => $assignment->id,
                                'section_id' => $sectionId,
                                'section_name' => $sectionData['name'],
                                'section_code' => $sectionData['code'],
                                'section_description' => $sectionData['description'] ?? null,
                                'section_order' => $sectionOrder,
                                'step_order' => $stepOrder,
                                'step' => $stepNumber,
                                'step_name' => $stepData['step_name'] ?? null,
                                'department_id' => $stepData['department_id'] ?? null,
                                'checklist_id' => $stepData['checklist_id'] ?? null,
                                'checklist_description' => $stepData['checklist_description'] ?? null,
                                'user_id' => $stepData['user_id'] ?? null,
                                'turn_around_time' => $stepData['turn_around_time'] ?? null,
                                'trigger' => $stepData['trigger'] ?? 0,
                                'dependency' => $stepData['dependency'] ?? 'NO_DEPENDENCY',
                                'dependency_steps' => [],
                                'is_entry_point' => filter_var($stepData['is_entry_point'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 1 : 0,

                                'maker_escalation_user_id' => $stepData['maker_escalation_user_ids'][0] ?? ($stepData['maker_escalation_user_id'] ?? null),
                                'maker_escalation_user_ids' => $stepData['maker_escalation_user_ids'] ?? [],
                                'maker_turn_around_time_day' => $stepData['maker_turn_around_time_day'] ?? null,
                                'maker_turn_around_time_hour' => $stepData['maker_turn_around_time_hour'] ?? null,
                                'maker_turn_around_time_minute' => $stepData['maker_turn_around_time_minute'] ?? null,
                                'maker_escalation_after_day' => $stepData['maker_escalation_after_day'] ?? null,
                                'maker_escalation_after_hour' => $stepData['maker_escalation_after_hour'] ?? null,
                                'maker_escalation_after_minute' => $stepData['maker_escalation_after_minute'] ?? null,
                                'maker_escalation_email_notification' => $stepData['maker_escalation_email_notification'] ?? null,
                                'maker_escalation_push_notification' => $stepData['maker_escalation_push_notification'] ?? null,
                                'maker_completion_email_notification' => $stepData['maker_completion_email_notification'] ?? null,
                                'maker_completion_push_notification' => $stepData['maker_completion_push_notification'] ?? null,
                                'maker_dependency_email_notification' => $stepData['maker_dependency_email_notification'] ?? null,
                                'maker_dependency_push_notification' => $stepData['maker_dependency_push_notification'] ?? null,

                                'checker_id' => $stepData['checker_id'] ?? null,
                                'checker_turn_around_time_day' => $stepData['checker_turn_around_time_day'] ?? null,
                                'checker_turn_around_time_hour' => $stepData['checker_turn_around_time_hour'] ?? null,
                                'checker_turn_around_time_minute' => $stepData['checker_turn_around_time_minute'] ?? null,

                                'checker_escalation_user_id' => $stepData['checker_escalation_user_ids'][0] ?? ($stepData['checker_escalation_user_id'] ?? null),
                                'checker_escalation_user_ids' => $stepData['checker_escalation_user_ids'] ?? [],
                                'checker_escalation_after_day' => $stepData['checker_escalation_after_day'] ?? null,
                                'checker_escalation_after_hour' => $stepData['checker_escalation_after_hour'] ?? null,
                                'checker_escalation_after_minute' => $stepData['checker_escalation_after_minute'] ?? null,
                                'checker_escalation_email_notification' => $stepData['checker_escalation_email_notification'] ?? null,
                                'checker_escalation_push_notification' => $stepData['checker_escalation_push_notification'] ?? null,
                            ]);

                            $dependencyVariable[] = [
                                'reference' => $stepData['id'],
                                'record' => $newStep->id
                            ];
                        }
                    }
                }
            }

            $dependencyVariable = collect($dependencyVariable);

            if ($dependencyVariable->count() > 0) {
                foreach ($validated['sections'] as $sectionId => $sectionData) {
                    if (isset($sectionData['steps']) && is_array($sectionData['steps'])) {
                        foreach ($sectionData['steps'] as $stepId => $stepData) {
                            if (!empty($stepData['dependency_steps'])) {
                                $origin = $dependencyVariable->where('reference', $stepData['id'])->first()['record'] ?? '';
                                $targets = $dependencyVariable->whereIn('reference', $stepData['dependency_steps'])->pluck('record')->toArray();

                                if (!empty($origin) && !empty($targets) && is_numeric($origin)) {
                                    NewWorkflowAssignmentItem::where('id', $origin)->update([
                                        'dependency_steps' => self::stringToInt($targets)
                                    ]);
                                }
                            }
                        }
                    }
                }
            }

            $items = NewWorkflowAssignmentItem::with(['checklist', 'user'])->where('new_workflow_assignment_id', $assignment->id)->orderBy('step_order')->get();
            $currentDate = $assignment->start_from ? $assignment->start_from->copy() : Carbon::now();

            foreach ($items as $item) {
                $days = (int) ($item->maker_turn_around_time_day ?? 0);
                $hours = (int) ($item->maker_turn_around_time_hour ?? 0);
                $minutes = (int) ($item->maker_turn_around_time_minute ?? 0);


                $hash = md5(json_encode($item->checklist->schema ?? []));
                $endDate = $currentDate->copy()->addDays($days)->addHours($hours)->addMinutes($minutes);

                $createdTask = ChecklistTask::create([
                    'type' => 1,
                    'workflow_checklist_id' => $item->id,
                    'version_id' => Helper::getFormVersion($item->checklist_id, $hash),
                    'date' => $currentDate->format('Y-m-d H:i:s'),
                    'completed_by' => $endDate->format('Y-m-d H:i:s'),
                    'status' => 0,
                    'code' => 'WF-' . ($item->user->employee_id ?? '') . '-' . $currentDate->format('Y-m-d')
                ]);

                \App\Jobs\SendWorkflowTaskStartAlert::dispatch($createdTask, 'maker')->delay($currentDate->subMinutes(30));

                $currentDate = $endDate;
            }

            return redirect()->route('workflow-assignments.index')->withSuccess('Assignment created successfully');
        });
    }

    public function edit($id)
    {
        $assignment = NewWorkflowAssignment::find(decrypt($id));
        $assignment = $assignment->load('children');
        $templates = NewWorkflowTemplate::where('status', 1)->orderBy('title')->get(['id', 'title']);

        return view('workflow-assignments.edit', compact('assignment', 'templates'));
    }

    public function update(Request $request, $id)
    {
        $validated = $this->validateStore($request);

        $new_workflow_assignment = NewWorkflowAssignment::find($id);

        return DB::transaction(function () use ($validated, $new_workflow_assignment) {
            $sectionsData = [];
            if (isset($validated['sections'])) {
                foreach ($validated['sections'] as $sectionId => $sectionData) {
                    $sectionsData[] = [
                        'id' => $sectionId,
                        'name' => $sectionData['name'],
                        'code' => $sectionData['code'],
                        'description' => $sectionData['description'] ?? null,
                        'order' => array_search($sectionId, array_keys($validated['sections'])) + 1
                    ];
                }
            }

            $new_workflow_assignment->update([
                'title' => $validated['title'],
                'status' => request('status') == 1 ? 1 : 0,
                'description' => $validated['description'] ?? null,
                'sections' => $sectionsData,
                'send_to_all_notification' => isset($validated['send_to_all_notification']) && $validated['send_to_all_notification'] == 'true' ? 1 : 0,
                'on_going_project' => isset($validated['on_going_project']) && $validated['on_going_project'] == 'true' ? 1 : 0
            ]);

            $allSteps = [];
            $dependencyVariable = [];

            if (isset($validated['sections'])) {
                foreach ($validated['sections'] as $sectionId => $sectionData) {
                    if (isset($sectionData['steps']) && is_array($sectionData['steps'])) {
                        $sectionOrder = array_search($sectionId, array_keys($validated['sections'])) + 1;

                        foreach ($sectionData['steps'] as $stepId => $stepData) {
                            $stepOrder = array_search($stepId, array_keys($sectionData['steps'])) + 1;

                            if (isset($stepData['record_id']) && is_numeric($stepData['record_id']) && $stepData['record_id'] > 0 && NewWorkflowAssignmentItem::where('id', $stepData['record_id'])->where('new_workflow_assignment_id', $new_workflow_assignment->id)->exists()) {
                                NewWorkflowAssignmentItem::where('id', $stepData['record_id'])->update([
                                    'new_workflow_assignment_id' => $new_workflow_assignment->id,
                                    'section_id' => $sectionId,
                                    'section_name' => $sectionData['name'],
                                    'section_code' => $sectionData['code'],
                                    'section_description' => $sectionData['description'] ?? null,
                                    'section_order' => $sectionOrder,
                                    'step_order' => $stepOrder,
                                    'step' => $stepData['step'] ?? $stepOrder,
                                    'step_name' => $stepData['step_name'] ?? null,
                                    'department_id' => $stepData['department_id'] ?? null,
                                    'checklist_id' => $stepData['checklist_id'] ?? null,
                                    'checklist_description' => $stepData['checklist_description'] ?? null,
                                    'user_id' => $stepData['user_id'] ?? null,
                                    'turn_around_time' => $stepData['turn_around_time'] ?? null,
                                    'trigger' => $stepData['trigger'] ?? 0,
                                    'dependency' => $stepData['dependency'] ?? 'NO_DEPENDENCY',
                                    'dependency_steps' => $stepData['dependency'] === 'SELECTED_COMPLETED' ? self::stringToInt($stepData['dependency_steps'] ?? []) : [],
                                    'is_entry_point' => filter_var($stepData['is_entry_point'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 1 : 0,

                                    'maker_escalation_user_id' => $stepData['maker_escalation_user_ids'][0] ?? ($stepData['maker_escalation_user_id'] ?? null),
                                    'maker_escalation_user_ids' => $stepData['maker_escalation_user_ids'] ?? [],
                                    'maker_turn_around_time_day' => $stepData['maker_turn_around_time_day'] ?? null,
                                    'maker_turn_around_time_hour' => $stepData['maker_turn_around_time_hour'] ?? null,
                                    'maker_turn_around_time_minute' => $stepData['maker_turn_around_time_minute'] ?? null,
                                    'maker_escalation_after_day' => $stepData['maker_escalation_after_day'] ?? null,
                                    'maker_escalation_after_hour' => $stepData['maker_escalation_after_hour'] ?? null,
                                    'maker_escalation_after_minute' => $stepData['maker_escalation_after_minute'] ?? null,
                                    'maker_escalation_email_notification' => $stepData['maker_escalation_email_notification'] ?? null,
                                    'maker_escalation_push_notification' => $stepData['maker_escalation_push_notification'] ?? null,
                                    'maker_completion_email_notification' => $stepData['maker_completion_email_notification'] ?? null,
                                    'maker_completion_push_notification' => $stepData['maker_completion_push_notification'] ?? null,
                                    'maker_dependency_email_notification' => $stepData['maker_dependency_email_notification'] ?? null,
                                    'maker_dependency_push_notification' => $stepData['maker_dependency_push_notification'] ?? null,

                                    'checker_id' => $stepData['checker_id'] ?? null,
                                    'checker_turn_around_time_day' => $stepData['checker_turn_around_time_day'] ?? null,
                                    'checker_turn_around_time_hour' => $stepData['checker_turn_around_time_hour'] ?? null,
                                    'checker_turn_around_time_minute' => $stepData['checker_turn_around_time_minute'] ?? null,

                                    'checker_escalation_user_id' => $stepData['checker_escalation_user_ids'][0] ?? ($stepData['checker_escalation_user_id'] ?? null),
                                    'checker_escalation_user_ids' => $stepData['checker_escalation_user_ids'] ?? [],
                                    'checker_escalation_after_day' => $stepData['checker_escalation_after_day'] ?? null,
                                    'checker_escalation_after_hour' => $stepData['checker_escalation_after_hour'] ?? null,
                                    'checker_escalation_after_minute' => $stepData['checker_escalation_after_minute'] ?? null,
                                    'checker_escalation_email_notification' => $stepData['checker_escalation_email_notification'] ?? null,
                                    'checker_escalation_push_notification' => $stepData['checker_escalation_push_notification'] ?? null,
                                ]);

                                $allSteps[] = $stepData['record_id'];
                                $dependencyVariable[] = [
                                    'reference' => $stepData['id'],
                                    'record' => $stepData['record_id']
                                ];
                            } else {
                                $freshNewStep = NewWorkflowAssignmentItem::create([
                                    'new_workflow_assignment_id' => $new_workflow_assignment->id,
                                    'section_id' => $sectionId,
                                    'section_name' => $sectionData['name'],
                                    'section_code' => $sectionData['code'],
                                    'section_description' => $sectionData['description'] ?? null,
                                    'section_order' => $sectionOrder,
                                    'step_order' => $stepOrder,
                                    'step' => $stepData['step'] ?? $stepOrder,
                                    'step_name' => $stepData['step_name'] ?? null,
                                    'department_id' => $stepData['department_id'] ?? null,
                                    'checklist_id' => $stepData['checklist_id'] ?? null,
                                    'checklist_description' => $stepData['checklist_description'] ?? null,
                                    'user_id' => $stepData['user_id'] ?? null,
                                    'turn_around_time' => $stepData['turn_around_time'] ?? null,
                                    'trigger' => $stepData['trigger'] ?? 0,
                                    'dependency' => $stepData['dependency'] ?? 'NO_DEPENDENCY',
                                    'dependency_steps' => $stepData['dependency'] === 'SELECTED_COMPLETED' ? self::stringToInt($stepData['dependency_steps'] ?? []) : [],
                                    'is_entry_point' => filter_var($stepData['is_entry_point'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 1 : 0,

                                    'maker_escalation_user_id' => $stepData['maker_escalation_user_ids'][0] ?? ($stepData['maker_escalation_user_id'] ?? null),
                                    'maker_escalation_user_ids' => $stepData['maker_escalation_user_ids'] ?? [],
                                    'maker_turn_around_time_day' => $stepData['maker_turn_around_time_day'] ?? null,
                                    'maker_turn_around_time_hour' => $stepData['maker_turn_around_time_hour'] ?? null,
                                    'maker_turn_around_time_minute' => $stepData['maker_turn_around_time_minute'] ?? null,
                                    'maker_escalation_after_day' => $stepData['maker_escalation_after_day'] ?? null,
                                    'maker_escalation_after_hour' => $stepData['maker_escalation_after_hour'] ?? null,
                                    'maker_escalation_after_minute' => $stepData['maker_escalation_after_minute'] ?? null,
                                    'maker_escalation_email_notification' => $stepData['maker_escalation_email_notification'] ?? null,
                                    'maker_escalation_push_notification' => $stepData['maker_escalation_push_notification'] ?? null,
                                    'maker_completion_email_notification' => $stepData['maker_completion_email_notification'] ?? null,
                                    'maker_completion_push_notification' => $stepData['maker_completion_push_notification'] ?? null,
                                    'maker_dependency_email_notification' => $stepData['maker_dependency_email_notification'] ?? null,
                                    'maker_dependency_push_notification' => $stepData['maker_dependency_push_notification'] ?? null,

                                    'checker_id' => $stepData['checker_id'] ?? null,
                                    'checker_turn_around_time_day' => $stepData['checker_turn_around_time_day'] ?? null,
                                    'checker_turn_around_time_hour' => $stepData['checker_turn_around_time_hour'] ?? null,
                                    'checker_turn_around_time_minute' => $stepData['checker_turn_around_time_minute'] ?? null,

                                    'checker_escalation_user_id' => $stepData['checker_escalation_user_ids'][0] ?? ($stepData['checker_escalation_user_id'] ?? null),
                                    'checker_escalation_user_ids' => $stepData['checker_escalation_user_ids'] ?? [],
                                    'checker_escalation_after_day' => $stepData['checker_escalation_after_day'] ?? null,
                                    'checker_escalation_after_hour' => $stepData['checker_escalation_after_hour'] ?? null,
                                    'checker_escalation_after_minute' => $stepData['checker_escalation_after_minute'] ?? null,
                                    'checker_escalation_email_notification' => $stepData['checker_escalation_email_notification'] ?? null,
                                    'checker_escalation_push_notification' => $stepData['checker_escalation_push_notification'] ?? null,
                                ])->id;

                                $allSteps[] = $freshNewStep;
                                $dependencyVariable[] = [
                                    'reference' => $stepData['id'],
                                    'record' => $freshNewStep
                                ];
                            }
                        }
                    }
                }
            }

            $dependencyVariable = collect($dependencyVariable);

            if ($dependencyVariable->count() > 0) {
                foreach ($validated['sections'] as $sectionId => $sectionData) {
                    if (isset($sectionData['steps']) && is_array($sectionData['steps'])) {
                        foreach ($sectionData['steps'] as $stepId => $stepData) {
                            if (!empty($stepData['dependency_steps'])) {
                                $origin = $dependencyVariable->where('reference', $stepData['id'])->first()['record'] ?? '';
                                $targets = $dependencyVariable->whereIn('reference', $stepData['dependency_steps'])->pluck('record')->toArray();

                                if (!empty($origin) && !empty($targets) && is_numeric($origin)) {
                                    NewWorkflowAssignmentItem::where('id', $origin)->update([
                                        'dependency_steps' => self::stringToInt($targets)
                                    ]);
                                }
                            }
                        }
                    }
                }
            }

            $itemsToDeleteQuery = NewWorkflowAssignmentItem::where('new_workflow_assignment_id', $new_workflow_assignment->id);
            if (!empty($allSteps)) {
                $itemsToDeleteQuery->whereNotIn('id', $allSteps);
            }
            $deletedItemIds = $itemsToDeleteQuery->pluck('id');
            $itemsToDeleteQuery->delete();

            if ($deletedItemIds->isNotEmpty()) {
                ChecklistTask::where('type', 1)
                    ->whereIn('workflow_checklist_id', $deletedItemIds)
                    ->where('status', 0)
                    ->delete();
            }

            $items = NewWorkflowAssignmentItem::with(['checklist', 'user'])->where('new_workflow_assignment_id', $new_workflow_assignment->id)->orderBy('step_order')->get();
            $currentDate = $new_workflow_assignment->start_from ? $new_workflow_assignment->start_from->copy() : Carbon::now();

            foreach ($items as $item) {
                $days = (int) ($item->maker_turn_around_time_day ?? 0);
                $hours = (int) ($item->maker_turn_around_time_hour ?? 0);

                $task = ChecklistTask::where('type', 1)
                    ->where('workflow_checklist_id', $item->id)
                    ->first();

                if ($task) {
                    if ($task->status == 0) {
                        $task->update(['date' => $currentDate->format('Y-m-d H:i:s')]);
                    }
                } else {
                    $endDate = $currentDate->copy()->addDays($days)->addHours($hours);
                    $hash = md5(json_encode($item->checklist->schema ?? []));

                    $createdTask = ChecklistTask::create([
                        'type' => 1,
                        'workflow_checklist_id' => $item->id,
                        'version_id' => Helper::getFormVersion($item->checklist_id, $hash),
                        'date' => $currentDate->format('Y-m-d H:i:s'),
                        'completed_by' => $endDate->format('Y-m-d H:i:s'),
                        'status' => 0,
                        'code' => 'WF-' . ($item->user->employee_id ?? '') . '-' . $currentDate->format('Y-m-d')
                    ]);

                    \App\Jobs\SendWorkflowTaskStartAlert::dispatch($createdTask, 'maker')->delay($currentDate->subMinutes(30));

                    $currentDate = $endDate;
                }
            }

            return redirect()->route('workflow-assignments.index')->withSuccess('Assignment updated successfully');
        });
    }

    public function show($id)
    {
        $assignment = NewWorkflowAssignment::find(decrypt($id));
        $assignment = $assignment->load('children');

        return view('workflow-assignments.show', compact('assignment'));
    }

    /**
     * Display the tree visualization for a workflow assignment
     */
    public function treeView($id)
    {
        $assignment = NewWorkflowAssignment::find(decrypt($id));
        $assignment = $assignment->load([
            'children' => function ($query) {
                $query->with(['user', 'checker', 'department', 'checklist']);
            }
        ]);

        return view('workflow-assignments.tree', compact('assignment'));
    }

    /**
     * Get tree data in JSON format for D3.js visualization
     */
    public function treeData($id)
    {
        $assignment = NewWorkflowAssignment::find(decrypt($id));

        $root = [
            'name' => $assignment->title,
            'title' => 'Project',
            'className' => 'root-node',
            'children' => []
        ];

        $mainIndex = 1;

        $entryPointIcon = '&nbsp;&nbsp;&nbsp;<img src="' . asset('assets/images/entry-point.png') . '" style="height:20px;" />';

        if ($assignment->sections && is_array($assignment->sections)) {
            $sections = collect($assignment->sections)->sortBy('order');

            foreach ($sections as $section) {
                $steps = $assignment->children->where('section_id', $section['id']);
                $total = $count = 0;
                
                $stepList = [];
                foreach ($steps as $step) {
                    $linkedNodes = NewWorkflowAssignmentItem::selectRaw('step_name as title')->whereIn('id', $step->dependency_steps ?? [])->pluck('title')->toArray();
                    if (!empty($linkedNodes)) {
                        $linkedNodes = '<img src="' . asset('assets/images/link.png') . '" style="height:20px;cursor:pointer;" data-bubbles="' . count($linkedNodes) . '" data-json="' . implode(',', $linkedNodes) . '">';
                    } else {
                        $linkedNodes = '';
                    }

                    $percentage = DB::table('checklist_tasks')->where('workflow_checklist_id', $step->id)->select('percentage')->first();
                    $percentage = isset($percentage->percentage) && is_numeric($percentage->percentage) ? $percentage->percentage : 0;

                    $stepList[] = ($mainIndex . ' . ') . $step->step_name . ' - <strong class="fs-6">' . ($percentage) . '%</strong>' . ' ' . ($step->is_entry_point ? $entryPointIcon : $linkedNodes);
                    $total += $percentage;
                    $count++;
                    $mainIndex++;
                }

                $pr = round($count > 0 ? ($total / $count) : 0);

                $root['children'][] = [
                    'name' => $section['name'] . ' - <strong class="fs-6">' . $pr . '%</strong>',
                    'percentage' => $pr,
                    'title' => $section['description'] ?? '',
                    'steps' => $stepList,
                    'className' => 'section-node'
                ];
            }
        }

        return response()->json($root);
    }

    public function destroy($id)
    {
        $assignment = NewWorkflowAssignment::find(decrypt($id));
        $assignment->children()->delete();
        $assignment->delete();

        return redirect()->route('workflow-assignments.index')->withSuccess('Assignment deleted');
    }

    protected function validateStore($request): array
    {
        $sectionsArray = [];

        if (!empty($request['data']) && is_array($request['data'])) {
            // Preserve section IDs as keys
            foreach ($request['data'] as $sectionId => $sectionData) {
                $sectionsArray[$sectionId] = $sectionData;
            }
        }

        $finalArray = [
            'sections' => $sectionsArray,
            'title' => $request['title'],
            'send_to_all_notification' => $request['send_to_all_notification'],
            'on_going_project' => $request['on_going_project'],
            'description' => $request['description'],
            'status' => $request['status'],
            'new_workflow_template_id' => $request['new_workflow_template_id'] ?? null,
            'start_from' => $request['start_from'] ?? null,
        ];

        $rules = [
            'title' => 'required|string|max:255',
            'send_to_all_notification' => 'nullable',
            'on_going_project' => 'nullable',
            'description' => 'nullable|string',
            'new_workflow_template_id' => 'nullable|exists:new_workflow_templates,id',
            'start_from' => 'nullable|date',
            'sections' => 'required|array|min:1',
            'sections.*.name' => 'required|string|max:255',
            'sections.*.code' => 'required|string|max:50',
            'sections.*.description' => 'nullable|string|max:500',
            'sections.*.steps' => 'required|array|min:1',
            'sections.*.steps.*.step_name' => 'required|string|max:255',
            'sections.*.steps.*.department_id' => 'nullable|exists:departments,id',
            'sections.*.steps.*.checklist_id' => 'nullable|exists:dynamic_forms,id',
            'sections.*.steps.*.checklist_description' => 'nullable|string',
            'sections.*.steps.*.user_id' => 'nullable|exists:users,id',
            'sections.*.steps.*.trigger' => 'required|in:0,1',
            'sections.*.steps.*.dependency' => 'required|in:ALL_COMPLETED,NO_DEPENDENCY,SELECTED_COMPLETED',
            'sections.*.steps.*.dependency_steps' => 'array',
            'sections.*.steps.*.is_entry_point' => 'nullable',
            'sections.*.steps.*.record_id' => 'nullable|integer',

            'sections.*.steps.*.maker_escalation_user_id' => 'nullable|exists:users,id',
            'sections.*.steps.*.maker_escalation_user_ids' => 'nullable|array',
            'sections.*.steps.*.maker_escalation_user_ids.*' => 'exists:users,id',
            'sections.*.steps.*.maker_turn_around_time_day' => 'nullable|numeric|min:0',
            'sections.*.steps.*.maker_turn_around_time_hour' => 'nullable|numeric|min:0|max:23',
            'sections.*.steps.*.maker_turn_around_time_minute' => 'nullable|numeric|min:0|max:59',
            'sections.*.steps.*.maker_escalation_after_day' => 'nullable|numeric|min:0',
            'sections.*.steps.*.maker_escalation_after_hour' => 'nullable|numeric|min:0|max:23',
            'sections.*.steps.*.maker_escalation_after_minute' => 'nullable|numeric|min:0|max:59',
            'sections.*.steps.*.maker_escalation_email_notification' => 'nullable|exists:notification_templates,id',
            'sections.*.steps.*.maker_escalation_push_notification' => 'nullable|exists:notification_templates,id',
            'sections.*.steps.*.maker_completion_email_notification' => 'nullable|exists:notification_templates,id',
            'sections.*.steps.*.maker_completion_push_notification' => 'nullable|exists:notification_templates,id',
            'sections.*.steps.*.maker_dependency_email_notification' => 'nullable|exists:notification_templates,id',
            'sections.*.steps.*.maker_dependency_push_notification' => 'nullable|exists:notification_templates,id',

            'sections.*.steps.*.checker_id' => 'nullable|exists:users,id',
            'sections.*.steps.*.checker_turn_around_time_day' => 'nullable|numeric|min:0',
            'sections.*.steps.*.checker_turn_around_time_hour' => 'nullable|numeric|min:0|max:23',
            'sections.*.steps.*.checker_turn_around_time_minute' => 'nullable|numeric|min:0|max:59',

            'sections.*.steps.*.checker_escalation_user_id' => 'nullable|exists:users,id',
            'sections.*.steps.*.checker_escalation_user_ids' => 'nullable|array',
            'sections.*.steps.*.checker_escalation_user_ids.*' => 'exists:users,id',
            'sections.*.steps.*.checker_escalation_after_day' => 'nullable|numeric|min:0',
            'sections.*.steps.*.checker_escalation_after_hour' => 'nullable|numeric|min:0|max:23',
            'sections.*.steps.*.checker_escalation_after_minute' => 'nullable|numeric|min:0|max:59',
            'sections.*.steps.*.checker_escalation_email_notification' => 'nullable|exists:notification_templates,id',
            'sections.*.steps.*.checker_escalation_push_notification' => 'nullable|exists:notification_templates,id',
        ];

        $request = new Request($finalArray);
        $validated = $request->validate($rules);

        if (!empty($validated['sections'])) {
            foreach ($validated['sections'] as $sectionId => $sectionData) {
                if (isset($sectionData['steps']) && is_array($sectionData['steps'])) {
                    foreach ($sectionData['steps'] as $stepId => $stepData) {
                        $isEntryPoint = filter_var($stepData['is_entry_point'] ?? false, FILTER_VALIDATE_BOOLEAN);

                        if ($isEntryPoint) {
                            $validated['sections'][$sectionId]['steps'][$stepId]['dependency_steps'] = [];
                            $validated['sections'][$sectionId]['steps'][$stepId]['dependency'] = 'NO_DEPENDENCY';
                        } elseif (($stepData['dependency'] ?? 'NO_DEPENDENCY') === 'SELECTED_COMPLETED') {
                            $validated['sections'][$sectionId]['steps'][$stepId]['dependency_steps'] = self::stringToInt(array_values(array_unique(array_filter($stepData['dependency_steps'] ?? []))));
                        } else {
                            $validated['sections'][$sectionId]['steps'][$stepId]['dependency_steps'] = [];
                        }
                    }
                }
            }
        }

        return $validated;
    }

    public static function stringToInt($array)
    {
        return array_map('intval', $array);
    }

    public function workflowAssignmentList(Request $request) {
        $queryString = trim($request->searchQuery);
        $page = $request->input('page', 1);
        $type = $request->type;
        $limit = 10;
        $getAll = $request->getall;
    
        $query = NewWorkflowAssignment::latest();
    
        if (!empty($queryString)) {
            $query->where('title', 'LIKE', "%{$queryString}%");
        }


        $data = $query->paginate($limit, ['*'], 'page', $page);
        $response = $data->map(function ($item) {
            return [
                'id' => $item->id,
                'text' => $item->title
            ];
        });        
    
        if ($getAll && $page == 1) {
            $response->prepend(['id' => 'all', 'text' => 'All']);
        }

        return response()->json([
            'items' => $response,
            'pagination' => [
                'more' => $data->hasMorePages()
            ]
        ]);
    }

    public function exportPdf(Request $request, $id) {
        if ($request->regenerate == 1) {
            return self::testPdf($request, $id);
        } else {
            if (!(file_exists(storage_path("app/public/task-pdf/task-{$id}.pdf")) && is_file(storage_path("app/public/task-pdf/task-{$id}.pdf")))) {
                abort(404, 'You have to request for task report generation');
                return false;
            }

            $path = storage_path("app/public/task-pdf/task-{$id}.pdf");

            return response()->file($path, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="task-' . $id . '.pdf"'
            ]);
        }
    }

    public function exportCompressedPdf(Request $request, $id) {
        $path = storage_path("app/public/task-pdf/task-compressed-{$id}.pdf");

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="task-' . $id . '.pdf"'
        ]);
    }

    public static function testPdf(Request $request, $id) {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '-1');

        $task = ChecklistTask::with(['wf.checklist', 'wf.parent'])->find($id);
        $path = storage_path('app/public/task-pdf');

        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        // Generate PDF
            $json = $task->data ?? [];
            if (is_string($json)) {
                $data = json_decode($json, true);
            } else if (is_array($json)) {
                $data = $json;
            } else {
                $data = [];
            }
            
            $groupedData = [];

            foreach ($data as $item) {
                if (!isset($groupedData[$item->className])) {
                    $groupedData[$item->className][] = $item->label;
                }

                $groupedData[$item->className][] = property_exists($item, 'value_label') ? (!empty($item->value_label) ? $item->value_label : $item->value) : $item->value;
            }

            $groupedData = array_values($groupedData);

            $varients = Helper::categorizePoints($task->data ?? []);

            $total = count(Helper::selectPointsQuestions($task->data));
            $toBeCounted = $total - count($varients['na']);

            $failed = abs(count(array_column($varients['negative'], 'value')));
            $achieved = $toBeCounted - abs($failed);
            
            if ($failed <= 0) {
                $achieved = array_sum(array_column($varients['positive'], 'value'));
            }
            
            if ($toBeCounted > 0) {
                $percentage = number_format(($achieved / $toBeCounted) * 100, 2);
            } else {
                $percentage = 0;
            }

            $finalResultData = [];

            $finalResultData['total_count'] = $total;
            $finalResultData['passed'] = $achieved;
            $finalResultData['failed'] = count($varients['negative']);
            $finalResultData['na'] = count($varients['na']);
            $finalResultData['percentage'] = "{$percentage}%";
            $finalResultData['final_result'] = $percentage > 80 ? "Pass" : "Fail";

            $formToPass = Helper::getVersionForm($task->version_id);
            if (!Helper::isPointChecklist($formToPass)) {
                $toBeCounted = collect($task->data)->flatten(1)->pluck('className')->filter()->unique()->count();
            }

        if ($request->type == 'html') {
            return view('workflow-tasks.pdf', ['data' => $groupedData, 'task' => $task, 'toBeCounted' => $toBeCounted, 'finalResultData' => $finalResultData]);
        } else {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('workflow-tasks.pdf', ['data' => $groupedData, 'task' => $task, 'toBeCounted' => $toBeCounted, 'finalResultData' => $finalResultData])
            ->setPaper('A4', 'landscape');

            return $pdf->stream("task-{$task->id}.pdf");
        }
    }

    public function exportExcel(Request $request, $id) {
        $task = ChecklistTask::find( $id);

        $json = $task->data ?? [];
        if (is_string($json)) {
            $data = json_decode($json, true);
        } else if (is_array($json)) {
            $data = $json;
        } else {
            $data = [];
        }
        
        $siteUrl = url('storage/workflow-task-uploads') . '/';

        foreach ($data as $item) {
            if (!empty($item->isFile)) {
                if (is_array($item->value)) {
                    $item->value = array_map(function ($v) use ($siteUrl) {
                        return $siteUrl . ltrim($v, '/');
                    }, $item->value);
                } elseif (is_string($item->value)) {
                    $item->value = $siteUrl . ltrim($item->value, '/');
                }
            }
        }

        $groupedData = [];
        foreach ($data as $item) {
            if (!isset($groupedData[$item->className])) {
                $groupedData[$item->className][] = $item->label;
            }

            $groupedData[$item->className][] = property_exists($item, 'value_label') ? (!empty($item->value_label) ? $item->value_label : $item->value) : $item->value;
        }

        $groupedData = array_values($groupedData);
        $fileName = $task->code . '-' . (date('m-d-Y', strtotime($task->date))) . ".xlsx";

        $varients = Helper::categorizePoints($task->data ?? []);

        $total = count(Helper::selectPointsQuestions($task->data));
        $toBeCounted = $total - count($varients['na']);

        $failed = abs(count(array_column($varients['negative'], 'value')));
        $achieved = $toBeCounted - abs($failed);
        
        if ($failed <= 0) {
            $achieved = array_sum(array_column($varients['positive'], 'value'));
        }
        
        if ($toBeCounted > 0) {
            $percentage = number_format(($achieved / $toBeCounted) * 100, 2);
        } else {
            $percentage = 0;
        }

        $groupedData[] = ["Total Questions", $total];
        $groupedData[] = ["Passed", $achieved];
        $groupedData[] = ["Failed", count($varients['negative'])];
        $groupedData[] = ["N/A", count($varients['na'])];
        $groupedData[] = ["Percentage", "{$percentage}%"];
        $groupedData[] = ["Final Result", $percentage > 80 ? "Pass" : "Fail"];

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\TaskExport($groupedData, $task), $fileName);
    }

    public function tableView(Request $request, $id) {
        if ($request->ajax()) {


            $allEmployees = User::selectRaw("id, CONCAT(COALESCE(employee_id, ''), ' - ', COALESCE(name, ''), ' ', COALESCE(middle_name, ''), ' ', COALESCE(last_name, '')) as name")
            ->pluck('name', 'id')->toArray();

            $currentUser = auth()->user()->id;
            $thisUserRoles = auth()->user()->roles()->pluck('id')->toArray();

            $checklistScheduling = ChecklistTask::query()
            ->whereHas('wf', function ($builder) use ($id) {
                $builder->where('new_workflow_assignment_id', decrypt($id));
            })
            ->when(!in_array(Helper::$roles['admin'], $thisUserRoles), function ($builder) use ($currentUser) {
                $builder->where(function ($innerBuilder) use ($currentUser) {
                    $innerBuilder->orWhereHas('wf', function ($innerBuilder2) use ($currentUser) {
                        $innerBuilder2->where('user_id', $currentUser);
                    });
                });
            })
            ->when(!empty($request->locs), function ($builder) {
                return $builder->whereHas('wf', function ($innerBuilder) {
                    $innerBuilder->whereIn('section_code', request('locs'));
                });
            })
            ->when(!empty($request->user), function ($builder) {
                return $builder->whereHas('wf', function ($innerBuilder) {
                    $innerBuilder->whereIn('user_id', request('user'));
                });
            })
            ->when(!empty($request->from), function ($builder) {
                return $builder->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), '>=', date('Y-m-d', strtotime(request('from'))));
            })
            ->when(!empty($request->to), function ($builder) {
                return $builder->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), '<=', date('Y-m-d', strtotime(request('to'))));
            })
            ->when($request->status === '0' || in_array($request->status, range(1, 6)), function ($builder) {
                if (request('status') === '0' || request('status') === '1') {
                    return $builder->where('status', request('status'));
                } else if (request('status') === '5') {
                    return $builder->where('status', 3)
                    ->whereHas('wf', function ($innerBuilder) {
                        $innerBuilder->whereNotNull('checker_id');
                    });
                } else if (request('status') === '6') {
                    return $builder->where('status', 3)
                    ->whereHas('wf', function ($innerBuilder) {
                        $innerBuilder->whereNull('checker_id');
                    });
                } else {
                    if (request('status') === '2') {
                        return $builder->where('status', 2)
                        ->whereDoesntHave('redos', function ($innerBuilder) {
                            $innerBuilder->whereIn('status', [0, 1]);
                        });
                    } else if (request('status') === '3') {
                        return $builder->where('status', 2)
                        ->whereDoesntHave('redos', function ($innerBuilder) {
                            $innerBuilder->where('status', 1);
                        })
                        ->whereHas('redos', function ($innerBuilder) {
                            $innerBuilder->where('status', 0);
                        });
                    } else {
                        return $builder->where('status', 2)
                        ->whereHas('redos', function ($innerBuilder) {
                            $innerBuilder->where('status', 1);
                        });
                    }
                }
            })

            ->where('type', 1)
            ->workflow()
            ->orderBy('date', 'ASC');

            return datatables()
            ->eloquent($checklistScheduling)
            ->editColumn('task_date', function ($row) {
                return date('d-m-Y H:i', strtotime($row->date));
            })
            ->editColumn('task_status', function ($row) use ($thisUserRoles, $currentUser) {
                if ($row->cancelled) {
                    return '<span class="badge bg-danger">Cancelled</span>';
                }

                if ((in_array(Helper::$roles['admin'], $thisUserRoles) || $row->checker_id == $currentUser)) {
                    if (in_array($row->status, [2, 3])) {

                        $html = '';

                        if ($row->status == 0) {
                            $html .= '<span class="badge bg-warning">Pending</span>';
                        } else if ($row->status == 1) {
                            $html .= '<span class="badge bg-info">In-Progress</span>';
                        } else if ($row->status == 2) {
                            if (isset($row->wf->checker_id)) {
                                if ($row->redos()->count() == 0) {
                                    $html .= '<span class="badge bg-secondary">Pending Verification</span>';
                                } else if ($row->redos()->where('status', 1)->count() == 0) {
                                    $html .= '<span class="badge bg-secondary">Reassigned</span>';
                                } else {
                                    $html .= '<span class="badge bg-secondary">Verifying</span>';
                                }
                            } else {
                                $html .= '<span class="badge bg-success">Completed</span>';
                            }
                        } else {
                            if (isset($row->wf->checker_id)) {
                                $html .= '<span class="badge bg-success">Verified</span>';
                            } else {
                                $html .= '<span class="badge bg-success">Completed</span>';
                            }
                        }

                        if ($row->status != 3) {
                            $html .= "<br><br><select class='me-2 change-status' data-id='".$row->id."' data-last-selected='".$row->status."'>
                            <option value='2' ".($row->status == 2 ? 'selected' : '').">Pending Verification</option>
                            <option value='3' ".($row->status == 3 ? 'selected' : '').">Verified</option>
                            </select>";
                        }

                        return $html;

                    } else {
                        if ($row->status == 0) {
                            return '<span class="badge bg-warning">Pending</span>';
                        } else if ($row->status == 1) {
                            return '<span class="badge bg-info">In-Progress</span>';
                        } else if ($row->status == 2) {
                            if (isset($row->wf->checker_id)) {
                                if ($row->redos()->count() == 0) {
                                    return '<span class="badge bg-secondary">Pending Verification</span>';
                                } else if ($row->redos()->where('status', 1)->count() == 0) {
                                    return '<span class="badge bg-secondary">Reassigned</span>';
                                } else {
                                    return '<span class="badge bg-secondary">Verifying</span>';
                                }
                            } else {
                                return '<span class="badge bg-success">Completed</span>';
                            }
                        } else {
                            if (isset($row->wf->checker_id)) {
                                return '<span class="badge bg-success">Verified</span>';
                            } else {
                                return '<span class="badge bg-success">Completed</span>';
                            }
                        }
                    }
                    
                } else {
                    if ($row->status == 0) {
                        return '<span class="badge bg-warning">Pending</span>';
                    } else if ($row->status == 1) {
                        return '<span class="badge bg-info">In-Progress</span>';
                    } else if ($row->status == 2) {
                        if (isset($row->wf->checker_id)) {
                            if ($row->redos()->count() == 0) {
                                return '<span class="badge bg-secondary">Pending Verification</span>';
                            } else if ($row->redos()->where('status', 1)->count() == 0) {
                                return '<span class="badge bg-secondary">Reassigned</span>';
                            } else {
                                return '<span class="badge bg-secondary">Verifying</span>';
                            }
                        } else {
                            return '<span class="badge bg-success">Completed</span>';
                        }
                    } else {
                        if (isset($row->wf->checker_id)) {
                            return '<span class="badge bg-success">Verified</span>';
                        } else {
                            return '<span class="badge bg-success">Completed</span>';
                        }
                    }
                }
            })
            ->addColumn('action', function ($row) use ($thisUserRoles, $currentUser) {
                return '<a class="btn btn-secondary" href="' . route('workflow-tasks.index') . '?redirect_task_id=' . encrypt($row->id) . '"> View </a>';
            })
            ->editColumn('code', function ($row) use ($currentUser) {
                $html = $row->code;

                if (isset($row->wf) && $row->wf->checker_id == $currentUser) {
                    $html .= " <br/> <span class='badge bg-warning'> To Check </span>";
                }

                if (isset($row->restasks[0]) && $row->restasks[0]->status === 0) {
                    $html .= " <br/> <span class='badge bg-primary'> Rescheduling Requested </span>";
                }

                return $html;
            })
            ->addColumn('workflow_name', function ($row)  {
                return isset($row->wf->parent->title) ? $row->wf->parent->title : '';
            })
            ->addColumn('task_percentage', function ($row)  {
                return round($row->percentage) . '%';
            })
            ->addColumn('department_name', function ($row) {
                return $row->wf->section_name ?? '';
            })
            ->addColumn('task_name', function ($row) {
                return $row->wf->step_name ?? '';
            })
            ->addColumn('user_name', function ($row) use ($allEmployees) {
                return isset($row->wf->user_id) && isset($allEmployees[$row->wf->user_id]) ? $allEmployees[$row->wf->user_id] : '';
            })
            ->addIndexColumn()
            ->rawColumns(['action', 'task_status', 'code'])
            ->toJson();
        }

        $id = decrypt($id);
        $assignment = NewWorkflowAssignment::find($id);
        $assignment = $assignment->load([
            'children' => function ($query) {
                $query->with(['user', 'checker', 'department', 'checklist']);
            }
        ]);

        $allDepartments = $assignment->children()->groupBy('section_code')->pluck('section_name', 'section_code')->toArray();

        return view('workflow-assignments.table', compact('assignment', 'allDepartments'));
    }

    public function dashboard(Request $request, $id) {
        $id = decrypt($id);
        $assignment = NewWorkflowAssignment::find($id);
        $assignment = $assignment->load([
            'children' => function ($query) {
                $query->with(['user', 'checker', 'department', 'checklist']);
            }
        ]);

        $allDepartments = $assignment->children()->groupBy('section_code')->pluck('section_name', 'section_code')->toArray();

        return view('workflow-assignments.dashboard', compact('assignment', 'allDepartments'));
    }
}

