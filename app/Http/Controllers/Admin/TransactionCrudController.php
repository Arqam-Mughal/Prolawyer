<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\TransactionRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

use function PHPSTORM_META\map;

/**
 * Class TransactionCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class TransactionCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation {
        store as traitStore;
    }

    use \App\Http\Operations\InlineCreateOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Transaction::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/transaction');
        CRUD::setEntityNameStrings('transaction', 'transactions');

        $this->crud->allowAccessOnlyTo(['list', 'create']);

        if (!backpack_user()->hasRole('Super admin')) {
            $this->crud->addClause('where', 'user_id', backpack_user()->id);
        }
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
        // $this->crud->disableResponsiveTable();

        CRUD::column('user');
        CRUD::column('plan')->label('Plan name');
        CRUD::column('payment_method');
        CRUD::column('tracking_id');
        CRUD::column('payment_mode');
        CRUD::column('billing_info');
        CRUD::column('plan_validity');
        CRUD::column('charged_amount');
        CRUD::column('description');
        CRUD::column('trans_date')->label('Transaction date');


        $this->crud->addFilter(
            [
                'name'  => 'user_id',
                'type'  => 'select2',
                'label' => 'User'
            ],
            \App\Models\User::pluck('name', 'id')->toArray(),
            function ($value) {
                $this->crud->addClause('where', 'user_id', 'LIKE', "%$value%");
            }
        );

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
        CRUD::setValidation(TransactionRequest::class);

        if ($this->crud->getCurrentOperation() == 'InlineCreate') {
            CRUD::addField('billing_info');
            CRUD::field('trans_date')->label('Transaction date');
            CRUD::addField('description');


            CRUD::addField([
                'name' => 'plan_id',
                'type' => 'select2_from_array',
                'options' => \App\Models\Role::where('type', 'regular_user')->pluck('name', 'id')
            ]);

            CRUD::addField([
                'name' => 'plan_validity',
                'type' => 'select_from_array',
                'options' => [
                    'Monthly' => 'Monthly',
                    'Quaterly' => 'Quaterly',
                    'Yearly' => 'Yearly',
                ]
            ]);
        } else
            CRUD::setFromDb();

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

    public function store()
    {
        $response = $this->traitStore();

        
        return $response;
    }
}
