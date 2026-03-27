<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class ChecklistChecker extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function checklist()
    {
        return $this->belongsTo(DynamicForm::class, 'checklist_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
