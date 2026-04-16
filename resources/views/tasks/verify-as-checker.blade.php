@extends('layouts.app-master')

@push('css')
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

        .bg-success-1 {
            background-color: #94e494 !important;
            font-weight: bolder;
        }

        .bg-danger-1 {
            background-color: #e49494 !important;
            font-weight: bolder;
        }

        .bg-warning-1 {
            background-color: #e4d594 !important;
            font-weight: bolder;
        }

        canvas {
            border: 1px solid #000;
            touch-action: none;
        }
    </style>
@endpush


@php
    $namesToBeIgnored = array_combine(\App\Helpers\Helper::$namesToBeIgnored, \App\Helpers\Helper::$namesToBeIgnored);

    if (is_string($checkerData->data)) {
        $data = json_decode($checkerData->data, true);
    } elseif (is_array($checkerData->data)) {
        $data = $checkerData->data;
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

    $varients = \App\Helpers\Helper::categorizePoints($checkerData->data ?? []);

    $total = count(\App\Helpers\Helper::selectPointsQuestions($checkerData->data));
    $toBeCounted = $total - count($varients['na']);

    $failed = abs(count(array_column($varients['negative'], 'value')));
    $achieved = $toBeCounted - abs($failed);

    if ($failed <= 0) {
        $achieved = array_sum(array_column($varients['positive'], 'value'));
    }

    if ($toBeCounted > 0) {
        $percentage = ($achieved / $toBeCounted) * 100;
    } else {
        $percentage = 0;
    }

    $ptp =
        isset($task->parent->parent->checklist->ptp) && is_numeric($task->parent->parent->checklist->ptp)
            ? $task->parent->parent->checklist->ptp
            : 0;

    $hasImages = false;

    $globalCounter = new \stdClass();
    $globalCounter->value = 0;

    $versionedForm = \App\Helpers\Helper::getVersionForm($task->version_id);
    $isPointChecklist = \App\Helpers\Helper::isPointChecklist($versionedForm);

    $date1 = \Carbon\Carbon::parse($task->started_at);
    $date2 = \Carbon\Carbon::parse($task->completion_date);
    $diff = $date1->diff($date2);
@endphp

@section('content')

    <form action="{{ route('verify-as-checker', ['task' => encrypt($task->id), 'user' => encrypt($user->id)]) }}" method="POST"> @csrf
        <div class="bg-light p-4 rounded">

            <div class="container-for-data">
                <div class="bg-light p-4 rounded">

                    @php
                        $maxCols = max(array_map('count', $groupedData));
                    @endphp

                    <table class="table table-bordered table-stripped">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Maker</th>
                                <th>Location/Asset</th>
                                <th>Started At</th>
                                <th>Completed At</th>
                                <th>TAT</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $task->code }}</td>
                                <td>
                                    @if ($task->status == 0)
                                        Pending
                                    @elseif($task->status == 1)
                                        In-Progress
                                    @elseif($task->status == 2)
                                        @php
                                            $lastStatus = \App\Models\TaskChecker::where('task_id', $task->id)
                                                ->where('status', 1)
                                                ->orderBy('level', 'DESC')
                                                ->first();
                                            if (!$lastStatus) {
                                                $lastStatus = \App\Models\TaskChecker::where('task_id', $task->id)
                                                    ->where('status', 2)
                                                    ->orderBy('level', 'DESC')
                                                    ->first();
                                                if (!$lastStatus) {
                                                    $lastStatus = \App\Models\TaskChecker::where('task_id', $task->id)
                                                        ->where('status', 0)
                                                        ->orderBy('level', 'ASC')
                                                        ->first();
                                                }
                                            }
                                        @endphp

                                        @if ($lastStatus->status == 0)
                                            Level {{ $lastStatus->level }} : Pending-Verification
                                        @elseif($lastStatus->status == 1)
                                            Level {{ $lastStatus->level }} : Reassigned
                                        @else
                                            Level {{ $lastStatus->level }} : Verified
                                        @endif
                                    @else
                                        Verified
                                    @endif
                                </td>
                                <td>
                                    {{ date('d-m-Y H:i', strtotime($task->date)) }}
                                </td>
                                <td>
                                    {{ $task->parent->user->employee_id ?? '' }} - {{ $task->parent->user->name ?? '' }}
                                    {{ $task->parent->user->middle_name ?? '' }} {{ $task->parent->user->last_name ?? '' }}
                                </td>
                                <td>
                                    {{ $task->parent->actuallocation->code ?? 'N/A' }} -
                                    {{ $task->parent->actuallocation->name ?? 'N/A' }}
                                </td>
                                <td>{{ $date1->format('d-m-Y H:i') }}</td>
                                <td>{{ in_array($task->status, [2, 3]) ? $date2->format('d-m-Y H:i') : '' }}</td>
                                <td>
                                    @if (in_array($task->status, [0, 1]))
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
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    @if (isset($checkerData->id))
                        <table class="table table-bordered table-stripped">

                            <tbody>
                                <tr>
                                    <td>
                                        <strong>
                                            Checker
                                        </strong>
                                    </td>
                                    <td>
                                        {{ $checkerData->user->employee_id ?? '' }} - {{ $checkerData->user->name ?? '' }}
                                        {{ $checkerData->user->middle_name ?? '' }} {{ $checkerData->user->last_name ?? '' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong>
                                            Over all Remark
                                        </strong>
                                    </td>
                                    <td>
                                        <textarea @if($checkerData->status == 2) disabled @endif name="overall_remarks" class="form-control">{{ $checkerData->remarks }}</textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong>
                                            Signature
                                        </strong>
                                    </td>
                                    <td>
                                        @if($checkerData->signature)
                                            <a href="{{ $checkerData->signature }}" target="_blank">
                                                <img src="{{ $checkerData->signature }}" style="height: 130px;width:130px;">
                                            </a>
                                        @else
                                            <canvas id="sig" width="400" height="200"></canvas><br>
                                            <button type="button" class="btn btn-sm btn-secondary" id="clear-signature">Clear</button>
                                            <input type="hidden" name="signature" id="signature-input">
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    @endif

                    <table class="table table-bordered table-stripped gallery">
                        <thead>
                            <tr>
                                @for ($i = 0; $i <= $maxCols; $i++)
                                    @if ($i == 0)
                                        <th width="20%">
                                            Inspection Item
                                        </th>
                                    @elseif($i == 1)
                                        <th>
                                            Maker Submission
                                        </th>
                                    @else
                                        <th></th>
                                    @endif
                                @endfor
                                <th>Checker Result</th>
                                <th>Checker Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($groupedData as $className => $fields)
                                <tr>
                                    @php
                                        $label = Helper::getQuestionField($fields);
                                        $thisField = collect(\App\Helpers\Helper::$priorityTypes)
                                            ->map(fn($type) => collect($fields)->firstWhere('type', $type))
                                            ->filter()
                                            ->first();
                                        $thisField = is_object($thisField) ? $thisField : new \stdClass();
                                        $badge = strtoupper(
                                            property_exists($thisField, 'approved') && $thisField->approved == 'yes'
                                                ? 'Approved'
                                                : (property_exists($thisField, 'approved') && $thisField->approved == 'no'
                                                    ? 'Disapproved'
                                                    : ''),
                                        );
                                        $rowClassName =
                                            $badge == 'APPROVED'
                                                ? 'bg-success-1'
                                                : ($badge == 'DISAPPROVED'
                                                    ? 'bg-danger-1'
                                                    : '');
                                    @endphp
                                    <td class="{{ $rowClassName }}">{!! $label !!}</td>

                                    @foreach ($fields as $field)
                                        @if (property_exists($field, 'isFile') && $field->isFile)
                                            @if (is_array($field->value))
                                                <td class="{{ $rowClassName }}">
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
                                                <td class="{{ $rowClassName }}">
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
                                                        <td class="{{ $rowClassName }}"> {!! implode(',', $field->value_label) !!} </td>
                                                    @else
                                                        <td class="{{ $rowClassName }}"> {!! $field->value_label !!}
                                                            ({{ is_array($field->value) ? implode(',', $field->value) : $field->value }})
                                                        </td>
                                                    @endif
                                                @else
                                                    @if (is_array($field->value_label))
                                                        <td class="{{ $rowClassName }}"> {!! implode(',', $field->value_label) !!} </td>
                                                    @else
                                                        <td class="{{ $rowClassName }}"> {!! $field->value_label !!}
                                                            {{ is_array($field->value) ? implode(',', $field->value) : $field->value }}
                                                        </td>
                                                    @endif
                                                @endif
                                            @else
                                                @if (is_array($field->value))
                                                    <td class="{{ $rowClassName }}"> {!! implode(',', $field->value) !!} </td>
                                                @else
                                                    <td class="{{ $rowClassName }}"> {!! $field->value !!} </td>
                                                @endif
                                            @endif
                                        @endif
                                    @endforeach

                                    {{-- Pad missing columns --}}
                                    @for ($i = count($fields); $i < $maxCols; $i++)
                                        <td class="{{ $rowClassName }}"></td>
                                    @endfor

                                    {{-- Two extra columns at end --}}
                                    @php
                                        $nameAttributeName = 'name_' . ($thisField->name ?? '');

                                        if (is_object($thisField) && property_exists($thisField, 'type') && property_exists($thisField, 'className') && in_array($thisField->type, ['header', 'paragraph'])) {
                                            $nameAttributeName = 'className_' . ($thisField->className ?? '');
                                        }
                                    @endphp

                                    <td class="{{ $rowClassName }}">
                                        @if($checkerData->status == 2) 
                                            @if(property_exists($thisField, 'approved') && $thisField->approved == 'yes')
                                                Approved
                                            @elseif(property_exists($thisField, 'approved') && $thisField->approved == 'no')
                                                Disapproved
                                            @endif
                                        @else
                                            <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                                                <input type="radio" class="btn-check"
                                                    name="field[{{ $nameAttributeName }}]"
                                                    id="btnradio1-{{ $loop->index }}" autocomplete="off" value="yes"
                                                    @if (property_exists($thisField, 'approved') && $thisField->approved == 'yes') checked @endif>
                                                <label class="btn btn-outline-primary"
                                                    for="btnradio1-{{ $loop->index }}">Approve</label>

                                                <input type="radio" class="btn-check"
                                                    name="field[{{ $nameAttributeName }}]"
                                                    id="btnradio2-{{ $loop->index }}" autocomplete="off" value="no"
                                                    @if (property_exists($thisField, 'approved') && $thisField->approved == 'no') checked @endif>
                                                <label class="btn btn-outline-primary"
                                                    for="btnradio2-{{ $loop->index }}">Disapprove</label>
                                            </div>                                        
                                        @endif
                                    </td>
                                    <td class="{{ $rowClassName }}">
                                        @if(property_exists($thisField, 'approved') && $thisField->approved == 'yes')
                                            {{ property_exists($thisField, 'remarks') ? $thisField->remarks : '' }}
                                        @else
                                            <textarea @if($checkerData->status == 2) disabled @endif name="remarks[{{ $nameAttributeName }}]" class="form-control">{{ property_exists($thisField, 'remarks') ? $thisField->remarks : '' }}</textarea>
                                        @endif
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td>No Data Found</td>
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
                                    <td>{{ $percentage >= $ptp ? 'Pass' : 'Fail' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    @endif

                    @if($checkerData->status != 2)
                    <button type="submit" class="btn btn-primary w-100"> Verify </button>
                    @endif
                </div>
            </div>

        </div>
    </form>


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

            const canvas = document.getElementById("sig");
            const ctx = canvas.getContext("2d");

            let drawing = false;

            function getPos(e) {
            const rect = canvas.getBoundingClientRect();
            if (e.touches) {
                return {
                x: e.touches[0].clientX - rect.left,
                y: e.touches[0].clientY - rect.top
                };
            }
            return {
                x: e.clientX - rect.left,
                y: e.clientY - rect.top
            };
            }

            function start(e) {
            drawing = true;
            const pos = getPos(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
            }

            function draw(e) {
            if (!drawing) return;
            e.preventDefault();
            const pos = getPos(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
            }

            function stop() {
                drawing = false;
                document.getElementById('signature-input').value = canvas.toDataURL();
            }

            canvas.addEventListener("mousedown", start);
            canvas.addEventListener("mousemove", draw);
            canvas.addEventListener("mouseup", stop);
            canvas.addEventListener("mouseleave", stop);

            canvas.addEventListener("touchstart", start);
            canvas.addEventListener("touchmove", draw);
            canvas.addEventListener("touchend", stop);

            $('#clear-signature').on('click', function () {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                document.getElementById('signature-input').value = '';
            });

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

        });
    </script>
@endpush
