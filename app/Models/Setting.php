<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'ticket_watchers' => 'array',
    ];

    protected $appends = [
        'logo_url',
        'favicon_url',
        'app_logo_url',
    ];

    public function getLogoUrlAttribute()
    {
        return $this->logo ? url("storage/{$this->logo}") : null;
    }

    public function getFaviconUrlAttribute()
    {
        return $this->favicon ? url("storage/{$this->favicon}") : null;
    }

    public function getAppLogoUrlAttribute()
    {
        return $this->app_logo ? url("storage/{$this->app_logo}") : null;
    }

}
