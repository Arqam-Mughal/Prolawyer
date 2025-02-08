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
        <div class="row"><center><h1>Court Fee Calculator</h1></center></div>
        
        <div class="row form-group">
            <div class="primary_input col-md-4">
                <label style="float:right;">Select court:</label>
            </div>
            <div class="primary_input col-md-8">
                <select name="ratelist" class="primary_select form-control">
                    <option value selected>Select the total claim value</option>
                    <option value="200.00">0000 - 1000</option>
                    <option value="212.00">1000 - 1100</option>
                    <option value="224.00">1100 - 1200</option>
                    <option value="236.00">1200 - 1300</option>
                    <option value="248.00">1300 - 1400</option>
                    <option value="260.00">1400 - 1500</option>
                    <option value="272.00">1500 - 1600</option>
                    <option value="280.00">1600 - 1700</option>
                    <option value="296.00">1700 - 1800</option>
                    <option value="308.00">1800 - 1900</option>
                    <option value="320.00">1900 - 2000</option>
                    <option value="332.00">2000 - 2100</option>
                    <option value="344.00">2100 - 2200</option>
                    <option value="356.00">2200 - 2300</option>
                    <option value="368.00">2300 - 2400</option>
                    <option value="380.00">2400 - 2500</option>
                    <option value="392.00">2500 - 2600</option>
                    <option value="404.00">2600 - 2700</option>
                    <option value="416.00">2700 - 2800</option>
                    <option value="428.00">2800 - 2900</option>
                    <option value="440.00">2900 - 3000</option>
                    <option value="452.00">3000 - 3100</option>
                    <option value="464.00">3100 - 3200</option>
                    <option value="476.00">3200 - 3300</option>
                    <option value="488.00">3300 - 3400</option>
                    <option value="500.00">3400 - 3500</option>
                    <option value="512.00">3500 - 3600</option>
                    <option value="524.00">3600 - 3700</option>
                    <option value="536.00">3700 - 3800</option>
                    <option value="548.00">3800 - 3900</option>
                    <option value="566.00">3900 - 4000</option>
                    <option value="572.00">4000 - 4100</option>
                    <option value="584.00">4100 - 4200</option>
                    <option value="596.00">4200 - 4300</option>
                    <option value="608.00">4300 - 4400</option>
                    <option value="620.00">4400 - 4500</option>
                    <option value="632.00">4500 - 4600</option>
                    <option value="644.00">4600 - 4700</option>
                    <option value="656.00">4700 - 4800</option>
                    <option value="668.00">4800 - 4900</option>
                    <option value="680.00">4900 - 5000</option>
                    <option value="695.00">5000 - 5100</option>
                    <option value="710.00">5100 - 5200</option>
                    <option value="725.00">5200 - 5300</option>
                    <option value="740.00">5300 - 5400</option>
                    <option value="755.00">5400 - 5500</option>
                    <option value="770.00">5500 - 5600</option>
                    <option value="785.00">5600 - 5700</option>
                    <option value="800.00">5700 - 5800</option>
                    <option value="815.00">5800 - 5900</option>
                    <option value="830.00">5900 - 6000</option>
                    <option value="845.00">6000 - 6100</option>
                    <option value="860.00">6100 - 6200</option>
                    <option value="875.00">6200 - 6300</option>
                    <option value="890.00">6300 - 6400</option>
                    <option value="905.00">6400 - 6500</option>
                    <option value="920.00">6500 - 6600</option>
                    <option value="935.00">6600 - 6700</option>
                    <option value="950.00">6700 - 6800</option>
                    <option value="965.00">6800 - 6900</option>
                    <option value="980.00">6900 - 7000</option>
                    <option value="995.00">7000 - 7100</option>
                    <option value="1010.00">7100 - 7200</option>
                    <option value="1025.00">7200 - 7300</option>
                    <option value="1040.00">7300 - 7400</option>
                    <option value="1055.00">7400 - 7500</option>
                    <option value="1070.00">7500 - 7600</option>
                    <option value="1085.00">7600 - 7700</option>
                    <option value="1100.00">7700 - 7800</option>
                    <option value="1115.00">7800 - 7900</option>
                    <option value="1130.00">7900 - 8000</option>
                    <option value="1145.00">8000 - 8100</option>
                    <option value="1160.00">8100 - 8200</option>
                    <option value="1175.00">8200 - 8300</option>
                    <option value="1190.00">8300 - 8400</option>
                    <option value="1205.00">8400 - 8500</option>
                    <option value="1220.00">8500 - 8600</option>
                    <option value="1235.00">8600 - 8700</option>
                    <option value="1250.00">8700 - 8800</option>
                    <option value="1265.00">8800 - 8900</option>
                    <option value="1280.00">8900 - 9000</option>
                    <option value="1295.00">9000 - 9100</option>
                    <option value="1310.00">9100 - 9200</option>
                    <option value="1325.00">9200 - 9300</option>
                    <option value="1340.00">9300 - 9400</option>
                    <option value="1355.00">9400 - 9500</option>
                    <option value="1370.00">9500 - 9600</option>
                    <option value="1385.00">9600 - 9700</option>
                    <option value="1400.00">9700 - 9800</option>
                    <option value="1415.00">9800 - 9900</option>
                    <option value="1430.00">9900 - 10000</option>
                    <option value="1505.00">10000 - 10500</option>
                    <option value="1580.00">10500 - 11000</option>
                    <option value="1655.00">11000 - 11500</option>
                    <option value="1730.00">11500 - 12000</option>
                    <option value="1805.00">12000 - 12500</option>
                    <option value="1880.00">12500 - 13000</option>
                    <option value="1955.00">13000 - 13500</option>
                    <option value="2030.00">13500 - 14000</option>
                    <option value="2105.00">14000 - 14500</option>
                    <option value="2180.00">14500 - 15000</option>
                    <option value="2255.00">15000 - 15500</option>
                    <option value="2330.00">15500 - 16000</option>
                    <option value="2405.00">16000 - 16500</option>
                    <option value="2480.00">16500 - 17000</option>
                    <option value="2555.00">17000 - 17500</option>
                    <option value="2630.00">17500 - 18000</option>
                    <option value="2705.00">18000 - 18500</option>
                    <option value="2780.00">18500 - 19000</option>
                    <option value="2855.00">19000 - 19500</option>
                    <option value="2930.00">19500 - 20000</option>
                    <option value="3030.00">20000 - 21000</option>
                    <option value="3130.00">21000 - 22000</option>
                    <option value="3230.00">22000 - 23000</option>
                    <option value="3330.00">23000 - 24000</option>
                    <option value="3430.00">24000 - 25000</option>
                    <option value="3530.00">25000 - 26000</option>
                    <option value="3630.00">26000 - 27000</option>
                    <option value="3730.00">27000 - 28000</option>
                    <option value="3830.00">28000 - 29000</option>
                    <option value="3930.00">29000 - 30000</option>
                    <option value="4030.00">30000 - 32000</option>
                    <option value="4130.00">32000 - 34000</option>
                    <option value="4230.00">34000 - 36000</option>
                    <option value="4330.00">36000 - 38000</option>
                    <option value="4430.00">38000 - 40000</option>
                    <option value="4530.00">40000 - 42000</option>
                    <option value="4630.00">42000 - 44000</option>
                    <option value="4730.00">44000 - 46000</option>
                    <option value="4830.00">46000 - 48000</option>
                    <option value="4930.00">48000 - 50000</option>
                    <option value="5080.00">50000 - 55000</option>
                    <option value="5230.00">55000 - 60000</option>
                    <option value="5380.00">60000 - 65000</option>
                    <option value="5530.00">65000 - 70000</option>
                    <option value="5680.00">70000 - 75000</option>
                    <option value="5830.00">75000 - 80000</option>
                    <option value="5980.00">80000 - 85000</option>
                    <option value="6130.00">85000 - 90000</option>
                    <option value="6280.00">90000 - 95000</option>
                    <option value="6430.00">95000 - 100000</option>
                    <option value="6430.00">100000</option>
                    <option value="8430.00">200000</option>
                    <option value="10430.00">300000</option>
                    <option value="12430.00">400000</option>
                    <option value="14430.00">500000</option>
                    <option value="16430.00">600000</option>
                    <option value="18430.00">700000</option>
                    <option value="20430.00">800000</option>
                    <option value="22430.00">900000</option>
                    <option value="24430.00">1000000</option>
                    <option value="26430.00">1100000</option>
                    <option value="27630.00">1200000</option>
                    <option value="28830.00">1300000</option>
                    <option value="30030.00">1400000</option>
                    <option value="31230.00">1500000</option>
                    <option value="300000.00">Maximum Fee</option>
                </select>
            </div>
        </div>
        <div class="row form-group">
            <div class="primary_input col-md-4">
                <label style="float:right;">Court Fee:</label>
            </div>
            <div class="primary_input col-md-8">
                <input type="text" name="ratefield" class="form-control primary_input_field" disabled=""> 
            </div>
        </div>
    </div>

    <section class="mt-2 admin-visitor-area up_st_admin_visitor">
        <div class="container-fluid p-0">
            <!-- form start =-->
                <div class="tab-content" id="pills-tabContent">
                        <!-- tab 1 =-->
                        <div class="tab-pane fade show active" id="pills-district-court" role="tabpanel" aria-labelledby="pills-contact-tab">
                            <div class="col-12" id="custom_case" >
                                <div class="white_box_50px box_shadow_white">

                                <!-- Form -->
                                <div class="row form-group">
                                    <div class="primary_input col-md-6">
                                            <h4>Consumer Matter :</h4>
                                    </div>
                                </div>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Sr. No.</th>
                                            <th>Value of goods or services  paid as consideration</th>
                                            <th>Amount of fees payable</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>Upto Five Lakhs rupees (0 -5L)</td>
                                            <td>Nil</td>
                                        </tr>
                                        <tr>
                                            <td>2</td>
                                            <td>Above Five Lakhs and upto Ten Lakhs(5L-10L) </td>
                                            <td>Above Ten Lakhs and upto Twenty Lakhs(10L-20L)</td>
                                        </tr>
                                        <tr>
                                            <td>3</td>
                                            <td>Above Ten Lakhs and upto Twenty Lakhs(10L-20L)</td>
                                            <td>Rs. 1000</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!--  -->
                        <!--  -->
                    </div>
                        <!-- Table start -->
                </div>
                    <!-- form end =-->
               

        </div>
    </section>

    <section class="mt-2 admin-visitor-area up_st_admin_visitor">
        <div class="container-fluid p-0">
            <!-- form start =-->
                <div class="tab-content" id="pills-tabContent">
                        <!-- tab 1 =-->
                        <div class="tab-pane fade show active" id="pills-district-court" role="tabpanel" aria-labelledby="pills-contact-tab">
                            <div class="col-12" id="custom_case" >
                                <div class="white_box_50px box_shadow_white">

                                <!-- Form -->
                                <div class="row form-group">
                                    <div class="primary_input col-md-6">
                                            <h4>Debt Recovery Tribunal :</h4>
                                    </div>
                                </div>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Sr. No.</th>
                                            <th>Debt Recovery Tribunal :</th>
                                            <th>Amount of fees payable</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td rowspan="3">1</td>
                                            <td>Application for recovering of debt due </td>
                                            <td rowspan="3">Rs. 12,000 Rs. 12,000 plus Rs. 1,000 for every one lakh, subject to a maximum of Rs. 1,50,000. </td>
                                        </tr>
                                        <tr>
                                            <td>(a) Where amount of debt due is Rs. 10 lakh </td>
                                        </tr>
                                        <tr>
                                            <td>(b) Where amount of debt due is above Rs. 10 lakh</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!--  -->
                        <!--  -->
                    </div>
                        <!-- Table start -->
                </div>
                    <!-- form end =-->
               

        </div>
    </section>
@endsection

@section('after_scripts')
    <script>
        $('select[name="ratelist"]').change(function(){
            var ratelist = $('select[name="ratelist"]').val()
            if(ratelist){
                $('input[name="ratefield"]').val(ratelist);
                // $('input[name="amount"]').val($('select[name="ratelist"] option:selected').text());
            } else {
                $('input[name="ratefield"]').val('');
                // $('input[name="amount"]').val('');
            }
        });
    </script>
   
    @basset('https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js')
    <script>
        
    </script>
@endsection
