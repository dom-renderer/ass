@extends('layouts.app-master')

@push('css')
    <script src="{{ asset('assets/js/tailwindcss-cdn.js') }}"></script>
    <script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['DynamicAppFont', 'sans-serif'] } } } }</script>
    <style>
        body {
            font-family: 'DynamicAppFont', sans-serif !important;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.375rem;
        }

        /* Timeline styles */
        .timeline-container {
            position: relative;
            padding: 20px 0;
        }

        .timeline-line {
            position: absolute;
            left: 30px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e5e7eb;
        }

        .timeline-date-label {
            position: relative;
            margin: 30px 0 20px 0;
            padding-left: 70px;
        }

        .timeline-date-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            color: white;
            background: #ef4444;
        }

        .timeline-date-badge.green {
            background: #22c55e;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 30px;
            padding-left: 70px;
        }

        .timeline-icon {
            position: absolute;
            left: 0;
            top: 0;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            z-index: 1;
            border: 3px solid white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .timeline-icon.blue {
            background: #3b82f6;
        }

        .timeline-icon.green {
            background: #22c55e;
        }

        .timeline-icon.orange {
            background: #f59e0b;
        }

        .timeline-icon.purple {
            background: #a855f7;
        }

        .timeline-icon.red {
            background: #ef4444;
        }

        .timeline-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
            margin-left: 10px;
        }

        .timeline-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .timeline-title {
            font-weight: 600;
            font-size: 15px;
            color: #1f2937;
            margin: 0;
        }

        .timeline-time {
            font-size: 13px;
            color: #6b7280;
        }

        .timeline-content {
            color: #4b5563;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .timeline-content-desc {
            color: #374151;
            line-height: 1.6;
            display: flex !important;
        }

        .timeline-content p {
            margin-bottom: 10px;
        }

        .timeline-attachments {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }

        .timeline-attachment {
            width: 100px;
            height: 100px;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }

        .timeline-attachment img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .timeline-empty {
            text-align: center;
            padding: 40px;
            color: #9ca3af;
        }
    </style>
@endpush

