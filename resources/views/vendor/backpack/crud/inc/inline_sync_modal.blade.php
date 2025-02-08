<div class="modal fade" id="customSyncModal" tabindex="-1" role="dialog" aria-labelledby="customModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form action="{{ route('cases.changeDateAndStage') }}" method="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="customModalLabel">Change Next Date and Stage</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="next_date" class="col-form-label">Next Date</label>
            <input type="date" class="form-control" name="next_date" id="next_date" placeholder="Y-m-d">
          </div>
          <div class="form-group">
            <label for="case_stage" class="col-form-label">Case Stage</label>
            <input type="text" class="form-control" name="case_stage" id="case_stage">
            <input type="hidden" class="form-control" name="case_id" id="case_id">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Save</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>