@extends('layouts.app-master')

@push('css')
<link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
@endpush

@section('content')
<div class="bg-light p-4 rounded shadow-sm border-0">
    <h1 class="h3 mb-3"><i class="fa-solid fa-pen-to-square me-2"></i>{{ $page_title }}</h1>
    @include('layouts.partials.messages')
    <form method="POST" action="{{ route('menu.categories.store') }}" enctype="multipart/form-data" id="menuCategoryForm" class="mt-3">
        @csrf
        <div class="mb-3">
            <label class="form-label">Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" maxlength="191" required>
            @error('name')
                <span class="text-danger small d-block mt-1">{{ $message }}</span>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Slug</label>
            <input type="text" name="slug" class="form-control" value="{{ old('slug') }}" maxlength="191" placeholder="Auto from name if empty">
            @error('slug')
                <span class="text-danger small d-block mt-1">{{ $message }}</span>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
            @error('description')
                <span class="text-danger small d-block mt-1">{{ $message }}</span>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Image</label>
            <input type="file" name="image" class="form-control" accept="image/*">
            @error('image')
                <span class="text-danger small d-block mt-1">{{ $message }}</span>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-control" required>
                <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ old('status') === '0' ? 'selected' : '' }}>Inactive</option>
            </select>
            @error('status')
                <span class="text-danger small d-block mt-1">{{ $message }}</span>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Ordering</label>
            <input type="number" name="ordering" class="form-control" value="{{ old('ordering', 0) }}" min="0" required>
            @error('ordering')
                <span class="text-danger small d-block mt-1">{{ $message }}</span>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
        <a href="{{ route('menu.categories.index') }}" class="btn btn-secondary">Back</a>
    </form>
</div>
@endsection

@push('js')
<script src="{{ asset('assets/js/select2.min.js') }}"></script>
<script>
$('#menuCategoryForm').validate({
    rules: {
        name: { required: true, maxlength: 191 },
        ordering: { required: true, digits: true, min: 0 }
    },
    errorPlacement: function (error, element) {
        error.addClass('text-danger small d-block mt-1');
        error.insertAfter(element);
    },
    highlight: function (el) { $(el).addClass('is-invalid'); },
    unhighlight: function (el) { $(el).removeClass('is-invalid'); }
});
</script>
@endpush
