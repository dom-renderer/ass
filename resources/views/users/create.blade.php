@extends('layouts.app-master')

@push('css')
    <script src="{{ asset('assets/js/tailwindcss-cdn.js') }}"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['DynamicAppFont', 'sans-serif'] },
                    colors: {
                        primary: '#3b82f6',
                        'primary-hover': '#2563eb'
                    }
                }
            }
        }
    </script>
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

        .form-input.file-input {
            padding: 0.375rem 0.75rem;
            background-color: #f9fafb;
            font-size: 0.8125rem;
        }

        .form-label,
        label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.375rem;
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
            transition: all 0.2s;
            background-color: #fff;
        }

        .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 1px #3b82f6;
        }

        /* Select2 Overrides */
        .select2-container {
            width: 100% !important;
        }

        .select2-container--classic .select2-selection--single {
            height: 42px !important;
            border: 1px solid #e5e7eb !important;
            border-radius: 0.375rem !important;
            display: flex;
            align-items: center;
            background-image: none !important;
            box-shadow: none !important;
        }

        .select2-container--classic .select2-selection--single .select2-selection__rendered {
            line-height: 40px !important;
            color: #374151 !important;
            font-size: 0.875rem !important;
        }

        .select2-container--classic .select2-selection--single .select2-selection__arrow {
            height: 40px !important;
            border-left: 1px solid #e5e7eb !important;
            background-image: none !important;
            background-color: transparent !important;
        }

        .select2-container--classic .select2-selection--multiple {
            min-height: 42px !important;
            border: 1px solid #e5e7eb !important;
            border-radius: 0.375rem !important;
            padding: 2px 4px !important;
            background-image: none !important;
            box-shadow: none !important;
        }

        .select2-container--classic .select2-selection--multiple .select2-selection__choice {
            background-color: #f3f4f6 !important;
            border: 1px solid #d1d5db !important;
            border-radius: 0.375rem !important;
            padding: 2px 8px !important;
            margin-top: 4px !important;
            font-size: 0.875rem !important;
            color: #374151 !important;
            box-shadow: none !important;
            background-image: none !important;
        }

        .select2-container--classic.select2-container--focus .select2-selection--multiple {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 1px #3b82f6 !important;
        }

        /* Toggle switches */
        .switch-toggle {
            position: relative;
            display: inline-block;
            width: 40px;
            height: 22px;
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
            border-radius: 22px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
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
            transform: translateX(18px);
        }

        /* Hide dynamically injected labels momentarily if needed, no wait we specifically styled label tag above */
    </style>
@endpush

