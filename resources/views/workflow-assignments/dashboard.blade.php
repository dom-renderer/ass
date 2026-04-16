@extends('layouts.app-master')

@push('css')
<style>
    :root {
        --brand-green: #065E2E;
        --deep-green: #3C4D48;
        --soft-sage: #84A19F;
        --warm-light: #ECEAE2;
        --off-white: #F8F9FA;
        --ink: #060606;
    }

    body { font-family: 'Inter', sans-serif; background-color: var(--off-white); color: var(--ink); }
    
    .bg-brand-green { background-color: var(--brand-green) !important; }
    .bg-deep-green { background-color: var(--deep-green) !important; }
    .bg-warm-light { background-color: var(--warm-light) !important; }
    .text-brand-green { color: var(--brand-green) !important; }
    .text-soft-sage { color: var(--soft-sage) !important; }
    .text-ink { color: var(--ink) !important; }

    .nav-link-custom { color: white; opacity: 0.8; transition: 0.3s; padding: 0.5rem 1rem; border-radius: 0.375rem; text-decoration: none; display: flex; align-items: center; gap: 8px; }
    .nav-link-custom:hover { opacity: 1; background: rgba(6, 94, 46, 0.4); }
    .nav-link-custom.active { background: rgba(6, 94, 46, 0.3); opacity: 1; }

    .card { border-radius: 12px; border: 1px solid rgba(0,0,0,0.1); }
    .btn-brand { background-color: var(--brand-green); color: white; border: none; transition: transform 0.2s; }
    .btn-brand:hover { background-color: #044d26; color: white; transform: translateY(-1px); }
    
    .progress { height: 12px; border-radius: 10px; background-color: #e9ecef; }
    .progress-bar-gradient { background: linear-gradient(90deg, var(--brand-green), #10B981); }
    .status-pulse { animation: pulse 2s infinite cubic-bezier(0.4, 0, 0.6, 1); }
    @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }

    .activity-line { position: absolute; left: 16px; top: 0; bottom: 0; width: 2px; background: #dee2e6; z-index: 1; }
    .activity-item { position: relative; padding-left: 45px; z-index: 2; }
    .activity-icon { position: absolute; left: 0; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }

    .accordion-button:not(.collapsed) { background-color: var(--warm-light); color: var(--ink); box-shadow: none; }
    .accordion-button::after { display: none; }
    .bg-success-subtle {background-color: #d1e7dd;}
    .bg-danger-subtle {background-color: #ffe6e9;}
</style>
@endpush

@section('content')

<section class="bg-white border-bottom p-4">
    <div class="row align-items-start g-4">
        <div class="col-lg-8">
            <div class="d-flex flex-column flex-sm-row align-items-start gap-4">
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-3 mb-2 flex-wrap">
                        <h1 class="h2 fw-bold mb-0"> {{ $assignment->title }} </h1>
                        <span class="badge rounded-pill @if($assignment->status) bg-success-subtle text-success border border-success @else bg-danger-subtle text-danger border border-danger @endif px-3 py-2">{{ $assignment->status ? 'Published' : 'Unpublished' }}</span>
                    </div>
                    <p class="text-muted mb-3">{{ $assignment->description }}</p>
                    <div class="d-flex flex-wrap gap-4 small text-muted">
                        <span><i class="bi bi-person-circle me-1"></i> Created by <strong class="text-ink">{{ isset($assignment->usr->id) ? ($assignment->usr->employee_id . ' - ' . $assignment->usr->name . ' ' . $assignment->usr->middle_name . ' ' . $assignment->usr->last_name) : 'N/A' }}</strong></span>
                        <span><i class="bi bi-calendar-check me-1"></i> Last updated <strong class="text-ink"> {{ $assignment->updated_at ? \Carbon\Carbon::parse($assignment->updated_at)->diffForHumans() : 'N/A' }} </strong> </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 d-flex flex-wrap justify-content-lg-end gap-2">
            <a href="{{ route("workflow-assignments.tree", encrypt($assignment->id)) }}" class="btn btn-brand px-4 shadow"><i class="bi bi-diagram-3 me-2"></i> Tree View </a>
            <a href="{{ route("workflow-assignments.table", encrypt($assignment->id)) }}" class="btn btn-brand px-4 shadow"><i class="bi bi-table me-2"></i> Table View </a>
        </div>
    </div>
</section>
@php
    $mainTotalTasks = $mainCopmletedTasks = $mainCompletionPercentage = $mainInprogress = $mainPending = 0;
    foreach ($assignment->sections as $section) {
        $mainInprogress += \App\Models\ChecklistTask::whereHas('wf', function ($builder) use ($assignment, $section) {
            return $builder->where('new_workflow_assignment_id', $assignment->id)
            ->where('section_id', $section['id']);
        })->whereIn('status', [1])->count();
        $mainPending += \App\Models\ChecklistTask::whereHas('wf', function ($builder) use ($assignment, $section) {
            return $builder->where('new_workflow_assignment_id', $assignment->id)
            ->where('section_id', $section['id']);
        })->whereIn('status', [0])->count();
        $mainTotalTasks += \App\Models\NewworkflowAssignmentItem::where('new_workflow_assignment_id', $assignment->id)->where('section_id', $section['id'])->count();
        $mainCopmletedTasks += \App\Models\ChecklistTask::whereHas('wf', function ($builder) use ($assignment, $section) {
            return $builder->where('new_workflow_assignment_id', $assignment->id)
            ->where('section_id', $section['id']);
        })->whereIn('status', [2, 3])->count();
    }

    $mainCompletionPercentage = $mainTotalTasks > 0
            ? round(($mainCopmletedTasks / $mainTotalTasks) * 100, 2)
            : 0;

    $result = \App\Models\NewWorkflowAssignmentItem::where('new_workflow_assignment_id', $assignment->id)->selectRaw('
        FLOOR(AVG(
            (COALESCE(maker_turn_around_time_day,0) * 1440) +
            (COALESCE(maker_turn_around_time_hour,0) * 60) +
            COALESCE(maker_turn_around_time_minute,0)
        ) / 1440) as avg_days,

        FLOOR(
            (AVG(
                (COALESCE(maker_turn_around_time_day,0) * 1440) +
                (COALESCE(maker_turn_around_time_hour,0) * 60) +
                COALESCE(maker_turn_around_time_minute,0)
            ) % 1440) / 60
        ) as avg_hours,

        FLOOR(
            AVG(
                (COALESCE(maker_turn_around_time_day,0) * 1440) +
                (COALESCE(maker_turn_around_time_hour,0) * 60) +
                COALESCE(maker_turn_around_time_minute,0)
            ) % 60
        ) as avg_minutes
    ')->first();

$parts = [];

if ($result->avg_days > 0) {
    $parts[] = $result->avg_days . ' ' . ($result->avg_days == 1 ? 'Day' : 'Days');
}

if ($result->avg_hours > 0) {
    $parts[] = $result->avg_hours . ' ' . ($result->avg_hours == 1 ? 'Hour' : 'Hours');
}

if ($result->avg_minutes > 0 || empty($parts)) {
    $parts[] = $result->avg_minutes . ' ' . ($result->avg_minutes == 1 ? 'Minute' : 'Minutes');
}

$averageTurnaroundTime = implode(' ', $parts);

$childTaskTotalCount = $assignment->children()->count();

$childTaskBreachCount = $assignment->children()
    ->whereHas('task', function ($taskQuery) {
        $taskQuery
            ->whereNotNull('completion_date')
            ->whereColumn('completion_date', '>', 'completed_by');
    })
    ->count();

$childTaskBreachPercentage = $childTaskTotalCount > 0
    ? ($childTaskBreachCount / $childTaskTotalCount) * 100
    : 0;

$childTaskBreachPercentage = round($childTaskBreachPercentage, 2);

@endphp

<section class="bg-warm-light border-bottom p-4">
    <div class="row g-3">
        <div class="col-lg-1">
        </div>

        <div class="col-6 col-md-4 col-lg-2">
            <div class="card p-3 shadow-sm border-0">
                <div class="bg-success-subtle rounded-3 p-2 mb-2 d-inline-block text-center" style="width: 40px;"><i class="bi bi-git text-brand-green"></i></div>
                <h3 class="fw-bold mb-0">{{ count($assignment->sections) }}</h3>
                <small class="text-muted">Departments</small>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card p-3 shadow-sm border-0">
                <div class="bg-warning-subtle rounded-3 p-2 mb-2 d-inline-block text-center" style="width: 40px;"><i class="bi bi-card-checklist text-warning"></i></div>
                <h3 class="fw-bold mb-0">{{ $assignment->children()->count() }}</h3>
                <small class="text-muted">Tasks</small>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card p-3 shadow-sm border-0">
                <div class="bg-info-subtle rounded-3 p-2 mb-2 d-inline-block text-center" style="width: 40px;"><i class="bi bi-alt text-info"></i></div>
                <h3 class="fw-bold mb-0">{{ $averageTurnaroundTime }}</h3>
                <small class="text-muted">Avg. TAT</small>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card p-3 shadow-sm border-0">
                <div class="bg-info-subtle rounded-3 p-2 mb-2 d-inline-block text-center" style="width: 40px;"><i class="bi bi-alarm-fill text-info"></i></div>
                <h3 class="fw-bold mb-0">{{ $assignment->children()->whereHas('task', function ($innerBuilder) {
                    $innerBuilder->whereIn('status', [2, 3])
                    ->whereColumn('completion_date', '<=', 'completed_by');
                })->count() }}</h3>
                <small class="text-muted">On Time</small>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card p-3 shadow-sm border-0">
                <div class="bg-info-subtle rounded-3 p-2 mb-2 d-inline-block text-center" style="width: 40px;"><i class="bi bi-alarm text-info"></i></div>
                <h3 class="fw-bold mb-0">{{ $childTaskBreachPercentage }}%</h3>
                <small class="text-muted">TAT Breach</small>
            </div>
        </div>
        <div class="col-lg-1">
        </div>
    </div>
</section>

<div class="container-fluid p-4">
    <div class="row g-4">
        <main class="col-xl-12">
            
            <section class="card mb-4 overflow-hidden border-0 shadow-sm">
                <div class="bg-brand-green p-4 text-white d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-bold mb-1">Current Execution Status</h4>
                    </div>
                    <div class="text-end">
                        <h2 class="fw-bold mb-0">{{ $mainCompletionPercentage }}%</h2>
                        <small class="text-soft-sage">{{ $mainCompletionPercentage <= 0 ? 'Pending' : ($mainCompletionPercentage > 0 && $mainCompletionPercentage <= 99 ? 'In Progress' : 'Completed') }}</small>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between fw-bold small mb-2">
                            <span>Overall Progress</span>
                            <span class="text-brand-green">{{ $mainCopmletedTasks }} of {{ $mainTotalTasks }} tasks</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar progress-bar-gradient" style="width: {{ $mainCompletionPercentage }}%"></div>
                        </div>
                    </div>
                    
                    <div class="row g-3 mb-4 text-center">
                        <div class="col-md-3">
                            <div class="p-3 bg-success-subtle rounded-3 border border-success-subtle d-flex align-items-center gap-3">
                                <div class="bg-success rounded-circle p-3 text-white"><i class="fas fa-check"></i></div>
                                <div class="text-start"><h3 class="mb-0 fw-bold">{{ $mainCopmletedTasks }}</h3><small class="text-success">Completed</small></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-primary-subtle rounded-3 border border-primary-subtle d-flex align-items-center gap-3">
                                <div class="bg-primary rounded-circle p-3 text-white status-pulse"><i class="fas fa-spinner"></i></div>
                                <div class="text-start"><h3 class="mb-0 fw-bold">{{ $mainInprogress }}</h3><small class="text-primary">In Progress</small></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3 border d-flex align-items-center gap-3">
                                <div class="bg-secondary rounded-circle p-3 text-white"><i class="fas fa-clock"></i></div>
                                <div class="text-start"><h3 class="mb-0 fw-bold">{{ $mainPending }}</h3><small class="text-muted">Pending</small></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3 border d-flex align-items-center gap-3">
                                <div class="bg-secondary rounded-circle p-3 text-white"><i class="fas fa-clock"></i></div>
                                <div class="text-start"><h3 class="mb-0 fw-bold">{{ $childTaskBreachCount }}</h3><small class="text-muted">TAT Breach</small></div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-6"><div class="p-3 bg-light rounded d-flex justify-content-between small"><span>Started:</span><strong>{{ date('d-m-Y H:i', strtotime($assignment->start_from)) }}</strong></div></div>
                        <div class="col-md-6"><div class="p-3 bg-light rounded d-flex justify-content-between small"><span>Completion:</span><strong>N/A</strong></div></div>
                        <div class="col-md-6"><div class="p-3 bg-light rounded d-flex justify-content-between small"><span>Days Elapsed:</span><strong>{{ \Carbon\Carbon::parse($assignment->start_from)->diffInDays() }}</strong></div></div>
                        <div class="col-md-6"><div class="p-3 bg-light rounded d-flex justify-content-between small"><span>Days Remaining:</span><strong class="text-brand-green">N/A</strong></div></div>
                    </div>
                </div>
            </section>

            <h5 class="fw-bold mb-3 text-ink">Phase Progress Breakdown</h5>
            <div class="accordion mb-4" id="phases">

                @foreach ($assignment->sections as $section)
                @php
                    $totalTasks = \App\Models\NewworkflowAssignmentItem::where('new_workflow_assignment_id', $assignment->id)->where('section_id', $section['id'])->count();
                    $copmletedTasks = \App\Models\ChecklistTask::whereHas('wf', function ($builder) use ($assignment, $section) {
                        return $builder->where('new_workflow_assignment_id', $assignment->id)
                        ->where('section_id', $section['id']);
                    })->whereIn('status', [2, 3])->count();

                    $completionPercentage = $totalTasks > 0
                            ? round(($copmletedTasks / $totalTasks) * 100, 2)
                            : 0;
                @endphp
                <div class="accordion-item mb-2 border rounded shadow-sm overflow-hidden">
                    <h2 class="accordion-header">
                        <button class="accordion-button d-flex align-items-center gap-3 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#p2-{{ $loop->iteration }}">
                            <i class="fas fa-chevron-down text-muted small"></i>
                            <div class="bg-primary rounded-circle p-2 text-white small status-pulse" style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;"><i class="fas fa-box"></i></div>
                            <div class="flex-grow-1">
                                <span class="d-block fw-bold">Phase {{ $loop->iteration }}: {{ $section['name'] }}</span>
                                <small class="text-muted">{{ $totalTasks }} tasks • @if($completionPercentage <= 0) Pending @elseif($completionPercentage > 0 && $completionPercentage < 100) In Progress @else Completed @endif</small>
                            </div>
                            <div class="text-end me-3">
                                <span class="badge bg-primary">{{ $completionPercentage }}%</span>
                                <small class="d-block text-muted" style="font-size:10px;"> {{ $copmletedTasks }} /{{ $totalTasks }} tasks</small>
                            </div>
                        </button>
                    </h2>
                    <div id="p2-{{ $loop->iteration }}" class="accordion-collapse collapse">
                        <div class="accordion-body p-0 border-top">

                            {{-- Tasks --}}
                            @foreach (\App\Models\NewworkflowAssignmentItem::with('task')->where('new_workflow_assignment_id', $assignment->id)->where('section_id', $section['id'])->where('section_code', $section['code'])->get() as $rowItem)
                                <div class="accordion-body p-0 border-top">
                                    <span class="d-block fw-bold" style="margin-left: 16px;margin-top: 15px;">  {{ $rowItem->step_name ?? '' }} • {{ round(isset($rowItem->task->id) ? ($rowItem->task->percentage) : 0) }}% </span>
                                    <div class="p-3"><div class="progress" style="height: 8px;"><div class="progress-bar" style="width: {{ round(isset($rowItem->task->id) ? ($rowItem->task->percentage) : 0) }}%"></div></div></div>
                                </div>
                            @endforeach
                            {{-- Tasks --}}

                        </div>
                    </div>
                </div>
                @endforeach

            </div>

            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <div class="card p-4 border-0 shadow-sm h-100">
                        <h6 class="fw-bold mb-4">Completion Timeline</h6>
                        <div id="timeline-chart" style="height: 300px"></div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card p-4 border-0 shadow-sm h-100">
                        <h6 class="fw-bold mb-4">Step Distribution by Status</h6>
                        <div id="distribution-chart" style="height: 300px"></div>
                    </div>
                </div>
            </div>

        </main>

    </div>
</div>
@endsection

@php
$tasks = \App\Models\ChecklistTask::with('wf')
    ->whereHas('wf', function ($builder) use ($assignment) {
        $builder->where('new_workflow_assignment_id', $assignment->id);
    })
    ->select(
        'id',
        'workflow_checklist_id',
        // DB::raw('TIMESTAMPDIFF(MINUTE, completed_by, completion_date) as minutes_taken')
        DB::raw('TIMESTAMPDIFF(MINUTE, completed_by, completion_date) / 60 as hours_taken')
    )
    ->whereNotNull('completed_by')
    ->whereNotNull('completion_date')
    ->whereColumn('completion_date', '>', 'completed_by')
    ->get();
    
    $chartData = $tasks
        ->groupBy(fn ($task) => $task->wf->step_name)
        ->map(fn ($group) => round($group->avg('hours_taken'), 2) . ' hour');

    $xAxis = $chartData->keys()->values();
    $yAxis = $chartData->values();
@endphp

@push('js')
<script src="https://cdn.plot.ly/plotly-3.1.1.min.js"></script>
<script>
window.addEventListener('load', function() {
    const xAxis = @json($xAxis);
    const yAxis = @json($yAxis);

    const timelineData = [{
        type: 'scatter', mode: 'lines+markers',
        x: xAxis,
        y: yAxis,
        line: { color: '#065E2E', width: 3 },
        marker: { size: 8, color: '#065E2E' },
        name: 'Completed in Hours'
    }];

    Plotly.newPlot('timeline-chart', timelineData, {
        margin: { t: 20, r: 20, b: 40, l: 50 },
        plot_bgcolor: '#F8F9FA', paper_bgcolor: '#F8F9FA',
        showlegend: true, legend: { orientation: 'h', y: 1.1 }
    }, {responsive: true, displayModeBar: false});

    const distributionData = [{
        type: 'pie', labels: ['Completed', 'In Progress', 'Pending'],
        values: [{{ $mainCopmletedTasks }}, {{ $mainInprogress }}, {{ $mainPending }}],
        marker: { colors: ['#10B981', '#3B82F6', '#9CA3AF'] },
        textinfo: 'label+percent'
    }];

    Plotly.newPlot('distribution-chart', distributionData, {
        margin: { t: 20, r: 20, b: 20, l: 20 },
        plot_bgcolor: '#F8F9FA', paper_bgcolor: '#F8F9FA'
    }, {responsive: true, displayModeBar: false});
});
</script>
@endpush
