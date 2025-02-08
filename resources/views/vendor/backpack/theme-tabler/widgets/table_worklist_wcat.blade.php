{{-- Only used in dashboard  --}}
@includeWhen(!empty($widget['wrapper']), backpack_view('widgets.inc.wrapper_start'))

<div class="mb-4">
    <div class="card">
        <div class="card-header">
            <h2 style="margin: 0; margin-right: 10px;">{{ $widget['title'] }}</h2>
            @if (isset($widget['title_link']))
                <a class="btn btn-info"
                    href="{{ $widget['title_link']['link'] }}">{{ $widget['title_link']['label'] }}</a>
            @endif
        </div>
        
        @php
            $crud = app()->make('crud');
            $crud->setModel(\App\Models\Worklist::class);
            $crud->addField([
                'name' => 'category_id',
                'type' => 'select2_from_array',
                'options' => \App\Models\WorklistCategory::pluck('name', 'id')->toArray(),
                'attributes' => [
                    'id' => 'worklistCategory'
                ]
            ]);
        @endphp

        <form method="post">
            @include('crud::form_content', ['fields' => $crud->fields(), 'action' => 'create'])
        </form>

        @if (count($widget['data']))
            <table class="table table-striped widget-table" id="datatable{{ str_replace(' ', '', $widget['title']) }}">
                <thead>
                    <tr>
                        @foreach ($widget['columns'] as $column => $label)
                            <th>{{ $label }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($widget['data'] as $row)
                        <tr>
                            @foreach ($widget['columns'] as $column => $label)
                                <td>{!! $row[$column] !!}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="text-center p-3">
                No data.
            </div>
        @endif
    </div>
</div>

@basset('https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css')
@basset('https://cdn.datatables.net/fixedheader/3.3.1/css/fixedHeader.dataTables.min.css')
@basset('https://cdn.datatables.net/responsive/2.4.0/css/responsive.dataTables.min.css')
@basset('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css')
@basset('https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css')

@basset('https://unpkg.com/jquery@3.6.1/dist/jquery.min.js')
@basset('https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js')
@basset('https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js')
@basset('https://cdn.datatables.net/responsive/2.4.0/js/dataTables.responsive.min.js')
@basset('https://cdn.datatables.net/responsive/2.4.0/css/responsive.dataTables.min.css')
@basset('https://cdn.datatables.net/fixedheader/3.3.1/js/dataTables.fixedHeader.min.js')
@basset('https://cdn.datatables.net/fixedheader/3.3.1/css/fixedHeader.dataTables.min.css')
@includeWhen(!empty($widget['wrapper']), backpack_view('widgets.inc.wrapper_end'))

@push('after_styles')
    <style>
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_info {
            padding: 8px 12px;
        }

        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_paginate {
            padding: 8px 12px;
        }
    </style>
@endpush

@push('after_scripts')
    <script>
        Number('{{ count($widget['data']) }}') > 10 && $("#datatable{{ str_replace(' ', '', $widget['title']) }}")
            .dataTable();
    </script>

    <!-- include select2 js-->
    <script src="{{ basset('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js') }}"></script>
    
    <script>
        function bpFieldInitSelect2FromArrayElement(element) {
            if (!element.hasClass("select2-hidden-accessible"))
                {
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

        bpFieldInitSelect2FromArrayElement($('.select2_from_array'));

        $('#worklistCategory').change(function() {
            location.href = 'dashboard?worklist_category_id=' + $(this).val();
        });
    </script>
@endpush
