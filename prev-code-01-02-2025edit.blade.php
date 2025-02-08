@extends(backpack_view('blank'))

@php
  $defaultBreadcrumbs = [
    trans('backpack::crud.admin') => backpack_url('dashboard'),
    $crud->entity_name_plural => url($crud->route),
    trans('backpack::crud.edit') => false,
  ];

  // if breadcrumbs aren't defined in the CrudController, use the default breadcrumbs
  $breadcrumbs = $breadcrumbs ?? $defaultBreadcrumbs;
@endphp

@section('header')
    <section class="header-operation container-fluid animated fadeIn d-flex mb-2 align-items-baseline d-print-none" bp-section="page-header">
        <h1 class="text-capitalize mb-0" bp-section="page-heading">
            @if ( "all case" === $crud->entity_name )
                @php $crud->entity_name = "Case" @endphp
            @endif

            @if ( "Case" === $crud->entity_name )
                {!! $crud->getSubheading() ?? trans('backpack::crud.edit').' '.$crud->entity_name !!}.</p>
            @else
                {!! $crud->getHeading() ?? $crud->entity_name_plural !!}
            @endif
        </h1>
        <p class="ms-2 ml-2 mb-0" bp-section="page-subheading">
            @if ( "Case" !== $crud->entity_name )
                {!! $crud->getSubheading() ?? trans('backpack::crud.edit').' '.$crud->entity_name !!}.
            @endif
        </p>
        @if ($crud->hasAccess('list'))
            <p class="mb-0 ms-2 ml-2" bp-section="page-subheading-back-button">
                <small><a href="{{ url($crud->route) }}" class="d-print-none font-sm"><i class="la la-angle-double-{{ config('backpack.base.html_direction') == 'rtl' ? 'right' : 'left' }}"></i> {{ trans('backpack::crud.back_to_all') }} <span>{{ $crud->entity_name_plural }}</span></a></small>
            </p>
        @endif
    </section>
@endsection

@section('content')
<div class="row" bp-section="crud-operation-update">
	<div class="{{ $crud->getEditContentClass() }}">
		{{-- Default box --}}

		@include('crud::inc.grouped_errors')

		  <form method="post"
		  		action="{{ url($crud->route.'/'.$entry->getKey()) }}"
				@if ($crud->hasUploadFields('update', $entry->getKey()))
				enctype="multipart/form-data"
				@endif
		  		>
		  {!! csrf_field() !!}
		  {!! method_field('PUT') !!}

		  	@if ($crud->model->translationEnabled())
		    <div class="mb-2 text-right">
		    	{{-- Single button --}}
				<div class="btn-group">
				  <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				    {{trans('backpack::crud.language')}}: {{ $crud->model->getAvailableLocales()[request()->input('_locale')?request()->input('_locale'):App::getLocale()] }} &nbsp; <span class="caret"></span>
				  </button>
				  <ul class="dropdown-menu">
				  	@foreach ($crud->model->getAvailableLocales() as $key => $locale)
					  	<a class="dropdown-item" href="{{ url($crud->route.'/'.$entry->getKey().'/edit') }}?_locale={{ $key }}">{{ $locale }}</a>
				  	@endforeach
				  </ul>
				</div>
		    </div>
		    @endif
		      {{-- load the view from the application if it exists, otherwise load the one in the package --}}
		      @if(view()->exists('vendor.backpack.crud.form_content'))
		      	@include('vendor.backpack.crud.form_content', ['fields' => $crud->fields(), 'action' => 'edit'])
		      @else
		      	@include('crud::form_content', ['fields' => $crud->fields(), 'action' => 'edit'])
              @endif
              {{-- This makes sure that all field assets are loaded. --}}
            <div class="d-none" id="parentLoadedAssets">{{ json_encode(Basset::loaded()) }}</div>
            @include('crud::inc.form_save_buttons')
		  </form>
	</div>
</div>
</div>
@endsection

<style>
	.container-repeatable-elements .controls {
		left: 13px;
		top: unset;
	}

	.move-element-up,
	.move-element-down {
		display: none !important;
	}
</style>

