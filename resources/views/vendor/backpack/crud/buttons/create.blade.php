@if ($crud->hasAccess('create'))
    <a href="{{ url($crud->route.'/create') }}" class="btn btn-primary" data-style="zoom-in">
        <span><i class="la la-plus"></i> 
            {{ trans('backpack::crud.add') }} 
            @php $current_route = Route::currentRouteName(); @endphp
            @if (str_contains( $current_route, 'case-model-' ))
            Case
            @else
            {{ $crud->entity_name }}
            @endif
        </span>
    </a>
@endif