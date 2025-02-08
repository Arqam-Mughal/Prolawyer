<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PetitionerAdvocate extends Model
{
    protected $fillable = [
        'case_id',  // Add any other fields that are fillable
        'petitioner_advocate',
        // Add more fields as needed
    ];

    public function case()
    {
        return $this->belongsTo(\App\Models\CaseModel::class)
                    ->withPivot('petitioner_advocate');
    }
}
