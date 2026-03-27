@extends('layouts.app-master')

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/custom-select-style.css') }}">
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />
    <style>
        .bg-gradient-primary {
            background: linear-gradient(135deg, #0d5d31 0%, #327350 100%);
        }

        .step-card {
            transition: all 0.3s ease;
        }

        .step-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
        }

        .step-item {
            border-left: 4px solid #667eea;
        }

        .drag-handle {
            cursor: move;
        }

        .drag-handle:hover {
            background-color: rgba(255, 255, 255, 0.2) !important;
        }

        .step-number {
            font-size: 0.9rem;
            font-weight: 600;
        }

        .form-label {
            color: #495057;
            margin-bottom: 0.5rem;
        }

        .card-header h6 {
            font-weight: 600;
        }

        .dependency-steps-container {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 0.375rem;
            border: 1px solid #e9ecef;
        }

        .form-text {
            font-size: 0.875rem;
            color: #6c757d;
        }

        .btn-outline-light:hover {
            background-color: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.3);
        }

        .select2-container--default .select2-selection--multiple {
            min-height: 38px;
            border: 1px solid #ced4da;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #667eea;
            border: 1px solid #667eea;
        }

        .section-item {
            border: 1px solid #e9ecef;
            border-radius: 0.375rem;
            margin-bottom: 0.75rem;
            cursor: pointer;
            transition: all 0.2s ease;
            background-color: #f8f9fa;
        }

        .section-item:hover {
            background-color: #e9ecef;
            border-color: #dee2e6;
        }

        .section-item.active {
            background-color: #28a745;
            border-color: #28a745;
            color: white;
        }

        .section-item.active .text-muted {
            color: rgba(255, 255, 255, 0.8) !important;
        }

        .section-drag-handle {
            cursor: move;
            padding: 0.5rem;
            color: #6c757d;
        }

        .section-drag-handle:hover {
            color: #495057;
        }

        .section-item.active .section-drag-handle {
            color: rgba(255, 255, 255, 0.8);
        }

        .section-code {
            font-family: 'Courier New', monospace;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .section-steps-count {
            font-size: 0.75rem;
            color: #6c757d;
        }

        .section-item.active .section-steps-count {
            color: rgba(255, 255, 255, 0.8);
        }

        .section-number {
            font-size: 0.75rem;
            font-weight: 600;
            color: #6c757d;
        }

        .section-item.active .section-number {
            color: rgba(255, 255, 255, 0.8);
        }

        .form-hint {
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }

        .character-counter {
            font-size: 0.75rem;
            color: #6c757d;
        }

        .steps-placeholder {
            text-align: center;
            padding: 2rem;
            color: #6c757d;
        }

        .steps-placeholder i {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
        }
    </style>
@endpush

@section('content')
    <div class="p-4 rounded">
        <form action="{{ route('workflow-templates.update', $template->id) }}" method="post" id="templateForm" novalidate>
            @csrf
            @method('PUT')

            <div id="validationErrorsContainer" class="d-none"></div>

            <div class="row g-3 mb-4">
                <div class="col-md-12">
                    <label class="form-label">Title <span class="text-danger"> * </span> </label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $template->title) }}"
                        required>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Description <span class="text-danger"> * </span> </label>
                    <input type="text" name="description" class="form-control"
                        value="{{ old('description', $template->description) }}">
                </div>

                <div class="col-md-12">
                    <label class="form-label">Status <span class="text-danger"> * </span> </label>
                    <select name="status" id="status" class="form-control">
                        <option value="1" @if($template->status == 1) selected @endif> Active </option>
                        <option value="0" @if($template->status != 1) selected @endif> InActive </option>
                    </select>
                </div>

                <div class="col-md-12">
                    <input type="checkbox" style="height:20px;width:20px;" value="1" name="send_to_all_notification" id="send_to_all_notification" @if($template->send_to_all_notification == 1) checked @endif>
                    <label class="form-label" for="send_to_all_notification" style="position: relative;bottom: 5px;" > Send all Notification to Maker </label>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Project Departments</h6>
                            <button type="button" class="btn btn-success btn-sm" id="addSection">
                                <i class="bi bi-plus-circle me-1"></i>Add Department
                            </button>
                        </div>
                        <div class="card-body p-3">
                            <p class="text-muted small mb-3">Drag to reorder departments. Click to edit details.</p>
                            <div id="sectionsContainer">
                                @if($template->sections && count($template->sections) > 0)
                                    @foreach($template->sections as $index => $section)
                                        @php
                                            $sectionSteps = $template->children->where('section_id', $section['id'])->sortBy('step_order');
                                        @endphp
                                        <div class="section-item" data-section-id="{{ $section['id'] }}">
                                            <div class="d-flex align-items-center p-3">
                                                <div class="section-drag-handle me-2">
                                                    <i class="bi bi-grip-vertical"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="fw-semibold section-name">{{ $section['name'] }}</div>
                                                    <div class="section-code text-muted">{{ $section['code'] }}</div>
                                                    <div class="text-muted small section-description">
                                                        {{ $section['description'] ?? 'No description provided' }}
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                                        <span class="section-steps-count">{{ $sectionSteps->count() }} tasks</span>
                                                        <span class="section-number">Section {{ $index + 1 }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Department Details</h6>
                                <small class="text-muted">Configure the details for this project department</small>
                            </div>
                            <div>
                                <button type="button" class="btn btn-outline-secondary btn-sm me-2" id="cloneSection"
                                    disabled>
                                    <i class="bi bi-files me-1"></i>Clone
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" id="deleteSection" disabled>
                                    <i class="bi bi-trash me-1"></i>Delete
                                </button>
                            </div>
                        </div>
                        <div class="card-body" id="sectionDetailsContainer">
                            <div class="text-center py-5">
                                <i class="bi bi-list-ol text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-3">Select a department to configure its details</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button class="btn btn-success mt-4">Save</button>
        </form>
    </div>
@endsection

@push('js')
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        $(document).ready(function () {
            let sectionIndex = {{ count($template->sections ?? []) }};
            let currentSectionId = null;
            let sections = {!! json_encode($template->sections ?? []) !!};
            let globalStepCounter = {{ $template->children->max('step') ?? 0 }};

            if (Array.isArray(sections)) {
                const sectionsObj = {};
                sections.forEach((section, index) => {
                    const sectionId = section.id;
                    sectionsObj[sectionId] = {
                        id: sectionId,
                        name: section.name || '',
                        code: section.code || '',
                        description: section.description || '',
                        steps: []
                    };
                });
                sections = sectionsObj;
            }

            @if($template->children && count($template->children) > 0)
                @foreach($template->children as $step)
                    @if(isset($step->section_id))
                        sections['{{ $step->section_id }}'].steps.push({
                            id: '{{ $step->id }}',
                            record_id: {{ $step->id }},
                            globalNumber: {{ $step->step }},
                            step_name: '{{ $step->step_name }}',
                            department_id: '{{ $step->department_id }}',
                            checklist_id: '{{ $step->checklist_id }}',
                            checklist_description: '{{ $step->checklist_description }}',
                            trigger: {{ $step->trigger }},
                            dependency: '{{ $step->dependency }}',
                            dependency_steps: {!! json_encode($step->dependency_steps ?? []) !!},
                            is_entry_point: {{ $step->is_entry_point ? 'true' : 'false' }},
                            user_id: '{{ $step->user_id }}',
                            maker_turn_around_time_day: '{{ $step->maker_turn_around_time_day }}',
                            maker_turn_around_time_hour: '{{ $step->maker_turn_around_time_hour }}',
                            maker_turn_around_time_minute: '{{ $step->maker_turn_around_time_minute }}',
                            maker_escalation_user_id: '{{ $step->maker_escalation_user_id }}',
                            maker_escalation_user_ids: {!! json_encode($step->maker_escalation_user_ids ?? []) !!},
                            maker_escalation_user_names: {!! json_encode($step->makerEscalationUsers()->pluck('name')->toArray()) !!},
                            maker_escalation_after_day: '{{ $step->maker_escalation_after_day }}',
                            maker_escalation_after_hour: '{{ $step->maker_escalation_after_hour }}',
                            maker_escalation_after_minute: '{{ $step->maker_escalation_after_minute }}',
                            maker_escalation_email_notification: '{{ $step->maker_escalation_email_notification }}',
                            maker_escalation_push_notification: '{{ $step->maker_escalation_push_notification }}',
                            checker_id: '{{ $step->checker_id }}',
                            checker_turn_around_time_day: '{{ $step->checker_turn_around_time_day }}',
                            checker_turn_around_time_hour: '{{ $step->checker_turn_around_time_hour }}',
                            checker_turn_around_time_minute: '{{ $step->checker_turn_around_time_minute }}',
                            checker_escalation_user_id: '{{ $step->checker_escalation_user_id }}',
                            checker_escalation_user_ids: {!! json_encode($step->checker_escalation_user_ids ?? []) !!},
                            checker_escalation_user_names: {!! json_encode($step->checkerEscalationUsers()->pluck('name')->toArray()) !!},
                            checker_escalation_after_day: '{{ $step->checker_escalation_after_day }}',
                            checker_escalation_after_hour: '{{ $step->checker_escalation_after_hour }}',
                            checker_escalation_after_minute: '{{ $step->checker_escalation_after_minute }}',
                            checker_escalation_email_notification: '{{ $step->checker_escalation_email_notification }}',
                            checker_escalation_push_notification: '{{ $step->checker_escalation_push_notification }}',
                            department: {!! json_encode($step->department) !!},
                            checklist: {!! json_encode($step->checklist) !!},
                            user: {!! json_encode($step->user) !!},
                            makerEscalationUser: {!! json_encode($step->makerEscalationUser) !!},
                            checker: {!! json_encode($step->checker) !!},
                            checkerEscalationUser: {!! json_encode($step->checkerEscalationUser) !!},
                            makerEscalationEmailNotification: {!! json_encode($step->makerEscalationEmailNotification) !!},
                            makerEscalationPushNotification: {!! json_encode($step->makerEscalationPushNotification) !!},
                            checkerEscalationEmailNotification: {!! json_encode($step->checkerEscalationEmailNotification) !!},
                            checkerEscalationPushNotification: {!! json_encode($step->checkerEscalationPushNotification) !!},
                            makerCompletionEmailNotification: {!! json_encode($step->makerCompletionEmailNotification) !!},
                            makerCompletionPushNotification: {!! json_encode($step->makerCompletionPushNotification) !!},
                            maker_completion_email_notification: '{{ $step->maker_completion_email_notification }}',
                            maker_completion_push_notification: '{{ $step->maker_completion_push_notification }}',
                            makerDependencyEmailNotification: {!! json_encode($step->makerDependencyEmailNotification) !!},
                            makerDependencyPushNotification: {!! json_encode($step->makerDependencyPushNotification) !!},
                            maker_dependency_email_notification: '{{ $step->maker_dependency_email_notification }}',
                            maker_dependency_push_notification: '{{ $step->maker_dependency_push_notification }}'
                        });
                    @endif
                @endforeach
            @endif

                function generateSectionCode(name) {
                    if (!name) return '';
                    return name.toUpperCase()
                        .replace(/[^A-Z0-9\s]/g, '')
                        .replace(/\s+/g, '_')
                        .substring(0, 20);
                }

            function getAllStepsExceptCurrent(currentStepId) {
                const allSteps = [];
                Object.values(sections).forEach(section => {
                    section.steps.forEach(step => {
                        if (String(step.id) !== String(currentStepId)) {
                            allSteps.push({
                                id: step.id,
                                stepNumber: step.globalNumber,
                                text: `Task ${step.globalNumber}${step.step_name ? ' - ' + step.step_name : ''}`
                            });
                        }
                    });
                });
                return allSteps;
            }

            function generateDependencyStepsOptions(currentStepId, selectedSteps) {
                const allSteps = getAllStepsExceptCurrent(currentStepId);
                const selectedArray = Array.isArray(selectedSteps) ? selectedSteps.map(s => String(s)) : [];
                return allSteps.map(step =>
                    `<option value="${step.id}" ${selectedArray.includes(String(step.id)) ? 'selected' : ''}>${step.text}</option>`
                ).join('');
            }

            function refreshAllDependencyDropdowns() {
                Object.values(sections).forEach(section => {
                    section.steps.forEach(step => {
                        const stepCard = $(`.step-card[data-step-id="${step.id}"]`);
                        if (stepCard.length > 0) {
                            const depSelect = stepCard.find('.dep-steps');
                            if (depSelect.length > 0) {
                                const currentSelected = depSelect.val() || [];
                                const newOptions = generateDependencyStepsOptions(step.id, currentSelected);
                                depSelect.html(newOptions);
                            }
                        }
                    });
                });
            }

            function addSection() {
                sectionIndex++;
                const sectionId = `section_${sectionIndex}`;
                const sectionNumber = sectionIndex;

                const sectionData = {
                    id: sectionId,
                    name: '',
                    code: '',
                    description: '',
                    steps: []
                };

                sections[sectionId] = sectionData;

                const sectionItem = $(`
                                                <div class="section-item" data-section-id="${sectionId}">
                                                    <div class="d-flex align-items-center p-3">
                                                        <div class="section-drag-handle me-2">
                                                            <i class="bi bi-grip-vertical"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <div class="fw-semibold section-name">New Department</div>
                                                            <div class="section-code text-muted">NEW_DEPARTMENT</div>
                                                            <div class="text-muted small section-description">No description provided</div>
                                                            <div class="d-flex justify-content-between align-items-center mt-1">
                                                                <span class="section-steps-count">0 tasks</span>
                                                                <span class="section-number">Section ${sectionNumber}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            `);

                $('#sectionsContainer').append(sectionItem);
                sectionItem.click(function () {
                    selectSection(sectionId);
                });

                selectSection(sectionId);
                initializeSortable();
            }

            function selectSection(sectionId) {

                if (currentSectionId && sections[currentSectionId]) {
                    saveCurrentStepData(currentSectionId);
                }

                $('.section-item').removeClass('active');
                $(`.section-item[data-section-id="${sectionId}"]`).addClass('active');

                currentSectionId = sectionId;
                $('#cloneSection, #deleteSection').prop('disabled', false);

                const section = sections[sectionId];
                renderSectionDetails(section);
            }

            function getStepDisplayName(step, field) {
                const fieldMappings = {
                    'department': { nested: 'department', flat: 'department_name', prop: 'name' },
                    'checklist': { nested: 'checklist', flat: 'checklist_name', prop: 'name' },
                    'user': { nested: 'user', flat: 'user_name', prop: 'name', fullName: true },
                    'checker': { nested: 'checker', flat: 'checker_name', prop: 'name', fullName: true },
                    'makerEscalationUser': { nested: 'makerEscalationUser', flat: 'maker_escalation_user_name', prop: 'name', fullName: true },
                    'checkerEscalationUser': { nested: 'checkerEscalationUser', flat: 'checker_escalation_user_name', prop: 'name', fullName: true },
                    'makerEscalationEmailNotification': { nested: 'makerEscalationEmailNotification', flat: 'maker_escalation_email_name', prop: 'name' },
                    'makerEscalationPushNotification': { nested: 'makerEscalationPushNotification', flat: 'maker_escalation_push_name', prop: 'name' },
                    'checkerEscalationEmailNotification': { nested: 'checkerEscalationEmailNotification', flat: 'checker_escalation_email_name', prop: 'name' },
                    'checkerEscalationPushNotification': { nested: 'checkerEscalationPushNotification', flat: 'checker_escalation_push_name', prop: 'name' },
                    'makerCompletionEmailNotification': { nested: 'makerCompletionEmailNotification', flat: 'maker_completion_email_name', prop: 'name' },
                    'makerCompletionPushNotification': { nested: 'makerCompletionPushNotification', flat: 'maker_completion_push_name', prop: 'name' },
                    'makerDependencyEmailNotification': { nested: 'makerDependencyEmailNotification', flat: 'maker_dependency_email_name', prop: 'name' },
                    'makerDependencyPushNotification': { nested: 'makerDependencyPushNotification', flat: 'maker_dependency_push_name', prop: 'name' }
                };

                const mapping = fieldMappings[field];
                if (!mapping) return '';

                if (step[mapping.flat]) {
                    return step[mapping.flat];
                }

                if (step[mapping.nested]) {
                    if (mapping.fullName && step[mapping.nested]) {
                        const obj = step[mapping.nested];
                        return `${obj.employee_id || ''} - ${obj.name || ''} ${obj.middle_name || ''} ${obj.last_name || ''}`.trim();
                    }
                    return step[mapping.nested][mapping.prop] || '';
                }

                return '';
            }

            function renderStepsForSection(section) {
                if (section.steps.length === 0) {
                    return `
                                                    <div class="text-center py-4 text-muted">
                                                        <i class="bi bi-list-ol" style="font-size: 2rem;"></i>
                                                        <p class="mt-2 mb-0">No tasks added yet. Click "Add Task" to get started.</p>
                                                    </div>
                                                `;
                }

                return section.steps.map(step => `
                                                <div class="step-card mb-3" data-step-id="${step.id}">
                                                    ${!isNaN(step.id) && step.id > 0 ? ('<input type="hidden" name="sections[' + section.id + '][steps][' + step.id + '][record_id]" value="' + step.id + '"/>') : ''}
                                                    <div class="card border-0 shadow-sm step-item">
                                                        <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center">
                                                            <div class="d-flex align-items-center">
                                                                <span class="step-number badge bg-white text-primary me-2">S${step.globalNumber}</span>
                                                                <h6 class="mb-0">Step ${step.globalNumber}</h6>
                                                                <h5 class="mb-0 nearest-step-heading"></h5>
                                                            </div>
                                                            <div class="d-flex align-items-center">
                                                                <div class="form-check form-switch me-4">
                                                                    <label class="form-check-label" for="switchCheckChecked-${step.id}"> Entry Point </label>
                                                                    <input class="form-check-input" type="checkbox" role="switch" id="switchCheckChecked-${step.id}" name="sections[${section.id}][steps][${step.id}][is_entry_point]" ${step.is_entry_point ? 'checked' : ''}>
                                                                </div>
                                                                <button type="button" class="btn btn-sm btn-outline-light btn-danger remove-step" data-step-id="${step.id}">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row g-3">
                                                                <div class="col-md-4">
                                                                    <label class="form-label fw-semibold">Task Name <span class="text-danger">*</span></label>
                                                                    <input type="text" class="form-control step-name" name="sections[${section.id}][steps][${step.id}][step_name]" value="${step.step_name || ''}" required>
                                                                    <input type="hidden" class="step-input" name="sections[${section.id}][steps][${step.id}][step]" value="${step.globalNumber}">
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label class="form-label fw-semibold">Department <span class="text-danger">*</span></label>
                                                                    <select class="form-select select2" data-whichselect="departments-list" id="step-${step.id}-department_id" name="sections[${section.id}][steps][${step.id}][department_id]" required>
                                                                    ${step.department_id ? `<option value="${step.department_id}" selected>${getStepDisplayName(step, 'department') || 'Selected Department'}</option>` : ''}
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label class="form-label fw-semibold">Checklist <span class="text-danger">*</span></label>
                                                                    <select class="form-select select2" data-whichselect="checklists-list" id="step-${step.id}-checklist_id" name="sections[${section.id}][steps][${step.id}][checklist_id]" required>
                                                                    ${step.checklist_id ? `<option value="${step.checklist_id}" selected>${getStepDisplayName(step, 'checklist') || 'Selected Checklist'}</option>` : ''}
                                                                    </select>
                                                                </div>

                                                                <div class="col-md-12">
                                                                    <label class="form-label fw-semibold">Checklist Description</label>
                                                                    <textarea class="form-control" name="sections[${section.id}][steps][${step.id}][checklist_description]" rows="2" placeholder="Describe what needs to be done...">${step.checklist_description || ''}</textarea>
                                                                </div>

                                                                <div class="col-md-6">
                                                                    <label class="form-label fw-semibold">Trigger</label>
                                                                    <select class="form-select select2" name="sections[${section.id}][steps][${step.id}][trigger]" required>
                                                                        <option value="0" ${step.trigger == 0 ? 'selected' : ''}>Auto</option>
                                                                        <option value="1" ${step.trigger == 1 ? 'selected' : ''}>Manual</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label fw-semibold">Dependency</label>
                                                                    <select class="form-select dependency-select select2" name="sections[${section.id}][steps][${step.id}][dependency]" required>
                                                                        <option value="NO_DEPENDENCY" ${step.dependency == 'NO_DEPENDENCY' ? 'selected' : ''}>No Dependency</option>
                                                                        <option value="SELECTED_COMPLETED" ${step.dependency == 'SELECTED_COMPLETED' ? 'selected' : ''}>Selected Tasks</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-12 dependency-steps-container ${step.dependency == 'SELECTED_COMPLETED' ? '' : 'd-none'}">
                                                                    <label class="form-label fw-semibold">Select Dependent Tasks</label>
                                                                    <select class="form-select dep-steps select2" multiple name="sections[${section.id}][steps][${step.id}][dependency_steps][]" data-step-id="${step.id}">
                                                                        ${generateDependencyStepsOptions(step.id, step.dependency_steps || [])}
                                                                    </select>
                                                                    <div class="form-text">Choose which specific tasks must be completed before this task can start.</div>
                                                                </div>

                                                                <div class="accordion" id="accordionExample-${step.id}">
                                                                    <div class="accordion-item">
                                                                        <h2 class="accordion-header">
                                                                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-maker-${step.id}" aria-expanded="true" aria-controls="collapse-maker-${step.id}">
                                                                                Maker
                                                                            </button>
                                                                        </h2>
                                                                        <div id="collapse-maker-${step.id}" class="accordion-collapse collapse" data-bs-parent="#accordionExample-${step.id}">
                                                                            <div class="accordion-body row">
                                                                                <div class="col-md-6">
                                                                                    <label class="form-label fw-semibold">Maker <span class="text-danger">*</span></label>
                                                                                    <select class="form-select select2" data-whichselect="maker-list" id="step-${step.id}-user_id" name="sections[${section.id}][steps][${step.id}][user_id]" required>
                                                                                        ${step.user_id ? `<option value="${step.user_id}" selected>${getStepDisplayName(step, 'user') || 'Selected User'}</option>` : ''}
                                                                                    </select>
                                                                                </div>
                                                                                <div class="col-md-6">
                                                                                    <label class="form-label fw-semibold">Turnaround Time </label>
                                                                                    <div class="input-group">
                                                                                        <span class="input-group-text">Days</span>
                                                                                        <input type="number" class="form-control" name="sections[${section.id}][steps][${step.id}][maker_turn_around_time_day]" placeholder="Enter days" value="${step.maker_turn_around_time_day || ''}">
                                                                                        <span class="input-group-text">Hours</span>
                                                                                        <input type="number" class="form-control" name="sections[${section.id}][steps][${step.id}][maker_turn_around_time_hour]" placeholder="Enter hours" value="${step.maker_turn_around_time_hour || ''}">
                                                                                        <span class="input-group-text">Minutes</span>
                                                                                        <input type="number" class="form-control" name="sections[${section.id}][steps][${step.id}][maker_turn_around_time_minute]" placeholder="Enter minutes" value="${step.maker_turn_around_time_minute || ''}">
                                                                                    </div>
                                                                                </div>
                                                                                <hr class="mt-4 mb-4">
                                                                                <h5>Completion Notification</h5>
                                                                                <div class="col-md-6 mt-2">
                                                                                    <label class="form-label fw-semibold">Email Notification Template</label>
                                                                                    <select class="form-select select2" data-whichselect="maker-completion-email-list" id="step-${step.id}-maker_completion_email_notification" name="sections[${section.id}][steps][${step.id}][maker_completion_email_notification]" >
                                                                                        ${step.maker_completion_email_notification ? `<option value="${step.maker_completion_email_notification}" selected>${getStepDisplayName(step, 'makerCompletionEmailNotification') || 'Selected Template'}</option>` : ''}
                                                                                    </select>
                                                                                </div>
                                                                                <div class="col-md-6 mt-2">
                                                                                    <label class="form-label fw-semibold">Push Notification Template</label>
                                                                                    <select class="form-select select2" data-whichselect="maker-completion-push-list" id="step-${step.id}-maker_completion_push_notification" name="sections[${section.id}][steps][${step.id}][maker_completion_push_notification]" >
                                                                                        ${step.maker_completion_push_notification ? `<option value="${step.maker_completion_push_notification}" selected>${getStepDisplayName(step, 'makerCompletionPushNotification') || 'Selected Template'}</option>` : ''}
                                                                                    </select>
                                                                                </div>
                                                                                <hr class="mt-4 mb-4">
                                                                                <h5>Dependency Notification</h5>
                                                                                <div class="col-md-6 mt-2">
                                                                                    <label class="form-label fw-semibold">Email Notification Template</label>
                                                                                    <select class="form-select select2" data-whichselect="maker-dependency-email-list" id="step-${step.id}-maker_dependency_email_notification" name="sections[${section.id}][steps][${step.id}][maker_dependency_email_notification]" >
                                                                                        ${step.maker_dependency_email_notification ? `<option value="${step.maker_dependency_email_notification}" selected>${getStepDisplayName(step, 'makerDependencyEmailNotification') || 'Selected Template'}</option>` : ''}
                                                                                    </select>
                                                                                </div>
                                                                                <div class="col-md-6 mt-2">
                                                                                    <label class="form-label fw-semibold">Push Notification Template</label>
                                                                                    <select class="form-select select2" data-whichselect="maker-dependency-push-list" id="step-${step.id}-maker_dependency_push_notification" name="sections[${section.id}][steps][${step.id}][maker_dependency_push_notification]" >
                                                                                        ${step.maker_dependency_push_notification ? `<option value="${step.maker_dependency_push_notification}" selected>${getStepDisplayName(step, 'makerDependencyPushNotification') || 'Selected Template'}</option>` : ''}
                                                                                    </select>
                                                                                </div>
                                                                                <hr class="mt-4 mb-4">
                                                                                <h5>Maker Escalation</h5>
                                                                                <div class="col-md-6">
                                                                                    <label class="form-label fw-semibold">Escalation Users</label>
                                                                                    <select class="form-select select2" data-whichselect="escalation-maker-list" id="step-${step.id}-maker_escalation_user_ids" name="sections[${section.id}][steps][${step.id}][maker_escalation_user_ids][]" multiple>
                                                                                        ${(step.maker_escalation_user_ids && Array.isArray(step.maker_escalation_user_ids)) ? step.maker_escalation_user_ids.map((userId, index) => `<option value="${userId}" selected>${(step.maker_escalation_user_names && step.maker_escalation_user_names[index]) || 'Selected User'}</option>`).join('') : (step.maker_escalation_user_id ? `<option value="${step.maker_escalation_user_id}" selected>${getStepDisplayName(step, 'makerEscalationUser') || 'Selected User'}</option>` : '')}
                                                                                    </select>
                                                                                </div>
                                                                                <div class="col-md-6">
                                                                                    <label class="form-label fw-semibold">Escalation After</label>
                                                                                    <div class="input-group">
                                                                                        <span class="input-group-text">Days</span>
                                                                                        <input type="number" class="form-control" name="sections[${section.id}][steps][${step.id}][maker_escalation_after_day]" placeholder="Enter days" value="${step.maker_escalation_after_day || ''}">
                                                                                        <span class="input-group-text">Hours</span>
                                                                                        <input type="number" class="form-control" name="sections[${section.id}][steps][${step.id}][maker_escalation_after_hour]" placeholder="Enter hours" value="${step.maker_escalation_after_hour || ''}">
                                                                                        <span class="input-group-text">Minutes</span>
                                                                                        <input type="number" class="form-control" name="sections[${section.id}][steps][${step.id}][maker_escalation_after_minute]" placeholder="Enter minutes" value="${step.maker_escalation_after_minute || ''}">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-md-6 mt-2">
                                                                                    <label class="form-label fw-semibold">Email Notification Template</label>
                                                                                    <select class="form-select select2" data-whichselect="maker-email-list" id="step-${step.id}-maker_escalation_email_notification" name="sections[${section.id}][steps][${step.id}][maker_escalation_email_notification]" required>
                                                                                        ${step.maker_escalation_email_notification ? `<option value="${step.maker_escalation_email_notification}" selected>${getStepDisplayName(step, 'makerEscalationEmailNotification') || 'Selected Template'}</option>` : ''}
                                                                                    </select>
                                                                                </div>
                                                                                <div class="col-md-6 mt-2">
                                                                                    <label class="form-label fw-semibold">Push Notification Template</label>
                                                                                    <select class="form-select select2" data-whichselect="maker-push-list" id="step-${step.id}-maker_escalation_push_notification" name="sections[${section.id}][steps][${step.id}][maker_escalation_push_notification]" required>
                                                                                        ${step.maker_escalation_push_notification ? `<option value="${step.maker_escalation_push_notification}" selected>${getStepDisplayName(step, 'makerEscalationPushNotification') || 'Selected Template'}</option>` : ''}
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="accordion-item">
                                                                        <h2 class="accordion-header">
                                                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-checker-${step.id}" aria-expanded="false" aria-controls="collapse-checker-${step.id}">
                                                                                Checker
                                                                            </button>
                                                                        </h2>
                                                                        <div id="collapse-checker-${step.id}" class="accordion-collapse collapse" data-bs-parent="#accordionExample-${step.id}">
                                                                            <div class="accordion-body row">
                                                                                <div class="col-md-6">
                                                                                    <label class="form-label fw-semibold">Checker <span class="text-danger">*</span></label>
                                                                                    <select class="form-select select2" data-whichselect="checker-list" id="step-${step.id}-checker_id" name="sections[${section.id}][steps][${step.id}][checker_id]" required>
                                                                                        ${step.checker_id ? `<option value="${step.checker_id}" selected>${getStepDisplayName(step, 'checker') || 'Selected User'}</option>` : ''}
                                                                                    </select>
                                                                                </div>
                                                                                <div class="col-md-6">
                                                                                    <label class="form-label fw-semibold">Turnaround Time </label>
                                                                                    <div class="input-group">
                                                                                        <span class="input-group-text">Days</span>
                                                                                        <input type="number" class="form-control" name="sections[${section.id}][steps][${step.id}][checker_turn_around_time_day]" placeholder="Enter days" value="${step.checker_turn_around_time_day || ''}">
                                                                                        <span class="input-group-text">Hours</span>
                                                                                        <input type="number" class="form-control" name="sections[${section.id}][steps][${step.id}][checker_turn_around_time_hour]" placeholder="Enter hours" value="${step.checker_turn_around_time_hour || ''}">
                                                                                        <span class="input-group-text">Minutes</span>
                                                                                        <input type="number" class="form-control" name="sections[${section.id}][steps][${step.id}][checker_turn_around_time_minute]" placeholder="Enter minutes" value="${step.checker_turn_around_time_minute || ''}">
                                                                                    </div>
                                                                                </div>
                                                                                <hr class="mt-4 mb-4">
                                                                                <h5>Checker Escalation</h5>
                                                                                <div class="col-md-6">
                                                                                    <label class="form-label fw-semibold">Escalation Users</label>
                                                                                    <select class="form-select select2" data-whichselect="escalation-checker-list" id="step-${step.id}-checker_escalation_user_ids" name="sections[${section.id}][steps][${step.id}][checker_escalation_user_ids][]" multiple>
                                                                                        ${(step.checker_escalation_user_ids && Array.isArray(step.checker_escalation_user_ids)) ? step.checker_escalation_user_ids.map((userId, index) => `<option value="${userId}" selected>${(step.checker_escalation_user_names && step.checker_escalation_user_names[index]) || 'Selected User'}</option>`).join('') : (step.checker_escalation_user_id ? `<option value="${step.checker_escalation_user_id}" selected>${getStepDisplayName(step, 'checkerEscalationUser') || 'Selected User'}</option>` : '')}
                                                                                    </select>
                                                                                </div>
                                                                                <div class="col-md-6">
                                                                                    <label class="form-label fw-semibold">Escalation After</label>
                                                                                    <div class="input-group">
                                                                                        <span class="input-group-text">Days</span>
                                                                                        <input type="number" class="form-control" name="sections[${section.id}][steps][${step.id}][checker_escalation_after_day]" placeholder="Enter days" value="${step.checker_escalation_after_day || ''}">
                                                                                        <span class="input-group-text">Hours</span>
                                                                                        <input type="number" class="form-control" name="sections[${section.id}][steps][${step.id}][checker_escalation_after_hour]" placeholder="Enter hours" value="${step.checker_escalation_after_hour || ''}">
                                                                                        <span class="input-group-text">Minutes</span>
                                                                                        <input type="number" class="form-control" name="sections[${section.id}][steps][${step.id}][checker_escalation_after_minute]" placeholder="Enter minutes" value="${step.checker_escalation_after_minute || ''}">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-md-6 mt-2">
                                                                                    <label class="form-label fw-semibold">Email Notification Template</label>
                                                                                    <select class="form-select select2" data-whichselect="checker-email-list" id="step-${step.id}-checker_escalation_email_notification" name="sections[${section.id}][steps][${step.id}][checker_escalation_email_notification]" required>
                                                                                        ${step.checker_escalation_email_notification ? `<option value="${step.checker_escalation_email_notification}" selected>${getStepDisplayName(step, 'checkerEscalationEmailNotification') || 'Selected Template'}</option>` : ''}
                                                                                    </select>
                                                                                </div>
                                                                                <div class="col-md-6 mt-2">
                                                                                    <label class="form-label fw-semibold">Push Notification Template</label>
                                                                                    <select class="form-select select2" data-whichselect="checker-push-list" id="step-${step.id}-checker_escalation_push_notification" name="sections[${section.id}][steps][${step.id}][checker_escalation_push_notification]" required>
                                                                                        ${step.checker_escalation_push_notification ? `<option value="${step.checker_escalation_push_notification}" selected>${getStepDisplayName(step, 'checkerEscalationPushNotification') || 'Selected Template'}</option>` : ''}
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            `).join('');
            }

            function renderSectionDetails(section) {

                if (currentSectionId && sections[currentSectionId]) {
                    saveCurrentStepData(currentSectionId);
                }

                const sectionDetailsHtml = `
                                                <div class="row g-3">
                                                    <div class="col-md-12">
                                                        <label class="form-label fw-semibold">Department Name <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" name="sections[${section.id}][name]" 
                                                               value="${section.name ?? ''}" placeholder="e.g., Store Setup & Configuration" required>
                                                        <div class="form-hint">
                                                            <i class="bi bi-info-circle me-1"></i>
                                                            Use section-oriented names that clearly indicate the section's purpose
                                                        </div>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <label class="form-label fw-semibold">Department Code <span class="text-danger">*</span></label>
                                                        <div class="input-group">
                                                            <input type="text" class="form-control" name="sections[${section.id}][code]" 
                                                                   value="${section.code ?? ''}" placeholder="e.g., STORE_SETUP" required>
                                                            <button class="btn btn-outline-secondary" type="button" id="regenerateCode">
                                                                <i class="bi bi-arrow-clockwise"></i>
                                                            </button>
                                                        </div>
                                                        <div class="form-hint">
                                                            Use uppercase letters and underscores (e.g., STORE_SETUP, INVENTORY_CHECK)
                                                        </div>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <label class="form-label fw-semibold">Description</label>
                                                        <textarea class="form-control" name="sections[${section.id}][description]" 
                                                                  rows="3" placeholder="Describe the activities, objectives, and outcomes for this  department..." 
                                                                  maxlength="200">${section.description ?? ''}</textarea>
                                                        <div class="d-flex justify-content-between align-items-center mt-1">
                                                            <div class="form-hint">
                                                                <i class="bi bi-pencil me-1"></i>
                                                                Include key activities and expected outcomes
                                                            </div>
                                                            <div class="character-counter">
                                                                <span id="charCount">${section.description ? section.description.length : ''}</span>/200 characters
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-12">
                                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                                            <h6 class="mb-0">Tasks In This Department</h6>
                                                            <div>
                                                                <span class="badge bg-secondary me-2">${section.steps.length} tasks</span>
                                                                <button type="button" class="btn btn-primary btn-sm" onclick="addStepToSection('${section.id}')">
                                                                    <i class="bi bi-plus-circle me-1"></i>Add Task
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <div id="steps-container-${section.id}" class="steps-container">
                                                            ${renderStepsForSection(section)}
                                                        </div>
                                                    </div>
                                                </div>
                                            `;

                $('#sectionDetailsContainer').html(sectionDetailsHtml);

                $('#regenerateCode').click(function () {
                    const name = $(`input[name="sections[${section.id}][name]"]`).val();
                    const newCode = generateSectionCode(name);
                    $(`input[name="sections[${section.id}][code]"]`).val(newCode);
                    updateSectionDisplay(section.id);
                });

                $(`input[name="sections[${section.id}][name]"]`).on('input', function () {
                    const name = $(this).val();
                    const code = generateSectionCode(name);
                    $(`input[name="sections[${section.id}][code]"]`).val(code);
                    updateSectionDisplay(section.id);
                });

                $(`textarea[name="sections[${section.id}][description]"]`).on('input', function () {
                    const length = $(this).val().length;
                    $('#charCount').text(length);
                    updateSectionDisplay(section.id);
                });

                $('input, textarea').on('input change', function () {
                    updateSectionData(section.id);
                });

                initializeStepEventHandlers(section.id);
            }

            function addStepToSection(sectionId) {
                globalStepCounter++;
                const stepId = `${globalStepCounter}`;
                const stepData = {
                    id: stepId,
                    globalNumber: globalStepCounter,
                    step_name: '',
                    department_id: '',
                    checklist_id: '',
                    checklist_description: '',
                    trigger: 0,
                    dependency: 'NO_DEPENDENCY',
                    dependency_steps: [],
                    is_entry_point: false,
                    user_id: '',
                    maker_turn_around_time_day: '',
                    maker_turn_around_time_hour: '',
                    maker_turn_around_time_minute: '',
                    maker_escalation_user_id: '',
                    maker_escalation_user_ids: [],
                    maker_escalation_after_day: '',
                    maker_escalation_after_hour: '',
                    maker_escalation_after_minute: '',
                    maker_escalation_email_notification: '',
                    maker_escalation_push_notification: '',
                    checker_id: '',
                    checker_turn_around_time_day: '',
                    checker_turn_around_time_hour: '',
                    checker_turn_around_time_minute: '',
                    checker_escalation_user_id: '',
                    checker_escalation_user_ids: [],
                    checker_escalation_after_day: '',
                    checker_escalation_after_hour: '',
                    checker_escalation_after_minute: '',
                    checker_escalation_email_notification: '',
                    checker_escalation_push_notification: ''
                };

                sections[sectionId].steps.push(stepData);
                updateSectionDisplay(sectionId);

                if (currentSectionId === sectionId) {
                    renderSectionDetails(sections[sectionId]);
                }

                setTimeout(() => refreshAllDependencyDropdowns(), 150);
            }

            function removeStepFromSection(sectionId, stepId) {
                const section = sections[sectionId];
                const stepIndex = section.steps.findIndex(step => String(step.id) === String(stepId));

                if (stepIndex > -1) {
                    section.steps.splice(stepIndex, 1);
                    updateSectionDisplay(sectionId);

                    if (currentSectionId === sectionId) {
                        renderSectionDetails(section);
                    }

                    refreshAllStepNumbers();
                    setTimeout(() => refreshAllDependencyDropdowns(), 150);
                }
            }

            function refreshAllStepNumbers() {
                globalStepCounter = 0;
                Object.values(sections).forEach(section => {
                    section.steps.forEach(step => {
                        globalStepCounter++;
                        step.globalNumber = globalStepCounter;
                    });
                });

                if (currentSectionId) {
                    renderSectionDetails(sections[currentSectionId]);
                }
            }

            function initializeStepEventHandlers(sectionId) {
                $(document).off('click', '.remove-step');
                $(document).on('click', '.remove-step', function () {
                    const stepId = $(this).data('step-id');
                    Swal.fire({
                        title: 'Are you sure you want to delete this step?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            removeStepFromSection(sectionId, stepId);
                        }
                    });
                });

                $(document).off('keyup', '.step-name');
                $(document).on('keyup', '.step-name', function () {
                    const nearestHeading = $(this).closest('.step-card').find('.nearest-step-heading');
                    if (nearestHeading && $(this).val().trim()) {
                        nearestHeading.text(` - ${$(this).val().trim()}`);
                    }

                    saveCurrentStepData(sectionId);

                    clearTimeout(window.stepNameDebounce);
                    window.stepNameDebounce = setTimeout(() => refreshAllDependencyDropdowns(), 300);
                });

                $(document).off('change', '.dependency-select');
                $(document).on('change', '.dependency-select', function () {
                    const stepCard = $(this).closest('.step-card');
                    const isEntryPoint = stepCard.find('input[name*="[is_entry_point]"]').is(':checked');

                    if (isEntryPoint) {
                        return;
                    }

                    const container = stepCard.find('.dependency-steps-container');
                    if ($(this).val() === 'SELECTED_COMPLETED') {
                        container.removeClass('d-none');
                    } else {
                        container.addClass('d-none');
                        container.find('.dep-steps').val(null).trigger('change');
                    }

                    saveCurrentStepData(sectionId);
                });

                $(document).off('change', 'input[name*="[is_entry_point]"]');
                $(document).on('change', 'input[name*="[is_entry_point]"]', function () {
                    const stepCard = $(this).closest('.step-card');
                    const isEntryPoint = $(this).is(':checked');
                    const dependencySelect = stepCard.find('.dependency-select');
                    const dependencyContainer = stepCard.find('.dependency-steps-container');
                    const depSteps = stepCard.find('.dep-steps');

                    if (isEntryPoint) {
                        dependencySelect.val('NO_DEPENDENCY').trigger('change.select2');
                        dependencySelect.prop('disabled', true);
                        depSteps.val(null).trigger('change');
                        dependencyContainer.addClass('d-none');
                    } else {
                        dependencySelect.prop('disabled', false);
                    }

                    saveCurrentStepData(sectionId);
                });

                $(document).off('input change', '.step-card input, .step-card select, .step-card textarea');
                $(document).on('input change', '.step-card input, .step-card select, .step-card textarea', function () {
                    saveCurrentStepData(sectionId);
                });

                setTimeout(() => {
                    initializeSelect2($('#sectionDetailsContainer'));
                }, 100);
            }

            function initializeSelect2(scope) {
                if (scope) {
                    if (scope.find('[data-whichselect="departments-list"]').length > 0) {
                        scope.find('[data-whichselect="departments-list"]').select2({
                            placeholder: 'Select Department',
                            width: '100%',
                            theme: 'classic',
                            ajax: {
                                url: "{{ route('departments-list') }}",
                                type: "POST",
                                dataType: 'json',
                                delay: 250,
                                data: function (params) {
                                    return {
                                        searchQuery: params.term,
                                        page: params.page || 1,
                                        _token: "{{ csrf_token() }}"
                                    };
                                },
                                processResults: function (data, params) {
                                    params.page = params.page || 1;
                                    return {
                                        results: $.map(data.items, function (item) {
                                            return {
                                                id: item.id,
                                                text: item.text
                                            };
                                        }),
                                        pagination: {
                                            more: data.pagination.more
                                        }
                                    };
                                },
                                cache: true
                            }
                        });
                    }

                    if (scope.find('[data-whichselect="checklists-list"]').length > 0) {
                        scope.find('[data-whichselect="checklists-list"]').select2({
                            placeholder: 'Select Checklist',
                            allowClear: true,
                            width: '100%',
                            theme: 'classic',
                            ajax: {
                                url: "{{ route('checklists-list') }}",
                                type: "POST",
                                dataType: 'json',
                                delay: 250,
                                data: function (params) {
                                    return {
                                        searchQuery: params.term,
                                        page: params.page || 1,
                                        _token: "{{ csrf_token() }}",
                                        type: 2
                                    };
                                },
                                processResults: function (data, params) {
                                    params.page = params.page || 1;
                                    return {
                                        results: $.map(data.items, function (item) {
                                            return {
                                                id: item.id,
                                                text: item.text
                                            };
                                        }),
                                        pagination: {
                                            more: data.pagination.more
                                        }
                                    };
                                },
                                cache: true
                            }
                        });
                    }

                    if (scope.find('[data-whichselect="maker-list"]').length > 0) {
                        scope.find('[data-whichselect="maker-list"]').select2({
                            placeholder: "Select a User",
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
                                        ignoreDesignation: 1
                                    }
                                },
                                processResults: function (data, params) {
                                    params.page = params.page || 1;
                                    return {
                                        results: $.map(data.items, function (item) {
                                            return {
                                                id: item.id,
                                                text: item.text
                                            };
                                        }),
                                        pagination: {
                                            more: data.pagination.more
                                        }
                                    };
                                },
                                cache: true
                            }
                        });
                    }

                    if (scope.find('[data-whichselect="checker-list"]').length > 0) {
                        scope.find('[data-whichselect="checker-list"]').select2({
                            placeholder: "Select a User",
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
                                        ignoreDesignation: 1
                                    }
                                },
                                processResults: function (data, params) {
                                    params.page = params.page || 1;
                                    return {
                                        results: $.map(data.items, function (item) {
                                            return {
                                                id: item.id,
                                                text: item.text
                                            };
                                        }),
                                        pagination: {
                                            more: data.pagination.more
                                        }
                                    };
                                },
                                cache: true
                            }
                        });
                    }

                    if (scope.find('[data-whichselect="escalation-maker-list"]').length > 0) {
                        scope.find('[data-whichselect="escalation-maker-list"]').select2({
                            placeholder: "Select Users",
                            allowClear: true,
                            width: "100%",
                            theme: 'classic',
                            multiple: true,
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
                                        ignoreDesignation: 1
                                    }
                                },
                                processResults: function (data, params) {
                                    params.page = params.page || 1;
                                    return {
                                        results: $.map(data.items, function (item) {
                                            return {
                                                id: item.id,
                                                text: item.text
                                            };
                                        }),
                                        pagination: {
                                            more: data.pagination.more
                                        }
                                    };
                                },
                                cache: true
                            }
                        });
                    }

                    if (scope.find('[data-whichselect="escalation-checker-list"]').length > 0) {
                        scope.find('[data-whichselect="escalation-checker-list"]').select2({
                            placeholder: "Select Users",
                            allowClear: true,
                            width: "100%",
                            theme: 'classic',
                            multiple: true,
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
                                        ignoreDesignation: 1
                                    }
                                },
                                processResults: function (data, params) {
                                    params.page = params.page || 1;
                                    return {
                                        results: $.map(data.items, function (item) {
                                            return {
                                                id: item.id,
                                                text: item.text
                                            };
                                        }),
                                        pagination: {
                                            more: data.pagination.more
                                        }
                                    };
                                },
                                cache: true
                            }
                        });
                    }

                    if (scope.find('[data-whichselect="maker-email-list"]').length > 0) {
                        scope.find('[data-whichselect="maker-email-list"]').select2({
                            placeholder: "Select Template",
                            allowClear: true,
                            width: "100%",
                            theme: 'classic',
                            ajax: {
                                url: "{{ route('notification-template-list') }}",
                                type: "POST",
                                dataType: 'json',
                                delay: 250,
                                data: function (params) {
                                    return {
                                        searchQuery: params.term,
                                        page: params.page || 1,
                                        _token: "{{ csrf_token() }}",
                                        type: 0
                                    }
                                },
                                processResults: function (data, params) {
                                    params.page = params.page || 1;
                                    return {
                                        results: $.map(data.items, function (item) {
                                            return {
                                                id: item.id,
                                                text: item.text
                                            };
                                        }),
                                        pagination: {
                                            more: data.pagination.more
                                        }
                                    };
                                },
                                cache: true
                            }
                        });
                    }

                    if (scope.find('[data-whichselect="maker-push-list"]').length > 0) {
                        scope.find('[data-whichselect="maker-push-list"]').select2({
                            placeholder: "Select Template",
                            allowClear: true,
                            width: "100%",
                            theme: 'classic',
                            ajax: {
                                url: "{{ route('notification-template-list') }}",
                                type: "POST",
                                dataType: 'json',
                                delay: 250,
                                data: function (params) {
                                    return {
                                        searchQuery: params.term,
                                        page: params.page || 1,
                                        _token: "{{ csrf_token() }}",
                                        type: 1
                                    }
                                },
                                processResults: function (data, params) {
                                    params.page = params.page || 1;
                                    return {
                                        results: $.map(data.items, function (item) {
                                            return {
                                                id: item.id,
                                                text: item.text
                                            };
                                        }),
                                        pagination: {
                                            more: data.pagination.more
                                        }
                                    };
                                },
                                cache: true
                            }
                        });
                    }

                    if (scope.find('[data-whichselect="checker-email-list"]').length > 0) {
                        scope.find('[data-whichselect="checker-email-list"]').select2({
                            placeholder: "Select Template",
                            allowClear: true,
                            width: "100%",
                            theme: 'classic',
                            ajax: {
                                url: "{{ route('notification-template-list') }}",
                                type: "POST",
                                dataType: 'json',
                                delay: 250,
                                data: function (params) {
                                    return {
                                        searchQuery: params.term,
                                        page: params.page || 1,
                                        _token: "{{ csrf_token() }}",
                                        type: 0
                                    }
                                },
                                processResults: function (data, params) {
                                    params.page = params.page || 1;
                                    return {
                                        results: $.map(data.items, function (item) {
                                            return {
                                                id: item.id,
                                                text: item.text
                                            };
                                        }),
                                        pagination: {
                                            more: data.pagination.more
                                        }
                                    };
                                },
                                cache: true
                            }
                        });
                    }

                    if (scope.find('[data-whichselect="checker-push-list"]').length > 0) {
                        scope.find('[data-whichselect="checker-push-list"]').select2({
                            placeholder: "Select Template",
                            allowClear: true,
                            width: "100%",
                            theme: 'classic',
                            ajax: {
                                url: "{{ route('notification-template-list') }}",
                                type: "POST",
                                dataType: 'json',
                                delay: 250,
                                data: function (params) {
                                    return {
                                        searchQuery: params.term,
                                        page: params.page || 1,
                                        _token: "{{ csrf_token() }}",
                                        type: 1
                                    }
                                },
                                processResults: function (data, params) {
                                    params.page = params.page || 1;
                                    return {
                                        results: $.map(data.items, function (item) {
                                            return {
                                                id: item.id,
                                                text: item.text
                                            };
                                        }),
                                        pagination: {
                                            more: data.pagination.more
                                        }
                                    };
                                },
                                cache: true
                            }
                        });
                    }

                    if (scope.find('[data-whichselect="maker-completion-email-list"]').length > 0) {
                        scope.find('[data-whichselect="maker-completion-email-list"]').select2({
                            placeholder: "Select Template",
                            allowClear: true,
                            width: "100%",
                            theme: 'classic',
                            ajax: {
                                url: "{{ route('notification-template-list') }}",
                                type: "POST",
                                dataType: 'json',
                                delay: 250,
                                data: function (params) {
                                    return {
                                        searchQuery: params.term,
                                        page: params.page || 1,
                                        _token: "{{ csrf_token() }}",
                                        type: 0
                                    }
                                },
                                processResults: function (data, params) {
                                    params.page = params.page || 1;
                                    return {
                                        results: $.map(data.items, function (item) {
                                            return {
                                                id: item.id,
                                                text: item.text
                                            };
                                        }),
                                        pagination: {
                                            more: data.pagination.more
                                        }
                                    };
                                },
                                cache: true
                            }
                        });
                    }

                    if (scope.find('[data-whichselect="maker-completion-push-list"]').length > 0) {
                        scope.find('[data-whichselect="maker-completion-push-list"]').select2({
                            placeholder: "Select Template",
                            allowClear: true,
                            width: "100%",
                            theme: 'classic',
                            ajax: {
                                url: "{{ route('notification-template-list') }}",
                                type: "POST",
                                dataType: 'json',
                                delay: 250,
                                data: function (params) {
                                    return {
                                        searchQuery: params.term,
                                        page: params.page || 1,
                                        _token: "{{ csrf_token() }}",
                                        type: 1
                                    }
                                },
                                processResults: function (data, params) {
                                    params.page = params.page || 1;
                                    return {
                                        results: $.map(data.items, function (item) {
                                            return {
                                                id: item.id,
                                                text: item.text
                                            };
                                        }),
                                        pagination: {
                                            more: data.pagination.more
                                        }
                                    };
                                },
                                cache: true
                            }
                        });
                    }

                    if (scope.find('[data-whichselect="maker-dependency-email-list"]').length > 0) {
                        scope.find('[data-whichselect="maker-dependency-email-list"]').select2({
                            placeholder: "Select Template",
                            allowClear: true,
                            width: "100%",
                            theme: 'classic',
                            ajax: {
                                url: "{{ route('notification-template-list') }}",
                                type: "POST",
                                dataType: 'json',
                                delay: 250,
                                data: function (params) {
                                    return {
                                        searchQuery: params.term,
                                        page: params.page || 1,
                                        _token: "{{ csrf_token() }}",
                                        type: 0
                                    }
                                },
                                processResults: function (data, params) {
                                    params.page = params.page || 1;
                                    return {
                                        results: $.map(data.items, function (item) {
                                            return {
                                                id: item.id,
                                                text: item.text
                                            };
                                        }),
                                        pagination: {
                                            more: data.pagination.more
                                        }
                                    };
                                },
                                cache: true
                            }
                        });
                    }

                    if (scope.find('[data-whichselect="maker-dependency-push-list"]').length > 0) {
                        scope.find('[data-whichselect="maker-dependency-push-list"]').select2({
                            placeholder: "Select Template",
                            allowClear: true,
                            width: "100%",
                            theme: 'classic',
                            ajax: {
                                url: "{{ route('notification-template-list') }}",
                                type: "POST",
                                dataType: 'json',
                                delay: 250,
                                data: function (params) {
                                    return {
                                        searchQuery: params.term,
                                        page: params.page || 1,
                                        _token: "{{ csrf_token() }}",
                                        type: 1
                                    }
                                },
                                processResults: function (data, params) {
                                    params.page = params.page || 1;
                                    return {
                                        results: $.map(data.items, function (item) {
                                            return {
                                                id: item.id,
                                                text: item.text
                                            };
                                        }),
                                        pagination: {
                                            more: data.pagination.more
                                        }
                                    };
                                },
                                cache: true
                            }
                        });
                    }

                    scope.find('select').each(function () {
                        if ($(this).is('[data-whichselect="departments-list"], [data-whichselect="checklists-list"], [data-whichselect="maker-list"], [data-whichselect="checker-list"], [data-whichselect="escalation-maker-list"], [data-whichselect="escalation-checker-list"], [data-whichselect="maker-email-list"], [data-whichselect="maker-push-list"], [data-whichselect="checker-email-list"], [data-whichselect="checker-push-list"], [data-whichselect="maker-completion-email-list"], [data-whichselect="maker-completion-push-list"], [data-whichselect="maker-dependency-email-list"], [data-whichselect="maker-dependency-push-list"]')) {
                            return;
                        }
                        $(this).select2({
                            width: '100%',
                            theme: 'classic',
                            placeholder: 'Select an option...'
                        });
                    });

                } else {
                    $(document).find('select.select2').select2({
                        width: '100%',
                        theme: 'classic',
                        placeholder: 'Select an option...',
                        allowClear: true
                    });
                }
            }

            function saveCurrentStepData(sectionId) {
                if (!sections[sectionId]) return;

                const section = sections[sectionId];
                const stepsContainer = $(`#steps-container-${sectionId}`);

                stepsContainer.find('.step-card').each(function () {
                    const stepCard = $(this);
                    const stepId = stepCard.data('step-id');
                    const step = section.steps.find(s => String(s.id) === String(stepId));

                    if (step) {
                        // Preserve record_id if it exists
                        const recordIdInput = stepCard.find('input[name*="[record_id]"]');
                        if (recordIdInput.length > 0 && recordIdInput.val()) {
                            step.record_id = parseInt(recordIdInput.val()) || null;
                        }

                        step.step_name = stepCard.find('input[name*="[step_name]"]').val() || '';
                        step.department_id = stepCard.find('select[name*="[department_id]"]').val() || '';
                        step.checklist_id = stepCard.find('select[name*="[checklist_id]"]').val() || '';
                        step.checklist_description = stepCard.find('textarea[name*="[checklist_description]"]').val() || '';
                        step.trigger = parseInt(stepCard.find('select[name*="[trigger]"]').val()) || 0;
                        step.dependency = stepCard.find('select[name*="[dependency]"]').val() || 'NO_DEPENDENCY';
                        step.is_entry_point = stepCard.find('input[name*="[is_entry_point]"]').is(':checked');
                        step.user_id = stepCard.find('select[name*="[user_id]"]').val() || '';

                        step.maker_turn_around_time_day = stepCard.find('input[name*="[maker_turn_around_time_day]"]').val() || '';
                        step.maker_turn_around_time_hour = stepCard.find('input[name*="[maker_turn_around_time_hour]"]').val() || '';
                        step.maker_turn_around_time_minute = stepCard.find('input[name*="[maker_turn_around_time_minute]"]').val() || '';
                        step.maker_escalation_user_ids = stepCard.find('select[name*="[maker_escalation_user_ids]"]').val() || [];
                        step.maker_escalation_after_day = stepCard.find('input[name*="[maker_escalation_after_day]"]').val() || '';
                        step.maker_escalation_after_hour = stepCard.find('input[name*="[maker_escalation_after_hour]"]').val() || '';
                        step.maker_escalation_after_minute = stepCard.find('input[name*="[maker_escalation_after_minute]"]').val() || '';
                        step.maker_escalation_email_notification = stepCard.find('select[name*="[maker_escalation_email_notification]"]').val() || '';
                        step.maker_escalation_push_notification = stepCard.find('select[name*="[maker_escalation_push_notification]"]').val() || '';
                        step.maker_completion_email_notification = stepCard.find('select[name*="[maker_completion_email_notification]"]').val() || '';
                        step.maker_completion_push_notification = stepCard.find('select[name*="[maker_completion_push_notification]"]').val() || '';
                        step.maker_dependency_email_notification = stepCard.find('select[name*="[maker_dependency_email_notification]"]').val() || '';
                        step.maker_dependency_push_notification = stepCard.find('select[name*="[maker_dependency_push_notification]"]').val() || '';

                        step.checker_id = stepCard.find('select[name*="[checker_id]"]').val() || '';
                        step.checker_turn_around_time_day = stepCard.find('input[name*="[checker_turn_around_time_day]"]').val() || '';
                        step.checker_turn_around_time_hour = stepCard.find('input[name*="[checker_turn_around_time_hour]"]').val() || '';
                        step.checker_turn_around_time_minute = stepCard.find('input[name*="[checker_turn_around_time_minute]"]').val() || '';
                        step.checker_escalation_user_ids = stepCard.find('select[name*="[checker_escalation_user_ids]"]').val() || [];
                        step.checker_escalation_after_day = stepCard.find('input[name*="[checker_escalation_after_day]"]').val() || '';
                        step.checker_escalation_after_hour = stepCard.find('input[name*="[checker_escalation_after_hour]"]').val() || '';
                        step.checker_escalation_after_minute = stepCard.find('input[name*="[checker_escalation_after_minute]"]').val() || '';
                        step.checker_escalation_email_notification = stepCard.find('select[name*="[checker_escalation_email_notification]"]').val() || '';
                        step.checker_escalation_push_notification = stepCard.find('select[name*="[checker_escalation_push_notification]"]').val() || '';

                        // Properly get dependency_steps from Select2 or regular select
                        const dependencySteps = stepCard.find('select[name*="[dependency_steps]"]').val() || [];
                        step.dependency_steps = Array.isArray(dependencySteps) ? dependencySteps : [];

                        step.department_name = stepCard.find('select[name*="[department_id]"] option:selected').text() || '';
                        step.checklist_name = stepCard.find('select[name*="[checklist_id]"] option:selected').text() || '';
                        step.user_name = stepCard.find('select[name*="[user_id]"] option:selected').text() || '';
                        
                        step.maker_escalation_user_names = [];
                        stepCard.find('select[name*="[maker_escalation_user_ids]"] option:selected').each(function() {
                            step.maker_escalation_user_names.push($(this).text());
                        });

                        step.maker_escalation_email_name = stepCard.find('select[name*="[maker_escalation_email_notification]"] option:selected').text() || '';
                        step.maker_escalation_push_name = stepCard.find('select[name*="[maker_escalation_push_notification]"] option:selected').text() || '';
                        step.maker_completion_email_name = stepCard.find('select[name*="[maker_completion_email_notification]"] option:selected').text() || '';
                        step.maker_completion_push_name = stepCard.find('select[name*="[maker_completion_push_notification]"] option:selected').text() || '';
                        step.maker_dependency_email_name = stepCard.find('select[name*="[maker_dependency_email_notification]"] option:selected').text() || '';
                        step.maker_dependency_push_name = stepCard.find('select[name*="[maker_dependency_push_notification]"] option:selected').text() || '';
                        step.checker_name = stepCard.find('select[name*="[checker_id]"] option:selected').text() || '';
                        
                        step.checker_escalation_user_names = [];
                        stepCard.find('select[name*="[checker_escalation_user_ids]"] option:selected').each(function() {
                            step.checker_escalation_user_names.push($(this).text());
                        });

                        step.checker_escalation_email_name = stepCard.find('select[name*="[checker_escalation_email_notification]"] option:selected').text() || '';
                        step.checker_escalation_push_name = stepCard.find('select[name*="[checker_escalation_push_notification]"] option:selected').text() || '';
                    }
                });
            }

            function updateSectionData(sectionId) {
                const section = sections[sectionId];
                section.name = $(`input[name="sections[${sectionId}][name]"]`).val();
                section.code = $(`input[name="sections[${sectionId}][code]"]`).val();
                section.description = $(`textarea[name="sections[${sectionId}][description]"]`).val();
                updateSectionDisplay(sectionId);
            }

            function updateSectionDisplay(sectionId) {
                const section = sections[sectionId];
                const sectionItem = $(`.section-item[data-section-id="${sectionId}"]`);

                sectionItem.find('.section-name').text(section.name || 'New Department');
                sectionItem.find('.section-code').text(section.code || 'NEW_DEPARTMENT');
                sectionItem.find('.section-description').text(section.description || 'No description provided');
                sectionItem.find('.section-steps-count').text(`${section.steps.length} tasks`);
            }

            function cloneSection() {
                if (!currentSectionId) return;

                const originalSection = sections[currentSectionId];
                sectionIndex++;
                const newSectionId = `section_${sectionIndex}`;
                const sectionNumber = sectionIndex;

                const clonedSteps = originalSection.steps.map(step => {
                    globalStepCounter++;
                    return {
                        ...step,
                        id: `${globalStepCounter}`,
                        globalNumber: globalStepCounter
                    };
                });

                const clonedSection = {
                    id: newSectionId,
                    name: originalSection.name + ' (Copy)',
                    code: originalSection.code + '_COPY',
                    description: originalSection.description,
                    steps: clonedSteps
                };

                sections[newSectionId] = clonedSection;

                const sectionItem = $(`
                                                <div class="section-item" data-section-id="${newSectionId}">
                                                    <div class="d-flex align-items-center p-3">
                                                        <div class="section-drag-handle me-2">
                                                            <i class="bi bi-grip-vertical"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <div class="fw-semibold section-name">${clonedSection.name}</div>
                                                            <div class="section-code text-muted">${clonedSection.code}</div>
                                                            <div class="text-muted small section-description">${clonedSection.description || 'No description provided'}</div>
                                                            <div class="d-flex justify-content-between align-items-center mt-1">
                                                                <span class="section-steps-count">${clonedSection.steps.length} tasks</span>
                                                                <span class="section-number">Section ${sectionNumber}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            `);

                $('#sectionsContainer').append(sectionItem);
                sectionItem.click(function () {
                    selectSection(newSectionId);
                });

                selectSection(newSectionId);
                initializeSortable();
            }

            function deleteSection() {
                if (!currentSectionId) return;

                Swal.fire({
                    title: 'Are you sure you want to delete this department?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $(`.section-item[data-section-id="${currentSectionId}"]`).remove();
                        delete sections[currentSectionId];

                        const remainingSections = Object.keys(sections);
                        if (remainingSections.length > 0) {
                            selectSection(remainingSections[0]);
                        } else {
                            currentSectionId = null;
                            $('#cloneSection, #deleteSection').prop('disabled', true);
                            $('#sectionDetailsContainer').html(`
                                                            <div class="text-center py-5">
                                                                <i class="bi bi-list-ol text-muted" style="font-size: 3rem;"></i>
                                                                <p class="text-muted mt-3">Select a department to configure its details</p>
                                                            </div>
                                                        `);
                        }

                        updateSectionNumbers();
                    }
                });
            }

            function updateSectionNumbers() {
                $('.section-item').each(function (index) {
                    $(this).find('.section-number').text(`Department ${index + 1}`);
                });
            }

            function initializeSortable() {
                if (typeof Sortable !== 'undefined') {
                    new Sortable(document.getElementById('sectionsContainer'), {
                        handle: '.section-drag-handle',
                        animation: 150,
                        onEnd: function (evt) {
                            updateSectionNumbers();
                        }
                    });
                }
            }

            function validateForm() {
                const errors = {};
                const sectionIds = Object.keys(sections);

                const title = $('[name="title"]').val();
                if (!title || !title.trim()) {
                    errors['title'] = ['The title field is required.'];
                }

                if (sectionIds.length === 0) {
                    errors['sections'] = ['At least one department is required.'];
                }

                for (const sectionId of sectionIds) {
                    const section = sections[sectionId];
                    const sectionIndex = sectionIds.indexOf(sectionId) + 1;

                    if (!section.name || !section.name.trim()) {
                        errors[`sections.${sectionIndex}.name`] = ['The department name field is required.'];
                    }

                    if (!section.code || !section.code.trim()) {
                        errors[`sections.${sectionIndex}.code`] = ['The department code field is required.'];
                    }

                    if (!section.steps || section.steps.length === 0) {
                        errors[`sections.${sectionIndex}.steps`] = [`Department "${section.name || 'Department ' + sectionIndex}" must have at least one task.`];
                    }

                    if (section.steps) {
                        section.steps.forEach((step, stepIdx) => {
                            const stepNumber = stepIdx + 1;
                            const stepPrefix = `sections.${sectionIndex}.steps.${stepNumber}`;

                            if (!step.step_name || !step.step_name.trim()) {
                                errors[`${stepPrefix}.step_name`] = ['The task name field is required.'];
                            }

                            if (!step.department_id) {
                                errors[`${stepPrefix}.department_id`] = ['The department field is required.'];
                            }

                            if (!step.checklist_id) {
                                errors[`${stepPrefix}.checklist_id`] = ['The checklist field is required.'];
                            }

                            if (!step.user_id) {
                                errors[`${stepPrefix}.user_id`] = ['The maker field is required.'];
                            }

                            if (!step.checker_id) {
                                errors[`${stepPrefix}.checker_id`] = ['The checker field is required.'];
                            }

                            if (step.is_entry_point && step.dependency_steps && step.dependency_steps.length > 0) {
                                errors[`${stepPrefix}.dependency_steps`] = [`Entry point "${step.step_name || 'Task ' + step.globalNumber}" cannot have parent dependencies.`];
                            }

                            if (step.dependency === 'SELECTED_COMPLETED' && (!step.dependency_steps || step.dependency_steps.length === 0) && !step.is_entry_point) {
                                errors[`${stepPrefix}.dependency_steps`] = ['Please select at least one dependent task or choose "No Dependency".'];
                            }
                        });
                    }
                }

                if (Object.keys(errors).length > 0) {
                    displayValidationErrors(errors);
                    return false;
                }

                $('#validationErrorsContainer').addClass('d-none').empty();
                return true;
            }

            window.addStepToSection = addStepToSection;

            $('.section-item').click(function () {
                const sectionId = $(this).data('section-id');
                selectSection(sectionId);
            });

            if (Object.keys(sections).length > 0) {
                selectSection(Object.keys(sections)[0]);
            }

            $('#addSection').click(addSection);
            $('#cloneSection').click(cloneSection);
            $('#deleteSection').click(deleteSection);

            $('#templateForm').on('submit', function (e) {
                e.preventDefault();

                // Save data from all sections, not just the current one
                Object.keys(sections).forEach(function (sectionId) {
                    if (sections[sectionId]) {
                        saveCurrentStepData(sectionId);
                    }
                });

                // Ensure record_id is included in all steps before submission
                Object.keys(sections).forEach(function (sectionId) {
                    const section = sections[sectionId];
                    if (section && section.steps) {
                        section.steps.forEach(function (step) {
                            // If step has a numeric id, use it as record_id
                            if (step.id && !isNaN(step.id) && parseInt(step.id) > 0 && !step.record_id) {
                                step.record_id = parseInt(step.id);
                            }
                        });
                    }
                });

                if (validateForm()) {

                    $.ajax({
                        url: "{{ route('workflow-templates.duplicate') }}",
                        type: 'POST',
                        data: JSON.stringify({
                            data: sections,
                            title: $('[name="title"]').val(),
                            description: $('[name="description"]').val(),
                            send_to_all_notification: $('#send_to_all_notification').is(':checked'),
                            status: $('#status option:selected').val()
                        }),
                        contentType: 'application/json',
                        processData: false,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        beforeSend: function () {
                            $('body').find('.LoaderSec').removeClass('d-none');
                        },
                        success: function (response) {
                            Swal.fire('Success', 'Project template duplicated succesfully', 'success');
                            location.href = "{{ route('workflow-templates.index') }}";
                        },
                        error: function (response) {
                            $('#validationErrorsContainer').addClass('d-none').empty();

                            if (response.status === 422 && response.responseJSON && response.responseJSON.errors) {
                                displayValidationErrors(response.responseJSON.errors);
                            } else if (response.responseJSON && response.responseJSON.message) {
                                Swal.fire('Error', response.responseJSON.message, 'error');
                            } else {
                                Swal.fire('Error', 'An unexpected error occurred. Please try again.', 'error');
                            }
                        },
                        complete: function () {
                            $('body').find('.LoaderSec').addClass('d-none');
                        }
                    });
                }
            });

            function displayValidationErrors(errors) {
                const generalErrors = [];
                const sectionErrors = [];
                const stepErrors = [];

                Object.keys(errors).forEach(function (key) {
                    const messages = errors[key];
                    messages.forEach(function (message) {
                        let friendlyMessage = makeFriendlyErrorMessage(key, message);

                        if (key.includes('sections.') && key.includes('.steps.')) {
                            stepErrors.push(friendlyMessage);
                        } else if (key.includes('sections.')) {
                            sectionErrors.push(friendlyMessage);
                        } else {
                            generalErrors.push(friendlyMessage);
                        }
                    });
                });

                let errorHtml = `
                            <div class="alert alert-danger border-0 shadow-sm mb-4" role="alert">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="bi bi-exclamation-triangle-fill fs-4 me-2"></i>
                                    <div>
                                        <h5 class="alert-heading mb-0">Please fix the following errors</h5>
                                        <small class="text-muted">Review the issues below and make the necessary corrections</small>
                                    </div>
                                    <button type="button" class="btn-close ms-auto" onclick="$('#validationErrorsContainer').addClass('d-none')"></button>
                                </div>
                        `;

                if (generalErrors.length > 0) {
                    errorHtml += `
                                <div class="error-group mb-3">
                                    <h6 class="d-flex align-items-center mb-2">
                                        <i class="bi bi-info-circle me-2"></i>General Information
                                    </h6>
                                    <ul class="mb-0 ps-4">
                                        ${generalErrors.map(e => `<li class="mb-1">${e}</li>`).join('')}
                                    </ul>
                                </div>
                            `;
                }

                if (sectionErrors.length > 0) {
                    errorHtml += `
                                <div class="error-group mb-3">
                                    <h6 class="d-flex align-items-center mb-2">
                                        <i class="bi bi-folder me-2"></i>Department Configuration
                                    </h6>
                                    <ul class="mb-0 ps-4">
                                        ${sectionErrors.map(e => `<li class="mb-1">${e}</li>`).join('')}
                                    </ul>
                                </div>
                            `;
                }

                if (stepErrors.length > 0) {
                    errorHtml += `
                                <div class="error-group mb-3">
                                    <h6 class="d-flex align-items-center mb-2">
                                        <i class="bi bi-list-check me-2"></i>Step Configuration
                                    </h6>
                                    <ul class="mb-0 ps-4">
                                        ${stepErrors.map(e => `<li class="mb-1">${e}</li>`).join('')}
                                    </ul>
                                </div>
                            `;
                }

                errorHtml += `
                                <div class="mt-3 pt-3 border-top border-danger border-opacity-25">
                                    <small class="d-flex align-items-center">
                                        <i class="bi bi-lightbulb me-2"></i>
                                        <span><strong>Tip:</strong> Make sure all required fields marked with <span class="text-danger">*</span> are filled in correctly.</span>
                                    </small>
                                </div>
                            </div>
                        `;

                $('#validationErrorsContainer').html(errorHtml).removeClass('d-none');

                $('html, body').animate({
                    scrollTop: $('#validationErrorsContainer').offset().top - 20
                }, 500);
            }

            function makeFriendlyErrorMessage(key, message) {
                const friendlyNames = {
                    'title': 'Title',
                    'description': 'Description',
                    'status': 'Status',
                    'name': 'Department Name',
                    'code': 'Department Code',
                    'step_name': 'Task Name',
                    'department_id': 'Department',
                    'checklist_id': 'Checklist',
                    'checklist_description': 'Checklist Description',
                    'user_id': 'Maker',
                    'trigger': 'Trigger',
                    'dependency': 'Dependency',
                    'dependency_steps': 'Dependent Tasks',
                    'maker_escalation_user_id': 'Maker Escalation User',
                    'maker_turn_around_time_day': 'Maker Turnaround Days',
                    'maker_turn_around_time_hour': 'Maker Turnaround Hours',
                    'maker_turn_around_time_minute': 'Maker Turnaround Minutes',
                    'maker_escalation_after_day': 'Maker Escalation Days',
                    'maker_escalation_after_hour': 'Maker Escalation Hours',
                    'maker_escalation_after_minute': 'Maker Escalation Minutes',
                    'maker_escalation_email_notification': 'Maker Email Notification Template',
                    'maker_escalation_push_notification': 'Maker Push Notification Template',
                    'checker_id': 'Checker',
                    'checker_turn_around_time_day': 'Checker Turnaround Days',
                    'checker_turn_around_time_hour': 'Checker Turnaround Hours',
                    'checker_turn_around_time_minute': 'Checker Turnaround Minutes',
                    'checker_escalation_user_id': 'Checker Escalation User',
                    'checker_escalation_after_day': 'Checker Escalation Days',
                    'checker_escalation_after_hour': 'Checker Escalation Hours',
                    'checker_escalation_after_minute': 'Checker Escalation Minutes',
                    'checker_escalation_email_notification': 'Checker Email Notification Template',
                    'checker_escalation_push_notification': 'Checker Push Notification Template'
                };

                let contextInfo = '';
                const sectionMatch = key.match(/sections\.([^.]+)/);
                const stepMatch = key.match(/steps\.([^.]+)/);

                if (stepMatch) {
                    contextInfo = ` (Task ${stepMatch[1]})`;
                } else if (sectionMatch) {
                    contextInfo = ` (Department)`;
                }

                let friendlyMessage = message;

                Object.keys(friendlyNames).forEach(function (field) {
                    const patterns = [
                        new RegExp(`sections\\.[^.]+\\.steps\\.[^.]+\\.${field}`, 'gi'),
                        new RegExp(`sections\\.[^.]+\\.${field}`, 'gi'),
                        new RegExp(`The ${field} field`, 'gi'),
                        new RegExp(`${field} field`, 'gi')
                    ];

                    patterns.forEach(function (pattern) {
                        friendlyMessage = friendlyMessage.replace(pattern, `${friendlyNames[field]} field`);
                    });
                });

                return friendlyMessage + contextInfo;
            }

            initializeSortable();
        });
    </script>
@endpush
