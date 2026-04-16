<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class DocumentUpload extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'document_owners' => 'array',
    ];

    protected $appends = ['doc_link'];

    public function document()
    {
        return $this->belongsTo( Document::class, 'document_id' );
    }

    public function store()
    {
        return $this->belongsTo( Store::class, 'location_id' )->withoutGlobalScope('os')->ass();
    }
    
    public function storeCategory()
    {
        return $this->belongsTo( StoreCategory::class, 'location_category_id' );
    }

    public function users()
    {
        return $this->belongsToMany( User::class, 'document_users', 'document_upload_id', 'user_id' )->where('document_users.user_type', 0)->withTimestamps();
    }

    public function addusers()
    {
        return $this->belongsToMany( User::class, 'document_users', 'document_upload_id', 'user_id' )->where('document_users.user_type', 1)->withTimestamps();
    }

    public function getAttachmentPathAttribute() {

        if ( !empty(trim($this->file_name)) && file_exists( public_path( "storage/documents/{$this->file_name}" ) ) ) {
            return asset( "storage/documents/{$this->file_name}" );
        }
        return '';
    }

    public function getDocLinkAttribute() {

        if ( !empty(trim($this->file_name)) && file_exists( public_path( "storage/documents/{$this->file_name}" ) ) ) {
            return asset( "storage/documents/{$this->file_name}" );
        }
        return '';
    }

    public function scopeExpirable($query) {
        return $query->where('perpetual', 0);
    }

}
