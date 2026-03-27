<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Helpers\Helper;

class ChecklistTask extends Model implements Auditable
{
    use HasFactory, SoftDeletes, \OwenIt\Auditing\Auditable;

    protected $guarded = [];

    protected $auditInclude = [
        'completion_date',
        'status',
        'data',
        'started_at',
        'extra_info'
    ];

    protected $casts = [
        'data' => 'object',
        'form' => 'object',
        'extra_info' => 'object'
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function (ChecklistTask $task) {
            $task->handleGatePassGeneration();
            $task->handleCheckersMapping();
        });
    }

    public function handleCheckersMapping() 
    {
        try {
            $this->loadMissing(['parent.parent.checklist.checkers']);

            if ($this->type == 0 && $this->parent && $this->parent->parent && $this->parent->parent->checklist && !empty($this->parent->parent->checklist->checkers)) {
                foreach ($this->parent->parent->checklist->checkers as $row) {
                    \App\Models\TaskChecker::create([
                        'task_id' => $this->id,
                        'user_id' => $row->user_id,
                        'level' => $row->level
                    ]);
                }
            }

        } catch (\Throwable $e) {
            Log::error('Checkers Mapping generation failed', [
                'task_id' => $this->id,
                'error'   => $e->getMessage()
            ]);
        }
    }

    public function handleGatePassGeneration()
    {
        try {
            $this->loadMissing(['parent.parent.checklist', 'parent.actuallocation']);

            if (!$this->shouldGeneratePass()) {
                return;
            }

            $passNumber = $this->generatePassNumber();

            $this->updateQuietly([
                'pass_number' => $passNumber
            ]);

            $this->generateQrCode($passNumber);

        } catch (\Throwable $e) {
            Log::error('Gate pass generation failed', [
                'task_id' => $this->id,
                'error'   => $e->getMessage()
            ]);
        }
    }

    protected function shouldGeneratePass()
    {
        return (
            $this->parent &&
            $this->parent->parent &&
            $this->parent->parent->checklist &&
            $this->parent->parent->checklist->needs_pass &&
            empty($this->pass_number) &&
            $this->type == 0
        );
    }

    protected function generatePassNumber()
    {
        $locationCode = substr(strval(optional($this->parent->actuallocation)->code), 0, 4);

        if (empty($locationCode)) {
            $locationCode = strtoupper(Str::random(3));
        }

        $datePart = now()->format('ymd');
        $randomPart = Helper::generatePass($this->id);
        $randomPart = Helper::taskIdEncrypt($randomPart);

        return "{$locationCode}-{$datePart}-{$randomPart}";
    }

    protected function generateQrCode(string $passNumber)
    {
        $folderPath = 'public/passes';

        if (!Storage::exists($folderPath)) {
            Storage::makeDirectory($folderPath);
        }

        $fileName = $this->pass_number . '.png';
        $fullPath = storage_path('app/' . $folderPath . '/' . $fileName);

        QrCode::format('png')
            ->size(300)
            ->margin(2)
            ->generate($passNumber, $fullPath);
    }

    public function parent() {
        return $this->belongsTo(ChecklistSchedulingExtra::class, 'checklist_scheduling_id');
    }

    public function ckrs() {
        return $this->hasMany(TaskChecker::class, 'task_id', 'id')->orderBy('level');
    }

    public function wf() {
        return $this->belongsTo(NewWorkflowAssignmentItem::class, 'workflow_checklist_id');
    }

    public function passlogs() {
        return $this->hasMany(GatePassLog::class, 'task_id')->orderBy('id', 'DESC');
    }

    public function scopePending($query) {
        return $query->where('status', 0);
    }

    public function scopeInprogress($query) {
        return $query->where('status', 1);
    }

    public function scopeCompleted($query) {
        return $query->where('status', 2);
    }

    public static function scopeScheduling($query) {
        return $query->where('type', 0);
    }

    public static function scopeWorkflow($query) {
        return $query->where('type', 1);
    }

    public function clist() {
        return $this->belongsTo(DynamicForm::class, 'checklist_id');
    }

    public function redos() {
        return $this->hasMany(RedoAction::class, 'task_id');
    }

    public function submissionentries() {
        return $this->hasMany(SubmissionTime::class, 'task_id')->orderBy('id', 'DESC');
    }

    public function restasks() {
        return $this->hasMany(RescheduledTask::class, 'task_id')->orderBy('id', 'DESC');
    }
}