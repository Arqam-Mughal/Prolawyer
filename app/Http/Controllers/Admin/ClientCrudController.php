<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ClientRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use App\Http\Operations\InlineCreateOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class ClientCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ClientCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation { store as traitStore; }
    use \App\Http\Operations\InlineCreateOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Client::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/client');
        CRUD::setEntityNameStrings('Client', 'Clients');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        // dd('yes');
        if (!backpack_user()->hasRole('Super admin') && !backpack_user()->can('My Clients'))
        $this->crud->denyAccess('list');

        CRUD::setFromDb(); // set columns from db columns.

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
        if (!backpack_user()->hasRole('Super admin') && !backpack_user()->can('My Clients'))
        $this->crud->denyAccess('create');

        $this->addFields();

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
        if (!backpack_user()->hasRole('Super admin') && !backpack_user()->can('My Clients'))
        $this->crud->denyAccess('update');

        $this->setupCreateOperation();
    }

    private function addFields()
    {
        CRUD::addFields([
            [
                'name'   => 'name',
                'label'  => 'Name',
                'type'   => "text",
            ],
            [
                'name'   => 'address',
                'label'  => 'Address',
                'type'   => "text",
            ],
            [
                'name'   => 'gender',
                'label'  => 'Gender',
                'type'   => "select_from_array",
                'options' => array(
                    'Male' => 'Male',
                    'Female' => 'Female',
                )
            ],
            [
                'name'   => 'email',
                'label'  => 'Email',
                'type'   => "text",
            ],
            [
                'name'   => 'mobile',
                'label'  => 'Contact',
                'type'   => "text",
            ],
            [
                'name'   => 'description',
                'label'  => 'Description',
                'type'   => "text",
            ],

        ]);
    }

    public function store()
    {
        $response = $this->traitStore();
        $item = $this->crud->entry;
        $item->user_id = auth()->user()->id;
        $item->save();

        return $response;
    }

}