@section('content')
    <form method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data" class="px-6 pt-6 pb-10">
        @csrf

        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
            <div>
                <h2 class="text-2xl font-semibold text-gray-800">Create New User</h2>
                <p class="text-sm text-gray-400 mt-0.5">Fill out the information below to register a new user in the system.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('users.index') }}"
                    class="px-4 py-2 border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors shadow-sm text-decoration-none">
                    Cancel
                </a>
                <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2 bg-primary text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                    <i class="bi bi-save"></i> Save User
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Left Column (2/3) --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Identity & Profile Card --}}
                <div class="bg-white rounded-xl border border-[#e5e7eb] shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center gap-2">
                        <i class="bi bi-person-badge text-gray-400"></i>
                        <h3 class="text-sm font-semibold text-gray-800">Identity & Profile</h3>
                    </div>
                    <div class="p-5">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-5">
                            <div>
                                <label for="name" class="form-label">First Name</label>
                                <input value="{{ old('name') }}" type="text" class="form-input" name="name"
                                    placeholder="First Name" required>
                                @if ($errors->has('name'))
                                    <span class="text-xs text-red-500 mt-1 block">{{ $errors->first('name') }}</span>
                                @endif
                            </div>
                            <div>
                                <label for="middle_name" class="form-label">Middle Name</label>
                                <input value="{{ old('middle_name') }}" type="text" class="form-input" name="middle_name"
                                    placeholder="Middle Name">
                                @if ($errors->has('middle_name'))
                                    <span class="text-xs text-red-500 mt-1 block">{{ $errors->first('middle_name') }}</span>
                                @endif
                            </div>
                            <div>
                                <label for="last_name" class="form-label">Last Name</label>
                                <input value="{{ old('last_name') }}" type="text" class="form-input" name="last_name"
                                    placeholder="Last Name">
                                @if ($errors->has('last_name'))
                                    <span class="text-xs text-red-500 mt-1 block">{{ $errors->first('last_name') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                            <div>
                                <label for="employee_id" class="form-label">Employee ID</label>
                                <input value="{{ old('employee_id') }}" type="text" class="form-input" name="employee_id"
                                    placeholder="Ex: EMP-001">
                                @if ($errors->has('employee_id'))
                                    <span class="text-xs text-red-500 mt-1 block">{{ $errors->first('employee_id') }}</span>
                                @endif
                            </div>
                            <div>
                                <label for="profile" class="form-label">Profile Image</label>
                                <input type="file" class="form-input file-input" name="profile" accept="image/*">
                                @if ($errors->has('profile'))
                                    <span class="text-xs text-red-500 mt-1 block">{{ $errors->first('profile') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Account Credentials Card --}}
                <div class="bg-white rounded-xl border border-[#e5e7eb] shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center gap-2">
                        <i class="bi bi-shield-lock text-gray-400"></i>
                        <h3 class="text-sm font-semibold text-gray-800">Account Credentials</h3>
                    </div>
                    <div class="p-5 space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                            <div>
                                <label for="email" class="form-label">Email Address</label>
                                <input value="{{ old('email') }}" type="email" class="form-input" name="email"
                                    placeholder="example@company.com" required>
                                @if ($errors->has('email'))
                                    <span class="text-xs text-red-500 mt-1 block">{{ $errors->first('email') }}</span>
                                @endif
                            </div>
                            <div>
                                <label for="username" class="form-label">Username</label>
                                <input value="{{ old('username') }}" type="text" class="form-input" name="username"
                                    placeholder="Unique username" required>
                                @if ($errors->has('username'))
                                    <span class="text-xs text-red-500 mt-1 block">{{ $errors->first('username') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label for="phone_number" class="form-label">Phone Number</label>
                                <input value="{{ old('phone_number') }}" type="text" class="form-input" name="phone_number"
                                    placeholder="(555) 555-5555" required>
                                @if ($errors->has('phone_number'))
                                    <span class="text-xs text-red-500 mt-1 block">{{ $errors->first('phone_number') }}</span>
                                @endif
                            </div>
                            <div>
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-input" name="password" placeholder="Secure password"
                                    required>
                                @if ($errors->has('password'))
                                    <span class="text-xs text-red-500 mt-1 block">{{ $errors->first('password') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Dynamic Particular View for Agents --}}
                <div id="particular-view" class="hidden">
                    <div class="bg-white rounded-xl border border-[#e5e7eb] shadow-sm overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50">
                            <h4 class="text-sm font-semibold text-gray-800">Assign Operations Issues (Agent)</h4>
                        </div>
                        <div class="p-5 space-y-6">
                            @foreach(\App\Models\Department::whereHas('particulars')->with(['particulars.issues'])->get() as $department)
                                <div class="department bg-[#f8fafc] border border-gray-100 rounded-lg p-5">

                                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-200/60">
                                        <h5 class="text-sm font-semibold text-gray-800 m-0"> {{ $department->name }} </h5>
                                        <div class="flex items-center gap-2">
                                            <label class="text-xs font-medium text-gray-500 cursor-pointer"
                                                for="selectAll{{ $department->id }}">Select All</label>
                                            <label class="switch-toggle">
                                                <input type="checkbox" class="deptChckbx" data-iid="chckbx{{ $department->id }}"
                                                    id="selectAll{{ $department->id }}">
                                                <span class="slider"></span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="space-y-4">
                                        @foreach ($department->particulars as $particular)
                                            <div>
                                                <h6 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">
                                                    {{ $particular->name }}</h6>
                                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                                    @foreach ($particular->issues as $issue)
                                                        <div
                                                            class="flex items-center gap-2 bg-white border border-gray-100 px-3 py-2 rounded-md">
                                                            <label class="switch-toggle" style="width: 32px; height: 18px;">
                                                                <input type="checkbox" class="chckbx{{ $department->id }}"
                                                                    name="issues[]" value="{{ $issue->id }}"
                                                                    id="issuesChk{{ $issue->id }}">
                                                                <span class="slider" style="border-radius:18px;"></span>
                                                            </label>
                                                            <label
                                                                class="text-xs font-medium text-gray-700 m-0 cursor-pointer truncate flex-1"
                                                                for="issuesChk{{ $issue->id }}">
                                                                {{ $issue->name }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>

            {{-- Right Column (1/3) --}}
            <div class="space-y-6">

                {{-- Role & Status Card --}}
                <div class="bg-white rounded-xl border border-[#e5e7eb] shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center gap-2">
                        <i class="bi bi-diagram-3 text-gray-400"></i>
                        <h3 class="text-sm font-semibold text-gray-800">Role & Status</h3>
                    </div>
                    <div class="p-5 space-y-5">
                        <div>
                            <label for="status" class="form-label">Account Status</label>
                            <select class="form-select" name="status" id="status">
                                <option value="1">Enabled</option>
                                <option value="0">Disabled</option>
                            </select>
                        </div>

                        <div>
                            <label for="role" class="form-label">System Role</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="">Select a role</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('role'))
                                <span class="text-xs text-red-500 mt-1 block">{{ $errors->first('role') }}</span>
                            @endif
                        </div>

                        {{-- Dynamic JS containers --}}
                        <div id="dynamic-dom-options" class="empty:hidden"></div>
                        <div id="dynamic-role-options" class="empty:hidden"></div>
                    </div>
                </div>

                {{-- Ticket System Settings --}}
                <div class="bg-white rounded-xl border border-[#e5e7eb] shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center gap-2">
                        <i class="bi bi-ticket-perforated text-gray-400"></i>
                        <h3 class="text-sm font-semibold text-gray-800">Ticket System</h3>
                    </div>
                    <div class="p-5 space-y-5">
                        <div class="ticket-settings">
                            <label class="form-label">Ticket Roles</label>
                            <select id="ticket_roles" name="ticket_roles[]" multiple="multiple">
                                @foreach ($tRoles as $tRole)
                                    <option value="{{ $tRole->id }}"> {{ $tRole->name }} </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Dynamic DOM Stores view --}}
                        <div id="store-view" class="hidden">
                            <label class="form-label">Assigned DOM Stores</label>
                            <select id="ticket-dom-stores" name="dom_stores[]" multiple="multiple">
                                @foreach (\App\Models\Store::whereNull('dom_id')->orWhere('dom_id', '')->get() as $store)
                                    <option value="{{ $store->id }}"> {{ $store->code }} - {{ $store->name }} </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {

            $('#ticket-dom-stores').select2({
                placeholder: "Select stores",
                allowClear: true,
                width: "100%",
                theme: 'classic'
            });

            // The custom css logic overrides inside dynamic containers need class injected correctly
            // Instead of overriding JS, let's keep it mostly untouched and ensure `.form-label` works via our CSS.

            $('.deptChckbx').on('change', function () {
                let isChecked = $(this).is(':checked');
                let toCheck = $(this).data('iid');
                $(`.${toCheck}`).prop('checked', isChecked);
            });

            $('#ticket_roles').select2({
                placeholder: "Select ticket roles",
                allowClear: true,
                width: "100%",
                theme: 'classic'
            }).on('change', function () {
                let selected = $(this).val();

                if (Array.isArray(selected)) {
                    if (selected.includes('1')) {
                        $('#particular-view').removeClass('hidden').hide().fadeIn(200);
                    } else {
                        $('#particular-view').addClass('hidden').hide();
                    }

                    if (selected.includes('2')) {
                        $('#store-view').removeClass('hidden').hide().fadeIn(200);
                    } else {
                        $('#store-view').addClass('hidden').hide();
                    }
                } else {
                    $('#store-view').addClass('hidden').hide();
                    $('#particular-view').addClass('hidden').hide();
                }
            });

            $('#role').select2({
                placeholder: "Select a system role",
                allowClear: true,
                width: "100%",
                theme: 'classic'
            }).on('change', function () {
                let selectedRole = $('#role option:selected').val();

                if (!isNaN(selectedRole) && ['10', '6'].includes(selectedRole)) {
                    $('#dynamic-dom-options').html(`
                        <div class="mt-4">
                            <label class="form-label">Assign DoM</label>
                            <select id="doms" name="doms[]" multiple></select>
                        </div>
                    `);

                    if ($('#doms').length > 0) {
                        $('#doms').select2({
                            placeholder: "Select DoM",
                            allowClear: true,
                            width: "100%",
                            theme: 'classic',
                            ajax: {
                                url: "{{ route('users-list') }}",
                                type: "POST",
                                dataType: 'json',
                                delay: 250,
                                data: function (params) {
                                    return {
                                        searchQuery: params.term,
                                        page: params.page || 1,
                                        _token: "{{ csrf_token() }}",
                                        roles: "{{ implode(',', [Helper::$roles['divisional-operations-manager'], Helper::$roles['operations-manager']]) }}",
                                    };
                                },
                                processResults: function (data, params) {
                                    params.page = params.page || 1;
                                    return {
                                        results: $.map(data.items, function (item) {
                                            return { id: item.id, text: item.text };
                                        }),
                                        pagination: { more: data.pagination.more }
                                    };
                                },
                                cache: true
                            },
                            templateResult: function (data) {
                                if (data.loading) return data.text;
                                return $('<span></span>').text(data.text);
                            }
                        });
                    }
                } else {
                    $('#dynamic-dom-options').html('');
                }

                if (!isNaN(selectedRole)) {
                    if (['2', '3', '4'].includes(selectedRole)) {
                        $('#dynamic-role-options').html(`
                            <div class="mt-4">
                                <label class="form-label">Location</label>
                                <select id="thisStoreDepartmentCOffice" name="office[]" multiple required></select>
                            </div>
                        `);
                        initDepartmentOfficeSelect('stores-list', 'Select a Location');
                    } else if (['5'].includes(selectedRole)) {
                        $('#dynamic-role-options').html(`
                            <div class="mt-4">
                                <label class="form-label">Office</label>
                                <select id="thisStoreDepartmentCOffice" name="office[]" multiple required></select>
                            </div>
                        `);
                        initDepartmentOfficeSelect('corporate-offices-list', 'Select an Office');
                    } else if (['6', '7', '10'].includes(selectedRole)) {
                        $('#dynamic-role-options').html(`
                            <div class="mt-4">
                                <label class="form-label">Departments</label>
                                <select id="thisStoreDepartmentCOffice" name="office[]" multiple required></select>
                            </div>
                        `);
                        initDepartmentOfficeSelect('departments-list', 'Select a Department');
                    } else {
                        $('#dynamic-role-options').html('');
                    }
                }
            });

            function initDepartmentOfficeSelect(routeName, placeholderText) {
                let baseUrl = "{{ url('/') }}";
                let endpointUrl = baseUrl + '/' + routeName.replace('-list', '/list'); // fallback if needed, but route() requires exact naming
                // Since we cannot easily pass blade dynamic functions dynamically here without mapping, we use raw mapping:
                let routesMap = {
                    'stores-list': "{{ route('stores-list') }}",
                    'corporate-offices-list': "{{ route('corporate-offices-list') }}",
                    'departments-list': "{{ route('departments-list') }}"
                };

                if ($('#thisStoreDepartmentCOffice').length > 0) {
                    $('#thisStoreDepartmentCOffice').select2({
                        placeholder: placeholderText,
                        allowClear: true,
                        width: "100%",
                        theme: 'classic',
                        ajax: {
                            url: routesMap[routeName] || endpointUrl,
                            type: "POST",
                            dataType: 'json',
                            delay: 250,
                            data: function (params) {
                                return {
                                    searchQuery: params.term,
                                    page: params.page || 1,
                                    assetswloc: 1,
                                    _token: "{{ csrf_token() }}"
                                };
                            },
                            processResults: function (data, params) {
                                params.page = params.page || 1;
                                return {
                                    results: $.map(data.items, function (item) {
                                        return { id: item.id, text: item.text };
                                    }),
                                    pagination: { more: data.pagination.more }
                                };
                            },
                            cache: true
                        },
                        templateResult: function (data) {
                            if (data.loading) return data.text;
                            return $('<span></span>').text(data.text);
                        }
                    });
                }
            }

        });
    </script>
@endpush