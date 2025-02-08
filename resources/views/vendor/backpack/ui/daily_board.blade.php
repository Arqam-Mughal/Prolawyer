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

   $data = isset($data) ? $data : array();

   Widget::add([
        'type' => 'table_daily_board',
        'wrapper' => ['class' => 'col-sm-12'],
        'title' => 'Courts',
        'table_inprogress' => [
            'columns' => [
                'item' => 'Item No',
                'court_id' => 'Court ID',
                'title' => 'Title',
                'case_no' => 'Case No.',
            ],
            'data' => $data,
        ],
    ])->to('after_content');
@endphp

@section('content')
    <div class="my-5 row d-flex align-items-center justify-space-between">
        <h2 class="col-md-2 m-0">Daily Board</h2>
        <form method="post" action={{ route('dailyboard.fetch') }}>
            @csrf
            <div class="row form-group">
                <div class="primary_input col-md-4">
                    <label style="margin-top:5px;float:right;">Select court:</label>
                </div>
                <div class="primary_input col-md-4">
                    <select name="court" class="primary_select hearingCourt form-control">
                        @foreach ($courts as $key => $court)
                            <option value="{!! $court->name !!}">{!! $court->name !!}</option>
                        @endforeach
                    </select>
                </div>
                <div class="primary_input col-md-4">
                    <button class="btn btn-primary submit" type="submit"> Search
                    </button>
                </div>
            </div>
        </form>

        @if ( isset( $error ) )
            <p>{{ $error }}</p>
        @endif
    </div>
@endsection

@section('after_scripts')
    <script>
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
    </script>
@endsection
