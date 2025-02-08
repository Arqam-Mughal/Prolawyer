<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Petitioner extends Model
{
    protected $fillable = [
        'case_id',  // Add any other fields that are fillable
        'petitioner',
        // Add more fields as needed
    ];

    // Rest of your model code
    public function case()
    {
        return $this->belongsToMany(\App\Models\CaseModel::class)
                    ->withPivot('petitioner');
    }
}
