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
    </style>
@endpush

@section('content')

    <div class="px-6 pt-6 pb-10">
        <div class="max-w-3xl mx-auto">

            {{-- Page Header --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-800">View Asset Category</h2>
                    <p class="text-sm text-gray-400 mt-0.5">Read-only view of this asset category's details.</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('assets-categories.index') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors shadow-sm text-decoration-none">
                        <i class="bi bi-arrow-left"></i> Back to Categories
                    </a>
                </div>
            </div>

            {{-- Category Profile Card --}}
            <div class="bg-white rounded-xl border border-[#e5e7eb] shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center gap-2">
                    <i class="bi bi-tag text-gray-400 text-lg"></i>
                    <h3 class="text-sm font-semibold text-gray-800 m-0">Category Details</h3>
                </div>

                <div class="p-6">
                    <div class="rounded-lg border border-gray-100 bg-gray-50/30 p-5">
                        <span class="block text-xs font-semibold text-gray-400 tracking-wide uppercase mb-1">Asset Category
                            Name</span>
                        <span class="block text-lg text-gray-800 font-medium">{{ $storecategory->name }}</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

@endsection