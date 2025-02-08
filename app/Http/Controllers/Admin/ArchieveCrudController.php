<?php

namespace App\Http\Controllers\Admin;

use App\Models\CaseLabel;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ArchieveRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class ArchieveCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ArchieveCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\CaseModel::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/archive');
        CRUD::setEntityNameStrings('archive', 'archives');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        if (!backpack_user()->hasRole('Super admin') && !backpack_user()->can('Archive'))
        $this->crud->denyAccess('list');

        $this->crud->removeAllButtons();

        // $this->crud->disableResponsiveTable();

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
                'label' => 'Tags/Labels',
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

        $this->crud->addFilter([
            'name'  => 'case_decided',
            'type'  => 'select2',
            'label' => 'Case'
        ], function() {
            return [
                'decided_toggle' => 'Decided Cases',
                'abbondend_toggle' => 'Abandoned Cases',
            ];
        }, function($value) {
            $this->crud->addClause('whereIn', $value, ['check', 1]);
        });
        /**
         * Columns can be defined using the fluent syntax:
         * - CRUD::column('price')->type('number');
         */
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation([
            // 'name' => 'required|min:2',
        ]);
        CRUD::setFromDb(); // set fields from db columns.

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
}
