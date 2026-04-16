<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Workflow Assignment Report</title>
    <style>

        /* ============================================================
           RESET & BASE  — DejaVu Sans is bundled with DomPDF
        ============================================================ */
        * { margin: 0; padding: 0; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9.5px;
            color: #1C1C2E;
            background: #FFFFFF;
            line-height: 1.55;
            padding: 0;
            margin: 0;
        }

        /* ============================================================
           LAYOUT HELPERS  
        ============================================================ */
        .wrap        { padding: 0 26px 24px 26px; }
        .tar         { text-align: right; }
        .tac         { text-align: center; }
        .bold        { font-weight: bold; }
        .muted       { color: #7E8A9A; }
        .upper       { text-transform: uppercase; letter-spacing: 1px; }

        /* ============================================================
           TOP STRIPE  — two-tone decorative bar
        ============================================================ */
        .stripe-outer {
            width: 100%;
            height: 7px;
            background-color: #0B5E35;
        }
        .stripe-gold {
            height: 7px;
            background-color: #C9952A;
            width: 28%;
        }

        /* ============================================================
           HEADER BAND
        ============================================================ */
        .header-band {
            background-color: #0B5E35;
            padding: 18px 26px 16px 26px;
        }

        .report-eyebrow {
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2.5px;
            color: #C9952A;
            margin-bottom: 5px;
        }

        .report-title {
            font-size: 19px;
            font-weight: bold;
            color: #FFFFFF;
            line-height: 1.2;
            margin-bottom: 4px;
        }

        .report-desc {
            font-size: 9px;
            color: #A8C4B0;
            margin-bottom: 10px;
        }

        .meta-item {
            font-size: 8.5px;
            color: #A8C4B0;
            margin-top: 3px;
        }
        .meta-item strong {
            color: #FFFFFF;
        }

        /* Badge */
        .badge {
            display: inline-block;
            padding: 3px 11px;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-radius: 2px;
        }
        .badge-pub   { background-color: #C9952A; color: #FFFFFF; }
        .badge-unpub { background-color: #8B2020; color: #FFFFFF; }

        /* ============================================================
           CONTENT AREA
        ============================================================ */
        .content { padding: 20px 26px 0 26px; }

        /* ---- Section label ---- */
        .section-label {
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #0B5E35;
            border-left: 3px solid #C9952A;
            padding-left: 7px;
            margin-bottom: 10px;
            margin-top: 18px;
        }

        /* ============================================================
           KPI CARDS  — 4 per row
        ============================================================ */
        .kpi-table { width: 100%; border-collapse: collapse; }
        .kpi-card {
            background-color: #FFFFFF;
            border: 1px solid #D6DCE6;
            border-top: 3px solid #0B5E35;
            padding: 10px 12px;
        }
        .kpi-card.gold-top { border-top-color: #C9952A; }

        .kpi-eyebrow {
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #7E8A9A;
            margin-bottom: 5px;
        }
        .kpi-value {
            font-size: 20px;
            font-weight: bold;
            color: #0B5E35;
            line-height: 1;
        }
        .kpi-value.danger { color: #B03030; }
        .kpi-sub {
            font-size: 7.5px;
            color: #9AAAB8;
            margin-top: 4px;
        }

        /* ============================================================
           PROGRESS CARD
        ============================================================ */
        .progress-card {
            background-color: #FFFFFF;
            border: 1px solid #D6DCE6;
            border-left: 4px solid #0B5E35;
            padding: 13px 16px;
            margin-top: 0;
        }

        .prog-title {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #0B5E35;
            margin-bottom: 7px;
        }

        /* Progress track */
        .track {
            width: 100%;
            height: 9px;
            background-color: #E8EEF4;
            border-radius: 4px;
            margin-bottom: 5px;
        }
        .fill {
            height: 9px;
            background-color: #1A8050;
            border-radius: 4px;
        }
        .fill.complete { background-color: #0B5E35; }
        .fill.zero     { background-color: #CBD5E1; }

        .prog-caption {
            font-size: 8px;
            color: #9AAAB8;
            margin-top: 4px;
        }

        .big-pct {
            font-size: 30px;
            font-weight: bold;
            color: #0B5E35;
            line-height: 1;
        }
        .big-pct-label {
            font-size: 8px;
            color: #9AAAB8;
            margin-top: 3px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Mini stat pills below progress */
        .stat-card {
            background-color: #EEF1F6;
            border-radius: 2px;
            padding: 6px 9px;
        }
        .stat-label {
            font-size: 7px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #7E8A9A;
            margin-bottom: 3px;
        }
        .stat-value {
            font-size: 13px;
            font-weight: bold;
            color: #1C1C2E;
        }
        .stat-value.green  { color: #1A8050; }
        .stat-value.amber  { color: #C9952A; }
        .stat-value.red    { color: #B03030; }

        /* ============================================================
           TASK TABLE
        ============================================================ */
        .task-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
            margin-top: 0;
        }

        .task-table thead tr {
            background-color: #0B5E35;
        }

        .task-table th {
            color: #FFFFFF;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding: 7px 9px;
            text-align: left;
            border: none;
        }

        .task-table th:first-child {
            border-left: 3px solid #C9952A;
        }

        .task-table tbody tr { border-bottom: 1px solid #E8EEF4; }
        .task-table tbody tr.alt { background-color: #F6F8FB; }

        .task-table td {
            padding: 6px 9px;
            color: #2A2A3E;
            border: none;
            vertical-align: middle;
        }

        .task-table td:first-child { border-left: 3px solid transparent; }
        .task-table tbody tr.alt td:first-child { border-left-color: #E8EEF4; }

        /* Status chips */
        .chip {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 2px;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .chip-done     { background-color: #D4EDE1; color: #0B5E35; }
        .chip-progress { background-color: #FEF3CD; color: #8A6200; }
        .chip-pending  { background-color: #FCE8E8; color: #8B2020; }

        .breach-yes { color: #B03030; font-weight: bold; }
        .breach-no  { color: #1A8050; }

        .no-data {
            text-align: center;
            color: #9AAAB8;
            padding: 20px;
            font-style: italic;
        }

        /* ============================================================
           FOOTER
        ============================================================ */
        .footer {
            margin-top: 22px;
            padding-top: 9px;
            border-top: 1px solid #D6DCE6;
        }
        .footer-brand { font-weight: bold; color: #0B5E35; }

    </style>
</head>
<body>

{{-- ═══════════════════════════════════════
     PHP: compute all KPIs
═══════════════════════════════════════ --}}
@php
    $mainTotalTasks = $mainCopmletedTasks = $mainCompletionPercentage = $mainInprogress = $mainPending = 0;

    foreach ($assignment->sections as $section) {
        $q = function ($b) use ($assignment, $section) {
            return $b->where('new_workflow_assignment_id', $assignment->id)
                    ->where('section_id', $section['id']);
        };

        $mainInprogress     += \App\Models\ChecklistTask::whereHas('wf', $q)->whereIn('status', [1])->count();
        $mainPending        += \App\Models\ChecklistTask::whereHas('wf', $q)->whereIn('status', [0])->count();
        $mainTotalTasks     += \App\Models\NewWorkflowAssignmentItem::where('new_workflow_assignment_id', $assignment->id)
                                    ->where('section_id', $section['id'])->count();
        $mainCopmletedTasks += \App\Models\ChecklistTask::whereHas('wf', $q)->whereIn('status', [2, 3])->count();
    }

    $mainCompletionPercentage = $mainTotalTasks > 0
        ? round(($mainCopmletedTasks / $mainTotalTasks) * 100, 2) : 0;

    // Avg TAT
    $result = \App\Models\NewWorkflowAssignmentItem::where('new_workflow_assignment_id', $assignment->id)
        ->selectRaw('
            FLOOR(AVG(
                (COALESCE(maker_turn_around_time_day,0)*1440)+
                (COALESCE(maker_turn_around_time_hour,0)*60)+
                COALESCE(maker_turn_around_time_minute,0)
            ) / 1440) as avg_days,
            FLOOR((AVG(
                (COALESCE(maker_turn_around_time_day,0)*1440)+
                (COALESCE(maker_turn_around_time_hour,0)*60)+
                COALESCE(maker_turn_around_time_minute,0)
            ) % 1440) / 60) as avg_hours,
            FLOOR(AVG(
                (COALESCE(maker_turn_around_time_day,0)*1440)+
                (COALESCE(maker_turn_around_time_hour,0)*60)+
                COALESCE(maker_turn_around_time_minute,0)
            ) % 60) as avg_minutes
        ')->first();

    $parts = [];
    if ($result && $result->avg_days    > 0) $parts[] = $result->avg_days    . ($result->avg_days    == 1 ? ' Day'    : ' Days');
    if ($result && $result->avg_hours   > 0) $parts[] = $result->avg_hours   . ($result->avg_hours   == 1 ? ' Hr'     : ' Hrs');
    if ($result && ($result->avg_minutes > 0 || empty($parts)))
        $parts[] = ($result->avg_minutes ?? 0) . ' Min';
    $averageTurnaroundTime = implode(' ', $parts) ?: '0 Min';

    // Breach
    $childTaskTotalCount  = $assignment->children()->count();
    $childTaskBreachCount = $assignment->children()
        ->whereHas('task', function ($q) {
            $q->whereNotNull('completion_date')
            ->whereColumn('completion_date', '>', 'completed_by');
        })
        ->count();
    $childTaskBreachPercentage = $childTaskTotalCount > 0
        ? round(($childTaskBreachCount / $childTaskTotalCount) * 100, 2) : 0;

    // Status label
    if ($mainCompletionPercentage <= 0) {
        $statusLabel = 'Not Started';
    } elseif ($mainCompletionPercentage >= 100) {
        $statusLabel = 'Completed';
    } else {
        $statusLabel = 'In Progress';
    }

    // Fill class
    if ($mainCompletionPercentage >= 100) {
        $fillClass = 'fill complete';
    } elseif ($mainCompletionPercentage <= 0) {
        $fillClass = 'fill zero';
    } else {
        $fillClass = 'fill';
    }
@endphp

{{-- ═══════════════════════════════════════
     TOP STRIPE
═══════════════════════════════════════ --}}
<div class="stripe-outer">
    <div class="stripe-gold"></div>
</div>

{{-- ═══════════════════════════════════════
     HEADER BAND
═══════════════════════════════════════ --}}
<div class="header-band">
    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td valign="top" style="width: 65%;">
                <div class="report-eyebrow">Workflow Assignment Report</div>
                <div class="report-title">{{ $assignment->title }}</div>
                @if($assignment->description)
                    <div class="report-desc">{{ $assignment->description }}</div>
                @endif
                <div class="meta-item">
                    Created by:&nbsp;
                    <strong>
                        @if(isset($assignment->usr->id))
                            {{ $assignment->usr->employee_id }} &mdash;
                            {{ trim($assignment->usr->name . ' ' . $assignment->usr->middle_name . ' ' . $assignment->usr->last_name) }}
                        @else
                            N/A
                        @endif
                    </strong>
                </div>
                <div class="meta-item">
                    Last updated:&nbsp;
                    <strong>{{ $assignment->updated_at ? \Carbon\Carbon::parse($assignment->updated_at)->format('d M Y, H:i') : 'N/A' }}</strong>
                </div>
            </td>
            <td valign="top" style="width: 35%; text-align: right;">
                <span class="badge {{ $assignment->status ? 'badge-pub' : 'badge-unpub' }}" style="margin-bottom: 8px;">
                    {{ $assignment->status ? 'Published' : 'Unpublished' }}
                </span>
                <div class="meta-item" style="margin-top: 6px;">
                    Start Date<br>
                    <strong>{{ $assignment->start_from ? \Carbon\Carbon::parse($assignment->start_from)->format('d M Y') : 'N/A' }}</strong>
                </div>
                <div class="meta-item" style="margin-top: 6px;">
                    On-Going Project<br>
                    <strong>{{ $assignment->on_going_project ? 'Yes' : 'No' }}</strong>
                </div>
            </td>
        </tr>
    </table>
</div>

{{-- ═══════════════════════════════════════
     CONTENT
═══════════════════════════════════════ --}}
<div class="content">

    {{-- KPI SECTION LABEL --}}
    <div class="section-label">Key Metrics</div>

    {{-- KPI CARDS --}}
    <table class="kpi-table" cellspacing="0" cellpadding="0">
        <tr>
            <td style="width: 25%; padding-right: 4px; vertical-align: top;">
                <div class="kpi-card">
                    <div class="kpi-eyebrow">Departments</div>
                    <div class="kpi-value">{{ count($assignment->sections ?? []) }}</div>
                    <div class="kpi-sub">sections assigned</div>
                </div>
            </td>
            <td style="width: 25%; padding: 0 4px; vertical-align: top;">
                <div class="kpi-card">
                    <div class="kpi-eyebrow">Total Tasks</div>
                    <div class="kpi-value">{{ $childTaskTotalCount }}</div>
                    <div class="kpi-sub">across all sections</div>
                </div>
            </td>
            <td style="width: 25%; padding: 0 4px; vertical-align: top;">
                <div class="kpi-card">
                    <div class="kpi-eyebrow">Avg. Turnaround</div>
                    <div class="kpi-value" style="font-size:15px;">{{ $averageTurnaroundTime }}</div>
                    <div class="kpi-sub">per task (maker)</div>
                </div>
            </td>
            <td style="width: 25%; padding-left: 4px; vertical-align: top;">
                <div class="kpi-card gold-top">
                    <div class="kpi-eyebrow">TAT Breach Rate</div>
                    <div class="kpi-value {{ $childTaskBreachPercentage > 20 ? 'danger' : '' }}">
                        {{ $childTaskBreachPercentage }}%
                    </div>
                    <div class="kpi-sub">{{ $childTaskBreachCount }} tasks breached</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- PROGRESS SECTION LABEL --}}
    <div class="section-label">Completion Progress</div>

    {{-- PROGRESS CARD --}}
    <div class="progress-card">
        <table width="100%" cellspacing="0" cellpadding="0" style="border:none;">
            <tr>
                <td valign="top" style="width: 65%; padding-right: 15px;">
                    <div class="prog-title">Overall Task Completion</div>
                    <div class="track">
                        <div class="{{ $fillClass }}" style="width: {{ $mainCompletionPercentage }}%;"></div>
                    </div>
                    <div class="prog-caption">
                        <strong>{{ $mainCopmletedTasks }}</strong> of <strong>{{ $mainTotalTasks }}</strong> tasks completed
                        &nbsp;&bull;&nbsp; {{ 100 - $mainCompletionPercentage }}% remaining
                    </div>
                </td>
                <td valign="top" style="width: 35%; text-align: right;">
                    <div class="big-pct">{{ $mainCompletionPercentage }}%</div>
                    <div class="big-pct-label">{{ $statusLabel }}</div>
                </td>
            </tr>
        </table>

        {{-- Mini stats row --}}
        <table width="100%" cellspacing="0" cellpadding="0" style="margin-top:14px; border-collapse: collapse;">
            <tr>
                <td style="width: 25%; padding-right: 4px; vertical-align: top;">
                    <div class="stat-card">
                        <div class="stat-label">Completed</div>
                        <div class="stat-value green">{{ $mainCopmletedTasks }}</div>
                    </div>
                </td>
                <td style="width: 25%; padding: 0 4px; vertical-align: top;">
                    <div class="stat-card">
                        <div class="stat-label">In Progress</div>
                        <div class="stat-value amber">{{ $mainInprogress }}</div>
                    </div>
                </td>
                <td style="width: 25%; padding: 0 4px; vertical-align: top;">
                    <div class="stat-card">
                        <div class="stat-label">Pending</div>
                        <div class="stat-value red">{{ $mainPending }}</div>
                    </div>
                </td>
                <td style="width: 25%; padding-left: 4px; vertical-align: top;">
                    <div class="stat-card">
                        <div class="stat-label">TAT Breached</div>
                        <div class="stat-value red">{{ $childTaskBreachCount }}</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- TASK TABLE SECTION LABEL --}}
    <div class="section-label">Task Details</div>

    <table class="task-table">
        <thead>
            <tr>
                <th style="width:13%;">Department</th>
                <th style="width:24%;">Task</th>
                <th style="width:15%;">Maker</th>
                <th style="width:15%;">Checker</th>
                <th style="width:9%;">TAT Breach</th>
                <th style="width:12%;">End Date</th>
                <th style="width:12%;">Status</th>
            </tr>
        </thead>
        <tbody>
        @forelse($rows as $i => $row)
            @php
                $isAlt = ($i % 2 !== 0);
                $s     = strtolower($row['status'] ?? '');
                $isBreached = strtolower($row['breach'] ?? '') === 'yes';

                if (strpos($s, 'complete') !== false || strpos($s, 'done') !== false || strpos($s, 'finish') !== false) {
                    $chipClass = 'chip-done';
                } elseif (strpos($s, 'progress') !== false || strpos($s, 'ongoing') !== false || strpos($s, 'active') !== false) {
                    $chipClass = 'chip-progress';
                } else {
                    $chipClass = 'chip-pending';
                }
            @endphp
            <tr class="{{ $isAlt ? 'alt' : '' }}">
                <td>{{ $row['department'] }}</td>
                <td>{{ $row['task'] }}</td>
                <td>{{ $row['maker'] }}</td>
                <td>{{ $row['checker'] }}</td>
                <td>
                    @if($isBreached)
                        <span class="breach-yes">&#x2717; Yes</span>
                    @else
                        <span class="breach-no">&#x2713; No</span>
                    @endif
                </td>
                <td>{{ $row['end_date'] }}</td>
                <td><span class="chip {{ $chipClass }}">{{ $row['status'] }}</span></td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="no-data">No tasks found for this workflow assignment.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    {{-- FOOTER --}}
    <div class="footer">
        <table width="100%" cellspacing="0" cellpadding="0">
            <tr>
                <td style="font-size: 8px; color: #B0BAC8;">
                    Generated on {{ now()->format('d M Y \a\t H:i') }} &nbsp;&bull;&nbsp; Confidential &mdash; Internal Use Only
                </td>
                <td style="font-size: 8px; color: #B0BAC8; text-align: right;">
                    <span class="footer-brand">&#9632;&nbsp;Workflow System</span>
                </td>
            </tr>
        </table>
    </div>

</div>{{-- /content --}}

</body>
</html>