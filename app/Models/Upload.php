<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    protected $fillable = [
        'case_id',
        'user_id',
        'hearing_date_id',
        'user_filename',
        'filename',
        'filepath',
        'file_type',
        'uuid', // Add any other fields you want to allow for mass assignment
    ];

    // If you have timestamps, you can add this too
    public $timestamps = true; // Assuming you want to keep track of created_at and updated_at
}

