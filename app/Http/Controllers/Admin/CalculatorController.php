<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CalculatorController extends Controller
{
    public function interest_calculator() {
        if (!backpack_user()->hasRole('Super admin') && !backpack_user()->can('Interest'))
        abort('403', 'Unauthorized Access!');

        return view(backpack_view('interest_calculator'));
    }

    public function court_fee_calculator() {
        if (!backpack_user()->hasRole('Super admin') && !backpack_user()->can('Court Fee'))
        abort('403', 'Unauthorized Access!');

        return view(backpack_view('court_fee_calculator'));
    }

    public function limitation_calculator() {
        if (!backpack_user()->hasRole('Super admin') && !backpack_user()->can('Limitation'))
        abort('403', 'Unauthorized Access!');

        return view(backpack_view('limitation_calculator'));
    }

}
