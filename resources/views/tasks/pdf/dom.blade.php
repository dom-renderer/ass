<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Tea Post Inspection Report - {{ $task->code ?? '-' }} </title>
    <style>
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

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', sans-serif;
            margin: 0;
            padding: 0;
        }

        .header {
            background-color: #174C3C;
            color: #fff;
            padding: 20px;
            text-align: center;
            border-radius: 5px;
            position: relative;
            height: 32px;
        }

        .header img {
            width: 50px;
            height: auto;
            position: absolute;
            left: 20px;
            top: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .header p {
            margin: 0;
            font-size: 15px;
            float: right;
            position: relative;
            bottom: 23px;
        }

        .summary {
            display: flex;
            justify-content: space-between;
            background-color: #e8f5e9;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .summary div {
            flex: 1;
            text-align: center;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        th,
        td {
            text-align: left;
        }

        table.old-design th,
        table.old-design td {
            border: 1px solid #ddd;
            padding: 12px;
        }

        th {
            background-color: #174C3C;
            color: white;
            text-transform: uppercase;
        }

        .pass {
            background-color: #c8e6c9;
        }

        .fail {
            background-color: #ffccbc;
        }

        .bolder {
            font-weight: bold;
        }
    </style>

    <style>
        .header-table {
            width: 100%;
            padding-bottom: 20px;
        }

        .report-tag {
            background-color: #0d5c36;
            color: white;
            padding: 4px 10px;
            font-size: 12px;
            font-weight: bold;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 10px;
        }

        .title {
            font-size: 32px;
            font-weight: bold;
            margin: 5px 0;
        }

        .subtitle {
            color: #777;
            font-size: 16px;
        }

        .store-info {
            background-color: #f8f9fa;
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 15px;
            text-align: right;
        }

        /* Meta Info (Date/Auditor) */
        .meta-table {
            width: 100%;
        }

        .meta-box {
            background-color: #fcfcfc;
            border-radius: 5px;
            font-size: 14px;
            padding: 10px 0px;
        }

        /* Main Dashboard Layout */
        .main-container {
            width: 100%;
        }

        .compliance-col {
            width: 35%;
            vertical-align: top;
        }

        .stats-col {
            width: 65%;
            vertical-align: top;
            padding-left: 20px;
        }

        /* Compliance Circle Box */
        .score-card {
            background-color: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            text-align: center;
            padding: 20px;
        }

        .score-title {
            color: #666;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 40px;
        }

        /* dompdf can't do complex CSS circles easily, so we simulate the look */
        .circle-container {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            margin: 0 auto;
            position: relative;
        }

        .circle-text {
            font-size: 38px;
            font-weight: bold;
            margin-top: 63px;
        }

        .circle-percent {
            font-size: 24px;
        }

        .status-fail {
            display: inline-block;
            margin-top: 50px;
            color: rgb(8, 189, 32);
            border: 1px solid #f5c2c7;
            background-color: #f8d7da;
            padding: 10px 30px;
            border-radius: 8px;
            font-weight: bold;
        }

        .status-pass {
            display: inline-block;
            margin-top: 25px;
            color: #065e2e;
            border: 1px solid #5ce299;
            background-color: #afe9c9;
            padding: 10px 30px;
            border-radius: 8px;
            font-weight: bold;
        }

        /* Stats Grid */
        .stats-table {
            width: 100%;
            border-spacing: 15px 0;
        }

        .stat-box {
            background: #fff;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            height: 130px;
        }

        .stat-label {
            font-size: 11px;
            font-weight: bold;
            color: #666;
            text-transform: uppercase;
        }

        .stat-value {
            font-size: 48px;
            font-weight: bold;
            margin-top: 10px;
        }

        /* Specific Borders */
        .border-passed {
            border: 2px solid #0d5c36;
            color: #0d5c36;
        }

        .border-failed {
            border: 1px solid #bd5008;
            color:#bd5008;
        }

        .border-critical {
            border: 2px solid #bd081c;
            color: #bd081c;
        }

        .border-na {
            border: 2px solid grey;
            color: grey;
        }

        /* Key Insights Box */
        .insights-box {
            background-color: #fffdf2;
            border-left: 4px solid #d39e00;
            padding: 20px;
            margin-top: 20px;
            border-radius: 4px;
        }

        .insight-title {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .insight-text {
            font-size: 14px;
            line-height: 1.4;
        }
    </style>

    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            font-size: 14px;
            margin: 0;
            padding: 20px;
        }

        /* Header Section */
        .header-table {
            width: 100%;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .title-border {
            border-left: 4px solid #1a5e32;
            padding-left: 10px;
        }

        .report-title {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }

        .report-subtitle {
            color: #777;
            font-size: 13px;
            margin-top: 5px;
        }

        .store-info {
            text-align: right;
            font-size: 12px;
        }

        /* Audit Info Bar */
        .audit-info-bar {
            background-color: #f8f9fa;
            padding: 10px;
            margin-bottom: 30px;
            width: 100%;
        }

        /* Chart Section */
        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .legend {
            text-align: right;
            margin-bottom: 15px;
        }

        .legend-item {
            display: inline-block;
            font-size: 11px;
            margin-left: 15px;
        }

        .dot {
            height: 10px;
            width: 10px;
            display: inline-block;
            margin-right: 5px;
        }

        /* Performance Table */
        .performance-table {
            width: 100%;
            border-collapse: collapse;
        }

        .performance-table td {
            padding: 8px 0;
            vertical-align: middle;
        }

        .label-cell {
            width: 20%;
            text-align: right;
            padding-right: 15px !important;
            font-weight: bold;
            font-size: 13px;
        }

        .bar-container {
            width: 80%;
            background-color: #f2f2f2;
            height: 35px;
            position: relative;
        }

        .bar-fill {
            height: 35px;
            text-align: right;
            border-top-left-radius: 7px;
            border-bottom-left-radius: 7px;
        }

        .percentage-text {
            color: white;
            font-weight: bold;
            font-size: 12px;
            padding-right: 10px;
            line-height: 25px;
        }

        /* Priority Box */
        .priority-box {
            background-color: #fff0f1;
            border: 1px solid #fcc;
            border-left: 5px solid #d30d2a;
            padding: 15px;
            margin-top: 30px;
        }

        .priority-title {
            color: #000;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .priority-text {
            font-size: 12px;
            color: #444;
        }

        .highlight-red {
            color: #d30d2a;
            font-weight: bold;
        }
    </style>

    <style>
        .section-header {
            margin-top: 30px;
            margin-bottom: 15px;
            clear: both;
        }

        .icon-box {
            display: inline-block;
            width: 32px;
            height: 32px;
            background-color: #e8f5e9;
            border-radius: 6px;
            text-align: center;
            vertical-align: middle;
        }

        .section-title {
            display: inline-block;
            font-size: 18px;
            font-weight: bold;
            margin-left: 10px;
            vertical-align: middle;
        }

        /* Grid Table for Cards */
        .card-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 15px 0;
            margin-left: -15px;
            /* Offset spacing */
        }

        .card-cell {
            width: 50%;
            vertical-align: top;
            padding-bottom: 20px;
        }

        /* Risk Card Styling */
        .risk-card {
            background-color: #fff9f9;
            /* Light pink/red background */
            border-left: 4px solid #9e0b21;
            padding: 15px;
            position: relative;
            min-height: 120px;
        }

        .risk-card.warning {
            background-color: #fffaf5;
            border-left-color: #b35924;
        }

        /* Card Header */
        .risk-title {
            font-size: 14px;
            font-weight: bold;
            margin-right: 60px;
            margin-bottom: 2px;
        }

        .risk-location {
            font-size: 12px;
            color: #666;
            margin-bottom: 8px;
        }

        /* Critical Badge */
        .badge {
            background-color: #9e0b21;
            color: white;
            font-size: 10px;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 4px;
            float: right;
            text-transform: uppercase;
        }

        .divider {
            border-top: 1px solid #f2d7d7;
            margin: 10px 0;
        }

        /* Remark Section */
        .remark-label {
            font-size: 12px;
            font-weight: bold;
        }

        .remark-text {
            font-size: 12px;
            color: #333;
            line-height: 1.4;
        }

        .qc-table {
            width: 100%;
            border-collapse: collapse;
        }

        .qc-cell {
            width: 50%;
            padding: 8px;
            vertical-align: top;
        }

        .qc-card {
            background-color: #fff4eb;
            border-left: 5px solid #d35400;
            padding: 12px 14px;
            border-radius: 4px;
        }

        .qc-text {
            color: #000000;
            font-weight: 600;
            display: inline-block;
            width: 80%;
            line-height: 1.4;
        }

        .qc-status {
            float: right;
            background-color: #c0392b;
            color: #ffffff;
            font-size: 10px;
            font-weight: bold;
            padding: 3px 8px;
            border-radius: 10px;
            text-transform: uppercase;
        }

        /* Optional PASS state */
        .qc-status-pass {
            background-color: #27ae60;
        }        
    </style>
</head>

<body>

    @php
        $date1 = \Carbon\Carbon::parse($task->started_at);
        $date2 = \Carbon\Carbon::parse($task->completion_date);
        $diff = $date1->diff($date2);

        $versionedForm = \App\Helpers\Helper::getVersionForm($task->version_id);
        $isPointChecklist = \App\Helpers\Helper::isPointChecklist($versionedForm);

        $sectionGroup = [];
        $iterationCount = 0;

        $sectionWise = collect($task->data ?? [])->groupBy('page')->values()->toArray();

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
                    $titleOfSection =
                        collect($versionedForm[$pKey])->where('type', 'header')->get(0)->label ??
                        'Page ' . ($pKey + 1);
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

        $groupedData = [];

        if (isset($task->parent->parent->checklist_id) && in_array($task->parent->parent->checklist_id, [106, 107])) {
            foreach ($task->data ?? [] as $item) {
                if (!isset($namesToBeIgnored[$item->name])) {
                    $groupedData[$item->className][] = $item;
                }
            }
        } else {
            foreach ($task->data ?? [] as $item) {
                $groupedData[$item->className][] = $item;
            }
        }
    @endphp

    <!-- Page 1 -->
    <table class="header-table">
        <tr>
            <td width="60%">
                <div class="report-tag"> REPORT</div>
                <div class="title">{{ $task->parent->parent->checklist->name ?? '' }}</div>
            </td>
            <td width="40%">
                <div class="store-info">
                    <div class="logo-part" style="float: right;">
                        <img src="{{ public_path('assets/logo.webp') }}" alt="Tea Post Logo" style="height:50px;">
                    </div>
                    <div class="store-part" style="padding-right:70px;">
                        <strong style="font-size: 18px;">{{ $task->parent->actuallocation->name ?? 'N/A' }}</strong><br>
                        <span style="color: #666;">Store Code:
                            <strong>{{ $task->parent->actuallocation->code ?? 'N/A' }}</strong></span><br>
                        <span style="color: #666;">Location:
                            <strong>{{ $task->parent->actuallocation->thecity->city_name ?? 'N/A' }},
                                {{ $task->parent->actuallocation->thecity->city_state ?? 'N/A' }}</strong></span>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <table class="meta-table" style="margin-bottom:15px;">
        <tr>
            <td width="33%">
                <span>
                    Start Time: <strong> {{ $date1->format('d F Y H:i') }} </strong>
                </span>
            </td>
            <td width="34%" style="text-align:center;">
                <span>
                    End Time: <strong> {{ $task->status == 1 ? '-' : $date2->format('d F Y H:i') }} </strong>
                </span>
            </td>
            <td width="33%" style="text-align:right;">
                <span class="meta-box">
                    Ops Time:
                    <strong>
                        @if ($task->status == 1)
                            -
                        @else
                            @if ($diff->d > 0)
                                {{ $diff->d }} days,
                            @endif
                            @if ($diff->h > 0)
                                {{ $diff->h }} hours,
                            @endif
                            @if ($diff->i > 0)
                                {{ $diff->i }} minutes
                            @endif
                            @if ($diff->d <= 0 && $diff->h <= 0 && $diff->i <= 0)
                                -
                            @endif
                        @endif
                    </strong>
                </span>
            </td>
        </tr>
    </table>

    <table class="meta-table" style="margin-bottom:30px;">
        <tr>
            <td width="33%">
                <span class="meta-box">
                    Audit Date: <strong>{{ date('d F Y', strtotime($task->date)) }}</strong></span>
            </td>
            <td width="34%"></td>
            <td width="33%" style="text-align:right;">
                <span class="meta-box"> Auditor: <strong>{{ $task->parent->user->employee_id ?? 'N/A' }} -
                        {{ $task->parent->user->name ?? 'N/A' }} {{ $task->parent->user->middle_name ?? 'N/A' }}
                        {{ $task->parent->user->last_name ?? 'N/A' }}</strong></span>
            </td>
        </tr>
    </table>

    <table class="main-container">
        <tr>
            <td class="compliance-col">
                <div class="score-card">
                    <div class="score-title">Overall Compliance Score</div>
                    <div class="circle-container"
                        style="@if ($finalResultData['final_result'] == 'Pass') border: 15px solid #065e2e; @else border: 15px solid #b80027; @endif">
                        <div class="circle-text">{{ $finalResultData['percentage'] }}</div>
                    </div>
                    <div class="@if ($finalResultData['final_result'] == 'Pass') status-pass @else status-fail @endif"> STATUS:
                        {{ strtoupper($finalResultData['final_result']) }}</div>
                </div>
            </td>

            <td class="stats-col">
                <table class="stats-table">
                    <tr>
                        <td width="48%">
                            <div class="stat-box" style="background-color: #f8f9fa;">
                                <div class="stat-label">Total Checks</div>
                                <div class="stat-value" style="color: #333;">
                                    {{ $finalResultData['passed'] + $finalResultData['failed'] + $finalResultData['na'] }}
                                </div>
                            </div>
                        </td>
                        <td width="4%"></td>
                        <td width="48%">
                            <div class="stat-box border-passed">
                                <div class="stat-label">Passed</div>
                                <div class="stat-value">{{ $finalResultData['passed'] }}</div>
                            </div>
                        </td>
                    </tr>
                </table>
                <table class="stats-table">
                    <tr>
                        <td width="30%">
                            <div class="stat-box border-critical">
                                <div class="stat-label">Critical Failures</div>
                                <div class="stat-value">{{ $criticalCount ?? 0 }}</div>
                            </div>
                        </td>
                        <td width="5%"></td>
                        <td width="30%">
                            <div class="stat-box border-failed">
                                <div class="stat-label">Failed</div>
                                <div class="stat-value">{{ $finalResultData['failed'] }}</div>
                            </div>
                        </td>
                        <td width="5%"></td>
                        <td width="30%">
                            <div class="stat-box border-na">
                                <div class="stat-label">N/A</div>
                                <div class="stat-value">{{ $finalResultData['na'] }}</div>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <!-- Page 2 -->

    <!-- Page 2 -->
    <table style="page-break-before:always !important;"></table>
    @if (count($sectionWise) > 0)
        <div class="section-title">Performance by Section</div>

        <div class="legend">
            <span class="legend-item"><span class="dot" style="background-color: #9e0b21;"></span> Fail (&lt;=80%)</span>
            <span class="legend-item"><span class="dot" style="background-color: #0d6032;"></span> Pass (>80%)</span>
        </div>

        <table class="performance-table">
            @foreach ($sectionGroup as $sectionGroupRow)
                <tr>
                    <td class="label-cell">{{ html_entity_decode($sectionGroupRow['title']) }}</td>
                    <td>
                        <div class="bar-container">
                            <div class="bar-fill"
                                style="width: {{ number_format($sectionGroupRow['percentage'], 2) }}%; background-color: {{ $sectionGroupRow['color'] }};">
                                <span class="percentage-text">{{ number_format($sectionGroupRow['percentage'], 2) }}%</span>
                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach
        </table>
    @endif
    <!-- Page 2 -->

    <!-- Page 3 -->
    <table style="page-break-before:always !important;"></table>
    @if (count($sectionWise) > 0)
        <div class="section-title">Critical Risk Summary</div>
        <table class="performance-table">
            <table class="qc-table">
            @foreach(collect($groupedData)->chunk(2) as $row)
                <tr>
                    @foreach($row as $item)
                        @if(isset($item[0]) && is_object($item[0]) && property_exists($item[0], 'className') && $item[0]->className && strpos(strtolower($item[0]->className), 'crtcl') !== false)
                            @php
                                $valueLabel = collect($item)
                                ->first(function ($itm) {
                                    return isset($itm->type) && $itm->type === 'radio-group';
                                })
                                ->value_label ?? null;
                            @endphp

                            @if($valueLabel != 'Pass')
                            <td class="qc-cell">
                                <div class="qc-card">
                                    <span class="qc-text">
                                        {!! Helper::getQuestionField($item) ?? '' !!}
                                    </span>

                                    <span class="qc-status {{ $valueLabel === 'Pass' ? 'qc-status-pass' : '' }}">
                                        {{ $valueLabel }}
                                    </span>
                                </div>
                            </td>
                            @endif
                        @endif
                    @endforeach

                    @if($row->count() === 1)
                        <td class="qc-cell"></td>
                    @endif
                </tr>
            @endforeach
            </table>
        </table>
    @endif
    <!-- Page 3 -->



    @php
        if (empty($data)) {
            $maxColumns = 3;
        } else {
            $maxColumns = max(array_map('count', $data));
        }
    @endphp

    {{-- FAILED ITEMS --}}
    <br><br>

    <center>
        <span class="bolder"> --- FAILED ITEMS --- </span> <br><br>
    </center>

    <table class="old-design">
        <thead>
            <tr>
                <th>Inspection Item</th>
                <th>Result</th>
                <th colspan="{{ $maxColumns - 2 }}">Remark</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $row)
                @if (is_string($row[1]) && (strtolower($row[1]) == 'no' || strtolower($row[1]) == 'fail'))
                    <tr class="fail">
                        @foreach ($row as $key => $value)
                            <td colspan="{{ $loop->last && count($row) < $maxColumns ? $maxColumns - count($row) + 1 : 1 }}"
                                style="font-weight: {{ $loop->first ? 'bold' : 'normal' }}">
                                @if (is_string($value) && strpos($value, 'SIGN-20') !== false)
                                    @php $webpToPng = str_replace(".webp", ".png", $value); @endphp
                                    @if (file_exists(storage_path("app/public/workflow-task-uploads-thumbnails/{$webpToPng}")) &&
                                            is_file(storage_path("app/public/workflow-task-uploads-thumbnails/{$webpToPng}")))
                                        <img src="{{ public_path("storage/workflow-task-uploads-thumbnails/{$webpToPng}") }}"
                                            style="height: 100px;">
                                    @else
                                        <img src="{{ public_path('no-image-found.png') }}"
                                            style="height: 100px;">
                                    @endif
                                @elseif(is_array($value))
                                    @foreach ($value as $vl)
                                        @if (strpos($vl, 'SIGN-20') !== false)
                                            @php $webpToPng = str_replace(".webp", ".png", $vl); @endphp
                                            @if (file_exists(storage_path("app/public/workflow-task-uploads-thumbnails/{$webpToPng}")) &&
                                                    is_file(storage_path("app/public/workflow-task-uploads-thumbnails/{$webpToPng}")))
                                                <img src="{{ public_path("storage/workflow-task-uploads-thumbnails/{$webpToPng}") }}"
                                                    style="height: 100px;">
                                            @else
                                                <img src="{{ public_path('no-image-found.png') }}"
                                                    style="height: 100px;">
                                            @endif
                                        @else
                                            {!! $vl !!}
                                        @endif
                                    @endforeach
                                @else
                                    {!! $value !!}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
    {{-- FAILED ITEMS --}}

    {{-- FULL REPORT --}}
    <div style="page-break-before:always !important;">
        <br>

        <center>
            <span class="bolder"> --- FULL REPORT --- </span> <br><br>
        </center>

        <table class="old-design">
            <thead>
                <tr>
                    <th>Inspection Item</th>
                    <th>Result</th>
                    <th colspan="{{ $maxColumns - 2 }}">Remark</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $row)
                    <tr
                        @if (is_string($row[1]) && (strtolower($row[1]) == 'pass' || strtolower($row[1]) == 'yes')) class="pass" 
                    @elseif(is_string($row[1]) && (strtolower($row[1]) == 'no' || strtolower($row[1]) == 'fail'))
                    class="fail" 
                    @else 
                    class="pass" @endif>
                        @foreach ($row as $key => $value)
                            <td colspan="{{ $loop->last && count($row) < $maxColumns ? $maxColumns - count($row) + 1 : 1 }}"
                                style="font-weight: {{ $loop->first ? 'bold' : 'normal' }}">
                                @if (is_string($value) && strpos($value, 'SIGN-20') !== false)
                                    @php $webpToPng = str_replace(".webp", ".png", $value); @endphp
                                    @if (file_exists(storage_path("app/public/workflow-task-uploads-thumbnails/{$webpToPng}")) &&
                                            is_file(storage_path("app/public/workflow-task-uploads-thumbnails/{$webpToPng}")))
                                        <img src="{{ public_path("storage/workflow-task-uploads-thumbnails/{$webpToPng}") }}"
                                            style="height: 100px;">
                                    @else
                                        <img src="{{ public_path('no-image-found.png') }}" style="height: 100px;">
                                    @endif
                                @elseif(is_array($value))
                                    @foreach ($value as $vl)
                                        @if (strpos($vl, 'SIGN-20') !== false)
                                            @php $webpToPng = str_replace(".webp", ".png", $vl); @endphp
                                            @if (file_exists(storage_path("app/public/workflow-task-uploads-thumbnails/{$webpToPng}")) &&
                                                    is_file(storage_path("app/public/workflow-task-uploads-thumbnails/{$webpToPng}")))
                                                <img src="{{ public_path("storage/workflow-task-uploads-thumbnails/{$webpToPng}") }}"
                                                    style="height: 100px;">
                                            @else
                                                <img src="{{ public_path('no-image-found.png') }}"
                                                    style="height: 100px;">
                                            @endif
                                        @else
                                            {!! $vl !!}
                                        @endif
                                    @endforeach
                                @else
                                    {!! $value !!}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <br><br>

    <center>
        <span class="bolder"> --- End of Report --- </span>
    </center>

</body>

</html>