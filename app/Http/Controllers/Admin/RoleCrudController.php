<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\RoleRequest;
use Spatie\Permission\PermissionRegistrar;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\PermissionManager\app\Http\Controllers\RoleCrudController as BRoleCrudController;

/**
 * Class RoleCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class RoleCrudController extends BRoleCrudController
{
    public function setup()
    {
        $this->crud->setModel(\App\Models\Role::class);
        $this->crud->setEntityNameStrings('Plan', 'Plans');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/role');

        // deny access according to configuration file
        if (config('backpack.permissionmanager.allow_role_create') == false) {
            $this->crud->denyAccess('create');
        }
        if (config('backpack.permissionmanager.allow_role_update') == false) {
            $this->crud->denyAccess('update');
        }
        if (config('backpack.permissionmanager.allow_role_delete') == false) {
            $this->crud->denyAccess('delete');
        }
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    public function setupListOperation()
    {
        if (!backpack_user()->hasRole('Super admin'))
        $this->crud->denyAccess('list');

        CRUD::column('name');
        CRUD::column('price')->label('Monthly price');
        CRUD::column('quarterly_price');
        CRUD::column('yearly_price');
        CRUD::column('type')->value(fn($x) => \App\Models\Role::ROLE_TYPES[$x->type]);
        CRUD::column('no_cases')->label('No. cases');
        CRUD::column('no_employees')->label('No. employees');
        CRUD::column('status')->value(fn($x) => $x->status ? 'On' : 'Off');

        /**
         * Columns can be defined using the fluent syntax:
         * - CRUD::column('price')->type('number');
         */
    }

    public function setupCreateOperation()
    {
        // dd('yes');
        if (!backpack_user()->hasRole('Super admin'))
        $this->crud->denyAccess('create');

        $this->addFields();
        $this->crud->setValidation(RoleRequest::class);

        //otherwise, changes won't have effect
        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function setupUpdateOperation()
    {
        if (!backpack_user()->hasRole('Super admin'))
        $this->crud->denyAccess('update');

        $this->setupCreateOperation();
    }


    private function addFields()
    {
        CRUD::addFields([
            [
                'name' => 'name',
                'type' => 'text',
                'label' => 'Name',
            ],
            [
                'name' => 'price',
                'type' => 'number',
                'label' => 'Monthly Price',
                'attributes' => ["step" => "any"], // if you want to allow decimal numbers
            ],
            [
                'name' => 'quarterly_price',
                'type' => 'number',
                'label' => 'Quarterly Price',
                'attributes' => ["step" => "any"], // if you want to allow decimal numbers
            ],
            [
                'name' => 'yearly_price',
                'type' => 'number',
                'label' => 'Yearly Price',
                'attributes' => ["step" => "any"], // if you want to allow decimal numbers
            ],
            [
                'name' => 'type',
                'type' => 'select_from_array',
                'label' => 'Type',
                'options' => \App\Models\Role::ROLE_TYPES,
                'allows_null' => false,
            ],
            [
                'name' => 'no_cases',
                'type' => 'number',
                'label' => 'Number of Cases',
            ],
            [
                'name' => 'no_employees',
                'type' => 'number',
                'label' => 'Number of employees',
            ],
            [
                'name' => 'status',
                'type' => 'select_from_array',
                'label' => 'Status',
                'options' => [1 => 'On', 0 => 'Off'],
            ],
            [
                'name' => 'permissions',
                'label' => trans('backpack::permissionmanager.permission_plural'),
                'type' => 'checklist_permissions',
                'entity'           => 'permissions', // the method that defines the relationship in your Model
                'entity_secondary' => 'permissions', // the method that defines the relationship in your Model
                'attribute'        => 'name', // foreign key attribute that is shown to user
                'model'            => config('permission.models.permission'), // foreign key model
                'pivot'            => true, // on create&update, do you need to add/delete pivot table entries?]
            ],
        ]);
    }

    public function store() {
        dd($this->crud->getRequest()->request);
    }
}
