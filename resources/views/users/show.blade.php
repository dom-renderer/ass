@extends('layouts.app-master')

@push('css')
    <script src="{{ asset('assets/js/tailwindcss-cdn.js') }}"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['DynamicAppFont', 'sans-serif'] },
                    colors: { primary: '#3b82f6', 'primary-hover': '#2563eb' }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'DynamicAppFont', sans-serif !important;
        }
    </style>
@endpush

@section('content')
    <div class="px-6 pt-6 pb-10">
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
            <div>
                <h2 class="text-2xl font-semibold text-gray-800">User Profile</h2>
                <p class="text-sm text-gray-400 mt-0.5">Detailed view of user identity and configurations.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('users.index') }}"
                    class="px-4 py-2 border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors shadow-sm text-decoration-none inline-flex items-center">
                    <i class="bi bi-arrow-left mr-2"></i> Back
                </a>
                @php
                    use App\Models\User;
                    $trashedUser = User::withTrashed()->find($user->id);
                @endphp
                @if(!$trashedUser->trashed())
                    <a href="{{ route('users.edit', $user->id) }}"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg transition-colors shadow-sm text-decoration-none">
                        <i class="bi bi-pencil"></i> Edit Profile
                    </a>
                @endif
            </div>
        </div>

        <!-- Main Profile Card -->
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-xl border border-[#e5e7eb] shadow-sm overflow-hidden">

                <!-- Header Section with Avatar -->
                <div class="bg-gray-50/80 border-b border-gray-100 flex flex-col md:flex-row items-center gap-6"
                    style="padding: 2rem;">
                    <div class="flex-shrink-0">
                        @if(!empty($user->profile) && file_exists(storage_path("app/public/users/{$user->profile}")))
                            <img src="{{ asset("storage/users/{$user->profile}") }}" alt="Profile"
                                class="h-28 w-28 rounded-full object-cover border-4 border-white shadow-sm">
                        @else
                            <div
                                class="h-28 w-28 rounded-full bg-blue-100 border-4 border-white shadow-sm flex items-center justify-center text-blue-600">
                                <i class="bi bi-person text-5xl"></i>
                            </div>
                        @endif
                    </div>
                    <div class="text-center md:text-left flex-1">
                        <h1 class="text-3xl font-bold text-gray-800 tracking-tight m-0">{{ $user->name }}
                            {{ $user->middle_name }} {{ $user->last_name }}</h1>
                        <p
                            class="text-base text-gray-500 mt-2 m-0 flex flex-wrap items-center justify-center md:justify-start gap-3">
                            <span class="inline-flex items-center gap-1.5"><i class="bi bi-person-vcard text-gray-400"></i>
                                {{ $user->username }}</span>
                            <span class="text-gray-300 hidden md:inline">|</span>
                            <span class="inline-flex items-center gap-1.5"><i class="bi bi-star text-gray-400"></i>
                                {{ $user->roles->first()->name ?? 'Unassigned' }}</span>
                        </p>
                    </div>
                    <div class="mt-4 md:mt-0">
                        @if($user->status == 1)
                            <span
                                class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-green-100 text-green-800 border border-green-200">
                                <i class="bi bi-check-circle-fill mr-1.5 text-green-500"></i> Active User
                            </span>
                        @else
                            <span
                                class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-red-100 text-red-800 border border-red-200">
                                <i class="bi bi-x-circle-fill mr-1.5 text-red-500"></i> Disabled
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Details Grouping -->
                <div class="py-6" style="padding-left: 2rem; padding-right: 2rem;">
                    <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-4">Contact & Identity Info
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">

                        <div class="space-y-1">
                            <span class="block text-xs font-medium text-gray-500">Employee ID</span>
                            <span
                                class="block text-sm text-gray-800 font-semibold">{{ $user->employee_id ?: 'Not provided' }}</span>
                        </div>

                        <div class="space-y-1">
                            <span class="block text-xs font-medium text-gray-500">Email Address</span>
                            <span class="block text-sm text-gray-800 font-semibold">{{ $user->email ?: '-' }}</span>
                        </div>

                        <div class="space-y-1">
                            <span class="block text-xs font-medium text-gray-500">Phone Number</span>
                            <span class="block text-sm text-gray-800 font-semibold">{{ $user->phone_number ?: '-' }}</span>
                        </div>

                        <div class="space-y-1">
                            <span class="block text-xs font-medium text-gray-500">System Role</span>
                            <span
                                class="block text-sm text-gray-800 font-semibold">{{ $user->roles->first()->name ?? '-' }}</span>
                        </div>

                    </div>
                </div>

                @if(!empty($store))
                    <!-- Extended Access Section -->
                    <div class="py-6 border-t border-gray-100 bg-[#f8fafc]" style="padding-left: 2rem; padding-right: 2rem;">
                        <h4 class="text-sm font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="bi bi-hdd-network text-blue-500"></i> Assigned {{ $type }}
                        </h4>
                        <ul class="grid grid-cols-1 sm:grid-cols-2 gap-3 pl-0 mb-0" style="list-style-type: none;">
                            @forelse ($store as $item)
                                <li
                                    class="flex items-center gap-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg px-4 py-2.5 shadow-sm">
                                    <i class="bi bi-pin-map-fill text-green-500"></i> {{ $item }}
                                </li>
                            @empty
                                <li class="col-span-full text-sm text-gray-500 italic">No specific assignments found.</li>
                            @endforelse
                        </ul>
                    </div>
                @endif

            </div>
        </div>
    </div>
@endsection