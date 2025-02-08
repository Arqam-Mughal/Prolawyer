<?php

namespace App\Http\Controllers\Admin;

use App\Models\CaseModel;
use App\Models\Petitioner;
use App\Models\Respondent;
use App\Http\Requests\ConnectedMattersRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class ConnectedMattersCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ConnectedMattersCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation  { store as traitStore; }
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
        CRUD::setModel(\App\Models\ConnectedMatters::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/connected-matters');
        CRUD::setEntityNameStrings('connected matters', 'connected matters');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        if (!backpack_user()->hasRole('Super admin') && !backpack_user()->can('Connected Matters'))
        $this->crud->denyAccess('list');

        // $this->crud->removeAllButtons();

        CRUD::addColumn([
            'name' => "primary_case",
            'label' => "Primary Case",
            'type' => "textarea",
            'value' => function($value){
                $str = "";
                $petitioner = Petitioner::where('petitioner', $value->primary_case)->first();
                $respondent = Respondent::where('respondent', $value->primary_case)->first();
                if ( ! empty( $petitioner ) && ! empty( $respondent ) ) {
                    $str = $petitioner->petitioner . " VS " . $respondent->respondent;
                } else {
                    $str = $value->primary_case;
                }
                return $str;
            },
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
        if (!backpack_user()->hasRole('Super admin') && !backpack_user()->can('Connected Matters'))
        $this->crud->denyAccess('create');

        CRUD::setValidation([
            // 'name' => 'required|min:2',
        ]);

        $case_options = array();
        $cases = CaseModel::latest()->paginate(1000);
        foreach( $cases as $case ) {
            $petitioner = Petitioner::where('case_id', $case->id)->first();
            $respondent = Respondent::where('case_id', $case->id)->first();
            if ( null !== $respondent && null !== $petitioner && null !== $petitioner->petitioner && null !== $respondent->respondent ) {
                $case_options[ $case->id ] = $petitioner->petitioner . ' VS ' . $respondent->respondent;
            }
        }
        // dd($case_options);
        /**
         * Fields can be defined using the fluent syntax:
         * - CRUD::field('price')->type('number');
         */
        CRUD::field('primary_case')->type('select2_from_array')->options( $case_options );

        CRUD::field([
            'name'          => 'connected_matters_and_cases',
            'label'          => 'Connected Cases',
            'type'          => "repeatable",
            'fields' => [
                [
                    'name'    => 'connected_matters',
                    'type'    => 'select2_from_array',
                    'options' => $case_options,
                    'label'   => 'Connected Matter',
                    'wrapper' => ['class' => 'form-group col-md-12'],
                ],
            ],
            'new_item_label'  => 'Connect New Case',
            'min_rows' => 1,
            'reorder' => false,
            'fake' => true,
        ]);
    }

    public function store()
    {

        $connected_cases = json_decode( $this->crud->getRequest()->get('connected_matters_and_cases') );
        $response = $this->traitStore();

        $item = $this->crud->entry;

        if (count($connected_cases) > 0) {
            foreach ( $connected_cases as $key => $case ){
                $connected_matters[] = $case->connected_matters;
            }
            $item->connected_matters = json_encode($connected_matters);
        }

        $item->lawyer_id = auth()->user()->id;
        $item->save();
        if ($response) {
            return redirect( backpack_url('connected-matters') );
        }
        // do something after save
        return $response;
    }

    protected function setupUpdateOperation()
    {
        if (!backpack_user()->hasRole('Super admin') && !backpack_user()->can('Connected Matters'))
        $this->crud->denyAccess('update');

        $conn_cases = $this->crud->getCurrentEntry()->connected_matters;
        $conn_cases = substr($conn_cases, 1, -1);
        $pets = explode(',', $conn_cases);
        foreach ( $pets as $i => $pet ) {
            $petadv[] = [
                'connected_matters' => substr($pet, 1, -1),
            ];
        }
        // dd($petadv);
        $this->crud->getCurrentEntry()->connected_matters_and_cases = json_encode( $petadv );

        $this->setupCreateOperation();
    }
}
