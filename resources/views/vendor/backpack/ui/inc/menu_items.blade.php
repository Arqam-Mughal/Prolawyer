{{-- This file is used for menu items by any Backpack v6 theme --}}

<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i>
        {{ trans('backpack::base.dashboard') }}</a></li>

        @role('Super admin')
<x-backpack::menu-dropdown title="Add-ons" icon="la la-puzzle-piece">
    {{-- <x-backpack::menu-dropdown-header title="Authentication" /> --}}

        <x-backpack::menu-dropdown-item title="Manage users" icon="la la-user" :link="backpack_url('user')" />
        <x-backpack::menu-dropdown-item title="Manage plans" icon="la la-group" :link="backpack_url('role')" />
        <x-backpack::menu-dropdown-item title="Transaction" icon="la la-exchange" :link="backpack_url('transaction')" />
    </x-backpack::menu-dropdown>
    @endrole

@hasanyrole(\App\Models\Role::where('type', 'regular_user')->pluck('name')->toArray())
@can('Add sub lawyer')
<li class="nav-item">
    <a class="nav-link" href="{{ backpack_url('user') }}">
        <i class="la la-user nav-icon"></i>
        {{ trans('Manage sub lawyers') }}
    </a>
</li>
@endcan
@endhasanyrole

<x-backpack::menu-dropdown title="Cases" icon="la la-home">
    @can('Add New Case')
    <x-backpack::menu-dropdown-item icon="la la-home" title="Add New Case" :link="backpack_url('case-model-all-cases/create')" />
    @endcan

    @can('All Cases')
        <x-backpack::menu-dropdown-item icon="la la-home" title="All Cases" :link="backpack_url('case-model-all-cases')" />
    @endcan

    @can('Active Cases')
        <x-backpack::menu-dropdown-item icon="la la-home" title="Active Cases" :link="backpack_url('case-model-active')" />
    @endcan

    @can('Today Cases')
        <x-backpack::menu-dropdown-item icon="la la-home" title="Today's Cases" :link="backpack_url('case-model-today')" />
    @endcan

    @can('Tomorrow Cases')
        <x-backpack::menu-dropdown-item icon="la la-home" title="Tomorrow's Cases" :link="backpack_url('case-model-tomorrow')" />
    @endcan

    @can('Daily Board')
        <x-backpack::menu-dropdown-item icon="la la-home" title="Daily Board Cases" :link="backpack_url('case-model-dailyboard')" />
    @endcan

    @can('Date Awaited Cases')
        <x-backpack::menu-dropdown-item icon="la la-home" title="Date Awaited Cases" :link="backpack_url('case-model-date-awaited')" />
    @endcan

    @can('Daily Board')
        <x-backpack::menu-dropdown-item icon="la la-home" title="Display Board" :link="backpack_url('daily-board')" />
    @endcan


</x-backpack::menu-dropdown>

@can('Advance Search')
<x-backpack::menu-item icon="la la-search" title="Advance Search" :link="backpack_url('case-advance-search')" />
@endcan

@can('Bare Acts and Formats')
<x-backpack::menu-item title="Bare Acts" :link="backpack_url('bare-acts')" />
@endcan

@can('Archive')
<x-backpack::menu-item title="Archive" :link="backpack_url('archive')" />
@endcan

@can('Connected Matters')
<x-backpack::menu-item title="Connected Matters" :link="backpack_url('connected-matters')" />
@endcan

@can('Court Fee Calculator')
<x-backpack::menu-dropdown title="Calculator">

    @can('Court Fee')
    <x-backpack::menu-dropdown-item title="Court Fee" :link="backpack_url('calculator-court-fee')" />
    @endcan

    @can('Interest')
    <x-backpack::menu-dropdown-item title="Interest" :link="backpack_url('calculator-interest')" />
    @endcan

    @can('Limitation')
    <x-backpack::menu-dropdown-item title="Limitation" :link="backpack_url('calculator-limitation')" />
    @endcan

</x-backpack::menu-dropdown>
@endcan

@can('My Clients')
<x-backpack::menu-dropdown title="My clients">
    <x-backpack::menu-dropdown-item title="Individual" icon="la la-user" :link="backpack_url('client')" />
    <x-backpack::menu-dropdown-item title="Organization" icon="la la-users" :link="backpack_url('organization')" />
    <x-backpack::menu-dropdown-item title="Reference" icon="la la-arrow-down" :link="backpack_url('ref-tag')" />
</x-backpack::menu-dropdown>
@endcan

{{-- @dd(backpack_user()->roles->pluck('name')->toArray()) --}}

@if ((backpack_user()->hasRole('Super admin')) || backpack_user()->can('Worklist'))
    <x-backpack::menu-dropdown title="Manage Worklist" icon="la la-check">
        <x-backpack::menu-dropdown-item title="Worklists" icon="la la-list" :link="backpack_url('worklist')" />
        <x-backpack::menu-dropdown-item title="Worklist categories" icon="la la-th" :link="backpack_url('worklist-category')" />
    </x-backpack::menu-dropdown>
@endcan


