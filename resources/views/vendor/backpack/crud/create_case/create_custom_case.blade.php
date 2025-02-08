<div class="row create-case" bp-section="crud-operation-create" data-case-type="custom-case" style="display:none;">
    <div class="{{ $crud->getCreateContentClass() }}">
        {{-- Default box --}}

        @include('crud::inc.grouped_errors')
        <form method="post" action="{{ url($crud->route) }}"
            @if ($crud->hasUploadFields('create')) enctype="multipart/form-data" @endif>
            {!! csrf_field() !!}
            {{-- load the view from the application if it exists, otherwise load the one in the package --}}
            @if (view()->exists('vendor.backpack.crud.form_content'))
                @include('vendor.backpack.crud.form_content', [
                    'fields' => $crud->fields(),
                    'action' => 'create',
                ])
            @endif
            {{-- This makes sure that all field assets are loaded. --}}
            <div class="d-none" id="parentLoadedAssets">{{ json_encode(Basset::loaded()) }}</div>
            @include('crud::inc.form_save_buttons')
        </form>
    </div>
</div>

<style>
    .btn-tabs-create {
        width: 100% !important;
    }

    .fetch-case {
        display: none;
    }

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
            var create_tab = $('.btn-tabs-create.btn-primary').attr("value");
            $('.create-case').hide();

            if ('custom-case' !== create_tab) {
                $('.fetch-' + create_tab).show();
            }

            $('.btn-tabs-create').on('click', function(e) {
                e.preventDefault();

                var active_tab = $(this).attr("value");
                $('.create-case').hide();
                $('.fetch-case').hide();

                if ('custom-case' !== active_tab) {
                    $('.fetch-' + active_tab).show();
                } else {
                    $('.create-case[data-case-type="' + active_tab + '"]').show();
                }
                $('.btn-tabs-create').removeClass('btn-primary').removeClass('btn-secondary').addClass(
                    'btn-secondary');
                $(this).removeClass('btn-primary').removeClass('btn-secondary').addClass('btn-primary');

            });


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
    </script>
@endpush
