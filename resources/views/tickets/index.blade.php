@extends('layouts.app-master')

@push('css')
    <script src="{{ asset('assets/js/tailwindcss-cdn.js') }}"></script>
    <script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['DynamicAppFont', 'sans-serif'] } } } }</script>
    <link rel="stylesheet" href="{{ asset('assets/css/custom-select-style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/jquery.datetimepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/datatables/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/datatables/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/daterangepicker.css') }}">
    <style type="text/css">
        body {
            font-family: 'DynamicAppFont', sans-serif !important;
        }

        .dataTables_wrapper .dataTables_length select {
            border: 1px solid #e5e7eb;
            border-radius: 0.375rem;
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #e5e7eb;
            border-radius: 0.375rem;
            padding: 0.25rem 0.75rem;
            font-size: 0.875rem;
            outline: none;
        }

        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 1px #3b82f6;
        }

        table.dataTable {
            border-collapse: collapse !important;
            margin-top: 0 !important;
            margin-bottom: 0 !important;
            width: 100% !important;
        }

        table.dataTable thead th {
            border-bottom: 1px solid #f3f4f6 !important;
            border-top: none !important;
            color: #6b7280;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 0.05em;
            padding: 0.75rem 1rem !important;
            background-color: #f9fafb;
        }

        table.dataTable tbody td {
            border-bottom: 1px solid #f3f4f6 !important;
            padding: 0.75rem 1rem !important;
            vertical-align: middle;
            color: #374151;
            font-size: 0.8rem;
        }

        table.dataTable tbody tr:hover {
            background-color: #f9fafb !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0 !important;
            margin: 0 !important;
            border: none !important;
        }

        .nav-tabs .nav-link {
            font-size: 0.875rem;
            font-weight: 500;
            color: #6b7280;
            border: none;
            padding: 0.75rem 1rem;
        }

        .nav-tabs .nav-link.active {
            color: #2563eb;
            border-bottom: 2px solid #2563eb;
            background: transparent;
        }

        .nav-tabs .nav-link:hover {
            color: #2563eb;
        }

        .form-label {
            font-size: 0.8rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.25rem;
        }
    </style>
@endpush

