@extends('layouts.app-master')

@push('css')
    <script src="{{ asset('assets/js/tailwindcss-cdn.js') }}"></script>
@endpush

@section('content')
<div class="max-w-5xl mx-auto px-3 py-5">
    <form id="settingsForm" method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data"
        data-logo-required="{{ empty(optional($setting)->logo) ? 1 : 0 }}"
        data-app-logo-required="{{ empty(optional($setting)->app_logo) ? 1 : 0 }}"
        data-favicon-required="{{ empty(optional($setting)->favicon) ? 1 : 0 }}">
        @csrf
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 bg-slate-50">
                <h5 class="m-0 text-lg font-semibold text-slate-800">Application Settings</h5>
                <p class="m-0 mt-1 text-sm text-slate-500">Branding, theme colors, and maintenance controls</p>
            </div>
            <div class="p-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1" for="app_name">App Name</label>
                        <input type="text" name="app_name" id="app_name" class="form-control @error('app_name') is-invalid @enderror"
                            value="{{ old('app_name', optional($setting)->app_name) }}" maxlength="255" required>
                        @error('app_name')
                            <span class="text-danger small d-block mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1" for="default_font_id">Default Font</label>
                        <select name="default_font_id" id="default_font_id" class="form-control @error('default_font_id') is-invalid @enderror">
                            <option value="">Select font (optional)</option>
                            @foreach($fonts as $font)
                                <option value="{{ $font->id }}" {{ (string) old('default_font_id', optional($setting)->default_font_id) === (string) $font->id ? 'selected' : '' }}>
                                    {{ $font->title }}
                                </option>
                            @endforeach
                        </select>
                        @error('default_font_id')
                            <span class="text-danger small d-block mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1" for="app_description">App Description</label>
                        <textarea name="app_description" id="app_description" rows="3"
                            class="form-control @error('app_description') is-invalid @enderror"
                            maxlength="2000" required>{{ old('app_description', optional($setting)->app_description) }}</textarea>
                        @error('app_description')
                            <span class="text-danger small d-block mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1" for="primary_theme_colour">Primary Theme Colour</label>
                        <input type="color" name="primary_theme_colour" id="primary_theme_colour"
                            class="form-control @error('primary_theme_colour') is-invalid @enderror"
                            value="{{ old('primary_theme_colour', optional($setting)->primary_theme_colour ?: '#0d6efd') }}" required>
                        @error('primary_theme_colour')
                            <span class="text-danger small d-block mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1" for="primary_font_colour">Primary Font Colour</label>
                        <input type="color" name="primary_font_colour" id="primary_font_colour"
                            class="form-control @error('primary_font_colour') is-invalid @enderror"
                            value="{{ old('primary_font_colour', optional($setting)->primary_font_colour ?: '#111827') }}" required>
                        @error('primary_font_colour')
                            <span class="text-danger small d-block mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1" for="logo">Logo</label>
                        <input type="file" name="logo" id="logo"
                            class="form-control @error('logo') is-invalid @enderror" accept=".jpg,.jpeg,.png,.svg,.webp"
                            {{ empty(optional($setting)->logo) ? 'required' : '' }}>
                        @error('logo')
                            <span class="text-danger small d-block mt-1">{{ $message }}</span>
                        @enderror
                        @if(!empty(optional($setting)->logo))
                            <img id="logo-preview" src="{{ asset('storage/' . $setting->logo) }}" alt="Logo Preview" class="mt-2 rounded border border-slate-300 p-1 bg-white" style="max-height:70px;">
                        @else
                            <img id="logo-preview" src="" alt="Logo Preview" class="mt-2 rounded border border-slate-300 p-1 bg-white d-none" style="max-height:70px;">
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1" for="app_logo">App Logo (1024x1024)</label>
                        <input type="file" name="app_logo" id="app_logo"
                            class="form-control @error('app_logo') is-invalid @enderror" accept=".jpg,.jpeg,.png,.webp"
                            {{ empty(optional($setting)->app_logo) ? 'required' : '' }}>
                        @error('app_logo')
                            <span class="text-danger small d-block mt-1">{{ $message }}</span>
                        @enderror
                        @if(!empty(optional($setting)->app_logo))
                            <img id="app-logo-preview" src="{{ asset('storage/' . $setting->app_logo) }}" alt="App Logo Preview" class="mt-2 rounded border border-slate-300 p-1 bg-white" style="max-height:70px;">
                        @else
                            <img id="app-logo-preview" src="" alt="App Logo Preview" class="mt-2 rounded border border-slate-300 p-1 bg-white d-none" style="max-height:70px;">
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1" for="favicon">Favicon</label>
                        <input type="file" name="favicon" id="favicon"
                            class="form-control @error('favicon') is-invalid @enderror" accept=".ico,.jpg,.jpeg,.png,.svg,.webp"
                            {{ empty(optional($setting)->favicon) ? 'required' : '' }}>
                        @error('favicon')
                            <span class="text-danger small d-block mt-1">{{ $message }}</span>
                        @enderror
                        @if(!empty(optional($setting)->favicon))
                            <img id="favicon-preview" src="{{ asset('storage/' . $setting->favicon) }}" alt="Favicon Preview" class="mt-2 rounded border border-slate-300 p-1 bg-white" style="max-height:40px;">
                        @else
                            <img id="favicon-preview" src="" alt="Favicon Preview" class="mt-2 rounded border border-slate-300 p-1 bg-white d-none" style="max-height:40px;">
                        @endif
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-2">App Maintenance Mode</label>
                        <div class="maintenance-option form-check form-check-inline me-4">
                            <input type="radio" name="maintenance_mode" value="1" class="form-check-input @error('maintenance_mode') is-invalid @enderror" id="maintenance_mode_on"
                                {{ old('maintenance_mode', isset($setting) ? (int) $setting->maintenance_mode : 0) == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="maintenance_mode_on">On</label>
        </div>
                        <div class="maintenance-option form-check form-check-inline">
                            <input type="radio" name="maintenance_mode" value="0" class="form-check-input @error('maintenance_mode') is-invalid @enderror" id="maintenance_mode_off"
                                {{ old('maintenance_mode', isset($setting) ? (int) $setting->maintenance_mode : 0) == 0 ? 'checked' : '' }}>
                            <label class="form-check-label" for="maintenance_mode_off">Off</label>
                        </div>
                        @error('maintenance_mode')
                            <span class="text-danger small d-block mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="px-5 py-4 border-t border-slate-200 bg-slate-50">
                <button class="btn btn-primary">Save Settings</button>
            </div>
        </div>
    </form>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mt-5">
        <div class="px-5 py-4 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
            <div>
                <h5 class="m-0 text-lg font-semibold text-slate-800">Font Management</h5>
                <p class="m-0 mt-1 text-sm text-slate-500">Create and manage normal/bold/italic font files</p>
            </div>
        </div>
        <div class="p-5">
            <form method="POST" action="{{ route('settings.fonts.store') }}" enctype="multipart/form-data" class="mb-5">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Font Title</label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="e.g. Poppins" required>
                        @error('title')
                            <span class="text-danger small d-block mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Normal Font File *</label>
                        <input type="file" name="normal_file" class="form-control @error('normal_file') is-invalid @enderror" accept=".ttf,.otf,.woff,.woff2" required>
                        @error('normal_file')
                            <span class="text-danger small d-block mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Bold Font File</label>
                        <input type="file" name="bold_file" class="form-control @error('bold_file') is-invalid @enderror" accept=".ttf,.otf,.woff,.woff2">
                        @error('bold_file')
                            <span class="text-danger small d-block mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Italic Font File</label>
                        <input type="file" name="italic_file" class="form-control @error('italic_file') is-invalid @enderror" accept=".ttf,.otf,.woff,.woff2">
                        @error('italic_file')
                            <span class="text-danger small d-block mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Bold Italic Font File</label>
                        <input type="file" name="bold_italic_file" class="form-control @error('bold_italic_file') is-invalid @enderror" accept=".ttf,.otf,.woff,.woff2">
                        @error('bold_italic_file')
                            <span class="text-danger small d-block mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Add Font</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="bg-slate-50">
                        <tr>
                            <th>Title</th>
                            <th>Normal</th>
                            <th>Bold</th>
                            <th>Italic</th>
                            <th>Bold Italic</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($fonts as $font)
                            <tr>
                                <td>{{ $font->title }}</td>
                                <td>{{ basename((string) $font->normal_file) }}</td>
                                <td>{{ $font->bold_file ? basename((string) $font->bold_file) : '-' }}</td>
                                <td>{{ $font->italic_file ? basename((string) $font->italic_file) : '-' }}</td>
                                <td>{{ $font->bold_italic_file ? basename((string) $font->bold_italic_file) : '-' }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary edit-font-btn"
                                        data-id="{{ $font->id }}"
                                        data-title="{{ $font->title }}">
                                        Edit
                                    </button>
                                    <form method="POST" action="{{ route('settings.fonts.delete', $font->id) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this font?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No fonts added yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editFontModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="editFontForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Edit Font</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Font Title</label>
                        <input type="text" name="title" id="edit-font-title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Normal Font File (leave empty to keep existing)</label>
                        <input type="file" name="normal_file" class="form-control" accept=".ttf,.otf,.woff,.woff2">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bold Font File</label>
                        <input type="file" name="bold_file" class="form-control" accept=".ttf,.otf,.woff,.woff2">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Italic Font File</label>
                        <input type="file" name="italic_file" class="form-control" accept=".ttf,.otf,.woff,.woff2">
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Bold Italic Font File</label>
                        <input type="file" name="bold_italic_file" class="form-control" accept=".ttf,.otf,.woff,.woff2">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Font</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="{{ asset('assets/js/jquery-validate.min.js') }}"></script>
    <script>
        function bindPreview(inputSelector, previewSelector) {
            $(inputSelector).on('change', function () {
                const file = this.files && this.files[0] ? this.files[0] : null;
                if (!file) {
                    return;
                }
                const reader = new FileReader();
                reader.onload = function (e) {
                    $(previewSelector).attr('src', e.target.result).removeClass('d-none');
                };
                reader.readAsDataURL(file);
            });
        }

        bindPreview('#logo', '#logo-preview');
        bindPreview('#app_logo', '#app-logo-preview');
        bindPreview('#favicon', '#favicon-preview');
        const logoRequired = Number($('#settingsForm').data('logo-required')) === 1;
        const appLogoRequired = Number($('#settingsForm').data('app-logo-required')) === 1;
        const faviconRequired = Number($('#settingsForm').data('favicon-required')) === 1;

        $('#settingsForm').validate({
            rules: {
                app_name: { required: true, maxlength: 255 },
                app_description: { required: true, maxlength: 2000 },
                logo: {
                    required: logoRequired,
                    extension: "jpg|jpeg|png|svg|webp"
                },
                app_logo: {
                    required: appLogoRequired,
                    extension: "jpg|jpeg|png|webp"
                },
                favicon: {
                    required: faviconRequired,
                    extension: "ico|jpg|jpeg|png|svg|webp"
                },
                primary_theme_colour: { required: true, pattern: /^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/ },
                primary_font_colour: { required: true, pattern: /^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/ },
                default_font_id: { digits: true, min: 1 },
                maintenance_mode: { required: true }
            },
            messages: {
                app_name: { required: "App Name is required.", maxlength: "Maximum 255 characters allowed." },
                app_description: { required: "App Description is required.", maxlength: "Maximum 2000 characters allowed." },
                logo: { required: "Logo is required.", extension: "Allowed logo types: jpg, jpeg, png, svg, webp." },
                app_logo: {
                    required: "App Logo is required.",
                    extension: "Allowed app logo types: jpg, jpeg, png, webp."
                },
                favicon: { required: "Favicon is required.", extension: "Allowed favicon types: ico, jpg, jpeg, png, svg, webp." },
                primary_theme_colour: { required: "Primary Theme Colour is required.", pattern: "Enter a valid HEX color like #0d6efd." },
                primary_font_colour: { required: "Primary Font Colour is required.", pattern: "Enter a valid HEX color like #ffffff." },
                default_font_id: { digits: "Default Font Id must be a number.", min: "Default Font Id must be at least 1." },
                maintenance_mode: { required: "Please select maintenance mode." }
            },
            errorPlacement: function(error, element) {
                error.addClass('text-danger small d-block mt-1');
                if (element.attr('name') === 'maintenance_mode') {
                    error.insertAfter($('.maintenance-option').last());
                } else {
                    error.insertAfter(element);
                }
            },
            highlight: function(element) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function(element) {
                $(element).removeClass('is-invalid');
            }
        });

        $(document).on('click', '.edit-font-btn', function () {
            const id = $(this).data('id');
            const title = $(this).data('title');
            $('#edit-font-title').val(title);
            $('#editFontForm').attr('action', "{{ url('/settings/fonts') }}/" + id + "/update");
            $('#editFontModal').modal('show');
        });
    </script>
@endpush
