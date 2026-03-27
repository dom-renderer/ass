@extends('layouts.app-master')

@php

    $sectionGroup = [];
    $namesToBeIgnored = array_combine(\App\Helpers\Helper::$namesToBeIgnored, \App\Helpers\Helper::$namesToBeIgnored);

    if (is_string($task->data)) {
        $data = json_decode($task->data, true);
    } elseif (is_array($task->data)) {
        $data = $task->data;
    } else {
        $data = [];
    }

    $groupedData = [];

    if (isset($task->parent->parent->checklist_id) && in_array($task->parent->parent->checklist_id, [106, 107])) {
        foreach ($data as $item) {
            if (!isset($namesToBeIgnored[$item->name])) {
                $groupedData[$item->className][] = $item;
            }
        }
    } else {
        foreach ($data as $item) {
            $groupedData[$item->className][] = $item;
        }
    }

    $varients = \App\Helpers\Helper::categorizePoints($task->data ?? []);

    $total = count(\App\Helpers\Helper::selectPointsQuestions($task->data));
    $toBeCounted = $total - count($varients['na']);

    $failed = abs(count(array_column($varients['negative'], 'value')));
    $achieved = $toBeCounted - abs($failed);

    if ($failed <= 0) {
        $achieved = array_sum(array_column($varients['positive'], 'value'));
    }

    $ptp = isset($task->parent->parent->checklist->ptp) && is_numeric($task->parent->parent->checklist->ptp) ? $task->parent->parent->checklist->ptp : 0;

    if ($toBeCounted > 0) {
        $percentage = ($achieved / $toBeCounted) * 100;
    } else {
        $percentage = 0;
    }

    $hasImages = false;

    $globalCounter = new \stdClass();
    $globalCounter->value = 0;

    $versionedForm = \App\Helpers\Helper::getVersionForm($task->version_id);
    $isPointChecklist = \App\Helpers\Helper::isPointChecklist($versionedForm);

    $sectionWise = collect($task->data ?? [])->groupBy('page')->values()->toArray();

    $iterationCount = 0;
    foreach ($sectionWise as $pKey => $totalSectionsRow) {
            if (!($iterationCount == 0 || $iterationCount == 1)) {
            $thisVarients = \App\Helpers\Helper::categorizePoints($totalSectionsRow ?? []);
            $thisTotal = count(\App\Helpers\Helper::selectPointsQuestions($totalSectionsRow));
            $thisToBeCounted = $thisTotal - count($thisVarients['na']);

            $thisFailed = abs(count(array_column($thisVarients['negative'], 'value')));
            $thisAchieved = $thisToBeCounted - abs($thisFailed);

            if ($thisFailed <= 0) {
                $thisAchieved = array_sum(array_column($thisVarients['positive'], 'value'));
            }
            
            if ($thisToBeCounted > 0) {
                $thisPer = number_format(($thisAchieved / $thisToBeCounted) * 100, 2);
            } else {
                $thisPer = 0;
            }

            $titleOfSection = 'Page ' . ($pKey + 1);

            if (is_array($versionedForm) && isset($versionedForm[$pKey])) {
                $titleOfSection = collect($versionedForm[$pKey])->where('type', 'header')->get(0)->label ?? ('Page ' . ($pKey + 1));
            }

            $ptp = isset($task->parent->parent->checklist->ptp) && is_numeric($task->parent->parent->checklist->ptp) ? $task->parent->parent->checklist->ptp : 0;

            if ($thisPer >= $ptp) {
                $color = '#9e0b21';
            } else {
                $color = '#0d6032';
            }

            $sectionGroup[] = [
                'percentage' => $thisPer,
                'title' => $titleOfSection,
                'color' => $color
            ];
        }
        $iterationCount++;
    }

    $sectionGroup = collect($sectionGroup);

    $failed = $sectionGroup
        ->filter(fn ($item) => $item['percentage'] > 0 && $item['percentage'] <= 80)
        ->sortByDesc('percentage');

    $passed = $sectionGroup
        ->filter(fn ($item) => $item['percentage'] > 80)
        ->sortByDesc('percentage');

    $notStarted = $sectionGroup
        ->filter(fn ($item) => $item['percentage'] <= 0)
        ->sortByDesc('percentage');

    $sectionGroup = $failed
        ->merge($passed)
        ->merge($notStarted)
        ->values()
        ->toArray();

    $date1 = \Carbon\Carbon::parse($task->started_at);
    $date2 = \Carbon\Carbon::parse($task->completion_date);
    $diff = $date1->diff($date2);

