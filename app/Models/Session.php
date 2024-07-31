<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Session extends Model
{
    use HasFactory;

    public $guarded = [];

    protected $casts = [
        'attachments' => 'array',
        'items' => 'array',
    ];

    public function created_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
