@if ($crud->hasAccess('create'))
    <a href="{{ url('admin/case-model-all-cases/create') }}" class="btn btn-primary" data-style="zoom-in">
        <span><i class="la la-plus"></i> 
            {{ trans('backpack::crud.add') }} 
            
            Case
            
        </span>
    </a>
@endif