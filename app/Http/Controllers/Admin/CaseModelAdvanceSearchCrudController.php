<?php

namespace App\Http\Controllers\Admin;

use App\Models\Client;
use App\Models\RefTag;
use App\Models\ApiState;
use App\Models\Petitioner;
use App\Models\Respondent;
use App\Models\ApiDistrict;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Models\PetitionerAdvocate;
use App\Models\RespondentAdvocate;
use Illuminate\Support\Facades\DB;
use Backpack\CRUD\app\Library\Widget;
use App\Http\Requests\CaseModelRequest;
use Backpack\CRUD\app\Library\CrudPanel\Traits\Input;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class CaseModelCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CaseModelAdvanceSearchCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation {
        store as traitStore;
    }
    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\CaseModel::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/case-advance-search');
        CRUD::setEntityNameStrings('advance search', 'advance search');

        CRUD::enableExportButtons();

        $this->crud->allowAccessOnlyTo('list');
    }


    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        if (!backpack_user()->hasRole('Super admin') && !backpack_user()->can('Advance Search'))
        $this->crud->denyAccess('list');

        // $this->crud->removeAllButtons();
        $this->crud->disableResponsiveTable();

        CRUD::addColumns([
            [
                'name' => 'previous_date',
                'type' => 'date',
                'format' => 'DD-MM-YYYY',
            ],
            [
                'name' => 'cnr_no',
                'type' => 'textarea',
            ],
            [
                'name' => 'custom4',
                'type' => 'textarea',
                'label' => 'Case number',
                'value' => function ($case) {
                    $str = sprintf("%s %s/%s", $case->case_category, $case->case_no, $case->case_year);
                    return $str;
                }
            ],

            [
                'name' => 'custom',
                'label' => 'Parties Details',
                'type' => 'custom_html',
                'value' => function ($case) {
                    $petitioner = DB::table('petitioners')->where('case_id', $case->id)->first()->petitioner ?? '';
                    $respondent = DB::table('respondents')->where('case_id', $case->id)->first()->respondent ?? '';

                    $str = sprintf('
                    <a href="#" class=" popover-demo mb-2" data-toggle="popover" title="Petitioners" data-content="%s">
                        %s
                    </a><strong> VS </strong>
                    <br>
                    <a href="#" class=" popover-demo mb-2" data-toggle="popover" title="Respondents" data-content="%s">
                        %s
                    </a>', ...[
                        $petitioner,
                        $petitioner ? wordwrap($petitioner, 30, '<br>', true) : '----',
                        $respondent,
                        $respondent ? wordwrap($respondent, 30, '<br>', true) : '----',
                    ]);


                    return $str;
                },
                'escaped' => false,
                // 'wrapper' => [
                //     'element' => 'div',
                //     'style' => 'width: 300px;word-wrap: break-word;word-break: break-all;'
                // ],
            ],
            [
                'name' => 'custom5',
                'type' => 'textarea',
                'label' => "Petitioner's Advocates",
                'value' => function ($case) {
                    $petitioner_advocate = DB::table('petitioner_advocates')->where('case_id', $case->id)->first()->petitioner_advocate ?? '';
                    return $petitioner_advocate;
                }
            ],
            [
                'name' => 'custom6',
                'type' => 'textarea',
                'label' => "Respondent's Advocates",
                'value' => function ($case) {
                    $respondent_advocate = DB::table('respondent_advocates')->where('case_id', $case->id)->first()->respondent_advocate ?? '';
                    return $respondent_advocate;
                }
            ],
            [
                'name' => 'court_bench',
                'type' => 'textarea',
                'label' => 'Court'
            ],


            [
                'name' => 'judge_name',
                'type' => 'textarea',
            ],

            [
                'name' => 'case_stage',
                'type' => 'textarea',
                'label' => 'Stage'

            ],
            [
                'name' => 'sr_no_in_court',
                'type' => 'textarea',
            ],
            [
                'name' => 'next_date',
                'type' => 'date',
                'format' => 'DD-MM-YYYY',
            ],
            [
                'name' => 'brief_for',
                'type' => 'textarea',
            ],
            [
                'name' => 'court_room_no',
                'type' => 'textarea',
                'label' => 'Room No.'
            ],
            [
                'name' => 'Brief_no',
                'type' => 'textarea',
                'label' => 'Brief No.',
            ],
            [
                'name' => 'custom1',
                'type' => 'textarea',
                'label' => 'Organization',
                'value' => function ($case) {
                    $organization = DB::table('organizations')->where('id', $case->organization_id)->first()->organization_name ?? '';

                    return $organization;
                }
            ],
            [
                'name' => 'custom7',
                'type' => 'textarea',
                'label' => 'Tags',
                'value' => function ($case) {
                    $organization = DB::table('ref_tags')->where('id', $case->tags)->first()->name ?? '';

                    return $organization;
                }
            ],
            [
                'name' => 'remarks',
                'type' => 'textarea',
            ],
            [
                'name' => 'custom2',
                'type' => 'textarea',
                'label' => 'State',
                'value' => function ($case) {
                    $organization = DB::table('api_states')->where('id', $case->state_id)->first()->name ?? '';

                    return $organization;
                }
            ],
            [
                'name' => 'custom3',
                'type' => 'textarea',
                'label' => 'District',
                'value' => function ($case) {
                    $organization = DB::table('api_districts')->where('id', $case->district_id)->first()->name ?? '';

                    return $organization;
                }
            ],
            [
                'name' => 'custom8',
                'type' => 'textarea',
                'label' => 'Individual',
                'value' => function ($case) {
                    $organization = DB::table('clients')->where('id', $case->client_id)->first()->name ?? '';

                    return $organization;
                }
            ],
            [
                'name' => 'created_at',
                'type' => 'date',
                'label' => 'Registered On',
            ],
        ]);

        /**
         * Columns can be defined using the fluent syntax:
         * - CRUD::column('price')->type('number');
         */


        Widget::add([
            'type' => 'advance_filters',
            'wrapper' => ['class' => 'mt-3 col-sm-12'],
        ])->to('before_content');

        $this->crud->addFilter(
            [
                'name'  => 'previous_date',
                'type'  => 'date_range',
                'label' => 'Previous Date'
            ],
            false,
            function ($value) {
                $dates = json_decode($value);
                $this->crud->addClause('where', 'previous_date', '>=', $dates->from);
                $this->crud->addClause('where', 'previous_date', '<=', $dates->to . ' 23:59:59');
            }
        );
        $this->crud->addFilter(
            [
                'name'  => 'case_year',
                'type'  => 'text',
                'label' => 'Case year'
            ],
            false,
            function ($value) {
                $this->crud->addClause('whereYear', 'created_at', $value);
            }
        );
        $this->crud->addFilter(
            [
                'name'  => 'next_date',
                'type'  => 'date_range',
                'label' => 'Next Date'
            ],
            false,
            function ($value) {
                $dates = json_decode($value);
                $this->crud->addClause('where', 'next_date', '>=', $dates->from);
                $this->crud->addClause('where', 'next_date', '<=', $dates->to . ' 23:59:59');
            }
        );

        $this->crud->addFilter(
            [
                'name'  => 'cnr_no',
                'type'  => 'text',
                'label' => 'CNR No.'
            ],
            false,
            function ($value) {
                $this->crud->addClause('where', 'id', $value);
            }
        );
        $this->crud->addFilter(
            [
                'name'  => 'case_type',
                'type'  => 'text',
                'label' => 'Category'
            ],
            false,
            function ($value) {
                $case = \App\Models\CaseModel::find($value);
                if ($case) {
                    $case_category = $case->case_category;
                    $this->crud->addClause('where', 'case_category', 'like', "%$case_category%");
                }
            }
        );
        $this->crud->addFilter(
            [
                'name'  => 'case_no',
                'type'  => 'text',
                'label' => 'Case No'
            ],
            false,
            function ($value) {
                $case = \App\Models\CaseModel::find($value);
                if ($case) {
                    $case_no = $case->case_no;
                    $this->crud->addClause('where', 'case_no', 'like', "%$case_no%");
                }
            }
        );

        $this->crud->addFilter(
            [
                'name'  => 'brief_no',
                'type'  => 'text',
                'label' => 'Brief No.'
            ],
            false,
            function ($value) {
                $case = \App\Models\CaseModel::find($value);
                if ($case) {
                    $brief_no = $case->brief_no;
                    $this->crud->addClause('where', 'brief_no', 'like', "%$brief_no%");
                }
            }
        );
        $this->crud->addFilter(
            [
                'name'  => 'court_bench',
                'type'  => 'text',
                'label' => 'Court Bench'
            ],
            false,
            function ($value) {
                $case = \App\Models\CaseModel::find($value);
                if ($case) {
                    $court_bench = $case->court_bench;
                    $this->crud->addClause('where', 'court_bench', 'like', "%$court_bench%");
                }
            }
        );
        $this->crud->addFilter(
            [
                'name'  => 'judge_name',
                'type'  => 'text',
                'label' => 'Judge Name'
            ],
            false,
            function ($value) {
                $case = \App\Models\CaseModel::find($value);
                if ($case) {
                    $judge_name = $case->judge_name;
                    $this->crud->addClause('where', 'judge_name', 'like', "%$judge_name%");
                }
            }
        );
        $this->crud->addFilter(
            [
                'name'  => 'case_stage',
                'type'  => 'text',
                'label' => 'Stage'
            ],
            false,
            function ($value) {
                $case = \App\Models\CaseModel::find($value);
                if ($case) {
                    $case_stage = $case->case_stage;
                    $this->crud->addClause('where', 'case_stage', 'like', "%$case_stage%");
                }
            }
        );
        $this->crud->addFilter(
            [
                'name'  => 'petitioner',
                'type'  => 'text',
                'label' => 'Parties Details'
            ],
            false,
            function ($value) {
                $petitioner = \App\Models\Petitioner::find($value)->petitioner;
                $case_ids = \App\Models\Petitioner::where('petitioner', 'like', "%$petitioner%")->pluck('case_id')->toArray();
                if ($case_ids)
                    $this->crud->addClause('whereIn', 'id', $case_ids);
            }
        );

        $this->crud->addFilter(
            [
                'name'  => 'respondent',
                'type'  => 'text',
                'label' => 'Parties Details'
            ],
            false,
            function ($value) {
                $respondent = \App\Models\Respondent::find($value)->respondent;
                $case_ids = \App\Models\Respondent::where('respondent', 'like', "%$respondent%")->pluck('case_id')->toArray();
                if ($case_ids)
                    $this->crud->addClause('whereIn', 'id', $case_ids);
            }
        );
        $this->crud->addFilter(
            [
                'name'  => 'petitioner_advocate',
                'type'  => 'text',
                'label' => 'Parties Details'
            ],
            false,
            function ($value) {
                $petitioner_advocate = \App\Models\PetitionerAdvocate::find($value)->petitioner_advocate;
                $case_ids = \App\Models\PetitionerAdvocate::where('petitioner_advocate', 'like', "%$petitioner_advocate%")->pluck('case_id')->toArray();
                if ($case_ids)
                    $this->crud->addClause('whereIn', 'id', $case_ids);
            }
        );
        $this->crud->addFilter(
            [
                'name'  => 'respondent_advocate',
                'type'  => 'text',
                'label' => 'Parties Details'
            ],
            false,
            function ($value) {
                $respondent_advocate = \App\Models\RespondentAdvocate::find($value)->respondent_advocate;
                $case_ids = \App\Models\RespondentAdvocate::where('respondent_advocate', 'like', "%$respondent_advocate%")->pluck('case_id')->toArray();
                if ($case_ids)
                    $this->crud->addClause('whereIn', 'id', $case_ids);
            }
        );
        $this->crud->addFilter(
            [
                'name'  => 'organization',
                'type'  => 'text',
                'label' => 'Organization'
            ],
            false,
            function ($value) {
                $organization_name = \App\Models\Organization::find($value)->organization_name;
                $organization_ids = \App\Models\Organization::where('organization_name', 'like', "%$organization_name%")->pluck('id')->toArray();
                $this->crud->addClause('whereIn', 'organization_id', $organization_ids);
            }
        );
        $this->crud->addFilter(
            [
                'name'  => 'reference',
                'type'  => 'text',
                'label' => 'Reference'
            ],
            false,
            function ($value) {
                $name = \App\Models\RefTag::find($value)->name;
                $ref_ids = \App\Models\RefTag::where('name', 'like', "%$name%")->pluck('id')->toArray();
                $this->crud->addClause('whereIn', 'tags', $ref_ids);
            }
        );
        $this->crud->addFilter(
            [
                'name'  => 'client',
                'type'  => 'text',
                'label' => 'Client'
            ],
            false,
            function ($value) {
                $name = \App\Models\Client::find($value)->name;
                $client_ids = \App\Models\Client::where('name', 'like', "%$name%")->pluck('id')->toArray();
                $this->crud->addClause('whereIn', 'client_id', $client_ids);
            }
        );

        $this->crud->addFilter(
            [
                'name'  => 'case_label',
                'type'  => 'text',
                'label' => 'Case label'
            ],
            false,
            function ($value) {
                $label = \App\Models\CaseLabel::find($value)->label;
                $case_label_ids = \App\Models\CaseLabel::where('label', 'like', "%$label%")->pluck('id')->toArray();
                $this->crud->addClause('whereHas', 'labels', function($query) use ($case_label_ids) {
                    $query->whereIn('case_label_id', $case_label_ids);
                });
            }
        );
        $this->crud->addFilter(
            [
                'name'  => 'state',
                'type'  => 'text',
                'label' => 'State'
            ],
            false,
            function ($value) {
                $this->crud->addClause('where', 'state_id', $value);
            }
        );
        $this->crud->addFilter(
            [
                'name'  => 'district',
                'type'  => 'text',
                'label' => 'District'
            ],
            false,
            function ($value) {
                $this->crud->addClause('where', 'district_id', $value);
            }
        );
        $this->crud->addFilter(
            [
                'name'  => 'registration_date',
                'type'  => 'date_range',
                'label' => 'Next Date'
            ],
            false,
            function ($value) {
                $dates = json_decode($value);
                $this->crud->addClause('where', 'created_at', '>=', $dates->from);
                $this->crud->addClause('where', 'created_at', '<=', $dates->to . ' 23:59:59');
            }
        );
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        $this->addFields();
        CRUD::setValidation(CaseModelRequest::class);
        /**
         * Fields can be defined using the fluent syntax:
         * - CRUD::field('price')->type('number');
         */
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    private function addFields()
    {
        CRUD::addFields([
            [
                'name' => 'cnr_no',
                'type' => 'text',
                'label' => 'CNR No.',
            ],
            [
                'name' => 'Brief_no',
                'type' => 'text',
                'label' => 'Brief No.',
            ],
            [
                'name' => 'state_id',
                'label' => 'State',
                'type' => 'select2_from_array',
                'options' => ApiState::pluck('name', 'id')->toArray(),
                'allows_null' => false,
                'attributes' => [
                    'id' => 'state-dropdown',
                ],
            ],
            [
                'name' => 'district_id',
                'label' => 'District',
                'type' => 'select2_from_array',
                'options' => [],
                'allows_null' => false,
                'attributes' => [
                    'id' => 'district-dropdown',
                ],
            ],
            [
                'name' => 'tabs',
                'type' => 'select_from_array',
                'label' => 'Tabs',
                'options' => [
                    'District Courts and Tribunals' => 'District Courts and Tribunals',
                    'High Court' => 'High Court',
                ],
                'required' => true,
            ],
            [
                'name' => 'court_bench',
                'type' => 'text',
                'label' => 'Court/Bench',
            ],
            [
                'name' => 'case_category',
                'type' => 'text',
                'label' => 'Case Type',
            ],
            [
                'name' => 'case_no',
                'type' => 'text',
                'label' => 'Case No.',
            ],
            [
                'name' => 'case_year',
                'type' => 'select2_from_array',
                'label' => 'Case Year',
                'options' => array_combine(range(2000, 2050), range(2000, 2050)), // Generate options from 2000 to 2050
                'default' => date('Y'),
            ],
            [
                'name' => 'previous_date',
                'type' => 'date',
                'label' => 'Previous Date',
                'format' => 'DD-MM-YYYY'
            ],
            [
                'name' => 'next_date',
                'type' => 'date',
                'label' => 'Next Date',
                'format' => 'DD-MM-YYYY'
            ],
            [
                'name' => 'petitioners1',
                'type' => 'text',
                'fake'     => true,
                'label' => 'Petitioner',
            ],
            [
                'name' => 'petitioner_advocates1',
                'type' => 'text',
                'fake'     => true,
                'label' => "Petitioner's Advocate",
            ],
            [
                'name' => 'add_new_petitioner',
                'type' => 'add_new_field',
                'fake'     => true,
                'value' => 'New Petitioner',
            ],
            [
                'name' => 'petitioners2',
                'type' => 'text',
                'fake'     => true,
                'attributes' => [
                    'placeholder' => 'Petitioners',
                    'style' => 'display:none;',
                ],
            ],
            [
                'name' => 'petitioner_advocates2',
                'type' => 'text',
                'fake'     => true,
                'attributes' => [
                    'placeholder' => "Petitioner's Advocate",
                    'style' => 'display:none;',
                ],
            ],
            [
                'name' => 'respondents1',
                'type' => 'text',
                'fake'     => true,
                'label' => 'Respondents',
            ],
            [
                'name' => 'respondent_advocates1',
                'type' => 'text',
                'fake'     => true,
                'label' => "Respondent's Advocate",
            ],
            [
                'name' => 'add_new_respondent',
                'type' => 'add_new_field',
                'fake'     => true,
                'value' => 'New Respondent',
            ],
            [
                'name' => 'respondents2',
                'type' => 'text',
                'fake'     => true,
                'attributes' => [
                    'placeholder' => 'Respondents',
                    'style' => 'display:none;',
                ],
            ],
            [
                'name' => 'respondent_advocates2',
                'type' => 'text',
                'fake'     => true,
                'attributes' => [
                    'placeholder' => "Respondent's Advocates",
                    'style' => 'display:none;',
                ],
            ],
            [
                'name' => 'judge_name',
                'type' => 'text',
                'label' => 'Judge Name',
            ],
            [
                'name' => 'court_room_no',
                'type' => 'text',
                'label' => 'Court Room No',
            ],
            [
                'name' => 'case_stage',
                'type' => 'text',
                'label' => 'Case Stage',
            ],
            [
                'name' => 'brief_for',
                'type' => 'text',
                'label' => 'Brief For',
            ],
            [
                'name' => 'sr_no_in_court',
                'type' => 'text',
                'label' => 'Sr No In Court',
            ],
            [
                'name' => 'organization_id',
                'type' => 'select2_from_array',
                'label' => 'Organization',
                'options' => array_merge(array(0 => "Select Organization"), Organization::pluck('organization_name', 'id')->toArray()),
                'allows_null' => false,
                'attributes' => [
                    'id' => 'organization-dropdown',
                ],
            ],
            [
                'name' => 'add_new_organization',
                'type' => 'add_new_field',
                'fake'     => true,
                'value' => 'New Organization',
            ],
            [
                'name' => 'organization_name',
                'type' => 'text',
                'fake'     => true,
                'attributes' => [
                    'placeholder' => 'Organization Name',
                    'style' => 'display:none;',
                ],
            ],
            [
                'name' => 'organization_address',
                'type' => 'text',
                'fake'     => true,
                'attributes' => [
                    'placeholder' => 'Organization Address',
                    'style' => 'display:none;',
                ],
            ],
            [
                'name' => 'organization_authorized_person_name',
                'type' => 'text',
                'fake'     => true,
                'attributes' => [
                    'placeholder' => 'Authorized Person Name',
                    'style' => 'display:none;',
                ],
            ],
            [
                'name' => 'organization_email',
                'type' => 'text',
                'fake'     => true,
                'attributes' => [
                    'placeholder' => 'Organization Email',
                    'style' => 'display:none;',
                ],
            ],
            [
                'name' => 'organization_contact',
                'type' => 'text',
                'fake'     => true,
                'attributes' => [
                    'placeholder' => 'Organization Contact',
                    'style' => 'display:none;',
                ],
            ],
            [
                'name' => 'tags',
                'type' => 'select2_from_array',
                'label' => 'Tags/Reference',
                'options' => array_merge(array(0 => "Select Tags"), RefTag::pluck('name', 'id')->toArray()),
                'allows_null' => false,
                'attributes' => [
                    'id' => 'tags-dropdown',
                ],
            ],
            [
                'name' => 'add_new_tags',
                'type' => 'add_new_field',
                'fake'     => true,
                'value' => 'New Tags',
            ],
            [
                'name' => 'reference_name',
                'type' => 'text',
                'fake'     => true,
                'attributes' => [
                    'placeholder' => 'Reference Name',
                    'style' => 'display:none;',
                ],
            ],
            [
                'name' => 'reference_contact',
                'type' => 'text',
                'fake'     => true,
                'attributes' => [
                    'placeholder' => 'Reference Contact',
                    'style' => 'display:none;',
                ],
            ],
            [
                'name' => 'reference_email',
                'type' => 'text',
                'fake'     => true,
                'attributes' => [
                    'placeholder' => 'Reference Email',
                    'style' => 'display:none;',
                ],
            ],
            [
                'name' => 'reference_address',
                'type' => 'text',
                'fake'     => true,
                'attributes' => [
                    'placeholder' => 'Reference Address',
                    'style' => 'display:none;',
                ],
            ],
            [
                'name' => 'client_id',
                'type' => 'select2_from_array',
                'label' => 'Client Name',
                'options' => array_merge(array(0 => "Select Client"), Client::pluck('name', 'id')->toArray()),
                'allows_null' => false,
                'attributes' => [
                    'id' => 'client-dropdown',
                ],
            ],
            [
                'name' => 'add_new_client',
                'type' => 'add_new_field',
                'fake'     => true,
                'value' => 'New Client',
            ],
            [
                'name' => 'client_name',
                'type' => 'text',
                'fake'     => true,
                'attributes' => [
                    'placeholder' => 'Client Name',
                    'style' => 'display:none;',
                ],
            ],
            [
                'name' => 'client_contact',
                'type' => 'text',
                'fake'     => true,
                'attributes' => [
                    'placeholder' => 'Client Contact',
                    'style' => 'display:none;',
                ],
            ],
            [
                'name' => 'client_email',
                'type' => 'text',
                'fake'     => true,
                'attributes' => [
                    'placeholder' => 'Client Email',
                    'style' => 'display:none;',
                ],
            ],
            [
                'name' => 'client_address',
                'type' => 'text',
                'fake'     => true,
                'attributes' => [
                    'placeholder' => 'Client Address',
                    'style' => 'display:none;',
                ],
            ],
            [
                'name' => 'client_gender',
                'type' => 'text',
                'fake'     => true,
                'attributes' => [
                    'placeholder' => 'Client Gender',
                    'style' => 'display:none;',
                ],
            ],
            [
                'name' => 'client_desc',
                'type' => 'text',
                'fake'     => true,
                'attributes' => [
                    'placeholder' => 'Client Description',
                    'style' => 'display:none;',
                ],
            ],
            [
                'name' => 'decided_toggle',
                'type' => 'checkbox',
                'label' => 'Decided Toggle',
            ],
            [
                'name' => 'abbondend_toggle',
                'type' => 'checkbox',
                'label' => 'Abondoned Toggle',
            ],
            [
                'name' => 'remarks',
                'type' => 'textarea',
                'label' => 'Remarks',
            ],
            [
                'name' => 'notes_description',
                'type' => 'summernote',
                'label' => 'Notes',
                'options' => [
                    'toolbar' => [
                        ['style', ['style']],
                        ['font', ['bold', 'italic', 'underline', 'clear']],
                        ['fontname', ['fontname']],
                        ['color', ['color']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['table', ['table']],
                        ['insert', ['link', 'picture', 'video']],
                        ['view', ['codeview', 'help']],
                        // Add minimize button in the toolbar
                        ['minimize', ['minimize']]
                    ],
                ],
            ],
            [
                'name' => 'uploads',
                'type' => 'upload_multiple',
                'label' => 'Uploads',
                'fake'     => true,
            ],
        ]);
    }

    public static function fetch_case_by_case_no(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => 'Your case by case number data here'
        ]);
    }

    public function getDistricts(Request $request)
    {
        $districts = ApiDistrict::where('state_value', $request->state_id)->pluck('name', 'id');
        return response()->json($districts);
    }

    public function store()
    {
        $this->crud->setValidation([
            'tabs' => 'required',
        ]);
        $uploads = $this->crud->getRequest()->get('uploads');
        $petitioner = $this->crud->getRequest()->get('petitioners1');
        $petitioner_advocate = $this->crud->getRequest()->get('petitioner_advocates1');
        $respondent = $this->crud->getRequest()->get('respondents1');
        $respondent_advocate = $this->crud->getRequest()->get('respondent_advocates1');
        $petitioner2 = $this->crud->getRequest()->get('petitioners2');
        $petitioner_advocate2 = $this->crud->getRequest()->get('petitioner_advocates2');
        $respondent2 = $this->crud->getRequest()->get('respondents2');
        $respondent_advocate2 = $this->crud->getRequest()->get('respondent_advocates2');

        $response = $this->traitStore();

        $item = $this->crud->entry;

        if (! empty($petitioner)) {
            Petitioner::create([
                'petitioner' => $petitioner, // Assuming you have a field petitionerd 'petitioner_name' in the request
                'case_id' => $item->id,
            ]);
        }
        if (! empty($petitioner_advocate)) {
            PetitionerAdvocate::create([
                'petitioner_advocate' => $petitioner_advocate, // Assuming you have a field named 'petitioner_name' in the request
                'case_id' => $item->id,
            ]);
        }
        if (! empty($respondent)) {
            Respondent::create([
                'respondent' => $respondent, // Assuming you have a field petitionerd 'petitioner_name' in the request
                'case_id' => $item->id,
            ]);
        }
        if (! empty($respondent_advocate)) {
            RespondentAdvocate::create([
                'respondent_advocate' => $respondent_advocate, // Assuming you have a field named 'petitioner_name' in the request
                'case_id' => $item->id,
            ]);
        }
        if (! empty($petitioner2)) {
            Petitioner::create([
                'petitioner' => $petitioner2, // Assuming you have a field petitionerd 'petitioner_name' in the request
                'case_id' => $item->id,
            ]);
        }
        if (! empty($petitioner_advocate2)) {
            PetitionerAdvocate::create([
                'petitioner_advocate' => $petitioner_advocate2, // Assuming you have a field named 'petitioner_name' in the request
                'case_id' => $item->id,
            ]);
        }
        if (! empty($respondent22)) {
            Respondent::create([
                'respondent' => $respondent2, // Assuming you have a field petitionerd 'petitioner_name' in the request
                'case_id' => $item->id,
            ]);
        }
        if (! empty($respondent_advocate2)) {
            RespondentAdvocate::create([
                'respondent_advocate' => $respondent_advocate2, // Assuming you have a field named 'petitioner_name' in the request
                'case_id' => $item->id,
            ]);
        }

        $organization_name = $this->crud->getRequest()->get('organization_name');
        $organization_authorized_person_name = $this->crud->getRequest()->get('organization_authorized_person_name');
        $organization_address = $this->crud->getRequest()->get('organization_address');
        $organization_contact = $this->crud->getRequest()->get('organization_contact');
        $organization_email = $this->crud->getRequest()->get('organization_email');

        if (! empty($organization_name) && ! empty($organization_authorized_person_name)) {
            $org = Organization::create([
                'organization_name' => $organization_name, // Assuming you have a field named 'petitioner_name' in the request
                'representator' => $organization_authorized_person_name, // Assuming you have a field named 'petitioner_name' in the request
                'contact' => $organization_contact, // Assuming you have a field named 'petitioner_name' in the request
                'email' => $organization_email, // Assuming you have a field named 'petitioner_name' in the request
                'address' => $organization_address, // Assuming you have a field named 'petitioner_name' in the request
                'adv_id' => auth()->user()->id, // Assuming you have a field named 'petitioner_name' in the request
            ]);
            $item->organization_id = $org->id;
        }

        $client_name = $this->crud->getRequest()->get('client_name');
        $client_gender = $this->crud->getRequest()->get('client_gender');
        $client_address = $this->crud->getRequest()->get('client_address');
        $client_contact = $this->crud->getRequest()->get('client_contact');
        $client_email = $this->crud->getRequest()->get('client_email');
        $client_desc = $this->crud->getRequest()->get('client_desc');

        if (! empty($client_name) && ! empty($client_email)) {
            $org = Client::create([
                'name' => $client_name, // Assuming you have a field named 'petitioner_name' in the request
                'email' => $client_email, // Assuming you have a field named 'petitioner_name' in the request
                'mobile' => $client_contact, // Assuming you have a field named 'petitioner_name' in the request
                'gender' => $client_gender, // Assuming you have a field named 'petitioner_name' in the request
                'address' => $client_address, // Assuming you have a field named 'petitioner_name' in the request
                'description' => $client_desc, // Assuming you have a field named 'petitioner_name' in the request
                'user_id' => auth()->user()->id, // Assuming you have a field named 'petitioner_name' in the request
            ]);
            $item->client_id = $org->id;
        }

        $reference_name = $this->crud->getRequest()->get('reference_name');
        $reference_address = $this->crud->getRequest()->get('reference_address');
        $reference_contact = $this->crud->getRequest()->get('reference_contact');
        $reference_email = $this->crud->getRequest()->get('reference_email');
        if (! empty($reference_name) && ! empty($reference_email)) {
            $org = Client::create([
                'name' => $reference_name, // Assuming you have a field named 'petitioner_name' in the request
                'email' => $reference_email, // Assuming you have a field named 'petitioner_name' in the request
                'contact' => $reference_contact, // Assuming you have a field named 'petitioner_name' in the request
                'address' => $reference_address, // Assuming you have a field named 'petitioner_name' in the request
                'adv_id' => auth()->user()->id, // Assuming you have a field named 'petitioner_name' in the request
            ]);
            $item->tags = $org->id;
        }

        $item->save();

        if ($response->isSuccessful()) {
            return backpack_url('case-model-active');
        }
        // do something after save
        return $response;
    }

    public function setupShowOperation()
    {

        $this->crud->setOperationSetting('tabsEnabled', true);
        CRUD::column([
            'name' => 'cnr_no',
            'tab' => 'Case Details',
            'label' => 'CNR Number',
        ]);
        CRUD::column([
            'name' => 'Brief_no',
            'tab' => 'Case Details',
            'label' => 'Brief Number',
        ]);
        CRUD::column([
            'name' => 'case_category',
            'tab' => 'Case Details',
            'label' => 'Case Type',
        ]);
        CRUD::column([
            'name' => 'case_no',
            'tab' => 'Case Details',
            'label' => 'Case Number',
        ]);
        CRUD::column([
            'name' => 'case_year',
            'tab' => 'Case Details',
            'label' => 'Case Year',
        ]);
        CRUD::column([
            'name' => 'previous_date',
            'tab' => 'Case Details',
            'label' => 'Previous Date',
        ]);
        CRUD::column([
            'name' => 'next_date',
            'tab' => 'Case Details',
            'label' => 'Next Date',
        ]);
        CRUD::column([
            'name' => 'case_stage',
            'tab' => 'Case Details',
            'label' => 'Stage',
        ]);
        CRUD::column([
            'name' => 'brief_for',
            'tab' => 'Case Details',
            'label' => 'Brief For',
        ]);
        CRUD::column([
            'name' => 'state_name',
            'tab' => 'Case Details',
            'label' => 'State',
            'value' => function ($entry) {
                return $entry->state->name;
            }
        ]);
        CRUD::column([
            'name' => 'district_name',
            'tab' => 'Case Details',
            'label' => 'District',
            'value' => function ($entry) {
                return $entry->district->name;
            }
        ]);
        CRUD::column([
            'name' => 'custom',
            'tab' => 'Case Details',
            'label' => 'Petitioner/Additional',
            'value' => function ($case) {
                return $case->petitioners1 . $case->petitioners2;
            }
        ]);
        CRUD::column([
            'name' => 'custom1',
            'tab' => 'Case Details',
            'label' => 'Their Advocates',
            'value' => function ($case) {
                return $case->petitioner_advocates1 . $case->petitioner_advocates2;
            }
        ]);
        CRUD::column([
            'name' => 'custom2',
            'tab' => 'Case Details',
            'label' => 'Respondent/Additional',
            'value' => function ($case) {
                return $case->respondents1 . $case->respondents2;
            }
        ]);
        CRUD::column([
            'name' => 'custom3',
            'tab' => 'Case Details',
            'label' => 'Their Advocates',
            'value' => function ($case) {
                return $case->respondent_advocates1 . $case->respondent_advocates2;
            }
        ]);
        CRUD::column([
            'name' => 'court_bench',
            'tab' => 'Case Details',
            'label' => 'Court',
            'type' => 'textarea',
        ]);
        CRUD::column([
            'name' => 'judge_name',
            'tab' => 'Case Details',
            'label' => 'Judge Name',
        ]);
        CRUD::column([
            'name' => 'court_room_no',
            'tab' => 'Case Details',
            'label' => 'Court Room No',
        ]);
        CRUD::column([
            'name' => 'custom4',
            'tab' => 'Case Details',
            'label' => 'Client name',
            'value' => function ($case) {
                return $case->client;
            }
        ]);
        CRUD::column([
            'name' => 'custom5',
            'tab' => 'Case Details',
            'label' => 'Tags',
            'value' => function ($case) {
                return RefTag::find($case->tags)->first()->name;
            }
        ]);
        CRUD::column([
            'name' => 'custom6',
            'tab' => 'Case Details',
            'label' => 'Organization',
            'value' => function ($case) {
                return $case->organization->organization_name;
            }
        ]);
        CRUD::column([
            'name' => 'remarks',
            'tab' => 'Case Details',
            'label' => 'Remarks',

        ]);
        CRUD::column([
            'name' => 'created_at',
            'tab' => 'Case Details',
            'label' => 'Registered on Casewise',

        ]);


        // or using the fluent syntax
        CRUD::column('history')->type('textarea')->tab('Case History');
        CRUD::column('tabs')->tab('Worklist');
        CRUD::column('notes_description')->type('summernote')->tab('Notes');
        CRUD::column('orders')->type('textarea')->tab('Orders/Evidence');
    }
}
