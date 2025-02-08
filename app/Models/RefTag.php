<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefTag extends Model
{
    use CrudTrait;
    use HasFactory;
    protected $fillable = [
        'name',  // Add any other fields that are fillable
        'email',
        'contact',
        'address',
        // Add more fields as needed
    ];
}
