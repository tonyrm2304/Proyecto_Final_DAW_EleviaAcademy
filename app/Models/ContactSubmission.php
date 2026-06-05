<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactSubmission extends Model
{
    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'privacy_accepted',
        'status',
    ];

    protected $casts = [
        'privacy_accepted' => 'boolean',
    ];
}
