@extends('layouts.app-master')

@push('css')
    <script src="{{ asset('assets/js/tailwindcss-cdn.js') }}"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['DynamicAppFont', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datatable-bootstrap.css') }}" />

    <style type="text/css">
        body {
            font-family: 'DynamicAppFont', sans-serif !important;
        }

        /* ── Select2 overrides to match Tailwind input height/style ── */
        .select2-container {
            width: 100% !important;
            background: none;
            border: none;
        }

        .select2-container--classic .select2-selection--single {
            height: 42px !important;
            border: 1px solid #e5e7eb !important;
            border-radius: 0.375rem !important;
            background-color: #fff !important;
            box-shadow: none !important;
        }

        .select2-container--classic .select2-selection--single .select2-selection__rendered {
            line-height: 40px !important;
            padding-left: 12px !important;
            color: #374151 !important;
            font-size: 0.875rem !important;
        }

        .select2-container--classic .select2-selection--single .select2-selection__arrow {
            height: 40px !important;
            border-left: 1px solid #e5e7eb !important;
            background: transparent !important;
        }

        .select2-container--classic .select2-selection--single .select2-selection__clear {
            height: 38px !important;
            line-height: 38px !important;
        }

        .select2-container--classic .select2-selection--single:focus {
            border-color: #3b82f6 !important;
            outline: none !important;
        }

        /* ── DataTables Tailwind Skin overrides ── */
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
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            padding: 0.75rem 1.5rem !important;
            background-color: #f9fafb;
        }

        table.dataTable tbody td {
            border-bottom: 1px solid #f3f4f6 !important;
            padding: 1rem 1.5rem !important;
            vertical-align: middle;
            color: #374151;
            font-size: 0.875rem;
        }

        table.dataTable tbody tr:hover {
            background-color: #f9fafb !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0 !important;
            margin: 0 !important;
            border: none !important;
        }

        /* Nav Pills Tailwind Style */
        .nav-pills .nav-link {
            border-radius: 0.5rem;
            color: #4b5563;
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: all 0.2s;
            background-color: transparent;
        }

        .nav-pills .nav-link.active,
        .nav-pills .show>.nav-link {
            color: #111827;
            background-color: #f3f4f6;
        }

        .nav-pills .nav-link:hover:not(.active) {
            color: #111827;
            background-color: #f9fafb;
        }

        /* Modal Overrides */
        .modal-backdrop {
            z-index: 1040 !important;
        }

        .modal {
            z-index: 1050 !important;
        }
    </style>
@endpush

