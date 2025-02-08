@includeWhen(!empty($widget['wrapper']), backpack_view('widgets.inc.wrapper_start'))

<div class="mb-4">
    <div class="card">
        <div class="card-header">
            <h2 style="margin: 0; margin-right: 10px;">{{ $widget['title'] }}</h2>

            @if (isset($widget['title_link_add']))
                <a class="btn btn-info mx-2"
                    href="{{ $widget['title_link_add']['link'] }}">{{ $widget['title_link_add']['label'] }}</a>
            @endif

            @if (isset($widget['title_link']))
                <a class="btn btn-info mx-2"
                    href="{{ $widget['title_link']['link'] }}">{{ $widget['title_link']['label'] }}</a>
            @endif
        </div>

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
        Number('{{ count($widget['data']) }}') > 10 && $(
            "#datatable{{ str_replace(' ', '', $widget['id'] ?? $widget['title']) }}").dataTable();
    </script>
@endpush
