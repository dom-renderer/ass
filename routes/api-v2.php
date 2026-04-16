<?php

use App\Http\Controllers\API\v2\WorkflowApiController;
use App\Http\Controllers\API\v2\ApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'api-maintenance'])->group(function () {
    Route::post('submit', [ApiController::class, 'submission']);
    Route::get('tasks', [ApiController::class, 'tasks']);
    Route::post('verify-pass', [ApiController::class, 'verifyPass']);
    Route::post('image-submissions', [ApiController::class, 'submitImages']);
    Route::post('refresh-task-list', [ApiController::class, 'refreshTaskListing']);

    Route::get('get-notifications', [ApiController::class, 'getNotifications']);
    Route::post('generate-pdf-report', [ApiController::class, 'generatePdfReport']);

    Route::get('particulars', [ApiController::class, 'particulars']);
    Route::get('issues', [ApiController::class, 'issues']);
    Route::get('users', [ApiController::class, 'users']);

    Route::post('create-ticket', [ApiController::class, 'createTicket']);
    Route::get('tickets', [ApiController::class, 'tickets']);
    Route::post('tickets/{id}/accept', [ApiController::class, 'acceptTicket']);
    Route::post('tickets/{id}/reopen', [ApiController::class, 'reopenTicket']);
    Route::post('tickets/{id}/in-progress', [ApiController::class, 'inprogressTicket']);
    Route::post('tickets/{id}/close', [ApiController::class, 'closeTicket']);
    Route::post('tickets/{id}/reply', [ApiController::class, 'replyTicket']);

    Route::get('data-web-view', [APIController::class,'dataWebView']);

    Route::get('task-status', [ApiController::class, 'taskStatus']);
    Route::get('task-progress', [ApiController::class, 'taskProgres']);
    Route::get('checklist-list', [ApiController::class, 'checklistList']);

    Route::get('document-types', [ApiController::class, 'documentTypes']);
    Route::get('documents', [ApiController::class, 'documents']);

    Route::get('workflow-progress-dashboard', [WorkflowApiController::class, 'progressDashboard']);
    Route::get('workflow-section-progress', [WorkflowApiController::class, 'sectionProgress']);
    Route::get('workflow-section-progress-2', [WorkflowApiController::class, 'sectionProgress2']);
    Route::get('workflow-tasks', [WorkflowApiController::class, 'tasks']);
    Route::post('workflow-submit', [WorkflowApiController::class, 'submitTask']);
    Route::post('workflow-image-submissions', [WorkflowApiController::class, 'submitOnlyImagesForTask']);
    Route::get('workflows', [WorkflowApiController::class, 'workflows']);
    Route::post('verify-workflow-task', [WorkflowApiController::class, 'verifyTask']);

    Route::post('workflow-approve-decline', [WorkflowApiController::class, 'approveDecline']);
    Route::get('workflow-list-redo-action-tasks', [WorkflowApiController::class, 'redoActionTasks']);
    Route::get('workflow-get-redo-actions', [WorkflowApiController::class, 'getRedoActions']);
    Route::post('workflow-submit-redo', [WorkflowApiController::class, 'submitRedo']);
    Route::get('workflow-reassignment-tasks', [WorkflowApiController::class, 'reassignmentTasks']);

    Route::get('pass-verifications', [ApiController::class, 'passVerifications']);
    Route::post('checker-submission', [ApiController::class, 'checkerSubmission']);

    Route::get('scan-asset-location', [ApiController::class, 'scanAssetLocation']);

    Route::get('asset-categories', [ApiController::class, 'assetCategories']);
    Route::get('asset-makes', [ApiController::class, 'assetMakes']);
    Route::get('asset-models', [ApiController::class, 'assetModels']);

    Route::get('settings', [ApiController::class, 'settings']);
});