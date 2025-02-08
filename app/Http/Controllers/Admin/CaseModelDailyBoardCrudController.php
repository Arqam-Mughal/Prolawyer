<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\CaseLabel;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\CaseModelRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class CaseModelCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CaseModelDailyBoardCrudController extends CrudController
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
        CRUD::setRoute(config('backpack.base.route_prefix') . '/case-model-dailyboard');
        CRUD::setEntityNameStrings('daily board', 'daily boards');

        CRUD::enableExportButtons();

        $date = isset($this->crud->getRequest()->date) ? $this->crud->getRequest()->date : today();

        $this->crud->addClause('select', 'cases.*', 'court_lists.short_form');
        $this->crud->addClause('leftJoin', 'court_lists', 'cases.court_bench', '=', 'court_lists.long_form');

        $this->crud->addClause('where', function ($query) use ($date) {
            $query->whereNotNull('history')
                ->where('history', '!=', '')
                ->whereRaw("JSON_VALID(`history`) > 0 AND JSON_SEARCH(`history`, 'all', '" . date('d-m-Y', strtotime($date)) . "', NULL, '$**.date') IS NOT NULL")
                ->orWhereDate('next_date', date('Y-m-d', strtotime($date)))
                ->orWhereDate('previous_date', date('Y-m-d', strtotime($date)));
        });

        // Grouping and Ordering
        $this->crud->addClause('groupBy', 'cases.id');
        $this->crud->addClause('orderBy', 'cases.created_at', 'desc');
        $this->crud->orderBy('id', 'desc');

        $this->crud->allowAccessOnlyTo(['list','create','delete','update','show']);

    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        if (!backpack_user()->hasRole('Super admin') && !backpack_user()->can('Daily Board'))
        $this->crud->denyAccess('list');

        // Remove the default create button
        $this->crud->removeButton('create');
        CRUD::enableBulkActions();

        // Add a custom create button that links to the create page of CategoryCrudController
        $this->crud->addButtonFromView('top', 'create_case', 'create_case', 'beginning');

        // $this->crud->removeAllButtons();
        $this->crud->disableResponsiveTable();
        CRUD::addColumn([
            'name' => 'first_col',
            'type' => 'inline_preview',
            'label' => 'Quick Actions',
        ]);
        CRUD::addColumns([
            [
                'name' => 'previous_date',
                'type' => 'date',
                'format' => 'DD-MM-YYYY',
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
                        $respondent ? wordwrap($respondent, 30, '<br>', true) : '----'
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
                'value' => function ( $case ) {
                    $str = User::where('id',$case->assigned_to)->first()->name ?? '';
                    return $str;
                }
            ],
            [
                'name' => 'remarks',
                'type' => 'textarea',
            ],

            [
                'name' => 'custom8',
                'type' => 'textarea',
                'label' => 'My Client',
                'value' => function ($case) {
                    $organization = DB::table('clients')->where('id', $case->client_id)->first()->name ?? '';

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
                'name' => 'attended_case',
                'type' => 'textarea',
                'label' => 'Attended Status',
                'value' => function( $case ) {
                    return $case->attended_case ? "Attended" : "Not Attended";
                },
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
            'name'  => 'remarks',
            'type'  => 'text',
            'label' => 'Remarks'
            ],
            false,
            function ($value) {
            $this->crud->addClause('where', 'remarks', 'LIKE', "%$value%");
        });

        $this->crud->addFilter([
            'name'  => 'custom8',
            'type'  => 'text',
            'label' => 'My Client'
            ],
            false,
            function ($value) {
                $matching_case_ids = [];
                array_push($matching_case_ids, ...DB::table('clients')->where('name', 'LIKE', "%$value%")->pluck('id')->toArray());
                $this->crud->addClause('whereIn', 'client_id', $matching_case_ids);
        });
        $this->crud->addFilter([
            'name'  => 'custom7',
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
            'name'  => 'attended_case',
            'type'  => 'select2',
            'label' => 'Attendance Status'
            ],
            function() {
                return [
                    0 => 'Not Attended',
                    1 => 'Attended',
                ];
            }, function($value) {
                $this->crud->addClause('where', 'attended_case', '=', $value);
        });

        $this->crud->addFilter([
                'name'  => 'tabs',
                'type'  => 'select2',
                'label' => 'Tabs'
            ], function() {
                return [
                    'District Courts and Tribunals' => 'District Courts and Tribunals',
                    'High Court' => 'High Courts and Supreme Court',
                ];
            }, function($value) {
                $this->crud->addClause('where', 'tabs', '=', $value);
        });
        $this->crud->addClause('where', 'tabs', '=', "District Courts and Tribunals");

    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(CaseModelRequest::class);
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
