{{-- dependencyJson --}}
@php
    $field['wrapper'] = $field['wrapper'] ?? ($field['wrapperAttributes'] ?? []);
    $field['wrapper']['class'] = $field['wrapper']['class'] ?? 'form-group col-sm-12';
    $field['wrapper']['class'] = $field['wrapper']['class'] . ' checklist_dependency';
    $field['wrapper']['data-entity'] = $field['wrapper']['data-entity'] ?? $field['field_unique_name'];
    $field['wrapper']['data-init-function'] =
        $field['wrapper']['init-function'] ?? 'bpFieldInitChecklistDependencyElement';
@endphp

@include('crud::fields.inc.wrapper_start')

<label>{!! $field['label'] !!}</label>
<?php
$entity_model = $crud->getModel();

//short name for dependency fields
$primary_dependency = $field['subfields']['primary'];
$secondary_dependency = $field['subfields']['secondary'];

//all items with relation
$dependencies = $primary_dependency['model']::with($primary_dependency['entity_secondary'])->get();

$dependencyArray = [];

//convert dependency array to simple matrix ( primary id as key and array with secondaries id )
foreach ($dependencies as $primary) {
    $dependencyArray[$primary->id] = [];
    foreach ($primary->{$primary_dependency['entity_secondary']} as $secondary) {
        $dependencyArray[$primary->id][] = $secondary->id;
    }
}

$old_primary_dependency = old_empty_or_null($primary_dependency['name'], false) ?? false;
$old_secondary_dependency = old_empty_or_null($secondary_dependency['name'], false) ?? false;

//for update form, get initial state of the entity
if (isset($id) && $id) {
    //get entity with relations for primary dependency
    $entity_dependencies = $entity_model
        ->with($primary_dependency['entity'])
        ->with($primary_dependency['entity'] . '.' . $primary_dependency['entity_secondary'])
        ->find($id);

    $secondaries_from_primary = [];

    //convert relation in array
    $primary_array = $entity_dependencies->{$primary_dependency['entity']}->toArray();

    $secondary_ids = [];
    //create secondary dependency from primary relation, used to check what checkbox must be checked from second checklist
    if ($old_primary_dependency) {
        foreach ($old_primary_dependency as $primary_item) {
            foreach ($dependencyArray[$primary_item] as $second_item) {
                $secondary_ids[$second_item] = $second_item;
            }
        }
    } else {
        //create dependencies from relation if not from validate error
        foreach ($primary_array as $primary_item) {
            foreach ($primary_item[$secondary_dependency['entity']] as $second_item) {
                $secondary_ids[$second_item['id']] = $second_item['id'];
            }
        }
    }
}

//json encode of dependency matrix
$dependencyJson = json_encode($dependencyArray);
?>

