@extends(backpack_view('blank'))

@php
    $breadcrumbs = [
        trans('backpack::crud.admin') => url(config('backpack.base.route_prefix'), 'dashboard'),
        trans('backpack::base.my_account') => false,
    ];
@endphp

@section('header')
    <section class="content-header">
        <div class="container-fluid mb-3">
            <h1>{{ trans('backpack::base.my_account') }}</h1>
        </div>
    </section>
@endsection

@section('content')
    <div class="row">

        @if (session('success'))
            <div class="col-lg-8">
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if ($errors->count())
            <div class="col-lg-8">
                <div class="alert alert-danger">
                    <ul class="mb-1">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- USER INFORMATION --}}
        <div class="col-lg-8 mb-4">

            <div class="card">

                <div class="card-header">
                    <h3 class="card-title">User information</h3>
                </div>

                <div class="card-body backpack-profile-form bold-labels">
                    @php
                        $user = backpack_user();
                        $transaction = \App\Models\Transaction::where('user_id', $user->id)->first();
                    @endphp
                    <p><strong>Name: </strong>{{ $user->name }}</p>
                    <p><strong>Email: </strong>{{ $user->email }}</p>
                    <p><strong>Mobile number: </strong>{{ $user->phone_number }}</p>
                    <p><strong>Plan: </strong>{{ $user->roles->pluck('name')->implode(', ') }}</p>
                    <p><strong>Address: </strong>{{ $user->address }}</p>

                    @if ($transaction)
                        <p><strong>Plan name: </strong>{{ $transaction->plan->name }}</p>
                        <p><strong>Plan amount: </strong>{{ $transaction->charged_amount }}</p>
                        <p><strong>Plan buy date: </strong>{{ $transaction->created_at }}</p>
                        <p><strong>Plan validity: </strong>{{ $transaction->plan_validity }}</p>
                    @endif

                    <p><strong>Permissions: </strong>{{ $user->getAllPermissions()->pluck('name')->implode(', ') }}</p>
                    @role('Sub lawyer')
                        <p><strong>Senior lawyer: </strong>{{ \App\Models\User::find($user->senior_lawyer_id)->name ?? '' }}
                        </p>
                    @endrole

                </div>
            </div>
        </div>

        {{-- Sub lawyers --}}

        @hasanyrole(\App\Models\Role::where('type', 'regular_user')->pluck('name')->toArray())
            @php
                $sub_lawyers = \App\Models\User::where('senior_lawyer_id', $user->id)->get();

                $disp_sub_lawyers = $sub_lawyers->map(function ($s_l) {
                    return [
                        'name' => $s_l->name,
                        'email' => $s_l->email,
                        'permissions' => $s_l->getAllPermissions()->pluck('name')->implode(', '),
                    ];
                });
            @endphp
            @include(backpack_view('widgets.table'), [
                'widget' => [
                    'wrapper' => ['class' => 'col-lg-8'],
                    'title' => 'Sub Laywers',
                    'title_link' => [
                        'link' => backpack_url('user?senior_lawyer_id=' . $user->id),
                        'label' => 'View in detail',
                    ],
                    'columns' => [
                        'name' => 'Name',
                        'email' => 'Email',
                        'permissions' => 'Permissions',
                    ],
                    'data' => $disp_sub_lawyers,
                ],
            ])

            @php
                $transactions = \App\Models\Transaction::where('user_id', $user->id)->get();

                $disp_transactions = $transactions->map(function ($txn) {
                    return [
                        'name' => $txn->plan_name,
                        'payment_method' => $txn->payment_method,
                        'charged_amount' => $txn->charged_amount,
                        'tracking' => $txn->tracking,
                        'plan_validity' => $txn->plan_validity,
                    ];
                });
            @endphp
            @include(backpack_view('widgets.table'), [
                'widget' => [
                    'wrapper' => ['class' => 'col-lg-8'],
                    'title' => 'Transactions',
                    'title_link' => [
                        'link' => backpack_url('transaction?user_id=' . $user->id),
                        'label' => 'View in detail',
                    ],
                    'columns' => [
                        'name' => 'Plan name',
                        'payment_method' => 'Payment method',
                        'charged_amount' => 'Charged',
                        'tracking' => 'Tracking',
                        'plan_validity' => 'Plan validity',
                    ],
                    'data' => $disp_transactions,
                ],
            ])
        @endhasanyrole


        {{-- UPDATE INFO FORM --}}
        <div class="col-lg-8 mb-4">
            <form class="form" action="{{ route('backpack.account.info.store') }}" method="post">

                {!! csrf_field() !!}

                <div class="card">

                    <div class="card-header">
                        <h3 class="card-title">{{ trans('backpack::base.update_account_info') }}</h3>
                    </div>

                    <div class="card-body backpack-profile-form bold-labels">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                @php
                                    $label = trans('backpack::base.name');
                                    $field = 'name';
                                @endphp
                                <label class="required">{{ $label }}</label>
                                <input required class="form-control" type="text" name="{{ $field }}"
                                    value="{{ old($field) ? old($field) : $user->$field }}">
                            </div>

                            <div class="col-md-6 form-group">
                                @php
                                    $label = trans(
                                        'backpack::base.' .
                                            strtolower(config('backpack.base.authentication_column_name')),
                                    );
                                    $field = backpack_authentication_column();
                                @endphp
                                <label class="required">{{ $label }}</label>
                                <input required class="form-control"
                                    type="{{ backpack_authentication_column() == backpack_email_column() ? 'email' : 'text' }}"
                                    name="{{ $field }}" value="{{ old($field) ? old($field) : $user->$field }}">
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-success"><i class="la la-save"></i>
                            {{ trans('backpack::base.save') }}</button>
                        <a href="{{ backpack_url() }}" class="btn">{{ trans('backpack::base.cancel') }}</a>
                    </div>
                </div>

            </form>
        </div>

        {{-- CHANGE PASSWORD FORM --}}
        <div class="col-lg-8 mb-4">
            <form class="form" action="{{ route('backpack.account.password') }}" method="post">

                {!! csrf_field() !!}

                <div class="card padding-10">

                    <div class="card-header">
                        <h3 class="card-title">{{ trans('backpack::base.change_password') }}</h3>
                    </div>

                    <div class="card-body backpack-profile-form bold-labels">
                        <div class="row">
                            <div class="col-md-4 form-group">
                                @php
                                    $label = trans('backpack::base.old_password');
                                    $field = 'old_password';
                                @endphp
                                <label class="required">{{ $label }}</label>
                                <input autocomplete="new-password" required class="form-control" type="password"
                                    name="{{ $field }}" id="{{ $field }}" value="">
                            </div>

                            <div class="col-md-4 form-group">
                                @php
                                    $label = trans('backpack::base.new_password');
                                    $field = 'new_password';
                                @endphp
                                <label class="required">{{ $label }}</label>
                                <input autocomplete="new-password" required class="form-control" type="password"
                                    name="{{ $field }}" id="{{ $field }}" value="">
                            </div>

                            <div class="col-md-4 form-group">
                                @php
                                    $label = trans('backpack::base.confirm_password');
                                    $field = 'confirm_password';
                                @endphp
                                <label class="required">{{ $label }}</label>
                                <input autocomplete="new-password" required class="form-control" type="password"
                                    name="{{ $field }}" id="{{ $field }}" value="">
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-success"><i class="la la-save"></i>
                            {{ trans('backpack::base.change_password') }}</button>
                        <a href="{{ backpack_url() }}" class="btn">{{ trans('backpack::base.cancel') }}</a>
                    </div>

                </div>

            </form>
        </div>

    </div>
@endsection


@hasanyrole(\App\Models\Role::where('type', 'regular_user')->pluck('name')->toArray())
    @section('after_scripts')
        <script src="{{ asset('/assets/js/load_datatable_lawyer.js') }}"></script>
    @endsection
@endhasanyrole
