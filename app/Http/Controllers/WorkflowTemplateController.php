<?php

namespace App\Http\Controllers;

use App\Models\NewWorkflowAssignmentItem;
use App\Models\NewWorkflowTemplateItem;
use App\Models\NewWorkflowTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\DynamicForm;
use App\Models\Department;
use App\Models\User;
use App\Imports\WorkflowTemplateArrayImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class WorkflowTemplateController extends Controller
{
    public function index(Request $request)
    {
        /** @var User|null $currentUser */
        $currentUser = auth()->user();

        if ($request->ajax()) {

            $checklistScheduling = NewWorkflowTemplate::latest();

            return datatables()
                ->eloquent($checklistScheduling)
                ->editColumn('status', function ($row) {
                    if ($row->status) {
                        return '<span class="badge bg-success"> Active </span>';
                    } else {
                        return '<span class="badge bg-danger"> InActive </span>';
                    }
                })
                ->addColumn('action', function ($row) use ($currentUser) {
                    $action = '';

                    if ($currentUser && $currentUser->can('workflow-templates.show')) {
                        $action .= '<a href="' . route("workflow-templates.show", encrypt($row->id)) . '" class="btn btn-warning btn-sm me-2"> Show </a>';
                        $action .= '<a href="' . route("workflow-templates.tree", encrypt($row->id)) . '" class="btn btn-primary btn-sm me-2" title="View Tree"><i class="bi bi-diagram-3"></i></a>';
                    }

                    if ($currentUser && $currentUser->can('workflow-templates.edit')) {
                        $action .= '<a href="' . route('workflow-templates.edit', encrypt($row->id)) . '" class="btn btn-info btn-sm me-2">Edit</a>';
                        $action .= '<a href="' . route('workflow-templates.duplicate', encrypt($row->id)) . '" class="btn btn-success btn-sm me-2">Duplicate</a>';
                    }

                    if ($currentUser && $currentUser->can('workflow-templates.destroy')) {
                        $action .= '<form method="POST" action="' . route("workflow-templates.destroy", encrypt($row->id)) . '" style="display:inline;"><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="' . csrf_token() . '"><button type="submit" class="btn btn-danger btn-sm deleteGroup">Delete</button></form>';
                    }

                    return $action;
                })
                ->addColumn('stepscnt', function ($row) {
                    return $row->children()->count();
                })
                ->rawColumns(['action', 'status'])
                ->toJson();
        }

        $page_title = 'Project Template';
        $page_description = 'Manage project template here';
        return view('workflow-templates.index', compact('page_title', 'page_description'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get(['id', 'name']);
        $users = User::orderBy('name')->get(['id', 'name']);
        $checklists = DynamicForm::orderBy('name')->get(['id', 'name']);
        return view('workflow-templates.create', compact('departments', 'users', 'checklists'));
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

            $template = NewWorkflowTemplate::create([
                'title' => $validated['title'],
                'status' => request('status') == 1 ? 1 : 0,
                'description' => $validated['description'] ?? null,
                'sections' => $sectionsData,
                'added_by' => auth()->user()->id,
                'send_to_all_notification' => isset($validated['send_to_all_notification']) && $validated['send_to_all_notification'] == 'true' ? 1 : 0
            ]);

            $dependencyVariable = [];

            if (isset($validated['sections'])) {
                foreach ($validated['sections'] as $sectionId => $sectionData) {
                    if (isset($sectionData['steps']) && is_array($sectionData['steps'])) {
                        $sectionOrder = array_search($sectionId, array_keys($validated['sections'])) + 1;

                        foreach ($sectionData['steps'] as $stepId => $stepData) {
                            $stepOrder = array_search($stepId, array_keys($sectionData['steps'])) + 1;
                            $stepNumber = $stepData['step'] ?? $stepOrder;

                            $newStep = NewWorkflowTemplateItem::create([
                                'new_workflow_template_id' => $template->id,
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

                $dependencyVariable = collect($dependencyVariable);

                if ($dependencyVariable->count() > 0) {
                    foreach ($validated['sections'] as $sectionId => $sectionData) {
                        if (isset($sectionData['steps']) && is_array($sectionData['steps'])) {
                            foreach ($sectionData['steps'] as $stepId => $stepData) {
                                if (!empty($stepData['dependency_steps'])) {
                                    $origin = $dependencyVariable->where('reference', $stepData['id'])->first()['record'] ?? '';
                                    $targets = $dependencyVariable->whereIn('reference', $stepData['dependency_steps'])->pluck('record')->toArray();

                                    if (!empty($origin) && !empty($targets) && is_numeric($origin)) {
                                        NewWorkflowTemplateItem::where('id', $origin)->update([
                                            'dependency_steps' => self::stringToInt($targets)
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }

            }

            return redirect()->route('workflow-templates.index')->withSuccess('Template created successfully');
        });
    }

    public function edit($id)
    {
        $template = NewWorkflowTemplate::find(decrypt($id));
        $template = $template->load('children');

        return view('workflow-templates.edit', compact('template'));
    }

    public function update(Request $request, $id)
    {
        $validated = $this->validateStore($request);

        $new_workflow_template = NewWorkflowTemplate::find($id);

        return DB::transaction(function () use ($validated, $new_workflow_template) {
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

            $new_workflow_template->update([
                'title' => $validated['title'],
                'status' => request('status') == 1 ? 1 : 0,
                'description' => $validated['description'] ?? null,
                'sections' => $sectionsData,
                'send_to_all_notification' => isset($validated['send_to_all_notification']) && $validated['send_to_all_notification'] == 'true' ? 1 : 0
            ]);

            $allSteps = [];
            $dependencyVariable = [];
            
            if (isset($validated['sections'])) {
                foreach ($validated['sections'] as $sectionId => $sectionData) {
                    if (isset($sectionData['steps']) && is_array($sectionData['steps'])) {
                        $sectionOrder = array_search($sectionId, array_keys($validated['sections'])) + 1;

                        foreach ($sectionData['steps'] as $stepId => $stepData) {
                            $stepOrder = array_search($stepId, array_keys($sectionData['steps'])) + 1;

                            if (isset($stepData['record_id']) && is_numeric($stepData['record_id']) && $stepData['record_id'] > 0 && NewWorkflowTemplateItem::where('id', $stepData['record_id'])->where('new_workflow_template_id', $new_workflow_template->id)->exists()) {
                                NewWorkflowTemplateItem::where('id', $stepData['record_id'])->update([
                                    'new_workflow_template_id' => $new_workflow_template->id,
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
                                $freshNewStep = NewWorkflowTemplateItem::create([
                                    'new_workflow_template_id' => $new_workflow_template->id,
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
                                    NewWorkflowTemplateItem::where('id', $origin)->update([
                                        'dependency_steps' => self::stringToInt($targets)
                                    ]);
                                }
                            }
                        }
                    }
                }
            }

            if (!empty($allSteps)) {
                NewWorkflowTemplateItem::where('new_workflow_template_id', $new_workflow_template->id)->whereNotIn('id', $allSteps)->delete();
            } else {
                NewWorkflowTemplateItem::where('new_workflow_template_id', $new_workflow_template->id)->delete();
            }

            return redirect()->route('workflow-templates.index')->withSuccess('Template updated successfully');
        });
    }

    public function show($id)
    {
        $template = NewWorkflowTemplate::find(decrypt($id));
        $template = $template->load('children');

        return view('workflow-templates.show', compact('template'));
    }

    /**
     * Display the tree visualization for a workflow template
     */
    public function treeView($id)
    {
        $template = NewWorkflowTemplate::find(decrypt($id));
        $template = $template->load([
            'children' => function ($query) {
                $query->with(['user', 'checker', 'department', 'checklist']);
            }
        ]);

        return view('workflow-templates.tree', compact('template'));
    }

    /**
     * Get tree data in JSON format for D3.js visualization
     */
    public function treeData($id)
    {
        $assignment = NewWorkflowTemplate::find(decrypt($id));

        $root = [
            'name' => $assignment->title,
            'title' => 'Project',
            'className' => 'root-node',
            'children' => []
        ];

        $entryPointIcon = '&nbsp;&nbsp;&nbsp;<img src="' . asset('assets/images/entry-point.png') . '" style="height:20px;" />';

        $mainIndex = 1;

        if ($assignment->sections && is_array($assignment->sections)) {
            $sections = collect($assignment->sections)->sortBy('order');

            foreach ($sections as $section) {
                $steps = $assignment->children->where('section_id', $section['id']);
                $total = $count = 0;
                
                $stepList = [];
                foreach ($steps as $step) {
                    $linkedNodes = NewWorkflowTemplateItem::selectRaw('step_name as title')->whereIn('id', $step->dependency_steps ?? [])->pluck('title')->toArray();
                    if (!empty($linkedNodes)) {
                        $linkedNodes = '<img src="' . asset('assets/images/link.png') . '" style="height:20px;cursor:pointer;" data-bubbles="' . count($linkedNodes) . '" data-json="' . implode(',', $linkedNodes) . '">';
                    } else {
                        $linkedNodes = '';
                    }

                    $stepList[] = ($mainIndex . ' . ') . $step->step_name . ' ' . ($step->is_entry_point ? $entryPointIcon : $linkedNodes);
                    $mainIndex++;
                }

                $pr = round($count > 0 ? ($total / $count) : 0);

                $root['children'][] = [
                    'name' => $section['name'],
                    'percentage' => '',
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
        $template = NewWorkflowTemplate::find(decrypt($id));
        $template->children()->delete();
        $template->delete();

        return redirect()->route('workflow-templates.index')->withSuccess('Template deleted');
    }

    protected function validateStore($request, $saveType = null): array
    {
        $sectionsArray = [];

        if (!empty($request['data']) && is_array($request['data'])) {
            foreach ($request['data'] as $sectionId => $sectionData) {
                $sectionsArray[$sectionId] = $sectionData;
            }
        }

        $finalArray = [
            'sections' => $sectionsArray,
            'title' => $request['title'],
            'send_to_all_notification' => $request['send_to_all_notification'],
            'description' => $request['description'],
            'status' => $request['status']
        ];

        $userColumn = 'id';

        $rules = [
            'title' => 'required|string|max:255',
            'send_to_all_notification' => 'nullable',
            'description' => 'nullable|string',
            'sections' => 'required|array|min:1',
            'sections.*.name' => 'required|string|max:255',
            'sections.*.code' => 'required|string|max:50',
            'sections.*.description' => 'nullable|string|max:500',
            'sections.*.steps' => 'required|array|min:1',
            'sections.*.steps.*.step_name' => 'required|string|max:255',
            'sections.*.steps.*.department_id' => 'nullable|exists:departments,id',
            'sections.*.steps.*.checklist_id' => 'nullable|exists:dynamic_forms,id',
            'sections.*.steps.*.checklist_description' => 'nullable|string',
            'sections.*.steps.*.user_id' => "nullable|exists:users,{$userColumn}",
            'sections.*.steps.*.trigger' => 'required|in:0,1',
            'sections.*.steps.*.dependency' => 'required|in:ALL_COMPLETED,NO_DEPENDENCY,SELECTED_COMPLETED',
            'sections.*.steps.*.dependency_steps' => 'array',
            'sections.*.steps.*.is_entry_point' => 'nullable',
            'sections.*.steps.*.record_id' => 'nullable|integer',

            'sections.*.steps.*.maker_escalation_user_id' => "nullable|exists:users,{$userColumn}",
            'sections.*.steps.*.maker_escalation_user_ids' => 'nullable|array',
            'sections.*.steps.*.maker_escalation_user_ids.*' => "exists:users,{$userColumn}",
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

            'sections.*.steps.*.checker_id' => "nullable|exists:users,{$userColumn}",
            'sections.*.steps.*.checker_turn_around_time_day' => 'nullable|numeric|min:0',
            'sections.*.steps.*.checker_turn_around_time_hour' => 'nullable|numeric|min:0|max:23',
            'sections.*.steps.*.checker_turn_around_time_minute' => 'nullable|numeric|min:0|max:59',

            'sections.*.steps.*.checker_escalation_user_id' => "nullable|exists:users,{$userColumn}",
            'sections.*.steps.*.checker_escalation_user_ids' => 'nullable|array',
            'sections.*.steps.*.checker_escalation_user_ids.*' => "exists:users,{$userColumn}",
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

    public function templateLists(Request $request)
    {
        $queryString = trim($request->searchQuery);
        $page = $request->input('page', 1);
        $limit = 10;

        $query = NewWorkflowTemplate::where('status', 1);

        if (!empty($queryString)) {
            $query->where('title', 'LIKE', "%{$queryString}%");
        }

        $data = $query->paginate($limit, ['*'], 'page', $page);

        return response()->json([
            'items' => collect($data->items())->map(function ($item) {
                return [
                    'id' => $item->id,
                    'text' => $item->title
                ];
            }),
            'pagination' => [
                'more' => $data->hasMorePages()
            ]
        ]);
    }

    public static function stringToInt($array)
    {
        return array_map('intval', $array);
    }

    /**
     * Download sample xlsx template for workflow import.
     */
    public function downloadTemplate()
    {
        $headings = [
            'TEMPLATE TITLE',
            'TEMPLATE DESCRIPTION',
            'STATUS (1=ACTIVE,0=INACTIVE)',
            'DEPARTMENT CODE',
            'DEPARTMENT NAME',
            'DEPARTMENT DESCRIPTION',
            'TASK NUMBER',
            'TASK NAME',
            'SYSTEM DEPARTMENT NAME',
            'CHECKLIST NAME',
            'CHECKLIST DESCRIPTION',
            'MAKER EMPLOYEE ID',
            'TRIGGER (0=AUTO,1=MANUAL)',
            'DEPENDENCY (NO_DEPENDENCY,SELECTED_COMPLETED)',
            'DEPENDENCY TASKS (COMMA SEPARATED TASK NUMBERS)',
            'IS ENTRY POINT (1=YES,0=NO)',
            'CHECKER EMPLOYEE ID',
            'MAKER TAT DAYS',
            'MAKER TAT HOURS',
            'CHECKER TAT DAYS',
            'CHECKER TAT HOURS',
            'MAKER ESCALATION USER ID',
            'MAKER ESCALATION AFTER DAYS',
            'MAKER ESCALATION AFTER HOURS',
            'CHECKER ESCALATION USER ID',
            'CHECKER ESCALATION AFTER DAYS',
            'CHECKER ESCALATION AFTER HOURS',
        ];

        $sample = [
            [
                'New Customer Onboarding',
                'Default Project for onboarding a new customer',
                1,
                'SEC-001',
                'Initial Checks',
                'All base validations',
                1,
                'Collect documents',
                'Operations',
                null,
                'Upload ID proofs',
                'DOM001',
                0,
                'NO_DEPENDENCY',
                '',
                1,
                8,
                1,
                0,
                0,
                0,
                null,
                null,
                null,
                null,
                null,
                null,
            ],
            [
                'New Customer Onboarding',
                'Default project for onboarding a new customer',
                1,
                'SEC-002',
                'Approval',
                'Maker/Checker approvals',
                2,
                'Approve documents',
                'Risk',
                null,
                'Approve or reject documents',
                'USR100',
                1,
                'SELECTED_COMPLETED',
                '1',
                0,
                9,
                0,
                12,
                0,
                4,
                null,
                null,
                null,
                null,
                null,
                null,
            ],
        ];

        $export = new class($sample, $headings) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings {
            private array $rows;
            private array $headings;

            public function __construct(array $rows, array $headings)
            {
                $this->rows = $rows;
                $this->headings = $headings;
            }

            public function array(): array
            {
                return $this->rows;
            }

            public function headings(): array
            {
                return $this->headings;
            }
        };

        return Excel::download($export, 'workflow-template-import.xlsx');
    }

    /**
     * Import workflow template from uploaded xlsx file.
     */
    public function import(Request $request)
    {
        $request->validate([
            'xlsx' => 'required|file|mimes:xlsx,xls',
        ]);

        $data = Excel::toArray(new WorkflowTemplateArrayImport(), $request->file('xlsx'));

        $expectedHeaders = [
            'TEMPLATE TITLE',
            'TEMPLATE DESCRIPTION',
            'STATUS (1=ACTIVE,0=INACTIVE)',
            'DEPARTMENT CODE',
            'DEPARTMENT NAME',
            'DEPARTMENT DESCRIPTION',
            'TASK NUMBER',
            'TASK NAME',
            'SYSTEM DEPARTMENT NAME',
            'CHECKLIST NAME',
            'CHECKLIST DESCRIPTION',
            'MAKER EMPLOYEE ID',
            'TRIGGER (0=AUTO,1=MANUAL)',
            'DEPENDENCY (ALL_COMPLETED,ANY_COMPLETED,SELECTED_COMPLETED)',
            'DEPENDENCY TASKS (COMMA SEPARATED TASK NUMBERS)',
            'IS ENTRY POINT (1=YES,0=NO)',
            'CHECKER EMPLOYEE ID',
            'MAKER TAT DAYS',
            'MAKER TAT HOURS',
            'CHECKER TAT DAYS',
            'CHECKER TAT HOURS',
            'MAKER ESCALATION USER ID',
            'MAKER ESCALATION AFTER DAYS',
            'MAKER ESCALATION AFTER HOURS',
            'CHECKER ESCALATION USER ID',
            'CHECKER ESCALATION AFTER DAYS',
            'CHECKER ESCALATION AFTER HOURS',
        ];

        if (empty($data) || empty($data[0])) {
            return back()->withErrors(['xlsx' => 'File is empty or unreadable.']);
        }

        $sheet = $data[0];
        $headerRow = $sheet[0] ?? [];

        if (!$this->headersAreValid($headerRow, $expectedHeaders)) {
            return back()->withErrors(['xlsx' => 'Uploaded file headers do not match the template. Please download the latest template.']);
        }

        $sections = [];
        $duplicateStepCheck = [];
        $templateTitle = null;
        $templateDescription = null;
        $status = 1;

        foreach ($sheet as $rowIndex => $row) {
            if ($rowIndex === 0 || $this->isRowEmpty($row)) {
                continue;
            }

            $currentRow = $rowIndex + 1; // Human readable index

            if ($templateTitle === null) {
                $templateTitle = trim((string) ($row[0] ?? ''));
                $templateDescription = trim((string) ($row[1] ?? ''));
                $status = isset($row[2]) && (int) $row[2] === 0 ? 0 : 1;
            }

            if (empty($templateTitle)) {
                return back()->withErrors(['xlsx' => "Template title is required at row {$currentRow}."]);
            }

            $sectionCode = trim((string) ($row[3] ?? ''));
            $sectionName = trim((string) ($row[4] ?? ''));

            if (empty($sectionCode) || empty($sectionName)) {
                return back()->withErrors(['xlsx' => "Section code and name are required at row {$currentRow}."]);
            }

            if (!isset($sections[$sectionCode])) {
                $sections[$sectionCode] = [
                    'name' => $sectionName,
                    'code' => $sectionCode,
                    'description' => trim((string) ($row[5] ?? '')),
                    'steps' => []
                ];
            }

            $stepName = trim((string) ($row[7] ?? ''));
            if (empty($stepName)) {
                return back()->withErrors(['xlsx' => "Task name is required at row {$currentRow}."]);
            }

            $dependency = strtoupper(trim((string) ($row[13] ?? 'NO_DEPENDENCY')));
            if (!in_array($dependency, ['NO_DEPENDENCY', 'SELECTED_COMPLETED'])) {
                $dependency = 'NO_DEPENDENCY';
            }

            $dependencySteps = [];
            if (!empty($row[14])) {
                $dependencySteps = array_values(array_filter(array_map('intval', preg_split('/,/', (string) $row[14]))));
            }

            $stepNumber = !empty($row[6]) ? (int) $row[6] : count($sections[$sectionCode]['steps']) + 1;
            $dupKey = $sectionCode . '|' . $stepNumber;
            if (isset($duplicateStepCheck[$dupKey])) {
                return back()->withErrors(['xlsx' => "Duplicate task number {$stepNumber} found in section {$sectionCode} (row {$currentRow})."]);
            }
            $duplicateStepCheck[$dupKey] = true;

            // Department by name
            $departmentName = trim((string) ($row[8] ?? ''));
            $departmentId = null;
            if ($departmentName !== '') {
                $department = Department::where(DB::raw('LOWER(name)'), strtolower($departmentName))->first();
                if (!$department) {
                    return back()->withErrors(['xlsx' => "Department '{$departmentName}' not found (row {$currentRow})."]);
                }
                $departmentId = $department->id;
            }

            // Checklist by name
            $checklistName = trim((string) ($row[9] ?? ''));
            $checklistId = null;
            if ($checklistName !== '') {
                $checklist = DynamicForm::where(DB::raw('LOWER(name)'), strtolower($checklistName))->first();
                if (!$checklist) {
                    return back()->withErrors(['xlsx' => "Checklist '{$checklistName}' not found (row {$currentRow})."]);
                }
                $checklistId = $checklist->id;
            }

            // Users by employee_id
            $makerEmployeeId = trim((string) ($row[11] ?? ''));
            $makerId = null;
            if ($makerEmployeeId !== '') {
                $maker = User::where('employee_id', $makerEmployeeId)->first();
                if (!$maker) {
                    return back()->withErrors(['xlsx' => "Maker employee id '{$makerEmployeeId}' not found (row {$currentRow})."]);
                }
                $makerId = $maker->id;
            }

            $checkerEmployeeId = trim((string) ($row[16] ?? ''));
            $checkerId = null;
            if ($checkerEmployeeId !== '') {
                $checker = User::where('employee_id', $checkerEmployeeId)->first();
                if (!$checker) {
                    return back()->withErrors(['xlsx' => "Checker employee id '{$checkerEmployeeId}' not found (row {$currentRow})."]);
                }
                $checkerId = $checker->id;
            }

            $makerEscalationEmployeeId = trim((string) ($row[21] ?? ''));
            $makerEscalationId = null;
            if ($makerEscalationEmployeeId !== '') {
                $makerEscalation = User::where('employee_id', $makerEscalationEmployeeId)->first();
                if (!$makerEscalation) {
                    return back()->withErrors(['xlsx' => "Maker escalation employee id '{$makerEscalationEmployeeId}' not found (row {$currentRow})."]);
                }
                $makerEscalationId = $makerEscalation->id;
            }

            $checkerEscalationEmployeeId = trim((string) ($row[24] ?? ''));
            $checkerEscalationId = null;
            if ($checkerEscalationEmployeeId !== '') {
                $checkerEscalation = User::where('employee_id', $checkerEscalationEmployeeId)->first();
                if (!$checkerEscalation) {
                    return back()->withErrors(['xlsx' => "Checker escalation employee id '{$checkerEscalationEmployeeId}' not found (row {$currentRow})."]);
                }
                $checkerEscalationId = $checkerEscalation->id;
            }

            $sections[$sectionCode]['steps'][] = [
                'step' => $stepNumber,
                'step_name' => $stepName,
                'department_id' => $departmentId,
                'checklist_id' => $checklistId,
                'checklist_description' => $row[10] ?? null,
                'user_id' => $makerId,
                'trigger' => in_array((int) ($row[12] ?? 0), [0, 1], true) ? (int) $row[12] : 0,
                'dependency' => $dependency,
                'dependency_steps' => $dependencySteps,
                'is_entry_point' => (int) ($row[15] ?? 0) === 1,
                'checker_id' => $checkerId,
                'maker_turn_around_time_day' => $row[17] !== null && $row[17] !== '' ? (int) $row[17] : null,
                'maker_turn_around_time_hour' => $row[18] !== null && $row[18] !== '' ? (int) $row[18] : null,
                'checker_turn_around_time_day' => $row[19] !== null && $row[19] !== '' ? (int) $row[19] : null,
                'checker_turn_around_time_hour' => $row[20] !== null && $row[20] !== '' ? (int) $row[20] : null,
                'maker_escalation_user_id' => $makerEscalationId,
                'maker_escalation_after_day' => $row[22] !== null && $row[22] !== '' ? (int) $row[22] : null,
                'maker_escalation_after_hour' => $row[23] !== null && $row[23] !== '' ? (int) $row[23] : null,
                'checker_escalation_user_id' => $checkerEscalationId,
                'checker_escalation_after_day' => $row[25] !== null && $row[25] !== '' ? (int) $row[25] : null,
                'checker_escalation_after_hour' => $row[26] !== null && $row[26] !== '' ? (int) $row[26] : null,
            ];
        }

        if (empty($sections)) {
            return back()->withErrors(['xlsx' => 'No data rows were found in the uploaded file.']);
        }

        $validated = $this->validateStore([
            'title' => $templateTitle,
            'description' => $templateDescription,
            'status' => $status,
            'data' => $sections,
        ], 'excel');
        $validated['status'] = $status;

        try {
            $this->createTemplateFromValidated($validated);
        } catch (\Throwable $th) {
            Log::error('Workflow template import failed: ' . $th->getMessage(), ['line' => $th->getLine()]);
            return back()->withErrors(['xlsx' => 'Something went wrong while importing the Project template.']);
        }

        return redirect()->route('workflow-templates.index')->withSuccess('Project template imported successfully.');
    }

    private function headersAreValid(array $headerRow, array $expectedHeaders): bool
    {
        if (count($headerRow) !== count($expectedHeaders)) {
            return false;
        }

        foreach ($expectedHeaders as $index => $header) {
            if (strtoupper(trim((string) ($headerRow[$index] ?? ''))) !== $header) {
                return false;
            }
        }

        return true;
    }

    private function isRowEmpty(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * Create template & items from already validated data.
     */
    private function createTemplateFromValidated(array $validated): void
    {
        DB::transaction(function () use ($validated) {
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

            $template = NewWorkflowTemplate::create([
                'title' => $validated['title'],
                'status' => isset($validated['status']) && (int) $validated['status'] === 1 ? 1 : 0,
                'description' => $validated['description'] ?? null,
                'sections' => $sectionsData,
                'added_by' => auth()->user()->id,
            ]);

            $dependencyVariable = [];

            if (isset($validated['sections'])) {
                foreach ($validated['sections'] as $sectionId => $sectionData) {
                    if (isset($sectionData['steps']) && is_array($sectionData['steps'])) {
                        $sectionOrder = array_search($sectionId, array_keys($validated['sections'])) + 1;

                        foreach ($sectionData['steps'] as $stepId => $stepData) {
                            $stepOrder = array_search($stepId, array_keys($sectionData['steps'])) + 1;
                            $stepNumber = $stepData['step'] ?? $stepOrder;

                            $newStep = NewWorkflowTemplateItem::create([
                                'new_workflow_template_id' => $template->id,
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

                                'maker_escalation_user_id' => $stepData['maker_escalation_user_id'] ?? null,
                                'maker_turn_around_time_day' => $stepData['maker_turn_around_time_day'] ?? null,
                                'maker_turn_around_time_hour' => $stepData['maker_turn_around_time_hour'] ?? null,
                                'maker_escalation_after_day' => $stepData['maker_escalation_after_day'] ?? null,
                                'maker_escalation_after_hour' => $stepData['maker_escalation_after_hour'] ?? null,
                                'maker_escalation_email_notification' => $stepData['maker_escalation_email_notification'] ?? null,
                                'maker_escalation_push_notification' => $stepData['maker_escalation_push_notification'] ?? null,

                                'checker_id' => $stepData['checker_id'] ?? null,
                                'checker_turn_around_time_day' => $stepData['checker_turn_around_time_day'] ?? null,
                                'checker_turn_around_time_hour' => $stepData['checker_turn_around_time_hour'] ?? null,

                                'checker_escalation_user_id' => $stepData['checker_escalation_user_id'] ?? null,
                                'checker_escalation_after_day' => $stepData['checker_escalation_after_day'] ?? null,
                                'checker_escalation_after_hour' => $stepData['checker_escalation_after_hour'] ?? null,
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
                                    NewWorkflowTemplateItem::where('id', $origin)->update([
                                        'dependency_steps' => $targets
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
        });
    }

    public function duplicate(Request $request, $id = null) {
        if ($request->ajax()) {

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

                $template = NewWorkflowTemplate::create([
                    'title' => $validated['title'],
                    'status' => request('status') == 1 ? 1 : 0,
                    'description' => $validated['description'] ?? null,
                    'sections' => $sectionsData,
                    'added_by' => auth()->user()->id,
                    'send_to_all_notification' => isset($validated['send_to_all_notification']) && $validated['send_to_all_notification'] == 'true' ? 1 : 0
                ]);

                $dependencyVariable = [];

                if (isset($validated['sections'])) {
                    foreach ($validated['sections'] as $sectionId => $sectionData) {
                        if (isset($sectionData['steps']) && is_array($sectionData['steps'])) {
                            $sectionOrder = array_search($sectionId, array_keys($validated['sections'])) + 1;

                            foreach ($sectionData['steps'] as $stepId => $stepData) {
                                $stepOrder = array_search($stepId, array_keys($sectionData['steps'])) + 1;
                                $stepNumber = $stepData['step'] ?? $stepOrder;

                                $newStep = NewWorkflowTemplateItem::create([
                                    'new_workflow_template_id' => $template->id,
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

                    $dependencyVariable = collect($dependencyVariable);

                    if ($dependencyVariable->count() > 0) {
                        foreach ($validated['sections'] as $sectionId => $sectionData) {
                            if (isset($sectionData['steps']) && is_array($sectionData['steps'])) {
                                foreach ($sectionData['steps'] as $stepId => $stepData) {
                                    if (!empty($stepData['dependency_steps'])) {
                                        $origin = $dependencyVariable->where('reference', $stepData['id'])->first()['record'] ?? '';
                                        $targets = $dependencyVariable->whereIn('reference', $stepData['dependency_steps'])->pluck('record')->toArray();

                                        if (!empty($origin) && !empty($targets) && is_numeric($origin)) {
                                            NewWorkflowTemplateItem::where('id', $origin)->update([
                                                'dependency_steps' => self::stringToInt($targets)
                                            ]);
                                        }
                                    }
                                }
                            }
                        }
                    }

                }

                return redirect()->route('workflow-templates.index')->withSuccess('Template duplicated successfully');
            });
        } else {
            $template = NewWorkflowTemplate::find(decrypt($id));
            $template = $template->load('children');

            return view('workflow-templates.duplicate', compact('template'));
        }
    }
}
