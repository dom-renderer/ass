<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Font extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'normal_file',
        'bold_file',
        'italic_file',
        'bold_italic_file',
    ];

    protected $appends = [
        'normal_url',
        'bold_url',
        'italic_url',
        'bold_italic_url',
    ];

    public function getNormalUrlAttribute()
    {
        return $this->normal_file ? Storage::url($this->normal_file) : null;
    }

    public function getBoldUrlAttribute()
    {
        return $this->bold_file ? Storage::url($this->bold_file) : null;
    }

    public function getItalicUrlAttribute()
    {
        return $this->italic_file ? Storage::url($this->italic_file) : null;
    }

    public function getBoldItalicUrlAttribute()
    {
        return $this->bold_italic_file ? Storage::url($this->bold_italic_file) : null;
    }
}

