<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Worklist extends Model
{
    use CrudTrait;
    use HasFactory;

    protected $guarded = ['id'];

    public function category()
    {
        return $this->belongsTo(WorklistCategory::class);
    }

    public function case()
    {
        return $this->belongsTo(CaseModel::class, 'case_id');
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
