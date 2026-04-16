@extends('layouts.app-master')

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/jquery.datetimepicker.css') }}">
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/twitter-bootstrap.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datatable-bootstrap.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/daterangepicker.css') }}">
    <style>
        .section {
            margin-bottom: 20px;
            padding: 20px;
            border-radius: 8px;
            background-color: #f8f9fa;
            box-shadow: 0 2px 4px #0000001a;
        }

        .section h2 {
            color: #012440;
        }

        .select2-container--classic .select2-selection--single .select2-selection__arrow {
            height: 38px !important;
        }

        .select2-container--classic .select2-selection--single {
            height: 40px !important;
        }

        .select2-container--classic .select2-selection--single .select2-selection__clear {
            height: 37px !important;
        }

        .select2-container--classic .select2-selection--single .select2-selection__rendered {
            line-height: 39px !important;
        }

        ul.nav-tabs button.nav-link.active {
            color: #012440!important;
            border-bottom: 1px solid #012440!important;
        }
    </style>
@endpush

@section('content')
    <div class="row">

        <div class="">
            <div class="section">
                <div class="row mb-3">
                    <div class="col-md-4 col-lg-4 col-xl-2">
                        <label class="form-label" for="task-date-range-picker"> Date </label>
                        <input type="text" id="task-date-range-picker" class="form-control" readonly />
                    </div>

                    <div class="col-md-4 col-lg-4 col-xl-2">
                        <label class="form-label" for="filterChecklist"> Checklist </label>
                        <select id="filterChecklist">
                            <option value="all" selected> All </option>
                        </select>
                    </div>

                    <div class="col-md-4 col-lg-4 col-xl-2">
                        <label for="filterDom" class="form-label">DoM</label>
                        <select id="filterDom" class="form-select">
                            @if (auth()->user()->isAdmin())
                                <option value="all" selected> All </option>
                            @else
                                <option value="{{ auth()->user()->id }}"> {{ auth()->user()->employee_id }} -
                                    {{ auth()->user()->name }} {{ auth()->user()->middle_name }}
                                    {{ auth()->user()->last_name }} </option>
                            @endif
                        </select>
                    </div>

                    <div class="col-md-4 col-lg-4 col-xl-2">
                        <label for="filterState" class="form-label">State</label>
                        <select id="filterState">
                            <option value="all" selected> All </option>
                        </select>
                    </div>

                    <div class="col-md-4 col-lg-4 col-xl-2">
                        <label for="filterCity" class="form-label">City</label>
                        <select id="filterCity">
                            <option value="all" selected> All </option>
                        </select>
                    </div>

                    <div class="col-md-4 col-lg-4 col-xl-2">
                        <label for="filterStore" class="form-label">Location</label>
                        <select id="filterStore">
                            <option value="all" selected> All </option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="section">

                {{-- Statistics --}}
                <section id="kpi-section" class="">
                    <div class="grid grid-cols-7 gap-7">
                        <!-- Total Assets Card -->
                        <div class="bg-white cursor-pointer rounded-xl border border-neutral-200 p-6 hover:shadow-lg transition-shadow" data-status="total">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 bg-neutral-100 rounded-lg flex items-center justify-center">
                                    <i class="fa fa-clipboard"></i>
                                </div>
                            </div>
                            <h3 class="text-sm text-neutral-600 mb-1">Total Inspections</h3>
                            <p class="text-3xl text-neutral-900" id="total-inspection-count">0</p>
                            <div class="mt-4 flex items-center text-xs text-neutral-600">
                            </div>
                        </div>

                        <!-- Due for Inspection Card -->
                        <div class="bg-white cursor-pointer rounded-xl border border-neutral-200 p-6 hover:shadow-lg transition-shadow" data-status="in_progress">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 bg-neutral-50 rounded-lg flex items-center justify-center">
                                    <i class="fa fa-spinner fa-spin"></i>
                                </div>
                            </div>
                            <h3 class="text-sm text-neutral-600 mb-1">In-Progress</h3>
                            <p class="text-3xl text-[#0dcaf0]" id="in-progress-count">0</p>
                            <div class="mt-4 flex items-center text-xs text-neutral-600">
                            </div>
                        </div>

                        <!-- Overdue Inspections Card -->
                        <div class="bg-white cursor-pointer rounded-xl border border-neutral-200 p-6 hover:shadow-lg transition-shadow" data-status="pending">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 bg-neutral-50 rounded-lg flex items-center justify-center">
                                    <i class="fa fa-clock-o fa-pulse"></i>
                                </div>
                            </div>
                            <h3 class="text-sm text-neutral-600 mb-1">Pending</h3>
                            <p class="text-3xl text-[#F59E0B]" id="pending-inspection">0</p>
                            <div class="mt-4 flex items-center text-xs text-neutral-600">
                            </div>
                        </div>

                        <!-- Completed Assets Card -->
                        <div class="bg-white cursor-pointer rounded-xl border border-neutral-200 p-6 hover:shadow-lg transition-shadow" data-status="completed">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 bg-neutral-50 rounded-lg flex items-center justify-center">
                                    <i class="fa fa-check-circle-o"></i>
                                </div>
                            </div>
                            <h3 class="text-sm text-neutral-600 mb-1">Completed</h3>
                            <p class="text-3xl text-[#065e2e]" id="completed-count">0</p>
                            <div class="mt-4 flex items-center text-xs text-neutral-600">
                            </div>
                        </div>

                        <!-- Failed/At-Risk Assets Card -->
                        <div class="bg-white cursor-pointer rounded-xl border border-neutral-200 p-6 hover:shadow-lg transition-shadow" data-status="over_due">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 bg-neutral-50 rounded-lg flex items-center justify-center">
                                    <i class="fa fa-exclamation-triangle"></i>
                                </div>
                            </div>
                            <h3 class="text-sm text-neutral-600 mb-1">Over Due</h3>
                            <p class="text-3xl text-[#F59E0B]" id="over-due-count">0</p>
                            <div class="mt-4 flex items-center text-xs text-neutral-600">
                            </div>
                        </div>

                        <!-- Failed/At-Risk Assets Card -->
                        <div class="bg-white cursor-pointer rounded-xl border border-neutral-200 p-6 hover:shadow-lg transition-shadow" data-status="failed">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 bg-neutral-50 rounded-lg flex items-center justify-center">
                                    <i class="fa fa-times-circle"></i>
                                </div>
                            </div>
                            <h3 class="text-sm text-neutral-600 mb-1">Failed</h3>
                            <p class="text-3xl text-[#dd2d20]" id="failed-count">0</p>
                            <div class="mt-4 flex items-center text-xs text-neutral-600">
                            </div>
                        </div>

                        <!-- Failed/At-Risk Assets Card -->
                        <div class="bg-white cursor-pointer rounded-xl border border-neutral-200 p-6 hover:shadow-lg transition-shadow" data-status="passed">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 bg-neutral-50 rounded-lg flex items-center justify-center">
                                    <i class="fa fa-check-circle"></i>
                                </div>
                            </div>
                            <h3 class="text-sm text-neutral-600 mb-1">Passed</h3>
                            <p class="text-3xl text-[#065e2e]" id="passed-count">0</p>
                            <div class="mt-4 flex items-center text-xs text-neutral-600">
                            </div>
                        </div>
                    </div>
                </section>
                {{-- Statistics --}}

            </div>

            <div class="section">
                <h2 style="font-size:20px;margin-bottom:20px;">Flagged Items
                    <button class="btn btn-success btn-sm float-end export-flagged-items"> Export </button>
                </h2>

                <div class="table -responsive">
                    <table class="table table-striped" id="table2">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>Checklist</th>
                                <th>DoM</th>
                                <th>Location</th>
                                <th>City</th>
                                <th>State</th>
                                <th>Initial Status</th>
                                <th>Latest Status</th>
                                <th>Last Updated</th>
                            </tr>
                        </thead>
                        <tbody id="table2body">
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="section">
                <h2 style="font-size:20px;margin-bottom:20px;">Tickets
                    <button class="btn btn-success btn-sm float-end export-tickets"> Export </button>
                </h2>

                <div class="row mb-3">
                    <div class="col-md-3 col-lg-6">
                        <label for="tktStart" class="form-label">Start Date</label>
                        <input type="text" id="tktStart" class="form-control"
                            value="{{ \Carbon\Carbon::now()->startOfMonth()->format('d-m-Y') }}">
                    </div>
                    <div class="col-md-3 col-lg-6">
                        <label for="tktEnd" class="form-label">End Date</label>
                        <input type="text" id="tktEnd" class="form-control" value="{{ date('d-m-Y') }}">
                    </div>
                    <div class="col-md-3 col-lg-6">
                        <label for="tktDoM" class="form-label">Raised By</label>
                        <select id="tktDoM" class="form-select">
                            @if (auth()->user()->isAdmin())
                                <option value="all" selected> All </option>
                            @else
                                <option value="{{ auth()->user()->id }}"> {{ auth()->user()->employee_id }} -
                                    {{ auth()->user()->name }} {{ auth()->user()->middle_name }}
                                    {{ auth()->user()->last_name }} </option>
                            @endif
                        </select>
                    </div>

                    <div class="col-md-3 col-lg-6">
                        <label for="tktState" class="form-label">State</label>
                        <select id="tktState">
                            <option value="all" selected> All </option>
                        </select>
                    </div>

                    <div class="col-md-3 col-lg-6">
                        <label for="tktCity" class="form-label">City</label>
                        <select id="tktCity">
                            <option value="all" selected> All </option>
                        </select>
                    </div>

                    <div class="col-md-3 col-lg-6">
                        <label for="tktLocation" class="form-label">Location</label>
                        <select id="tktLocation">
                            <option value="all" selected> All </option>
                        </select>
                    </div>

                    <div class="col-md-3 col-lg-6">
                        <label for="tktDeptartment" class="form-label">Department</label>
                        <select id="tktDeptartment">
                            <option value="all" selected> All </option>
                        </select>
                    </div>

                    <div class="col-md-3 col-lg-6">
                        <label for="tktStatus" class="form-label">Status</label>
                        <select id="tktStatus">
                            <option value="all" selected> All </option>
                            <option value="pending">Pending</option>
                            <option value="accepted">Accepted</option>
                            <option value="in_progress">In Progress</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                </div>

                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home"
                            type="button" role="tab" aria-controls="home" aria-selected="true">Pending</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile"
                            type="button" role="tab" aria-controls="profile"
                            aria-selected="false">Accepted</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="onhold-tab" data-bs-toggle="tab" data-bs-target="#onhold"
                            type="button" role="tab" aria-controls="onhold" aria-selected="false">In
                            Progress</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact"
                            type="button" role="tab" aria-controls="contact" aria-selected="false">Closed</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="stale-tab" data-bs-toggle="tab" data-bs-target="#stale"
                            type="button" role="tab" aria-controls="contact" aria-selected="false">Stale</button>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                        <div class="table-responisve">
                            <table class="table table-striped table-responsive" style="width:100%;" id="ticket-table-a">
                                <thead>
                                    <tr>
                                        <th>Ticket ID</th>
                                        <th>Title</th>
                                        <th>Location</th>
                                        <th>City</th>
                                        <th>Department</th>
                                        <th>Particular</th>
                                        <th>Issue</th>
                                        <th>Raised By</th>
                                        <th>Date Opened</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                        <div class="table-responisve">
                            <table class="table table-striped table-responsive" style="width:100%;" id="ticket-table-b">
                                <thead>
                                    <tr>
                                        <th>Ticket ID</th>
                                        <th>Title</th>
                                        <th>Location</th>
                                        <th>City</th>
                                        <th>Department</th>
                                        <th>Particular</th>
                                        <th>Issue</th>
                                        <th>Raised By</th>
                                        <th>Date Opened</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="onhold" role="tabpanel" aria-labelledby="onhold-tab">
                        <div class="table-responisve">
                            <table class="table table-striped table-responsive" style="width:100%;"
                                id="ticket-table-onhold">
                                <thead>
                                    <tr>
                                        <th>Ticket ID</th>
                                        <th>Title</th>
                                        <th>Location</th>
                                        <th>City</th>
                                        <th>Department</th>
                                        <th>Particular</th>
                                        <th>Issue</th>
                                        <th>Raised By</th>
                                        <th>Date Opened</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
                        <div class="table-responisve">
                            <table class="table table-striped table-responsive" style="width:100%;" id="ticket-table-c">
                                <thead>
                                    <tr>
                                        <th>Ticket ID</th>
                                        <th>Title</th>
                                        <th>Location</th>
                                        <th>City</th>
                                        <th>Department</th>
                                        <th>Particular</th>
                                        <th>Issue</th>
                                        <th>Raised By</th>
                                        <th>Date Opened</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="stale" role="tabpanel" aria-labelledby="contact-tab">
                        <div class="table-responisve">
                            <table class="table table-striped table-responsive" style="width:100%;" id="ticket-table-d">
                                <thead>
                                    <tr>
                                        <th>Ticket ID</th>
                                        <th>Title</th>
                                        <th>Location</th>
                                        <th>City</th>
                                        <th>Department</th>
                                        <th>Particular</th>
                                        <th>Issue</th>
                                        <th>Raised By</th>
                                        <th>Date Opened</th>
                                        <th>Day Opened</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="viewData" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel"> Detailed </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="detail-Body">

                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="{{ asset('assets/js/tailwindcss-cdn.js') }}"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            "50": "#fafafa",
                            "100": "#f5f5f5",
                            "200": "#e5e5e5",
                            "300": "#d4d4d4",
                            "400": "#a3a3a3",
                            "500": "#737373",
                            "600": "#525252",
                            "700": "#404040",
                            "800": "#262626",
                            "900": "#171717",
                            "950": "#0a0a0a"
                        }
                    }
                }
            }
        }
    </script>
    <script src="{{ asset('assets/js/jquery.datetimepicker.js') }}"></script>
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/js/other/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/other/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/js/other/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/js/moment.min.js') }}"></script>
    <script src="{{ asset('assets/js/daterangepicker.min.js') }}"></script>
    <script>
        $(document).ready(function() {

            $(document).on('click', '#kpi-section .cursor-pointer', function () {
                let status = $(this).data('status');

                let dateRange = $('#task-date-range-picker').val().split(' - ');
                let startDate = dateRange[0] ?? '';
                let endDate   = dateRange[1] ?? '';

                let params = {
                    status: status,
                    start_date: startDate,
                    end_date: endDate,
                    dom: $('#filterDom').val(),
                    state: $('#filterState').val(),
                    city: $('#filterCity').val(),
                    store: $('#filterStore').val(),
                    checklist: $('#filterChecklist').val()
                };

                Object.keys(params).forEach(key => {
                    if (!params[key] || params[key] === 'all') {
                        delete params[key];
                    }
                });

                let url = "{{ route('drill-down-to-tasks') }}";

                window.location.href = url + '?' + $.param(params); 
            });

            var startTaskDate = moment("{{ date('d-m-Y') }}", 'DD-MM-YYYY');
            var endTaskDate = moment("{{ date('d-m-Y') }}", 'DD-MM-YYYY');

            function cb(start, end) {
                $('#task-date-range-picker').val(start.format('DD-MM-YYYY') + ' - ' + end.format('DD-MM-YYYY'));
            }

            $('#task-date-range-picker').daterangepicker({
                startDate: startTaskDate,
                endDate: endTaskDate,
                locale: {
                    format: 'DD-MM-YYYY'
                },
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

            $('#task-date-range-picker').on('apply.daterangepicker', function(ev, picker) {
                startTaskDate = picker.startDate;
                endTaskDate = picker.endDate

                usersTable.ajax.reload();
            });

            $('.export-flagged-items').on('click', function() {

                $.ajax({
                    url: "{{ route('export-flagged-items-export') }}",
                    type: 'GET',
                    xhrFields: {
                        responseType: 'blob'
                    },
                    data: {
                        startd: function() {
                            return startTaskDate.format('DD-MM-YYYY');
                        },
                        endd: function() {
                            return endTaskDate.format('DD-MM-YYYY');
                        },
                        dom: function() {
                            return $('#filterDom').val();
                        },
                        store: function() {
                            return $('#filterStore').val();
                        },
                        state: function() {
                            return $('#filterState').val();
                        },
                        city: function() {
                            return $('#filterCity').val();
                        },
                        checklist: function () {
                            return $('#filterChecklist').val();
                        }
                    },
                    beforeSend: function() {
                        $('body').find('.LoaderSec').removeClass('d-none');
                    },
                    success: function(response) {
                        var url = window.URL || window.webkitURL;
                        var objectUrl = url.createObjectURL(response);
                        var a = $("<a />", {
                            href: objectUrl,
                            download: "{{ date('d-m-Y-His') }}-flagged-items.pdf"
                        }).appendTo("body")
                        a[0].click()
                        a.remove()
                    },
                    complete: function(response) {
                        $('body').find('.LoaderSec').addClass('d-none');
                    }
                });

            });

            $('.export-tickets').on('click', function() {

                $.ajax({
                    url: "{{ route('export-tickets') }}",
                    type: 'GET',
                    xhrFields: {
                        responseType: 'blob'
                    },
                    data: {
                        startd: function() {
                            return $('#tktStart').val();
                        },
                        endd: function() {
                            return $('#tktEnd').val();
                        },
                        dom: function() {
                            return $('#tktDoM').val();
                        },
                        store: function() {
                            return $('#tktLocation').val();
                        },
                        state: function() {
                            return $('#tktState').val();
                        },
                        city: function() {
                            return $('#tktCity').val();
                        },
                        dept: function() {
                            return $('#tktDeptartment').val();
                        },
                        status: function() {
                            return $('#tktStatus').val();
                        }
                    },
                    beforeSend: function() {
                        $('body').find('.LoaderSec').removeClass('d-none');
                    },
                    success: function(response) {
                        var url = window.URL || window.webkitURL;
                        var objectUrl = url.createObjectURL(response);
                        var a = $("<a />", {
                            href: objectUrl,
                            download: "{{ date('d-m-Y-His') }}-flagged-items.pdf"
                        }).appendTo("body")
                        a[0].click()
                        a.remove()
                    },
                    complete: function(response) {
                        $('body').find('.LoaderSec').addClass('d-none');
                    }
                });

            });

            let usersTable = new DataTable('#table2', {
                pageLength: 10,
                "aLengthMenu": [
                    [10, 50, 100, 250],
                    [10, 50, 100, 250]
                ],
                ajax: {
                    url: "{{ route('flagged-items-dashboard') }}",
                    beforeSend: function() {
                        $('body').find('.LoaderSec').removeClass('d-none');
                    },
                    data: function(d) {
                        return $.extend({}, d, {
                            startd: function () {
                                return startTaskDate.format('DD-MM-YYYY');
                            },
                            endd: function () {
                                return endTaskDate.format('DD-MM-YYYY');
                            },
                            dom: $('#filterDom').val(),
                            store: $('#filterStore').val(),
                            state: $('#filterState').val(),
                            city: $('#filterCity').val(),
                            checklist: $('#filterChecklist').val()
                        });
                    },
                    complete: function(response) {
                        $('body').find('.LoaderSec').addClass('d-none');
                    }
                },
                processing: false,
                ordering: false,
                serverSide: true,
                columns: [{
                        data: 'item_name'
                    },
                    {
                        data: 'clistname'
                    },
                    {
                        data: 'dom_name'
                    },
                    {
                        data: 'location_name'
                    },
                    {
                        data: 'city_name'
                    },
                    {
                        data: 'state_name'
                    },
                    {
                        data: 'initial_status_name'
                    },
                    {
                        data: 'latest_status_name'
                    },
                    {
                        data: 'last_updated'
                    }
                ],
                initComplete: function(settings) {

                },
                drawCallback: function (settings) {
                    $('#total-inspection-count').text(settings.json.staistics.total ?? 0);
                    $('#in-progress-count').text(settings.json.staistics.in_progress ?? 0);
                    $('#pending-inspection').text(settings.json.staistics.pending ?? 0);
                    $('#over-due-count').text(settings.json.staistics.over_due ?? 0);
                    $('#failed-count').text(settings.json.staistics.failed ?? 0);
                    $('#passed-count').text(settings.json.staistics.passed ?? 0);
                    $('#completed-count').text(settings.json.staistics.completed ?? 0);
                }
            });

            $('#filterStart').datetimepicker({
                format: 'd-m-Y',
                timepicker: false,
                onChangeDateTime: function() {
                    usersTable.ajax.reload();
                }
            });

            $('#filterEnd').datetimepicker({
                format: 'd-m-Y',
                timepicker: false,
                onChangeDateTime: function() {
                    usersTable.ajax.reload();
                }
            });

            $('#filterState').select2({
                placeholder: 'Select State',
                allowClear: true,
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('state-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            _token: "{{ csrf_token() }}",
                            getall: true
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;

                        return {
                            results: $.map(data.items, function(item) {
                                return {
                                    id: item.id,
                                    text: item.text
                                };
                            }),
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                },
                templateResult: function(data) {
                    if (data.loading) {
                        return data.text;
                    }

                    var $result = $('<span></span>');
                    $result.text(data.text);
                    return $result;
                }
            }).on('change', function() {
                usersTable.ajax.reload();
                $('#filterCity').val(null).trigger('change');
            });

            $('#filterChecklist').select2({
                placeholder: 'Select Checklist',
                allowClear: true,
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('checklists-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            _token: "{{ csrf_token() }}",
                            type: 1
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;

                        return {
                            results: $.map(data.items, function(item) {
                                return {
                                    id: item.id,
                                    text: item.text
                                };
                            }),
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                },
                templateResult: function(data) {
                    if (data.loading) {
                        return data.text;
                    }

                    var $result = $('<span></span>');
                    $result.text(data.text);
                    return $result;
                }
            }).on('change', function() {
                usersTable.ajax.reload();
            });

            $('#filterCity').select2({
                placeholder: 'Select City',
                allowClear: true,
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('city-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            _token: "{{ csrf_token() }}",
                            state: function() {
                                return $('#filterState').val();
                            },
                            getall: true
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;

                        return {
                            results: $.map(data.items, function(item) {
                                return {
                                    id: item.id,
                                    text: item.text
                                };
                            }),
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                },
                templateResult: function(data) {
                    if (data.loading) {
                        return data.text;
                    }

                    var $result = $('<span></span>');
                    $result.text(data.text);
                    return $result;
                }
            }).on('change', function() {
                usersTable.ajax.reload();
            });

            $('#filterDom').select2({
                placeholder: 'Select DOM',
                allowClear: true,
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('users-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            _token: "{{ csrf_token() }}",
                            ignoreDesignation: 1,
                            roles: "{{ implode(',', [Helper::$roles['store-phone'], Helper::$roles['store-manager'], Helper::$roles['store-employee'], Helper::$roles['store-cashier'], Helper::$roles['divisional-operations-manager'], Helper::$roles['operations-manager'], Helper::$roles['head-of-department']]) }}",
                            getall: true
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;

                        return {
                            results: $.map(data.items, function(item) {
                                return {
                                    id: item.id,
                                    text: item.text
                                };
                            }),
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                },
                templateResult: function(data) {
                    if (data.loading) {
                        return data.text;
                    }

                    var $result = $('<span></span>');
                    $result.text(data.text);
                    return $result;
                }
            }).on('change', function() {
                usersTable.ajax.reload();
            });

            $('#filterSop').select2({
                placeholder: 'Select Checklist',
                allowClear: true,
                width: '100%',
                theme: 'classic'
            }).on('change', function() {
                usersTable.ajax.reload();
            });

            $('#filterStore').select2({
                placeholder: 'Select location',
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('stores-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            _token: "{{ csrf_token() }}",
                            getall: true
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;

                        return {
                            results: $.map(data.items, function(item) {
                                return {
                                    id: item.id,
                                    text: item.text
                                };
                            }),
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                },
                templateResult: function(data) {
                    if (data.loading) {
                        return data.text;
                    }

                    var $result = $('<span></span>');
                    $result.text(data.text);
                    return $result;
                }
            }).on('change', function() {
                usersTable.ajax.reload();
            });











            // Ticket Tabs

            let ticket1 = new DataTable('#ticket-table-a', {
                pageLength: 10,
                "aLengthMenu": [
                    [10, 50, 100, 250],
                    [10, 50, 100, 250]
                ],
                ajax: {
                    url: "{{ route('get-ticket-listing') }}",
                    data: function(d) {
                        return $.extend({}, d, {
                            mainstatus: 'pending',
                            startd: $('#tktStart').val(),
                            endd: $('#tktEnd').val(),
                            dom: $('#tktDoM').val(),
                            store: $('#tktLocation').val(),
                            state: $('#tktState').val(),
                            city: $('#tktCity').val(),
                            dept: $('#tktDeptartment').val(),
                            status: $('#tktStatus').val()
                        });
                    }
                },
                processing: false,
                ordering: false,
                serverSide: true,
                columns: [{
                        data: 'ticket_number'
                    },
                    {
                        data: 'subject'
                    },
                    {
                        data: 'location_name'
                    },
                    {
                        data: 'city_name'
                    },
                    {
                        data: 'department_name'
                    },
                    {
                        data: 'priority_name'
                    },
                    {
                        data: 'issue_name'
                    },
                    {
                        data: 'dom_name'
                    },
                    {
                        data: 'date_opened'
                    },
                    {
                        data: 'status_name'
                    }
                ],
                initComplete: function(settings) {

                }
            });
            let ticket2 = new DataTable('#ticket-table-b', {
                pageLength: 10,
                "aLengthMenu": [
                    [10, 50, 100, 250],
                    [10, 50, 100, 250]
                ],
                ajax: {
                    url: "{{ route('get-ticket-listing') }}",
                    data: function(d) {
                        return $.extend({}, d, {
                            mainstatus: 'accepted',
                            startd: $('#tktStart').val(),
                            endd: $('#tktEnd').val(),
                            dom: $('#tktDoM').val(),
                            store: $('#tktLocation').val(),
                            state: $('#tktState').val(),
                            city: $('#tktCity').val(),
                            dept: $('#tktDeptartment').val(),
                            status: $('#tktStatus').val()
                        });
                    }
                },
                processing: false,
                ordering: false,
                serverSide: true,
                columns: [{
                        data: 'ticket_number'
                    },
                    {
                        data: 'subject'
                    },
                    {
                        data: 'location_name'
                    },
                    {
                        data: 'city_name'
                    },
                    {
                        data: 'department_name'
                    },
                    {
                        data: 'priority_name'
                    },
                    {
                        data: 'issue_name'
                    },
                    {
                        data: 'dom_name'
                    },
                    {
                        data: 'date_opened'
                    },
                    {
                        data: 'status_name'
                    }
                ],
                initComplete: function(settings) {

                }
            });

            let ticketonhold = new DataTable('#ticket-table-onhold', {
                pageLength: 10,
                "aLengthMenu": [
                    [10, 50, 100, 250],
                    [10, 50, 100, 250]
                ],
                ajax: {
                    url: "{{ route('get-ticket-listing') }}",
                    data: function(d) {
                        return $.extend({}, d, {
                            mainstatus: 'in_progress',
                            startd: $('#tktStart').val(),
                            endd: $('#tktEnd').val(),
                            dom: $('#tktDoM').val(),
                            store: $('#tktLocation').val(),
                            state: $('#tktState').val(),
                            city: $('#tktCity').val(),
                            dept: $('#tktDeptartment').val(),
                            status: $('#tktStatus').val()
                        });
                    }
                },
                processing: false,
                ordering: false,
                serverSide: true,
                columns: [{
                        data: 'ticket_number'
                    },
                    {
                        data: 'subject'
                    },
                    {
                        data: 'location_name'
                    },
                    {
                        data: 'city_name'
                    },
                    {
                        data: 'department_name'
                    },
                    {
                        data: 'priority_name'
                    },
                    {
                        data: 'issue_name'
                    },
                    {
                        data: 'dom_name'
                    },
                    {
                        data: 'date_opened'
                    },
                    {
                        data: 'status_name'
                    }
                ],
                initComplete: function(settings) {

                }
            });

            let ticket3 = new DataTable('#ticket-table-c', {
                pageLength: 10,
                "aLengthMenu": [
                    [10, 50, 100, 250],
                    [10, 50, 100, 250]
                ],
                ajax: {
                    url: "{{ route('get-ticket-listing') }}",
                    data: function(d) {
                        return $.extend({}, d, {
                            mainstatus: 'closed',
                            startd: $('#tktStart').val(),
                            endd: $('#tktEnd').val(),
                            dom: $('#tktDoM').val(),
                            store: $('#tktLocation').val(),
                            state: $('#tktState').val(),
                            city: $('#tktCity').val(),
                            dept: $('#tktDeptartment').val(),
                            status: $('#tktStatus').val()
                        });
                    }
                },
                processing: false,
                ordering: false,
                serverSide: true,
                columns: [{
                        data: 'ticket_number'
                    },
                    {
                        data: 'subject'
                    },
                    {
                        data: 'location_name'
                    },
                    {
                        data: 'city_name'
                    },
                    {
                        data: 'department_name'
                    },
                    {
                        data: 'priority_name'
                    },
                    {
                        data: 'issue_name'
                    },
                    {
                        data: 'dom_name'
                    },
                    {
                        data: 'date_opened'
                    },
                    {
                        data: 'status_name'
                    }
                ],
                initComplete: function(settings) {

                }
            });

            let ticket4 = new DataTable('#ticket-table-d', {
                pageLength: 10,
                "aLengthMenu": [
                    [10, 50, 100, 250],
                    [10, 50, 100, 250]
                ],
                ajax: {
                    url: "{{ route('get-ticket-listing') }}",
                    data: function(d) {
                        return $.extend({}, d, {
                            startd: $('#tktStart').val(),
                            endd: $('#tktEnd').val(),
                            dom: $('#tktDoM').val(),
                            store: $('#tktLocation').val(),
                            state: $('#tktState').val(),
                            city: $('#tktCity').val(),
                            dept: $('#tktDeptartment').val(),
                            status: $('#tktStatus').val()
                        });
                    }
                },
                processing: false,
                ordering: false,
                serverSide: true,
                columns: [{
                        data: 'ticket_number'
                    },
                    {
                        data: 'subject'
                    },
                    {
                        data: 'location_name'
                    },
                    {
                        data: 'city_name'
                    },
                    {
                        data: 'department_name'
                    },
                    {
                        data: 'priority_name'
                    },
                    {
                        data: 'issue_name'
                    },
                    {
                        data: 'dom_name'
                    },
                    {
                        data: 'date_opened'
                    },
                    {
                        data: 'opened'
                    },
                    {
                        data: 'status_name'
                    }
                ],
                initComplete: function(settings) {

                }
            });

            $('#tktStart').datetimepicker({
                format: 'd-m-Y',
                timepicker: false,
                onChangeDateTime: function() {
                    ticket1.ajax.reload();
                    ticket2.ajax.reload();
                    ticket3.ajax.reload();
                    ticket4.ajax.reload();
                    ticketonhold.ajax.reload();
                }
            });

            $('#tktEnd').datetimepicker({
                format: 'd-m-Y',
                timepicker: false,
                onChangeDateTime: function() {
                    ticket1.ajax.reload();
                    ticket2.ajax.reload();
                    ticket3.ajax.reload();
                    ticket4.ajax.reload();
                    ticketonhold.ajax.reload();
                }
            });

            $('#tktDoM').select2({
                placeholder: 'Select DOM',
                allowClear: true,
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('users-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            _token: "{{ csrf_token() }}",
                            ignoreDesignation: 1,
                            getall: true
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;

                        return {
                            results: $.map(data.items, function(item) {
                                return {
                                    id: item.id,
                                    text: item.text
                                };
                            }),
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                },
                templateResult: function(data) {
                    if (data.loading) {
                        return data.text;
                    }

                    var $result = $('<span></span>');
                    $result.text(data.text);
                    return $result;
                }
            }).on('change', function() {
                ticket1.ajax.reload();
                ticket2.ajax.reload();
                ticket3.ajax.reload();
                ticket4.ajax.reload();
                ticketonhold.ajax.reload();
            });

            $('#tktState').select2({
                placeholder: 'Select State',
                allowClear: true,
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('state-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            _token: "{{ csrf_token() }}",
                            getall: true
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;

                        return {
                            results: $.map(data.items, function(item) {
                                return {
                                    id: item.id,
                                    text: item.text
                                };
                            }),
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                },
                templateResult: function(data) {
                    if (data.loading) {
                        return data.text;
                    }

                    var $result = $('<span></span>');
                    $result.text(data.text);
                    return $result;
                }
            }).on('change', function() {
                ticket1.ajax.reload();
                ticket2.ajax.reload();
                ticket3.ajax.reload();
                ticket4.ajax.reload();
                ticketonhold.ajax.reload();
                $('#filterCity').val(null).trigger('change');
            });

            $('#tktCity').select2({
                placeholder: 'Select City',
                allowClear: true,
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('city-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            _token: "{{ csrf_token() }}",
                            state: function() {
                                return $('#filterState').val();
                            },
                            getall: true
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;

                        return {
                            results: $.map(data.items, function(item) {
                                return {
                                    id: item.id,
                                    text: item.text
                                };
                            }),
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                },
                templateResult: function(data) {
                    if (data.loading) {
                        return data.text;
                    }

                    var $result = $('<span></span>');
                    $result.text(data.text);
                    return $result;
                }
            }).on('change', function() {
                ticket1.ajax.reload();
                ticket2.ajax.reload();
                ticket3.ajax.reload();
                ticket4.ajax.reload();
                ticketonhold.ajax.reload();
            });

            $('#tktLocation').select2({
                placeholder: 'Select location',
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('stores-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            _token: "{{ csrf_token() }}",
                            getall: true
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;

                        return {
                            results: $.map(data.items, function(item) {
                                return {
                                    id: item.id,
                                    text: item.text
                                };
                            }),
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                },
                templateResult: function(data) {
                    if (data.loading) {
                        return data.text;
                    }

                    var $result = $('<span></span>');
                    $result.text(data.text);
                    return $result;
                }
            }).on('change', function() {
                ticket1.ajax.reload();
                ticket2.ajax.reload();
                ticket3.ajax.reload();
                ticket4.ajax.reload();
                ticketonhold.ajax.reload();
            });

            $('#tktDeptartment').select2({
                placeholder: 'Select Department',
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('departments-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            _token: "{{ csrf_token() }}",
                            getall: true
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;

                        return {
                            results: $.map(data.items, function(item) {
                                return {
                                    id: item.id,
                                    text: item.text
                                };
                            }),
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                },
                templateResult: function(data) {
                    if (data.loading) {
                        return data.text;
                    }

                    var $result = $('<span></span>');
                    $result.text(data.text);
                    return $result;
                }
            }).on('change', function() {
                ticket1.ajax.reload();
                ticket2.ajax.reload();
                ticket3.ajax.reload();
                ticket4.ajax.reload();
                ticketonhold.ajax.reload();
            });

            $('#tktStatus').select2({
                placeholder: 'Select location',
                width: '100%',
                theme: 'classic'
            }).on('change', function() {
                ticket1.ajax.reload();
                ticket2.ajax.reload();
                ticket3.ajax.reload();
                ticket4.ajax.reload();
                ticketonhold.ajax.reload();
            });
            // Ticket Tabs

        });
    </script>
@endpush
