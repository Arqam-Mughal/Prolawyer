<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'case_subject',
        'display_board_subject',
        'stripe_publishable_key',
        'stripe_secret_key',
        'ccavenue',
        'ccavenue_access_code',
        'ccavenue_key',
        'ccavenue_merchant_id',
        'encryption_key',
        'created_at'
    ];
}