@endphp

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <style>
        .gallery img {
            width: 150px;
            cursor: pointer;
            margin: 5px;
        }

        .lightbox {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            text-align: center;
        }

        .lightbox img {
            max-width: 80%;
            max-height: 80%;
            margin-top: 5%;
            transition: transform 0.3s;
        }

        .controls {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
        }

        .controls button {
            margin: 5px;
            padding: 10px;
            cursor: pointer;
        }

        .close {
            position: absolute;
            top: 10px;
            right: 20px;
            font-size: 30px;
            color: white;
            cursor: pointer;
        }

        .prev,
        .next {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            font-size: 24px;
            color: white;
            background: rgba(0, 0, 0, 0.5);
            border: none;
            padding: 10px;
            cursor: pointer;
        }

        .prev {
            left: 10px;
            display: none;
        }

        .next {
            right: 10px;
            display: none;
        }

        .main-sidebar::-webkit-scrollbar {
            height: 12px;
            width: 5px;
            background: #000;
        }

        .main-sidebar::-webkit-scrollbar-thumb {
            background: #fff410;
            -webkit-border-radius: 1ex;
            -webkit-box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.75);
        }

        .main-sidebar::-webkit-scrollbar-corner {
            background: #000;
        }

        .main-sidebar::-webkit-scrollbar-thumb:hover {
            background: #fff410;
            /* Brighter green on hover */
        }

        /* Optional: active (when clicked) state */
        .main-sidebar::-webkit-scrollbar-thumb:active {
            background: 065e2e;
            /* Even brighter when clicking */
        }


        ::-webkit-scrollbar {
            height: 12px;
            width: 8px;
            background: #000;
        }

        ::-webkit-scrollbar-thumb {
            background: #065e2e;
            border-radius: 1ex;
            box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.75);
        }

        /* Hover effect for scrollbar thumb */
        ::-webkit-scrollbar-thumb:hover {
            background: #065e2e;
            /* Brighter green on hover */
        }

        /* Optional: active (when clicked) state */
        ::-webkit-scrollbar-thumb:active {
            background: #065e2e;
            /* Even brighter when clicking */
        }

        ::-webkit-scrollbar-corner {
            background: #000;
        }
        :root {
            --teapost-primary: rgb(6, 94, 46);
            --teapost-dark: rgb(4, 70, 34);
            --teapost-light: rgb(8, 118, 58);
            --failure: rgb(185, 28, 28);
            --failure-light: rgb(254, 242, 242);
            --failure-border: rgb(252, 165, 165);
            --warning: rgb(217, 119, 6);
            --warning-light: rgb(254, 252, 232);
            --warning-border: rgb(253, 224, 71);
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-900: #111827;

            --teapost-primary-light: rgb(8, 120, 59);
            --teapost-lighter: rgb(220, 237, 228);
            --risk-critical: rgb(153, 27, 27);
            --risk-critical-bg: rgb(254, 242, 242);
            --risk-critical-border: rgb(252, 165, 165);
            --risk-major: rgb(180, 83, 9);
            --risk-major-bg: rgb(255, 247, 237);
            --risk-major-border: rgb(253, 186, 116);
            --risk-minor: rgb(113, 113, 122);
            --risk-minor-bg: rgb(250, 250, 250);
            --risk-minor-border: rgb(212, 212, 216);
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-900: #111827;            
        }

        #report-container {
            width: 100%;
            background: white;
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            display: flex;
            flex-direction: column;
            border-radius: 0.5rem;
            margin-bottom: 20px;
        }

        /* --- HEADER SECTION --- */
        #report-header {
            padding-bottom: 1.5rem;
            border-bottom: 2px solid var(--gray-200);
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }

        .audit-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background: var(--teapost-primary);
            color: white;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-radius: 0.25rem;
            margin-bottom: 0.75rem;
        }

        .store-info-box {
            text-align: right;
            background-color: var(--gray-50);
            padding: 1rem 1.25rem;
            border-radius: 0.5rem;
            border: 1px solid var(--gray-200);
        }

        .header-meta-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            font-size: 0.875rem;
            color: var(--gray-600);
        }

        .meta-pill {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--gray-50);
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
        }

        /* --- LAYOUT GRID --- */
        .summary-content {
            margin-top: 2.5rem;
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 2rem;
            flex: 1;
        }

        /* --- SCORE PANEL --- */
        .score-panel {
            background: linear-gradient(to bottom right, var(--gray-50), var(--gray-100));
            padding: 2rem;
            border-radius: 0.75rem;
            border: 2px solid var(--gray-200);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .gauge-wrap {
            position: relative;
            width: 13rem;
            height: 13rem;
        }

        .gauge-svg {
            width: 100%;
            height: 100%;
            transform: rotate(-90deg);
        }

        .gauge-bg {
            fill: none;
            stroke: var(--gray-200);
            stroke-width: 2.5;
        }

        .gauge-fill {
            fill: none;
            @if($percentage > $ptp)
            stroke: var(--teapost-primary);
            @else
            stroke: var(--failure);
            @endif
            stroke-width: 2.5;
            stroke-linecap: round;
            transition: stroke-dasharray 1s ease-out;
        }

        .status-badge {
            margin-top: 1.5rem;
            display: inline-flex;
            align-items: center;
            color: var(--failure);
            padding: 0.75rem 2rem;
            border-radius: 0.5rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .status-badge-success {
            background: #afe9c9;
            color: var(--teapost-primary);
            border: 2px solid var(--teapost-primary);
        }

        .status-badge-danger {
            background: var(--failure-light);
            color: var(--failure);
            border: 2px solid var(--failure-border);
        }

        /* --- KPI PANEL --- */
        .kpi-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
        }

        .kpi-grid-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1.25rem;
            margin-top: 20px;
        }

        .kpi-card {
            background: white;
            border: 2px solid var(--gray-200);
            padding: 1.5rem;
            border-radius: 0.75rem;
        }

        .kpi-card.pass {
            border-color: var(--teapost-primary);
        }

        .kpi-card.fail {
            border-color: var(--failure-border);
        }

        .kpi-card.warn {
            border-color: var(--warning-border);
        }

        .kpi-card h3 {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--gray-500);
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .kpi-icon-box {
            width: 2.5rem;
            height: 2.5rem;
            background: var(--gray-100);
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* --- INSIGHT BLOCK --- */
        .insight-block {
            margin-top: 1.5rem;
            background-color: #fffbeb;
            border-left: 4px solid var(--warning);
            padding: 1.5rem;
            border-radius: 0.75rem;
            display: flex;
            gap: 1rem;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.25rem;
        }

        .header-title-group { display: flex; align-items: center; }
        .accent-bar { width: 0.25rem; height: 2rem; background: var(--teapost-primary); margin-right: 0.75rem; }

        .store-info-box {
            text-align: right;
            line-height: 1.4;
        }

        .meta-strip {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: var(--gray-50);
            padding: 0.625rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            color: var(--gray-600);
        }

        /* --- CHART SECTION --- */
        .chart-header {
            margin-top: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.75rem;
        }

        .legend {
            display: flex;
            gap: 1.25rem;
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--gray-600);
        }

        .legend-item { display: flex; align-items: center; gap: 0.5rem; }
        .dot { width: 0.875rem; height: 0.875rem; border-radius: 0.25rem; }

        /* --- BAR CHART ENGINE --- */
        .bar-chart { display: flex; flex-direction: column; gap: 0.875rem; }
        .chart-row {
            display: grid;
            grid-template-columns: 180px 1fr;
            align-items: center;
            gap: 1.25rem;
        }

        .row-label {
            text-align: right;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--gray-900);
        }

        .bar-container {
            width: 100%;
            background: var(--gray-100);
            height: 2.5rem;
            border-radius: 0.25rem;
            overflow: hidden;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);
        }

        .bar-fill {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 0.75rem;
            transition: width 1s ease-in-out;
            width: 0%; /* Start at 0 for animation */
        }

        .bar-fill span { font-size: 0.875rem; font-weight: 600; color: white; }

        /* --- INSIGHT BLOCK --- */
        .insight-card {
            margin-top: 2rem;
            background: linear-gradient(to right, var(--failure-light), white);
            border-left: 4px solid var(--failure-border);
            padding: 1.5rem;
            border-radius: 0.5rem;
            display: flex;
            gap: 1rem;
            align-items: flex-start;
        }     
        

        /* --- HEADER --- */
        #header {
            border-bottom: 2px solid var(--gray-200);
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.25rem;
        }

        .store-info {
            text-align: right;
            line-height: 1.4;
        }

        .meta-strip {
            display: flex;
            justify-content: space-between;
            font-size: 0.875rem;
            color: var(--gray-600);
            margin-top: 1rem;
        }

        /* --- RISK SECTIONS --- */
        .risk-section { margin-top: 2rem; }
        
        .section-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .icon-box {
            width: 2.5rem;
            height: 2.5rem;
            background: var(--teapost-lighter);
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--teapost-primary);
            font-size: 1.125rem;
        }

        .risk-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        /* --- RISK CARDS --- */
        .risk-card {
            border-left: 4px solid transparent;
            padding: 1rem;
            border-radius: 0 0.5rem 0.5rem 0;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.5rem;
        }

        .badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.625rem;
            border-radius: 0.375rem;
            color: white;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .remark-box {
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            border-top: 1px solid;
            font-size: 0.875rem;
            line-height: 1.5;
        }

        /* Color Modifiers */
        .critical { border-color: var(--risk-critical); background: var(--risk-critical-bg); }
        .critical .badge { background: var(--risk-critical); }
        .critical .remark-box { border-color: var(--risk-critical-border); }

        .major { border-color: var(--risk-major); background: var(--risk-major-bg); }
        .major .badge { background: var(--risk-major); }
        .major .remark-box { border-color: var(--risk-major-border); }

        .minor { border-color: var(--risk-minor); background: var(--risk-minor-bg); }
        .minor .badge { background: var(--risk-minor); }
        .minor .remark-box { border-color: var(--risk-minor-border); }

        /* --- FOOTER --- */
        footer {
            margin-top: auto;
            padding-top: 1.5rem;
            border-top: 2px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
   
        td {
            font-size: 1rem;
            font-weight: 600;
            line-height: 1.3;
        }
    </style>
@endpush

@section('content')

    <div id="report-container">
        <header id="report-header">
            <div class="header-top">
                <div>
                    <div class="audit-badge">Report</div>
                    <h1 style="font-size: 2.25rem; font-weight: 800; color: var(--gray-900); letter-spacing: -0.025em;">
                        {{ $task->parent->parent->checklist->name ?? '' }}</h1>
                </div>
                <div class="store-info-box">
                    <p style="font-weight: 700; font-size: 1.125rem; color: var(--gray-900);">{{ $task->parent->actuallocation->name ?? 'N/A' }}</p>
                    <div style="margin-top: 0.5rem; line-height: 1.4;">
                        <p style="font-size: 0.875rem; color: var(--gray-600);">Store Code: <span style="font-weight: 600;">{{ $task->parent->actuallocation->code ?? 'N/A' }}</span></p>
                        <p style="font-size: 0.875rem; color: var(--gray-600);">Location: <span style="font-weight: 600;">{{ $task->parent->actuallocation->thecity->city_name ?? 'N/A' }}, {{ $task->parent->actuallocation->thecity->city_state ?? 'N/A' }}  </span></p>
                    </div>
                </div>
            </div>
            <div class="header-meta-row">
                <div class="meta-pill">
                    <i class="fa-solid fa-calendar-day" style="color: var(--teapost-primary);"></i>
                    <span>Start Time: <strong style="color: var(--gray-900);">{{ $date1->format('d F Y H:i') }}</strong></span>
                </div>
                <div class="meta-pill">
                    <i class="fa-solid fa-user-check" style="color: var(--teapost-primary);"></i>
                    <span>End Time: <strong style="color: var(--gray-900);"> {{ $task->status == 1 ? '-' : $date2->format('d F Y H:i') }} </strong></span>
                </div>
                <div class="meta-pill">
                    <i class="fa-solid fa-user-check" style="color: var(--teapost-primary);"></i>
                    <span>Ops Time: <strong style="color: var(--gray-900);"> 
                        @if($task->status == 1)
                            -
                        @else
                            @if($diff->d > 0)
                            {{ $diff->d }} days,
                            @endif
                            @if($diff->h > 0)
                            {{ $diff->h }} hours,
                            @endif
                            @if($diff->i > 0)
                            {{ $diff->i }} minutes
                            @endif
                            @if($diff->d <= 0 && $diff->h <= 0 && $diff->i <= 0)
                                -
                            @endif
                        @endif
                </strong></span>
                </div>
            </div>
            <div class="header-meta-row">
                <div class="meta-pill">
                    <i class="fa-solid fa-calendar-day" style="color: var(--teapost-primary);"></i>
                    <span>Audit Date: <strong style="color: var(--gray-900);">{{ date('d F Y', strtotime($task->date)) }}</strong></span>
                </div>
                <div class="meta-pill">
                    <i class="fa-solid fa-user-check" style="color: var(--teapost-primary);"></i>
                    <span>Auditor: <strong style="color: var(--gray-900);"> {{ $task->parent->user->employee_id ?? 'N/A' }} - {{ $task->parent->user->name ?? 'N/A' }} {{ $task->parent->user->middle_name ?? 'N/A' }} {{ $task->parent->user->last_name ?? 'N/A' }} </strong></span>
                </div>
            </div>
        </header>

        <section class="summary-content">
            <div class="score-panel">
                <h2
                    style="font-size: 0.875rem; font-weight: 600; color: var(--gray-500); text-transform: uppercase; margin-bottom: 1.5rem;">
                    Overall Compliance Score</h2>
                <div class="gauge-wrap">
                    <svg class="gauge-svg" viewBox="0 0 36 36">
                        <path class="gauge-bg"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"></path>
                        <path id="gauge-fill" class="gauge-fill" stroke-dasharray="0, 100"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"></path>
                    </svg>
                    <div
                        style="position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        <span id="score-num" style="font-size: 3.75rem; font-weight: 800; color: var(--gray-900);">0</span>
                        <span
                            style="font-size: 1.5rem; font-weight: 600; color: var(--gray-500); margin-top: -8px;">%</span>
                    </div>
                </div>
                <div class="status-badge @if($percentage > $ptp) status-badge-success @else status-badge-danger @endif">
                    <i class="fa-solid fa-circle-xmark" style="margin-right: 0.5rem;"></i>
                    Status: {{ $percentage > $ptp ? 'Pass' : 'Fail' }}
                </div>
            </div>

            <div style="display: flex; flex-direction: column;">
                <div class="kpi-grid">
                    <div class="kpi-card">
                        <h3>
                            <div class="kpi-icon-box">
                                <i class="fa fa-list-alt" aria-hidden="true"></i>
                            </div> Total Checks
                        </h3>
                        <p style="font-size: 3rem; font-weight: 800; color: var(--gray-900);">{{ $total }}</p>
                    </div>
                    <div class="kpi-card pass">
                        <h3 style="color: var(--teapost-primary);">
                            <div class="kpi-icon-box" style="background: #f0fdf4;">
                                <i class="fa fa-check-circle" aria-hidden="true"></i>
                            </div> Passed
                        </h3>
                        <p style="font-size: 3rem; font-weight: 800; color: var(--teapost-primary);">{{ $achieved }}</p>
                    </div>
                </div>
                <div class="kpi-grid-3">
                    <div class="kpi-card fail">
                        <h3 style="color: var(--failure);">
                            <div class="kpi-icon-box">
                                <i class="fa fa-exclamation-circle" aria-hidden="true"></i>
                            </div> Critical Failures
                        </h3>
                        <p style="font-size: 3rem; font-weight: 800; color: var(--failure);">{{ $criticalCount ?? 0 }}</p>
                    </div>
                    <div class="kpi-card warn">
                        <h3 style="color: var(--warning);">
                            <div class="kpi-icon-box">
                                <i class="fa fa-window-close" aria-hidden="true"></i>
                            </div> Failed
                        </h3>
                        <p style="font-size: 3rem; font-weight: 800; color: var(--warning);">{{ count($varients['negative']) }}</p>
                    </div>
                    <div class="kpi-card ">
                        <h3 style="color: var(--gray-600);">
                            <div class="kpi-icon-box">
                                <i class="fa fa-window-close" aria-hidden="true"></i>
                            </div> N/A
                        </h3>
                        <p style="font-size: 3rem; font-weight: 800; color: var(--gray-600);">{{ count($varients['na']) }}</p>
                    </div>
                </div>
            </div>
        </section>

        <footer
            style="margin-top: 2rem; pt: 1.5rem; border-top: 2px solid var(--gray-200); display: flex; justify-content: space-between; align-items: center; padding-top: 1.5rem;">
            <p style="font-size: 0.75rem; color: var(--gray-500);">Report ID: {{ $task->code }}</p>
        </footer>
    </div>

    @if(count($sectionWise) > 0)
    <div id="report-container">
        <header id="header">
            <div class="header-top">
                <div>
                    <div class="header-title-group">
                        <div class="accent-bar"></div>
                        <h1
                            style="font-size: 1.875rem; font-weight: 700; color: var(--gray-900); letter-spacing: -0.025em;">
                            Section-wise Performance</h1>
                    </div>
                </div>
            </div>
        </header>

        <section class="performance-chart-section">
            <div class="chart-header">
                <h2 style="font-size: 1.25rem; font-weight: 600; color: var(--gray-900);">Performance by Section</h2>
                <div class="legend">
                    <div class="legend-item"><span class="dot" style="background: var(--failure);"></span> Fail
                        (&le;80%)</div>
                    <div class="legend-item"><span class="dot" style="background: var(--teapost-primary);"></span>
                        Pass (&gt;80%)</div>
                </div>
            </div>

            <div class="bar-chart">
                @foreach ($sectionGroup as $sectionGroupRow)
                    <div class="chart-row">
                        <div class="row-label">{{ html_entity_decode($sectionGroupRow['title']) }}</div>
                        <div class="bar-container">
                            <div class="bar-fill" style="background: {{ $sectionGroupRow['color'] }}; --final-width: {{ $sectionGroupRow['percentage'] }}%;">
                                <span>{{ number_format($sectionGroupRow['percentage'], 2) }}%</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
    @endif

    <div id="report-container">
        <header id="header">
            <div class="header-top">
                <div>
                    <div class="header-title-group">
                        <div class="accent-bar"></div>
                        <h1 style="font-size: 1.875rem; font-weight: 700; color: var(--gray-900); letter-spacing: -0.025em;"> Critical Risk Summary </h1>
                    </div>
                </div>
            </div>
        </header>

        <div class="risk-section">
            <div class="risk-grid">
            
            @forelse ($groupedData as $className => $fields)
                @if(isset($fields[0]) && is_object($fields[0]) && property_exists($fields[0], 'className') && $fields[0]->className && strpos(strtolower($fields[0]->className), 'crtcl') !== false)
                    @php
                        $valueLabel = collect($fields)
                        ->first(function ($item) {
                            return isset($item->type) && $item->type === 'radio-group';
                        })
                        ->value_label ?? null;
                    @endphp

                    @if($valueLabel != 'Pass')
                        <div class="risk-card major" style="margin-bottom:8px;">
                            <div class="card-header">
                                <h3 style="font-size: 1rem; font-weight: 600; line-height: 1.3;"> {!! Helper::getQuestionField($fields) ?? '' !!} </h3>
                                <span class="badge">{{ $valueLabel }}</span>
                            </div>
                        </div>
                    @endif
                @else
                    @continue
                @endif
            @empty
            @endforelse

            </div>
        </div>


    </div>

    <div id="report-container">
        <div class="container-for-data">
            <div class="">
                <table class="table table-bordered table-stripped gallery">
                    <tbody>
                        @forelse ($groupedData as $className => $fields)
                            <tr>
                                @php
                                    $label = Helper::getQuestionField($fields);
                                @endphp
                                <td>{!! $label !!}</td>

                                @foreach ($fields as $field)
                                    @if (property_exists($field, 'isFile') && $field->isFile)
                                        @if (is_array($field->value))
                                            <td>
                                                @foreach ($field->value as $thisImg)
                                                    @php
                                                        $tImage = str_replace(
                                                            'assets/app/public/workflow-task-uploads/',
                                                            '',
                                                            $thisImg,
                                                        );
                                                        $hasImages = true;
                                                    @endphp
                                                    <img data-index="{{ $globalCounter->value++ }}" class="thumbnail"
                                                        src="{{ asset("storage/workflow-task-uploads/{$tImage}") }}"
                                                        style="height: 100px;width:100px;object-fit:cover;">
                                                @endforeach
                                            </td>
                                        @else
                                            <td>
                                                @php
                                                    $tImage = str_replace(
                                                        'assets/app/public/workflow-task-uploads/',
                                                        '',
                                                        $field->value,
                                                    );
                                                    $hasImages = true;
                                                @endphp
                                                <img data-index="{{ $globalCounter->value++ }}" class="thumbnail"
                                                    src="{{ asset("storage/workflow-task-uploads/{$tImage}") }}"
                                                    style="height: 100px;width:100px;object-fit:cover;">
                                            </td>
                                        @endif
                                    @else
                                        @if (property_exists($field, 'value_label'))
                                            @if ($isPointChecklist)
                                                @if (is_array($field->value_label))
                                                    <td> {!! implode(',', $field->value_label) !!} </td>
                                                @else
                                                    <td> {!! $field->value_label !!}
                                                        ({{ is_array($field->value) ? implode(',', $field->value) : $field->value }})
                                                    </td>
                                                @endif
                                            @else
                                                @if (is_array($field->value_label))
                                                    <td> {!! implode(',', $field->value_label) !!} </td>
                                                @else
                                                    <td> {!! $field->value_label !!}
                                                        {{ is_array($field->value) ? implode(',', $field->value) : $field->value }}
                                                    </td>
                                                @endif
                                            @endif
                                        @else
                                            @if (is_array($field->value))
                                                <td> {!! implode(',', $field->value) !!} </td>
                                            @else
                                                <td> {!! $field->value !!} </td>
                                            @endif
                                        @endif
                                    @endif
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td>
                                    No Data Found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                
                @if ($isPointChecklist)
                    <table class="table table-striped table-bordered">
                        <tbody>
                            <tr>
                                <td>Total Questions</td>
                                <td>{{ $total }}</td>
                            </tr>
                            <tr>
                                <td>Passed</td>
                                <td>{{ $achieved }}</td>
                            </tr>
                            <tr>
                                <td>Failed</td>
                                <td>{{ count($varients['negative']) }}</td>
                            </tr>
                            <tr>
                                <td>N/A</td>
                                <td>{{ count($varients['na']) }}</td>
                            </tr>
                            <tr>
                                <td>Percentage</td>
                                <td>{{ number_format($percentage, 2) }}%</td>
                            </tr>
                            <tr>
                                <td>Final Result</td>
                                <td>{{ $percentage > $ptp ? 'Pass' : 'Fail' }}</td>
                            </tr>
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>


    @if ($hasImages)
        <div class="lightbox">
            <span class="close">&times;</span>
            <button class="prev">&#10094;</button>
            <img id="lightbox-img" src="">
            <button class="next">&#10095;</button>
            <div class="controls">
                <button class="btn btn-sm btn-secondary" id="zoom-in">Zoom In</button>
                <button class="btn btn-sm btn-secondary" id="zoom-out">Zoom Out</button>
                <button class="btn btn-sm btn-secondary" id="download">Download</button>
            </div>
        </div>
    @endif
@endsection


@push('js')
    <script>
        $(document).ready(function($) {

            let currentIndex = 0;
            let scale = 1;
            let isDragging = false;
            let startX = 0,
                startY = 0;
            let moveX = 0,
                moveY = 0;
            let images = $(".thumbnail").map(function() {
                return $(this).attr("src");
            }).get();

            function showLightbox(index) {
                currentIndex = index;
                scale = 1;
                resetImage();
                $("#lightbox-img").attr("src", images[currentIndex]);
                $(".lightbox").fadeIn();
                updateNavButtons();
            }

            function updateNavButtons() {
                $(".prev").toggle(currentIndex > 0);
                $(".next").toggle(currentIndex < images.length - 1);
            }

            $(".thumbnail").click(function() {
                showLightbox($(this).data('index'));
            });

            $(".close").click(function() {
                $(".lightbox").fadeOut();
            });

            $(".prev").click(function() {
                if (currentIndex > 0) {
                    showLightbox(currentIndex - 1);
                }
            });

            $(".next").click(function() {
                if (currentIndex < images.length - 1) {
                    showLightbox(currentIndex + 1);
                }
            });

            $("#zoom-in").click(function() {
                scale += 0.2;
                applyTransform();
                if (scale > 1) {
                    $("#lightbox-img").css("cursor", "grab");
                }
            });

            $("#zoom-out").click(function() {
                if (scale > 1) {
                    scale -= 0.2;
                    if (scale <= 1) {
                        resetImage();
                    } else {
                        applyTransform();
                    }
                }
            });

            $("#download").click(function() {
                let link = document.createElement('a');
                link.href = images[currentIndex];
                link.download = 'image.jpg';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });

            $("#lightbox-img").on("mousedown", function(e) {
                if (scale > 1) {
                    isDragging = true;
                    startX = e.clientX - moveX;
                    startY = e.clientY - moveY;
                    $(this).css("cursor", "grabbing");
                }
            });

            $(document).on("mousemove", function(e) {
                if (isDragging) {
                    moveX = e.clientX - startX;
                    moveY = e.clientY - startY;
                    applyTransform();
                }
            });

            $(document).on("mouseup", function() {
                isDragging = false;
                $("#lightbox-img").css("cursor", "grab");
            });

            function applyTransform() {
                $("#lightbox-img").css("transform", `scale(${scale}) translate(${moveX}px, ${moveY}px)`);
            }

            function resetImage() {
                scale = 1;
                moveX = 0;
                moveY = 0;
                $("#lightbox-img").css({
                    "transform": `scale(1) translate(0px, 0px)`,
                    "cursor": "default"
                });
            }

            function animate(target) {
                const scoreVal = document.getElementById('score-num');
                const gaugeVal = document.getElementById('gauge-fill');
                let current = 0;
                const dur = 1000;
                const start = performance.now();

                function step(now) {
                    const elapsed = now - start;
                    const progress = Math.min(elapsed / dur, 1);
                    const val = Math.floor(progress * target);
                    scoreVal.innerText = val;
                    gaugeVal.setAttribute('stroke-dasharray', `${val}, 100`);
                    if (progress < 1) requestAnimationFrame(step);
                }
                requestAnimationFrame(step);
            }

            window.onload = () => {
                animate({{ $percentage }})
                const bars = document.querySelectorAll('.bar-fill');
                bars.forEach(bar => {
                    const finalWidth = bar.style.getPropertyValue('--final-width');
                    setTimeout(() => {
                        bar.style.width = finalWidth;
                    }, 100);
                });
            };
        });
    </script>
@endpush