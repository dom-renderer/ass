<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\NewWorkflowAssignmentItem;
use Illuminate\Support\Facades\Log;
use App\Models\NotificationTemplate;
use App\Models\ChecklistTask;
use App\Models\DeviceToken;
use App\Helpers\Helper;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class SendWorkflowCompletionNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $taskId;

    public $timeout = 3600;
    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @param int $taskId
     * @return void
     */
    public function __construct($taskId)
    {
        $this->taskId = $taskId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $task = ChecklistTask::find($this->taskId);

        if (!$task) {
            Log::warning("SendWorkflowCompletionNotification: Task #{$this->taskId} not found.");
            return;
        }

        $workflowItem = $task->wf;

        if (!$workflowItem) {
            Log::warning("SendWorkflowCompletionNotification: No workflow item found for task #{$this->taskId}.");
            return;
        }

        // 1. Send completion notification to the current task's maker
        $this->sendMakerCompletionNotification($task, $workflowItem);

        // 2. Send completion notification to the current task's checker (if defined)
        if ($workflowItem->checker_id) {
            $this->sendMakerCompletionNotification($task, $workflowItem);
        }

        // 2. Send notification to dependent task makers
        // Find sibling steps where dependency = SELECTED_COMPLETED
        // and this step's ID is in their dependency_steps array
        $this->sendDependentTaskMakerNotifications($task, $workflowItem);
    }

    /**
     * Send maker completion notifications (email + push) to the current task's maker.
     */
    private function sendMakerCompletionNotification(ChecklistTask $task, NewWorkflowAssignmentItem $workflowItem)
    {
        $maker = $workflowItem->user;

        if (!$maker) {
            return;
        }

        $defaultTitle = "Workflow Task Completed: {$task->code}";
        $defaultContent = "Task {$task->code} ({$workflowItem->step_name}) has been completed by " . ($maker->name ?? 'Unknown') . ".";

        // Push notification
        try {
            $title = $this->replacePlaceholders($defaultTitle, $task, $workflowItem, $maker);
            $content = $this->replacePlaceholders($defaultContent, $task, $workflowItem, $maker);

            $deviceTokens = DeviceToken::where('user_id', $maker->id)->pluck('token')->toArray();
            if (!empty($deviceTokens)) {
                Helper::sendPushNotification($deviceTokens, [
                    'title' => $title,
                    'description' => $content
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Workflow Maker Completion Push Notification Failed: ' . $e->getMessage());
        }

        // Email notification
        try {
            $title = $this->replacePlaceholders($defaultTitle, $task, $workflowItem, $maker);
            $content = $this->replacePlaceholders($defaultContent, $task, $workflowItem, $maker);

            if (!empty($maker->email)) {
                Mail::to($maker->email)->send(new \App\Mail\EscalationMail($title, $content));
            }
        } catch (\Exception $e) {
            Log::error('Workflow Maker Completion Email Notification Failed: ' . $e->getMessage());
        }
    }

    /**
     * Send notifications to makers of dependent tasks.
     * Finds sibling assignment items where dependency = SELECTED_COMPLETED
     * and the current step's ID is in their dependency_steps.
     * Sends maker completion notification using the CURRENT step's templates.
     */
    private function sendDependentTaskMakerNotifications(ChecklistTask $task, NewWorkflowAssignmentItem $workflowItem)
    {
        // Only send if the current step has maker completion notification templates defined
        if (!$workflowItem->maker_dependency_push_notification && !$workflowItem->maker_dependency_email_notification) {
            return;
        }

        $maker = $workflowItem->user;

        // Find sibling steps in the same assignment where:
        // - dependency = SELECTED_COMPLETED
        // - dependency_steps JSON array contains the current step's ID
        $dependentItems = NewWorkflowAssignmentItem::where('new_workflow_assignment_id', $workflowItem->new_workflow_assignment_id)
            ->where('id', '!=', $workflowItem->id)
            ->where('dependency', 'SELECTED_COMPLETED')
            ->whereNotNull('dependency_steps')
            ->get();

        foreach ($dependentItems as $dependentItem) {
            $dependencySteps = is_array($dependentItem->dependency_steps) ? $dependentItem->dependency_steps : [];

            // Check if the current step's ID is in this item's dependency_steps
            // Cast to strings for comparison as JSON may store as strings
            $currentStepId = (string) $workflowItem->id;
            $dependencyStepsStr = array_map('strval', $dependencySteps);

            if (!in_array($currentStepId, $dependencyStepsStr)) {
                continue;
            }

            // Send notification to the dependent task's maker
            $dependentMaker = $dependentItem->user;

            if (!$dependentMaker) {
                continue;
            }

            $defaultTitle = "Dependent Task Ready: {$task->code}";
            $defaultContent = "Task {$task->code} ({$workflowItem->step_name}) has been completed. Your task ({$dependentItem->step_name}) may now be available to start.";

            // Push notification using current step's maker dependency push template
            try {
                if ($workflowItem->maker_dependency_push_notification && $workflowItem->makerCompletionPushNotification) {
                    $template = $workflowItem->makerCompletionPushNotification;
                    $title = $this->replacePlaceholders($template->title, $task, $workflowItem, $dependentMaker);
                    $content = $this->replacePlaceholders($template->content, $task, $workflowItem, $dependentMaker);

                    $deviceTokens = DeviceToken::where('user_id', $dependentMaker->id)->pluck('token')->toArray();
                    if (!empty($deviceTokens)) {
                        Helper::sendPushNotification($deviceTokens, [
                            'title' => $title,
                            'description' => $content
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::error("Workflow Dependent Maker Completion Push Notification Failed (dependent item #{$dependentItem->id}): " . $e->getMessage());
            }

            // Email notification using current step's maker dependency email template
            try {
                if ($workflowItem->maker_dependency_email_notification && $workflowItem->makerCompletionEmailNotification) {
                    $template = $workflowItem->makerCompletionEmailNotification;
                    $title = $this->replacePlaceholders($template->title, $task, $workflowItem, $dependentMaker);
                    $content = $this->replacePlaceholders($template->content, $task, $workflowItem, $dependentMaker);

                    if (!empty($dependentMaker->email)) {
                        Mail::to($dependentMaker->email)->send(new \App\Mail\EscalationMail($title, $content));
                    }
                }
            } catch (\Exception $e) {
                Log::error("Workflow Dependent Maker Completion Email Notification Failed (dependent item #{$dependentItem->id}): " . $e->getMessage());
            }
        }
    }

    /**
     * Replace placeholders in notification template text.
     */
    private function replacePlaceholders($text, ChecklistTask $task, NewWorkflowAssignmentItem $workflowItem, $user = null)
    {
        $checklist = $workflowItem->checklist;

        $replacements = [
            '{$name}' => $user ? "{$user->name} {$user->middle_name} {$user->last_name}" : 'N/A',
            '{$maker_name}' => isset($workflowItem->user->id) ? "{$workflowItem->user->name} {$workflowItem->user->middle_name} {$workflowItem->user->last_name}" : 'N/A',
            '{$checker_name}' => isset($workflowItem->checker->id) ? "{$workflowItem->checker->name} {$workflowItem->checker->middle_name} {$workflowItem->checker->last_name}" : 'N/A',
            '{$username}' => $user->username ?? 'N/A',
            '{$phone_number}' => $user->phone_number ?? 'N/A',
            '{$email}' => $user->email ?? 'N/A',
            '{$checklist_name}' => $checklist->name ?? 'N/A',
            '{$department_name}' => $workflowItem->section_name ?? 'N/A',
            '{$task_code}' => $task->code ?? 'N/A',
            '{$task_name}' => $workflowItem->step_name ?? 'N/A',
            '{$workflow_name}' => $workflowItem->parent->title ?? 'N/A',
            '{$task_start_date}' => date('d-m-Y H:i', strtotime($task->date)),
            '{$task_completed_by}' => $task->completion_date ? date('d-m-Y H:i', strtotime($task->completion_date)) : date('d-m-Y H:i', strtotime($task->date))
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }
}
