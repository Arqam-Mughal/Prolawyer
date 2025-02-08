<?php

namespace App\Models;

use App\Models\CaseLabel;
use App\Models\Petitioner;
use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CaseModel extends Model
{
    use CrudTrait;

    protected $table = 'cases';

    protected $primaryKey = 'id';

    protected $fillable = [
        'case_no',
        'file_no',
        'previous_date',
        'case_stage',
        'sr_no_in_court',
        'brief_for',
        'Brief_no',
        'police_station',
        'organization_id',
        'tags',
        'cnr_no',
        'remarks',
        'notes_description',
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
        'favourite_case',
        'attended_case',
        'in_progress_case',
        'completed_case',
        'case_labels',
        'cwn',
        'assigned_to',
        'receiving_date',
        'filling_date',
        'hearing_date',

    ];


    public function caseImages()
    {
        return $this->hasMany(CaseImage::class, 'case_id', 'id');
    }

    public function petitioners() {
        return $this->hasMany(Petitioner::class, 'case_id', 'id');
    }

    public function clients()
    {
        return $this->hasMany(Client::class, 'id', 'client_id');
    }

    public function respondents() {
        return $this->hasMany(Respondent::class, 'case_id', 'id');
    }

    public function petitioner_advocates() {
        return $this->hasMany(PetitionerAdvocate::class, 'case_id', 'id');
    }

    public function respondent_advocates() {
        return $this->hasMany(RespondentAdvocate::class, 'case_id', 'id');
    }

    public function state()
    {
        return $this->belongsTo(ApiState::class, 'state_id');
    }

    public function district()
    {
        return $this->belongsTo(ApiDistrict::class, 'district_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }
    public function tag()
    {
        return $this->belongsTo(RefTag::class, 'tags');
    }

    public function labels(){
        return $this->belongsToMany(CaseLabel::class, 'case_labels_case', 'case_id', 'case_label_id');
    }

    public function petitionerAdvocates()
    {
        return $this->hasManyThrough(
            PetitionerAdvocate::class, // The model you want to access
            Petitioner::class, // The intermediate model
            'case_id', // Foreign key on the petitioners table
            'id', // Foreign key on the advocates table
            'id', // Local key on the cases table
            'advocate_id' // Local key on the petitioners table
        );
    }

    // Relationship to fetch advocates for respondents
    public function respondentAdvocates()
    {
        return $this->hasManyThrough(
            RespondentAdvocate::class, // The model you want to access
            Respondent::class, // The intermediate model
            'case_id', // Foreign key on the respondents table
            'id', // Foreign key on the advocates table
            'id', // Local key on the cases table
            'advocate_id' // Local key on the respondents table
        );
    }


    public function getTitleAttribute()
    {
        return $this->petitioners->implode('petitioner', ', ') // List all petitioners
            . ' VS ' . $this->respondents->implode('respondent', ', ') // List all respondents
            . ' (' . $this->case_no . ')'; // Include case number
    }
}
