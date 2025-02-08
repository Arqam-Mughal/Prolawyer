<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CaseModelRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\DB;

/**
 * Class CaseModelCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CaseModelFilterCrudController extends CrudController
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
        CRUD::setRoute(config('backpack.base.route_prefix') . '/case-model-filtered');
        CRUD::setEntityNameStrings('filter case', 'filter cases');


        // isko filter se dynamic karna padega
        $tabs = 'District Courts and Tribunals';

        $this->crud->addClause('where', 'status', '=', 'Open');
        $this->crud->addClause('where', 'tabs', '=', $tabs);
        $this->crud->addClause('whereNull', 'decided_toggle');
        $this->crud->addClause('whereNull', 'abbondend_toggle');
        $this->crud->addClause('whereDate', 'next_date', today()->format('Y-m-d'));

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
        $this->crud->removeAllButtons();

        CRUD::addColumns([
            [
                'name' => 'previous_date',
            ],
            [
                'name' => 'case_no',
                'label' => 'Case number'
            ],
            [
                'name' => 'custom',
                'label' => 'Parties Details',
                'type' => 'custom_html',
                'value' => function ($case) {
                    $petitioner = DB::table('petitioners')->where('case_id', $case->id)->first()->petitioner;
                    $respondent = DB::table('respondents')->where('case_id', $case->id)->first()->respondent;

                    $str = sprintf('<strong>Parties: </strong>
                    <a href="#" class=" popover-demo mb-2" data-toggle="popover" title="Petitioners" data-content="%s">
                        %s
                    </a><strong> VS </strong>
                    <br>
                    <a href="#" class=" popover-demo mb-2" data-toggle="popover" title="Respondents" data-content="%s">
                        %s
                    </a>
                    <br><strong>Brief For: </strong>%s
                    <br><strong>Organization: </strong>%s
                    <br><strong>Brief No: </strong>%s', ...[
                        $petitioner,
                        $petitioner ? wordwrap($petitioner, 30, '<br>', true) : '----',
                        $respondent,
                        $respondent ? wordwrap($respondent, 30, '<br>', true) : '----',
                        $case->brief_for,
                        DB::table('organizations')
                            ->where('id', $case->organization_id)
                            ->first()->organization_name ?? '----',
                        $case->Brief_no ? $case->Brief_no : '-'
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
                'name' => 'custom2',
                'label' => 'Court Details',
                'type' => 'custom_html',
                'value' => function ($case) {
                    $str = sprintf('<strong>Court: </strong>%s
                        <br><strong>Room No: </strong>%s
                        <br><strong>Judge: </strong>%s', ...[
                        $case->short_form ? wordwrap($case->short_form, '30', '<br>', true) : wordwrap($case->court_bench, '30', '<br>', true),
                        $case->court_room_no,
                        wordwrap($case->judge_name, '30', '<br>', true)
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
                'name' => 'custom3',
                'label' => 'Stage',
                'type' => 'custom_html',
                'value' => function ($case) {
                    $str = sprintf('<a href="%s">%s</a>', ...[
                        url('case/' . $case->id),
                        $case->case_stage
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
                'name' => 'previous_date',
            ],
            [
                'name' => 'remarks',
            ],
        ]);

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
