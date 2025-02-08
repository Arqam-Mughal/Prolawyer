<?php

namespace App\Imports;

use App\Models\CaseModel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CasesImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new CaseModel([
            'case_no'          => array_key_exists('case_no', $row) ? $row['case_no'] : null,
            'file_no'          => array_key_exists('file_no', $row) ? $row['file_no'] : null,
            'previous_date'    => array_key_exists('previous_date', $row) ? $row['previous_date'] : null,
            'case_stage'       => array_key_exists('case_stage', $row) ? $row['case_stage'] : null,
            'sr_no_in_court'   => array_key_exists('sr_no_in_court', $row) ? $row['sr_no_in_court'] : null,
            'brief_for'        => array_key_exists('brief_for', $row) ? $row['brief_for'] : null,
            'police_station'   => array_key_exists('police_station', $row) ? $row['police_station'] : null,
            'organization_id'  => array_key_exists('organization_id', $row) ? $row['organization_id'] : null,
            'tags'             => array_key_exists('tags', $row) ? $row['tags'] : null,
            'cnr_no'           => array_key_exists('cnr_no', $row) ? $row['cnr_no'] : null,
            'remarks'          => array_key_exists('remarks', $row) ? $row['remarks'] : null,
            'notes_description'=> array_key_exists('notes_description', $row) ? $row['notes_description'] : null,
            'court_room_no'    => array_key_exists('court_room_no', $row) ? $row['court_room_no'] : null,
            'judge_name'       => array_key_exists('judge_name', $row) ? $row['judge_name'] : null,
            'state_id'         => array_key_exists('state_id', $row) ? $row['state_id'] : null,
            'next_date'        => array_key_exists('next_date', $row) ? $row['next_date'] : null,
            'district_id'      => array_key_exists('district_id', $row) ? $row['district_id'] : null,
            'tabs'             => array_key_exists('tabs', $row) ? $row['tabs'] : null,
            'court_bench'      => array_key_exists('court_bench', $row) ? $row['court_bench'] : null,
            'case_year'        => array_key_exists('case_year', $row) ? $row['case_year'] : null,
            'case_category'    => array_key_exists('case_category', $row) ? $row['case_category'] : null,
            'client_id'        => array_key_exists('client_id', $row) ? $row['client_id'] : null,
            'decided_toggle'   => array_key_exists('decided_toggle', $row) ? $row['decided_toggle'] : null,
            'abbondend_toggle' => array_key_exists('abbondend_toggle', $row) ? $row['abbondend_toggle'] : null,
            'receiving_date'   => array_key_exists('receiving_date', $row) ? $row['receiving_date'] : null,
            'filling_date'     => array_key_exists('filling_date', $row) ? $row['filling_date'] : null,
            'hearing_date'     => array_key_exists('hearing_date', $row) ? $row['hearing_date'] : null,
        ]);
    }
}