@section('content')
    @php
        use App\Models\NewTicket;
        $currentStatus = old('status', $ticket->status === NewTicket::STATUS_CLOSED ? NewTicket::STATUS_CLOSED : ($ticket->status === NewTicket::STATUS_ACCEPTED ? NewTicket::STATUS_ACCEPTED : NewTicket::STATUS_IN_PROGRESS));
    @endphp

    <div class="px-6 pt-6 pb-10">

        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
            <div>
                <h2 class="text-2xl font-semibold text-gray-800">{{ $page_title }}</h2>
                <p class="text-sm text-gray-400 mt-0.5">Read-only view of this ticket.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('ticket-management.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors shadow-sm text-decoration-none">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <div class="mt-2 mb-4">@include('layouts.partials.messages')</div>

        <div class="row g-4">

            {{-- Left Column: Ticket Content --}}
            <div class="col-lg-8">
                <div class="bg-white rounded-xl border border-[#e5e7eb] shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-ticket-detailed text-gray-400"></i>
                            <h3 class="text-sm font-semibold text-gray-800 m-0">Ticket Details</h3>
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success">
                            {{ ucwords(str_replace('_', ' ', $ticket->status)) }}
                        </span>
                    </div>
                    <div class="p-5">
                        <h5 class="mb-1 font-semibold text-gray-800">{{ $ticket->subject }}</h5>
                        <div class="text-muted small">Created on {{ date('d F, Y H:i A', strtotime($ticket->created_at)) }}
                        </div>
                        <div class="mt-3 text-muted">{!! $ticket->description !!}</div>
                        @if(!empty($ticket->attachments))
                            <div class="mt-4">
                                <label class="form-label">Images</label>
                                <div class="d-flex flex-wrap gap-3">
                                    @foreach($ticket->attachments as $image)
                                        <div class="border rounded p-2" style="width: 140px;">
                                            <a href="{{ asset('storage/' . $image) }}" target="_blank">
                                                <img src="{{ asset('storage/' . $image) }}" class="img-fluid rounded"
                                                    alt="Attachment">
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Right Column: Ticket Info + Location --}}
            <div class="col-lg-4">
                {{-- Ticket Information --}}
                <div class="bg-white rounded-xl border border-[#e5e7eb] shadow-sm overflow-hidden mb-4">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="text-sm font-semibold text-gray-800 m-0">Ticket Information</h3>
                    </div>
                    <div class="p-5">
                        <div class="row g-2 small">
                            <div class="col-6 text-gray-500">Ticket ID</div>
                            <div class="col-6 text-muted">{{ $ticket->ticket_number }}</div>
                            <div class="col-6 text-gray-500">Department</div>
                            <div class="col-6 text-muted">{{ optional($ticket->department)->name ?: '-' }}</div>
                            <div class="col-6 text-gray-500">Particulars</div>
                            <div class="col-6 text-muted">{{ optional($ticket->particular)->name ?: '-' }}</div>
                            <div class="col-6 text-gray-500">Issue</div>
                            <div class="col-6 text-muted">{{ optional($ticket->issue)->name ?: '-' }}</div>
                            <div class="col-6 text-gray-500">Assigned Users</div>
                            <div class="col-6 text-muted">
                                <strong>
                                    {!! implode('<br/>', $ticket->primaryOwners->map(function ($owner) {
        if (isset($owner->user->id)) {
            return trim($owner->user->employee_id . ' ' . $owner->user->name . ' ' . $owner->user->middle_name . ' ' . $owner->user->last_name);
        } else {
            return 'N/A'; }
    })->values()->toArray()) !!}
                                </strong>
                            </div>
                            <div class="col-6 text-gray-500">Extra Assigned Users</div>
                            <div class="col-6 text-muted">
                                <strong>
                                    {!! implode('<br/>', $ticket->secondaryOwners->map(function ($owner) {
        if (isset($owner->user->id)) {
            return trim($owner->user->employee_id . ' ' . $owner->user->name . ' ' . $owner->user->middle_name . ' ' . $owner->user->last_name);
        } else {
            return 'N/A'; }
    })->values()->toArray()) !!}
                                </strong>
                            </div>
                            <div class="col-6 text-gray-500">Ticket Priority</div>
                            <div class="col-6"><span
                                    class="badge bg-danger">{{ ucwords(str_replace('_', ' ', $ticket->priority)) }}</span>
                            </div>
                            <div class="col-6 text-gray-500">Open Date</div>
                            <div class="col-6 text-muted">{{ date('d F, Y H:i A', strtotime($ticket->created_at)) }}</div>
                            <div class="col-6 text-gray-500">Ticket Status</div>
                            <div class="col-6"><span
                                    class="badge bg-success">{{ ucwords(str_replace('_', ' ', $ticket->status)) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Location/Asset Details --}}
                <div class="bg-white rounded-xl border border-[#e5e7eb] shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="text-sm font-semibold text-gray-800 m-0">
                            @if(isset($ticket->store->type) && $ticket->store->type == 0) Location @else Asset @endif
                            Details
                        </h3>
                    </div>
                    <div class="p-5">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            @if(isset($ticket->store->type) && $ticket->store->type == 0)
                                <span class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center"
                                    style="width:64px;height:64px;"><i class="bi bi-person"
                                        style="font-size: 1.5rem;"></i></span>
                            @else
                                <span class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center"
                                    style="width:64px;height:64px;">
                                    <img src="{{ $ticket->store->pi }}"
                                        style="width:64px;height:64px;object-fit:contain;border-radius:50%;">
                                </span>
                            @endif
                            <div>
                                <div class="fw-semibold">{{ $ticket->store->code ?? '' }} {{ $ticket->store->name ?? '' }}
                                </div>
                                <div class="text-muted small">{{ $ticket->store->modeltype->name ?? '' }} |
                                    {{ $ticket->store->storetype->name ?? '' }}</div>
                            </div>
                        </div>
                        <div class="row g-2 small">
                            <div class="col-4 text-gray-500">Unique Code</div>
                            <div class="col-8 text-truncate">{{ $ticket->store->ucode ?? 'N/A' }}</div>
                            <div class="col-4 text-gray-500">Internal Code</div>
                            <div class="col-8 text-truncate">{{ $ticket->store->code ?? 'N/A' }}</div>
                            @if(isset($ticket->store->type) && $ticket->store->type == 0)
                                <div class="col-4 text-gray-500">Email</div>
                                <div class="col-8 text-truncate"><a
                                        href="mailto:{{ $ticket->store->email ?? '' }}">{{ $ticket->store->email ?? 'N/A' }}</a>
                                </div>
                                <div class="col-4 text-gray-500">Phone</div>
                                <div class="col-8 text-muted">{{ $ticket->store->mobile ?? 'N/A' }}</div>
                                <div class="col-4 text-gray-500">Whatsapp</div>
                                <div class="col-8 text-muted">{{ $ticket->store->whatsapp ?? 'N/A' }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Ticket History Timeline --}}
        <div class="bg-white rounded-xl border border-[#e5e7eb] shadow-sm overflow-hidden mt-6">
            <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center gap-2">
                <i class="bi bi-clock-history text-gray-400"></i>
                <h3 class="text-sm font-semibold text-gray-800 m-0">Ticket History</h3>
            </div>
            <div class="p-5">
                @if($histories->isEmpty())
                    <div class="timeline-empty">
                        <p class="mb-0">No history available.</p>
                    </div>
                @else
                    @php
                        $groupedHistories = $histories->groupBy(function ($history) {
                            return $history->created_at ? $history->created_at->format('Y-m-d') : 'unknown'; });
                        $currentDate = null;
                    @endphp
                    <div class="timeline-container">
                        <div class="timeline-line"></div>
                        @foreach($groupedHistories as $date => $dateHistories)
                            @php
                                $displayDate = \Carbon\Carbon::parse($date)->format('d M. Y');
                                $isFirstDate = $currentDate === null;
                                $currentDate = $date;
                            @endphp
                            <div class="timeline-date-label">
                                <span class="timeline-date-badge {{ $isFirstDate ? '' : 'green' }}">{{ $displayDate }}</span>
                            </div>
                            @foreach($dateHistories as $history)
                                @php
                                    $type = $history->type ?? 'reply';
                                    $iconClass = 'bi-envelope';
                                    $iconColor = 'blue';
                                    if ($type === 'created') {
                                        $iconClass = 'bi-plus-circle';
                                        $iconColor = 'green';
                                    } elseif ($type === 'accepted') {
                                        $iconClass = 'bi-check-circle';
                                        $iconColor = 'green';
                                    } elseif (isset($history->data['reopened']) && $history->data['reopened']) {
                                        $iconClass = 'bi-arrow-clockwise';
                                        $iconColor = 'red';
                                    } elseif ($type === 'reply') {
                                        $iconClass = 'bi-chat-dots';
                                        $iconColor = 'orange';
                                    } elseif ($type === 'closed') {
                                        $iconClass = 'bi bi-x-circle';
                                        $iconColor = 'red';
                                    }
                                    $authorName = optional($history->author)->name ?: 'System';
                                    $timeDisplay = $history->created_at ? $history->created_at->format('H:i') : '';
                                @endphp
                                <div class="timeline-item">
                                    <div class="timeline-icon {{ $iconColor }}"><i class="bi {{ $iconClass }}"></i></div>
                                    <div class="timeline-card">
                                        <div class="timeline-header">
                                            <h6 class="timeline-title">
                                                @if($type === 'created') {{ $authorName }} created this ticket
                                                @elseif($type === 'closed') {{ $authorName }} closed this ticket
                                                @elseif($type === 'accepted') {{ $authorName }} accepted this ticket
                                                @elseif(isset($history->data['reopened']) && $history->data['reopened'])
                                                    {{ $authorName }} reopened this ticket
                                                @else {{ $authorName }} replied
                                                @endif
                                            </h6>
                                            <span class="timeline-time">{{ $timeDisplay }}</span>
                                        </div>
                                        <div class="timeline-content">{!! $history->description !!}</div>
                                        @if(isset($history->data['description']) && !empty($history->data['description']))
                                            <div class="timeline-content-desc"><strong>Comment : &nbsp;&nbsp; </strong>
                                                {!! $history->data['description'] !!}</div>
                                        @endif
                                        @if(!empty($history->attachments))
                                            <div class="timeline-attachments">
                                                @foreach($history->attachments as $image)
                                                    <a href="{{ asset('storage/' . $image) }}" target="_blank" class="timeline-attachment">
                                                        <img src="{{ asset('storage/' . $image) }}" alt="History Attachment">
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection