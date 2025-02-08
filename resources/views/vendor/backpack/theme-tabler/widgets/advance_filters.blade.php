{{-- Only used in dashboard  --}}
@includeWhen(!empty($widget['wrapper']), backpack_view('widgets.inc.wrapper_start'))

<div class="mb-4">
    <div class="card">
        <div class="card-header">
            <h2 style="margin: 0; margin-right: 10px;">Filters</h2>

            <a href="#" id="remove_filters_button"
                class="nav-link {{ count(Request::input()) != 0 ? '' : 'invisible' }}"><i class="la la-eraser"></i>
                Reset filters</a>
        </div>

        @php
            $crud = app()->make('crud');
            $crud->setModel(\App\Models\Worklist::class);

            $crud->addFields([
                [
                    'name' => 'separator1',
                    'type' => 'custom_html',
                    'value' => '<h3 class="text-center m-0">SEARCH BY CASE DETAILS</h3>',
                    'wrapper' => [
                        'class' => 'form-group col-sm-12 mt-5 mb-2',
                    ],
                ],
                [
                    'name' => 'cnr_no',
                    'type' => 'select2_from_ajax',
                    'label' => 'CNR No',
                    'model' => 'App\Models\CaseModel',
                    'attribute' => 'cnr_no',
                    'data_source' => route('advance-search-cnr-no'),
                    'placeholder' => 'Select CNR No',
                    'minimum_input_length' => 2,
                    'wrapper' => [
                        'class' => 'col-md-2',
                    ],
                    'attributes' => [
                        'class' => 'select2_from_ajax form-control filter-advance-search',
                    ],
                ],
                [
                    'name' => 'case_type',
                    'type' => 'select2_from_ajax',
                    'label' => 'Case Type',
                    'model' => 'App\Models\CaseModel',
                    'attribute' => 'Brief_no',
                    'data_source' => route('advance-search-case-type'),
                    'placeholder' => 'Select Case Type',
                    'minimum_input_length' => 2,
                    'wrapper' => [
                        'class' => 'col-md-2',
                    ],
                    'attributes' => [
                        'class' => 'select2_from_ajax form-control filter-advance-search',
                    ],
                ],
                [
                    'name' => 'case_no',
                    'type' => 'select2_from_ajax',
                    'label' => 'Case No',
                    'model' => 'App\Models\CaseModel',
                    'attribute' => 'case_no',
                    'data_source' => route('advance-search-case-no'),
                    'placeholder' => 'Select Case No',
                    'minimum_input_length' => 2,
                    'wrapper' => [
                        'class' => 'col-md-3',
                    ],
                    'attributes' => [
                        'class' => 'select2_from_ajax form-control filter-advance-search',
                    ],
                ],
                [
                    'name' => 'case_year',
                    'type' => 'date_picker',
                    'label' => 'Case Year',
                    'wrapper' => [
                        'class' => 'col-md-2',
                    ],
                    'date_picker_options' => [
                        'format' => 'yyyy',
                        'startView' => 'decade',
                        'minView' => 'decade',
                        'autoclose' => true,
                    ],
                    'attributes' => [
                        'class' => 'datepicker-input-custom form-control filter-advance-search',
                        'data-name' => 'case_year',
                    ],
                ],
                [
                    'name' => 'Brief_no',
                    'type' => 'select2_from_ajax',
                    'label' => 'Brief No',
                    'model' => 'App\Models\CaseModel',
                    'attribute' => 'Brief_no',
                    'data_source' => route('advance-search-brief-no'),
                    'placeholder' => 'Select Brief No',
                    'minimum_input_length' => 2,
                    'wrapper' => [
                        'class' => 'col-md-2',
                    ],
                    'attributes' => [
                        'class' => 'select2_from_ajax form-control filter-advance-search',
                    ],
                ],
                [
                    'name' => 'separator2',
                    'type' => 'custom_html',
                    'value' => '<h3 class="text-center m-0">DATE-WISE SEARCH</h3>',
                    'wrapper' => [
                        'class' => 'form-group col-sm-12 mt-5 mb-2',
                    ],
                ],
                [
                    'name' => 'gg1',
                    'type' => 'custom_html',
                    'value' => '',
                    'wrapper' => [
                        'class' => 'form-group col-md-2',
                    ],
                ],
                [
                    'name' => 'previous_start_date, previous_end_date',
                    'label' => 'Previous date',
                    'type' => 'date_range',
                    'wrapper' => [
                        'class' => 'col-md-4',
                    ],
                    'attributes' => [
                        'class' => 'daterange-input-custom form-control filter-advance-search',
                        'data-name' => 'previous_date',
                    ],
                ],
                [
                    'name' => 'next_start_date, next_end_date',
                    'label' => 'Next date',
                    'type' => 'date_range',
                    'wrapper' => [
                        'class' => 'col-md-4',
                    ],
                    'attributes' => [
                        'class' => 'daterange-input-custom form-control filter-advance-search',
                        'data-name' => 'next_date',
                    ],
                ],
                [
                    'name' => 'gg2',
                    'type' => 'custom_html',
                    'value' => '',
                    'wrapper' => [
                        'class' => 'form-group col-md-2',
                    ],
                ],
                [
                    'name' => 'separator3',
                    'type' => 'custom_html',
                    'value' => '<h3 class="text-center m-0">SEARCH BY CASE PARTIES AND ADVOCATES</h3>',
                    'wrapper' => [
                        'class' => 'form-group col-sm-12 mt-5 mb-2',
                    ],
                ],
                [
                    'label' => 'Petitioner',
                    'name' => 'petitioner',
                    'type' => 'select2_from_ajax',
                    'model' => 'App\Models\Petitioner',
                    'attribute' => 'petitioner',
                    'placeholder' => 'Select a petitioner',
                    'data_source' => route('advance-search-petitioner'),
                    'minimum_input_length' => 2,

                    'wrapper' => [
                        'class' => 'col-md-3',
                    ],
                    'attributes' => [
                        'class' => 'select2_from_ajax form-control filter-advance-search',
                        'style' => 'padding: 0 5px;',
                    ],
                ],
                [
                    'label' => 'Respondent',
                    'name' => 'respondent',
                    'type' => 'select2_from_ajax',
                    'model' => 'App\Models\Respondent',
                    'attribute' => 'respondent',
                    'placeholder' => 'Select a respondent',
                    'data_source' => route('advance-search-respondent'),
                    'minimum_input_length' => 2,

                    'wrapper' => [
                        'class' => 'col-md-3',
                    ],
                    'attributes' => [
                        'class' => 'select2_from_ajax form-control filter-advance-search',
                        'style' => 'padding: 0 5px;',
                    ],
                ],
                [
                    'label' => "Petitioner's Advocate",
                    'name' => 'petitioner_advocate',
                    'type' => 'select2_from_ajax',
                    'model' => 'App\Models\PetitionerAdvocate',
                    'attribute' => 'petitioner_advocate',
                    'placeholder' => "Select a petitioner's advocate",
                    'data_source' => route('advance-search-petitioner-advocate'),
                    'minimum_input_length' => 2,

                    'wrapper' => [
                        'class' => 'col-md-3',
                    ],
                    'attributes' => [
                        'class' => 'select2_from_ajax form-control filter-advance-search',
                        'style' => 'padding: 0 5px;',
                    ],
                ],
                [
                    'label' => "Respondent's Advocate",
                    'name' => 'respondent_advocate',
                    'type' => 'select2_from_ajax',
                    'model' => 'App\Models\RespondentAdvocate',
                    'attribute' => 'respondent_advocate',
                    'placeholder' => "Select a respondent's advocate",
                    'data_source' => route('advance-search-respondent-advocate'),
                    'minimum_input_length' => 2,

                    'wrapper' => [
                        'class' => 'col-md-3',
                    ],
                    'attributes' => [
                        'class' => 'select2_from_ajax form-control filter-advance-search',
                        'style' => 'padding: 0 5px;',
                    ],
                ],
                [
                    'name' => 'separator4',
                    'type' => 'custom_html',
                    'value' => '<h3 class="text-center m-0">SEARCH BY COURT AND STAGE</h3>',
                    'wrapper' => [
                        'class' => 'form-group col-sm-12 mt-5 mb-2',
                    ],
                ],
                [
                    'label' => 'Court/Bench',
                    'name' => 'court_bench',
                    'type' => 'select2_from_ajax',
                    'model' => 'App\Models\CaseModel',
                    'attribute' => 'court_bench',
                    'placeholder' => 'Select Court/Bench',
                    'data_source' => route('advance-search-court-bench'),
                    'minimum_input_length' => 2,
                    'wrapper' => [
                        'class' => 'col-md-4',
                    ],
                    'attributes' => [
                        'class' => 'select2_from_ajax form-control filter-advance-search',
                    ],
                ],
                [
                    'label' => 'Judge',
                    'name' => 'judge_name',
                    'type' => 'select2_from_ajax',
                    'model' => 'App\Models\CaseModel',
                    'attribute' => 'judge_name',
                    'placeholder' => 'Select Judge',
                    'data_source' => route('advance-search-judge'),
                    'minimum_input_length' => 2,
                    'wrapper' => [
                        'class' => 'col-md-4',
                    ],
                    'attributes' => [
                        'class' => 'select2_from_ajax form-control filter-advance-search',
                    ],
                ],
                [
                    'label' => 'Stage',
                    'name' => 'case_stage',
                    'type' => 'select2_from_ajax',
                    'model' => 'App\Models\CaseModel',
                    'attribute' => 'case_stage',
                    'placeholder' => 'Select Stage',
                    'data_source' => route('advance-search-case-stage'),
                    'minimum_input_length' => 2,
                    'wrapper' => [
                        'class' => 'col-md-4',
                    ],
                    'attributes' => [
                        'class' => 'select2_from_ajax form-control filter-advance-search',
                    ],
                ],
                [
                    'name' => 'separator5',
                    'type' => 'custom_html',
                    'value' => '<h3 class="text-center m-0">SEARCH BY CLIENT NAME</h3>',
                    'wrapper' => [
                        'class' => 'form-group col-sm-12 mt-5 mb-2',
                    ],
                ],
                [
                    'label' => 'Organization',
                    'name' => 'organization',
                    'type' => 'select2_from_ajax',
                    'model' => 'App\Models\Organization',
                    'attribute' => 'organization_name',
                    'placeholder' => 'Select Organization',
                    'data_source' => route('advance-search-organization'),
                    'minimum_input_length' => 2,
                    'wrapper' => [
                        'class' => 'col-md-4',
                    ],
                    'attributes' => [
                        'class' => 'select2_from_ajax form-control filter-advance-search',
                    ],
                ],
                [
                    'label' => 'Organization',
                    'name' => 'organization',
                    'type' => 'select2_from_ajax',
                    'model' => 'App\Models\Organization',
                    'attribute' => 'organization_name',
                    'placeholder' => 'Select Organization',
                    'data_source' => route('advance-search-organization'),
                    'minimum_input_length' => 2,
                    'wrapper' => [
                        'class' => 'col-md-4',
                    ],
                    'attributes' => [
                        'class' => 'select2_from_ajax form-control filter-advance-search',
                    ],
                ],
                [
                    'label' => 'Reference',
                    'name' => 'reference',
                    'type' => 'select2_from_ajax',
                    'model' => 'App\Models\RefTag',
                    'attribute' => 'name',
                    'placeholder' => 'Select reference',
                    'data_source' => route('advance-search-reference'),
                    'minimum_input_length' => 2,
                    'wrapper' => [
                        'class' => 'col-md-4',
                    ],
                    'attributes' => [
                        'class' => 'select2_from_ajax form-control filter-advance-search',
                    ],
                ],
                [
                    'label' => 'Individual',
                    'name' => 'client',
                    'type' => 'select2_from_ajax',
                    'model' => 'App\Models\Client',
                    'attribute' => 'name',
                    'placeholder' => 'Select client',
                    'data_source' => route('advance-search-client'),
                    'minimum_input_length' => 2,
                    'wrapper' => [
                        'class' => 'col-md-4',
                    ],
                    'attributes' => [
                        'class' => 'select2_from_ajax form-control filter-advance-search',
                    ],
                ],
                [
                    'name' => 'separator6',
                    'type' => 'custom_html',
                    'value' => '<h3 class="text-center m-0">SEARCH BY TAGS</h3>',
                    'wrapper' => [
                        'class' => 'form-group col-sm-6 mt-5 mb-2',
                    ],
                ],
                [
                    'name' => 'separator7',
                    'type' => 'custom_html',
                    'value' => '<h3 class="text-center m-0">SEARCH BY TEAM MEMBERS</h3>',
                    'wrapper' => [
                        'class' => 'form-group col-sm-6 mt-5 mb-2',
                    ],
                ],
                [
                    'name' => 'gg3',
                    'type' => 'custom_html',
                    'value' => '',
                    'wrapper' => [
                        'class' => 'form-group col-md-2',
                    ],
                ],
                [
                    'label' => 'Tags',
                    'name' => 'case_label',
                    'type' => 'select2_from_ajax',
                    'model' => 'App\Models\CaseLabel',
                    'attribute' => 'label',
                    'placeholder' => 'Select case label',
                    'data_source' => route('advance-search-case-label'),
                    'minimum_input_length' => 2,
                    'wrapper' => [
                        'class' => 'col-md-4',
                    ],
                    'attributes' => [
                        'class' => 'select2_from_ajax form-control filter-advance-search',
                    ],
                ],
                [
                    'label' => 'Team members',
                    'name' => 'client3',
                    'type' => 'select2_from_ajax',
                    'model' => 'App\Models\Client',
                    'attribute' => 'name',
                    'placeholder' => 'Select client',
                    'data_source' => route('advance-search-client'),
                    'minimum_input_length' => 2,
                    'wrapper' => [
                        'class' => 'col-md-4',
                    ],
                    'attributes' => [
                        'class' => 'select2_from_ajax form-control filter-advance-search',
                    ],
                ],
                [
                    'name' => 'gg4',
                    'type' => 'custom_html',
                    'value' => '',
                    'wrapper' => [
                        'class' => 'form-group col-md-2',
                    ],
                ],
                [
                    'name' => 'separator8',
                    'type' => 'custom_html',
                    'value' => '<h3 class="text-center m-0">SEARCH BY PLACE</h3>',
                    'wrapper' => [
                        'class' => 'form-group col-sm-6 mt-5 mb-2',
                    ],
                ],
                [
                    'name' => 'separator9',
                    'type' => 'custom_html',
                    'value' => '<h3 class="text-center m-0">SEARCH BY REGISTRATION DATE</h3>',
                    'wrapper' => [
                        'class' => 'form-group col-sm-6 mt-5 mb-2',
                    ],
                ],
                [
                    'label' => 'State',
                    'name' => 'state',
                    'type' => 'select2_from_ajax',
                    'model' => 'App\Models\ApiState',
                    'attribute' => 'name',
                    'placeholder' => 'Select state',
                    'data_source' => route('advance-search-state'),
                    'minimum_input_length' => 2,
                    'wrapper' => [
                        'class' => 'col-md-3',
                    ],
                    'attributes' => [
                        'class' => 'select2_from_ajax form-control filter-advance-search',
                    ],
                ],
                [
                    'label' => 'District',
                    'name' => 'district',
                    'type' => 'select2_from_ajax',
                    'model' => 'App\Models\ApiDistrict',
                    'attribute' => 'name',
                    'placeholder' => 'Select district',
                    'data_source' => route('advance-search-district'),
                    'minimum_input_length' => 2,
                    'wrapper' => [
                        'class' => 'col-md-3',
                    ],
                    'attributes' => [
                        'class' => 'select2_from_ajax form-control filter-advance-search',
                    ],
                ],
                [
                    'name' => 'registration_start_date, registration_end_date',
                    'label' => 'Registration date',
                    'type' => 'date_range',
                    'wrapper' => [
                        'class' => 'col-md-6',
                    ],
                    'attributes' => [
                        'class' => 'daterange-input-custom form-control filter-advance-search',
                        'data-name' => 'registration_date',
                    ],
                ],
            ]);
        @endphp <form onsubmit="return false;">
            @include('crud::form_content', ['fields' => $crud->fields()])
        </form>
    </div>
