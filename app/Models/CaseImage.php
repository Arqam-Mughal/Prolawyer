<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseImage extends Model
{
    protected $table = 'case_images';

    // Add case_id and other fields to the fillable array
    protected $fillable = [
        'case_id', // Add case_id to the fillable array
        'path',
        'image',
    ];

    // If necessary, you can add guarded fields here
    protected $guarded = [];
}




