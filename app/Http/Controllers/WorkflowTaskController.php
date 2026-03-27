<?php

namespace App\Http\Controllers;

use App\Models\ChecklistScheduling;
use App\Models\RescheduledTask;
use App\Models\SubmissionTime;
use App\Models\ChecklistTask;
use Illuminate\Http\Request;
use App\Models\DynamicForm;
use Illuminate\Support\Arr;
use App\Models\RedoAction;
use App\Helpers\Helper;
use App\Models\Store;
use App\Models\User;

class WorkflowTaskController extends Controller
{
    public function index(Request $request)
    {
        ini_set('memory_limit', '-1');

        if ($request->ajax()) {

            $allStoreName = \App\Models\Department::selectRaw("id, name")->pluck('name', 'id')->toArray();
            $allCTemplateName = DynamicForm::selectRaw("id, name")->pluck('name', 'id')->toArray();
            $allEmployees = User::whereHas('roles', function ($builder) {
                $builder->whereIn('id', [Helper::$roles['store-phone'], Helper::$roles['store-manager'], Helper::$roles['store-employee'], 
                Helper::$roles['store-cashier'], Helper::$roles['divisional-operations-manager'], Helper::$roles['admin'], Helper::$roles['operations-manager']
            ]);
            })
            ->selectRaw("id, CONCAT(COALESCE(employee_id, ''), ' - ', COALESCE(name, ''), ' ', COALESCE(middle_name, ''), ' ', COALESCE(last_name, '')) as name")
            ->pluck('name', 'id')->toArray();

            $currentUser = auth()->user()->id;
            $thisUserRoles = auth()->user()->roles()->pluck('id')->toArray();

            $checklistScheduling = ChecklistTask::query()
            ->where('type', 1)
            ->when(!in_array(Helper::$roles['admin'], $thisUserRoles), function ($builder) use ($currentUser) {
                $builder->where(function ($innerBuilder) use ($currentUser) {
                    $innerBuilder->orWhereHas('wf', function ($innerBuilder2) use ($currentUser) {
                        $innerBuilder2->where('user_id', $currentUser);
                    });
                });
            })
            ->when(!empty($request->locs), function ($builder) {
                return $builder->whereHas('wf', function ($innerBuilder) {
                    $innerBuilder->whereIn('department_id', request('locs'));
                });
            })
            ->when(!empty($request->workflow), function ($builder) {
                return $builder->whereHas('wf', function ($innerBuilder) {
                    $innerBuilder->whereIn('new_workflow_assignment_id', request('workflow'));
                });
            })
            ->when(!empty($request->user), function ($builder) {
                return $builder->whereHas('wf', function ($innerBuilder) {
                    $innerBuilder->whereIn('user_id', request('user'));
                });
            })
            ->when(!empty($request->checker), function ($builder) {
                return $builder->whereHas('wf', function ($innerBuilder) {
                    $innerBuilder->whereIn('checker_id', request('checker'));
                });
            })
            ->when(!empty($request->checklist), function ($builder) {
                return $builder->whereHas('wf', function ($innerBuilder) {
                    return $innerBuilder->whereIn('checklist_id', request('checklist'));
                });
            })
            ->when(!session()->has('redirect_task_id') && !empty($request->from), function ($builder) {
                return $builder->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), '>=', date('Y-m-d', strtotime(request('from'))));
            })
            ->when(!session()->has('redirect_task_id') && !empty($request->to), function ($builder) {
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
            ->when(session()->has('redirect_task_id'), function ($row) {
                $row->where('id', decrypt(session()->get('redirect_task_id')));
                session()->pull('redirect_task_id');
            })
            ->workflow()
            ->orderBy('id', 'DESC');

            return datatables()
            ->eloquent($checklistScheduling)
            ->addColumn('checklist_name', function ($row) use ($allCTemplateName) {
                return isset($row->wf->checklist_id) && isset($allCTemplateName[$row->wf->checklist_id]) ? $allCTemplateName[$row->wf->checklist_id] : '';
            })
            ->editColumn('date', function ($row) {
                return date('d-m-Y H:i', strtotime($row->date));
            })
            ->editColumn('status', function ($row) use ($thisUserRoles, $currentUser) {
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
                $dropdownItems = '';
            
                if (in_array($row->status, [1, 2, 3]) && !empty($row->data)) {
            
                    if (auth()->user()->can('scheduled-tasks.show')) {
                        $dropdownItems .= '<li><a class="dropdown-item" href="'.route('checklists-submission-comparison', encrypt($row->id)).'">Compare</a></li>';

                        $dropdownItems .= '<li><a class="dropdown-item" href="'.route('checklists-submission-view-for-maker', encrypt($row->id)).'">Data</a></li>';
                        
                        if (isset($row->parent->parent->checker_user_id) && $row->parent->parent->checker_user_id == auth()->user()->id) {
                            $dropdownItems .= '<li><a class="dropdown-item" href="'.route('checklists-submission-view-for-checker', encrypt($row->id)).'">Check</a></li>';
                        }
                    }
            
                    if (auth()->user()->can('workflow-task-export-excel')) {
                        $dropdownItems .= '<li><a class="dropdown-item" href="'.route("task-export-excel", $row->id).'">Export Excel</a></li>';
                    }
            
                    if (auth()->user()->can('workflow-task-export-pdf')) {
                        $dropdownItems .= '<li><a class="dropdown-item" href="'.route("task-export-pdf", $row->id).'">Export PDF</a></li>';
                    }
            
                    if (auth()->user()->can('task-log')) {
                        $dropdownItems .= '<li><a class="dropdown-item" href="'.route("task-log", encrypt($row->id)).'">Logs</a></li>';
                    }

                    $dropdownItems .= '<li><a class="dropdown-item text-primary" href="' . route("workflow-task-export-pdf", $row->id) . '?regenerate=1' . '"> Regenerate PDF </a></li>';
                }
            
                if ($dropdownItems) {
                    $action = '
                    <div class="dropdown">
                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Actions
                        </button>
                        <ul class="dropdown-menu">
                            '.$dropdownItems.'
                        </ul>
                    </div>';
                } else {
                    $action = '-';
                }
            
                return $action;
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
            ->addColumn('store_name', function ($row) use ($allStoreName) {
                return isset($row->wf->department_id) && isset($allStoreName[$row->wf->department_id]) ? $allStoreName[$row->wf->department_id] : '';
            })
            ->addColumn('workflow_name', function ($row) use ($allStoreName) {
                return isset($row->wf->parent->title) ? $row->wf->parent->title : '';
            })
            ->addColumn('user_name', function ($row) use ($allEmployees) {
                return isset($row->wf->user_id) && isset($allEmployees[$row->wf->user_id]) ? $allEmployees[$row->wf->user_id] : '';
            })
            ->addColumn('checker_user_name', function ($row) use ($allEmployees) {
                return isset($row->wf->checker_id) && isset($allEmployees[$row->wf->checker_id]) ? $allEmployees[$row->wf->checker_id] : '';
            })
            ->addColumn('section_name', function ($row) {
                return $row->wf->section_name ?? '';
            })
            ->addColumn('step_name', function ($row) {
                return $row->wf->step_name ?? '';
            })
            ->rawColumns(['action', 'status', 'code'])
            ->toJson();
        }

        if (request()->has('redirect_task_id')) {
            session()->put('redirect_task_id', request('redirect_task_id'));
            return redirect()->route('workflow-tasks.index');
        }

        $page_title = 'Workflow Tasks';
        $page_description = 'Manage workflow tasks here';
        return view('workflow-tasks.index',compact('page_title', 'page_description'));
    }

    public function destroy($id)
    {
        $id = decrypt($id);
        $task = ChecklistTask::where('id', $id);

        if ($task) {
            $task->delete();
            return redirect()->route('workflow-tasks.index')->with('success', 'Task deleted successfully');
        }

        return redirect()->route('workflow-tasks.index')->with('error', 'Task not found');
    }

    public function submission(Request $request, $id)
    {
        $task = ChecklistTask::where('id', decrypt($id));

        if ($task) {
            if ($request->method() == 'POST') {
                if (empty($task->data)) {
                    $task->data = json_decode($request->data, true);
                    $task->status = Helper::$status['completed'];
                    $task->save();

                    return redirect()->route('submission-response', ['submission_response' => 'success']);
                } else {
                    return redirect()->route('submission-response', ['submission_response' => 'failed', 'already_submitted' => 1]);
                }
            }
    
            return view('workflow-tasks.submission', compact('task', 'id'));
        }

        return redirect()->route('submission-response', ['submission_response' => 'failed']);
    }

    public function submissionView(Request $request, $id) {
        $decId = decrypt($id);
        $task = ChecklistTask::find($decId);

        if ($task) {
            if (isset($task->wf->checker_id) && $task->wf->checker_id == auth()->user()->id) {
                $redoActionData = RedoAction::where('task_id', $decId)->get()->keyBy('field_id')->toArray();
                return view('workflow-tasks.submission-checker', compact('task', 'id', 'redoActionData'));
            }

            return view('workflow-tasks.submission-view', compact('task', 'id'));
        }

        return redirect()->route('submission-response', ['submission_response' => 'failed']);
    }

    public function sideBySideComparison(Request $request, $id) {
        $decId = decrypt($id);
        $task = ChecklistTask::find($decId);

        if ($task) {
            return view('workflow-tasks.submission-compare', compact('task', 'id'));
        }

        return redirect()->route('submission-response', ['submission_response' => 'failed']);
    }

    public function submissionViewForMaker(Request $request, $id) {
        $decId = decrypt($id);
        $task = ChecklistTask::find($decId);

        if ($task) {
            return view('workflow-tasks.submission-view', compact('task', 'id'));
        }

        return redirect()->route('submission-response', ['submission_response' => 'failed']);
    }

    public function submissionViewForChecker(Request $request, $id) {
        $decId = decrypt($id);
        $task = ChecklistTask::find($decId);

        if ($task) {
            $redoActionData = RedoAction::where('task_id', $decId)->get()->keyBy('field_id')->toArray();
            return view('workflow-tasks.submission-checker', compact('task', 'id', 'redoActionData'));
        }

        return redirect()->route('submission-response', ['submission_response' => 'failed']);
    }

    public function truthyFalsyFields(Request $request) {
        
        $task = ChecklistTask::find($request->task_id);
        $flaggedItems = Helper::getBooleanFields($task->data)[in_array($request->type, ['truthy', 'falsy']) ? $request->type : 'falsy'];

        $groupedData = [];
        foreach ($flaggedItems as $item) {
            $groupedData[$item['className']][] = (object)$item;
        }

        $formToPass = Helper::getVersionForm($task->version_id);
        $isPointChecklist = Helper::isPointChecklist($formToPass);

        return response()->json(['status' => true, 'html' => view('tasks.truthy-falsy', compact('flaggedItems', 'task', 'groupedData', 'isPointChecklist'))->render()]);
    }

    public function verifyEachFields(Request $request, $id) {
        //RedoAction
        $id = decrypt($id);
        $task = ChecklistTask::find($id);

        if (empty($request->justify_field)) {
            return redirect()->route('workflow-tasks.index')->with('success', 'Updated successfully');
        }

        \DB::beginTransaction();

        try {
            $json = $task->data;

            foreach ($request->justify_field as $index => $value) {
                if ($value == 'approve') {
                    foreach ($json as &$item) {
                        if (isset($item->className) && $item->className === $index) {
                            $item->approved = 'yes';
                        }
                    }
                } else if ($value == 'decline') {
                    foreach ($json as &$item) {
                        if (isset($item->className) && $item->className === $index) {
                            $item->approved = 'no';
                        }
                    }

                    $redoActionExists = RedoAction::where('task_id', $id)
                    ->where('field_id', $index);

                    $tempArr = isset($request->action[$index]) ? (array)json_decode($request->action[$index]) : [];

                    if ($redoActionExists->exists()) {
                        $redoActionExists->update([
                            'title' => isset($tempArr['title']) ? $tempArr['title'] : '',
                            'remarks' => isset($tempArr['remark']) ? $tempArr['remark'] : '',
                            'status' => 0,
                            'start_at' => isset($tempArr['start']) ? date('Y-m-d H:i:s', strtotime($tempArr['start'])) : '',
                            'completed_by' => isset($tempArr['end']) ? date('Y-m-d H:i:s', strtotime($tempArr['end'])) : '',
                            'do_not_allow_late_submission' => isset($tempArr['lsub']) ? $tempArr['lsub'] : 0
                        ]);
                    } else {
                        RedoAction::create([
                            'task_id' => $id,
                            'field_id' => $index,
                            'title' => isset($tempArr['title']) ? $tempArr['title'] : '',
                            'remarks' => isset($tempArr['remark']) ? $tempArr['remark'] : '',
                            'start_at' => isset($tempArr['start']) ? date('Y-m-d H:i:s', strtotime($tempArr['start'])) : '',
                            'completed_by' => isset($tempArr['end']) ? date('Y-m-d H:i:s', strtotime($tempArr['end'])) : '',
                            'do_not_allow_late_submission' => isset($tempArr['lsub']) ? $tempArr['lsub'] : 0
                        ]);
                    }
                }
            }

            $task->data = $json;
            $task->save();

            //generating from notification center
            // \App\Jobs\GenerateOptimizedTaskPdf::dispatch($task->id);

            \DB::commit();
            return redirect()->back()->with('success', 'Updated successfully');

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('CHECKER VERIFICATION: ' . $e->getMessage() . ' ON LINE ' . $e->getLine());
            return redirect()->back()->with('error', 'Something went wrong');
        }
    }

    public function changeStatus(Request $request) {
        $order = ChecklistTask::find($request->id);
        $order->status = $request->status;
        $order->save();

        //generating from notification center
        // \App\Jobs\GenerateOptimizedTaskPdf::dispatch($order->id);

        return response()->json(['status' => true, 'message' => 'Status updated successfully']);
    }

    public function reassignmentView(Request $request, $id) {
        $task = RedoAction::with(['task'])->where('task_id', decrypt($id))->first();
        $allData = RedoAction::where('task_id', decrypt($id))->get()->keyBy('field_id')->toArray();
        $allClass = array_keys($allData);

        return view('reassignments.show', compact('task', 'allClass', 'allData'));
    }

    public function fetchTaskDataToCompare(Request $request) {
        $task = ChecklistTask::find($request->current);
        $tasks = empty($request->tasks) ? [] : $request->tasks;
        array_unshift($tasks, $request->current);

        if (count($tasks) > 3) {
            $tasks = array_slice($tasks, 0, 3);
        }

        return response()->json(['status' => true, 'html' => view('tasks.compare', compact('tasks', 'task'))->render()]);
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        ChecklistTask::whereIn('id', $ids)->delete();
        SubmissionTime::whereIn('task_id', $ids)->delete();

        return response()->json(['status' => true]);
    }

    public function reschedule(Request $request, $encryptedId)
    {
        $id = decrypt($encryptedId);

        $last = RescheduledTask::where('task_id', $id)->latest()->first();

        if ($last && $last->status === 0) {
            return response()->json(['status' => false, 'message' => 'Rescheduling approval is already in pending.']);
        }

        $resDate = date('Y-m-d H:i:s', strtotime($request->date));
        $mainTask = ChecklistTask::find($id);

        RescheduledTask::create([
            'task_id' => $id,
            'remarks' => $request->remark,
            'date' => $resDate,
            'task_date' => $mainTask->date ?? null
        ]);

        $task = ChecklistTask::find($id);
        \App\Jobs\NotificationRescheduleRequest::dispatch($task, $resDate);

        return response()->json(['status' => true]);
    }

    public function update(Request $request, $encryptedId)
    {
        $id = decrypt($encryptedId);

        ChecklistTask::where('id', $id)->update([
            'date' => date('Y-m-d H:i:s', strtotime($request->date))
        ]);

        return response()->json(['status' => true]);
    }

    public function taskVersioning(Request $request) {
        if ($request->method() == 'POST') {

        } else {

            if ($request->ajax()) {
                $tasks = ChecklistTask::scheduling()
                ->where('status', 0)
                ->where(function ($builder) {
                    $builder->whereNull('version_id')
                    ->orWhere('version_id', '');
                });

                return datatables()
                ->eloquent($tasks)
                ->addColumn('action', function ($row) {
                    $action = '';
                    $action .= '<a href="'.route('reassignments.show', encrypt($row->id)).'" class="btn btn-info btn-sm me-2"> Data </a>';
                    return $action;
                })
                ->rawColumns(['action'])
                ->toJson();
            } else {
                return view('tasks.version');
            }
        }
    }
}