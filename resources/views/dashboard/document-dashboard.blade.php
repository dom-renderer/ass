@extends('layouts.app-master')

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/jquery.datetimepicker.css') }}">
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
    <script src="{{ asset('assets/js/tailwindcss-cdn.js') }}"></script>
    {{-- Keeping Bootstrap for DataTables compatibility, but overriding layout with Tailwind --}}
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/twitter-bootstrap.min.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datatable-bootstrap.css') }}"/>
    
    <style>
        /* Professional Overrides */
        .select2-container--classic .select2-selection--single {
            height: 42px !important;
            border-color: #e5e7eb !important; /* Tailwind gray-200 */
            display: flex !important;
            align-items: center !important;
        }
        .select2-container--classic .select2-selection--single .select2-selection__arrow {
            height: 40px !important;
        }
        
        /* DataTable Style Polishing */
        table.dataTable {
            border-collapse: collapse !important;
            border-spacing: 0 !important;
            width: 100% !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #5f0000 !important;
            color: white !important;
            border: 1px solid #5f0000 !important;
            border-radius: 6px;
        }
        .dataTables_filter input {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 4px 12px;
            outline: none;
        }
        .dataTables_filter input:focus {
            border-color: #5f0000;
            ring: 1px solid #5f0000;
        }
    </style>
@endpush

@section('content')
<div class="p-6 bg-gray-50 min-h-screen">
    <div class="max-w-full mx-auto">
        <header class="mb-8">
            <h1 class="text-2xl font-bold text-gray-800">Document Management Dashboard</h1>
            <p class="text-gray-500">Monitor expiration statuses and document compliance.</p>
        </header>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-white to-orange-50">
                    <h2 class="text-lg font-bold text-[#5f0000] flex items-center">
                        <span class="w-2 h-6 bg-[#5f0000] rounded-full mr-3"></span>
                        Near Expiration Documents (60 Days)
                    </h2>
                </div>
                <div class="p-6 overflow-x-auto">
                    <table id="nearExpirationTable" class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">#</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Document</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">File</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Location</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Expiry</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Issue</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            {{-- DataTables Populated --}}
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-white to-red-50">
                    <h2 class="text-lg font-bold text-[#5f0000] flex items-center">
                        <span class="w-2 h-6 bg-red-600 rounded-full mr-3"></span>
                        Expired Documents
                    </h2>
                </div>
                <div class="p-6 overflow-x-auto">
                    <table id="expiredTable" class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">#</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Document</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">File</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Location</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Expiry</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Issue</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            {{-- DataTables Populated --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
    <script src="{{ asset('assets/js/jquery.datetimepicker.js') }}"></script>
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/js/other/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/other/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/js/other/dataTables.bootstrap5.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Updated DOM string to use Tailwind classes for layout
            const dtDom = '<"flex flex-col md:flex-row justify-between items-center mb-4 gap-4" <"user-role-table-filter-container"> f > rt <"flex flex-col md:flex-row justify-between items-center mt-4 gap-4" i p >';

            let nearTable = new DataTable('#nearExpirationTable', {
                "dom": dtDom,
                ajax: {
                    url: '{{ route("document-dashboard") }}',
                    data: { section: 'near_expiration' }
                },
                processing: false,
                ordering: false,
                serverSide: true,
                searching: false,
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'document_name', name: 'document_name' },
                    { data: 'attachment', name: 'document_file' },
                    { data: 'location', name: 'location' },
                    { data: 'expiry_date', name: 'expiry_date' },
                    { data: 'issue_date', name: 'issue_date' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                language: {
                    paginate: {
                        next: '<span class="px-2">→</span>',
                        previous: '<span class="px-2">←</span>'
                    }
                }
            });
            
            let expiredTable = new DataTable('#expiredTable', {
                "dom": dtDom,
                ajax: {
                    url: '{{ route("document-dashboard") }}',
                    data: { section: 'expired' }
                },
                processing: false,
                ordering: false,
                serverSide: true,
                searching: false,
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'document_name', name: 'document_name' },
                    { data: 'attachment', name: 'document_file' },
                    { data: 'location', name: 'location' },
                    { data: 'expiry_date', name: 'expiry_date' },
                    { data: 'issue_date', name: 'issue_date' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                language: {
                    paginate: {
                        next: '<span class="px-2">→</span>',
                        previous: '<span class="px-2">←</span>'
                    }
                }
            });

            // Remind Me Later
            $(document).on('click', '.zp_remindLaterBtn', function() {
                var action_url = $(this).data('url');

                $.ajax({
                    url: action_url,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        _token: "{{ csrf_token() }}",
                    },
                    beforeSend: function () {
                        $('body').find('.LoaderSec').removeClass('d-none');
                    },
                    success: function(response) {
                        $('body').find('.LoaderSec').addClass('d-none');
                        if ( response.status ) {
                            Swal.fire('Success', response.message, 'success');
                            nearTable.ajax.reload();
                            expiredTable.ajax.reload();
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    }
                });
            });

            // Delete
            $(document).on('click', '.deleteGroup', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Are you sure you want to delete this Document Upload?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $(this).parents('form').submit();
                        return true;
                    } else {
                        return false;
                    }
                })
            });

        });
    </script>
@endpush