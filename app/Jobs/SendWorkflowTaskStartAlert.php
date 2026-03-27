<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
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
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class SendWorkflowTaskStartAlert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $task;
    public $type;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($task, $type)
    {
        $this->task = $task;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $task = ChecklistTask::find($this->task->id);
        $workflowItem = $task->wf;
        $type = $this->type;

        if ($type == 'maker') {
            $maker = $workflowItem->user;
            $escalationUser = \App\Models\User::whereIn('id', [$maker->id])->get();

            $defaultTitle = "Workflow Task : {$task->code}";
            $defaultContent = "Task {$task->code} will be starting in 30 minutes";

            $template = NotificationTemplate::find(16);
            $template2 = NotificationTemplate::find(17);

            try {
                if (isset($template->id)) {
                    $title = $this->replacePlaceholders($template->title, $task, $workflowItem, $maker);
                    $content = $this->replacePlaceholders($template->content, $task, $workflowItem, $maker);

                    if (!$escalationUser->isEmpty($escalationUser)) {
                        foreach ($escalationUser as $escalationUserRow) {
                            $deviceTokens = DeviceToken::where('user_id', $escalationUserRow->id)->pluck('token')->toArray();
                            if (!empty($deviceTokens)) {
                                Helper::sendPushNotification($deviceTokens, [
                                    'title' => $title,
                                    'description' => $content
                                ]);
                            }
                        }
                    }
                } else {
                    foreach ($escalationUser as $escalationUserRow) {
                        $deviceTokens = DeviceToken::where('user_id', $escalationUserRow->id)->pluck('token')->toArray();
                        if (!empty($deviceTokens)) {
                            Helper::sendPushNotification($deviceTokens, [
                                'title' => $defaultTitle,
                                'description' => $defaultContent
                            ]);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Workflow Maker Starting Push Notification Failed: ' . $e->getMessage());
            }

            try {
                if (isset($template2->id)) {
                    $title = $this->replacePlaceholders($template2->title, $task, $workflowItem, $maker);
                    $content = $this->replacePlaceholders($template2->content, $task, $workflowItem, $maker);

                    if (!$escalationUser->isEmpty($escalationUser)) {
                        foreach ($escalationUser as $escalationUserRow) {
                            Mail::to($escalationUserRow->email)->send(new \App\Mail\EscalationMail($title, $content));
                        }
                    }
                } else {
                    if (!$escalationUser->isEmpty($escalationUser)) {
                        foreach ($escalationUser as $escalationUserRow) {
                            Mail::to($escalationUserRow->email)->send(new \App\Mail\EscalationMail($defaultTitle, $defaultContent));
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Workflow Maker Starting Email Notification Failed: ' . $e->getMessage());
            }
        }
    }

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
            '{$task_completed_by}' => date('d-m-Y H:i', strtotime($task->date))
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }
}
