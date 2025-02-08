{{-- checklist --}}
@php
    $key_attribute = (new ($field['model'])())->getKeyName();
    $field['attribute'] = $field['attribute'] ?? (new ($field['model'])())->identifiableAttribute();
    $field['number_of_columns'] = $field['number_of_columns'] ?? 3;

    // calculate the checklist options
    if (!isset($field['options'])) {
        $field['options'] = $field['model']
            ::all()
            ->pluck($field['attribute'], $key_attribute)
            ->toArray();
    } else {
        $field['options'] = call_user_func($field['options'], $field['model']::query());
    }

    // calculate the value of the hidden input
    $field['value'] = old_empty_or_null($field['name'], []) ?? ($field['value'] ?? ($field['default'] ?? []));
    if (!empty($field['value'])) {
        if (is_a($field['value'], \Illuminate\Support\Collection::class)) {
            $field['value'] = $field['value']->pluck($key_attribute)->toArray();
        } elseif (is_string($field['value'])) {
            $field['value'] = json_decode($field['value']);
        }
    }

    // define the init-function on the wrapper
    $field['wrapper']['data-init-function'] = 'bpFieldInitChecklistPermission';
@endphp

@include('crud::fields.inc.wrapper_start')
<label>{!! $field['label'] !!}</label>
@include('crud::fields.inc.translatable_icon')

<?php
use App\Models\Permission;
if (backpack_user()->hasRole('Super admin')) {
    $query = Permission::all();
} else {
    $query = backpack_user()->getAllPermissions();
    $query = $query->where('name', '<>', 'Add sub lawyer');
}

$m1 = $query->where('type', 1);
$m2 = $query->where('type', 2);
$m3 = $query->where('type', 3);
?>


