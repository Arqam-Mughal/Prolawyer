<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use CrudTrait;
    use HasFactory;
    protected $fillable = [
        'name',  // Add any other fields that are fillable
        'email',
        'gender',
        'mobile',
        'address',
        'description',
        'state_id',
        'city_id',
        'country_id',


    ];


    public function user() {
        return $this->belongsTo(User::class);
    }
}
