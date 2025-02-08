<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourtList extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'case_no',
        'file_no',
        'petitioner',
        'petitioner_advocates',
        'respondent',
        'respondent_advocates',
        'judgement_status',
        'acts',
        'image',
        'hearing_dates',
        'hearing_date_yes',
        'filling_date_yes',
        'receiving_date_yes',
        'hearing_date',
        'filling_date',
        'receiving_date',
        'brief_no',
        'previous_date',
        'stage',
        'sr_no_in_court',
        'brief_for',
        'police_station',
        'organization_id',
        'tags',
        'cnr_no',
        'remarks',
        'description',
        'court_room_no',
        'judge_name',
        'state_id',
        'next_date',
        'district_id',
        'tabs',
        'court_bench',
        'case_year',
        'case_category',
        'client_id',
        'decided_toggle',
        'abbondend_toggle',
        'custom_field',
        'long_form'  // Add this line
    ];
}
