<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RespondentAdvocate extends Model
{
    use HasFactory;

    protected $fillable = [
        'case_id',  // Add any other fields that are fillable
        'respondent_advocate'
        // Add more fields as needed
    ];

    public function case()
    {
        return $this->belongsTo(\App\Models\CaseModel::class)
                    ->withPivot('respondent_advocate');
    }
}
