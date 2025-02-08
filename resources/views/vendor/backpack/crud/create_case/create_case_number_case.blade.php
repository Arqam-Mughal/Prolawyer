
<div class="row fetch-case fetch-case-number mb-2 align-items-center">
    
    <center><h1 style="margin-top:5px;">Fetch By Case Number<h1></center>
    <br />
    <input type="text" name="case_no" class="form-control col-sm-6 fetch-by-case-no-number" style="margin-top:5;">
    <div class="col-sm-3"></div>
    <a href="" class="btn btn-primary fetch-by-case-no col-sm-3" data-style="zoom-in" style="margin-top:10px;">Fetch</a>
</div>

@push('after_scripts')
    @basset('https://unpkg.com/urijs@1.19.11/src/URI.min.js')

    <script>

    jQuery(document).ready(function($) {

        $('.fetch-by-case-no').on('click', function(e){
            e.preventDefault();

            var case_no = $('.fetch-by-case-no-number[type="text"]').val();

            $.ajax({
                url: '{{ route('cases.fetch_by_case_no') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    // Add other data you want to send to the server
                    case_no: case_no
                },
                success: function(response) {
                    if (response.success) {
                        console.log('Success:', response.data);
                        $( '.fetch-case-number' ).hide();
                        $('.create-case[data-case-type="custom-case"]').show();

                        // Handle the success response
                    } else {
                        console.log('Error:', response.message);
                        // Handle the error response
                    }
                },
            });
        });
    });

    </script>
@endpush