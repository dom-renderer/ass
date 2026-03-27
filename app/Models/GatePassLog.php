<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class GatePassLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function task()
    {
        return $this->belongsTo(ChecklistTask::class, 'task_id');
    }

    public function verifiedby()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function scopeEntry($query) {
        return $query->where('entry_type', 1);
    }

    public function scopeExit($query) {
        return $query->where('entry_type', 2);
    }

    public function scopeScan($query) {
        return $query->where('validation_type', 0);
    }

    public function scopeManual($query) {
        return $query->where('validation_type', 1);
    }

    public function scopeValid($query) {
        return $query->where('is_valid', 0);
    }

    public function scopeInvalid($query) {
        return $query->where('is_valid', 1);
    }
}
