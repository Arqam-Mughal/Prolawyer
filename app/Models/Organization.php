<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use CrudTrait;
    protected $fillable = [
        'representator',  // Add any other fields that are fillable
        'email',
        'organization_name',
        'contact',
        'address',
        // Add more fields as needed
    ];

    public function identifiableAttribute()
    {
        return $this->organization_name; // Or whatever attribute you want to use
    }
}
