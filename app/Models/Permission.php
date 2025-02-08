<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use CrudTrait;
    protected $guarded = ['id'];

    public function roles()
    {
        return $this->belongsToMany(Role::class,'role_permission','permission_id','role_id');
    }

    public function scopeModule($query)
    {
        $query->where(function ($query){
            $query->where('route', 'LIKE', '%index%')->orWhere('route', 'LIKE', '%store%')->orWhere('route', 'LIKE', '%create%');
        });
    }

}
