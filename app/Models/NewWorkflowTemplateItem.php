<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class NewWorkflowTemplateItem extends Model
{
    use HasFactory, softDeletes;

    protected $guarded = [];

    protected $casts = [
        'dependency_steps' => 'array',
        'is_entry_point' => 'boolean',
        'maker_escalation_user_ids' => 'array',
        'checker_escalation_user_ids' => 'array'
    ];

    public function parent() {
        return $this->belongsTo(NewWorkflowTemplate::class);
    }

    /**
     * Get multiple maker escalation users
     */
    public function makerEscalationUsers() {
        return User::whereIn('id', $this->maker_escalation_user_ids ?? [])->get();
    }

    /**
     * Get multiple checker escalation users
     */
    public function checkerEscalationUsers() {
        return User::whereIn('id', $this->checker_escalation_user_ids ?? [])->get();
    }

    public function department() {
        return $this->belongsTo(Department::class);
    }

    public function checklist() {
        return $this->belongsTo(DynamicForm::class, 'checklist_id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function makerEscalationUser() {
        return $this->belongsTo(User::class, 'maker_escalation_user_id');
    }

    public function checker() {
        return $this->belongsTo(User::class, 'checker_id');
    }

    public function checkerEscalationUser() {
        return $this->belongsTo(User::class, 'checker_escalation_user_id');
    }

    public function makerEscalationEmailNotification() {
        return $this->belongsTo(NotificationTemplate::class, 'maker_escalation_email_notification');
    }

    public function makerEscalationPushNotification() {
        return $this->belongsTo(NotificationTemplate::class, 'maker_escalation_push_notification');
    }

    public function checkerEscalationEmailNotification() {
        return $this->belongsTo(NotificationTemplate::class, 'checker_escalation_email_notification');
    }

    public function checkerEscalationPushNotification() {
        return $this->belongsTo(NotificationTemplate::class, 'checker_escalation_push_notification');
    }

    public function makerCompletionEmailNotification() {
        return $this->belongsTo(NotificationTemplate::class, 'maker_completion_email_notification');
    }

    public function makerCompletionPushNotification() {
        return $this->belongsTo(NotificationTemplate::class, 'maker_completion_push_notification');
    }

    public function makerDependencyEmailNotification() {
        return $this->belongsTo(NotificationTemplate::class, 'maker_dependency_email_notification');
    }

    public function makerDependencyPushNotification() {
        return $this->belongsTo(NotificationTemplate::class, 'maker_dependency_push_notification');
    }
}
