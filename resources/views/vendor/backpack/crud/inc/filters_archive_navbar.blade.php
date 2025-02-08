   
<div class="row mb-2 align-items-center">
  <div class="col-sm-6">
    <a href="" class="btn btn-secondary btn-decided-filters decided-case" value="decided_toggle" data-style="zoom-in">
      Decided Cases
    </a>
  </div>
  <div class="col-sm-6">
    <a href="" class="btn btn-secondary btn-decided-filters abandoned-case" value="abbondend_toggle" data-style="zoom-in">
      Abandoned Cases
    </a>
  </div>
</div>

@push('crud_list_scripts')
    @basset('https://unpkg.com/urijs@1.19.11/src/URI.min.js')
    <script>
      jQuery(document).ready(function($) {
        $('.btn-decided-filters').on('click', function(e) {
          e.preventDefault();
          if ( $(this).hasClass('btn-secondary') ) {
            $(this).removeClass('btn-secondary').addClass('btn-primary');
            $(this).parent().siblings().find('.btn-decided-filters').removeClass('btn-secondary').removeClass('btn-primary').addClass('btn-secondary');
            var filtered_value = $(this).attr("value");
          }

          // behaviour for ajax table
          var ajax_table = $("#crudTable").DataTable();
          var current_url = ajax_table.ajax.url();
          var new_url = addOrUpdateUriParameter(current_url, 'case_decided', filtered_value);

          // replace the datatables ajax url with new_url and reload it
          new_url = normalizeAmpersand(new_url.toString());
          ajax_table.ajax.url(new_url).load();

          // add filter to URL
          crud.updateUrl(new_url);
        });

        var ajax_table = $("#crudTable").DataTable();
        var current_url = ajax_table.ajax.url();
        var urlObj = new URL(current_url);
    
        if ( urlObj.searchParams.size === 0 ) {
          $('.btn-decided-filters[value="decided_toggle"]').removeClass('btn-secondary').removeClass('btn-primary').addClass('btn-primary');
        } else {
          const tabs = urlObj.searchParams.get('case_decided');
          $('.btn-decided-filters[value="'+tabs+'"]').removeClass('btn-secondary').removeClass('btn-primary').addClass('btn-primary');
        }

      });
    </script>
@endpush

@push('crud_list_styles')
<style>
  .navbar-filters{
    display: none;
  }
  .btn-decided-filters{
    width: 100% !important;
  }

</style>

@endpush
