<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth', 'permission']], function() {

    Route::resource('workflow-checklists', \App\Http\Controllers\WorkflowChecklistController::class);
    Route::match(['GET', 'PUT'],'workflow-duplicate-checklist/{id}', [\App\Http\Controllers\WorkflowChecklistController::class, 'duplicate'])->name('workflow-duplicate-checklist');

    Route::post('workflow-templates/import', [\App\Http\Controllers\WorkflowTemplateController::class, 'import'])->name('workflow-templates.import');
    Route::get('workflow-templates/template/download', [\App\Http\Controllers\WorkflowTemplateController::class, 'downloadTemplate'])->name('workflow-templates.download-template');
    Route::resource('workflow-templates', \App\Http\Controllers\WorkflowTemplateController::class);
    
    Route::resource('workflow-assignments', \App\Http\Controllers\WorkflowAssignmentController::class);

    Route::resource('workflow-tasks', \App\Http\Controllers\WorkflowTaskController::class);
    Route::post('bulk-delete-workflow-tasks', [\App\Http\Controllers\WorkflowTaskController::class, 'bulkDelete'])->name('workflow-tasks.bulk-delete');
    Route::get('workflow-tasks-submission-view/{id}', [\App\Http\Controllers\WorkflowTaskController::class, 'submissionView'])->name('workflow-tasks.submission-view');

    Route::post('workflow-assignment-list', [\App\Http\Controllers\WorkflowAssignmentController::class, 'workflowAssignmentList'])->name('workflow-assignment-list');

    Route::get('workflow-task-export-excel/{id}', [\App\Http\Controllers\WorkflowAssignmentController::class, 'exportExcel'])->name('workflow-task-export-excel');
    Route::get('workflow-task-export-pdf/{id}', [\App\Http\Controllers\WorkflowAssignmentController::class, 'exportPdf'])->name('workflow-task-export-pdf');
    Route::get('workflow-task-export-compressed-pdf/{id}', [\App\Http\Controllers\WorkflowAssignmentController::class, 'exportCompressedPdf'])->name('workflow-task-export-compressed-pdf');
    Route::get('workflow-test-report/{id}', [\App\Http\Controllers\WorkflowAssignmentController::class, 'testPdf'])->name('workflow-test-report');

    Route::get('workflow-assignments-export-excel/{id}', [\App\Http\Controllers\WorkflowAssignmentController::class, 'exportAssignmentExcel'])->name('workflow-assignments.export-excel');
    Route::get('workflow-assignments-export-pdf/{id}', [\App\Http\Controllers\WorkflowAssignmentController::class, 'exportAssignmentPdf'])->name('workflow-assignments.export-pdf');

    Route::get('workflow-checklists-submission-comparison/{id}', [\App\Http\Controllers\WorkflowTaskController::class, 'sideBySideComparison'])->name('workflow.checklists-submission-comparison');
    Route::get('workflow-checklists-submission-view-for-maker/{id}', [\App\Http\Controllers\WorkflowTaskController::class, 'submissionViewForMaker'])->name('workflow.checklists-submission-view-for-maker');
    Route::get('workflow-checklists-submission-view-for-checker/{id}', [\App\Http\Controllers\WorkflowTaskController::class, 'submissionViewForChecker'])->name('workflow.checklists-submission-view-for-checker');

    Route::withoutMiddleware('permission')->group(function() {
        Route::match(['GET', 'POST'],'workflow-templates-duplicate/{id?}', [\App\Http\Controllers\WorkflowTemplateController::class, 'duplicate'])->name('workflow-templates.duplicate');
        Route::get('workflow-dashboard/{id}', [\App\Http\Controllers\WorkflowAssignmentController::class, 'dashboard'])->name('workflow-dashboard');
        Route::post('workflow-verify-each-fields/{id}', [\App\Http\Controllers\WorkflowTaskController::class, 'verifyEachFields'])->name('workflow.verify-each-fields');
        Route::get('workflow-task-status-change', [\App\Http\Controllers\WorkflowTaskController::class, 'changeStatus'])->name('workflow.task-status-change');
        Route::get('workflow-truthy-falsy', [\App\Http\Controllers\WorkflowTaskController::class, 'truthyFalsyFields'])->name('workflow.truthy-falsy');
        Route::get('workflow-templates/{id}/tree', [\App\Http\Controllers\WorkflowTemplateController::class, 'treeView'])->name('workflow-templates.tree');
        Route::get('workflow-templates/{id}/tree-data', [\App\Http\Controllers\WorkflowTemplateController::class, 'treeData'])->name('workflow-templates.tree-data');
        Route::get('workflow-assignments/{id}/tree', [\App\Http\Controllers\WorkflowAssignmentController::class, 'treeView'])->name('workflow-assignments.tree');
        Route::get('workflow-assignments/{id}/table', [\App\Http\Controllers\WorkflowAssignmentController::class, 'tableView'])->name('workflow-assignments.table');
        Route::get('workflow-assignments/{id}/tree-data', [\App\Http\Controllers\WorkflowAssignmentController::class, 'treeData'])->name('workflow-assignments.tree-data');
        Route::get('workflow-assignments/load-template/{id}', [\App\Http\Controllers\WorkflowAssignmentController::class, 'loadTemplate'])->name('workflow-assignments.load-template');
    });

});