@push('after_scripts')
    @basset('https://unpkg.com/urijs@1.19.11/src/URI.min.js')
    <script>
        jQuery(document).ready(function($) {

            $('#state-dropdown').on('change', function(e) {
                var state_id = $(this).val(),
                    district_dropdown = $('#district-dropdown');

                if (state_id) {

                    $.ajax({
                        url: '{{ route('cases.getDistricts') }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            state_id: state_id
                        },
                        success: function(response) {
                            district_dropdown.empty(); // Clear previous options
                            district_dropdown.append(
                                '<option value="">Select District</option>');
                            $.each(response, function(key, value) {
                                district_dropdown.append('<option value="' + key +
                                    '">' + value + '</option>');
                            });

                            // Update the Select2 element
                            district_dropdown.select2({
                                theme: "bootstrap",
                                placeholder: 'Select District'
                            });
                        },
                    });
                } else {
                    districtDropdown.innerHTML = '';
                }
            });

        });



        var seniorLawyerId = <?php echo isset($crud->fields()['senior_lawyer_id']['value'])
    ? json_encode($crud->fields()['senior_lawyer_id']['value'])
    : null; ?>;

        if(seniorLawyerId != null){
            $('#senior_lawyer_select').prop('disabled', true).addClass('text-dark');

            $('#senior_lawyer_select_2').remove(); // Remove the old field if it exists
            $('#fhdgj').html('');
            var checklistHtml = '';
            var m1 = '';
            var m2 = '';
            var m3 = '';

            $.ajax({
                url: '/get-senior-lawyer-roles/' + seniorLawyerId, // Fetch data from this route
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

                    checklistHtml = '<div class="row h-50" id="fhdgj"><p>Permissions</p>';

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

        $('#ghsagjds .row').addClass('h-25');


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

                    checklistHtml = '<div class="row h-25" id="fhdgj"><p>Permissions</p>';

                    $.each(m1, function (index, permission) {
                        var subitems = m2.filter(function (subitem) {
                            return subitem.parent_id === permission.id;
                        });

                        checklistHtml += `<div class="dropdown col-md-4 col-sm-3 col-xs-2 mr-2 mb-1">`;

                        if (subitems.length) {
                            checklistHtml += `
                                <button class="btn btn-primary dropdown-toggle w-100 bjhkhskdvh"
                                        type="button" id="dropdownMenuButton${permission.id}"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                    <div class="checkbox checkbox-main">
                                        <input name="permissions[]" id="mainitem${permission.id}" type="checkbox" value="${permission.id}">

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

// $(document).on('click', '.checkbox-main input[type="checkbox"]', function () {
//     // alert('yes');
//     var isChecked = $(this).prop('checked'); // Check if main checkbox is checked
//     var dropdownMenu = $(this).closest('.dropdown').find('.dropdown-menu');

//     // Check/uncheck all checkboxes inside the dropdown
//     dropdownMenu.find('input[type="checkbox"]').prop('checked', isChecked);
// });

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



// $(document).on('click', '.sub-checkbox', function (event) {
//     event.stopPropagation(); // Prevent dropdown from toggling when clicking sub-checkbox
//     var dropdown = $(this).closest('.dropdown');
//     var allSubCheckboxes = dropdown.find('.dropdown-menu input[type="checkbox"]');
//     var checkedSubCheckboxes = dropdown.find('.dropdown-menu input[type="checkbox"]:checked');

//     // If all sub-checkboxes are checked, check the main checkbox
//     dropdown.find('.main-checkbox').prop('checked', allSubCheckboxes.length === checkedSubCheckboxes.length);
// });

// $(document).on('click', '.sub-sub-checkbox', function (event) {
//     event.stopPropagation(); // Prevent dropdown from toggling when clicking sub-sub-checkbox
//     var dropdown = $(this).closest('.dropdown');
//     var allSubSubCheckboxes = dropdown.find('.dropdown-menu input[type="checkbox"]');
//     var checkedSubSubCheckboxes = dropdown.find('.dropdown-menu input[type="checkbox"]:checked');

//     // If all sub-sub-checkboxes are checked, check the parent sub-checkbox
//     dropdown.find('.sub-checkbox').prop('checked', allSubSubCheckboxes.length === checkedSubSubCheckboxes.length);

//     // If all sub-checkboxes are checked, check the main checkbox
//     var allSubCheckboxes = dropdown.find('.dropdown-menu .sub-checkbox');
//     var checkedSubCheckboxes = dropdown.find('.dropdown-menu .sub-checkbox:checked');
//     dropdown.find('.main-checkbox').prop('checked', allSubCheckboxes.length === checkedSubCheckboxes.length);
// });


    </script>
@endpush
