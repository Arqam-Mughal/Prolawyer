<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorklistCategory extends Model
{
    use CrudTrait;

    protected $guarded = ['id'];

    public function worklists() {
        return $this->hasMany(Worklist::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
