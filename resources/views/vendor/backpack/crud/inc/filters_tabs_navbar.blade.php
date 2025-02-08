   
<div class="row mb-2 align-items-center">
  <div class="col-sm-6">
    <a href="" class="btn btn-secondary btn-tabs-filters district-court" value="District Courts and Tribunals" data-style="zoom-in">
      District Courts and Tribunals
    </a>
  </div>
  <div class="col-sm-6">
    <a href="" class="btn btn-secondary btn-tabs-filters high-court" value="High Court" data-style="zoom-in">
      High Courts and Supreme Court
    </a>
  </div>
</div>

@push('crud_list_scripts')
    @basset('https://unpkg.com/urijs@1.19.11/src/URI.min.js')
    <script>
      jQuery(document).ready(function($) {
        $('.btn-tabs-filters').on('click', function(e) {
          e.preventDefault();
          if ( $(this).hasClass('btn-secondary') ) {
            $(this).removeClass('btn-secondary').addClass('btn-primary');
            $(this).parent().siblings().find('.btn-tabs-filters').removeClass('btn-secondary').removeClass('btn-primary').addClass('btn-secondary');
            var filtered_value = $(this).attr("value");
          } else {
            $(this).removeClass('btn-secondary').removeClass('btn-primary').addClass('btn-secondary');
            var filtered_value = "";
          }

          // behaviour for ajax table
          var ajax_table = $("#crudTable").DataTable();
          var current_url = ajax_table.ajax.url();
          var new_url = addOrUpdateUriParameter(current_url, 'tabs', filtered_value);

          // replace the datatables ajax url with new_url and reload it
          new_url = normalizeAmpersand(new_url.toString());
          ajax_table.ajax.url(new_url).load();

          // add filter to URL
          crud.updateUrl(new_url);
        });

        var ajax_table = $("#crudTable").DataTable();
        var current_url = ajax_table.ajax.url();
        var urlObj = new URL(current_url);
        const tabs = urlObj.searchParams.get('tabs');
    
        $('.btn-tabs-filters[value="'+ tabs +'"]').removeClass('btn-secondary').removeClass('btn-primary').addClass('btn-primary');

      });
    </script>
@endpush

@push('crud_list_styles')
<style>
  .btn-tabs-filters{
    width: 100% !important;
  }

  </style>

@endpush