<div class="row">
    @foreach ($m1 as $item)
        @php
            $subitems = $m2->where('parent_id', $item->id);
        @endphp
        <div class="dropdown col-md-4 col-sm-3 col-xs-2 mr-2 mb-1">
            @if (count($subitems))
                <button class="btn btn-primary dropdown-toggle dropdown-menu-btns w-100"
                    type="button" id="dropdownMenuButton{{ $item->id }}">
                    <div class="checkbox checkbox-main">
                        <input type="checkbox" value="{{ $item->id }}" id="mainitem{{ $item->id }}">
                        <label class="font-weight-normal" for="mainitem{{ $item->id }}">
                            {{ $item->name }}
                        </label>
                    </div>
                </button>
            @else
                <div class="bg-primary text-white justify-content-center text-center h-100 py-2 d-flex align-items-center" style="border-radius: 4px;">
                    <label class="font-weight-normal w-100" for="subitem{{ $item->id }}" style=" font-weight: bold; ">
                        <input id="subitem{{ $item->id }}" type="checkbox" value="{{ $item->id }}">
                        {{ $item->name }}
                    </label>
                </div>
            @endif

            @if (count($subitems))
                <div class="dropdown-menu w-100" style="max-height: 500px; overflow-y: auto;"
                    aria-labelledby="dropdownMenuButton{{ $item->id }}">
                    @foreach ($subitems as $subitem)
                        @php
                            $sub_subitems = $m3->where('parent_id', $subitem->id);
                        @endphp

                        @if (count($sub_subitems))
                            <button class="btn btn-info justify-content-start dropdown-toggle dropdown-menu-btns w-100"
                                type="button" id="dropdownMenuButton{{ $subitem->id }}">
                                <div class="checkbox checkbox-main">
                                    <input type="checkbox" value="{{ $subitem->id }}"
                                        id="mainitem{{ $subitem->id }}">
                                    <label class="font-weight-normal" for="mainitem{{ $subitem->id }}">
                                        {{ $subitem->name }}
                                    </label>
                                </div>
                            </button>
                        @else
                            <div class="dropdown-item checkbox">
                                <label class="font-weight-normal w-100" for="subitem{{ $subitem->id }}">
                                    <input id="subitem{{ $subitem->id }}" type="checkbox"
                                        value="{{ $subitem->id }}">
                                    {{ $subitem->name }}
                                </label>
                            </div>
                        @endif

                        @if (count($sub_subitems))
                            <div class="dropdown-menu bg-info text-white w-100"
                                style="max-height: 500px; overflow-y: auto;"
                                aria-labelledby="dropdownMenuButton{{ $subitem->id }}">
                                @foreach ($sub_subitems as $sub_subitem)
                                    <div class="dropdown-item checkbox">
                                        <label class="font-weight-normal w-100" for="subitem{{ $sub_subitem->id }}">
                                            <input id="sub_subitem{{ $sub_subitem->id }}" type="checkbox"
                                                value="{{ $sub_subitem->id }}">
                                            {{ $sub_subitem->name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    @endforeach
</div>

<input type="hidden" value='@json($field['value'])' name="{{ $field['name'] }}">

{{-- HINT --}}
@if (isset($field['hint']))
    <p class="help-block">{!! $field['hint'] !!}</p>
@endif
@include('crud::fields.inc.wrapper_end')


{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
{{-- FIELD JS - will be loaded in the after_scripts section --}}
@push('crud_fields_scripts')
    <script>
        $('.dropdown-menu-btns').click(function(e) {
            e.stopPropagation();
            $(this).dropdown('toggle');

            $(this).next().find('.dropdown-menu-btns.show').dropdown('hide');
        });

        $('.checkbox-main input[type=checkbox]').click(function(e) {
            e.stopPropagation(); // Prevent dropdown from closing
            var isChecked = $(this).is(':checked');

            $(this).parent().parent().next().children('.dropdown-item').find(
                `input[type=checkbox]${isChecked ? ':not(:checked)' : ':checked'}`).trigger('click');
            $(this).parent().parent().next().children('.dropdown-menu-btns').children('.checkbox').find(
                `input[type=checkbox]${isChecked ? ':not(:checked)' : ':checked'}`).trigger('click');

            $(this).parent().parent().dropdown('show');
            $(this).parent().parent().next().find('.dropdown-menu-btns').dropdown('hide');
        })

        $('.dropdown-menu input[type=checkbox]').click(function() {
            const dropdown = $(this).parent().parent().parent();

            if (dropdown.find(':checked').length == 0)
                dropdown.prev().find('.checkbox-main input[type=checkbox]').prop('checked', false)
            else
                dropdown.prev().find('.checkbox-main input[type=checkbox]').prop('checked', true)
        })
    </script>
    @bassetBlock('backpack/crud/fields/checklist-field-permission.js')
        <script>
            function bpFieldInitChecklistPermission(element) {
                var hidden_input = element.find('input[type=hidden]');
                var selected_options = JSON.parse(hidden_input.val() || '[]');
                var checkboxes = element.find('input[type=checkbox]');
                var container = element.find('.row');

                // set the default checked/unchecked states on checklist options
                checkboxes.each(function(key, option) {
                    var id = $(this).val();

                    if (selected_options.map(String).includes(id)) {
                        $(this).prop('checked', 'checked');
                    } else {
                        $(this).prop('checked', false);
                    }
                });

                $('.dropdown-menu input[type=checkbox]').each(function() {
                    const dropdown = $(this).parent().parent().parent();

                    if (dropdown.find(':checked').length == 0)
                        dropdown.prev().find('.checkbox-main input[type=checkbox]').prop('checked', false)
                    else
                        dropdown.prev().find('.checkbox-main input[type=checkbox]').prop('checked', true)
                });

                // when a checkbox is clicked
                // set the correct value on the hidden input
                checkboxes.click(function() {
                    var newValue = [];

                    checkboxes.each(function() {
                        if ($(this).is(':checked')) {
                            var id = $(this).val();
                            newValue.push(id);
                        }
                    });

                    hidden_input.val(JSON.stringify(newValue)).trigger('change');
                });

                hidden_input.on('CrudField:disable', function(e) {
                    checkboxes.attr('disabled', 'disabled');
                });

                hidden_input.on('CrudField:enable', function(e) {
                    checkboxes.removeAttr('disabled');
                });

            }
        </script>
    @endBassetBlock
@endpush
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
