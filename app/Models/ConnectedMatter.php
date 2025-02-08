<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ConnectedMatter extends Model
{
    // Define the table associated with the model if it's not the plural of the model name
    protected $table = 'connected_matters';

    // Define the fillable attributes if you are using mass assignment
    protected $fillable = [
        'lawyer_id',
        'connected_matters',
        'primary_case'
        // Add other attributes as needed
    ];

    protected $casts = [
        'connected_matters' => 'array', // Cast to array
    ];


    // Relationship to fetch the primary case details

    public function primaryCase(): HasOne
    {
        return $this->hasOne(CaseModel::class, 'id', 'primary_case');
    }

    // Custom relationship to fetch each connected matter's details

    public function connectedCases()
    {
        // Convert JSON array of connected_matters to flat array and use whereIn
        return CaseModel::whereIn('id', $this->connected_matters ?: [])->get();
    }

}
