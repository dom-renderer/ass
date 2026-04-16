<?php

namespace App\Console\Commands;

use App\Models\NewWorkflowAssignmentItem;
use Illuminate\Support\Facades\Log;
use App\Models\NotificationTemplate;
use App\Models\ChecklistTask;
use App\Models\DeviceToken;
use Illuminate\Console\Command;
use App\Helpers\Helper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class SendWorkflowEscalationNotifications extends Command
{
    protected $signature = 'workflow:escalation-notifications';
    protected $description = 'Send escalation notifications for overdue workflow tasks';

    public function handle()
    {
        $this->info('Starting workflow escalation notifications...');

        $this->processMakerEscalations();
        $this->processCheckerEscalations();

        $this->info('Workflow escalation notifications completed.');
    }

    private function processMakerEscalations()
    {
        $tasks = ChecklistTask::with(['wf.user', 'wf.makerEscalationEmailNotification', 'wf.makerEscalationPushNotification'])
            ->workflow()
            ->whereHas('wf', function ($builder) {
                $builder->whereNotIn('new_workflow_assignment_id', [9, 10, 11]);
            })
            ->whereIn('status', [0, 1])
            ->whereNull('maker_escalation_sent_at')
            ->whereHas('wf', function ($query) {
                $query->whereNotNull('maker_escalation_user_ids')
                    ->where(function ($q) {
                        $q->whereNotNull('maker_escalation_after_day')
                            ->orWhereNotNull('maker_escalation_after_hour')
                            ->orWhereNotNull('maker_escalation_after_minute');
                    });
            })
            ->get();

        foreach ($tasks as $task) {
            $workflowItem = $task->wf;

            if (!$workflowItem || empty($workflowItem->maker_escalation_user_ids)) {
                continue;
            }

            $escalationDays = ($workflowItem->maker_escalation_after_day ?? 0) + ($workflowItem->maker_turn_around_time_day ?? 0);
            $escalationHours = ($workflowItem->maker_escalation_after_hour ?? 0) + ($workflowItem->maker_turn_around_time_hour ?? 0);
            $escalationMinutes = ($workflowItem->maker_escalation_after_minute ?? 0) + ($workflowItem->maker_turn_around_time_minute ?? 0);

            $startedAt = Carbon::parse($task->date);
            $escalationTime = $startedAt->copy()
                ->addDays($escalationDays)
                ->addHours($escalationHours)
                ->addMinutes($escalationMinutes);
            
            if (Carbon::now()->greaterThanOrEqualTo($escalationTime)) {
                $this->sendMakerEscalationNotification($task, $workflowItem);

                $task->maker_escalation_sent_at = now();
                $task->save();

                $this->info("Sent maker escalation for task #{$task->id}");
            }
        }
    }

    private function processCheckerEscalations()
    {
        $tasks = ChecklistTask::with(['wf.checker', 'wf.checkerEscalationEmailNotification', 'wf.checkerEscalationPushNotification'])
            ->workflow()
            ->whereHas('wf', function ($builder) {
                $builder->whereNotIn('new_workflow_assignment_id', [9, 10, 11]);
            })
            ->where('status', 2)
            ->whereNull('checker_escalation_sent_at')
            ->whereHas('wf', function ($query) {
                $query->whereNotNull('checker_escalation_user_ids')
                    ->where(function ($q) {
                        $q->whereNotNull('checker_escalation_after_day')
                            ->orWhereNotNull('checker_escalation_after_hour')
                            ->orWhereNotNull('checker_escalation_after_minute');
                    });
            })
            ->get();

        foreach ($tasks as $task) {
            $workflowItem = $task->wf;

            if (!$workflowItem || empty($workflowItem->checker_escalation_user_ids)) {
                continue;
            }

            $escalationDays = ($workflowItem->checker_escalation_after_day ?? 0) + ($workflowItem->checker_turn_around_time_day ?? 0);
            $escalationHours = ($workflowItem->checker_escalation_after_hour ?? 0) + ($workflowItem->checker_turn_around_time_hour ?? 0);
            $escalationMinutes = ($workflowItem->checker_escalation_after_minute ?? 0) + ($workflowItem->checker_turn_around_time_minute ?? 0);

            $completedAt = Carbon::parse($task->completed_by);
            $escalationTime = $completedAt->copy()
                ->addDays($escalationDays)
                ->addHours($escalationHours)
                ->addMinutes($escalationMinutes);

            if (Carbon::now()->greaterThanOrEqualTo($escalationTime)) {
                $this->sendCheckerEscalationNotification($task, $workflowItem);

                $task->checker_escalation_sent_at = now();
                $task->save();

                $this->info("Sent checker escalation for task #{$task->id}");
            }
        }
    }

    private function sendMakerEscalationNotification(ChecklistTask $task, NewWorkflowAssignmentItem $workflowItem)
    {
        $mixedUsers = is_array($workflowItem->maker_escalation_user_ids) ? $workflowItem->maker_escalation_user_ids : [];
        if (isset($workflowItem->parent->id) && $workflowItem->parent->send_to_all_notification) {
            array_push($mixedUsers, $workflowItem->user_id);
        }

        $escalationUser = \App\Models\User::whereIn('id', $mixedUsers)->get();
        $maker = $workflowItem->user;

        $defaultTitle = "Workflow Task Escalation: {$task->code}";
        $defaultContent = "Task {$task->code} assigned to " . ($maker ? $maker->name : 'Unknown') . " has exceeded the allowed time and requires attention.";

        try {
            if ($workflowItem->makerEscalationPushNotification) {
                $template = $workflowItem->makerEscalationPushNotification;
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
            Log::error('Workflow Maker Push Notification Failed: ' . $e->getMessage());
        }

        try {
            if ($workflowItem->makerEscalationEmailNotification) {
                $template = $workflowItem->makerEscalationEmailNotification;
                $title = $this->replacePlaceholders($template->title, $task, $workflowItem, $maker);
                $content = $this->replacePlaceholders($template->content, $task, $workflowItem, $maker);

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
            Log::error('Workflow Maker Email Notification Failed: ' . $e->getMessage());
        }
    }

    private function sendCheckerEscalationNotification(ChecklistTask $task, NewWorkflowAssignmentItem $workflowItem)
    {
        $mixedUsers = is_array($workflowItem->checker_escalation_user_ids) ? $workflowItem->checker_escalation_user_ids : [];
        if (isset($workflowItem->parent->id) && $workflowItem->parent->send_to_all_notification) {
            array_push($mixedUsers, $workflowItem->user_id);
        }

        $escalationUser = \App\Models\User::whereIn('id', $mixedUsers)->get();
        $checker = $workflowItem->checker;

        $defaultTitle = "Workflow Verification Escalation: {$task->code}";
        $defaultContent = "Task {$task->code} awaiting verification by " . ($checker ? $checker->name : 'Unknown') . " has exceeded the allowed time and requires attention.";

        try {
            if ($workflowItem->checkerEscalationPushNotification) {
                $template = $workflowItem->checkerEscalationPushNotification;
                $title = $this->replacePlaceholders($template->title, $task, $workflowItem, $checker);
                $content = $this->replacePlaceholders($template->content, $task, $workflowItem, $checker);

                foreach ($escalationUser as $escalationUserRow) {
                    $deviceTokens = DeviceToken::where('user_id', $escalationUserRow->id)->pluck('token')->toArray();
                    if (!empty($deviceTokens)) {
                        Helper::sendPushNotification($deviceTokens, [
                            'title' => $title,
                            'description' => $content
                        ]);
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
            Log::error('Workflow Checker Push Notification Failed: ' . $e->getMessage());
        }

        try {
            if ($workflowItem->checkerEscalationEmailNotification) {
                $template = $workflowItem->checkerEscalationEmailNotification;
                $title = $this->replacePlaceholders($template->title, $task, $workflowItem, $checker);
                $content = $this->replacePlaceholders($template->content, $task, $workflowItem, $checker);

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
            Log::error('Workflow Checker Email Notification Failed: ' . $e->getMessage());
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
