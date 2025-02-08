<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\WorklistRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class WorklistCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class WorklistCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation {
        destroy as traitDestroy;
    }

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Worklist::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/worklist');
        CRUD::setEntityNameStrings('worklist', 'worklists');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        if (!backpack_user()->hasRole('Super admin') && !backpack_user()->can('Worklist'))
        $this->crud->denyAccess('list');


        CRUD::column('user');
        CRUD::column('title');
        CRUD::column('description');
        CRUD::column('category');
        CRUD::addColumn([
            // 1-n relationship
            'label'     => 'Case Number',
            'type'      => 'select',
            'name'      => 'case_id',
            'entity'    => 'case',
            'attribute' => 'case_no',
            'model'     => "App\Models\CaseModel",
        ],);

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
        if (!backpack_user()->hasRole('Super admin') && !backpack_user()->can('Worklist'))
        $this->crud->denyAccess('create');

        CRUD::setValidation(WorklistRequest::class);

        if (backpack_user()->hasRole('Super admin'))
            CRUD::field('user_id');
        else
            CRUD::addField([
                'name' => 'user_id',
                'type' => 'hidden',
                'value' => backpack_user()->id
            ]);

        CRUD::field('category_id');
        CRUD::field('title');
        CRUD::field('description');


        CRUD::addField([
            'name' => 'start_date',
            'wrapper'   => [
                'class'      => 'form-group col-md-2'
            ]
        ]);
        CRUD::addField([
            'name' => 'set_time',
            'wrapper'   => [
                'class'      => 'form-group col-md-2'
            ]
        ]);

        CRUD::addField([
            'name' => 'repeated_options',
            'label' => 'Repeat',
            'type' => 'select_from_array',
            'options' => [
                0 => 'Does not repeat',
                1 => 'Repeats daily',
                2 => 'Repeats weekly',
                3 => 'Repeats monthly',
                4 => 'Repeats yearly',
            ],
            'allows_null' => false,
        ]);

        CRUD::addField([
            'label'     => 'Case',
            'name'      => 'case_id',
            'type'      => 'select2_from_array',
            'options'   => \App\Models\CaseModel::take(100)->pluck('case_no', 'id')
        ]);
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
        if (!backpack_user()->hasRole('Super admin') && !backpack_user()->can('Worklist'))
        $this->crud->denyAccess('update');

        $this->setupCreateOperation();
    }

    public function markAsCompleted(\App\Models\Worklist $worklist)
    {
        $worklist->status = 1;
        $worklist->save();


        \Alert::add('success', 'Worklist updated successfully')->flash();
        return redirect()->route('backpack.dashboard');
    }

    public function markAsIncomplete(\App\Models\Worklist $worklist)
    {
        $worklist->status = 0;
        $worklist->save();


        \Alert::add('success', 'Worklist updated successfully')->flash();
        return redirect()->route('backpack.dashboard');
    }


    public function destroy($id)
    {
        if (!backpack_user()->hasRole('Super admin') && !backpack_user()->can('Worklist'))
        $this->crud->denyAccess('delete');

        CRUD::hasAccessOrFail('delete');

        \Alert::add('success', 'Worklist deleted successfully')->flash();
        return CRUD::delete($id);
    }
}
