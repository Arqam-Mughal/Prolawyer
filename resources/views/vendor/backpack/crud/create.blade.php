@extends(backpack_view('blank'))

@php
  $defaultBreadcrumbs = [
    trans('backpack::crud.admin') => url(config('backpack.base.route_prefix'), 'dashboard'),
    $crud->entity_name_plural => url($crud->route),
    trans('backpack::crud.add') => false,
  ];

  // if breadcrumbs aren't defined in the CrudController, use the default breadcrumbs
  $breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;
@endphp

@section('header')
    <section class="header-operation container-fluid animated fadeIn d-flex mb-2 align-items-baseline d-print-none" bp-section="page-header">
        <h1 class="text-capitalize mb-0" bp-section="page-heading">
            @php $current_route = Route::currentRouteName(); @endphp
            @if (str_contains( $current_route, 'case-model-' ))
                Add Case
            @else
                {!! $crud->getHeading() ?? $crud->entity_name_plural !!}
            @endif
        </h1>
        <p class="ms-2 ml-2 mb-0" bp-section="page-subheading">
            @if (str_contains( $current_route, 'case-model-' ))

            @else
                {!! $crud->getSubheading() ?? trans('backpack::crud.add').' '.$crud->entity_name !!}.
            @endif
        </p>
        @if ($crud->hasAccess('list'))
            <p class="mb-0 ms-2 ml-2" bp-section="page-subheading-back-button">
                <small>
                    <a href="{{ url($crud->route) }}" class="d-print-none font-sm">
                        <span><i class="la la-angle-double-{{ config('backpack.base.html_direction') == 'rtl' ? 'right' : 'left' }}"></i>
                            @if (str_contains( $current_route, 'case-model-' ))
                                Back to
                            @else
                                {{ trans('backpack::crud.back_to_all') }}
                            @endif
                        <span>
                            {{ $crud->entity_name_plural }}
                        </span></span>
                    </a>
                </small>
            </p>
        @endif
    </section>
@endsection

@section('content')

    @php $current_route = Route::currentRouteName(); @endphp
    @if (str_contains( $current_route, 'case-model-' ))
        <div class="row mb-2 align-items-center">
            <div class="col-sm-4">
                <a href="" class="btn btn-primary btn-tabs-create" value="cnr-number" data-style="zoom-in">
                    CNR Number
                </a>
            </div>
            <div class="col-sm-4">
                <a href="" class="btn btn-secondary btn-tabs-create" value="case-number" data-style="zoom-in">
                    Case Number
                </a>
            </div>
            <div class="col-sm-4">
                <a href="" class="btn btn-secondary btn-tabs-create" value="custom-case" data-style="zoom-in">
                    Custom Case
                </a>
            </div>
        </div>
        @include('crud::create_case.create_cnr_case')
        @include('crud::create_case.create_case_number_case')
        @include('crud::create_case.create_custom_case')
    @else
        <div class="row" bp-section="crud-operation-create">
            <div class="{{ $crud->getCreateContentClass() }}">
                {{-- Default box --}}

                @include('crud::inc.grouped_errors')

                <form method="post"
                        action="{{ url($crud->route) }}"
                        @if ($crud->hasUploadFields('create'))
                        enctype="multipart/form-data"
                        @endif
                        >
                    {!! csrf_field() !!}
                    {{-- load the view from the application if it exists, otherwise load the one in the package --}}
                    @if(view()->exists('vendor.backpack.crud.form_content'))
                        @include('vendor.backpack.crud.form_content', [ 'fields' => $crud->fields(), 'action' => 'create' ])
                    @else
                        @include('crud::form_content', [ 'fields' => $crud->fields(), 'action' => 'create' ])
                    @endif
                        {{-- This makes sure that all field assets are loaded. --}}
                        <div class="d-none" id="parentLoadedAssets">{{ json_encode(Basset::loaded()) }}</div>
                    @include('crud::inc.form_save_buttons')
                </form>
            </div>
        </div>
    @endif

@endsection


@push('after_scripts')
    @basset('https://unpkg.com/urijs@1.19.11/src/URI.min.js')
    <script>
       $(document).ready(function () {
    var seniorLawyerField = $('#senior_lawyer_select_2').clone(); // Clone the field


  var urlParams = new URLSearchParams(window.location.search);
  var seniorLawyId = urlParams.get('senior_lawyer_id');
  if(seniorLawyId){
    functiontoshow(seniorLawyId)  }

    $('#senior_lawyer_select').on('change', function () {
        var seniorLawyerId = $(this).val();

        if (seniorLawyerId) {

            functiontoshow(seniorLawyerId);

        } else {
            location.reload();

            $(this).closest('.form-group').after(seniorLawyerField); // Revert to original field
            $('#fhdgj').html('');
        }
    });



});


