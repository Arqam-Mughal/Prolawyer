<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EventsController extends Controller
{
    public function index()
    {
        $holidays = \App\Models\Holiday::all();

        $events = [];
        if (auth()->user()->hasRole(\App\Models\Role::where('type', 'system_user')->pluck('name')->toArray())) {
            $events = \App\Models\Event::all();
        } elseif (
            auth()->user()->hasAnyRole(\App\Models\Role::where('type', 'regular_user')->pluck('name')->toArray())
        ) {
            $events = \App\Models\Event::where('for_whom', 'all')->orWhereIn(
                'for_whom',
                auth()->user()->roles->pluck('name')->toArray(),
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

        if (auth()->user()->hasAnyRole(\App\Models\Role::where('type', 'regular_user')->pluck('name')->toArray())) {
            $event_courts_count = $event_courts_count->where('lawyer_id', auth()->id());
        }

        if (auth()->user()->hasAnyRole(\App\Models\Role::where('type', 'sub_lawyer')->pluck('name')->toArray())) {
            $event_courts_count = $event_courts_count->where('lawyer_id', auth()->user()->senior_lawyer_id);
        }

        $event_courts_count = $event_courts_count->groupBy('next_date')->get();

        foreach ($event_courts_count as $k => $cases) {
            $calendar_events[$count_event]['title'] = 'DC ' . $cases->district_cases . ' & HC ' . $cases->high_court_cases;
            $calendar_events[$count_event]['district_courts_case_count'] = $cases->district_cases;
            $calendar_events[$count_event]['high_courts_case_count'] = $cases->high_courtt_cases;

            $calendar_events[$count_event]['start'] = Carbon::parse($cases->start)->format('Y-m-d');

            $calendar_events[$count_event]['end'] = \Carbon\Carbon::parse($cases->start)
                ->addDays(1)
                ->format('Y-m-d');

            $count_event++;
        }


        return response()->json([
            'success' => true,
            'message' => 'Events fetched.',
            'events' => $calendar_events
        ], 200);
    }
}
