<button id="inline-preview-case" type="button" class="btn btn-sm btn-link float-right inline-case-button inline-case-preview" data-case-id="{{ $entry->id }}"><span class="la la-eye"></span></button>
<button id="inline-favourite-case" type="button" class="btn btn-sm btn-link float-right inline-case-button inline-case-favourite" data-case-id="{{ $entry->id }}" title="Add as Favourite">
    @if( $entry->favourite_case  )
        <span class="la la-star"></span>
    @else
        <span class="la la-star-of-life"></span>
    @endif
</button>
<button id="inline-sync-case" type="button" class="btn btn-sm btn-link float-right inline-case-button inline-case-sync" data-case-id="{{ $entry->id }}" title="Update Next Date"><span class="la la-sync"></span></button>
    
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script> <!-- For Bootstrap 4 -->

<script type="text/javascript">
    jQuery(document).ready(function($) {
        var case_id = {{ $entry->id }};
        $('.inline-case-preview[data-case-id="'+case_id+'"]').on('click', function(){
            $.ajax({
                url: '{{ route('cases.getCaseForModal') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    case_id: case_id
                },
                success: function(response) {
                    var c = response.case
                    $modal = $('#customPreviewModal');
                    $modal.modal('show');
                    $modal.find('#case_number').val( c.case_no );
                    $modal.find('#remarks').val( c.remarks );
                    $modal.find('#brief_for').val( c.brief_for );
                    $modal.find('#tags').val( response.tags );
                    $modal.find('#assigned_to').val( response.assigned_to );
                    $modal.find("#case-preview-button").attr('href', response.preview_url);
                    $modal.find("#case-edit-button").attr('href', response.edit_url);
                    if ( response.petitioners.length > 0 && response.respondents.length > 0 ) {
                        var parties = response.petitioners[0] + " vs " + response.respondents[0];
                        $modal.find('#parties').val( parties );
                    }
                }
            });
        });
        $('.inline-case-favourite[data-case-id="'+case_id+'"]').on('click', function(){
            var $this = $(this);
            $.ajax({
                url: '{{ route('cases.changeFavourite') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    case_id: case_id
                },
                success: function(response) {
                    if ( response.favourite_case ) {
                        $this.find("span").addClass("la-star");
                        $this.find("span").removeClass("la-star-of-life");
                    } else { 
                        $this.find("span").removeClass("la-star");
                        $this.find("span").addClass("la-star-of-life");
                    }
                }
            });
        });
        $('.inline-case-sync[data-case-id="'+case_id+'"]').on('click', function(){
            var $this = $(this);
            $.ajax({
                url: '{{ route('cases.getCaseForModal') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    case_id: case_id
                },
                success: function(response) {
                    $modal = $('#customSyncModal');
                    $modal.modal('show');
                    $modal.find("#case_stage").val(response.case.case_stage);
                    $modal.find("#next_date").val(response.case.next_date);
                    $modal.find("#case_id").val(response.case.id);
                }
            });
        });
    });
</script> 

<style>
    .inline-case-button { 
        span{
            font-size: 20px !important;
        }
    }
</style>
