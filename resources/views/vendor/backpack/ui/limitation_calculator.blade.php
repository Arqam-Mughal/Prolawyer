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
        <div class="row"><center><h1>Calculation Of Days</h1></center></div>

        <div class="row form-group">
            <div class="primary_input col-md-4">
                <label style="margin-top:3px;float:right">Start Date:</label>
            </div>
            <div class="primary_input col-md-8">
                <input type="date" name="start_date" class="primary-input form-control datetime primary_input_field"> 
            </div>
        </div>

        <div class="row form-group">
            <div class="primary_input col-md-4">
                <label style="margin-top:3px;float:right;">End Date:</label>
            </div>
            <div class="primary_input col-md-8">
                <input type="date" name="end_date" class="primary-input form-control datetime primary_input_field"> 
            </div>
        </div>
        <div class="row form-group">
            <div class="primary_input col-md-4">
                <label style="margin-top:3px;float:right;">Certified Copy Applied On:</label>
            </div>
            <div class="primary_input col-md-8">
                <input type="date" name="applied_on" class="primary-input form-control datetime primary_input_field"> 
            </div>
        </div>
        <div class="row form-group">
            <div class="primary_input col-md-4">
                <label style="margin-top:3px;float:right;">Certified Copy Ready On:</label>
            </div>
            <div class="primary_input col-md-8">
                <input type="date" name="ready_on" class="primary-input form-control datetime primary_input_field"> 
            </div>
        </div>

        <div class="row form-group">
                
            <div class="primary_input col-md-12">
                <center><button class="btn btn-primary small fix-gr-bg submit calculate center" type="button" style="margin-left:5px;"><i class="ti-check"></i>{{ __('Calculate Duration') }}
                </button></center>
            </div>
        </div> 
        <div class="row form-group">
            <div class="primary_input col-md-4">
                <label style="margin-top:3px;float:right;">Days:</label>
            </div>
            <div class="primary_input col-md-8">
                <input type="text" id="days" name="no_of_days" class="primary-input form-control primary_input_field" placeholder="0" readonly> 
            </div>
        </div>
        <div class="row form-group">
            <div class="primary_input col-md-4">
                <label style="margin-top:3px;float:right;">Month and Days:</label>
            </div>
            <div class="primary_input col-md-8">
                <input type="text" id="months" name="interest_earned" class="primary-input form-control primary_input_field" placeholder="0" readonly> 
            </div>
        </div>
        <div class="row form-group">
            <div class="primary_input col-md-4">
                <label style="margin-top:3px;float:right;">Year, Months and Days:</label>
            </div>
            <div class="primary_input col-md-8">
                <input type="text" id="years" name="total_value" class="primary-input form-control primary_input_field" placeholder="0" readonly> 
            </div>
        </div>
        <div class="row form-group">
            <div class="primary_input col-md-4">
                <label style="margin-top:3px;float:right;">Number of Days Deducted:</label>
            </div>
            <div class="primary_input col-md-8">
                <input type="text" id="days_deducted" name="total_value_deducted" class="primary-input form-control primary_input_field" placeholder="0" readonly> 
            </div>
        </div>
    </div>
    
    <div class="my-5 row d-flex align-items-center justify-space-between">
        <div class="row"><center><h1>Limitation</h1></center></div>

        <div class="row form-group">
            <div class="primary_input col-md-4">
                <label style="margin-top:3px;float:right">Date:</label>
            </div>
            <div class="primary_input col-md-8">
                <input type="date" name="limit_date" class="primary-input form-control datetime primary_input_field"> 
            </div>
        </div>
         
        <div class="row form-group">
            <div class="primary_input col-md-4">
                <label style="margin-top:3px;float:right;" id="limit_type_label">Limitation By:</label>
            </div>
            <div class="primary_input col-md-8">
                <select name="limit_type" class="form-control primary_select">
                    <option value="1">Number of Days</option>
                    <option value="2">Number of Weeks</option>
                    <option value="3">Number of Months</option>
                    <option value="4">Number of Years</option>
                </select>
            </div>
        </div>

    
        <div class="row form-group">
            <div class="primary_input col-md-4">
                <label style="margin-top:3px;float:right;" id="label_no_of">Number Of Days:</label>
            </div>
            <div class="primary_input col-md-8">
                <input type="text" name="no_of" class="primary-input form-control datetime primary_input_field" placeholder="Number of Days"> 
            </div>
        </div>

        <div class="row form-group">
            <div class="primary_input col-md-4">
                <label style="margin-top:3px;float:right;">Last Date Of Limitation:</label>
            </div>
            <div class="primary_input col-md-8">
                <input type="date" name="last_date_limitation" class="primary-input form-control datetime primary_input_field"> 
            </div>
        </div>

    </div>
@endsection

@section('after_scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

    <script>
        $('.calculate').on('click', function() {
            var start_date = $('input[name="start_date"]').val();
            var end_date = $('input[name="end_date"]').val();
            var applied_on = $('input[name="applied_on"]').val();
            var ready_on = $('input[name="ready_on"]').val();
            if(!start_date || !end_date){
                alert('select start date and end date')
            } else {
                var startDay = new moment(start_date, 'YYYY-MM-DD');  
                var endDay = new moment(end_date, 'YYYY-MM-DD');
                var appliedOn = new moment(applied_on, 'YYYY-MM-DD');
                var readyOn = new moment(ready_on, 'YYYY-MM-DD');
                var days_deducted = readyOn.diff(appliedOn, 'days', true);
                var days = endDay.diff(startDay, 'days', true);
                days = days - days_deducted;
                var months = endDay.diff(startDay, 'months', true);
                var years = endDay.diff(startDay, 'years', true);
                var duration = moment.duration(days, 'days');
                var day_text = days == 1 ? ' Day' : ' Days';
                var month_text = duration.months() == 1 ? ' Month, ' : ' Months, ';
                var month_text2 = parseInt(months) == 1 ? ' Month, ' : ' Months, ';

                var year_text = duration.months() == 1 ? ' Year, ' : ' Years, ';
                $('#days').val(days+day_text);
                $('#months').val(parseInt(months)+month_text+duration.days()+day_text);
                $('#years').val(duration.years()+year_text+duration.months()+month_text2+duration.days()+day_text);
                $('#days_deducted').val(days_deducted+" Days");
            }
        });

        $('select[name="limit_type"]').change(function(){
            $('#label_no_of').text($('select[name="limit_type"] option:selected').text());
            $('input[name="no_of"]').attr('placeholder',$('select[name="limit_type"] option:selected').text());
        });

        $('input[name="no_of"]').on('keyup', function() {
            var limit_date = $('input[name="limit_date"]').val();
            if(!limit_date){
                alert('select date');
            } else {
                var no_of = $('input[name="no_of"]').val();
                var last_date_limitation = '';
                var limit_type = $('select[name="limit_type"]').val();
                limit_date = new moment(limit_date, 'YYYY-MM-DD');
                if(limit_type == 1) {
                    last_date_limitation = moment(limit_date).add(no_of, 'days');
                } else if(limit_type == 2) {
                    last_date_limitation = moment(limit_date).add(no_of, 'weeks');
                } else if(limit_type == 3) {
                    last_date_limitation = moment(limit_date).add(no_of, 'months');
                } else {
                    last_date_limitation = moment(limit_date).add(no_of, 'years');
                }
                $('input[name="last_date_limitation"]').val(last_date_limitation.format("YYYY-MM-DD"));
            }
        });
    </script>
   
    @basset('https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js')
    <script>
        
    </script>
@endsection
