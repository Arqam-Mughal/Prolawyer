<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use function PHPSTORM_META\map;
use App\Http\Requests\UserRequest;

use Backpack\CRUD\app\Library\Widget;
use Illuminate\Support\Facades\Route;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\PermissionManager\app\Http\Controllers\UserCrudController as BUserCrudController;

/**
 * Class UserCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class UserCrudController extends BUserCrudController
{

    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation {
        store as traitStore;
    }
    use \App\Http\Operations\FetchOperation;


    public function setup()
    {
        $this->crud->setModel(\App\Models\User::class);
        $this->crud->setEntityNameStrings(trans('backpack::permissionmanager.user'), trans('backpack::permissionmanager.users'));
        $this->crud->setRoute(backpack_url('user'));
    }


    public function setupListOperation()
    {
        // dd('yes');
        if (!backpack_user()->hasRole('Super admin') && !backpack_user()->can('Add sub lawyer'))
            $this->crud->denyAccess('list');

        if (backpack_user()->hasAnyRole(\App\Models\Role::where('type', 'regular_user')->pluck('name')->toArray()))
            $this->crud->addClause('where', 'senior_lawyer_id', backpack_user()->id);

        // if (backpack_user()->hasAnyRole(\App\Models\Role::where('type', 'system_user')->pluck('name')->toArray()))
        //     $this->crud->addClause('whereHas', 'roles', function ($query) {
        //         return $query->where('type', 'regular_user');
        //     });

        $this->crud->addColumns([
            [
                'name'  => 'name',
                'label' => 'Name',
                'type'  => 'text',
            ],
            [
                'name'  => 'email',
                'label' => 'Email ID',
                'type'  => 'email',
            ],
            [
                'name'  => 'phone_number',
                'label' => 'Mobile',
                'type'  => 'text',
            ],
            [
                'name'  => 'address',
                'label' => 'Address',
                'type'  => 'text',
            ],
            // [ // n-n relationship (with pivot table)
            //     'label'     => trans('backpack::permissionmanager.extra_permissions'), // Table column heading
            //     'type'      => 'select_multiple',
            //     'name'      => 'permissions', // the method that defines the relationship in your Model
            //     'entity'    => 'permissions', // the method that defines the relationship in your Model
            //     'attribute' => 'name', // foreign key attribute that is shown to user
            //     'model'     => config('permission.models.permission'), // foreign key model
            // ],
        ]);

        if (backpack_user()->hasRole('Super admin'))
            $this->crud->addColumns([
                [
                    'name' => 'senior_lawyer_id',
                    'label' => 'Senior lawyer',
                    'value' => fn($v) => \App\Models\User::find($v->senior_lawyer_id)->name ?? ''
                ],
                [ // n-n relationship (with pivot table)
                    'label'     => 'Plan name', // Table column heading
                    'type'      => 'select_multiple',
                    'name'      => 'roles', // the method that defines the relationship in your Model
                    'entity'    => 'roles', // the method that defines the relationship in your Model
                    'attribute' => 'name', // foreign key attribute that is shown to user
                    'model'     => config('permission.models.role'), // foreign key model
                ],
                [
                    'label' => 'Current plan amount',
                    'name' => 'text',
                    'value' => fn($v) => \App\Models\Transaction::where('user_id', $v->id)->orderBy('created_at')->first()->charged_amount ?? 0
                ],
                [
                    'label' => 'Plan validity',
                    'type'  => 'text',
                    'value' => fn($v) => \App\Models\Transaction::where('user_id', $v->id)->orderBy('created_at')->first()->plan_validity ?? 0
                ],
                [
                    'name'  => 'plan_expiry',
                    'label' => 'Plan expiry date',
                    'type'  => 'date',
                ],
            ]);


        // Role Filter
        if(backpack_user()->hasAnyRole('Super admin')){
        $this->crud->addFilter(
            [
                'name'  => 'role',
                'type'  => 'dropdown',
                'label' => trans('backpack::permissionmanager.role'),
            ],
            \Spatie\Permission\Models\Role::all()->pluck('name', 'id')->toArray(),
            function ($value) { // if the filter is active
                $this->crud->addClause('whereHas', 'roles', function ($query) use ($value) {
                    $query->where('role_id', '=', $value);
                });
            }
        );
    }

        // Extra Permission Filter
        $this->crud->addFilter(
            [
                'name'  => 'permissions',
                'type'  => 'select2',
                'label' => trans('backpack::permissionmanager.extra_permissions'),
            ],
            \Spatie\Permission\Models\Permission::all()->pluck('name', 'id')->toArray(),
            function ($value) { // if the filter is active
                $this->crud->addClause('whereHas', 'permissions', function ($query) use ($value) {
                    $query->where('permission_id', '=', $value);
                });
            }
        );

    if(backpack_user()->hasAnyRole('Super admin')){
        $this->crud->addFilter(
            [
                'name'  => 'senior_lawyer_id',
                'type'  => 'select2',
                'label' => 'Senior lawyer',
            ],
            \App\Models\User::whereHas('roles', function ($q) {
                $q->whereIn('name', \App\Models\Role::where('type', 'regular_user')->pluck('name')->toArray());
            })->pluck('name', 'id')->toArray(),
            function ($value) { // if the filter is active
                $this->crud->addClause('where', 'senior_lawyer_id', $value);
            }
        );
    }
    }

    public function setupShowOperation()
    {
        // dd('yes');
         if (!backpack_user()->hasRole('Super admin') && !backpack_user()->can('Add sub lawyer'))
            $this->crud->denyAccess('show');

        CRUD::addColumn([
            'label' => 'Profile Image',
            'name' => 'avatar',
            'type' => 'image',
            'prefix' => 'storage/',
            'width' => '350px',
            'height' => '350px',
            'height' => 'auto'
        ]);

        $this->setupListOperation();
        $this->crud->removeColumn('senior_lawyer_id');

        $transactions = $this->crud->getCurrentEntry()->transactions;

        if ($transactions->count()) {
            $transaction = $transactions->sortByDesc('created_at')->first();

            CRUD::addColumns([
                [
                    'label' => 'Plan buy date',
                    'name' => 'plan_buy_date',
                    'type' => 'date',
                    'value' => $transaction->created_at
                ],
            ]);
        }

        // $this->crud->addColumn([
        //     'name' => 'sub_lawyers',
        //     'value' => function($v) {
        //         return \App\Models\User::where('senior_lawyer_id', $v->id)->pluck('name')->implode(', ');
        //     }
        // ]);


        CRUD::addColumns([
            [
                'label' => 'Plan Validity',
                'name' => 'plan_validity',
                'type' => 'text',
                'value' => fn($v) => \App\Models\Transaction::where('user_id', $v->id)->orderBy('created_at')->first()->plan_validity ?? '-'
            ],
            [
                'label' => 'Permissions',
                'name' => 'permissions',
                'type' => 'textarea',
                'value' => fn($v) => \App\Models\User::find($v->id)->getAllPermissions()->pluck('name')->implode(', ')
            ],
        ]);

        if (backpack_user()->hasRole('Super admin')) {
            if ($this->crud->getCurrentEntry()->hasRole(\App\Models\Role::where('type', 'regular_user')->pluck('name')->toArray())) {
                $user_id = $this->crud->getCurrentEntryId();
                $sub_lawyers = \App\Models\User::where('senior_lawyer_id', $user_id)->get();

                $disp_sub_lawyers = $sub_lawyers->map(function ($s_l) {
                    return [
                        'name' => $s_l->name,
                        'email' => $s_l->email,
                        'phone_number' => $s_l->phone_number,
                        'permissions' => $s_l->permissions->pluck('name')->implode(', '),
                        'actions' => sprintf(
                            '
                        <div class="d-flex gap-2">
                            <a class="btn btn-primary" href="%s"><i class="la la-eye"></i></a>
                        </div>
                    ',
                            route('user.show', ['id' => $s_l->id]),
                        ),
                    ];
                });

                Widget::add([
                    'type' => 'table',
                    'wrapper' => ['class' => 'mt-5 col-lg-8'],
                    'title' => 'Sub Laywers',
                    'title_link' => [
                        'link' => backpack_url('user?senior_lawyer_id=' . $user_id),
                        'label' => 'View in detail',
                    ],
                    'title_link_add' => [
                        'link' => route('user.create', ['senior_lawyer_id' => $user_id, 'role' => \App\Models\Role::where('type', 'sub_lawyer')->first()->id]),
                        'label' => 'Add sub lawyer'
                    ],
                    'columns' => [
                        'name' => 'Name',
                        'email' => 'Email',
                        'phone_number' => 'Mobile',
                        'permissions' => 'Permissions',
                        'actions' => 'Actions',
                    ],
                    'data' => $disp_sub_lawyers,
                ])->to('after_content');

                $transactions = \App\Models\Transaction::where('user_id', $user_id)->get();

                $disp_transactions = $transactions->map(function ($txn) {
                    return [
                        'name' => $txn->plan->name,
                        'payment_method' => $txn->payment_method,
                        'charged_amount' => $txn->charged_amount,
                        'tracking' => $txn->tracking,
                        'plan_validity' => $txn->plan_validity,
                        'payment_mode' => $txn->payment_mode
                    ];
                });

                Widget::add([
                    'type' => 'table',
                    'wrapper' => ['class' => 'col-lg-8'],
                    'title' => 'Transactions',
                    'title_link' => [
                        'link' => backpack_url('transaction?user_id=' . $user_id),
                        'label' => 'View in detail',
                    ],
                    'columns' => [
                        'name' => 'Plan name',
                        'plan_validity' => 'Plan validity',
                        'payment_method' => 'Payment method',
                        'tracking' => 'Tracking',
                        'payment_mode' => 'Payment mode',
                        'charged_amount' => 'Charged',
                    ],
                    'data' => $disp_transactions,
                ])->to('after_content');
            }
        }
    }

    public function setupCreateOperation()
    {
           if (!backpack_user()->hasRole('Super admin') && !backpack_user()->can('Add sub lawyer'))
            $this->crud->denyAccess('create');

        $this->addUserFields();
        $this->crud->setValidation(UserRequest::class);
    }

    protected function addUserFields()
    {
        // if(Route::is('user.edit')){
        //     $user = User::where('id',request()->route('id'))->first();
        // }
        $role_permissions = \App\Models\Role::with('permissions')->get()->toArray();

        $rps_min = [];
        foreach ($role_permissions as $rp) {
            $permissions = [];
            foreach ($rp['permissions'] as $permission) {
                array_push($permissions, $permission['id']);
            }

            $rps_min[$rp['id']] = $permissions;
        }

        $this->crud->addFields([
            [
                'name'  => 'name',
                'label' => trans('backpack::permissionmanager.name'),
                'type'  => 'text',
            ],
            [
                'name'  => 'email',
                'label' => trans('backpack::permissionmanager.email'),
                'type'  => 'email',
            ],
            [
                'name'  => 'phone_number',
                'label' => 'Mobile',
                'type'  => 'text',
            ],
            [
                'name'  => 'password',
                'label' => trans('backpack::permissionmanager.password'),
                'type'  => 'password',
            ],
            [
                'name'  => 'password_confirmation',
                'label' => trans('backpack::permissionmanager.password_confirmation'),
                'type'  => 'password',
            ],
            [
                'name'  => 'address',
                'label' => trans('Address'),
                'type'  => 'textarea',
            ],
            [
                'name'      => 'avatar',
                'label'     => 'Profile image',
                'type'      => 'upload',
                'withFiles' => true
            ],
            [
                'type'          => "relationship",
                'name'          => 'transactions',
                'attribute'     => "id",
                'placeholder'     => 'Add transactions',
                'ajax' => true,
                'inline_create' => [
                    'entity' => 'transaction',
                    'include_main_form_fields' => ['user_id'],
                ],
                'label' => "Transactions"
            ],
        ]);




        if (backpack_user()->hasRole('Super admin')  || backpack_user()->hasRole('Premium') || backpack_user()->hasRole('Super Premium')) {

            $lawyer_roles = \App\Models\Role::where('type', 'regular_user')->pluck('name')->toArray();

            $lawyers = \App\Models\User::whereHas('roles', function ($q) use ($lawyer_roles) {
                $q->whereIn('name', $lawyer_roles);
            })->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'Sub lawyer');
            })->pluck('name', 'id');

                // dd($lawyers);
            $this->crud->addField([
                'label' => "Senior Lawyer",
                'name' => "senior_lawyer_id",
                'type' => 'select2_from_array',
                'options' => $lawyers,
                'default' => request()->senior_lawyer_id,
                'hint' => "Only required if user role is sub lawyer.",
                'attributes' => [
        'id' => 'senior_lawyer_select' // Add an ID to target it in JavaScript
                  ]
            ]);

            $this->crud->addField(array_merge([
                // two interconnected entities
                'label'             => 'Plans and permissions',
                'field_unique_name' => 'user_role_permission',
                'type'              => 'checklist_dependency_rp',
                'name'              => 'roles,permissions',
                'subfields'         => [
                    'primary' => [
                        'label'            => 'Plans',
                        'name'             => 'roles', // the method that defines the relationship in your Model
                        'entity'           => 'roles', // the method that defines the relationship in your Model
                        'entity_secondary' => 'permissions', // the method that defines the relationship in your Model
                        'attribute'        => 'name', // foreign key attribute that is shown to user
                        'model'            => \App\Models\Role::class, // foreign key model
                        'pivot'            => true, // on create&update, do you need to add/delete pivot table entries?]
                        'number_columns'   => 3, //can be 1,2,3,4,6
                    ],
                    'secondary' => [
                        'label'          => 'Permissions',
                        'name'           => 'permissions', // the method that defines the relationship in your Model
                        'entity'         => 'permissions', // the method that defines the relationship in your Model
                        'entity_primary' => 'roles', // the method that defines the relationship in your Model
                        'attribute'      => 'name', // foreign key attribute that is shown to user
                        'model'          => config('permission.models.permission'), // foreign key model
                        'pivot'          => true, // on create&update, do you need to add/delete pivot table entries?]
                        'number_columns' => 3, //can be 1,2,3,4,6
                    ],
                ],
                'wrapper' => [
                    'id' => 'senior_lawyer_select_2' // Add an ID to target it in JavaScript
                  ]
            ], $this->crud->getCurrentOperation() == 'create' ? [
                'value'             => [
                    \App\Models\Role::where('id', request()->role),
                    \App\Models\Permission::where('id', 0),
                ],
            ] : []));
        } else {

            $this->crud->addField([

                'name' => 'permissions',
                'label' => trans('backpack::permissionmanager.permission_plural'),
                'type' => 'checklist_permissions',
                'entity' => 'permissions', // Relationship method
                'attribute' => 'name',
                'model' => config('permission.models.permission'),
                'pivot' => true,
            ]);

//  Just to make permissions in work  //
            $this->crud->addField([
                'label' => "Nothing",
                'name' => "nothing",
                'type' => 'select2_from_array',
                'options' => [1,2,3],
                'wrapper'=>[
                    'class' => 'd-none'
                ]
            ]);
//  Just to make permissions in work  //

        }
    }

    public function store()
    {
        $yesSublawyerFromAdmin = false;
        // dd($this->crud->getRequest()->request);

        // If the current user has the 'regular_user' role, add the 'senior_lawyer_id' and set it to the current user's ID
        if (backpack_user()->hasRole(\App\Models\Role::where('type', 'regular_user')->pluck('name')->toArray()) && !backpack_user()  ->hasRole(\App\Models\Role::where('type', 'sub_lawyer')->pluck('name')->toArray())) {

            $this->crud->addField(['type' => 'hidden', 'name' => 'senior_lawyer_id']);
            $this->crud->getRequest()->request->add(['senior_lawyer_id' => backpack_user()->id]);
        } else {
            // If the selected roles are not from 'sub_lawyer', remove the 'senior_lawyer_id' field
            if($this->crud->getRequest()->senior_lawyer_id != null){
                $yesSublawyerFromAdmin = true;

                $this->crud->addField(['type' => 'hidden', 'name' => 'senior_lawyer_id']);
                $this->crud->getRequest()->request->add(['senior_lawyer_id' => $this->crud->getRequest()->senior_lawyer_id]);

            }else{
                $this->crud->getRequest()->request->remove('senior_lawyer_id');
            }
            // if (array_diff($this->crud->getRequest()->roles, \App\Models\Role::where('type', 'sub_lawyer')->pluck('id')->toArray())) {
            //     dd('yes');
            //     $this->crud->getRequest()->request->remove('senior_lawyer_id');
            // }
        }

        // Add plan expiry date if there are transactions
        if ($this->crud->getRequest()->transactions) {
            $transaction = \App\Models\Transaction::find($this->crud->getRequest()->transactions)->sortByDesc('created_at')->first();
            $plan_validity_map = [
                'Monthly' => 30,
                'Quaterly' => 120,
                'Yearly' => 365,
            ];

            $this->crud->addField(['type' => 'hidden', 'name' => 'plan_expiry']);
            $this->crud->getRequest()->request->add(['plan_expiry' => now()->addDays($plan_validity_map[$transaction->plan_validity] ?? 0)]);
        }

        // Add the selected role_id manually before storing
        $selected_role_id = $this->crud->getRequest()->roles[0] ?? null; // Assuming 'roles' is a single-select field (get the first selected role)

        if ($selected_role_id) {

            $this->crud->addField(['type' => 'hidden', 'name' => 'role_id']);
            $this->crud->getRequest()->request->add(['role_id' => $selected_role_id]);
        }
        // dd($this->crud->getRequest()->request);

        // Perform the default store behavior (save the request data)
        $response = $this->traitStore();

        // If the user is a 'regular_user', assign them the 'Sub lawyer' role after saving
        if (backpack_user()->hasRole(\App\Models\Role::where('type', 'regular_user')->pluck('name')->toArray()) && !backpack_user()  ->hasRole(\App\Models\Role::where('type', 'sub_lawyer')->pluck('name')->toArray())) {
            $this->crud->getCurrentEntry()->assignRole('Sub lawyer');
        }

        if ($yesSublawyerFromAdmin) {
            $this->crud->getCurrentEntry()->assignRole('Sub lawyer');
        }

        // Return the response after saving
        return $response;
    }

}
