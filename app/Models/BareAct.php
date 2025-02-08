<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BareAct extends Model
{
    protected $fillable = ['title', 'link'];


    //name protected
    protected $table = 'bare_acts';
}
