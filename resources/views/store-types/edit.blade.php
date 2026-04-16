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
        .form-input { width: 100%; height: 42px; border: 1px solid #e5e7eb; border-radius: 0.375rem; padding: 0 0.75rem; font-size: 0.875rem; color: #374151; transition: all 0.2s; outline: none; }
        .form-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 1px #3b82f6; }
        .form-textarea { width: 100%; min-height: 80px; border: 1px solid #e5e7eb; border-radius: 0.375rem; padding: 0.5rem 0.75rem; font-size: 0.875rem; color: #374151; transition: all 0.2s; outline: none; resize: vertical; }
        .form-textarea:focus { border-color: #3b82f6; box-shadow: 0 0 0 1px #3b82f6; }
        .form-label { display: block; font-size: 0.875rem; font-weight: 500; color: #374151; margin-bottom: 0.375rem; }
        label.error { color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem; display: block; }
    </style>
@endpush

@section('content')

<div class="px-6 pt-6 pb-10">
    <form method="POST" action="{{ route('store-types.update', $id) }}" class="max-w-3xl mx-auto">
        @csrf @method('PUT')

        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
            <div>
                <h2 class="text-2xl font-semibold text-gray-800">Edit Store Type</h2>
                <p class="text-sm text-gray-400 mt-0.5">Modify this Store Type entry.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('store-types.index') }}" class="px-4 py-2 border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors shadow-sm text-decoration-none">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 bg-primary text-white text-sm font-medium rounded-lg transition-colors shadow-sm cursor-pointer">
                    <i class="bi bi-save"></i> Save Changes
                </button>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-[#e5e7eb] shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center gap-2">
                <i class="bi bi-shop text-gray-400"></i>
                <h3 class="text-sm font-semibold text-gray-800 m-0">Store Type Details</h3>
            </div>
            <div class="p-5">
                <div>
                    <label for="name" class="form-label">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $storetype->name) }}" class="form-input" placeholder="Enter name" required>
                    @if ($errors->has('name'))
                        <span class="text-red-500 text-xs mt-1 block">{{ $errors->first('name') }}</span>
                    @endif
                </div>
                    <div class="mt-4">
                        <label for="description" class="form-label">Description <span class="text-red-500">*</span></label>
                        <textarea name="description" id="description" class="form-textarea" placeholder="Enter a brief description" required>{{ old('description', $storetype->description ?? '') }}</textarea>
                    </div>
            </div>
        </div>
    </form>
</div>

@endsection
