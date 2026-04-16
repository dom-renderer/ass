@extends('layouts.app-master')

@push('css')
    <script src="{{ asset('assets/js/tailwindcss-cdn.js') }}"></script>
    <script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['DynamicAppFont', 'sans-serif'] } } } }</script>
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
    <style>
        body {
            font-family: 'DynamicAppFont', sans-serif !important;
        }

        .form-input {
            width: 100%;
            height: 42px;
            border: 1px solid #e5e7eb;
            border-radius: 0.375rem;
            padding: 0 0.75rem;
            font-size: 0.875rem;
            color: #374151;
            transition: all 0.2s;
            outline: none;
        }

        .form-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 1px #3b82f6;
        }

        .form-select {
            width: 100%;
            height: 42px;
            border: 1px solid #e5e7eb;
            border-radius: 0.375rem;
            padding: 0 0.75rem;
            font-size: 0.875rem;
            color: #374151;
            outline: none;
            background-color: white;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.375rem;
        }

        label.error {
            color: #ef4444;
            font-size: 0.75rem;
            margin-top: 0.25rem;
            display: block;
        }
    </style>
@endpush

@section('content')
    <div class="px-6 pt-6 pb-10">
        <form method="POST" action="{{ route('issues.update', $id) }}" id="issueForm" class="max-w-3xl mx-auto">
            @csrf @method('PUT')
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-800">Edit Issue</h2>
                    <p class="text-sm text-gray-400 mt-0.5">Modify this issue entry.</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('issues.index') }}"
                        class="px-4 py-2 border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors shadow-sm text-decoration-none">Cancel</a>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm cursor-pointer"><i
                            class="bi bi-save"></i> Save Changes</button>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-[#e5e7eb] shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center gap-2">
                    <i class="bi bi-exclamation-triangle text-gray-400"></i>
                    <h3 class="text-sm font-semibold text-gray-800 m-0">Issue Details</h3>
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <label for="name" class="form-label">Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name', $issue->name) }}" class="form-input"
                            placeholder="Enter name">
                        @error('name')<span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label for="department" class="form-label">Department <span class="text-red-500">*</span></label>
                        <select name="department" id="department">
                            @php $dep = \App\Models\Department::withTrashed()->find($issue->department_id); @endphp
                            @if($dep)
                            <option value="{{ $dep->id }}" selected>{{ $dep->name }}</option>@endif
                        </select>
                        @error('department')<span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label for="particular" class="form-label">Particular <span class="text-red-500">*</span></label>
                        <select name="particular" id="particular">
                            @php $par = \App\Models\Particular::withTrashed()->find($issue->particular_id); @endphp
                            @if($par)
                            <option value="{{ $par->id }}" selected>{{ $par->name }}</option>@endif
                        </select>
                        @error('particular')<span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="1" {{ $issue->status ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ !$issue->status ? 'selected' : '' }}>InActive</option>
                        </select>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('js')
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $('#department').select2({
                placeholder: 'Select Department', allowClear: true, width: '100%', theme: 'classic',
                ajax: {
                    url: "{{ route('departments-list') }}", type: "POST", dataType: 'json', delay: 250,
                    data: function (params) { return { searchQuery: params.term, page: params.page || 1, _token: "{{ csrf_token() }}" }; },
                    processResults: function (data, params) {
                        params.page = params.page || 1;
                        return { results: $.map(data.items, function (item) { return { id: item.id, text: item.text }; }), pagination: { more: data.pagination.more } };
                    },
                    cache: true
                }
            });

            $('#particular').select2({
                placeholder: 'Select Particular', allowClear: true, width: '100%', theme: 'classic',
                ajax: {
                    url: "{{ route('particulars-list') }}", type: "POST", dataType: 'json', delay: 250,
                    data: function (params) {
                        return { searchQuery: params.term, page: params.page || 1, department_id: function () { return $('#department').val(); }, select2: 'particulars', _token: "{{ csrf_token() }}" };
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;
                        return { results: $.map(data.items, function (item) { return { id: item.id, text: item.text }; }), pagination: { more: data.pagination.more } };
                    },
                    cache: true
                }
            });

            $('#department').on('change', function () { $('#particular').val(null).trigger('change'); });

            $('#issueForm').validate({
                rules: { name: { required: true }, department: { required: true }, particular: { required: true } }
            });
        });
    </script>
@endpush