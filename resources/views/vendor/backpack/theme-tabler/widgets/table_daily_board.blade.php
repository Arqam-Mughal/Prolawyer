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

        <div class="tab-content">
            <div class="tab-pane fade show active" id="inprogress-tab-pane" role="tabpanel"
                aria-labelledby="inprogress-tab" tabindex="0">
                <table class="table table-striped widget-table" id="datatableWorklistInprogress">
                    <thead>
                        <tr>
                            @foreach ($widget['table_inprogress']['columns'] as $column => $label)
                                {{-- Just maximum width --}}
                                <th style="width: 2000px;">{{ $label }}</th>
                                
                            @endforeach
                        </tr>
                    </thead>
                    
                    <tbody>
                        @if ( !empty($widget['table_inprogress']['data']) )
                            @foreach ($widget['table_inprogress']['data']->display_board as $key => $record)
                                <tr>
                                    <td>{!! $record->item !!}</td>
                                    <td>{!! $record->court !!}</td>
                                    <td>{!! $record->title !!}</td>
                                    <td>{!! $record->caseNo !!}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
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
        Number('{{ count($widget['table_inprogress']['data']) }}') > 10 && $(
            "#datatableWorklistInprogress").dataTable();
    </script>
@endpush
