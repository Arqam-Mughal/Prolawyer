<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaseAct extends Model
{
    use HasFactory;

    protected $fillable = [
        'case_id',
        'act_id',
    ];
    public $timestamps = false;

}
