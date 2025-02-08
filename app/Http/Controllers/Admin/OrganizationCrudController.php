<?php

namespace App\Http\Controllers\Admin;

use App\Models\Organization;
use App\Http\Requests\OrganizationRequest;
use App\Http\Operations\InlineCreateOperation;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class OrganizationCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class OrganizationCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Organization::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/organization');
        CRUD::setEntityNameStrings('Organization', 'organizations', 'Organization');
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
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
        $this->setupCreateOperation();
    }

    private function addFields()
    {
        CRUD::addFields([
            [
                'name'   => 'organization_name',
                'label'  => 'Organization Name',
                'type'   => "text",
            ],
            [
                'name'    => 'address',
                'type'    => 'text',
                'label'   => "Address",
            ],
            [
                'name'          => 'representators',
                'label'          => 'Authorized Person',
                'type'          => "repeatable",
                'fields' => [
                    [
                        'name'    => 'representator',
                        'type'    => 'text',
                        'label'   => 'Name',
                        'wrapper' => ['class' => 'form-group col-md-12'],
                    ],
                    [
                        'name'   => 'email',
                        'label'  => 'Email',
                        'wrapper' => ['class' => 'form-group col-md-12'],
                        'type'   => "text",
                    ],
                    [
                        'name'    => 'contact',
                        'type'    => 'text',
                        'label'   => "Contact",
                        'wrapper' => ['class' => 'form-group col-md-12'],
                    ],
                ],
                'new_item_label'  => 'Add Person',
                'min_rows' => 1,
            ]
        ]);
    }


    public function store()
    {      
        $response = $this->traitStore();

        $item = $this->crud->entry;
        $rep = json_decode($this->crud->getRequest()->get('representators'));
        $item->representator = $rep[0]->representator;
        $item->email = $rep[0]->email;
        $item->contact = $rep[0]->contact;
        $item->adv_id = auth()->user()->id;
        $item->save();

        unset($rep[0]);

        foreach ($rep as $key => $r) {
            $organization = Organization::create([
                'adv_id' => auth()->id(),
                'organization_name' =>  $item->organization_name ?? "",
                'representator' => $r->representator ?? "",
                'contact' => $r->contact ?? "",
                'email' => $r->email ?? "",
                'address' => $item->address ?? "",
                'parent_id' => $item->id,
            ]);

            $organization->save();
        }

        return $response;
    }

}
