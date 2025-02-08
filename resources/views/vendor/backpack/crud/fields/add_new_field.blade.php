{{-- add_new_field_field field --}}
@php
    $field['value'] = old_empty_or_null($field['name'], '') ?? ($field['value'] ?? ($field['default'] ?? ''));
@endphp

@include('crud::fields.inc.wrapper_start')
    @include('crud::fields.inc.translatable_icon')

    <input type="button"
        name="{{ $field['name'] }}"
        data-init-function="bpFieldInitDummyFieldElement"
        value="Add {{ $field['value'] }}"
        class="btn btn-primary"
        @include('crud::fields.inc.attributes')>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
@include('crud::fields.inc.wrapper_end')

{{-- CUSTOM CSS --}}
@push('crud_fields_styles')
    {{-- How to load a CSS file? --}}
    @basset('add_new_fieldFieldStyle.css')

    {{-- How to add some CSS? --}}
    @bassetBlock('backpack/crud/fields/add_new_field_field-style.css')
        <style>
            .add_new_field_field_class {
                display: none;
            }
        </style>
    @endBassetBlock
@endpush

{{-- CUSTOM JS --}}
@push('crud_fields_scripts')
    {{-- How to load a JS file? --}}
    @basset('add_new_fieldFieldScript.js')

    {{-- How to add some JS to the field? --}}
    @bassetBlock('path/to/script.js')
    <script>
        function bpFieldInitDummyFieldElement(element) {
            // this function will be called on pageload, because it's
            // present as data-init-function in the HTML above; the
            // element parameter here will be the jQuery wrapped
            // element where init function was defined
        }
        jQuery(document).ready(function($) {
            $('.form-group[bp-field-name="organization_name"] label').hide();
            $('.form-group[bp-field-name="organization_address"] label').hide();
            $('.form-group[bp-field-name="organization_authorized_person_name"] label').hide();
            $('.form-group[bp-field-name="organization_email"] label').hide();
            $('.form-group[bp-field-name="organization_contact"] label').hide();
            $('.form-group[bp-field-name="reference_name"] label').hide();
            $('.form-group[bp-field-name="reference_contact"] label').hide();
            $('.form-group[bp-field-name="reference_email"] label').hide();
            $('.form-group[bp-field-name="reference_address"] label').hide();
            $('.form-group[bp-field-name="client_name"] label').hide();
            $('.form-group[bp-field-name="client_contact"] label').hide();
            $('.form-group[bp-field-name="client_email"] label').hide();
            $('.form-group[bp-field-name="client_address"] label').hide();
            $('.form-group[bp-field-name="client_gender"] label').hide();
            $('.form-group[bp-field-name="client_desc"] label').hide();
            $('.form-group[bp-field-name="petitioners2"] label').hide();
            $('.form-group[bp-field-name="petitioner_advocates2"] label').hide();
            $('.form-group[bp-field-name="respondents2"] label').hide();
            $('.form-group[bp-field-name="respondent_advocates2"] label').hide();

            $('.form-group[bp-field-type="add_new_field"] input[type="button"]').on('click', function(e){
                var add_new = $(this).attr("name");
                $(this).hide();

                if ( "add_new_client" === add_new ) {
                    $('.form-group[bp-field-name="client_name"] input').show();
                    $('.form-group[bp-field-name="client_contact"] input').show();
                    $('.form-group[bp-field-name="client_email"] input').show();
                    $('.form-group[bp-field-name="client_address"] input').show();
                    $('.form-group[bp-field-name="client_gender"] input').show();
                    $('.form-group[bp-field-name="client_desc"] input').show();
                }
                if ( "add_new_tags" === add_new ) {
                    $('.form-group[bp-field-name="reference_name"] input').show();
                    $('.form-group[bp-field-name="reference_contact"] input').show();
                    $('.form-group[bp-field-name="reference_email"] input').show();
                    $('.form-group[bp-field-name="reference_address"] input').show();
                }
                if ( "add_new_petitioner" === add_new ) {
                    $('.form-group[bp-field-name="petitioners2"] input').show();
                    $('.form-group[bp-field-name="petitioner_advocates2"] input').show();
                }
                if ( "add_new_respondent" === add_new ) {
                    $('.form-group[bp-field-name="respondents2"] input').show();
                    $('.form-group[bp-field-name="respondent_advocates2"] input').show();
                }
                if ( "add_new_organization" === add_new ) {
                    $('.form-group[bp-field-name="organization_name"] input').show();
                    $('.form-group[bp-field-name="organization_address"] input').show();
                    $('.form-group[bp-field-name="organization_authorized_person_name"] input').show();
                    $('.form-group[bp-field-name="organization_email"] input').show();
                    $('.form-group[bp-field-name="organization_contact"] input').show();
                }
            });
        });
    </script>
    @endBassetBlock
@endpush
