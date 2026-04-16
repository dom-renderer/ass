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
        body { font-family: 'DynamicAppFont', sans-serif !important; }
        
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
            background-color: #f9fafb;
            cursor: default;
        }
        .form-label { display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.375rem; }
        
        /* Toggle switches */
        .switch-toggle {
            position: relative;
            display: inline-block;
            width: 36px;
            height: 20px;
            vertical-align: middle;
            opacity: 0.75;
        }
        .switch-toggle input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: not-allowed; top: 0; left: 0; right: 0; bottom: 0; background-color: #e5e7eb; transition: .3s; border-radius: 20px; }
        .slider:before { position: absolute; content: ""; height: 14px; width: 14px; left: 3px; bottom: 3px; background-color: white; transition: .3s; border-radius: 50%; box-shadow: 0 1px 2px rgba(0,0,0,0.1); }
        input:checked + .slider { background-color: #3b82f6; }
        input:checked + .slider:before { transform: translateX(16px); }
    </style>
@endpush

@section('content')

<div class="px-6 pt-6 pb-10">
    <div class="">

        {{-- Page Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
            <div>
                <h2 class="text-2xl font-semibold text-gray-800">View Role Settings</h2>
                <p class="text-sm text-gray-400 mt-0.5">Read-only view of permissions and access layers for this role.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('roles.index') }}" class="inline-flex items-center gap-2 px-4 py-2 border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors shadow-sm text-decoration-none">
                    <i class="bi bi-arrow-left"></i> Back to Roles
                </a>
                <a href="{{ route('roles.edit', $role->id) }}" class="inline-flex items-center gap-2 px-5 py-2 bg-primary text-white text-sm font-medium rounded-lg transition-colors shadow-sm text-decoration-none">
                    <i class="bi bi-pencil"></i> Edit Role
                </a>
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
                    <label class="form-label">Role Name</label>
                    <input value="{{ $role->name }}" type="text" class="form-input" readonly>
                </div>
            </div>

            {{-- Permissions Grid --}}
            <div class="bg-white rounded-xl border border-[#e5e7eb] shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center gap-2">
                    <i class="bi bi-shield-lock text-gray-400"></i>
                    <h3 class="text-sm font-semibold text-gray-800 m-0">Assigned Permissions</h3>
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
                            @php
                                $groupChecked = collect($groupPermissions)
                                    ->pluck('name')
                                    ->every(fn ($name) => in_array($name, $rolePermissions));
                            @endphp

                            <div class="bg-white border border-gray-200 rounded-lg shadow-sm flex flex-col h-full overflow-hidden">
                                
                                {{-- Card Header --}}
                                <div class="px-4 py-3 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                                    <strong class="text-sm font-semibold text-gray-700 m-0">{{ Str::title(str_replace('-', ' ', $group)) }}</strong>
                                    
                                    <div class="flex items-center gap-2">
                                        <label class="text-xs font-medium text-gray-500 cursor-default mb-0" for="group-{{ $group }}">All</label>
                                        <label class="switch-toggle" style="width: 30px; height: 16px;">
                                            <input type="checkbox" onclick="return false;" tabindex="-1" id="group-{{ $group }}" {{ $groupChecked ? 'checked' : '' }}>
                                            <span class="slider" style="border-radius:16px;">
                                                <style>#group-{{ $group }}:checked + .slider:before { transform: translateX(14px); }</style>
                                            </span>
                                        </label>
                                    </div>
                                </div>

                                {{-- Card Body --}}
                                <div class="p-4 flex-1 space-y-3">
                                    @foreach($groupPermissions as $permission)
                                        <div class="flex items-center gap-3">
                                            <label class="switch-toggle" style="width: 30px; height: 16px;">
                                                <input type="checkbox"
                                                       onclick="return false;" tabindex="-1"
                                                       id="permission-{{ $permission->id }}"
                                                       {{ in_array($permission->name, $rolePermissions) ? 'checked' : '' }}>
                                                <span class="slider" style="border-radius:16px;">
                                                    <style>#permission-{{ $permission->id }}:checked + .slider:before { transform: translateX(14px); }</style>
                                                </span>
                                            </label>
                                            <label class="text-sm font-medium text-gray-600 m-0 cursor-default flex-1" for="permission-{{ $permission->id }}">
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
    </div>
</div>

@endsection