@section('content')
    <div class="px-6 pt-6 pb-4">

        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
            <div>
                <h2 class="text-2xl font-semibold text-gray-800">{{ $page_title }}</h2>
                <p class="text-sm text-gray-400 mt-0.5">Track and manage all tickets across departments.</p>
            </div>
            <div class="flex items-center gap-3 mt-4 md:mt-0">
                <button id="exportTicketsBtn"
                    class="inline-flex items-center gap-2 px-4 py-2 border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors shadow-sm">
                    <i class="bi bi-download"></i> Export
                </button>
                @can('ticket-management.create')
                    <a href="{{ route('ticket-management.create') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm text-decoration-none">
                        <i class="bi bi-plus-lg"></i> Create Ticket
                    </a>
                @endcan
            </div>
        </div>

        <div class="mt-2 mb-4">@include('layouts.partials.messages')</div>

        {{-- Filters Card --}}
        <div class="bg-white rounded-xl border border-[#e5e7eb] shadow-sm overflow-hidden mb-6">
            <div class="accordion accordion-flush" id="accordionFlushExample">
                <div class="accordion-item" style="border:none;">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed px-5 py-3 text-sm font-semibold text-gray-700"
                            type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne"
                            aria-expanded="false" aria-controls="flush-collapseOne">
                            <i class="bi bi-funnel me-2 text-gray-400"></i> Filters
                        </button>
                    </h2>
                    <div id="flush-collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionFlushExample">
                        <div class="accordion-body px-5 pb-5">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label for="filterDepartment" class="form-label">Department</label>
                                    <select id="filterDepartment"></select>
                                </div>
                                <div class="col-md-3">
                                    <label for="filterParticular" class="form-label">Particular</label>
                                    <select id="filterParticular"></select>
                                </div>
                                <div class="col-md-3">
                                    <label for="filterIssue" class="form-label">Issue</label>
                                    <select id="filterIssue"></select>
                                </div>
                                <div class="col-md-3">
                                    <label for="filterStore" class="form-label">Location/Asset</label>
                                    <select id="filterStore"></select>
                                </div>
                            </div>
                            <div class="row g-3 mt-1">
                                <div class="col-md-3">
                                    <label for="filterCreatedBy" class="form-label">Ticket Created By</label>
                                    <select id="filterCreatedBy"></select>
                                </div>
                                <div class="col-md-3">
                                    <label for="filterAssignedTo" class="form-label">Ticket Assigned To</label>
                                    <select id="filterAssignedTo"></select>
                                </div>
                                <div class="col-md-3">
                                    <label for="task-date-range-picker" class="form-label">Date</label>
                                    <input type="text" id="task-date-range-picker" class="form-control" readonly />
                                </div>
                                <div class="col-md-2 d-flex align-items-end gap-2">
                                    <button id="filter-data"
                                        class="inline-flex items-center gap-1 px-4 py-2 bg-gray-800 hover:bg-gray-900 text-white text-sm font-medium rounded-lg transition-colors">Search</button>
                                    <button id="resetFilters"
                                        class="inline-flex items-center gap-1 px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-lg transition-colors d-none">Clear</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabs + Tables Card --}}
        <div class="bg-white rounded-xl border border-[#e5e7eb] shadow-sm overflow-hidden">
            <div class="px-5 border-b border-gray-100">
                <ul class="nav nav-tabs" id="ticketTabs" role="tablist" style="border-bottom: none;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending"
                            type="button" role="tab" aria-controls="pending" aria-selected="true">Pending <span
                                id="pending-count">0</span></button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="active-tab" data-bs-toggle="tab" data-bs-target="#active" type="button"
                            role="tab" aria-controls="active" aria-selected="false">Accepted <span
                                id="accepted-count">0</span></button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="inprogress-tab" data-bs-toggle="tab" data-bs-target="#inprogress"
                            type="button" role="tab" aria-controls="inprogress" aria-selected="false">In Progress <span
                                id="inprogress-count">0</span></button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="closed-tab" data-bs-toggle="tab" data-bs-target="#closed" type="button"
                            role="tab" aria-controls="closed" aria-selected="false">Closed <span
                                id="closed-count">0</span></button>
                    </li>
                </ul>
            </div>
            <div class="tab-content p-0" id="ticketTabsContent">
                <div class="tab-pane fade show active" id="pending" role="tabpanel" aria-labelledby="pending-tab">
                    <div class="table-responsive">
                        <table class="table table-striped" id="pendingTicketsTable" style="width:100%;">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Subject</th>
                                    <th>Department</th>
                                    <th>Particular</th>
                                    <th>Issue</th>
                                    <th>Location</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th>Created At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade" id="inprogress" role="tabpanel" aria-labelledby="inprogress-tab">
                    <div class="table-responsive">
                        <table class="table table-striped" id="inprogressTicketsTable" style="width:100%;">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Subject</th>
                                    <th>Department</th>
                                    <th>Particular</th>
                                    <th>Issue</th>
                                    <th>Location</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th>Created At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade" id="active" role="tabpanel" aria-labelledby="active-tab">
                    <div class="table-responsive">
                        <table class="table table-striped" id="activeTicketsTable" style="width:100%;">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Subject</th>
                                    <th>Department</th>
                                    <th>Particular</th>
                                    <th>Issue</th>
                                    <th>Location</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th>Created At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade" id="closed" role="tabpanel" aria-labelledby="closed-tab">
                    <div class="table-responsive">
                        <table class="table table-striped" id="closedTicketsTable" style="width:100%;">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Subject</th>
                                    <th>Department</th>
                                    <th>Particular</th>
                                    <th>Issue</th>
                                    <th>Location</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th>Created At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Assign Users Modal --}}
    <div class="modal fade" id="assignUsersModal" tabindex="-1" aria-labelledby="assignUsersModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="assignUsersModalLabel">Accept & Assign</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="assignTicketEncryptedId" />
                    <div class="mb-3">
                        <label for="assign_users" class="form-label">Assign Users</label>
                        <select id="assign_users" multiple style="width: 100%"></select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" id="assignUsersSaveBtn" class="btn btn-primary">Save</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="{{ asset('assets/js/jquery.datetimepicker.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/js/moment.min.js') }}"></script>
    <script src="{{ asset('assets/js/daterangepicker.min.js') }}"></script>
    <script>

        const csrfToken = "{{ csrf_token() }}";
        var startTaskDate = moment().startOf('month');
        var endTaskDate = moment().endOf('month');

        $(document).ready(function ($) {

            function cb(start, end) {
                $('#task-date-range-picker').val(start.format('DD-MM-YYYY') + ' - ' + end.format('DD-MM-YYYY'));
            }

            $('#task-date-range-picker').daterangepicker({
                startDate: startTaskDate,
                endDate: endTaskDate,
                locale: { format: 'DD-MM-YYYY' },
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                }
            }, cb);

            cb(startTaskDate, endTaskDate);

            $('#task-date-range-picker').on('apply.daterangepicker', function (ev, picker) {
                startTaskDate = picker.startDate;
                endTaskDate = picker.endDate
            });

            const departmentFilter = $('#filterDepartment').select2({
                placeholder: 'Please Select', allowClear: true, width: '100%', theme: 'classic',
                ajax: {
                    url: "{{ route('departments-list') }}", type: 'POST', dataType: 'json', delay: 250,
                    data: function (params) { return { searchQuery: params.term, page: params.page || 1, _token: csrfToken }; },
                    processResults: function (data, params) { params.page = params.page || 1; return { results: $.map(data.items, function (item) { return { id: item.id, text: item.text }; }), pagination: { more: data.pagination.more } }; },
                    cache: true
                }
            });

            const particularFilter = $('#filterParticular').select2({
                placeholder: 'Please Select', allowClear: true, width: '100%', theme: 'classic',
                ajax: {
                    url: "{{ route('particulars-list') }}", type: 'POST', dataType: 'json', delay: 250,
                    data: function (params) { return { searchQuery: params.term, page: params.page || 1, department_id: function () { return $('#filterDepartment').val(); }, select2: 'particulars', _token: csrfToken }; },
                    processResults: function (data, params) { params.page = params.page || 1; return { results: $.map(data.items, function (item) { return { id: item.id, text: item.text }; }), pagination: { more: data.pagination.more } }; },
                    cache: true
                }
            });

            const issueFilter = $('#filterIssue').select2({
                placeholder: 'Please Select', allowClear: true, width: '100%', theme: 'classic',
                ajax: {
                    url: "{{ route('issues-list') }}", type: 'POST', dataType: 'json', delay: 250,
                    data: function (params) { return { searchQuery: params.term, page: params.page || 1, particular_id: function () { return $('#filterParticular').val(); }, _token: csrfToken }; },
                    processResults: function (data, params) { params.page = params.page || 1; return { results: $.map(data.items, function (item) { return { id: item.id, text: item.text }; }), pagination: { more: data.pagination.more } }; },
                    cache: true
                }
            });

            const storeFilter = $('#filterStore').select2({
                placeholder: 'Please Select', allowClear: true, width: '100%', theme: 'classic',
                ajax: {
                    url: "{{ route('stores-list') }}", type: 'POST', dataType: 'json', delay: 250,
                    data: function (params) { return { searchQuery: params.term, page: params.page || 1, assetswloc: 1, _token: csrfToken }; },
                    processResults: function (data, params) { params.page = params.page || 1; return { results: $.map(data.items, function (item) { return { id: item.id, text: item.text }; }), pagination: { more: data.pagination.more } }; },
                    cache: true
                }
            });

            const ticketAssignedTo = $('#filterAssignedTo').select2({
                placeholder: 'Please Select', allowClear: true, width: '100%', theme: 'classic',
                ajax: {
                    url: "{{ route('users-list') }}", type: 'POST', dataType: 'json', delay: 250,
                    data: function (params) { return { searchQuery: params.term, page: params.page || 1, ignoreDesignation: 1, _token: csrfToken }; },
                    processResults: function (data, params) { params.page = params.page || 1; return { results: $.map(data.items, function (item) { return { id: item.id, text: item.text }; }), pagination: { more: data.pagination.more } }; },
                    cache: true
                }
            });

            const createdByFilter = $('#filterCreatedBy').select2({
                placeholder: 'Please Select', allowClear: true, width: '100%', theme: 'classic',
                ajax: {
                    url: "{{ route('users-list') }}", type: 'POST', dataType: 'json', delay: 250,
                    data: function (params) { return { searchQuery: params.term, page: params.page || 1, ignoreDesignation: 1, _token: csrfToken }; },
                    processResults: function (data, params) { params.page = params.page || 1; return { results: $.map(data.items, function (item) { return { id: item.id, text: item.text }; }), pagination: { more: data.pagination.more } }; },
                    cache: true
                }
            });

            departmentFilter.on('change', function () { particularFilter.val(null).trigger('change'); issueFilter.val(null).trigger('change'); });
            particularFilter.on('change', function () { issueFilter.val(null).trigger('change'); });

            $('#resetFilters').on('click', function () {
                departmentFilter.val(null).trigger('change');
                particularFilter.val(null).trigger('change');
                storeFilter.val(null).trigger('change');
                issueFilter.val(null).trigger('change');
                createdByFilter.val(null).trigger('change');
                ticketAssignedTo.val(null).trigger('change');
                reloadTables();
                $('#resetFilters').addClass('d-none');
            });

            $(document).on('click', '#filter-data', function () {
                reloadTables();
                $('#resetFilters').removeClass('d-none');
            });

            const tableOptions = function (tab) {
                return {
                    processing: true, serverSide: true, searching: true, lengthChange: true, pageLength: 50,
                    ajax: {
                        url: "{{ route('ticket-management.index') }}",
                        data: function (d) {
                            d.tab = tab;
                            d.department_id = $('#filterDepartment').val();
                            d.particular_id = $('#filterParticular').val();
                            d.issue_id = $('#filterIssue').val();
                            d.created_from = startTaskDate.format('DD-MM-YYYY');
                            d.created_to = endTaskDate.format('DD-MM-YYYY');
                            d.created_by = $('#filterCreatedBy').val();
                            d.assigned = $('#filterAssignedTo').val();
                            d.location = $('#filterStore').val();
                        }
                    },
                    columns: [
                        { data: 'ticket_number', name: 'ticket_number' },
                        { data: 'subject', name: 'subject' },
                        { data: 'department', name: 'department', orderable: false, searchable: false },
                        { data: 'particular', name: 'particular', orderable: false, searchable: false },
                        { data: 'issue', name: 'issue', orderable: false, searchable: false },
                        { data: 'operator', name: 'operator', orderable: false, searchable: false },
                        { data: 'priority', name: 'priority', orderable: false, searchable: false },
                        { data: 'status', name: 'status', orderable: false, searchable: false },
                        { data: 'created_by', name: 'created_by', orderable: false, searchable: false },
                        { data: 'created_at', name: 'created_at' },
                        { data: 'action', name: 'action', orderable: false, searchable: false }
                    ],
                    order: [[0, 'desc']],
                    drawCallback: function (settings) {
                        $('#pending-count').text(`(${pendingTable.page.info().recordsTotal})`);
                        $('#accepted-count').text(`(${activeTable.page.info().recordsTotal})`);
                        $('#inprogress-count').text(`(${inProgressTable.page.info().recordsTotal})`);
                        $('#closed-count').text(`(${closedTable.page.info().recordsTotal})`);
                    }
                };
            };

            const pendingTable = $('#pendingTicketsTable').DataTable(tableOptions('pending'));
            const activeTable = $('#activeTicketsTable').DataTable(tableOptions('active'));
            const inProgressTable = $('#inprogressTicketsTable').DataTable(tableOptions('inprogress'));
            const closedTable = $('#closedTicketsTable').DataTable(tableOptions('closed'));

            function reloadTables() {
                pendingTable.ajax.reload(null, false);
                activeTable.ajax.reload(null, false);
                inProgressTable.ajax.reload(null, false);
                closedTable.ajax.reload(null, false);
            }

            $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function () {
                pendingTable.columns.adjust();
                activeTable.columns.adjust();
                inProgressTable.columns.adjust();
                closedTable.columns.adjust();
            });

            $(document).on('submit', '.acceptTicketForm', function () {
                return confirm('Are you sure you want to accept this ticket?');
            });

            var assignUsersModal = new bootstrap.Modal(document.getElementById('assignUsersModal'));
            const assignUsersSelect = $('#assign_users').select2({
                placeholder: 'Select users', allowClear: true, width: '100%', dropdownParent: $('#assignUsersModal'), theme: 'classic',
                ajax: {
                    url: "{{ route('users-list') }}", type: 'POST', dataType: 'json', delay: 250,
                    data: function (params) { return { searchQuery: params.term, page: params.page || 1, ignoreDesignation: 1, _token: csrfToken }; },
                    processResults: function (data, params) { params.page = params.page || 1; return { results: $.map(data.items, function (item) { return { id: item.id, text: item.text }; }), pagination: { more: data.pagination.more } }; },
                    cache: true
                }
            });

            $(document).on('click', 'a.dropdown-item.text-info', function (e) {
                const href = $(this).attr('href');
                const encId = $(this).data('tid');
                if (!href) return;
                e.preventDefault();
                $('#assignTicketEncryptedId').val(encId || '');
                assignUsersSelect.val(null).trigger('change');
                $.ajax({
                    url: href, method: 'GET', dataType: 'json',
                    success: function (resp) {
                        if (resp && Array.isArray(resp.users)) {
                            resp.users.forEach(function (u) {
                                var option = new Option(u.text, u.id, true, true);
                                $('#assign_users').append(option);
                            });
                            $('#assign_users').trigger('change');
                        }
                        assignUsersModal.show();
                    },
                    error: function () { alert('Failed to load assigned users.'); }
                });
            });

            $(document).on('click', '#assignUsersSaveBtn', function () {
                const encId = $('#assignTicketEncryptedId').val();
                if (!encId) { assignUsersModal.hide(); return; }
                const url = `{{ url('tickets') }}/${encId}/assign-users`;
                const selectedUsers = $('#assign_users').val() || [];
                $.ajax({
                    url: url, method: 'POST', dataType: 'json',
                    data: { _token: csrfToken, users: selectedUsers },
                    success: function () { assignUsersModal.hide(); reloadTables(); },
                    error: function (xhr) {
                        let msg = 'Failed to save assigned users.';
                        if (xhr.responseJSON && xhr.responseJSON.message) { msg = xhr.responseJSON.message; }
                        alert(msg);
                    }
                });
            });

            $('#exportTicketsBtn').on('click', function () {
                const params = new URLSearchParams({
                    department_id: $('#filterDepartment').val() || '',
                    particular_id: $('#filterParticular').val() || '',
                    issue_id: $('#filterIssue').val() || '',
                    location: $('#filterStore').val() || '',
                    assigned: $('#filterAssignedTo').val() || '',
                    created_from: startTaskDate.format('DD-MM-YYYY'),
                    created_to: endTaskDate.format('DD-MM-YYYY'),
                    created_by: $('#filterCreatedBy').val() || ''
                });
                for (const [key, value] of [...params.entries()]) { if (!value) { params.delete(key); } }
                window.location.href = "{{ route('ticket-management.export') }}?" + params.toString();
            });
        });
    </script>
@endpush