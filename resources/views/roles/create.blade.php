@extends('layouts.app-master')

@push('css')
    <script src="{{ asset('assets/js/tailwindcss-cdn.js') }}"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['DynamicAppFont', 'sans-serif'] },
                }
            }
        }
    </script>
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

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.375rem;
        }

        /* Toggle switches */
        .switch-toggle {
            position: relative;
            display: inline-block;
            width: 36px;
            height: 20px;
            vertical-align: middle;
        }

        .switch-toggle input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #e5e7eb;
            transition: .3s;
            border-radius: 20px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 14px;
            width: 14px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .3s;
            border-radius: 50%;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        input:checked+.slider {
            background-color: #3b82f6;
        }

        input:checked+.slider:before {
            transform: translateX(16px);
        }
    </style>
@endpush

@section('content')

    <div class="px-6 pt-6 pb-10">
        <form method="POST" action="{{ route('roles.store') }}" class="">
            @csrf

            {{-- Page Header --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-800">Create New Role</h2>
                    <p class="text-sm text-gray-400 mt-0.5">Define a new system role and assign its specific permissions.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('roles.index') }}"
                        class="px-4 py-2 border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors shadow-sm text-decoration-none">
                        Cancel
                    </a>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2 bg-primary text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                        <i class="bi bi-save"></i> Save Role
                    </button>
                </div>
            </div>

            {{-- Role Settings Container --}}
            <div class="space-y-6">

                {{-- Name Card --}}
                <div class="bg-white rounded-xl border border-[#e5e7eb] shadow-sm overflow-hidden w-full">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center gap-2">
                        <i class="bi bi-card-text text-gray-400"></i>
                        <h3 class="text-sm font-semibold text-gray-800 m-0">Role Details</h3>
                    </div>
                    <div class="p-5">
                        <label for="name" class="form-label">Role Name <span class="text-red-500">*</span></label>
                        <input value="{{ old('name') }}" type="text" class="form-input" name="name" id="name"
                            placeholder="E.g., Administrator, Auditor" required>
                    </div>
                </div>

                {{-- Permissions Grid --}}
                <div class="bg-white rounded-xl border border-[#e5e7eb] shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center gap-2">
                        <i class="bi bi-shield-check text-gray-400"></i>
                        <h3 class="text-sm font-semibold text-gray-800 m-0">Assign Permissions</h3>
                    </div>
                    <div class="p-6 bg-gray-50/30">

                        @php
                            $permissionGroups = [];
                            foreach ($permissions as $permission) {
                                $group = explode('.', $permission->name)[0];
                                $permissionGroups[$group][] = $permission;
                            }
                        @endphp

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                            @foreach($permissionGroups as $group => $groupPermissions)
                                <div
                                    class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow flex flex-col h-full overflow-hidden">

                                    {{-- Card Header --}}
                                    <div
                                        class="px-4 py-3 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                                        <strong
                                            class="text-sm font-semibold text-gray-700 m-0">{{ Str::title(str_replace('-', ' ', $group)) }}</strong>

                                        <div class="flex items-center gap-2">
                                            <label class="text-xs font-medium text-gray-500 cursor-pointer mb-0"
                                                for="group-{{ $group }}">All</label>
                                            <label class="switch-toggle" style="width: 30px; height: 16px;">
                                                <input type="checkbox" class="group-check" data-group="{{ $group }}"
                                                    id="group-{{ $group }}">
                                                <span class="slider" style="border-radius:16px;">
                                                    <style>
                                                        #group-{{ $group }}:checked+.slider:before {
                                                            transform: translateX(14px);
                                                        }
                                                    </style>
                                                </span>
                                            </label>
                                        </div>
                                    </div>

                                    {{-- Card Body --}}
                                    <div class="p-4 flex-1 space-y-3">
                                        @foreach($groupPermissions as $permission)
                                            <div class="flex items-center gap-3">
                                                <label class="switch-toggle" style="width: 30px; height: 16px;">
                                                    <input type="checkbox" name="permission[{{ $permission->name }}]"
                                                        value="{{ $permission->name }}" class="permission permission-{{ $group }}"
                                                        id="permission-{{ $permission->id }}">
                                                    <span class="slider" style="border-radius:16px;">
                                                        <style>
                                                            #permission-{{ $permission->id }}:checked+.slider:before {
                                                                transform: translateX(14px);
                                                            }
                                                        </style>
                                                    </span>
                                                </label>
                                                <label class="text-sm font-medium text-gray-600 m-0 cursor-pointer flex-1"
                                                    for="permission-{{ $permission->id }}">
                                                    {{ $permission->title }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>

                                </div>
                            @endforeach
                        </div>

                    </div>
                </div>

            </div>
        </form>
    </div>

@endsection

@push('js')
    <script>
        $(document).ready(function () {

            $('.group-check').on('change', function () {
                let group = $(this).data('group');
                $('.permission-' + group).prop('checked', $(this).is(':checked'));
            });

            $('.permission').on('change', function () {
                let classes = $(this).attr('class').split(' ');
                let groupClass = classes.find(c => c.startsWith('permission-') && c !== 'permission');
                let group = groupClass.replace('permission-', '');

                let total = $('.permission-' + group).length;
                let checked = $('.permission-' + group + ':checked').length;

                $('#group-' + group).prop('checked', total === checked);
            });

        });
    </script>
@endpush