function functiontoshow(seniorLawrid){
    $('#senior_lawyer_select_2').remove(); // Remove the old field if it exists
            $('#fhdgj').html('');
            var checklistHtml = '';
            var m1 = '';
            var m2 = '';
            var m3 = '';

            $.ajax({
                url: '/get-senior-lawyer-roles/' + seniorLawrid, // Fetch data from this route
                type: 'GET',
                success: function (response) {
                    console.log(response);

                    m1 = response.permissions.m1 || [];
                    m2 = response.permissions.m2 || [];
                    m3 = response.permissions.m3 || [];

                    if (!Array.isArray(m2)) {
                        console.error('m2 is not an array:', m2);
                        return;
                    }

                    checklistHtml = '<div class="row" id="fhdgj"><p>Permissions</p>';

                    $.each(m1, function (index, permission) {
                        var subitems = m2.filter(function (subitem) {
                            return subitem.parent_id === permission.id;
                        });

                        checklistHtml += `<div class="dropdown col-md-4 col-sm-3 col-xs-2 mr-2 mb-1">`;

                        if (subitems.length) {
                            checklistHtml += `
                                <button class="btn btn-primary dropdown-toggle w-100 hskdhksd"
                                        type="button" id="dropdownMenuButton${permission.id}"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                    <div class="checkbox checkbox-main">
                                        <input name="permissions[]" id="mainitem${permission.id}" type="checkbox" value="${permission.id}" class="main-checkbox">
                                        <label class="font-weight-normal" for="mainitem${permission.id}">
                                            ${permission.name}
                                        </label>
                                    </div>
                                </button>
                                <ul class="dropdown-menu w-100" style="max-height: 500px; overflow-y: auto;" aria-labelledby="dropdownMenuButton${permission.id}">`;

                            $.each(subitems, function (subindex, subitem) {
                                var sub_subitems = Array.isArray(m3) ? m3.filter(function (sub_subitem) {
                                    return sub_subitem.parent_id === subitem.id;
                                }) : [];

                                checklistHtml += `
                                    <li class="dropdown-item dropdown">
                                        <label class="font-weight-normal w-100" for="subitem${subitem.id}">
                                            <input name="permissions[]" id="subitem${subitem.id}" type="checkbox" value="${subitem.id}">
                                            ${subitem.name}
                                        </label>`;

                                if (sub_subitems.length) {
                                    checklistHtml += `
                                        <button class="btn btn-sm dropdown-toggle" type="button" id="dropdownMenuButton${subitem.id}" data-bs-toggle="dropdown" aria-expanded="false" onclick="event.stopPropagation()">
                                            ▶
                                        </button>
                                        <ul class="dropdown-menu bg-info text-white w-100" aria-labelledby="dropdownMenuButton${subitem.id}" style="max-height: 500px; overflow-y: auto;">`;

                                    $.each(sub_subitems, function (sub_subindex, sub_subitem) {
                                        checklistHtml += `
                                            <li class="dropdown-item">
                                                <label class="font-weight-normal w-100" for="sub_subitem${sub_subitem.id}">
                                                    <input id="sub_subitem${sub_subitem.id}" type="checkbox" value="${sub_subitem.id}">
                                                    ${sub_subitem.name}
                                                </label>
                                            </li>`;
                                    });

                                    checklistHtml += '</ul>';
                                }

                                checklistHtml += `</li>`;
                            });

                            checklistHtml += '</ul>';
                        } else {
                            checklistHtml += `
                                <div class="bg-primary text-white justify-content-center text-center h-100 py-2 d-flex align-items-center" style="border-radius: 4px;">
                                    <label class="font-weight-normal w-100" for="subitem${permission.id}" style=" font-weight: bold; ">
                                        <input id="subitem${permission.id}" type="checkbox" value="${permission.id}">
                                        ${permission.name}
                                    </label>
                                </div>`;
                        }

                        checklistHtml += '</div>';
                    });

                    checklistHtml += '</div>'; // Close the row div

                    $('#senior_lawyer_select').closest('.form-group').after(checklistHtml);

                    $('.dropdown-toggle').dropdown();
                }
            });
}

$(document).on('click','.hskdhksd',function(event) {
    // alert('yes');
    // Check if the clicked element is not .main-checkbox or its descendants
    event.preventDefault();

    if (!$(event.target).closest('.main-checkbox').length) {
        // Reset the clicked flag to allow the next click on .main-checkbox
        $('.main-checkbox').data('clicked', false);

        // Add dropdown classes and attributes
        $('.main-checkbox').parent().parent().addClass('dropdown-toggle').attr('data-bs-toggle', 'dropdown');
    }

    // Check if the event is already being triggered to prevent multiple clicks
    var self = $(this);
    if (!self.data('clicked')) {
        self.data('clicked', true);  // Mark that a click is being triggered

        setTimeout(function() {
            self.click();  // Trigger the click event again
        }, 0);
    }
});

$(document).on('click', '.main-checkbox', function (e) {
    // Prevent default behavior if necessary
    e.preventDefault();

    // Add a flag to ensure the click only happens once
    if (!$(this).data('clicked')) {
        $(this).data('clicked', true);  // Mark that this element has been clicked

        // Remove dropdown classes and attributes
        $(this).parent().parent().removeClass('dropdown-toggle').removeAttr('data-bs-toggle').removeAttr('id').removeClass('show');

        // Get the checkbox state
        var isChecked = $(this).prop('checked');

        // Find and set the checkboxes in the dropdown menu to the same state
        var dropdownMenu = $(this).closest('.dropdown').find('.dropdown-menu');
        dropdownMenu.find('input[type="checkbox"]').prop('checked', isChecked);

        // Simulate a second click on the checkbox to toggle it
        var self = $(this);
        setTimeout(function() {
            self.click();  // Trigger the click event again
        }, 0);
    }
});

    </script>

@endpush