</div>


@includeWhen(!empty($widget['wrapper']), backpack_view('widgets.inc.wrapper_end'))

@push('after_styles')
    <link rel="stylesheet" href="{{ basset('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css') }}">
    <link rel="stylesheet"
        href="{{ basset('https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css') }}">

    <!-- include select2 css-->
    <link rel="stylesheet"
        href="{{ basset('https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.min.css') }}">

    <link rel="stylesheet"
        href="{{ basset('https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/css/bootstrap-datepicker.min.css') }}" />

    <style>
        .input-group.date {
            width: 320px;
            max-width: 100%;
        }

        .daterangepicker.dropdown-menu {
            z-index: 3001 !important;
        }
    </style>
@endpush

@push('after_scripts')
    <!-- include select2 js-->
    <script src="{{ basset('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js') }}"></script>

    <script src="{{ basset('https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/moment.min.js') }}"></script>
    <script
        src="{{ basset('https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.min.js') }}">
    </script>
    <script
        src="{{ basset('https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/js/bootstrap-datepicker.min.js') }}">
    </script>
    @basset('https://unpkg.com/urijs@1.19.11/src/URI.min.js')


    <script>
        function updateDatatablesOnFilterChangeCustom(el, parameter, value) {
            // behaviour for ajax table
            var ajax_table = $('#crudTable').DataTable();
            var current_url = ajax_table.ajax.url();
            var new_url = addOrUpdateUriParameter(current_url, parameter, value);

            // replace the datatables ajax url with new_url and reload it
            new_url = normalizeAmpersand(new_url.toString());
            ajax_table.ajax.url(new_url).load();

            if (value)
                el.closest('[bp-section="crud-field"]').addClass('active');
            else
                el.closest('[bp-section="crud-field"]').removeClass('active');
        }
    </script>

    <script>
        jQuery(document).ready(function($) {
            $('.select2_from_array').on('change', function(e) {
                var parameter = $(this).attr('name');
                var value = $(this).val();

                updateDatatablesOnFilterChangeCustom($(this), parameter, value);
            });

            $('.select2_from_ajax').on('change', function(e) {
                var parameter = $(this).attr('name');
                var value = $(this).val();

                updateDatatablesOnFilterChangeCustom($(this), parameter, value);
            });

            $('.text-input-custom').on('input', function(e) {
                var parameter = $(this).attr('name');
                var value = $(this).val();

                if (parameter == 'case_year' && value.length != 4) {
                    return;
                }

                if (parameter == 'cnr_no' && value.length < 2) {
                    return;
                }

                updateDatatablesOnFilterChangeCustom($(this), parameter, value);
            });

            $('.daterange-input-custom').on('apply.daterangepicker', function() {
                var parameter = $(this).data('name');
                var start = $(this).parent().parent().find('input.datepicker-range-start').val();
                var end = $(this).parent().parent().find('input.datepicker-range-end').val();

                if (start && end) {
                    var dates = {
                        'from': start,
                        'to': end
                    };

                    var value = JSON.stringify(dates);
                } else {
                    var value = '';
                }


                updateDatatablesOnFilterChangeCustom($(this), parameter, value);
            });

            $('.datepicker-input-custom').on('changeYear', function(e) {
                var parameter = $(this).data('name');
                var value = $(this).val();

                updateDatatablesOnFilterChangeCustom($(this), parameter, value);
            });
        });
    </script>

    <script>
        function addOrUpdateUriParameter(uri, parameter, value) {
            var new_url = normalizeAmpersand(uri);

            new_url = URI(new_url).normalizeQuery();

            // this param is only needed in datatables persistent url redirector
            // not when applying filters so we remove it.
            if (new_url.hasQuery('persistent-table')) {
                new_url.removeQuery('persistent-table');
            }

            if (new_url.hasQuery(parameter)) {
                new_url.removeQuery(parameter);
            }

            if (value !== '' && value != null) {
                new_url = new_url.addQuery(parameter, value);
            }

            $('#remove_filters_button').toggleClass('invisible', !new_url.query());

            return new_url.toString();

        }

        function updateDatatablesOnFilterChange(filterName, filterValue, update_url = false) {
            // behaviour for ajax table
            var current_url = crud.table.ajax.url();
            var new_url = addOrUpdateUriParameter(current_url, filterName, filterValue);

            new_url = normalizeAmpersand(new_url);

            // add filter to URL
            crud.updateUrl(new_url);
            crud.table.ajax.url(new_url);

            // when we are clearing ALL filters, we would not update the table url here, because this is done PER filter
            // and we have a function that will do this update for us after all filters had been cleared.
            if (update_url) {
                // replace the datatables ajax url with new_url and reload it
                crud.table.ajax.url(new_url).load();
            }

            return new_url;
        }


        function normalizeAmpersand(string) {
            return string.replace(/&amp;/g, "&").replace(/amp%3B/g, "");
        }

        // button to remove all filters
        jQuery(document).ready(function($) {
            $("#remove_filters_button").click(function(e) {
                e.preventDefault();

                // behaviour for ajax table
                var new_url = '{{ url($crud->route . '/search') }}';
                var ajax_table = $("#crudTable").DataTable();

                // replace the datatables ajax url with new_url and reload it
                ajax_table.ajax.url(new_url).load();

                // clear all filters
                $(".filter-advance-search").each(function() {
                    $(this).val('').trigger('change');
                });

                $('.btn-tabs-filters').removeClass('btn-secondary').removeClass('btn-primary').addClass(
                    'btn-secondary')

                // remove filters from URL
                crud.updateUrl(new_url);
            });

            // hide the Remove filters button when no filter is active
            $(".filter-advance-search").on('change', function() {
                var anyActiveFilters = false;
                $(".filter-advance-search").each(function() {
                    if ($(this).val()) {
                        anyActiveFilters = true;
                        // console.log('ACTIVE FILTER');
                    }
                });

                if (anyActiveFilters == false) {
                    $('#remove_filters_button').addClass('invisible');
                }
            });


            $('form').find("[data-init-function]").not("[data-initialized=true]").each(function() {
                var element = $(this);
                var functionName = element.data('init-function');

                if (typeof window[functionName] === "function") {
                    window[functionName](element);

                    // mark the element as initialized, so that its function is never called again
                    element.attr('data-initialized', 'true');
                }
            });
        });
    </script>

    <script>
        function bpFieldInitSelect2FromAjaxElement(element) {
            var form = element.closest('form');
            var $placeholder = element.attr('data-placeholder');
            var $minimumInputLength = element.attr('data-minimum-input-length');
            var $dataSource = element.attr('data-data-source');
            var $method = element.attr('data-method');
            var $fieldAttribute = element.attr('data-field-attribute');
            var $connectedEntityKeyName = element.attr('data-connected-entity-key-name');
            var $includeAllFormFields = element.attr('data-include-all-form-fields') == 'false' ? false : true;
            var $allowClear = element.attr('data-column-nullable') == 'true' ? true : false;
            var $dependencies = JSON.parse(element.attr('data-dependencies'));
            var $ajaxDelay = element.attr('data-ajax-delay');
            var $selectedOptions = typeof element.attr('data-selected-options') === 'string' ? JSON.parse(element.attr(
                'data-selected-options')) : JSON.parse(null);
            var $isFieldInline = element.data('field-is-inline');


            var select2AjaxFetchSelectedEntry = function(element) {
                return new Promise(function(resolve, reject) {
                    $.ajax({
                        url: $dataSource,
                        data: {
                            'keys': $selectedOptions
                        },
                        type: $method,
                        success: function(result) {

                            resolve(result);
                        },
                        error: function(result) {
                            reject(result);
                        }
                    });
                });
            };

            // do not initialise select2s that have already been initialised
            if ($(element).hasClass("select2-hidden-accessible")) {
                return;
            }
            //init the element
            $(element).select2({
                theme: 'bootstrap',
                multiple: false,
                placeholder: $placeholder,
                minimumInputLength: $minimumInputLength,
                allowClear: $allowClear,
                dropdownParent: $isFieldInline ? $('#inline-create-dialog .modal-content') : document.body,
                ajax: {
                    url: $dataSource,
                    type: $method,
                    dataType: 'json',
                    delay: $ajaxDelay,
                    data: function(params) {
                        if ($includeAllFormFields) {
                            return {
                                q: params.term, // search term
                                page: params.page, // pagination
                                form: form.serializeArray() // all other form inputs
                            };
                        } else {
                            return {
                                q: params.term, // search term
                                page: params.page, // pagination
                            };
                        }
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;

                        var result = {
                            results: $.map(data.data, function(item) {
                                textField = $fieldAttribute;
                                return {
                                    text: item[textField],
                                    id: item[$connectedEntityKeyName]
                                }
                            }),
                            pagination: {
                                more: data.current_page < data.last_page
                            }
                        };

                        return result;
                    },
                    cache: true
                },
            });

            // if we have selected options here we are on a repeatable field, we need to fetch the options with the keys
            // we have stored from the field and append those options in the select.
            if (typeof $selectedOptions !== typeof undefined &&
                $selectedOptions !== false &&
                $selectedOptions != '' &&
                $selectedOptions != null &&
                $selectedOptions != []) {
                var optionsForSelect = [];
                select2AjaxFetchSelectedEntry(element).then(function(result) {
                    result.forEach(function(item) {
                        $itemText = item[$fieldAttribute];
                        $itemValue = item[$connectedEntityKeyName];
                        //add current key to be selected later.
                        optionsForSelect.push($itemValue);

                        //create the option in the select
                        $(element).append('<option value="' + $itemValue + '">' + $itemText + '</option>');
                    });

                    // set the option keys as selected.
                    $(element).val(optionsForSelect);
                });
            }

            // if any dependencies have been declared
            // when one of those dependencies changes value
            // reset the select2 value
            for (var i = 0; i < $dependencies.length; i++) {
                var $dependency = $dependencies[i];
                //if element does not have a custom-selector attribute we use the name attribute
                if (typeof element.attr('data-custom-selector') == 'undefined') {
                    form.find('[name="' + $dependency + '"], [name="' + $dependency + '[]"]').change(function(el) {
                        $(element.find('option:not([value=""])')).remove();
                        element.val(null).trigger("change");
                    });
                } else {
                    // we get the row number and custom selector from where element is called
                    let rowNumber = element.attr('data-row-number');
                    let selector = element.attr('data-custom-selector');

                    // replace in the custom selector string the corresponding row and dependency name to match
                    selector = selector
                        .replaceAll('%DEPENDENCY%', $dependency)
                        .replaceAll('%ROW%', rowNumber);

                    $(selector).change(function(el) {
                        $(element.find('option:not([value=""])')).remove();
                        element.val(null).trigger("change");
                    });
                }
            }
        }

        function bpFieldInitSelect2FromArrayElement(element) {
            if (!element.hasClass("select2-hidden-accessible")) {
                let $isFieldInline = element.data('field-is-inline');

                element.select2({
                    theme: "bootstrap",
                    dropdownParent: $isFieldInline ? $('#inline-create-dialog .modal-content') : document.body
                }).on('select2:unselect', function(e) {
                    if ($(this).attr('multiple') && $(this).val().length == 0) {
                        $(this).val(null).trigger('change');
                    }
                });
            }
        }


        function bpFieldInitDateRangeElement(element) {
            moment.locale('{{ app()->getLocale() }}');

            var $visibleInput = element;
            var $startInput = $visibleInput.closest('.input-group').parent().find('.datepicker-range-start');
            var $endInput = $visibleInput.closest('.input-group').parent().find('.datepicker-range-end');

            var $configuration = $visibleInput.data('bs-daterangepicker');
            // set the startDate and endDate to the defaults
            $configuration.startDate = moment($configuration.startDate);
            $configuration.endDate = moment($configuration.endDate);

            $ranges = $configuration.ranges;
            $configuration.ranges = {};

            //if developer configured ranges we convert it to moment() dates.
            for (var key in $ranges) {
                if ($ranges.hasOwnProperty(key)) {
                    $configuration.ranges[key] = $.map($ranges[key], function($val) {
                        return moment($val);
                    });
                }
            }

            // if the hidden inputs have values
            // then startDate and endDate should be the values there
            if ($startInput.val() != '') {
                $configuration.startDate = moment($startInput.val());
            }
            if ($endInput.val() != '') {
                $configuration.endDate = moment($endInput.val());
            }

            $visibleInput.daterangepicker($configuration);

            var $picker = $visibleInput.data('daterangepicker');

            $visibleInput.on('keydown', function(e) {
                e.preventDefault();
                return false;
            });

            $visibleInput.on('apply.daterangepicker hide.daterangepicker', function(e, picker) {
                $startInput.val(picker.startDate.format('YYYY-MM-DD HH:mm:ss'));
                $endInput.val(picker.endDate.format('YYYY-MM-DD HH:mm:ss'));
            });

            $visibleInput.parent().find('.daterangepicker-clear-button').click(function(e) {
                e.preventDefault();
                $startInput.val('');
                $endInput.val('');
                $visibleInput.val('').trigger('apply.daterangepicker', $visibleInput.data('daterangepicker'));
            })
        }

        if (jQuery.ui) {
            var datepicker = $.fn.datepicker.noConflict();
            $.fn.bootstrapDP = datepicker;
        } else {
            $.fn.bootstrapDP = $.fn.datepicker;
        }

        var dateFormat = function() {
            var a = /d{1,4}|m{1,4}|yy(?:yy)?|([HhMsTt])\1?|[LloSZ]|"[^"]*"|'[^']*'/g,
                b =
                /\b(?:[PMCEA][SDP]T|(?:Pacific|Mountain|Central|Eastern|Atlantic) (?:Standard|Daylight|Prevailing) Time|(?:GMT|UTC)(?:[-+]\d{4})?)\b/g,
                c = /[^-+\dA-Z]/g,
                d = function(a, b) {
                    for (a = String(a), b = b || 2; a.length < b;) a = "0" + a;
                    return a
                };
            return function(e, f, g) {
                var h = dateFormat;
                if (1 != arguments.length || "[object String]" != Object.prototype.toString.call(e) || /\d/.test(
                        e) || (f = e, e = void 0), e = e ? new Date(e) : new Date, isNaN(e)) throw SyntaxError(
                    "invalid date");
                f = String(h.masks[f] || f || h.masks.default), "UTC:" == f.slice(0, 4) && (f = f.slice(4), g = !0);
                var i = g ? "getUTC" : "get",
                    j = e[i + "Date"](),
                    k = e[i + "Day"](),
                    l = e[i + "Month"](),
                    m = e[i + "FullYear"](),
                    n = e[i + "Hours"](),
                    o = e[i + "Minutes"](),
                    p = e[i + "Seconds"](),
                    q = e[i + "Milliseconds"](),
                    r = g ? 0 : e.getTimezoneOffset(),
                    s = {
                        d: j,
                        dd: d(j),
                        ddd: h.i18n.dayNames[k],
                        dddd: h.i18n.dayNames[k + 7],
                        m: l + 1,
                        mm: d(l + 1),
                        mmm: h.i18n.monthNames[l],
                        mmmm: h.i18n.monthNames[l + 12],
                        yy: String(m).slice(2),
                        yyyy: m,
                        h: n % 12 || 12,
                        hh: d(n % 12 || 12),
                        H: n,
                        HH: d(n),
                        M: o,
                        MM: d(o),
                        s: p,
                        ss: d(p),
                        l: d(q, 3),
                        L: d(q > 99 ? Math.round(q / 10) : q),
                        t: n < 12 ? "a" : "p",
                        tt: n < 12 ? "am" : "pm",
                        T: n < 12 ? "A" : "P",
                        TT: n < 12 ? "AM" : "PM",
                        Z: g ? "UTC" : (String(e).match(b) || [""]).pop().replace(c, ""),
                        o: (r > 0 ? "-" : "+") + d(100 * Math.floor(Math.abs(r) / 60) + Math.abs(r) % 60, 4),
                        S: ["th", "st", "nd", "rd"][j % 10 > 3 ? 0 : (j % 100 - j % 10 != 10) * j % 10]
                    };
                return f.replace(a, function(a) {
                    return a in s ? s[a] : a.slice(1, a.length - 1)
                })
            }
        }();
        dateFormat.masks = {
            default: "ddd mmm dd yyyy HH:MM:ss",
            shortDate: "m/d/yy",
            mediumDate: "mmm d, yyyy",
            longDate: "mmmm d, yyyy",
            fullDate: "dddd, mmmm d, yyyy",
            shortTime: "h:MM TT",
            mediumTime: "h:MM:ss TT",
            longTime: "h:MM:ss TT Z",
            isoDate: "yyyy-mm-dd",
            isoTime: "HH:MM:ss",
            isoDateTime: "yyyy-mm-dd'T'HH:MM:ss",
            isoUtcDateTime: "UTC:yyyy-mm-dd'T'HH:MM:ss'Z'"
        }, dateFormat.i18n = {
            dayNames: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sunday", "Monday", "Tuesday", "Wednesday",
                "Thursday", "Friday", "Saturday"
            ],
            monthNames: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec", "January",
                "February", "March", "April", "May", "June", "July", "August", "September", "October", "November",
                "December"
            ]
        }, Date.prototype.format = function(a, b) {
            return dateFormat(this, a, b)
        };

        function bpFieldInitDatePickerElement(element) {
            var $fake = element,
                $field = $fake.closest('.input-group').parent().find('input[type="hidden"]'),
                $customConfig = $fake.data('bs-datepicker');
            $picker = $fake.bootstrapDP($customConfig);

            var $existingVal = $field.val();
            $existingVal += '-01-01';

            if ($existingVal && $existingVal.length) {
                // Passing an ISO-8601 date string (YYYY-MM-DD) to the Date constructor results in
                // varying behavior across browsers. Splitting and passing in parts of the date
                // manually gives us more defined behavior.
                // See https://stackoverflow.com/questions/2587345/why-does-date-parse-give-incorrect-results
                var parts = $existingVal.split('-');
                var year = parts[0];
                var month = parts[1] - 1; // Date constructor expects a zero-indexed month
                var day = parts[2];
                preparedDate = new Date(year, month, day).format($customConfig.format);

                $fake.val(preparedDate);
                $picker.bootstrapDP('update', preparedDate);
            }

            // prevent users from typing their own date
            // since the js plugin does not support it
            // $fake.on('keydown', function(e){
            //     e.preventDefault();
            //     return false;
            // });

            $picker.on('show hide', function(e) {
                if (e.date) {
                    var sqlDate = e.format('yyyy');
                } else {
                    try {
                        var sqlDate = $fake.val();

                        if ($customConfig.format === 'dd/mm/yyyy') {
                            sqlDate = new Date(sqlDate.split('/')[2], sqlDate.split('/')[1] - 1, sqlDate.split('/')[
                                0]).format('yyyy-mm-dd');
                        }
                    } catch (e) {
                        if ($fake.val()) {
                            new Noty({
                                type: "error",
                                text: "<strong>Whoops!</strong><br>Sorry we did not recognise that date format, please make sure it uses a yyyy mm dd combination"
                            }).show();
                        }
                    }
                }

                $field.val(sqlDate);
            });


            $picker.on('changeYear', function(e) {
                if (e.date) {
                    $picker.datepicker('update', e.date);
                    $picker.datepicker('hide');
    
                    $picker.datepicker('destroy');
                    bpFieldInitDatePickerElement($picker);
                }
            });

            $fake.parent().find('.datepicker-clear-button').click(function(e) {
                e.preventDefault();
                $picker.datepicker('clearDates');
                $fake.trigger('changeYear');
            })
        }

    </script>
@endpush
