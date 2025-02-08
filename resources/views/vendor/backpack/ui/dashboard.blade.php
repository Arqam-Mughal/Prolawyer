@extends(backpack_view('blank'))

@section('before_styles')
    <style>
        :root {
            --fc-button-hover-bg-color: var(--tblr-primary);
            --fc-button-hover-border-color: var(--tblr-primary);
            --fc-button-active-bg-color: var(--tblr-primary);
            --fc-button-active-border-color: var(--tblr-primary);
            --fc-event-bg-color: var(--tblr-primary);
            --fc-event-border-color: var(--tblr-primary);
        }

        .fc-button-group button,
        .fc-today-button {
            text-transform: capitalize !important;
        }

        .page-body {
            font-family: 'Poppins', var(--tblr-body-font-family) !important;
        }

        .card-dashboard {
            border-radius: 15px !important;
            box-shadow: 1px 1px 20px #0002 !important;
        }

        .card-dashboard:hover {
            background-color: var(--tblr-primary);
            color: var(--tblr-primary-fg);
        }

        a.card-link {
            text-decoration: none;
            color: var(--tblr-card-color);
        }

        a.card-link:hover {
            text-decoration: none;
        }


    </style>
@endsection

@php
    $widgets_defination_array = [
        [
            'type' => 'card_link',
            'link' => route('case-model-active.index'),
            'wrapper' => ['class' => 'col-sm-6 col-md-4 my-2'], // optional
            'class' => 'card card-dashboard', // optional
            'style' => 'float: left;',
            'content' => [
                'header' => '<h1 class="mb-3">Active Cases</h1>', // optional
                'body' => sprintf(
                    '
                        <p class="my-1">District Courts and Tribunals: %s</p>
                        <p class="my-1">High Courts and Supreme Court: %s</p>
                    ',
                    ...[
                        DB::table(
                            DB::raw(
                                "(select count(*) as total from cases where status = 'Open' AND tabs = 'District Courts and Tribunals' AND decided_toggle IS NULL AND abbondend_toggle IS NULL" .
                                    (backpack_user()->hasAnyRole(
                                        \App\Models\Role::where('type', 'regular_user')->pluck('name')->toArray(),
                                    )
                                        ? ' AND lawyer_id = ' . auth()->id()
                                        : '') .
                                    (backpack_user()->hasAnyRole(
                                        \App\Models\Role::where('type', 'sub_lawyer')->pluck('name')->toArray(),
                                    )
                                        ? ' AND lawyer_id = ' . auth()->user()->senior_lawyer_id
                                        : '') .
                                    ') as sub',
                            ),
                        )->value('total'),
                        DB::table(
                            DB::raw(
                                "(select count(*) as total from cases where status = 'Open' AND tabs = 'High Court' AND decided_toggle IS NULL AND abbondend_toggle IS NULL" .
                                    (backpack_user()->hasAnyRole(
                                        \App\Models\Role::where('type', 'regular_user')->pluck('name')->toArray(),
                                    )
                                        ? ' AND lawyer_id = ' . auth()->id()
                                        : '') .
                                    (backpack_user()->hasAnyRole(
                                        \App\Models\Role::where('type', 'sub_lawyer')->pluck('name')->toArray(),
                                    )
                                        ? ' AND lawyer_id = ' . auth()->user()->senior_lawyer_id
                                        : '') .
                                    ') as sub',
                            ),
                        )->value('total'),
                    ],
                ),
            ],
        ],
        [
            'type' => 'card_link',
            'link' => route('case-model-today.index'),
            'wrapper' => ['class' => 'col-sm-6 col-md-4 my-2'], // optional
            'class' => 'card card-dashboard', // optional
            'style' => 'float: left;',
            'content' => [
                'header' => '<h1 class="mb-3">Today\'s Cases</h1>', // optional
                'body' => sprintf(
                    '
                        <p class="my-1">District Courts and Tribunals: %s</p>
                        <p class="my-1">High Courts and Supreme Court: %s</p>
                    ',
                    ...[
                        DB::table(
                            DB::raw(
                                "(select count(*) as total from cases where status = 'Open' AND tabs = 'District Courts and Tribunals' AND DATE(next_date)='" .
                                    today()->format('Y-m-d') .
                                    "' AND decided_toggle IS NULL AND abbondend_toggle IS NULL" .
                                    (backpack_user()->hasAnyRole(
                                        \App\Models\Role::where('type', 'regular_user')->pluck('name')->toArray(),
                                    )
                                        ? ' AND lawyer_id = ' . auth()->id()
                                        : '') .
                                    (backpack_user()->hasAnyRole(
                                        \App\Models\Role::where('type', 'sub_lawyer')->pluck('name')->toArray(),
                                    )
                                        ? ' AND lawyer_id = ' . auth()->user()->senior_lawyer_id
                                        : '') .
                                    ') as sub',
                            ),
                        )->value('total'),
                        DB::table(
                            DB::raw(
                                "(select count(*) as total from cases where status = 'Open' AND tabs = 'High Court' AND DATE(next_date)='" .
                                    today()->format('Y-m-d') .
                                    "' AND decided_toggle IS NULL AND abbondend_toggle IS NULL" .
                                    (backpack_user()->hasAnyRole(
                                        \App\Models\Role::where('type', 'regular_user')->pluck('name')->toArray(),
                                    )
                                        ? ' AND lawyer_id = ' . auth()->id()
                                        : '') .
                                    (backpack_user()->hasAnyRole(
                                        \App\Models\Role::where('type', 'sub_lawyer')->pluck('name')->toArray(),
                                    )
                                        ? ' AND lawyer_id = ' . auth()->user()->senior_lawyer_id
                                        : '') .
                                    ') as sub',
                            ),
                        )->value('total'),
                    ],
                ),
            ],
        ],
        [
            'type' => 'card_link',
            'link' => route('case-model-tomorrow.index'),
            'wrapper' => ['class' => 'col-sm-6 col-md-4 my-2'], // optional
            'class' => 'card card-dashboard', // optional
            'style' => 'float: left;',
            'content' => [
                'header' => '<h1 class="mb-3">Tomorrow\'s Cases</h1>', // optional
                'body' => sprintf(
                    '
                        <p class="my-1">District Courts and Tribunals: %s</p>
                        <p class="my-1">High Courts and Supreme Court: %s</p>
                    ',
                    ...[
                        DB::table(
                            DB::raw(
                                "(select count(*) as total from cases where status = 'Open' AND tabs = 'District Courts and Tribunals' AND DATE(next_date)='" .
                                    \Carbon\Carbon::tomorrow()->format('Y-m-d') .
                                    "' AND decided_toggle IS NULL AND abbondend_toggle IS NULL" .
                                    (backpack_user()->hasAnyRole(
                                        \App\Models\Role::where('type', 'regular_user')->pluck('name')->toArray(),
                                    )
                                        ? ' AND lawyer_id = ' . auth()->id()
                                        : '') .
                                    (backpack_user()->hasAnyRole(
                                        \App\Models\Role::where('type', 'sub_lawyer')->pluck('name')->toArray(),
                                    )
                                        ? ' AND lawyer_id = ' . auth()->user()->senior_lawyer_id
                                        : '') .
                                    ') as sub',
                            ),
                        )->value('total'),
                        DB::table(
                            DB::raw(
                                "(select count(*) as total from cases where status = 'Open' AND tabs = 'High Court' AND DATE(next_date)='" .
                                    \Carbon\Carbon::tomorrow()->format('Y-m-d') .
                                    "' AND decided_toggle IS NULL AND abbondend_toggle IS NULL" .
                                    (backpack_user()->hasAnyRole(
                                        \App\Models\Role::where('type', 'regular_user')->pluck('name')->toArray(),
                                    )
                                        ? ' AND lawyer_id = ' . auth()->id()
                                        : '') .
                                    (backpack_user()->hasAnyRole(
                                        \App\Models\Role::where('type', 'sub_lawyer')->pluck('name')->toArray(),
                                    )
                                        ? ' AND lawyer_id = ' . auth()->user()->senior_lawyer_id
                                        : '') .
                                    ') as sub',
                            ),
                        )->value('total'),
                    ],
                ),
            ],
        ],
        [
            'type' => 'card_link',
            'link' => route('case-model-date-awaited.index'),
            'wrapper' => ['class' => 'col-sm-6 col-md-4 my-2'], // optional
            'class' => 'card card-dashboard', // optional
            'style' => 'float: left;',
            'content' => [
                'header' => '<h1 class="mb-3">Date Awaited Cases</h1>', // optional
                'body' => sprintf(
                    '
                        <p class="my-1">District Courts and Tribunals: %s</p>
                        <p class="my-1">High Courts and Supreme Court: %s</p>
                    ',
                    ...[
                        DB::table(
                            DB::raw(
                                "(select count(*) as total from cases where status = 'Open' AND tabs = 'District Courts and Tribunals' AND DATE(next_date)<'" .
                                    \Carbon\Carbon::tomorrow()->format('Y-m-d') .
                                    "' AND decided_toggle IS NULL AND abbondend_toggle IS NULL" .
                                    (backpack_user()->hasAnyRole(
                                        \App\Models\Role::where('type', 'regular_user')->pluck('name')->toArray(),
                                    )
                                        ? ' AND lawyer_id = ' . auth()->id()
                                        : '') .
                                    (backpack_user()->hasAnyRole(
                                        \App\Models\Role::where('type', 'sub_lawyer')->pluck('name')->toArray(),
                                    )
                                        ? ' AND lawyer_id = ' . auth()->user()->senior_lawyer_id
                                        : '') .
                                    ') as sub',
                            ),
                        )->value('total'),
                        DB::table(
                            DB::raw(
                                "(select count(*) as total from cases where status = 'Open' AND tabs = 'High Court' AND DATE(next_date)<'" .
                                    \Carbon\Carbon::tomorrow()->format('Y-m-d') .
                                    "' AND decided_toggle IS NULL AND abbondend_toggle IS NULL" .
                                    (backpack_user()->hasAnyRole(
                                        \App\Models\Role::where('type', 'regular_user')->pluck('name')->toArray(),
                                    )
                                        ? ' AND lawyer_id = ' . auth()->id()
                                        : '') .
                                    (backpack_user()->hasAnyRole(
                                        \App\Models\Role::where('type', 'sub_lawyer')->pluck('name')->toArray(),
                                    )
                                        ? ' AND lawyer_id = ' . auth()->user()->senior_lawyer_id
                                        : '') .
                                    ') as sub',
                            ),
                        )->value('total'),
                    ],
                ),
            ],
        ],
        [
            'type' => 'card_link',
            'link' => route('case-model-dailyboard.index'),
            'wrapper' => ['class' => 'col-sm-6 col-md-4 my-2'], // optional
            'class' => 'card card-dashboard', // optional
            'style' => 'float: left;',
            'content' => [
                'header' => '<h1 class="mb-3">Daily Board</h1>', // optional
                'body' => sprintf(
                    '
                        <p class="my-1">District Courts and Tribunals: %s</p>
                        <p class="my-1">High Courts and Supreme Court: %s</p>
                    ',
                    ...[
                        DB::table(
                            DB::raw(
                                "(select count(*) as total from cases where status = 'Open' AND tabs = 'District Courts and Tribunals'
                                    AND (
                                        (history IS NOT NULL AND history != '' AND JSON_VALID(history) > 0 AND JSON_SEARCH(history, 'all', '" .
                                    date('d-m-Y') .
                                    "', NULL, '$**.date') IS NOT NULL)
                                    OR DATE(cases.next_date) = '" .
                                    date('Y-m-d') .
                                    "'
                                        OR DATE(cases.previous_date) = '" .
                                    date('Y-m-d') .
                                    "'
                                    ) AND decided_toggle IS NULL AND abbondend_toggle IS NULL" .
                                    (backpack_user()->hasAnyRole(
                                        \App\Models\Role::where('type', 'regular_user')->pluck('name')->toArray(),
                                    )
                                        ? ' AND lawyer_id = ' . auth()->id()
                                        : '') .
                                    (backpack_user()->hasAnyRole(
                                        \App\Models\Role::where('type', 'sub_lawyer')->pluck('name')->toArray(),
                                    )
                                        ? ' AND lawyer_id = ' . auth()->user()->senior_lawyer_id
                                        : '') .
                                    ') as sub',
                            ),
                        )->value('total'),
                        DB::table(
                            DB::raw(
                                "(select count(*) as total from cases where status = 'Open' AND tabs = 'High Court'
                                    AND (
                                        (history IS NOT NULL AND history != '' AND JSON_VALID(history) > 0 AND JSON_SEARCH(history, 'all', '" .
                                    date('d-m-Y') .
                                    "', NULL, '$**.date') IS NOT NULL)
                                        OR DATE(cases.next_date) = '" .
                                    date('Y-m-d') .
                                    "'
                                        OR DATE(cases.previous_date) = '" .
                                    date('Y-m-d') .
                                    "'
                                    ) AND decided_toggle IS NULL AND abbondend_toggle IS NULL" .
                                    (backpack_user()->hasAnyRole(
                                        \App\Models\Role::where('type', 'regular_user')->pluck('name')->toArray(),
                                    )
                                        ? ' AND lawyer_id = ' . auth()->id()
                                        : '') .
                                    (backpack_user()->hasAnyRole(
                                        \App\Models\Role::where('type', 'sub_lawyer')->pluck('name')->toArray(),
                                    )
                                        ? ' AND lawyer_id = ' . auth()->user()->senior_lawyer_id
                                        : '') .
                                    ') as sub',
                            ),
                        )->value('total'),
                    ],
                ),
            ],
        ],
        [
            'type' => 'card_link',
            'link' => route('case-model-closed.index'),
            'wrapper' => ['class' => 'col-sm-6 col-md-4 my-2'], // optional
            'class' => 'card card-dashboard', // optional
            'style' => 'float: left;',
            'content' => [
                'header' => '<h1 class="mb-3">Decided Cases</h1>', // optional
                'body' => sprintf(
                    '
                        <p class="my-1">District Courts and Tribunals: %s</p>
                        <p class="my-1">High Courts and Supreme Court: %s</p>
                    ',
                    ...[
                        DB::table(
                            DB::raw(
                                "(select count(*) as total from cases where (decided_toggle IS NOT NULL OR abbondend_toggle IS NOT NULL) AND decided_toggle='check'" .
                                    (backpack_user()->hasAnyRole(
                                        \App\Models\Role::where('type', 'regular_user')->pluck('name')->toArray(),
                                    )
                                        ? ' AND lawyer_id = ' . auth()->id()
                                        : '') .
                                    (backpack_user()->hasAnyRole(
                                        \App\Models\Role::where('type', 'sub_lawyer')->pluck('name')->toArray(),
                                    )
                                        ? ' AND lawyer_id = ' . auth()->user()->senior_lawyer_id
                                        : '') .
                                    ') as sub',
                            ),
                        )->value('total'),
                        DB::table(
                            DB::raw(
                                '(select count(*) as total from cases where decided_toggle IS NULL AND (decided_toggle IS NOT NULL OR abbondend_toggle IS NOT NULL)' .
                                    (backpack_user()->hasAnyRole(
                                        \App\Models\Role::where('type', 'regular_user')->pluck('name')->toArray(),
                                    )
                                        ? ' AND lawyer_id = ' . auth()->id()
                                        : '') .
                                    (backpack_user()->hasAnyRole(
                                        \App\Models\Role::where('type', 'sub_lawyer')->pluck('name')->toArray(),
                                    )
                                        ? ' AND lawyer_id = ' . auth()->user()->senior_lawyer_id
                                        : '') .
                                    ') as sub',
                            ),
                        )->value('total'),
                    ],
                ),
            ],
        ],
    ];

    \Backpack\CRUD\app\Library\Widget::add([
        'type' => 'div',
        'class' => 'row',
        'content' => $widgets_defination_array,
    ])->to('after_content');

    Widget::add([
        'type' => 'calendar',
        'wrapper' => ['class' => 'mt-3 col-sm-12'],
    ])->to('after_content');

    $worklists = DB::table('worklists')
        ->select('worklists.*', 'petitioners.petitioner', 'respondents.respondent')
        ->leftJoin('petitioners', 'petitioners.case_id', '=', 'worklists.case_id')
        ->leftJoin('respondents', 'respondents.case_id', '=', 'worklists.case_id');

    if (backpack_user()->hasAnyRole(\App\Models\Role::where('type', 'regular_user')->pluck('name')->toArray())) {
        $worklists = $worklists->where('user_id', backpack_user()->id);
    }


    $disp_worklists = $worklists
        ->get()
        ->where('status', '1')
        ->map(function ($worklist) {
            return [
                'id' => $worklist->id,
                'title' => $worklist->title,
                'user' => \App\Models\User::find($worklist->user_id)->name ?? '---',
                'case_link' => $worklist->case_id
                    ? sprintf(
                        '<a class="btn-link" href="%s">%s <b>VS</b> %s</a>',
                        route('case-model-all-cases.show', ['id' => $worklist->case_id]),
                        strlen($worklist->petitioner) > 35
                            ? substr($worklist->petitioner, 0, 35 - 3) . '...'
                            : $worklist->petitioner,
                        strlen($worklist->respondent) > 35
                            ? substr($worklist->respondent, 0, 35 - 3) . '...'
                            : $worklist->respondent,
                    )
                    : '---',
                'ends' => $worklist->end_option
                    ? ($worklist->end_option == 1
                        ? date('d-m-Y', strtotime($worklist->end_date))
                        : $worklist->end_occurrences . '&nbsp;occurence')
                    : 'Never',
            ];
        });

    $disp_worklists_not_completed = $worklists
        ->get()
        ->where('status', '0')
        ->map(function ($worklist) {
            return [
                'id' => $worklist->id,
                'title' => $worklist->title,
                'user' => \App\Models\User::find($worklist->user_id)->name ?? '---',
                'case_link' => $worklist->case_id
                    ? sprintf(
                        '<a class="btn-link" href="%s">%s <b>VS</b> %s</a>',
                        route('case-model-all-cases.show', ['id' => $worklist->case_id]),
                        strlen($worklist->petitioner) > 35
                            ? substr($worklist->petitioner, 0, 35 - 3) . '...'
                            : $worklist->petitioner,
                        strlen($worklist->respondent) > 35
                            ? substr($worklist->respondent, 0, 35 - 3) . '...'
                            : $worklist->respondent,
                    )
                    : '---',
                'ends' => $worklist->end_option
                    ? ($worklist->end_option == 1
                        ? date('d-m-Y', strtotime($worklist->end_date))
                        : $worklist->end_occurrences . '&nbsp;occurence')
                    : 'Never',
            ];
        });

    Widget::add([
        'type' => 'table_worklist',
        'wrapper' => ['class' => 'col-sm-12'],
        'title' => 'Worklist',
        'title_link_add' => [
            'link' => route('worklist.create'),
            'label' => 'Add Worklist'
        ],
        'table_completed' => [
            'columns' => [
                'title' => 'Title',
                'user' => 'Assigned to',
                'case_link' => 'Case',
                'ends' => 'Ends',
            ],
            'data' => $disp_worklists,
        ],
        'table_inprogress' => [
            'columns' => [
                'title' => 'Title',
                'user' => 'Assigned to',
                'case_link' => 'Case',
                'ends' => 'Ends',
            ],
            'data' => $disp_worklists_not_completed,
        ],
    ])->to('after_content');
@endphp

@section('content')
    <div class="my-5 row d-flex align-items-center justify-space-between">
        <h2 class="col-md-2 m-0">Dashboard</h2>
        <a href="{{ backpack_url('case-model-all-cases/create') }}" class="btn btn-primary text-uppercase"
            style="width: 120px; margin-left: 10px;">
            <i class="la la-plus"></i> <span style="margin-left: 5px;">New case</span>
        </a>
    </div>
@endsection

@section('after_scripts')
    <script>
        const count_worklists = '{{ count($disp_worklists) }}';
        count_worklists > 10 && $('.widget-table').dataTable();
    </script>
    @php
        $holidays = \App\Models\Holiday::all();

        $events = [];
        if (backpack_user()->hasRole(\App\Models\Role::where('type', 'system_user')->pluck('name')->toArray())) {
            $events = \App\Models\Event::all();
        } elseif (
            backpack_user()->hasAnyRole(\App\Models\Role::where('type', 'regular_user')->pluck('name')->toArray())
        ) {
            $events = \App\Models\Event::where('for_whom', 'all')->orWhereIn(
                'for_whom',
                backpack_user()->roles->pluck('name')->toArray(),
            );
        }

        $calendar_events = [];
        $count_event = 0;
        foreach ($holidays as $k => $holiday) {
            $calendar_events[$k]['title'] = $holiday->name;
            $calendar_events[$k]['url'] = $holiday->name;
            $calendar_events[$k]['description'] = $holiday->name;

            if ($holiday->type == 0) {
                $calendar_events[$k]['date'] = $holiday->date;
            } else {
                $types = explode(',', $holiday->date);
                $calendar_events[$k]['start'] = $types[0];
                $calendar_events[$k]['end'] = Carbon::parse($types[1])
                    ->addDays(1)
                    ->format('Y-m-d');
            }
            $count_event = $k;
            $count_event++;
        }

        foreach ($events as $k => $event) {
            $calendar_events[$count_event]['title'] = $event->title;

            $calendar_events[$count_event]['start'] = $event->from_date;

            $calendar_events[$count_event]['end'] = Carbon::parse($event->to_date)
                ->addDays(1)
                ->format('Y-m-d');
            $calendar_events[$count_event]['description'] = $event->description;
            $calendar_events[$count_event]['url'] = $event->image;

            $count_event++;
        }

        $event_courts_count = \App\Models\CaseModel::selectRaw('DATE(cases.next_date) as start,
                        COUNT(CASE WHEN cases.tabs = "District Courts and Tribunals" THEN 1 ELSE NULL END) as district_cases,
                        COUNT(CASE WHEN cases.tabs = "High Court" THEN 1 ELSE NULL END) as high_court_cases');

        if (backpack_user()->hasAnyRole(\App\Models\Role::where('type', 'regular_user')->pluck('name')->toArray())) {
            $event_courts_count = $event_courts_count->where('lawyer_id', auth()->id());
        }

        if (backpack_user()->hasAnyRole(\App\Models\Role::where('type', 'sub_lawyer')->pluck('name')->toArray())) {
            $event_courts_count = $event_courts_count->where('lawyer_id', auth()->user()->senior_lawyer_id);
        }

        $event_courts_count = $event_courts_count->groupBy('next_date')->get();

        foreach ($event_courts_count as $k => $cases) {
            $_title = '';
            $_title =
                '<a class="anchor_tag" href="' .
                backpack_url('case-model-dailyboard?tabs=' . urlencode('District Courts and Tribunals')) .
                '&date=' .
                $cases->start .
                '" target="_blank" style="color:white;">DC ' .
                $cases->district_cases .
                '</a>   <span style="margin: 0 5px;">&amp;</span>   <a href="' .
                backpack_url('case-model-dailyboard?tabs=' . urlencode('High Court')) .
                '&date=' .
                $cases->start .
                '" style="color:white;" class="anchor_tag">HC ' .
                $cases->high_court_cases .
                '</a>';
            $calendar_events[$count_event]['title'] = htmlspecialchars_decode($_title);

            $calendar_events[$count_event]['start'] = $cases->start;

            $calendar_events[$count_event]['end'] = \Carbon\Carbon::parse($cases->start)
                ->addDays(1)
                ->format('Y-m-d');

            $count_event++;
        }
    @endphp
    @basset('https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js')
    <script>
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            locale: 'en',
            direction: 'ltr',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            eventClick: function(info) {
                document.getElementById('modalTitle').innerHTML = info.event.title;
                document.getElementById('modalBody').innerHTML = info.event.extendedProps.description;
                document.getElementById('image').setAttribute('src', info.event.url);
                var modal = new bootstrap.Modal(document.getElementById('fullCalModal'));
                modal.show();
                return false;
            },
            height: 650,
            events: @json($calendar_events),
            eventContent: function(arg) {
                var titleEl = document.createElement('div');
                titleEl.classList.add('fc-title');
                titleEl.innerHTML = arg.event.title;
                var arrayOfDomNodes = [titleEl]
                return {
                    domNodes: arrayOfDomNodes
                }
            }
        });

        calendar.render();

        // var body = body.content;
        var message = @json(session('permission_error'));

if (message) {
    $('.page-body').html('<h1 class="position-absolute d-flex justify-content-center align-items-center w-100 h-100">'+message+'</h1>');  // Clear the content of .container-xl
}
    </script>
@endsection