<div class="container">

    <div class="row">
        <div class="col-sm-12">
            <label>{!! $primary_dependency['label'] !!}</label>
            @include('crud::fields.inc.translatable_icon', ['field' => $primary_dependency])
        </div>
    </div>

    <div class="row">

        <div class="hidden_fields_primary" data-name = "{{ $primary_dependency['name'] }}">
            <input type="hidden" bp-field-name="{{ $primary_dependency['name'] }}"
                name="{{ $primary_dependency['name'] }}" value="" />
            @if (isset($field['value']))
                @if ($old_primary_dependency)
                    @foreach ($old_primary_dependency as $item)
                        <input type="hidden" class="primary_hidden" name="{{ $primary_dependency['name'] }}[]"
                            value="{{ $item }}">
                    @endforeach
                @else
                    @foreach ($field['value'][0]->pluck('id', 'id')->toArray() as $item)
                        <input type="hidden" class="primary_hidden" name="{{ $primary_dependency['name'] }}[]"
                            value="{{ $item }}">
                    @endforeach
                @endif
            @endif
        </div>

        @foreach ($primary_dependency['model']::where('type', 'regular_user')->get() as $connected_entity_entry)
            <div
                class="col-sm-{{ isset($primary_dependency['number_columns']) ? intval(12 / $primary_dependency['number_columns']) : '4' }}">
                <div class="checkbox">
                    <label class="font-weight-normal">
                        <input type="checkbox" data-id = "{{ $connected_entity_entry->id }}" class = 'primary_list'
                            @foreach ($primary_dependency as $attribute => $value)
                              @if (is_string($attribute) && $attribute != 'value')
                                  @if ($attribute == 'name')
                                  {{ $attribute }}="{{ $value }}_show[]"
                                  @else
                                  {{ $attribute }}="{{ $value }}"
                                  @endif
                              @endif @endforeach
                            value="{{ $connected_entity_entry->id }}"
                            @if (
                                (isset($field['value']) &&
                                    is_array($field['value']) &&
                                    in_array($connected_entity_entry->id, $field['value'][0]->pluck('id', 'id')->toArray())) ||
                                    ($old_primary_dependency && in_array($connected_entity_entry->id, $old_primary_dependency))) checked = "checked" @endif>
                        {{ $connected_entity_entry->{$primary_dependency['attribute']} }}
                    </label>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row">
        <div class="col-sm-12">
            <label>{!! $secondary_dependency['label'] !!}</label>
            @include('crud::fields.inc.translatable_icon', ['field' => $secondary_dependency])
        </div>
    </div>

    <div class="row" id="ghsagjds">
        <div class="hidden_fields_secondary" data-name="{{ $secondary_dependency['name'] }}">
            <input type="hidden" bp-field-name="{{ $secondary_dependency['name'] }}"
                name="{{ $secondary_dependency['name'] }}" value="" />
            @if (isset($field['value']))
                @if ($old_secondary_dependency)
                    @foreach ($old_secondary_dependency as $item)
                        <input type="hidden" class="secondary_hidden" name="{{ $secondary_dependency['name'] }}[]"
                            value="{{ $item }}">
                    @endforeach
                @else
                    @foreach ($field['value'][1]->pluck('id', 'id')->toArray() as $item)
                        <input type="hidden" class="secondary_hidden" name="{{ $secondary_dependency['name'] }}[]"
                            value="{{ $item }}">
                    @endforeach
                @endif
            @endif
        </div>

        <?php
        use App\Models\Permission;
        if (backpack_user()->hasRole('Super admin')) {
            $query = Permission::all();
        } else {
            $query = backpack_user()->getAllPermissions();
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
                <div
                    class="dropdown col-sm-{{ isset($secondary_dependency['number_columns']) ? intval(12 / $secondary_dependency['number_columns']) : '4' }} mr-2 mb-1">

                    @if (count($subitems))
                        <button
                            class="btn btn-primary {{ count($subitems) ? 'dropdown-toggle dropdown-menu-btns' : '' }} w-100"
                            type="button" id="dropdownMenuButton{{ $item->id }}">
                            <div class="checkbox {{ count($subitems) ? 'checkbox-main' : '' }}">
                                <input type="checkbox" value="{{ $item->id }}" data-id="{{ $item->id }}"
                                    class="secondary_list"
                                    @foreach ($secondary_dependency as $attribute => $value)
                        @if (is_string($attribute) && $attribute != 'value')
                          @if ($attribute == 'name')
                            {{ $attribute }}="{{ $value }}_show[]"
                          @else
                            {{ $attribute }}="{{ $value }}"
                          @endif
                        @endif @endforeach
                                    @if (
                                        (isset($field['value']) &&
                                            is_array($field['value']) &&
                                            (in_array($item->id, $field['value'][1]->pluck('id', 'id')->toArray()) || isset($secondary_ids[$item->id]))) ||
                                            ($old_secondary_dependency && in_array($item->id, $old_secondary_dependency))) checked = "checked"
                                  @if (isset($secondary_ids[$item->id]))
                                    disabled = disabled @endif
                                    @endif
                                >
                                <label class="font-weight-normal">
                                    {{ $item->name }}
                                </label>
                            </div>
                        </button>
                    @else
                        <div class="bg-primary text-white justify-content-center text-center h-100 d-flex align-items-center"
                            style="border-radius: 4px;">
                            <label class="font-weight-normal w-100" for="subitem{{ $item->id }}">
                                <input id="subitem{{ $item->id }}" data-id="{{ $item->id }}"
                                    class="secondary_list" type="checkbox"
                                    @foreach ($secondary_dependency as $attribute => $value)
                                                @if (is_string($attribute) && $attribute != 'value')
                                                    @if ($attribute == 'name')
                                                    {{ $attribute }}="{{ $value }}_show[]"
                                                    @else
                                                    {{ $attribute }}="{{ $value }}"
                                                    @endif
                                                @endif @endforeach
                                    value="{{ $item->id }}"
                                    @if (
                                        (isset($field['value']) &&
                                            is_array($field['value']) &&
                                            (in_array($item->id, $field['value'][1]->pluck('id', 'id')->toArray()) || isset($secondary_ids[$item->id]))) ||
                                            ($old_secondary_dependency && in_array($item->id, $old_secondary_dependency))) checked = "checked"
                                                        @if (isset($secondary_ids[$item->id]))
                                                            disabled = disabled @endif
                                    @endif
                                >
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
                                    <button
                                        class="btn btn-info justify-content-start dropdown-toggle dropdown-menu-btns w-100"
                                        type="button" id="dropdownMenuButton{{ $subitem->id }}">
                                        <div class="checkbox checkbox-main">
                                            <input type="checkbox" value="{{ $subitem->id }}"
                                                data-id="{{ $subitem->id }}" class="secondary_list"
                                                @foreach ($secondary_dependency as $attribute => $value)
                                                    @if (is_string($attribute) && $attribute != 'value')
                                                    @if ($attribute == 'name')
                                                        {{ $attribute }}="{{ $value }}_show[]"
                                                    @else
                                                        {{ $attribute }}="{{ $value }}"
                                                    @endif
                                                    @endif @endforeach
                                                @if (
                                                    (isset($field['value']) &&
                                                        is_array($field['value']) &&
                                                        (in_array($subitem->id, $field['value'][1]->pluck('id', 'id')->toArray()) ||
                                                            isset($secondary_ids[$subitem->id]))) ||
                                                        ($old_secondary_dependency && in_array($subitem->id, $old_secondary_dependency))) checked = "checked"
                                                        @if (isset($secondary_ids[$subitem->id]))
                                                            disabled = disabled @endif
                                                @endif
                                            >
                                            <label class="font-weight-normal">
                                                {{ $subitem->name }}
                                            </label>
                                        </div>
                                    </button>
                                @else
                                    <div class="dropdown-item checkbox">
                                        <label class="font-weight-normal w-100" for="subitem{{ $subitem->id }}">
                                            <input id="subitem{{ $subitem->id }}" data-id="{{ $subitem->id }}"
                                                class="secondary_list" type="checkbox"
                                                @foreach ($secondary_dependency as $attribute => $value)
                                                @if (is_string($attribute) && $attribute != 'value')
                                                    @if ($attribute == 'name')
                                                    {{ $attribute }}="{{ $value }}_show[]"
                                                    @else
                                                    {{ $attribute }}="{{ $value }}"
                                                    @endif
                                                @endif @endforeach
                                                value="{{ $subitem->id }}"
                                                @if (
                                                    (isset($field['value']) &&
                                                        is_array($field['value']) &&
                                                        (in_array($subitem->id, $field['value'][1]->pluck('id', 'id')->toArray()) ||
                                                            isset($secondary_ids[$subitem->id]))) ||
                                                        ($old_secondary_dependency && in_array($subitem->id, $old_secondary_dependency))) checked = "checked"
                                                        @if (isset($secondary_ids[$subitem->id]))
                                                            disabled = disabled @endif
                                                @endif
                                            >
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
                                                <label class="font-weight-normal w-100"
                                                    for="subitem{{ $sub_subitem->id }}">
                                                    <input id="subitem{{ $sub_subitem->id }}"
                                                        data-id="{{ $sub_subitem->id }}" class="secondary_list"
                                                        type="checkbox"
                                                        @foreach ($secondary_dependency as $attribute => $value)
                                                    @if (is_string($attribute) && $attribute != 'value')
                                                        @if ($attribute == 'name')
                                                        {{ $attribute }}="{{ $value }}_show[]"
                                                        @else
                                                        {{ $attribute }}="{{ $value }}"
                                                        @endif
                                                    @endif @endforeach
                                                        value="{{ $sub_subitem->id }}"
                                                        @if (
                                                            (isset($field['value']) &&
                                                                is_array($field['value']) &&
                                                                (in_array($sub_subitem->id, $field['value'][1]->pluck('id', 'id')->toArray()) ||
                                                                    isset($secondary_ids[$sub_subitem->id]))) ||
                                                                ($old_secondary_dependency && in_array($sub_subitem->id, $old_secondary_dependency))) checked = "checked"
                                                            @if (isset($secondary_ids[$sub_subitem->id]))
                                                                disabled = disabled @endif
                                                        @endif
                                                    >
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
    </div>
</div>{{-- /.container --}}


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

        $(".primary_list").click(function() {
            const group = "input.primary_list[type='checkbox'][name='" + $(this).attr("name") + "']";

            // Deselect all checkboxes in the group
            $(group).prop("checked", false).trigger('change');

            // Toggle the clicked checkbox's state
            $(this).prop("checked", !$(this).data('waschecked')).trigger('change');

            // Store the state
            $(group).data('waschecked', false);
            $(this).data('waschecked', $(this).prop('checked')).trigger('change');
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
            refreshMainCheckboxStatus()
        });
    </script>
    <script>
        var {{ $field['field_unique_name'] }} = {!! $dependencyJson !!};
    </script>

    {{-- include checklist_dependency js --}}
    @bassetBlock('backpack/crud/fields/checklist-dependency-field.js')
        <script>
            function refreshMainCheckboxStatus() {
                $('.dropdown-menu input[type=checkbox]').each(function() {
                    const dropdown = $(this).parent().parent().parent();

                    if (dropdown.find(':checked').length == 0)
                        dropdown.prev().find('.checkbox-main input[type=checkbox]').prop('checked', false)
                    else
                        dropdown.prev().find('.checkbox-main input[type=checkbox]').prop('checked', true)
                });
            }

            function bpFieldInitChecklistDependencyElement(element) {

                var unique_name = element.data('entity');
                var dependencyJson = window[unique_name];
                var thisField = element;
                var handleCheckInput = function(el, field, dependencyJson) {
                    let idCurrent = el.data('id');
                    //add hidden field with this value
                    let nameInput = field.find('.hidden_fields_primary').data('name');
                    if (field.find('input.primary_hidden[value="' + idCurrent + '"]').length === 0) {
                        let inputToAdd = $('<input type="hidden" class="primary_hidden" name="' + nameInput +
                            '[]" value="' + idCurrent + '">');

                        field.find('.hidden_fields_primary').append(inputToAdd);
                        field.find('.hidden_fields_primary').find('input.primary_hidden[value="' + idCurrent + '"]')
                            .trigger('change');
                    }
                    $.each(dependencyJson[idCurrent], function(key, value) {
                        //check and disable secondies checkbox
                        field.find('input.secondary_list[value="' + value + '"]').prop("checked", true);
                        field.find('input.secondary_list[value="' + value + '"]').prop("disabled", true);
                        field.find('input.secondary_list[value="' + value + '"]').attr('forced-select', 'true');
                        //remove hidden fields with secondary dependency if was set
                        var hidden = field.find('input.secondary_hidden[value="' + value + '"]');
                        if (hidden)
                            hidden.remove();
                    });
                };

                thisField.find('div.hidden_fields_primary').children('input').first().on('CrudField:disable', function(e) {
                    let input = $(e.target);
                    input.parent().parent().find('input[type=checkbox]').attr('disabled', 'disabled');
                    input.siblings('input').attr('disabled', 'disabled');
                });

                thisField.find('div.hidden_fields_primary').children('input').first().on('CrudField:enable', function(e) {
                    let input = $(e.target);
                    input.parent().parent().find('input[type=checkbox]').not('[forced-select]').removeAttr('disabled');
                    input.siblings('input').removeAttr('disabled');
                });

                thisField.find('div.hidden_fields_secondary').children('input').first().on('CrudField:disable', function(e) {
                    let input = $(e.target);
                    input.parent().parent().find('input[type=checkbox]').attr('disabled', 'disabled');
                    input.siblings('input').attr('disabled', 'disabled');
                });

                thisField.find('div.hidden_fields_secondary').children('input').first().on('CrudField:enable', function(e) {
                    let input = $(e.target);
                    input.parent().parent().find('input[type=checkbox]').not('[forced-select]').removeAttr('disabled');
                    input.siblings('input').removeAttr('disabled');
                });

                thisField.find('.primary_list').each(function() {
                    var checkbox = $(this);
                    // re-check the secondary boxes in case the primary is re-checked from old.
                    if (checkbox.is(':checked')) {
                        handleCheckInput(checkbox, thisField, dependencyJson);
                    }
                    // register the change event to handle subsquent checkbox state changes.
                    checkbox.change(function() {
                        if (checkbox.is(':checked')) {
                            handleCheckInput(checkbox, thisField, dependencyJson);
                        } else {
                            let idCurrent = checkbox.data('id');
                            //remove hidden field with this value.
                            thisField.find('input.primary_hidden[value="' + idCurrent + '"]').remove();

                            // uncheck and active secondary checkboxs if are not in other selected primary.
                            var secondary = dependencyJson[idCurrent];

                            var selected = [];
                            thisField.find('input.primary_hidden').each(function(index, input) {
                                selected.push($(this).val());
                            });

                            $.each(secondary, function(index, secondaryItem) {
                                var ok = 1;

                                $.each(selected, function(index2, selectedItem) {
                                    if (dependencyJson[selectedItem].indexOf(secondaryItem) != -
                                        1) {
                                        ok = 0;
                                    }
                                });

                                if (ok) {
                                    thisField.find('input.secondary_list[value="' + secondaryItem +
                                        '"]').prop('checked', false);
                                    thisField.find('input.secondary_list[value="' + secondaryItem +
                                        '"]').prop('disabled', false);
                                    thisField.find('input.secondary_list[value="' + secondaryItem +
                                        '"]').removeAttr('forced-select');
                                }
                            });

                        }
                        refreshMainCheckboxStatus();
                    });
                });

                refreshMainCheckboxStatus();
                thisField.find('.secondary_list').click(function() {

                    var idCurrent = $(this).data('id');
                    if ($(this).is(':checked')) {
                        //add hidden field with this value
                        var nameInput = thisField.find('.hidden_fields_secondary').data('name');
                        var inputToAdd = $('<input type="hidden" class="secondary_hidden" name="' + nameInput +
                            '[]" value="' + idCurrent + '">');

                        thisField.find('.hidden_fields_secondary').append(inputToAdd);

                    } else {
                        //remove hidden field with this value
                        thisField.find('input.secondary_hidden[value="' + idCurrent + '"]').remove();
                    }
                });

            }
        </script>
    @endBassetBlock
@endpush
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