@section('content')

    <div class="px-6 pt-6 pb-4">

        {{-- Page Header --}}
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between mb-6">
            <div>
                <h2 class="text-2xl font-semibold text-gray-800">{{ $page_title }}</h2>
                <p class="text-sm text-gray-400 mt-0.5">{{ $page_description }}</p>
            </div>
            <div class="flex items-center gap-3">
                @if(auth()->user()->can('users.import'))
                    <button type="button" data-bs-toggle="modal" data-bs-target="#browser-file"
                        class="inline-flex items-center gap-2 px-4 py-2 border border-blue-200 bg-blue-50 hover:bg-blue-100 text-blue-700 text-sm font-medium rounded-lg transition-colors">
                        <i class="bi bi-cloud-arrow-up"></i>
                        Import Users
                    </button>
                @endif
                @if(auth()->user()->can('users.export'))
                    <button type="button" id="export-user"
                        class="inline-flex items-center gap-2 px-4 py-2 border border-[#e5e7eb] bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors shadow-sm">
                        <i class="bi bi-cloud-arrow-down"></i>
                        Export
                    </button>
                @endif
                @if(auth()->user()->can('users.create'))
                    <a href="{{ route('users.create') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                        <i class="bi bi-plus-lg"></i>
                        Add User
                    </a>
                @endif
            </div>
        </div>

        <div class="mt-2 mb-4">
            @include('layouts.partials.messages')
        </div>

        {{-- Tabs --}}
        <ul class="nav nav-pills flex items-center gap-2 mb-6" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="users-tab" data-bs-toggle="tab" data-bs-target="#users-tab-pane"
                    type="button" role="tab" aria-controls="users-tab-pane" aria-selected="true">
                    Users
                    <span
                        class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">{{ $userCount }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="archived-users-tab" data-bs-toggle="tab"
                    data-bs-target="#archived-users-tab-pane" type="button" role="tab"
                    aria-controls="archived-users-tab-pane" aria-selected="false">
                    Archived
                    <span
                        class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">{{ $archivedUserCount }}</span>
                </button>
            </li>
        </ul>

        {{-- Tab Content --}}
        <div class="tab-content" id="myTabContent">

            {{-- Active Users Tab --}}
            <div class="tab-pane fade show active" id="users-tab-pane" role="tabpanel" aria-labelledby="users-tab"
                tabindex="0">
                <div class="bg-white rounded-xl border border-[#e5e7eb] shadow-sm overflow-hidden">
                    <div
                        class="px-6 py-4 border-b border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
                        <div class="flex items-center gap-2 text-gray-800">
                            <i class="bi bi-people text-gray-500"></i>
                            <h3 class="text-sm font-semibold">Active Users</h3>
                        </div>
                        <div id="user-role-table-filter" class="w-full md:w-auto min-w-[250px]">
                            <select></select>
                        </div>
                    </div>
                    <div class="p-0 table-responsive">
                        <table class="table table-striped dt-responsive w-100 m-0" id="users-table" cellspacing="0">
                            <thead>
                                <tr>
                                    <th scope="col" width="1%">#</th>
                                    <th scope="col">First Name</th>
                                    <th scope="col">Middle Name</th>
                                    <th scope="col">Last Name</th>
                                    <th scope="col" width="15%">Username</th>
                                    <th scope="col" width="10%">Email</th>
                                    <th scope="col" width="10%">Phone Number</th>
                                    <th scope="col" width="10%">Role</th>
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Archived Users Tab --}}
            <div class="tab-pane fade" id="archived-users-tab-pane" role="tabpanel" aria-labelledby="archived-users-tab"
                tabindex="0">
                <div class="bg-white rounded-xl border border-[#e5e7eb] shadow-sm overflow-hidden">
                    <div
                        class="px-6 py-4 border-b border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
                        <div class="flex items-center gap-2 text-gray-800">
                            <i class="bi bi-archive text-gray-500"></i>
                            <h3 class="text-sm font-semibold">Archived Users</h3>
                        </div>
                        <div id="archived-user-role-table-filter" class="w-full md:w-auto min-w-[250px]">
                            <select></select>
                        </div>
                    </div>
                    <div class="p-0 table-responsive">
                        <table class="table table-striped dt-responsive w-100 m-0" id="archived-users-table"
                            cellspacing="0">
                            <thead>
                                <tr>
                                    <th scope="col" width="1%">#</th>
                                    <th scope="col">First Name</th>
                                    <th scope="col">Middle Name</th>
                                    <th scope="col">Last Name</th>
                                    <th scope="col" width="15%">Username</th>
                                    <th scope="col" width="10%">Email</th>
                                    <th scope="col" width="10%">Phone Number</th>
                                    <th scope="col" width="10%">Role</th>
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Import Modal --}}
    <div class="modal fade" id="browser-file" tabindex="-1" aria-labelledby="browser-file-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="fileUploader" method="POST" action="{{ route('users.import') }}" enctype="multipart/form-data"
                class="modal-content rounded-xl border-0 shadow-xl overflow-hidden bg-white">
                @csrf
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <h5 class="text-base font-semibold text-gray-800" id="browser-file-label">Import Users via XLSX</h5>
                    <button type="button" class="text-gray-400 hover:text-gray-600 transition-colors"
                        data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg text-lg"></i>
                    </button>
                </div>
                <div class="px-6 py-5">
                    <div class="mb-4">
                        <label for="xlsxfile"
                            class="block text-sm font-medium text-gray-700 mb-1.5 flex items-center gap-2">
                            <i class="bi bi-file-earmark-spreadsheet text-blue-500"></i> Select Excel File (*.xlsx)
                        </label>
                        <div class="relative">
                            <input type="file" name="xlsx" id="xlsxfile" accept=".xlsx"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 file:cursor-pointer border border-[#e5e7eb] rounded-lg p-1"
                                required>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50">
                    <button type="button"
                        class="px-4 py-2 text-sm font-medium text-gray-600 border border-[#e5e7eb] bg-white rounded-lg hover:bg-gray-100 transition-colors shadow-sm"
                        data-bs-dismiss="modal">
                        Close
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors shadow-sm inline-flex items-center gap-2">
                        <i class="bi bi-upload"></i> Upload Users
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('js')
    <script src="{{ asset('assets/js/other/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/other/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/js/other/dataTables.bootstrap5.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('assets/js/jquery-validate.min.js') }}"></script>
    <script>
        const triggerTabList = document.querySelectorAll('#myTab button');
        const RoleSelectUser = document.querySelector('#user-role-table-filter select');
        const RoleSelectArchivedUser = document.querySelector('#archived-user-role-table-filter select');
        var rolesData = @json($roles);

        $(document).ready(function ($) {

            $(document).on('click', '.deleteGroup', function (e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Are you sure you want to archive this User?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, archive it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $(this).parents('form').submit();
                        return true;
                    } else {
                        return false;
                    }
                })
            });

            $(document).on('click', '.restoreGroup', function (e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Are you sure you want to restore this User?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, restore it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $(this).parents('form').submit();
                        return true;
                    } else {
                        return false;
                    }
                })
            });

            let usersTable = new DataTable('#users-table', {
                dom: '<"px-6 py-4 border-b border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4"lf>rt<"px-6 py-4 bg-gray-50 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4"pi><"clear">',
                pageLength: 50,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                ajax: {
                    url: "{{route('datatable.users')}}",
                    data: function (d) {
                        return $.extend({}, d, {
                            roles: RoleSelectUser.value,
                        });
                    }
                },
                processing: false,
                ordering: false,
                serverSide: true,
                columns: [
                    { data: 'DT_RowIndex', searchable: false },
                    { data: 'name' },
                    { data: 'middle_name' },
                    { data: 'last_name' },
                    { data: 'username' },
                    { data: 'email' },
                    { data: 'phone_number' },
                    { data: 'currentrole' },
                    { data: 'action', searchable: false, orderable: false }
                ],
                initComplete: function (settings) {
                    $(RoleSelectUser).select2({
                        placeholder: "Role Filter",
                        allowClear: true,
                        width: "100%",
                        theme: "classic",
                        data: Array.prototype.concat([{ id: '', text: 'All', selected: true }], rolesData.map(function (ele) {
                            return { id: ele.name, text: ele.name, selected: false };
                        }))
                    }).on('change', function () {
                        usersTable.ajax.reload();
                    });
                }
            });

            let archivedUsersTable = new DataTable('#archived-users-table', {
                dom: '<"px-6 py-4 border-b border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4"lf>rt<"px-6 py-4 bg-gray-50 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4"pi><"clear">',
                pageLength: 50,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                ajax: {
                    url: "{{route('datatable.users.archive')}}",
                    data: function (d) {
                        return $.extend({}, d, {
                            roles: RoleSelectArchivedUser.value,
                        });
                    }
                },
                processing: false,
                ordering: false,
                serverSide: true,
                columns: [
                    { data: 'DT_RowIndex', searchable: false },
                    { data: 'name' },
                    { data: 'middle_name' },
                    { data: 'last_name' },
                    { data: 'username' },
                    { data: 'email' },
                    { data: 'phone_number' },
                    { data: 'currentrole' },
                    { data: 'action', searchable: false, orderable: false }
                ],
                initComplete: function (settings) {
                    $(RoleSelectArchivedUser).select2({
                        placeholder: "Role Filter",
                        allowClear: true,
                        width: "100%",
                        theme: "classic",
                        data: Array.prototype.concat([{ id: '', text: 'All', selected: true }], rolesData.map(function (ele) {
                            return { id: ele.name, text: ele.name, selected: false };
                        }))
                    }).on('change', function () {
                        archivedUsersTable.ajax.reload();
                    });
                }
            });

            triggerTabList.forEach(triggerEl => {
                const tabTrigger = new bootstrap.Tab(triggerEl)
                triggerEl.addEventListener('click', event => {
                    event.preventDefault()
                    tabTrigger.show()
                })
                triggerEl.addEventListener('shown.bs.tab', event => {
                    $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
                });
            });

            jQuery.validator.addMethod("extension", function (value, element, param) {
                if (element.files.length > 0) {
                    const file = element.files[0];
                    const fileExtension = file.name.split('.').pop().toLowerCase();
                    return fileExtension === param.toLowerCase();
                }
                return true;
            }, "Please upload a valid file type.");

            jQuery.validator.addMethod("filesize", function (value, element, param) {
                if (element.files.length > 0) {
                    return element.files[0].size <= param;
                }
                return true;
            }, "File size must not exceed {0} bytes.");

            $('#fileUploader').validate({
                rules: {
                    xlsx: { required: true, extension: "xlsx", filesize: 5242880 }
                },
                messages: {
                    xlsx: {
                        required: "Please select a file",
                        extension: "Please select a XLSX file",
                        filesize: 'File size must not exceed 5MB.'
                    }
                },
                submitHandler: function (form, event) {
                    event.preventDefault();
                    let formData = new FormData(form);
                    $.ajax({
                        url: "{{ route('users.import') }}",
                        type: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        beforeSend: function () {
                            $('body').find('.LoaderSec').removeClass('d-none');
                        },
                        success: function (response) {
                            $('body').find('.LoaderSec').addClass('d-none');
                            if (response.status) {
                                $('#browser-file').modal('hide');
                                $('form#fileUploader')[0].reset();
                                $('.modal-backdrop').remove();
                                usersTable.ajax.reload();
                                Swal.fire('Success', response.message, 'success');
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        }
                    });
                }
            });

            $('#export-user').on('click', function (e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('users.export') }}",
                    type: 'GET',
                    cache: false,
                    xhrFields: { responseType: 'blob' },
                    beforeSend: function () {
                        $('body').find('.LoaderSec').removeClass('d-none');
                    },
                    success: function (response) {
                        var url = window.URL || window.webkitURL;
                        var objectUrl = url.createObjectURL(response);
                        var a = $("<a />", {
                            href: objectUrl,
                            download: "users.xlsx"
                        }).appendTo("body")
                        a[0].click()
                        a.remove()
                    },
                    complete: function () {
                        $('body').find('.LoaderSec').addClass('d-none');
                    }
                });
            });
        });
    </script>
@endpush