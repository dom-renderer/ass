@extends('layouts.app-master')

@push('css')
    <script src="{{ asset('assets/js/tailwindcss-cdn.js') }}"></script>
    <script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['DynamicAppFont', 'sans-serif'] } } } }</script>
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
    </style>
@endpush

@section('content')
    <div class="px-6 pt-6 pb-10">
        <form method="POST" action="{{ route('asset-statuses.store') }}" class="max-w-3xl mx-auto">
            @csrf
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-800">Add Asset Status</h2>
                    <p class="text-sm text-gray-400 mt-0.5">Define a new status label for the asset lifecycle.</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('asset-statuses.index') }}"
                        class="px-4 py-2 border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors shadow-sm text-decoration-none">Cancel</a>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm cursor-pointer"><i
                            class="bi bi-save"></i> Save</button>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-[#e5e7eb] shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center gap-2">
                    <i class="bi bi-flag text-gray-400"></i>
                    <h3 class="text-sm font-semibold text-gray-800 m-0">Status Details</h3>
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <label for="title" class="form-label">Title <span class="text-red-500">*</span></label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" class="form-input"
                            placeholder="e.g. Ready to Deploy" required>
                        @if ($errors->has('title'))<span
                        class="text-red-500 text-xs mt-1 block">{{ $errors->first('title') }}</span>@endif
                    </div>
                    <div>
                        <label for="color" class="form-label">Color <span class="text-red-500">*</span></label>
                        <input type="color" name="color" id="color" value="{{ old('color', '#000000') }}" class="form-input"
                            style="height: 42px; padding: 4px;" required>
                        @if ($errors->has('color'))<span
                        class="text-red-500 text-xs mt-1 block">{{ $errors->first('color') }}</span>@endif
                    </div>
                    <div>
                        <label class="form-label">Type <span class="text-red-500">*</span></label>
                        <div class="flex items-center gap-6 mt-1">
                            <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700">
                                <input type="radio" name="type" value="1" {{ old('type', '1') == '1' ? 'checked' : '' }}
                                    class="accent-blue-600"> Deployable
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700">
                                <input type="radio" name="type" value="2" {{ old('type') == '2' ? 'checked' : '' }}
                                    class="accent-blue-600"> Undeployable
                            </label>
                        </div>
                        @if ($errors->has('type'))<span
                        class="text-red-500 text-xs mt-1 block">{{ $errors->first('type') }}</span>@endif
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection