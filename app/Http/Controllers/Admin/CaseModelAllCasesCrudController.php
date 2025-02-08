<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Client;
use App\Models\RefTag;
use App\Models\ApiState;
use App\Models\Worklist;
use App\Models\CaseLabel;
use App\Models\Petitioner;
use App\Models\Respondent;
use App\Models\ApiDistrict;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Models\PetitionerAdvocate;
use App\Models\RespondentAdvocate;
use Illuminate\Support\Facades\DB;
use App\Http\Operations\FetchOperation;
use App\Http\Requests\CaseModelRequest;
use Backpack\CRUD\app\Library\CrudPanel\Traits\Input;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class CaseModelCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CaseModelAllCasesCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation{ store as traitStore; }
    use FetchOperation;
    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\CaseModel::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/case-model-all-cases');
        CRUD::setEntityNameStrings('all case', 'all cases');

        CRUD::enableExportButtons();
        $this->crud->setTitle('Add Case', 'create');

        $this->crud->orderBy('id', 'desc');
        $this->crud->allowAccessOnlyTo(['list','create','delete','update','show','quick']);
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        if (!backpack_user()->hasRole('Super admin') && !backpack_user()->can('All Cases'))
        $this->crud->denyAccess('list');

        CRUD::enableBulkActions();

        if(!backpack_user()->hasRole('Super admin') && !backpack_user()->can('View Case')){
        CRUD::denyAccess('show');
        }else{
        CRUD::allowAccess('show');
        }

        if(!backpack_user()->hasRole('Super admin') && !backpack_user()->can('Delete Case')){
        CRUD::denyAccess('delete');
        }else{
        CRUD::allowAccess('delete');
        }

        CRUD::allowAccess('create');

        if(!backpack_user()->hasRole('Super admin') && !backpack_user()->can('Edit Case')){
        CRUD::denyAccess('update');
        }else{
            CRUD::allowAccess('update');
        }

        // $this->crud->removeAllButtons();
        $this->crud->disableResponsiveTable();
        CRUD::addColumn([
            'name' => "quick_action",
            'type' => "inline_preview",
            'label' => "Quick Action",
        ]);
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
                'name' => 'cwn',
                'type' => 'textarea',
            ],
            [
                'name' => 'custom4',
                'type' => 'textarea',
                'label' => 'Case number',
                'value' => function ( $case ) {
                    $str = sprintf( "%s %s/%s", $case->case_category, $case->case_no, $case->case_year );
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
                'label' => "Petitioner's Advocate",
                'value' => function ($case) {
                    $petitioner_advocate = DB::table('petitioner_advocates')->where('case_id', $case->id)->first()->petitioner_advocate ?? '';
                    return $petitioner_advocate;
                }
            ],
            [
                'name' => 'custom6',
                'type' => 'textarea',
                'label' => "Respondent's Advocate",
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
                'label' => 'Brief No.'
            ],
            [
                'name' => 'custom_rand',
                'type' => 'textarea',
                'label' => 'Assigned To',
                'value' => function ($case) {
                    $organization = DB::table('users')->where('id', $case->assigned_to)->first()->name ?? '';

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
                'label' => 'Reference',
                'value' => function ($case) {
                    $organization = DB::table('ref_tags')->where('id', $case->tags)->first()->name ?? '';

                    return $organization;
                }
            ],
            [
                'name' => 'case_labels',
                'type' => 'textarea',
                'label' => 'Case Labels',
                'value' => function ($case) {
                    $str = '';
                    if ( null !== $case->case_labels ){
                        $clabels = explode(',', $case->case_labels );
                        if ( ! empty( $clabels ) ) {
                            foreach( $clabels as $clabel ) {
                                $case_label = CaseLabel::find($clabel);
                                $str .=  $case_label->label . ', ';
                            }
                            return $str;
                        }
                    }
                    return $str;
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
                'name' => 'created_at',
                'type' => 'date',
                'label' => 'Registered On',
            ],
        ]);

        /**
         * Columns can be defined using the fluent syntax:
         * - CRUD::column('price')->type('number');
         */

        $this->crud->addFilter([
            'name'  => 'previous_date',
            'type'  => 'date_range',
            'label' => 'Previous Date'
        ],
            false,
            function ($value) {
                $dates = json_decode($value);
                $this->crud->addClause('where', 'previous_date', '>=', $dates->from);
                $this->crud->addClause('where', 'previous_date', '<=', $dates->to . ' 23:59:59');
            });
        $this->crud->addFilter([
                'name'  => 'cnr_no',
                'type'  => 'text',
                'label' => 'CNR No.'
            ],
            false,
            function ($value) {
                $this->crud->addClause('where', 'cnr_no', 'LIKE', "%$value%");
            }
        );
        $this->crud->addFilter([
                'name'  => 'cwn',
                'type'  => 'text',
                'label' => 'CWN'
            ],
            false,
            function ($value) {
                $this->crud->addClause('where', 'cwn', 'LIKE', "%$value%");
            }
        );


        $this->crud->addFilter([
            'name'  => 'custom4',
            'type'  => 'text',
            'label' => 'Case No'
        ],
            false,
            function ($value) {
                $this->crud->addClause('where', function($query) use ($value) {
                    $query->where('case_no', 'LIKE', "%$value%")
                          ->orWhere('case_category', 'LIKE', "%$value%")
                          ->orWhere('case_year', 'LIKE', "%$value%");
                });
            });
        $this->crud->addFilter([
            'name'  => 'custom',
            'type'  => 'text',
            'label' => 'Parties Details'
        ],
            false,
            function ($value) {
                $matching_case_ids = [];
                array_push($matching_case_ids, ...DB::table('petitioners')->where('petitioner', 'LIKE', "%$value%")->pluck('case_id')->toArray());
                array_push($matching_case_ids, ...DB::table('respondents')->where('respondent', 'LIKE', "%$value%")->pluck('case_id')->toArray());
                $this->crud->addClause('whereIn', 'id', $matching_case_ids);
            });
        $this->crud->addFilter([
            'name'  => 'custom5',
            'type'  => 'text',
            'label' => "Petitioner's Advocate"
        ],
            false,
            function ($value) {
                $matching_case_ids = [];
                array_push($matching_case_ids, ...DB::table('petitioner_advocates')->where('petitioner_advocate', 'LIKE', "%$value%")->pluck('case_id')->toArray());

                $this->crud->addClause('whereIn', 'id', $matching_case_ids);
            });
        $this->crud->addFilter([
            'name'  => 'custom6',
            'type'  => 'text',
            'label' => "Respondent's Advocate"
        ],
            false,
            function ($value) {
                $matching_case_ids = [];
                array_push($matching_case_ids, ...DB::table('respondent_advocates')->where('respondent_advocate', 'LIKE', "%$value%")->pluck('case_id')->toArray());

                $this->crud->addClause('whereIn', 'id', $matching_case_ids);
            });

        $this->crud->addFilter([
            'name'  => 'court_bench',
            'type'  => 'text',
            'label' => 'Court'
        ],
            false,
            function ($value) {
                $this->crud->addClause('where', 'court_bench', 'LIKE', "%$value%");
            });

        $this->crud->addFilter([
            'name'  => 'judge_name',
            'type'  => 'text',
            'label' => 'Judge Name'
        ],
            false,
            function ($value) {
                $this->crud->addClause('where', 'judge_name', 'LIKE', "%$value%");
            });

        $this->crud->addFilter([
            'name'  => 'case_stage',
            'type'  => 'text',
            'label' => 'Stage'
        ],
            false,
            function ($value) {
                $this->crud->addClause('where', 'case_stage', 'LIKE', "%$value%");
            });
        $this->crud->addFilter([
            'name'  => 'sr_no_in_court',
            'type'  => 'text',
            'label' => 'Sr No'
        ],
            false,
            function ($value) {
                $this->crud->addClause('where', 'sr_no_in_court', 'LIKE', "%$value%");
            });

            $this->crud->addFilter([
                'name'  => 'next_date',
                'type'  => 'date_range',
                'label' => 'Next Date'
            ],
                false,
                function ($value) {
                    $dates = json_decode($value);
                    $this->crud->addClause('where', 'next_date', '>=', $dates->from);
                    $this->crud->addClause('where', 'next_date', '<=', $dates->to . ' 23:59:59');
                });

            $this->crud->addFilter([
                'name'  => 'brief_for',
                'type'  => 'text',
                'label' => 'Brief For.'
            ],
                false,
                function ($value) {
                    $this->crud->addClause('where', 'brief_for', 'LIKE', "%$value%");
                });


        $this->crud->addFilter([
            'name'  => 'court_room_no',
            'type'  => 'text',
            'label' => 'Room No.'
        ],
            false,
            function ($value) {
                $this->crud->addClause('where', 'court_room_no', 'LIKE', "%$value%");
            });


        $this->crud->addFilter([
            'name'  => 'Brief_no',
            'type'  => 'text',
            'label' => 'Brief No.'
        ],
            false,
            function ($value) {
                $this->crud->addClause('where', 'Brief_no', 'LIKE', "%$value%");
            });

            $this->crud->addFilter([
                'name'  => 'custom_rand',
                'type'  => 'text',
                'label' => 'Assigned To'
                ],
                false,
                function ($value) {
                    $matching_case_ids = [];
                    array_push($matching_case_ids, ...DB::table('users')->where('name', 'LIKE', "%$value%")->pluck('id')->toArray());
                    $this->crud->addClause('whereIn', 'assigned_to', $matching_case_ids);
            });
            $this->crud->addFilter([
                'name'  => 'custom8',
                'type'  => 'text',
                'label' => 'Individual'
                ],
                false,
                function ($value) {
                    $matching_case_ids = [];
                    array_push($matching_case_ids, ...DB::table('clients')->where('name', 'LIKE', "%$value%")->pluck('id')->toArray());
                    $this->crud->addClause('whereIn', 'client_id', $matching_case_ids);
            });
        $this->crud->addFilter([
            'name'  => 'custom1',
            'type'  => 'text',
            'label' => 'Organization'
        ],
            false,
            function ($value) {
                $matching_case_ids = [];
                array_push($matching_case_ids, ...DB::table('organizations')->where('organization_name', 'LIKE', "%$value%")->pluck('id')->toArray());
                $this->crud->addClause('whereIn', 'organization_id', $matching_case_ids);
            });
        $this->crud->addFilter([
            'name'  => 'custom7',
            'type'  => 'text',
            'label' => 'Reference'
        ],
            false,
            function ($value) {
                $matching_case_ids = [];
                array_push($matching_case_ids, ...DB::table('ref_tags')->where('name', 'LIKE', "%$value%")->pluck('id')->toArray());
                $this->crud->addClause('whereIn', 'tags', $matching_case_ids);
            });
            $this->crud->addFilter([
                'name'  => 'custom_rand1',
                'type'  => 'text',
                'label' => 'Case Labels'
                ],
                    false,
                    function ($value) {
                        $matching_case_ids = [];
                        $matching_labels = [];
                        array_push($matching_labels, ...DB::table('case_labels')->where('label', 'LIKE', "%$value%")->pluck('id')->toArray());
                        array_push($matching_case_ids, ...DB::table('case_labels_case')->whereIn('case_label_id', $matching_labels)->pluck('case_id')->toArray());
                        $this->crud->addClause('whereIn', 'case_labels', $matching_case_ids);
            });

            $this->crud->addFilter([
                'name'  => 'remarks',
                'type'  => 'text',
                'label' => 'Remarks'
            ],
                false,
                function ($value) {
                    $this->crud->addClause('where', 'remarks', 'LIKE', "%$value%");
                });

        $this->crud->addFilter([
            'name'  => 'custom2',
            'type'  => 'text',
            'label' => 'State'
        ],
            false,
            function ($value) {
                $matching_case_ids = [];
                array_push($matching_case_ids, ...DB::table('states')->where('name', 'LIKE', "%$value%")->pluck('id')->toArray());
                $this->crud->addClause('whereIn', 'state_id', $matching_case_ids);
            });
        $this->crud->addFilter([
            'name'  => 'custom3',
            'type'  => 'text',
            'label' => 'District'
        ],
            false,
            function ($value) {
                $matching_case_ids = [];
                array_push($matching_case_ids, ...DB::table('districts')->where('name', 'LIKE', "%$value%")->pluck('id')->toArray());
                $this->crud->addClause('whereIn', 'district_id', $matching_case_ids);
            });

            $this->crud->addFilter([
                'name'  => 'created_at',
                'type'  => 'text',
                'label' => 'Registered On'
            ],
                false,
                function ($value) {
                    $this->crud->addClause('where', 'created_at', 'LIKE', "%$value%");
                });

        $this->crud->addFilter([
            'name'  => 'tabs',
            'type'  => 'select2',
            'label' => 'Tabs'
        ], function() {
            return [
                'District Courts and Tribunals' => 'District Courts and Tribunals',
                'High Court' => 'High Courts and Supreme Courts',
            ];
        }, function($value) {
            $this->crud->addClause('where', 'tabs', '=', $value);
        });


    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        if (!backpack_user()->hasRole('Super admin') && !backpack_user()->can('Add New Case'))
        $this->crud->denyAccess('create');

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
     */protected function setupUpdateOperation()
    {
        $pets = $this->crud->getCurrentEntry()->petitioners;
        $padvs = $this->crud->getCurrentEntry()->petitioner_advocates;
        $resps = $this->crud->getCurrentEntry()->respondents;
        $radvs = $this->crud->getCurrentEntry()->respondent_advocates;
        $petadv = array();
        $resadv = array();
        foreach ( $pets as $i => $pet ) {
            $petadv[] = [
                'petitioner' => $pet->petitioner,
                'petitioner_advocate' => $padvs[$i]->petitioner_advocate ?? '',
            ];
        }
        foreach ( $resps as $i => $pet ) {
            $resadv[] = [
                'respondent' => $pet->respondent,
                'respondent_advocate' => $radvs[$i]->respondent_advocate ?? '',
            ];
        }

        $this->crud->getCurrentEntry()->petitioner_and_advocates = json_encode( $petadv );
        $this->crud->getCurrentEntry()->respondent_and_advocates = json_encode( $resadv );

        $this->setupCreateOperation();
    }


    private function addFields()
    {
        CRUD::addFields([
            [
                'name' => 'cnr_no',
                'type' => 'text',
                'label' => 'CNR No. <span style="color:red;">*</span>',
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
                'options' => ApiDistrict::pluck('name', 'id')->toArray(),
                'allows_null' => false,
                'attributes' => [
                    'id' => 'district-dropdown',
                ],
            ],
            [
                'name' => 'assigned_to',
                'label' => 'Assign To',
                'type' => 'select2_from_array',
                'options' => User::where('role_id', 7)->pluck('name', 'id')->toArray(),
                'allows_null' => false,
                'attributes' => [
                    'id' => 'assigned-to-dropdown',
                ],
                // 'required' => true,
            ],
            [
                'name' => 'tabs',
                'type' => 'select_from_array',
                'label' => 'Tabs <span style="color:red;">*<span>',
                'options' => [
                    'District Courts and Tribunals' => 'District Courts and Tribunals',
                    'High Court' => 'High Courts and Supreme Courts',
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
                'options' => array_combine(range(1990, 2050), range(1990, 2050)), // Generate options from 2000 to 2050
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
                'name'          => 'petitioner_and_advocates',
                'label'          => 'Petitioner',
                'type'          => "repeatable",
                'fields' => [
                    [
                        'name'    => 'petitioner',
                        'type'    => 'text',
                        'label'   => 'Petitioner',
                        'wrapper' => ['class' => 'form-group col-md-6'],
                    ],
                    [
                        'name'    => 'petitioner_advocate',
                        'type'    => 'text',
                        'label'   => "Petitioner's Advocate",
                        'wrapper' => ['class' => 'form-group col-md-6'],
                    ],
                ],
                'new_item_label'  => 'Add Petitioner',
                'min_rows' => 1,
                'reorder' => false,
            ],
            [
                'name'          => 'respondent_and_advocates',
                'label'          => 'Respondent',
                'type'          => "repeatable",
                'fields' => [
                    [
                        'name'    => 'respondent',
                        'type'    => 'text',
                        'label'   => 'Respondent',
                        'wrapper' => ['class' => 'form-group col-md-6'],
                    ],
                    [
                        'name'    => 'respondent_advocate',
                        'type'    => 'text',
                        'label'   => "Respondent's Advocate",
                        'wrapper' => ['class' => 'form-group col-md-6'],
                    ],
                ],
                'min_rows' => 1,
                'new_item_label'  => 'Add Respondent',
                'reorder' => false,
            ],
            [
                'name' => 'judge_name',
                'type' => 'text',
                'label' => 'Judge Name',
            ],
            [
                'name' => 'court_room_no',
                'type' => 'text',
                'label' => 'Court Room No.',
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
                'attributes' => [
                    'placeholder' => "Hint: You can save as P-1 or R-3 which means you are briefing for Petitioner 1 or Respondant 3."
                ]
            ],
            [
                'type'          => "relationship",
                'name'          => 'organization',
                'attribute'     => "organization_name",
                'placeholder'     => 'Organization',
                'ajax' => true,
                'inline_create' => true,
                'label' => "Organization"
            ],
            [
                'type'          => "relationship",
                'name'          => 'tag',
                'attribute'     => "name",
                'placeholder'     => 'Reference',
                'ajax' => true,
                'inline_create' => true,
                'label' => "Reference"
            ],
            [
                'type'          => "relationship",
                'name'          => 'labels', // the method on your model that defines the relationship
                'ajax'          => true,
                'attribute'     => 'label',
                'placeholder'     => 'Labels',
                'label' => "Case Labels",
                'inline_create' => ['entity' => "case-label"]
            ],
            [
                'type'          => "relationship",
                'name'          => 'client',
                'attribute'     => "name",
                'placeholder'     => 'Client',
                'ajax' => true,
                'inline_create' => true,
                'label' => "Client"
            ],
            [
                'name' => 'sr_no_in_court',
                'type' => 'text',
                'label' => 'Sr. No. In Court',
            ],
            [
                'name' => 'decided_toggle',
                'type' => 'switch',
                'label' => 'Decided Toggle',
                'wrapper' => ['class' => 'form-group col-md-3'],
            ],
            [
                'name' => 'abbondend_toggle',
                'type' => 'switch',
                'label' => 'Abandoned Toggle',
                'wrapper' => ['class' => 'form-group col-md-3'],
            ],
            [
                'name' => 'attended_case',
                'type' => 'switch',
                'label' => 'Attended',
                'wrapper' => ['class' => 'form-group col-md-3'],
            ],
            [
                'name' => 'favourite_case',
                'type' => 'switch',
                'label' => 'Add as Favourite',
                'wrapper' => ['class' => 'form-group col-md-3'],
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

    public static function fetch_case_by_case_no( Request $request ) {
        return response()->json([
            'success' => true,
            'data' => 'Your case by case number data here'
        ]);
    }

    public function getDistricts(Request $request)
    {
        $state = ApiState::find($request->state_id);
        $districts = ApiDistrict::where('state_value', $state->val)->pluck('name', 'id');

        return response()->json($districts);
    }

    public function changeFavourite(Request $request)
    {
        $case = \App\Models\CaseModel::find($request->case_id);
        $case->favourite_case = $case->favourite_case ? false : true;
        $msg = $case->favourite_case ? "Case added to favourite." : "Case removed from favourite.";
        $case->save();

        return response()->json([
            'favourite_case' => $case->favourite_case,
            'data' => $msg
        ]);
    }

    public function getCaseForModal(Request $request)
    {
        $str = "";
        $case = \App\Models\CaseModel::find($request->case_id);
        $petitioners = \App\Models\Petitioner::where('case_id', $case->id)->pluck('petitioner')->toArray();
        $respondents = \App\Models\Respondent::where('case_id', $case->id)->pluck('respondent')->toArray();
        if ( null !== $case->case_labels ) {
            $labels = explode(',', $case->case_labels);
            if ( ! empty( $labels ) ) {
                foreach ( $labels as $label ) {
                    $clabel = CaseLabel::find($label)->label;
                    $str .= $clabel . ', ';
                }
            }
        }

        return response()->json([
            'case' => $case,
            'petitioners' => $petitioners,
            'respondents' => $respondents,
            'tags' => $str,
            'preview_url' => backpack_url('case-model-all-cases/'. $case->id .'/show'),
            'edit_url' => backpack_url('case-model-all-cases/'. $case->id .'/edit'),
        ]);
    }

    public function changeDateAndStage(Request $request) {
        $case = \App\Models\CaseModel::find($request->case_id);
        $case->next_date = $request->next_date;
        $case->case_stage = $request->case_stage;
        $case->save();

        return redirect()->back();
    }

    public function store()
    {
        $this->crud->setValidation([
            'tabs' => 'required',
            'cnr_no' => 'required',
            'assigned_to' => 'required',
        ]);

        $uploads = $this->crud->getRequest()->get('uploads');
        $petitioners = json_decode( $this->crud->getRequest()->get('petitioner_and_advocates') );
        $respondents = json_decode( $this->crud->getRequest()->get('respondent_and_advocates') );

        $response = $this->traitStore();

        $item = $this->crud->entry;

        $labels = ! empty( $this->crud->getRequest()->get('labels') ) ? $this->crud->getRequest()->get('labels') : "";
        $clabels = implode(',', $labels);
        $item->case_labels = $clabels;
        if (count($petitioners) > 0) {
            foreach ($petitioners as $r) {
                if (!empty($r->petitioner)) {
                    Petitioner::create(['case_id' => $item->id, 'petitioner' =>  $r->petitioner]);
                }
                if (!empty($r->petitioner_advocate)) {
                    PetitionerAdvocate::create(['case_id' => $item->id, 'petitioner_advocate' =>  $r->petitioner_advocate]);
                }
            }
        }
        if (count($respondents) > 0) {
            foreach ($respondents as $r) {
                if (!empty($r->respondent)) {
                    Respondent::create(['case_id' => $item->id, 'respondent' =>  $r->respondent]);
                }
                if (!empty($r->respondent_advocate)) {
                    RespondentAdvocate::create(['case_id' => $item->id, 'respondent_advocate' =>  $r->respondent_advocate]);
                }
            }
        }

        $item->lawyer_id = auth()->user()->id;
        $item->save();

        if ($response) {
            return redirect( backpack_url('case-model-active') );
        }
        // do something after save
        return $response;
    }

    public function setupShowOperation() {

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
            'format' => 'DD-MM-YYYY',
        ]);
        CRUD::column([
            'name' => 'next_date',
            'tab' => 'Case Details',
            'label' => 'Next Date',
            'format' => 'DD-MM-YYYY',
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
            'value' => function($entry) {
                return $entry->state->name;
            }
        ]);
        CRUD::column([
            'name' => 'district_name',
            'tab' => 'Case Details',
            'label' => 'District',
            'value' => function($entry) {
                return $entry->district->name;
            }
        ]);
        CRUD::column([
            'name' => 'custom',
            'tab' => 'Case Details',
            'label' => 'Petitioner/Additional',
            'value' => function($case) {
                $str = "";
                $petitioners = Petitioner::where('case_id', $case->id)->get();
                foreach($petitioners as $p) {
                    $str .= $p->petitioner . ', ';
                }
                return $str;
            }
        ]);
        CRUD::column([
            'name' => 'custom1',
            'tab' => 'Case Details',
            'label' => 'Their Advocates',
            'value' => function($case) {
                $str = "";
                $petitioners = PetitionerAdvocate::where('case_id', $case->id)->get();
                foreach($petitioners as $p) {
                    $str .= $p->petitioner_advocate . ', ';
                }
                return $str;
            }
        ]);
        CRUD::column([
            'name' => 'custom2',
            'tab' => 'Case Details',
            'label' => 'Respondent/Additional',
            'value' => function($case) {
                $str = "";
                $petitioners = Respondent::where('case_id', $case->id)->get();
                foreach($petitioners as $p) {
                    $str .= $p->respondent . ', ';
                }
                return $str;
            }
        ]);
        CRUD::column([
            'name' => 'custom3',
            'tab' => 'Case Details',
            'label' => 'Their Advocates',
            'value' => function($case) {
                $str = "";
                $petitioners = RespondentAdvocate::where('case_id', $case->id)->get();
                foreach($petitioners as $p) {
                    $str .= $p->respondent_advocate . ', ';
                }
                return $str;
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
            'label' => 'Reference',
            'value' => function ($case) {
                return RefTag::find($case->tags)->first()->name;
            }
        ]);
        CRUD::column([
            'name' => 'case_labels',
            'tab' => 'Case Details',
            'label' => 'Case Labels',
            'value' => function ($case) {
                $str = "";
                if ( null !== $case->case_labels ) {
                    $labels = explode(',', $case->case_labels);
                    foreach ( $labels as $label ) {
                        $clabel = CaseLabel::find($label)->label;
                        $str .= $clabel . ', ';
                    }
                    return $str;
                }
                return $str;
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
            'name' => 'assigned_to',
            'tab' => 'Case Details',
            'label' => 'Assigned To',
            'value' => function($case) {
                if ( null !== $case->assigned_to ) {
                    return User::find($case->assigned_to)->name;
                } else {
                    return "";
                }
            }
        ]);
        CRUD::column([
            'name' => 'created_at',
            'tab' => 'Case Details',
            'label' => 'Registered on Casewise',

        ]);


        // or using the fluent syntax
        CRUD::column('history')->type('textarea')->tab('Case History');
        CRUD::column('custom123')->tab('Worklist')->label('Worklist')->value(function($case){
            return Worklist::where('case_id', $case->id)->first();
        });
        CRUD::column('notes_description')->type('summernote')->tab('Notes');
        CRUD::column('orders')->type('table')->tab('Orders/Evidence');
    }

    public function fetchOrganization()
    {
        return \App\Models\Organization::where('organization_name', 'LIKE', '%' . request()->input('q') . '%')->paginate(10);
    }
    public function fetchTag()
    {
        return \App\Models\RefTag::where('name', 'LIKE', '%' . request()->input('q') . '%')->paginate(10);
    }
    public function fetchClient()
    {
        return \App\Models\Client::where('name', 'LIKE', '%' . request()->input('q') . '%')->paginate(10);
    }
    public function fetchLabels()
    {
        return \App\Models\CaseLabel::where('label', 'LIKE', '%' . request()->input('q') . '%')->paginate(10);
    }
}
