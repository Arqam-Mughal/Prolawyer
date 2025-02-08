<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Role as OriginalRole;


class Role extends OriginalRole
{
    use CrudTrait;
    protected $guarded = ['id'];

    public function getRouteKeyName(): string
    {
        return 'type';  // Use 'type' to bind roles by type instead of id
    }

    const ROLE_TYPES = [
        'regular_user' => 'Regular user',
        'system_user' => 'System Admin',
        'sub_lawyer' => 'Sub lawyer'
    ];

    public function isAdmin($where = [])
    {
        if (auth()->check()) {
            $role = self::select('type')->where('id', auth()->user()->role_id)->first();

            if ($role->type == 'system_user') {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }


}
