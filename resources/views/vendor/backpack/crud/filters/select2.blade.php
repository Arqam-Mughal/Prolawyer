{{-- Select2 Backpack CRUD filter --}}
<li filter-name="{{ $filter->name }}" filter-type="{{ $filter->type }}" filter-key="{{ $filter->key }}"
    class="nav-item dropdown {{ Request::get($filter->name) ? 'active' : '' }}">
    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" type="button"
        aria-expanded="false">{{ $filter->label }} <span class="caret"></span></a>
    <div class="dropdown-menu p-0">
        <div class="form-group backpack-filter mb-0">
            <select id="filter_{{ $filter->key }}" name="filter_{{ $filter->key }}"
                class="form-control input-sm select2" placeholder="{{ $filter->placeholder }}"
                data-filter-key="{{ $filter->key }}" data-filter-type="select2" data-filter-name="{{ $filter->name }}"
                data-language="{{ str_replace('_', '-', app()->getLocale()) }}">
                <option value="">-</option>
                @if (is_array($filter->values) && count($filter->values))
                    @foreach ($filter->values as $key => $value)
                        <option value="{{ $key }}" @if ($filter->isActive() && $filter->currentValue == $key) selected @endif>
                            {{ $value }}
                        </option>
                    @endforeach
                @endif
            </select>
        </div>
    </div>
</li>

{{-- ########################################### --}}
{{-- Extra CSS and JS for this particular filter --}}

{{-- FILTERS EXTRA CSS  --}}
{{-- push things in the after_styles section --}}

@push('crud_list_styles')
    <!-- include select2 css-->
    @basset('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css')
    @basset('https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css')
    <style>
        .form-inline .select2-container {
            display: inline-block;
        }

        .select2-drop-active {
            border: none;
        }

        .select2-container .select2-choices .select2-search-field input,
        .select2-container .select2-choice,
        .select2-container .select2-choices {
            border: none;
        }

        .select2-container-active .select2-choice {
            border: none;
            box-shadow: none;
        }

        .select2-container--bootstrap .select2-dropdown {
            margin-top: -2px;
            margin-left: -1px;
        }

        .select2-container--bootstrap {
            position: relative !important;
            top: 0px !important;
        }
    </style>
@endpush


{{-- FILTERS EXTRA JS --}}
{{-- push things in the after_scripts section --}}

@push('crud_list_scripts')
    <!-- include select2 js-->
    @basset('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js')

    {{-- @if (app()->getLocale() !== 'en')
        <script src="{{ asset('packages/select2/dist/js/i18n/' . str_replace('_', '-', app()->getLocale()) . '.js') }}">
        </script>
    @endif --}}

    <script>
        jQuery(document).ready(function($) {
            // trigger select2 for each untriggered select2 box
            $('select[data-filter-type=select2]').not('[data-filter-enabled]').each(function() {
                var filterName = $(this).attr('data-filter-name');
                var filter_key = $(this).attr('data-filter-key');
                var element = $(this);

                $(this).attr('data-filter-enabled', 'true');

                var obj = $(this).select2({
                    allowClear: true,
                    closeOnSelect: false,
                    theme: "bootstrap",
                    dropdownParent: $(this).parent('.form-group'),
                    placeholder: $(this).attr('placeholder'),
                });

                $(this).change(function() {
                    var value = $(this).val();
                    var parameter = $(this).attr('data-filter-name');

                    // behaviour for ajax table
                    var ajax_table = $("#crudTable").DataTable();
                    var current_url = ajax_table.ajax.url();
                    var new_url = addOrUpdateUriParameter(current_url, parameter, value);

                    // replace the datatables ajax url with new_url and reload it
                    new_url = normalizeAmpersand(new_url.toString());
                    ajax_table.ajax.url(new_url).load();

                    // add filter to URL
                    crud.updateUrl(new_url);

                    // mark this filter as active in the navbar-filters
                    if (URI(new_url).hasQuery(parameter, true)) {
                        $("li[filter-key=" + filter_key + "]").addClass('active');
                    } else {
                        $("li[filter-key=" + filter_key + "]").removeClass("active");
                        $("li[filter-key=" + filter_key + "]").find('.dropdown-menu').removeClass(
                            "show");
                    }
                });

                // when the dropdown is opened, autofocus on the select2
                $("li[filter-key=" + filter_key + "]").on('shown.bs.dropdown', function() {
                    $('select[data-filter-key=' + filter_key + ']').select2('open');
                });

                // clear filter event (used here and by the Remove all filters button)
                $("li[filter-key=" + filter_key + "]").on('filter:clear', function(e) {
                    // console.log('select2 filter cleared');
                    $("li[filter-key=" + filter_key + "]").removeClass('active');
                    $('#filter_' + filter_key).val(null).trigger('change');
                });
            });
        });
    </script>
@endpush
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
