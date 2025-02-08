<div class="row fetch-case fetch-cnr-number mb-2 align-items-center">
    <center><h1 style="margin-top:5px;">Fetch By CNR<h1></center>
    <br />
    <input type="text" name="cnr_no" class="form-control col-sm-6 fetch-by-cnr-number" style="margin-top:5px;" placeholder="CNR Number">
    <div class="col-sm-3"></div>
    <a href="" class="btn btn-primary fetch-by-cnr col-sm-3" data-style="zoom-in" style="margin-top:10px;">Fetch</a>
</div>

@push('after_scripts')
    @basset('https://unpkg.com/urijs@1.19.11/src/URI.min.js')

    <script>

    jQuery(document).ready(function($) {
        $('.fetch-by-cnr').on('click', function(e){
            e.preventDefault();
            $(this).text("Fetching Please Wait...");
            var cnr_no = $('.fetch-by-cnr-number[type="text"]').val();

            $.ajax({
                url: '{{ route('cases.fetch_by_cnr') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    cnrid: cnr_no
                },
            }).done( function( response ) {
                var data = JSON.parse(response.response);

                $('.fetch-case.fetch-cnr-number').hide();
                $('.create-case[data-case-type="custom-case"]').show();
                if ( data.hasOwnProperty('cino') ) {
                    $('.form-group[bp-field-name="cnr_no"]').find('input[name="cnr_no"]').val( data.cino );
                    $('.form-group[bp-field-name="cnr_no"]').find('input[name="cnr_no"]').attr("readonly","readonly");
                }
                if ( data.hasOwnProperty('listingDate') ) {
                   $('.form-group[bp-field-name="next_date"]').find('input[name="next_date"]').val( data.listingDate );
                   $('.form-group[bp-field-name="next_date"]').find('input[name="next_date"]').attr("readonly","readonly");
                }
                if ( data.hasOwnProperty('previous_date') ) {
                   $('.form-group[bp-field-name="previous_date"]').find('input[name="previous_date"]').val( data.previous_date );
                   $('.form-group[bp-field-name="previous_date"]').find('input[name="previous_date"]').attr("readonly","readonly");
                }
                if ( data.hasOwnProperty('cn') ) {
                   $('.form-group[bp-field-name="case_no"]').find('input[name="case_no"]').val( data.cn );
                   $('.form-group[bp-field-name="case_no"]').find('input[name="case_no"]').attr("readonly","readonly");
                }
                if ( data.hasOwnProperty('cy') ) {
                   $('.form-group[bp-field-name="case_year"]').find('input[name="case_year"]').val( data.cy );
                   $('.form-group[bp-field-name="case_year"]').find('input[name="case_year"]').attr("readonly","readonly");
                }
                if ( data.hasOwnProperty('caseTypeStr') ) {
                   $('.form-group[bp-field-name="case_category"]').find('input[name="case_category"]').val( data.caseTypeStr );
                   $('.form-group[bp-field-name="case_category"]').find('input[name="case_category"]').attr("readonly","readonly");
                }
                if ( data.hasOwnProperty('listingJudges') ) {
                   $('.form-group[bp-field-name="court_bench"]').find('input[name="court_bench"]').val( data.listingJudges );
                   $('.form-group[bp-field-name="court_bench"]').find('input[name="court_bench"]').attr("readonly","readonly");
                }
                if ( data.hasOwnProperty('listingSno') ) {
                   $('.form-group[bp-field-name="sr_no_in_court"]').find('input[name="sr_no_in_court"]').val( data.listingSno );
                   $('.form-group[bp-field-name="sr_no_in_court"]').find('input[name="sr_no_in_court"]').attr("readonly","readonly");
                }
                if ( data.hasOwnProperty('listingStage') ) {
                   $('.form-group[bp-field-name="case_stage"]').find('input[name="case_stage"]').val( data.listingStage );
                   $('.form-group[bp-field-name="case_stage"]').find('input[name="case_stage"]').attr("readonly","readonly");
                }
                if ( data.hasOwnProperty('state_val') ) {
                    var state = parseInt( data.state_val );
                   $('.form-group[bp-field-name="state_id"]').find('input[name="state_id"]').val( state ).trigger('change');
                   $('.form-group[bp-field-name="state_id"]').find('input[name="state_id"]').attr("readonly","readonly");
                }
                if ( data.hasOwnProperty('petitioners') && data.petitioners.length > 0 ) {
                    if ( data.petitioners.hasOwnProperty(0) ) {
                        $('.form-group[bp-field-name="petitioners1"]').find('input[name="petitioners1"]').val( data.petitioners[0] );
                        $('.form-group[bp-field-name="petitioners1"]').find('input[name="petitioners1"]').attr("readonly","readonly");
                    }
                    if ( data.petitioners.hasOwnProperty(1) ) {
                        $('.form-group[bp-field-name="add_new_petitioner"]').hide();
                        $('.form-group[bp-field-name="petitioners2"]').show();
                        $('.form-group[bp-field-name="petitioners2"]').find('input[name="petitioners2"]').val( data.petitioners[1] );
                        $('.form-group[bp-field-name="petitioners2"]').find('input[name="petitioners2"]').attr("readonly","readonly");
                    }
                }
                if ( data.hasOwnProperty('respondents') && data.respondents.length > 0 ) {
                    if ( data.respondents.hasOwnProperty(0) ) {
                        $('.form-group[bp-field-name="respondents1"]').find('input[name="respondents1"]').val( data.respondents[0] );
                        $('.form-group[bp-field-name="respondents1"]').find('input[name="respondents1"]').attr("readonly","readonly");
                    }
                    if ( data.respondents.hasOwnProperty(1) ) {
                        $('.form-group[bp-field-name="add_new_respondent"]').hide();
                        $('.form-group[bp-field-name="respondents2"]').show();
                        $('.form-group[bp-field-name="respondents2"]').find('input[name="respondents2"]').val( data.respondents[1] );
                        $('.form-group[bp-field-name="respondents2"]').find('input[name="respondents2"]').attr("readonly","readonly");
                    }
                }
                if ( data.hasOwnProperty('radvocates') && data.radvocates.length > 0 ) {
                    if ( data.radvocates.hasOwnProperty(0) ) {
                        $('.form-group[bp-field-name="respondent_advocates1"]').find('input[name="respondent_advocates1"]').val( data.radvocates[0] );
                        $('.form-group[bp-field-name="respondent_advocates1"]').find('input[name="respondent_advocates1"]').attr("readonly","readonly");
                    }
                    if ( data.radvocates.hasOwnProperty(1) ) {
                        $('.form-group[bp-field-name="add_new_respondent"]').hide();
                        $('.form-group[bp-field-name="respondent_advocates2"]').show();
                        $('.form-group[bp-field-name="respondent_advocates2"]').find('input[name="respondent_advocates2"]').val( data.radvocates[1] );
                        $('.form-group[bp-field-name="respondent_advocates2"]').find('input[name="respondent_advocates2"]').attr("readonly","readonly");
                    }
                }
                if ( data.hasOwnProperty('padvocates') && data.padvocates.length > 0 ) {
                    if ( data.padvocates.hasOwnProperty(0) ) {
                        $('.form-group[bp-field-name="petitioner_advocates1"]').find('input[name="petitioner_advocates1"]').val( data.padvocates[0] );
                        $('.form-group[bp-field-name="petitioner_advocates1"]').find('input[name="petitioner_advocates1"]').attr("readonly","readonly");
                    }
                    if ( data.padvocates.hasOwnProperty(1) ) {
                        $('.form-group[bp-field-name="add_new_petitioner"]').hide();
                        $('.form-group[bp-field-name="petitioner_advocates2"]').show();
                        $('.form-group[bp-field-name="petitioner_advocates2"]').find('input[name="petitioner_advocates2"]').val( data.padvocates[1] );
                        $('.form-group[bp-field-name="petitioner_advocates2"]').find('input[name="petitioner_advocates2"]').attr("readonly","readonly");
                    }
                }
            } );
        });
    });

    </script>
@endpush
