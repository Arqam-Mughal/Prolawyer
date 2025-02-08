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

@endphp

@section('content')
    <div class="my-5 row d-flex align-items-center justify-space-between">
        <div class="row"><center><h1>Interest Calculator</h1></center></div>
            <div class="row form-group">
                <div class="primary_input col-md-4">
                    <label style="margin-top:3px;float:right;">Select Interest Type:</label>
                </div>
                <div class="primary_input col-md-8">
                    <select class="primary_input_field form-control" name="interest_type">
                        <option value="1">Simple Interest</option>
                        <option value="2">Compound Interest</option>
                    </select>
                </div>
            </div>

            <div class="row form-group">
                <div class="primary_input col-md-4">
                    <label style="margin-top:3px;float:right;">Principal Amount:</label>
                </div>
                <div class="primary_input col-md-8">
                    <input type="text" name="principal_amount" class="form-control primary_input_field" placeholder="0"> 
                </div>
            </div>
            <div class="row form-group">
                <div class="primary_input col-md-4">
                    <label for="ABC" style="margin-top:3px;float:right;">Annual Rate %:</label>
                </div>
                <div class="primary_input col-md-8">
                    <input type="text" name="annual_rate" class="form-control primary_input_field" placeholder="0%"> 
                </div>
            </div>
            <div class="row form-group">
                <div class="primary_input col-md-4">
                    <label style="margin-top:3px;float:right;">From:</label>
                </div>
                <div class="primary_input col-md-8">
                    <input type="date" name="start_date" class="primary-input form-control datetime primary_input_field" placeholder="From"> 
                </div>
            </div>
            <div class="row form-group">
                <div class="primary_input col-md-4">
                    <label style="margin-top:3px;float:right;">To:</label>
                </div>
                <div class="primary_input col-md-8">
                    <input type="date" name="end_date" class="primary-input form-control datetime primary_input_field" placeholder="To"> 
                </div>
            </div>
            <div class="row form-group">
                <div class="primary_input col-md-6">
                    <button class="btn btn-primary small fix-gr-bg submit reset" type="reset" style="float:right;><i class="ti-check"></i>{{ __('Reset') }}
                    </button>
                </div>
                <div class="primary_input col-md-6 d-flex">
                    <button class="btn btn-primary small fix-gr-bg submit calculate" type="button" style="margin-left:5px;"><i class="ti-check"></i>{{ __('Calculate') }}
                    </button>
                </div>
            </div> 
            <div class="row form-group">
                <div class="primary_input col-md-4">
                    <label style="margin-top:3px;float:right;">Number Of Days:</label>
                </div>
                <div class="primary_input col-md-8">
                    <input type="text" name="no_of_days" class="primary-input form-control primary_input_field" placeholder="0" readonly> 
                </div>
            </div>
            <div class="row form-group">
                <div class="primary_input col-md-4">
                    <label style="margin-top:3px;float:right;">Interest Earned:</label>
                </div>
                <div class="primary_input col-md-8">
                    <input type="text" name="interest_earned" class="primary-input form-control primary_input_field" placeholder="0" readonly> 
                </div>
            </div>
            <div class="row form-group">
                <div class="primary_input col-md-4">
                    <label style="margin-top:3px;float:right;">Total Value:</label>
                </div>
                <div class="primary_input col-md-8">
                    <input type="text" name="total_value" class="primary-input form-control primary_input_field" placeholder="0" readonly> 
                </div>
            </div>
    </div>
@endsection

@section('after_scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

    <script>
        $('.datetime').on('change', function(e){
            var start_date = $('input[name="start_date"]').val();
            var end_date   = $('input[name="end_date"]').val();
            if(start_date && end_date){
                var startDay  = new moment(start_date, 'YYYY-MM-DD');  
                var endDay    = new moment(end_date, 'YYYY-MM-DD');
                var days      = endDay.diff(startDay, 'days', true);
                
                $('input[name ="no_of_days"]').val(days);
            }
        });
        $('.calculate').on('click', function() {
            var interest_type    = $('select[name="interest_type"]').val();
            var principal_amount = $('input[name="principal_amount"]').val();
            var annual_rate      = $('input[name="annual_rate"]').val();
            var no_of_days      = $('input[name="no_of_days"]').val();
            var interest_earned  = $('input[name="interest_earned"]');
            var total_value      = $('input[name="total_value"]');
            if(!interest_type){
                alert('select interest type');
            } else if(!principal_amount){
                alert('enter principal amount');
            } else  if(!annual_rate){
                alert('enter annual rate');
            } else  if(!no_of_days){
                alert('select start date and end date');
            } else {
                var time = no_of_days/365;
                if(interest_type == 1) {
                    var simple_interest = principal_amount*annual_rate*time/100
                    interest_earned.val(Math.round(simple_interest*1000)/1000);
                    total_value.val( parseInt(principal_amount) + parseInt(interest_earned.val()) );
                } else {
                    var compound_interest = principal_amount * (Math.pow((1 + annual_rate / 100), time));
                    interest_earned.val(Math.round(compound_interest*1000)/1000);
                    total_value.val(parseInt(principal_amount) + parseInt(interest_earned.val()) );
                }
            }
        });

        $('.reset').on('click', function() {
            $('select[name="interest_type"]').val(1);
            $('input[name="principal_amount"]').val('');
            $('input[name="annual_rate"]').val('');
            $('input[name="no_of_days"]').val('');
            $('input[name="interest_earned"]').val('');
            $('input[name="total_value"]').val('');
            $('input[name="start_date"]').val('');
            $('input[name="end_date"]').val('');
        });
    </script>
    
    @basset('https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js')
    <script>
        
    </script>
@endsection
