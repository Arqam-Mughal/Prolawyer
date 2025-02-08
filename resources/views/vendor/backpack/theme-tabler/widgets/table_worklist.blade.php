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

        <ul class="nav nav-tabs justify-content-between" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="home-tab" data-bs-toggle="tab"
                    data-bs-target="#inprogress-tab-pane" type="button" role="tab"
                    aria-controls="inprogress-tab-pane" aria-selected="true">IN PROGRESS</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#completed-tab-pane"
                    type="button" role="tab" aria-controls="completed-tab-pane"
                    aria-selected="false">COMPLETED</button>
            </li>
        </ul>
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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($widget['table_inprogress']['data'] as $row)
                            <tr>
                                @foreach ($widget['table_inprogress']['columns'] as $column => $label)
                                    <td>{!! $row[$column] !!}</td>
                                @endforeach
                                <td>
                                    <div class="d-flex gap-2">
                                        <a class="btn btn-primary" title="Mark as Complete"
                                            href="{{ route('worklist.mark_as_completed', ['worklist' => $row['id']]) }}"><i
                                                class="la la-check"></i></a>
                                        <a class="btn btn-primary"
                                            href="{{ route('worklist.edit', ['id' => $row['id']]) }}"><i
                                                class="la la-pencil"></i></a>

                                        @php
                                            $crud = app()->make('crud');
                                            $crud->setModel(\App\Models\Worklist::class);
                                            $crud->setRoute(config('backpack.base.route_prefix') . '/worklist');
                                            $crud->allowAccess('delete');
                                        @endphp

                                        @include('crud::buttons.delete_worklist', [
                                            'crud' => $crud,
                                            'entry' => \App\Models\Worklist::find($row['id']),
                                        ])
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="tab-pane fade" id="completed-tab-pane" role="tabpanel" aria-labelledby="completed-tab"
                tabindex="0">
                <table class="table table-striped widget-table" id="datatableWorklistCompleted">
                    <thead>
                        <tr>
                            @foreach ($widget['table_completed']['columns'] as $column => $label)
                                <th style="width: 2000px;">{{ $label }}</th>
                            @endforeach
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($widget['table_completed']['data'] as $row)
                            <tr>
                                @foreach ($widget['table_completed']['columns'] as $column => $label)
                                    <td>{!! $row[$column] !!}</td>
                                @endforeach
                                <td>
                                    <div class="d-flex gap-2">
                                        <a class="btn btn-primary" title="Mark as In-progress"
                                            href="{{ route('worklist.mark_as_incomplete', ['worklist' => $row['id']]) }}"><i
                                                class="la la-close"></i></a>
                                        <a class="btn btn-primary"
                                            href="{{ route('worklist.edit', ['id' => $row['id']]) }}"><i
                                                class="la la-pencil"></i></a>

                                        @php
                                            $crud = app()->make('crud');
                                            $crud->setModel(\App\Models\Worklist::class);
                                        @endphp

                                        @include('crud::buttons.delete_worklist', [
                                            'crud' => $crud,
                                            'entry' => \App\Models\Worklist::find($row['id']),
                                        ])
                                    </div>
                                </td>
                            </tr>
                        @endforeach
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

        Number('{{ count($widget['table_completed']['data']) }}') > 10 && $(
            "#datatableWorklistCompleted").dataTable();
    </script>
@endpush
