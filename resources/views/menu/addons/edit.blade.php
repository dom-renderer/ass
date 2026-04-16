@extends('layouts.app-master')

@push('css')
<link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
@endpush

@section('content')
<div class="bg-light p-4 rounded shadow-sm border-0">
    <h1 class="h3 mb-3"><i class="fa-solid fa-pen-to-square me-2"></i>{{ $page_title }}</h1>
    @include('layouts.partials.messages')
    <form method="POST" action="{{ route('menu.addons.update', $addon->id) }}" id="addonForm" class="mt-3">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label class="form-label">Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $addon->name) }}" maxlength="191" required>
            @error('name')<span class="text-danger small d-block mt-1">{{ $message }}</span>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Price <span class="text-danger">*</span></label>
            <input type="number" step="0.01" min="0" name="price" class="form-control" value="{{ old('price', $addon->price) }}" required>
            @error('price')<span class="text-danger small d-block mt-1">{{ $message }}</span>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3">{{ old('description', $addon->description) }}</textarea>
            @error('description')<span class="text-danger small d-block mt-1">{{ $message }}</span>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-control" required>
                <option value="1" {{ old('status', $addon->status ? '1' : '0') == '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ old('status', $addon->status ? '1' : '0') == '0' ? 'selected' : '' }}>Inactive</option>
            </select>
            @error('status')<span class="text-danger small d-block mt-1">{{ $message }}</span>@enderror
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('menu.addons.index') }}" class="btn btn-secondary">Back</a>
    </form>
</div>
@endsection

@push('js')
<script src="{{ asset('assets/js/select2.min.js') }}"></script>
<script>
$('#addonForm').validate({
    rules: {
        name: { required: true, maxlength: 191 },
        price: { required: true, number: true, min: 0 }
    },
    errorPlacement: function (e, el) { e.addClass('text-danger small d-block mt-1'); e.insertAfter(el); },
    highlight: function (el) { $(el).addClass('is-invalid'); },
    unhighlight: function (el) { $(el).removeClass('is-invalid'); }
});
</script>
@endpush
