@if ($crud->hasAccess('show', $entry))
	@if (!$crud->model->translationEnabled())

	@php $current_route = Route::currentRouteName(); @endphp
            @if (str_contains( $current_route, 'case-model-' ))
			<a href="{{ url('admin/case-model-all-cases/'. $entry->getKey() .'/show') }}" class="btn btn-sm btn-link">
				<span><i class="la la-eye"></i> View</span>
			</a>
			@else
	<a href="{{ url($crud->route.'/'.$entry->getKey().'/show') }}" class="btn btn-sm btn-link">
		<span><i class="la la-eye"></i> {{ trans('backpack::crud.preview') }}</span>
	</a>
	@endif

	@else

	{{-- Edit button group --}}
	<div class="btn-group">
	  <a href="{{ url($crud->route.'/'.$entry->getKey().'/show') }}" class="btn btn-sm btn-link pr-0">
	  	<span><i class="la la-eye"></i> {{ trans('backpack::crud.preview') }}</span>
	  </a>
	  <a class="btn btn-sm btn-link dropdown-toggle text-primary pl-1" data-toggle="dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	    <span class="caret"></span>
	  </a>
	  <ul class="dropdown-menu dropdown-menu-right">
  	    <li class="dropdown-header">{{ trans('backpack::crud.preview') }}:</li>
	  	@foreach ($crud->model->getAvailableLocales() as $key => $locale)
		  	<a class="dropdown-item" href="{{ url($crud->route.'/'.$entry->getKey().'/show') }}?_locale={{ $key }}">{{ $locale }}</a>
	  	@endforeach
	  </ul>
	</div>

	@endif
@endif
